<?php

namespace App\Services\ExternalApps;

use App\Models\ExternalApp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ExternalAppService
{
    protected $appStoragePath;

    public function __construct()
    {
        // Modules live at {project-root}/modules/
        $this->appStoragePath = base_path('modules');
        if (!File::exists($this->appStoragePath)) {
            File::makeDirectory($this->appStoragePath, 0755, true);
        }
    }

    // -------------------------------------------------------------------------
    // Module .env Helpers
    // -------------------------------------------------------------------------

    /**
     * Get the absolute path to a module's .env file.
     */
    protected function moduleDotEnvPath(string $slug): string
    {
        return $this->appStoragePath . '/' . $slug . '/.env';
    }

    /**
     * Parse a module's .env file and return all key-value pairs as an array.
     * Lines starting with # are comments and are ignored.
     */
    public function getModuleEnvAll(string $slug): array
    {
        $path = $this->moduleDotEnvPath($slug);

        if (!File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        return $this->parseEnvContent($content);
    }

    /**
     * Internal helper to parse .env content with support for multi-line quoted values.
     */
    protected function parseEnvContent(string $content): array
    {
        $result = [];
        // Match key=value pairs. Value can be:
        // 1. Double quoted: "..." (can contain escaped quotes and newlines)
        // 2. Single quoted: '...' (can contain newlines)
        // 3. Unquoted: any chars until newline or #
        // Regex: /^\s*([A-Z0-9_]+)\s*=\s*(?:("((?:[^"\\]|\\.)*)"|'([^']*)'|([^#\n\r]*)))/m
        $pattern = '/^\s*([A-Z0-9_]+)\s*=[ \t]*(?:("((?:[^"\\\\]|\\\\.)*)"|\'([^\']*)\'|([^#\n\r]*)))/m';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = $match[1];
                if (isset($match[3]) && $match[3] !== '') { // Double quoted
                    $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $match[3]);
                } elseif (isset($match[4]) && $match[4] !== '') { // Single quoted
                    $value = $match[4];
                } else { // Unquoted
                    $value = trim($match[5] ?? '');
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Read a single value from a module's .env file.
     */
    public function getModuleEnv(string $slug, string $key, $default = null)
    {
        return $this->getModuleEnvAll($slug)[$key] ?? $default;
    }

    /**
     * Static version of getModuleEnv — usable from config files (no DI).
     */
    public static function staticGetModuleEnv(string $slug, string $key, $default = null)
    {
        // Resolve project root without needing Laravel bootstrap (ExternalAppService.php is 3 dirs deep)
        $path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($path)) {
            return $default;
        }

        $content = file_get_contents($path);
        
        // Simple static parser similar to the above but without dependencies
        $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=[ \t]*(?:("((?:[^"\\\\]|\\\\.)*)"|\'([^\']*)\'|([^#\n\r]*)))/m';

        if (preg_match($pattern, $content, $match)) {
            if (isset($match[2]) && $match[2] !== '') { // Double quoted
                return str_replace(['\\"', '\\\\'], ['"', '\\'], $match[2]);
            } elseif (isset($match[3]) && $match[3] !== '') { // Single quoted
                return $match[3];
            } else { // Unquoted
                return trim($match[4] ?? '');
            }
        }

        return $default;
    }

    /**
     * Write / update key-value pairs into a module's .env file.
     * Creates the file if it does not exist.
     */
    public function setModuleEnv(string $slug, array $data): void
    {
        $path = $this->moduleDotEnvPath($slug);

        // Ensure the module directory exists before writing the .env file
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $envContent = File::exists($path) ? File::get($path) : '';

        foreach ($data as $key => $value) {
            $value   = (string) $value;
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);

            // Quote values that contain whitespace, quotes, # or are empty
            if (preg_match('/[\s"\'#]/', $escaped) || $escaped === '') {
                $valueForEnv = '"' . $escaped . '"';
            } else {
                $valueForEnv = $escaped;
            }

            $pattern = "/^\s*{$key}\s*=[ \t]*(?:\"(?:[^\"\\\\]|\\\\.)*\"|'[^']*'|[^#\r\n]*)/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$valueForEnv}", $envContent);
            } else {
                $envContent .= PHP_EOL . "{$key}={$valueForEnv}";
            }
        }

        File::put($path, ltrim($envContent));
    }

    /**
     * Create a module's .env file with empty keys derived from config.json fields.
     * Existing files are not overwritten (preserves saved credentials on re-installs).
     */
    protected function createModuleDotEnv(string $slug, array $moduleConfig): void
    {
        $path = $this->moduleDotEnvPath($slug);

        // Don't overwrite so that re-installs keep saved credentials
        if (File::exists($path)) {
            return;
        }

        // Ensure the module directory exists
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $fields = $moduleConfig['metadata']['fields'] ?? [];
        $lines  = ["# {$moduleConfig['name']} credentials — managed by External Apps module"];

        foreach ($fields as $key => $meta) {
            $lines[] = "{$key}=";
        }

        File::put($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    // -------------------------------------------------------------------------
    // Main .env Flag Helpers (via inline write — same logic as EnvManagerTrait)
    // -------------------------------------------------------------------------

    /**
     * Write or update a single key in the main .env file.
     */
    protected function writeMainEnvFlag(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        // Quote if necessary
        $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
        $valueForEnv = preg_match('/[\s"\'#]/', $escaped) || $escaped === ''
            ? '"' . $escaped . '"'
            : $escaped;

        $pattern = "/^\s*{$key}\s*=\s*(?:\"(?:[^\"\\\\]|\\\\.)*\"|'[^']*'|[^#\r\n]*)/m";

        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, "{$key}={$valueForEnv}", $envContent);
        } else {
            $envContent .= PHP_EOL . "{$key}={$valueForEnv}";
        }

        File::put($envPath, $envContent);
    }

    /**
     * Derive the main .env integration flag key from module config.
     * Falls back to SLUG_INTEGRATION (uppercased) if not defined in config.json.
     */
    protected function integrationFlagKey(string $slug, array $moduleConfig): string
    {
        return $moduleConfig['integration_key']
            ?? strtoupper(str_replace('-', '_', $slug)) . '_INTEGRATION';
    }

    /**
     * Refresh the cached list of enabled external apps used by the sidebar.
     */
    protected function refreshEnabledAppsCache(): void
    {
        $enabledApps = ExternalApp::where('is_enabled', 1)->pluck('is_enabled', 'slug')->toArray();
        Cache::put('enabled_external_apps', $enabledApps, 3600);
    }

    /**
     * Queue individual jobs to sync local uploads to S3.
     */
    protected function dispatchUploadsToS3(): int
    {
        $uploadsDir = public_path('storage/uploads');

        if (!File::exists($uploadsDir)) {
            return 0;
        }

        $files = File::allFiles($uploadsDir);
        $count = 0;

        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            \App\Jobs\SyncLocalFileToS3Job::dispatch($file->getPathname(), 'uploads/' . $relativePath);
            $count++;
        }

        Log::info("SyncUploadsToS3: Queued {$count} file(s).");
        return $count;
    }

    /**
     * Queue individual jobs to download S3 files to local uploads.
     */
    protected function dispatchS3ToUploads(): int
    {
        $uploadsDir = public_path('storage/uploads');

        if (!File::exists($uploadsDir)) {
            File::makeDirectory($uploadsDir, 0777, true);
        }

        try {
            $s3Files = Storage::disk('s3')->allFiles('uploads');
        } catch (\Exception $e) {
            Log::warning('SyncS3ToUploads: Could not list S3 — ' . $e->getMessage());
            return 0;
        }

        $count = 0;
        foreach ($s3Files as $s3Path) {
            $relativePath = ltrim(substr($s3Path, strlen('uploads')), '/');
            $localPath = $uploadsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            \App\Jobs\SyncS3FileToLocalJob::dispatch($s3Path, $localPath);
            $count++;
        }

        Log::info("SyncS3ToUploads: Queued {$count} file(s).");
        return $count;
    }

    /**
     * Sync storage driver and S3 credentials to the main .env file.
     * Called when S3 settings are saved so that Laravel's built-in
     * Storage::disk('s3') and config('filesystems.default') work correctly.
     */
    public function syncStorageEnv(string $driver, array $credentials = []): void
    {
        $this->writeMainEnvFlag('FILESYSTEM_DRIVER', $driver === 's3' ? 's3' : 'local');

        if ($driver === 's3') {
            foreach ($credentials as $key => $value) {
                $this->writeMainEnvFlag($key, (string) $value);
            }
        }
    }

    // -------------------------------------------------------------------------
    // CRUD Operations
    // -------------------------------------------------------------------------

    /**
     * Upload and extract external app zip file.
     * The module slug is auto-detected from the zip filename keywords
     * ("zoom" → zoom, "teams" → teams) or derived from config.json name.
     * Only the folder containing both config.json and install.php is installed.
     */
    public function uploadAndInstall($file, $originalFileName = null)
    {
        $extractPath = null;

        try {
            // Validate file
            if ($file->getMimeType() !== 'application/zip') {
                throw new \Exception('File must be a zip archive');
            }

            // Use provided filename or fall back to the uploaded file's name
            $zipFileName = $originalFileName ?: $file->getClientOriginalName();

            // Create a temporary directory for extraction
            $extractPath = $this->appStoragePath . '/' . uniqid('temp_');
            File::makeDirectory($extractPath, 0755, true);

            // Extract zip file
            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                throw new \Exception('Failed to open zip file');
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // Find the folder that contains both config.json and install.php
            $contentDir = $this->findModuleContentDir($extractPath);

            // Validate module structure (check for required files)
            $this->validateModuleStructure($contentDir);

            // Get module metadata from config file
            $moduleConfig = $this->readModuleConfig($contentDir);

            // ── Determine the module slug ──────────────────────────────
            // Keyword map: if the zip filename contains a keyword, force that slug
            $keywordSlugMap = [
                'zoom'             => 'zoom',
                'google-meet'      => 'google-meet-integration',
                'meet'             => 'google-meet-integration',
                'teams'            => 'teams',
                'external-storage' => 'external-storage',
                'storage'          => 'external-storage',
            ];

            $moduleName = null;
            $lowerZipName = strtolower($zipFileName);

            foreach ($keywordSlugMap as $keyword => $slug) {
                if (str_contains($lowerZipName, $keyword)) {
                    $moduleName = $slug;
                    break;
                }
            }

            // Fall back: derive slug from config.json "name" field
            if (!$moduleName) {
                $configName = $moduleConfig['name'] ?? null;
                $moduleName = $configName
                    ? Str::slug($configName)
                    : Str::slug(pathinfo($zipFileName, PATHINFO_FILENAME));
            }

            if (empty($moduleName)) {
                throw new \Exception('Could not determine module name from zip file or config.json.');
            }

            // Create the final installation directory
            $installPath = $this->appStoragePath . '/' . $moduleName;

            // Block re-upload if the module is already installed
            $existingApp = ExternalApp::where('slug', $moduleName)->first();
            if (File::exists($installPath) && $existingApp) {
                // Clean up temp files
                File::deleteDirectory($extractPath);

                $displayName = $existingApp->name ?? $moduleName;
                $status      = $existingApp->is_enabled ? 'enabled' : 'disabled';

                return [
                    'success' => false,
                    'message' => "Module '{$displayName}' is already installed (currently {$status}). Please uninstall it first if you want to re-upload.",
                ];
            }

            // Move only the content folder to the final install path
            File::moveDirectory($contentDir, $installPath);

            // Clean up the entire temp directory
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            // Save to database
            $externalApp = ExternalApp::updateOrCreate(
                ['slug' => $moduleName],
                [
                    'name'           => $moduleConfig['name'] ?? ucwords(str_replace('-', ' ', $moduleName)),
                    'description'    => $moduleConfig['description'] ?? null,
                    'version'        => $moduleConfig['version'] ?? '1.0.0',
                    'installed_path' => $installPath,
                    'config_file'    => $installPath . '/config.json',
                    'configuration'  => $moduleConfig,
                    'status'         => 'active',
                    'error_message'  => null,
                    'installed_at'   => now(),
                    'last_updated_at' => now(),
                ]
            );

            // Create the module's .env file (with empty credential keys)
            $this->createModuleDotEnv($moduleName, $moduleConfig);

            // Run any installation commands if they exist
            $this->runInstallationCommands($installPath);

            // Create public symlink for serving module assets (JS, CSS, images)
            // $this->ensurePublicSymlink($moduleName, $installPath);

            // Refresh the sidebar cache so changes appear immediately
            $this->refreshEnabledAppsCache();

            Log::info("External app '$moduleName' installed successfully", [
                'path'    => $installPath,
                'version' => $moduleConfig['version'] ?? '1.0.0',
                'zip'     => $zipFileName,
            ]);

            return [
                'success' => true,
                'message' => "Module '$moduleName' installed successfully",
                'app'     => $externalApp,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to install external app", [
                'zip'   => $originalFileName,
                'error' => $e->getMessage(),
            ]);

            // Clean up temp directory
            if ($extractPath && File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            return [
                'success' => false,
                'message' => 'Failed to install module: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Recursively search the extracted directory for the folder that contains
     * both config.json and install.php — the actual module content directory.
     */
    protected function findModuleContentDir(string $basePath): string
    {
        // Check the base path itself first
        if (
            File::exists($basePath . '/config.json') &&
            File::exists($basePath . '/install.php')
        ) {
            return $basePath;
        }

        // Search subdirectories recursively
        $directories = File::directories($basePath);

        foreach ($directories as $dir) {
            if (
                File::exists($dir . '/config.json') &&
                File::exists($dir . '/install.php')
            ) {
                return $dir;
            }
        }

        // Go one more level deep if not found yet
        foreach ($directories as $dir) {
            $subDirs = File::directories($dir);
            foreach ($subDirs as $subDir) {
                if (
                    File::exists($subDir . '/config.json') &&
                    File::exists($subDir . '/install.php')
                ) {
                    return $subDir;
                }
            }
        }

        throw new \Exception(
            'No valid module found in the zip file. The zip must contain a folder with both config.json and install.php.'
        );
    }

    /**
     * Validate module folder structure
     */
    protected function validateModuleStructure($modulePath)
    {
        $requiredFiles = [
            'config.json',
            'install.php',
        ];

        foreach ($requiredFiles as $file) {
            if (!File::exists($modulePath . '/' . $file)) {
                throw new \Exception("Missing required file: $file");
            }
        }
    }

    /**
     * Read module configuration from config.json
     */
    protected function readModuleConfig($modulePath)
    {
        $configPath = $modulePath . '/config.json';

        if (!File::exists($configPath)) {
            return [];
        }

        $config = json_decode(File::get($configPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid config.json file');
        }

        return $config;
    }

    /**
     * Run installation commands from the module
     */
    protected function runInstallationCommands($modulePath)
    {
        // First, run composer install if composer.json exists
        $this->runComposerInstall($modulePath);

        $installScript = $modulePath . '/install.php';

        if (File::exists($installScript)) {
            try {
                ob_start();
                include $installScript;
                ob_end_clean();
            } catch (\Exception $e) {
                ob_end_clean();
                Log::warning("Installation script returned warning: " . $e->getMessage());
            }
        }
    }

    /**
     * Run composer install for the module if composer.json exists.
     */
    protected function runComposerInstall($modulePath)
    {
        if (!File::exists($modulePath . '/composer.json')) {
            return;
        }

        Log::info("Running composer install for module at: $modulePath");

        $composerPhar = base_path('composer.phar');
        if (!File::exists($composerPhar)) {
            Log::warning("composer.phar not found at project root. Skipping composer install for module.");
            return;
        }

        // We use php composer.phar install
        // --no-dev: Skip dev dependencies
        // --optimize-autoloader: Generate optimized class map
        // --working-dir: Specify the module directory
        $command = "php \"$composerPhar\" install --no-dev --optimize-autoloader --working-dir=\"$modulePath\" 2>&1";

        try {
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error("Composer install failed for module at $modulePath", [
                    'command' => $command,
                    'output' => implode("\n", $output),
                    'return_code' => $returnVar
                ]);
            } else {
                Log::info("Composer install successful for module at $modulePath");
            }
        } catch (\Exception $e) {
            Log::error("Exception during composer install for module: " . $e->getMessage());
        }
    }

    /**
     * Toggle app enabled/disabled status.
     */
    public function toggleStatus($slug, $enabled): array
    {
        $app = ExternalApp::where('slug', $slug)->firstOrFail();

        if (!$app->isInstalled()) {
            throw new \Exception('Module is not properly installed');
        }

        $app->update([
            'is_enabled'      => $enabled,
            'last_updated_at' => now(),
        ]);

        // Refresh the sidebar cache so changes appear immediately
        $this->refreshEnabledAppsCache();

        $syncInfo = null;

        if ($slug === 'external-storage') {
            if ($enabled) {
                // Restore driver to s3 if credentials are configured
                $hasCredentials = $this->getModuleEnv('external-storage', 'S3_ACCESS_KEY_ID')
                               && $this->getModuleEnv('external-storage', 'S3_BUCKET');
                $this->setModuleEnv('external-storage', [
                    'STORAGE_DRIVER' => $hasCredentials ? 's3' : 'local',
                ]);
                $count = $this->dispatchUploadsToS3();
                $syncInfo = ['direction' => 'local_to_s3', 'file_count' => $count];
            } else {
                // Force local when disabled
                $this->setModuleEnv('external-storage', [
                    'STORAGE_DRIVER' => 'local',
                ]);
                $count = $this->dispatchS3ToUploads();
                $syncInfo = ['direction' => 's3_to_local', 'file_count' => $count];
            }
        }

        if ($slug === 'interactive-whiteboard') {
            $this->writeMainEnvFlag('WHITEBOARD_INTEGRATION', $enabled ? 'true' : 'false');
        }

        Log::info("External app '$slug' status changed to: " . ($enabled ? 'enabled' : 'disabled'));

        return ['app' => $app, 'sync' => $syncInfo];
    }

    /**
     * Uninstall and remove external app
     */
    public function uninstall($slug)
    {
        try {
            $app = ExternalApp::where('slug', $slug)->firstOrFail();

            // Run uninstall script if exists
            if ($app->installed_path && File::exists($app->installed_path . '/uninstall.php')) {
                try {
                    ob_start();
                    include $app->installed_path . '/uninstall.php';
                    ob_end_clean();
                } catch (\Exception $e) {
                    ob_end_clean();
                    Log::warning("Uninstall script warning: " . $e->getMessage());
                }
            }

            // Remove public symlink for module assets
            // $this->removePublicSymlink($app->slug);

            // Remove from filesystem (this also removes the module's .env)
            if ($app->installed_path && File::exists($app->installed_path)) {
                File::deleteDirectory($app->installed_path);
            }

            // Delete from database
            $app->delete();

            // Refresh the sidebar cache so changes appear immediately
            $this->refreshEnabledAppsCache();

            Log::info("External app '$slug' uninstalled successfully");

            return [
                'success' => true,
                'message' => "Module '$slug' uninstalled successfully",
            ];

        } catch (\Exception $e) {
            Log::error("Failed to uninstall external app", [
                'slug'  => $slug,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to uninstall module: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get all installed external apps
     */
    public function getInstalledApps()
    {
        return ExternalApp::all();
    }

    /**
     * Get a specific external app
     */
    public function getApp($slug)
    {
        return ExternalApp::where('slug', $slug)->firstOrFail();
    }

    /**
     * Create a symlink from public/modules/{slug} → modules/{slug}
     * so that module assets (JS, CSS) are web-accessible.
     */
    protected function ensurePublicSymlink(string $slug, string $installPath): void
    {
        $publicModulesDir = public_path('modules');
        $linkPath = $publicModulesDir . DIRECTORY_SEPARATOR . $slug;

        // Create public/modules/ directory if it doesn't exist
        if (!File::isDirectory($publicModulesDir)) {
            File::makeDirectory($publicModulesDir, 0755, true);
        }

        // Remove existing link or directory if present
        if (is_link($linkPath)) {
            unlink($linkPath);
        } elseif (File::isDirectory($linkPath)) {
            File::deleteDirectory($linkPath);
        }

        // Create symlink: public/modules/{slug} → modules/{slug}
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: use junction for directory symlinks
            exec('mklink /J "' . str_replace('/', '\\', $linkPath) . '" "' . str_replace('/', '\\', $installPath) . '"');
        } else {
            symlink($installPath, $linkPath);
        }

        Log::info("Public symlink created for module '$slug'", [
            'link' => $linkPath,
            'target' => $installPath,
        ]);
    }

    /**
     * Remove the public symlink for a module.
     */
    protected function removePublicSymlink(string $slug): void
    {
        $linkPath = public_path('modules' . DIRECTORY_SEPARATOR . $slug);

        if (is_link($linkPath)) {
            unlink($linkPath);
        } elseif (File::isDirectory($linkPath)) {
            File::deleteDirectory($linkPath);
        }
    }

    /**
     * Get app public assets path
     */
    public function getAssetPath($slug)
    {
        // Assets served via public/modules symlink → {project-root}/modules/{slug}/public
        return url('modules/' . $slug . '/public');
    }

    /**
     * Validate module configuration against the module's validate-config.php.
     * The $configuration array comes from the module .env, not the DB.
     */
    public function validateConfiguration($slug, $configuration)
    {
        $app = $this->getApp($slug);

        if (!$app->installed_path) {
            throw new \Exception('Module is not installed');
        }

        $validatorScript = $app->installed_path . '/validate-config.php';

        if (File::exists($validatorScript)) {
            include $validatorScript;
        }

        return true;
    }
}
