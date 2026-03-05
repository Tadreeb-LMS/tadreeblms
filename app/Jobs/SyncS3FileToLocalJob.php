<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncS3FileToLocalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $s3Path;
    protected $localPath;

    /**
     * @param string $s3Path     Relative path on S3 (e.g. "uploads/file.mp4")
     * @param string $localPath  Absolute path to save locally
     */
    public function __construct(string $s3Path, string $localPath)
    {
        $this->s3Path = $s3Path;
        $this->localPath = $localPath;
    }

    public function handle()
    {
        if (!Storage::disk('s3')->exists($this->s3Path)) {
            Log::warning("SyncS3FileToLocalJob: File not found on S3 — {$this->s3Path}");
            return;
        }

        // Ensure local directory exists
        $dir = dirname($this->localPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $stream = Storage::disk('s3')->readStream($this->s3Path);

        file_put_contents($this->localPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        Log::info("SyncS3FileToLocalJob: Downloaded {$this->s3Path} → {$this->localPath}");
    }
}
