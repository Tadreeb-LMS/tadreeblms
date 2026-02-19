<?php

namespace App\Services\ExternalApps;

use App\Models\ExternalApp;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ExternalAppService
{
    protected $storagePath = 'external-modules';
    protected $appStoragePath;

    public function __construct()
    {
        $this->appStoragePath = storage_path('app/' . $this->storagePath);
        if (!File::exists($this->appStoragePath)) {
            File::makeDirectory($this->appStoragePath, 0755, true);
        }
    }

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
            $tempPath = $this->appStoragePath . '/' . uniqid('temp_');
            File::makeDirectory($tempPath, 0755, true);

            // Extract zip file
            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                throw new \Exception('Failed to open zip file');
            }

            $zip->extractTo($tempPath);
            $zip->close();

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

            // Save to database
            $externalApp = ExternalApp::updateOrCreate(
                ['slug' => $moduleName],
                [
                    'name' => $moduleConfig['name'] ?? ucwords(str_replace('-', ' ', $moduleName)),
                    'description' => $moduleConfig['description'] ?? null,
                    'version' => $moduleConfig['version'] ?? '1.0.0',
                    'installed_path' => $installPath,
                    'config_file' => $installPath . '/config.json',
                    'configuration' => $moduleConfig,
                    'status' => 'active',
                    'error_message' => null,
                    'installed_at' => now(),
                    'last_updated_at' => now(),
                ]
            );

            // Run any installation commands if they exist
            $this->runInstallationCommands($installPath);

            Log::info("External app '$moduleName' installed successfully", [
                'path' => $installPath,
                'version' => $moduleConfig['version'] ?? '1.0.0'
            ]);

            return [
                'success' => true,
                'message' => "Module '$moduleName' installed successfully",
                'app' => $externalApp
            ];

        } catch (\Exception $e) {
            Log::error("Failed to install external app", [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);

            // Clean up temp directory if it exists
            if (isset($tempPath) && File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            // Update database with error status
            ExternalApp::updateOrCreate(
                ['slug' => $moduleName],
                [
                    'status' => 'error',
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
     * Validate module folder structure
     */
    protected function validateModuleStructure($modulePath)
    {
        // Check for required files
        $requiredFiles = [
            'config.json', // Module configuration
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
                // Include the installation script in a safe way
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
            'is_enabled' => $enabled,
            'last_updated_at' => now(),
        ]);

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

            // Remove from filesystem
            if ($app->installed_path && File::exists($app->installed_path)) {
                File::deleteDirectory($app->installed_path);
            }

            // Delete from database
            $app->delete();

            Log::info("External app '$slug' uninstalled successfully");

            return [
                'success' => true,
                'message' => "Module '$slug' uninstalled successfully"
            ];

        } catch (\Exception $e) {
            Log::error("Failed to uninstall external app", [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to uninstall module: ' . $e->getMessage()
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
        return url('storage/external-modules/' . $slug . '/public');
    }

    /**
     * Validate module configuration
     */
    public function validateConfiguration($slug, $config)
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
