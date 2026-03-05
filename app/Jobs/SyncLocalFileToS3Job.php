<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncLocalFileToS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $localPath;
    protected $s3Path;

    /**
     * @param string $localPath  Absolute path to the local file
     * @param string $s3Path     Relative path to store on S3 (e.g. "uploads/file.mp4")
     */
    public function __construct(string $localPath, string $s3Path)
    {
        $this->localPath = $localPath;
        $this->s3Path = $s3Path;
    }

    public function handle()
    {
        if (!file_exists($this->localPath)) {
            Log::warning("SyncLocalFileToS3Job: File not found — {$this->localPath}");
            return;
        }

        $stream = fopen($this->localPath, 'r');

        Storage::disk('s3')->put($this->s3Path, $stream, [
            'visibility' => 'private',
        ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        Log::info("SyncLocalFileToS3Job: Uploaded {$this->s3Path}");
    }
}
