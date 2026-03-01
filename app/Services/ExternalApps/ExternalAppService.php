<?php

namespace App\Services\ExternalApps;

use App\Models\ExternalApp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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

        $lines  = explode("\n", str_replace("\r\n", "\n", File::get($path)));
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            // Strip surrounding quotes if present
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            $result[trim($key)] = $value;
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

        $lines = explode("\n", str_replace("\r\n", "\n", file_get_contents($path)));

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$envKey, $value] = explode('=', $line, 2);
            if (trim($envKey) === $key) {
                $value = trim($value);
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }
                return $value;
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

            $pattern = "/^{$key}=.*$/m";

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

        $pattern = "/^{$key}=.*$/m";

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
     * Dispatch a background job to sync one test file from local uploads/ to S3.
     */
    protected function dispatchTestFileSync(): void
    {
        $uploadsDir = public_path('storage/uploads');

        if (!File::exists($uploadsDir)) {
            Log::info('dispatchTestFileSync: No uploads directory found, skipping.');
            return;
        }

        // Pick the first file in uploads/
        $files = File::files($uploadsDir);

        if (empty($files)) {
            Log::info('dispatchTestFileSync: No files in uploads/, skipping.');
            return;
        }

        $file = $files[0];
        $localPath = $file->getPathname();
        $s3Path = 'uploads/' . $file->getFilename();

        \App\Jobs\SyncLocalFileToS3Job::dispatch($localPath, $s3Path);

        Log::info("dispatchTestFileSync: Queued {$s3Path} for S3 upload.");
    }

    /**
     * Dispatch a background job to download one test file from S3 back to local.
     * Uses the same first file that dispatchTestFileSync would have uploaded.
     */
    protected function dispatchTestFileDownload(): void
    {
        $uploadsDir = public_path('storage/uploads');

        if (!File::exists($uploadsDir)) {
            File::makeDirectory($uploadsDir, 0777, true);
        }

        // Pick the first file in uploads/ to know what was synced
        $files = File::files($uploadsDir);

        if (empty($files)) {
            Log::info('dispatchTestFileDownload: No files in uploads/ to reference, skipping.');
            return;
        }

        $file = $files[0];
        $s3Path = 'uploads/' . $file->getFilename();
        $localPath = $uploadsDir . '/' . $file->getFilename();

        \App\Jobs\SyncS3FileToLocalJob::dispatch($s3Path, $localPath);

        Log::info("dispatchTestFileDownload: Queued {$s3Path} for local download.");
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
     * Upload and extract external app zip file
     */
    public function uploadAndInstall($file, $moduleName)
    {
        try {
            // Validate file
            if ($file->getMimeType() !== 'application/zip') {
                throw new \Exception('File must be a zip archive');
            }

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

            // If the zip contained a single wrapper directory, unwrap it
            $tempPath = $this->unwrapSingleDirectory($extractPath);

            // Validate module structure (check for required files)
            $this->validateModuleStructure($tempPath);

            // Get module metadata from config file
            $moduleConfig = $this->readModuleConfig($tempPath);

            // Create the final installation directory
            $installPath = $this->appStoragePath . '/' . $moduleName;

            if (File::exists($installPath)) {
                File::deleteDirectory($installPath);
            }

            File::moveDirectory($tempPath, $installPath);

            // Clean up the outer temp directory if it was unwrapped
            if ($extractPath !== $tempPath && File::exists($extractPath)) {
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

            // Refresh the sidebar cache so changes appear immediately
            $this->refreshEnabledAppsCache();

            Log::info("External app '$moduleName' installed successfully", [
                'path'    => $installPath,
                'version' => $moduleConfig['version'] ?? '1.0.0',
            ]);

            return [
                'success' => true,
                'message' => "Module '$moduleName' installed successfully",
                'app'     => $externalApp,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to install external app", [
                'module' => $moduleName,
                'error'  => $e->getMessage(),
            ]);

            // Clean up temp directories if they exist
            if (isset($extractPath) && File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            if (isset($tempPath) && $tempPath !== ($extractPath ?? null) && File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            // Update database with error status
            ExternalApp::updateOrCreate(
                ['slug' => $moduleName],
                [
                    'status'        => 'error',
                    'error_message' => $e->getMessage(),
                ]
            );

            return [
                'success' => false,
                'message' => 'Failed to install module: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * If the extracted zip contains a single top-level directory (wrapper),
     * return the path to that inner directory instead.
     * e.g. temp_xxx/External-Storage/config.json → returns temp_xxx/External-Storage
     */
    protected function unwrapSingleDirectory(string $path): string
    {
        $items = File::glob($path . '/*');

        // If there's exactly one item and it's a directory, unwrap it
        if (count($items) === 1 && File::isDirectory($items[0])) {
            return $items[0];
        }

        return $path;
    }

    /**
     * Validate module folder structure
     */
    protected function validateModuleStructure($modulePath)
    {
        $requiredFiles = [
            'config.json',
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
        $installScript = $modulePath . '/install.php';

        if (File::exists($installScript)) {
            try {
                include $installScript;
            } catch (\Exception $e) {
                Log::warning("Installation script returned warning: " . $e->getMessage());
            }
        }
    }

    /**
     * Toggle app enabled/disabled status
     */
    public function toggleStatus($slug, $enabled)
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

        // When external-storage module is disabled, revert to local storage and download test file back
        if ($slug === 'external-storage' && !$enabled) {
            $this->writeMainEnvFlag('FILESYSTEM_DRIVER', 'local');
            $this->dispatchTestFileDownload();
        }

        // When external-storage module is enabled, sync storage driver and queue one test file upload
        if ($slug === 'external-storage' && $enabled) {
            $moduleDriver = $this->getModuleEnv('external-storage', 'STORAGE_DRIVER') ?: 'local';
            $this->writeMainEnvFlag('FILESYSTEM_DRIVER', $moduleDriver === 's3' ? 's3' : 'local');

            // Dispatch a test sync: pick the first file from uploads/
            $this->dispatchTestFileSync();
        }

        Log::info("External app '$slug' status changed to: " . ($enabled ? 'enabled' : 'disabled'));

        return $app;
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
                    include $app->installed_path . '/uninstall.php';
                } catch (\Exception $e) {
                    Log::warning("Uninstall script warning: " . $e->getMessage());
                }
            }

            // Remove from filesystem (this also removes the module's .env)
            if ($app->installed_path && File::exists($app->installed_path)) {
                File::deleteDirectory($app->installed_path);
            }

            // Revert to local storage if external-storage module is being uninstalled
            if ($slug === 'external-storage') {
                $this->writeMainEnvFlag('FILESYSTEM_DRIVER', 'local');
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
