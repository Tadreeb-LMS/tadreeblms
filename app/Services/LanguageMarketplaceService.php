<?php

namespace App\Services;

use App\Models\LanguageMarketplacePackage;
use App\Models\Locale;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class LanguageMarketplaceService
{
    /**
     * Get latest published source package.
     *
     * @return \App\Models\LanguageMarketplacePackage|null
     */
    public function getLatestPublishedSourcePackage()
    {
        return LanguageMarketplacePackage::query()
            ->where('package_type', 'source')
            ->where('status', 'published')
            ->latest('published_at')
            ->first();
    }

    /**
     * Build the complete contributor translation context.
     *
     * @param \App\Models\LanguageMarketplacePackage|null $sourcePackage
     * @param string $targetLocale
     * @return array
     */
    public function buildContributionContext($sourcePackage, $targetLocale)
    {
        $targetLocale = strtolower(trim((string) $targetLocale));
        $sourceLocale = $sourcePackage ? strtolower((string) $sourcePackage->target_locale) : 'en';
        $sourcePayload = $sourcePackage ? $this->readStoredPackage($sourcePackage->manifest_path) : null;
        $sourceModulesFromPackage = is_array($sourcePayload['modules'] ?? null)
            ? $sourcePayload['modules']
            : [];

        $sourceModules = $this->mergeSourceModulesWithLocaleSources($sourceModulesFromPackage, $sourceLocale);
        if (empty($sourceModules) && $sourceLocale !== 'en') {
            $sourceLocale = 'en';
            $sourceModules = $this->getCompleteLocaleSources('en');
        }

        $missingEntries = $this->detectUntranslatedEntries($sourceModules, $targetLocale);
        $missingCount = 0;
        foreach ($missingEntries as $items) {
            $missingCount += is_array($items) ? count($items) : 0;
        }

        return [
            'source_locale' => $sourceLocale,
            'source_modules' => $sourceModules,
            'source_module_count' => is_array($sourceModules) ? count($sourceModules) : 0,
            'missing_entries' => $missingEntries,
            'missing_count' => $missingCount,
        ];
    }

    /**
     * Build a normalized submission payload from JSON/file content and auto translations.
     *
     * @param \App\Models\LanguageMarketplacePackage|null $sourcePackage
     * @param string $targetLocale
     * @param string $rawPayload
     * @param array $autoTranslations
     * @return array
     */
    public function buildSubmissionPayload($sourcePackage, $targetLocale, $rawPayload, array $autoTranslations = [])
    {
        $targetLocale = strtolower(trim((string) $targetLocale));
        $rawPayload = trim((string) $rawPayload);

        if ($rawPayload !== '') {
            $decoded = json_decode($rawPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                throw new \InvalidArgumentException('Invalid JSON payload.');
            }
        } else {
            $decoded = [
                'source_locale' => $sourcePackage ? strtolower((string) $sourcePackage->target_locale) : 'en',
                'target_locale' => $targetLocale,
                'package_type' => 'translation',
                'generated_at' => now()->toIso8601String(),
                'modules' => [],
            ];
        }

        if (!empty($autoTranslations)) {
            $decoded['modules'] = array_replace_recursive((array) ($decoded['modules'] ?? []), $autoTranslations);
        }

        if (empty($decoded['target_locale'])) {
            $decoded['target_locale'] = $targetLocale;
        }

        if (empty($decoded['source_locale'])) {
            $decoded['source_locale'] = $sourcePackage ? strtolower((string) $sourcePackage->target_locale) : 'en';
        }

        return $decoded;
    }

    /**
     * Persist a submission package payload and return metadata.
     *
     * @param string $targetLocale
     * @param array $payload
     * @return array
     */
    public function persistSubmissionPayload($targetLocale, array $payload)
    {
        $targetLocale = strtolower(trim((string) $targetLocale));
        $version = now()->format('Ymd_His');
        $relativePath = 'language-marketplace/submissions/' . $targetLocale . '/' . $version . '.json';
        $absolutePath = storage_path('app/' . $relativePath);

        if (!File::isDirectory(dirname($absolutePath))) {
            File::makeDirectory(dirname($absolutePath), 0755, true);
        }

        File::put($absolutePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'version' => $version,
            'relative_path' => $relativePath,
        ];
    }

    /**
     * Sync a published package JSON to GitHub docs path.
     *
     * @param \App\Models\LanguageMarketplacePackage $package
     * @return array
     */
    public function syncPackageToGithub(LanguageMarketplacePackage $package)
    {
        $config = (array) config('services.github_docs', []);
        if (empty($config['enabled'])) {
            return ['ok' => false, 'message' => 'GitHub docs sync is disabled.'];
        }

        foreach (['token', 'owner', 'repo', 'branch', 'path_prefix'] as $requiredKey) {
            if (empty($config[$requiredKey])) {
                return ['ok' => false, 'message' => 'Missing GitHub docs config: ' . $requiredKey];
            }
        }

        $payload = $this->readStoredPackage($package->manifest_path);
        if (!$payload) {
            return ['ok' => false, 'message' => 'Package payload not found in storage.'];
        }

        $path = trim((string) $config['path_prefix'], '/');
        $targetPath = $path . '/' . strtolower((string) $package->target_locale) . '.json';
        $repoApiBase = 'https://api.github.com/repos/' . $config['owner'] . '/' . $config['repo'];
        $apiBase = $repoApiBase . '/contents/' . $targetPath;

        $client = new Client([
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'token ' . $config['token'],
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'tadreeblms-language-marketplace',
            ],
        ]);

        if (!empty($config['use_pull_requests'])) {
            return $this->syncPackageViaPullRequest($client, $repoApiBase, $apiBase, $targetPath, $config, $package, $payload);
        }

        return $this->syncPackageDirectCommit($client, $apiBase, $config, $package, $payload);
    }

    /**
     * Sync package with direct commit on configured branch.
     *
     * @param \GuzzleHttp\Client $client
     * @param string $apiBase
     * @param array $config
     * @param \App\Models\LanguageMarketplacePackage $package
     * @param array $payload
     * @return array
     */
    protected function syncPackageDirectCommit(Client $client, $apiBase, array $config, LanguageMarketplacePackage $package, array $payload)
    {
        $sha = null;
        try {
            $getResponse = $client->get($apiBase, [
                'query' => ['ref' => $config['branch']],
            ]);
            $existing = json_decode((string) $getResponse->getBody(), true);
            if (is_array($existing) && isset($existing['sha'])) {
                $sha = (string) $existing['sha'];
            }
        } catch (GuzzleException $e) {
            if ($this->extractGithubStatusCode($e) !== 404) {
                $this->recordGithubSyncResult($package, 'failed', null, null, $e->getMessage());
                return ['ok' => false, 'message' => 'GitHub read failed: ' . $e->getMessage()];
            }
        }

        $body = [
            'message' => 'chore(i18n): publish ' . strtoupper((string) $package->target_locale) . ' package ' . (string) $package->version,
            'content' => base64_encode(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'branch' => $config['branch'],
            'committer' => [
                'name' => (string) ($config['committer_name'] ?? 'TadreebLMS Bot'),
                'email' => (string) ($config['committer_email'] ?? 'noreply@tadreeblms.com'),
            ],
        ];

        if ($sha) {
            $body['sha'] = $sha;
        }

        try {
            $putResponse = $client->put($apiBase, ['json' => $body]);
            $result = json_decode((string) $putResponse->getBody(), true);
            $newSha = (string) ($result['content']['sha'] ?? '');
            $htmlUrl = (string) ($result['content']['html_url'] ?? '');
            $this->recordGithubSyncResult($package, 'synced', $newSha ?: null, $htmlUrl ?: null, null);

            return [
                'ok' => true,
                'message' => 'Synced to GitHub successfully.',
                'sha' => $newSha,
                'url' => $htmlUrl,
            ];
        } catch (GuzzleException $e) {
            $this->recordGithubSyncResult($package, 'failed', null, null, $e->getMessage());
            return ['ok' => false, 'message' => 'GitHub write failed: ' . $e->getMessage()];
        }
    }

    /**
     * Sync package by creating a branch and opening a PR.
     *
     * @param \GuzzleHttp\Client $client
     * @param string $repoApiBase
     * @param string $contentApiBase
     * @param string $targetPath
     * @param array $config
     * @param \App\Models\LanguageMarketplacePackage $package
     * @param array $payload
     * @return array
     */
    protected function syncPackageViaPullRequest(Client $client, $repoApiBase, $contentApiBase, $targetPath, array $config, LanguageMarketplacePackage $package, array $payload)
    {
        $baseBranch = (string) $config['branch'];
        $locale = strtolower((string) $package->target_locale);
        $version = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) $package->version);
        $headBranch = 'i18n/' . $locale . '-' . trim((string) $version, '-') . '-' . now()->format('YmdHis');

        try {
            $baseRefResponse = $client->get($repoApiBase . '/git/ref/heads/' . rawurlencode($baseBranch));
            $baseRef = json_decode((string) $baseRefResponse->getBody(), true);
            $baseSha = (string) ($baseRef['object']['sha'] ?? '');
            if ($baseSha === '') {
                $this->recordGithubSyncResult($package, 'failed', null, null, 'Unable to read base branch SHA.');
                return ['ok' => false, 'message' => 'Unable to read base branch SHA.'];
            }

            $client->post($repoApiBase . '/git/refs', [
                'json' => [
                    'ref' => 'refs/heads/' . $headBranch,
                    'sha' => $baseSha,
                ],
            ]);
        } catch (GuzzleException $e) {
            $this->recordGithubSyncResult($package, 'failed', null, null, $e->getMessage());
            return ['ok' => false, 'message' => 'GitHub branch creation failed: ' . $e->getMessage()];
        }

        $sha = null;
        try {
            $existingResponse = $client->get($contentApiBase, ['query' => ['ref' => $headBranch]]);
            $existing = json_decode((string) $existingResponse->getBody(), true);
            if (is_array($existing) && isset($existing['sha'])) {
                $sha = (string) $existing['sha'];
            }
        } catch (GuzzleException $e) {
            if ($this->extractGithubStatusCode($e) !== 404) {
                $this->recordGithubSyncResult($package, 'failed', null, null, $e->getMessage());
                return ['ok' => false, 'message' => 'GitHub content read failed: ' . $e->getMessage()];
            }
        }

        $commitMessage = ((string) ($config['pull_request_title_prefix'] ?? 'chore(i18n)'))
            . ': publish ' . strtoupper((string) $package->target_locale)
            . ' package ' . (string) $package->version;

        $body = [
            'message' => $commitMessage,
            'content' => base64_encode(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'branch' => $headBranch,
            'committer' => [
                'name' => (string) ($config['committer_name'] ?? 'TadreebLMS Bot'),
                'email' => (string) ($config['committer_email'] ?? 'noreply@tadreeblms.com'),
            ],
        ];

        if ($sha) {
            $body['sha'] = $sha;
        }

        try {
            $putResponse = $client->put($contentApiBase, ['json' => $body]);
            $putResult = json_decode((string) $putResponse->getBody(), true);
            $newSha = (string) ($putResult['content']['sha'] ?? '');

            $prResponse = $client->post($repoApiBase . '/pulls', [
                'json' => [
                    'title' => $commitMessage,
                    'head' => $headBranch,
                    'base' => $baseBranch,
                    'body' => 'Automated translation package sync for locale `' . strtoupper($locale) . '` from approved marketplace submission.',
                ],
            ]);
            $prResult = json_decode((string) $prResponse->getBody(), true);
            $prUrl = (string) ($prResult['html_url'] ?? '');

            $this->recordGithubSyncResult($package, 'synced', $newSha ?: null, $prUrl ?: null, null);

            return [
                'ok' => true,
                'message' => 'Pull request created successfully.',
                'sha' => $newSha,
                'url' => $prUrl,
            ];
        } catch (GuzzleException $e) {
            $this->recordGithubSyncResult($package, 'failed', null, null, $e->getMessage());
            return ['ok' => false, 'message' => 'GitHub PR sync failed: ' . $e->getMessage()];
        }
    }

    /**
     * Extract HTTP status from GitHub exception when available.
     *
     * @param \Throwable $e
     * @return int
     */
    protected function extractGithubStatusCode($e)
    {
        if (method_exists($e, 'getResponse') && $e->getResponse()) {
            return (int) $e->getResponse()->getStatusCode();
        }

        if (method_exists($e, 'getCode')) {
            return (int) $e->getCode();
        }

        return 0;
    }

    /**
     * Export locale files into a versioned JSON package and optionally publish docs copy.
     *
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param string $packageType
     * @param bool $publishDocsCopy
     * @return array
     */
    public function exportLocalePackage($sourceLocale, $targetLocale, $packageType = 'translation', $publishDocsCopy = false)
    {
        $sourceLocale = strtolower(trim((string) $sourceLocale));
        $targetLocale = strtolower(trim((string) $targetLocale));
        $modules = $this->getCompleteLocaleSources($targetLocale);

        $payload = [
            'source_locale' => $sourceLocale,
            'target_locale' => $targetLocale,
            'package_type' => $packageType,
            'generated_at' => now()->toIso8601String(),
            'modules' => $modules,
        ];

        $version = now()->format('Ymd_His');
        $relativePath = 'language-marketplace/' . $packageType . '/' . $targetLocale . '/' . $version . '.json';
        $absolutePath = storage_path('app/' . $relativePath);
        if (!File::isDirectory(dirname($absolutePath))) {
            File::makeDirectory(dirname($absolutePath), 0755, true);
        }
        File::put($absolutePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($publishDocsCopy) {
            $docsPath = base_path('docs/language-library/' . $targetLocale . '.json');
            if (!File::isDirectory(dirname($docsPath))) {
                File::makeDirectory(dirname($docsPath), 0755, true);
            }
            File::put($docsPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return [
            'relative_path' => $relativePath,
            'payload' => $payload,
            'version' => $version,
        ];
    }

    /**
     * Import a JSON package into locale files.
     *
     * @param string $targetLocale
     * @param array $decoded
     * @return void
     */
    public function importLocalePackage($targetLocale, array $decoded)
    {
        $targetLocale = strtolower(trim((string) $targetLocale));
        $modules = $this->extractModules($decoded);
        $langDir = resource_path('lang/' . $targetLocale);

        if (!File::isDirectory($langDir)) {
            File::makeDirectory($langDir, 0755, true);
        }

        foreach ($modules as $module => $translations) {
            if (!is_array($translations)) {
                continue;
            }

            if ($module === '__json' || strpos((string) $module, '__db__') === 0) {
                continue;
            }

            $moduleName = preg_replace('/[^a-z0-9_]+/', '_', strtolower(trim((string) $module)));
            if ($moduleName === '') {
                continue;
            }

            $modulePath = $langDir . DIRECTORY_SEPARATOR . $moduleName . '.php';
            $existing = [];
            if (File::exists($modulePath)) {
                $loaded = include $modulePath;
                if (is_array($loaded)) {
                    $existing = $loaded;
                }
            }

            $merged = array_replace_recursive($existing, Arr::undot(Arr::dot($translations)));
            $php = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
            File::put($modulePath, $php);
        }

        $this->importJsonTranslations($targetLocale, (array) ($modules['__json'] ?? []));
        $this->importDbTranslations($targetLocale, $modules);

        if (Schema::hasTable('locales')) {
            $locale = Locale::firstOrCreate(
                ['short_name' => $targetLocale],
                ['name' => strtoupper($targetLocale), 'display_type' => 'ltr']
            );

            if (Schema::hasColumn('locales', 'is_enabled')) {
                $locale->is_enabled = 1;
            }
            if (Schema::hasColumn('locales', 'library_uploaded_at')) {
                $locale->library_uploaded_at = now();
            }
            $locale->save();
        }
    }

    /**
     * Read package file into array.
     *
     * @param string $relativePath
     * @return array|null
     */
    public function readStoredPackage($relativePath)
    {
        $absolutePath = storage_path('app/' . ltrim((string) $relativePath, '/'));
        if (!File::exists($absolutePath)) {
            return null;
        }

        $decoded = json_decode((string) File::get($absolutePath), true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Return locale modules for a locale code.
     *
     * @param string $localeCode
     * @return array
     */
    public function getLocaleModules($localeCode)
    {
        return $this->readLocaleModules($localeCode);
    }

    /**
     * Get all label sources for a locale: php modules, json file, and DB translations.
     *
     * @param string $localeCode
     * @return array
     */
    public function getCompleteLocaleSources($localeCode)
    {
        $modules = $this->readLocaleModules($localeCode);

        $jsonTranslations = $this->readLocaleJsonLabels($localeCode);
        if (!empty($jsonTranslations)) {
            $modules['__json'] = $jsonTranslations;
        }

        $dbGroups = $this->readDbTranslationLabels($localeCode);
        foreach ($dbGroups as $group => $translations) {
            $modules['__db__' . $group] = $translations;
        }

        return $modules;
    }

    /**
     * Merge package modules with live locale sources to maximize label coverage.
     *
     * @param array $packageModules
     * @param string $sourceLocale
     * @return array
     */
    public function mergeSourceModulesWithLocaleSources(array $packageModules, $sourceLocale)
    {
        $liveModules = $this->getCompleteLocaleSources($sourceLocale);
        return array_replace_recursive($liveModules, $packageModules);
    }

    /**
     * Detect untranslated entries by comparing source modules with target locale files.
     *
     * @param array $sourceModules
     * @param string $targetLocale
     * @return array
     */
    public function detectUntranslatedEntries(array $sourceModules, $targetLocale)
    {
        $targetModules = $this->getCompleteLocaleSources($targetLocale);
        $result = [];

        foreach ($sourceModules as $module => $translations) {
            if (!is_array($translations)) {
                continue;
            }

            $sourceFlat = $this->flattenStringLeaves($translations);
            $targetFlat = $this->flattenStringLeaves((array) ($targetModules[$module] ?? []));

            foreach ($sourceFlat as $path => $sourceValue) {
                $targetValue = array_key_exists($path, $targetFlat) ? $targetFlat[$path] : null;
                $isUntranslated = $targetValue === null
                    || trim((string) $targetValue) === ''
                    || (string) $targetValue === (string) $sourceValue;

                if (!$isUntranslated) {
                    continue;
                }

                $result[$module][] = [
                    'key' => $path,
                    'source' => (string) $sourceValue,
                    'current' => $targetValue === null ? '' : (string) $targetValue,
                ];
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Get currently available locale modules.
     *
     * @param string $localeCode
     * @return array
     */
    protected function readLocaleModules($localeCode)
    {
        $langPath = resource_path('lang/' . $localeCode);
        if (!File::isDirectory($langPath)) {
            return [];
        }

        $modules = [];
        foreach (File::files($langPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $module = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $content = include $file->getRealPath();
            if (is_array($content)) {
                $modules[$module] = $content;
            }
        }

        ksort($modules);
        return $modules;
    }

    /**
     * Read locale JSON file labels, if available.
     *
     * @param string $localeCode
     * @return array
     */
    protected function readLocaleJsonLabels($localeCode)
    {
        $jsonPath = resource_path('lang/' . $localeCode . '.json');
        if (!File::exists($jsonPath)) {
            return [];
        }

        $decoded = json_decode((string) File::get($jsonPath), true);
        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $result[(string) $key] = (string) $value;
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Read DB-managed translations grouped by translation group.
     *
     * @param string $localeCode
     * @return array
     */
    protected function readDbTranslationLabels($localeCode)
    {
        if (!Schema::hasTable('ltm_translations')) {
            return [];
        }

        $rows = DB::table('ltm_translations')
            ->where('locale', $localeCode)
            ->get(['group', 'key', 'value']);

        $groups = [];
        foreach ($rows as $row) {
            $group = (string) $row->group;
            if ($group === '') {
                $group = 'default';
            }
            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }

            $groups[$group][(string) $row->key] = (string) ($row->value ?? '');
        }

        ksort($groups);
        return $groups;
    }

    /**
     * Extract modules from package payload.
     *
     * @param array $decoded
     * @return array
     */
    protected function extractModules(array $decoded)
    {
        if (isset($decoded['modules']) && is_array($decoded['modules'])) {
            return $decoded['modules'];
        }

        if (isset($decoded['module'], $decoded['translations']) && is_array($decoded['translations'])) {
            return [$decoded['module'] => $decoded['translations']];
        }

        return Arr::except($decoded, ['source_locale', 'target_locale', 'generated_at', 'package_type', 'meta']);
    }

    /**
     * Flatten only leaf string values from nested translation arrays.
     *
     * @param array $translations
     * @return array
     */
    protected function flattenStringLeaves(array $translations)
    {
        $flat = Arr::dot($translations);
        $result = [];

        foreach ($flat as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $result[(string) $key] = (string) $value;
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Import JSON translations into resources/lang/<locale>.json.
     *
     * @param string $targetLocale
     * @param array $jsonTranslations
     * @return void
     */
    protected function importJsonTranslations($targetLocale, array $jsonTranslations)
    {
        if (empty($jsonTranslations)) {
            return;
        }

        $jsonPath = resource_path('lang/' . $targetLocale . '.json');
        $existing = [];
        if (File::exists($jsonPath)) {
            $decoded = json_decode((string) File::get($jsonPath), true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $merged = array_merge($existing, $jsonTranslations);
        ksort($merged);
        File::put($jsonPath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Import DB translations from module buckets prefixed with __db__.
     *
     * @param string $targetLocale
     * @param array $modules
     * @return void
     */
    protected function importDbTranslations($targetLocale, array $modules)
    {
        if (!Schema::hasTable('ltm_translations')) {
            return;
        }

        foreach ($modules as $module => $translations) {
            if (!is_array($translations) || strpos((string) $module, '__db__') !== 0) {
                continue;
            }

            $group = substr((string) $module, 6);
            if ($group === '') {
                $group = 'default';
            }

            foreach ($translations as $key => $value) {
                $value = (string) $value;
                $existing = DB::table('ltm_translations')
                    ->where('locale', $targetLocale)
                    ->where('group', $group)
                    ->where('key', (string) $key)
                    ->first();

                if ($existing) {
                    DB::table('ltm_translations')
                        ->where('id', $existing->id)
                        ->update([
                            'value' => $value,
                            'status' => 1,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('ltm_translations')->insert([
                        'locale' => $targetLocale,
                        'group' => $group,
                        'key' => (string) $key,
                        'value' => $value,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Create package record.
     *
     * @param array $attributes
     * @return \App\Models\LanguageMarketplacePackage
     */
    public function createPackageRecord(array $attributes)
    {
        return LanguageMarketplacePackage::create($attributes);
    }

    /**
     * Persist sync metadata when GitHub sync columns exist.
     *
     * @param \App\Models\LanguageMarketplacePackage $package
     * @param string $status
     * @param string|null $sha
     * @param string|null $url
     * @param string|null $error
     * @return void
     */
    protected function recordGithubSyncResult(LanguageMarketplacePackage $package, $status, $sha = null, $url = null, $error = null)
    {
        if (!Schema::hasColumn('language_marketplace_packages', 'github_sync_status')) {
            return;
        }

        $package->github_sync_status = $status;
        if (Schema::hasColumn('language_marketplace_packages', 'github_sync_sha')) {
            $package->github_sync_sha = $sha;
        }
        if (Schema::hasColumn('language_marketplace_packages', 'github_sync_url')) {
            $package->github_sync_url = $url;
        }
        if (Schema::hasColumn('language_marketplace_packages', 'github_sync_error')) {
            $package->github_sync_error = $error;
        }
        if (Schema::hasColumn('language_marketplace_packages', 'github_synced_at')) {
            $package->github_synced_at = $status === 'synced' ? now() : null;
        }
        $package->save();
    }
}
