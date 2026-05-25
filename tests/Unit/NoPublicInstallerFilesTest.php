<?php

namespace Tests\Unit;

use Tests\TestCase;

class NoPublicInstallerFilesTest extends TestCase
{
    private const FORBIDDEN_FILES = [
        'install.php',
        'install_ajax.php',
        'install-b.php',
        'db_config.json',
        'composer.phar',
        'phpinfo.php',
    ];

    public function test_no_forbidden_files_in_public(): void
    {
        foreach (self::FORBIDDEN_FILES as $file) {
            $path = public_path($file);
            $this->assertFileDoesNotExist(
                $path,
                "Forbidden file {$file} must not be web-accessible."
            );
        }
    }

    public function test_index_php_does_not_redirect_to_installer(): void
    {
        $indexContents = file_get_contents(public_path('index.php'));

        $this->assertStringNotContainsString(
            'install.php',
            $indexContents,
            'public/index.php must not redirect to install.php'
        );
    }

    public function test_artisan_install_command_exists(): void
    {
        $this->assertTrue(
            class_exists(\App\Console\Commands\AppInstallCommand::class),
            'AppInstallCommand class must exist'
        );
    }
}
