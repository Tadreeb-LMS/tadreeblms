<?php

namespace Tests\Unit;

use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    private const FORBIDDEN_PUBLIC_FILES = [
        'install.php',
        'install_ajax.php',
        'install-b.php',
        'db_config.json',
        'composer.phar',
        'phpinfo.php',
        'install_error.log',
    ];

    private const NON_INDEX_PHP_FILES_IN_PUBLIC = [
        'ckeditor/samples/old/sample_posteddata.php',
        'ckeditor/samples/old/assets/posteddata.php',
        'plugins/amigo-sorter/index.php',
    ];

    public function test_no_forbidden_files_in_public(): void
    {
        foreach (self::FORBIDDEN_PUBLIC_FILES as $file) {
            $path = public_path($file);
            $this->assertFileDoesNotExist(
                $path,
                "Forbidden file {$file} must not be web-accessible."
            );
        }
    }

    public function test_only_index_php_exists_in_public_root(): void
    {
        $phpFiles = glob(public_path('*.php'));
        $allowed = [public_path('index.php')];

        $unexpected = array_diff($phpFiles, $allowed);
        $this->assertEmpty(
            $unexpected,
            'Only public/index.php should exist in public/ root. Found: ' . implode(', ', $unexpected)
        );
    }

    public function test_removed_installer_files_not_present(): void
    {
        foreach (self::NON_INDEX_PHP_FILES_IN_PUBLIC as $file) {
            $path = public_path($file);
            $this->assertFileDoesNotExist(
                $path,
                "Removed file {$file} should not exist."
            );
        }
    }

    public function test_htaccess_blocks_non_index_php(): void
    {
        $htaccess = file_get_contents(public_path('.htaccess'));

        $this->assertStringContainsString(
            '<FilesMatch "\.php$">',
            $htaccess,
            '.htaccess must have a FilesMatch rule to deny all PHP files'
        );

        $this->assertStringContainsString(
            'Require all denied',
            $htaccess,
            '.htaccess must deny all PHP files by default'
        );

        $this->assertStringContainsString(
            '<Files "index.php">',
            $htaccess,
            '.htaccess must have an explicit grant for index.php'
        );

        $this->assertStringContainsString(
            'Require all granted',
            $htaccess,
            '.htaccess must grant access to index.php'
        );
    }

    public function test_env_example_has_no_hardcoded_credentials(): void
    {
        $envExample = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString(
            'APP_KEY=',
            $envExample
        );

        preg_match('/^APP_KEY=(.*)$/m', $envExample, $matches);
        $appKey = trim($matches[1] ?? '');
        $this->assertEmpty(
            $appKey,
            '.env.example APP_KEY must be empty (placeholder). Found: ' . $appKey
        );

        preg_match('/^DB_PASSWORD=(.*)$/m', $envExample, $matches);
        $dbPassword = trim($matches[1] ?? '');
        $this->assertEmpty(
            $dbPassword,
            '.env.example DB_PASSWORD must be empty (placeholder). Found: ' . $dbPassword
        );

        preg_match('/^DEMO_PASSWORD=(.*)$/m', $envExample, $matches);
        $demoPassword = trim($matches[1] ?? '');
        $this->assertEmpty(
            $demoPassword,
            '.env.example DEMO_PASSWORD must be empty (placeholder). Found: ' . $demoPassword
        );

        preg_match('/^LDAP_PASSWORD=(.*)$/m', $envExample, $matches);
        $ldapPassword = trim($matches[1] ?? '');
        $this->assertEmpty(
            $ldapPassword,
            '.env.example LDAP_PASSWORD must be empty (placeholder). Found: ' . $ldapPassword
        );
    }

    public function test_seeders_use_env_instead_of_hardcoded_passwords(): void
    {
        $seeder = file_get_contents(base_path('database/seeders/Auth/UserTableSeeder.php'));

        preg_match_all("/'password'\s*=>\s*'secret'/", $seeder, $hardcoded);
        $this->assertEmpty(
            $hardcoded[0],
            'UserTableSeeder must not have hardcoded passwords. Found: ' . implode(', ', $hardcoded[0])
        );
    }

    public function test_user_factory_uses_env_for_password(): void
    {
        $factory = file_get_contents(base_path('database/factories/UserFactory.php'));

        $this->assertStringContainsString(
            "env('DEMO_PASSWORD', 'secret')",
            $factory,
            'UserFactory must use env() for default password'
        );
    }

    public function test_no_real_keygen_tokens_in_source(): void
    {
        $searchDirs = [
            base_path('app'),
            base_path('config'),
            base_path('database'),
            base_path('routes'),
            base_path('resources/views'),
        ];

        foreach ($searchDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php' && $file->getExtension() !== 'env') {
                    continue;
                }
                $content = file_get_contents($file->getPathname());
                if (preg_match('/KEYGEN_API_TOKEN\s*=\s*["\']?admin-[a-zA-Z0-9]{20,}/', $content)) {
                    $this->fail('Hardcoded KEYGEN_API_TOKEN found in: ' . $file->getPathname());
                }
            }
        }

        $this->assertTrue(true, 'No hardcoded KEYGEN tokens found in source');
    }

    public function test_artisan_install_does_not_write_keygen_tokens(): void
    {
        $commandPath = base_path('app/Console/Commands/AppInstallCommand.php');

        if (!file_exists($commandPath)) {
            $this->markTestSkipped('AppInstallCommand not present');
            return;
        }

        $contents = file_get_contents($commandPath);

        $this->assertStringNotContainsString(
            'KEYGEN_ACCOUNT_ID',
            $contents,
            'AppInstallCommand must not hardcode KEYGEN_ACCOUNT_ID'
        );

        $this->assertStringNotContainsString(
            'KEYGEN_API_TOKEN',
            $contents,
            'AppInstallCommand must not hardcode KEYGEN_API_TOKEN'
        );
    }

    public function test_public_php_files_are_audited(): void
    {
        $phpFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(public_path(), \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $unexpected = [];
        foreach ($phpFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $relative = str_replace(public_path() . '/', '', $file->getPathname());
            if ($relative === 'index.php') {
                continue;
            }
            $unexpected[] = $relative;
        }

        $this->assertEmpty(
            $unexpected,
            'Unexpected PHP files under public/: ' . implode(', ', $unexpected)
        );
    }
}
