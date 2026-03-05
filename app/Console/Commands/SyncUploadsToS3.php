<?php

namespace App\Console\Commands;

use App\Jobs\SyncLocalFileToS3Job;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncUploadsToS3 extends Command
{
    protected $signature = 'storage:sync-to-s3
                            {--dry-run : List files that would be synced without dispatching jobs}';

    protected $description = 'Sync all files from public/storage/uploads to S3 (dispatches one job per file)';

    public function handle(): int
    {
        $uploadsDir = public_path('storage/uploads');

        if (!File::exists($uploadsDir)) {
            $this->warn("Uploads directory not found: {$uploadsDir}");
            return self::FAILURE;
        }

        $files = File::allFiles($uploadsDir);

        if (empty($files)) {
            $this->info('No files found in uploads directory. Nothing to sync.');
            return self::SUCCESS;
        }

        $isDryRun = $this->option('dry-run');
        $count    = 0;

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . 'Scanning uploads directory...');

        foreach ($files as $file) {
            // Normalise to forward slashes for the S3 key
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $s3Path       = 'uploads/' . $relativePath;

            if ($isDryRun) {
                $this->line("  Would upload: {$s3Path}");
            } else {
                SyncLocalFileToS3Job::dispatch($file->getPathname(), $s3Path);
                $this->line("  Queued: {$s3Path}");
            }

            $count++;
        }

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . "Total: {$count} file(s) " . ($isDryRun ? 'found.' : 'queued for upload to S3.'));

        return self::SUCCESS;
    }
}
