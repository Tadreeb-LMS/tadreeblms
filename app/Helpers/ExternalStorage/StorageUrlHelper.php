<?php

namespace App\Helpers\ExternalStorage;

use Illuminate\Support\Facades\Storage;
use App\Services\ExternalApps\ExternalAppService;

class StorageUrlHelper
{
    /**
     * Generate a URL for the given file path based on the active storage driver.
     *
     * @param  string  $path  The file path relative to the storage root
     * @param  int|null  $expirationMinutes  Expiration time for temporary URLs (S3 only)
     * @return string
     */
    public function url(string $path, ?int $expirationMinutes = null): string
    {
        $driver = ExternalAppService::staticGetModuleEnv('external-storage', 'STORAGE_DRIVER') ?: 'local';

        if ($driver === 's3') {
            return $this->s3Url($path, $expirationMinutes);
        }

        return $this->localUrl($path);
    }

    protected function s3Url(string $path, ?int $expirationMinutes = null): string
    {
        $disk = Storage::disk('external-s3');
        $expiration = $expirationMinutes ?? 60;

        return $disk->temporaryUrl($path, now()->addMinutes($expiration));
    }

    protected function localUrl(string $path): string
    {
        return asset('storage/' . $path);
    }
}