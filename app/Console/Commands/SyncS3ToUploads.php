<?php

namespace App\Console\Commands;

use App\Jobs\SyncS3FileToLocalJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncS3ToUploads extends Command
{
    protected $signature = 'storage:sync-from-s3
                            {--dry-run : List files that would be synced without dispatching jobs}';

    protected $description = 'Sync all files from S3 (uploads/ prefix) down to public/storage/uploads (dispatches one job per file)';

    public function handle(): int
    {
        try {
            $s3Files = Storage::disk('s3')->allFiles('uploads');
        } catch (\Exception $e) {
            $this->error('Failed to list S3 files: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (empty($s3Files)) {
            $this->info('No files found on S3 under uploads/. Nothing to sync.');
            return self::SUCCESS;
        }

        $uploadsDir = public_path('storage/uploads');
        $isDryRun   = $this->option('dry-run');
        $count      = 0;

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . 'Scanning S3 uploads/ prefix...');

        foreach ($s3Files as $s3Path) {
            // s3Path = 'uploads/subdir/file.ext'
            // strip the leading 'uploads/' to get the relative path
            $relativePath = ltrim(substr($s3Path, strlen('uploads')), '/');
            $localPath    = $uploadsDir . DIRECTORY_SEPARATOR
                          . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if ($isDryRun) {
                $this->line("  Would download: {$s3Path} → {$localPath}");
            } else {
                SyncS3FileToLocalJob::dispatch($s3Path, $localPath);
                $this->line("  Queued: {$s3Path}");
            }

            $count++;
        }

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . "Total: {$count} file(s) " . ($isDryRun ? 'found.' : 'queued for download from S3.'));

        return self::SUCCESS;
    }
}
