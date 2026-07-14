<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;

class AppInstallCommand extends Command
{
    protected $signature = 'app:install
                            {--db-host=127.0.0.1 : MySQL host}
                            {--db-port=3306 : MySQL port}
                            {--db-database= : MySQL database name}
                            {--db-username= : MySQL username}
                            {--db-password= : MySQL password}
                            {--app-url= : Application URL}
                            {--skip-composer : Skip composer install}
                            {--force : Force reinstall even if already installed}';

    protected $description = 'Install the application via CLI';

    protected string $basePath;

    protected string $installedFlag;

    protected string $envFile;

    protected string $envExample;

    public function __construct()
    {
        parent::__construct();
        $this->basePath = base_path();
        $this->installedFlag = $this->basePath . '/installed';
        $this->envFile = $this->basePath . '/.env';
        $this->envExample = $this->basePath . '/.env.example';
    }

    public function handle(): int
    {
        if (file_exists($this->installedFlag)) {
            if (!$this->option('force')) {
                $this->warn('Application is already installed. Use --force to reinstall.');
                return Command::SUCCESS;
            }
            if (!$this->confirm('Reinstall will drop all data and reset the application. Continue?')) {
                $this->info('Cancelled.');
                return Command::SUCCESS;
            }
            $this->info('Force mode: clearing installation state...');
            $this->clearInstallationState();
        }

        $this->line("\n");
        $this->info('TadreebLMS Installer');
        $this->line(str_repeat('-', 40));

        $steps = [
            'check'       => 'Checking system requirements',
            'composer'    => 'Installing Composer dependencies',
            'env'         => 'Configuring environment',
            'key'         => 'Generating application key',
            'migrate'     => 'Running database migrations',
            'seed'        => 'Seeding database',
            'permissions' => 'Setting storage permissions',
            'finish'      => 'Finalizing installation',
        ];

        $bar = $this->output->createProgressBar(count($steps));
        $bar->start();

        foreach ($steps as $step => $label) {
            $this->line("\n\n=== {$label} ===");
            try {
                $this->{$step}();
            } catch (\Throwable $e) {
                $bar->finish();
                $this->error("\nInstallation failed at step '{$step}': " . $e->getMessage());
                return Command::FAILURE;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Installation complete!');

        return Command::SUCCESS;
    }

    protected function clearInstallationState(): void
    {
        @unlink($this->installedFlag);
        @unlink($this->basePath . '/.migrations_done');
        @unlink($this->basePath . '/.seed_done');
        @unlink($this->basePath . '/storage/app/installer/db_config.json');
    }

    protected function check(): void
    {
        $this->line('Checking PHP version...');
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            throw new \RuntimeException('PHP 8.0+ required, found ' . PHP_VERSION);
        }
        $this->info('PHP ' . PHP_VERSION . ' OK');

        $exts = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'curl', 'gd', 'zip', 'fileinfo'];
        $missing = [];
        foreach ($exts as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if ($missing) {
            throw new \RuntimeException('Missing PHP extensions: ' . implode(', ', $missing));
        }
        $this->info('All required PHP extensions present');
    }

    protected function composer(): void
    {
        if ($this->option('skip-composer') || file_exists($this->basePath . '/vendor/autoload.php')) {
            $this->info('Composer dependencies already installed. Skipping.');
            return;
        }

        $this->line('Running composer install...');

        $cmd = 'cd ' . escapeshellarg($this->basePath)
            . ' && composer install --no-interaction --prefer-dist --no-dev 2>&1';

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Composer install failed: ' . implode("\n", $output));
        }

        if (!file_exists($this->basePath . '/vendor/autoload.php')) {
            throw new \RuntimeException('vendor/autoload.php not found after composer install');
        }

        $this->info('Composer dependencies installed');
    }

    protected function env(): void
    {
        $this->line("\n<comment>Database configuration</comment>");
        $this->line('Press enter to accept defaults or provide values interactively.');

        $dbHost = $this->option('db-host') ?: $this->ask('DB host', env('DB_HOST', '127.0.0.1'));
        $dbPort = $this->option('db-port') ?: $this->ask('DB port', env('DB_PORT', '3306'));
        $dbDatabase = $this->option('db-database') ?: $this->ask('Database name', env('DB_DATABASE', ''));
        $dbUsername = $this->option('db-username') ?: $this->ask('DB username', env('DB_USERNAME', ''));
        $dbPassword = $this->option('db-password') ?: $this->secret('DB password') ?: env('DB_PASSWORD', '');
        $appUrl = $this->option('app-url') ?: $this->ask('Application URL', env('APP_URL', 'http://localhost'));

        $this->line('');

        if (!file_exists($this->envExample)) {
            throw new \RuntimeException('.env.example not found at ' . $this->envExample);
        }

        if (!file_exists($this->envFile)) {
            if (!copy($this->envExample, $this->envFile)) {
                throw new \RuntimeException('Failed to copy .env.example to .env');
            }
            $this->info('.env created from .env.example');
        } else {
            $this->info('.env already exists, updating values');
        }

        $env = file_get_contents($this->envFile);

        $replacements = [
            'APP_URL' => $appUrl,
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_HOST' => $dbHost,
            'DB_PORT' => $dbPort,
            'DB_DATABASE' => $dbDatabase,
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
        ];

        foreach ($replacements as $key => $value) {
            $escaped = ($key === 'DB_PASSWORD')
                ? '"' . addcslashes($value, "\\\"") . '"'
                : $value;

            if (preg_match('/^' . preg_quote($key, '/') . '=.*$/m', $env)) {
                $env = preg_replace(
                    '/^' . preg_quote($key, '/') . '=.*$/m',
                    $key . '=' . $escaped,
                    $env
                );
            } else {
                $env .= "\n" . $key . '=' . $escaped;
            }
        }

        $env .= "\n";
        file_put_contents($this->envFile, $env, LOCK_EX);

        @unlink($this->basePath . '/bootstrap/cache/config.php');

        $this->info('.env configured');
    }

    protected function key(): void
    {
        $this->line('Generating APP_KEY...');

        $appKey = 'base64:' . base64_encode(Str::random(32));

        $env = file_get_contents($this->envFile);
        if (preg_match('/^APP_KEY=.*$/m', $env)) {
            $env = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $env);
        } else {
            $env .= "\nAPP_KEY=" . $appKey . "\n";
        }
        file_put_contents($this->envFile, $env, LOCK_EX);

        $this->info('APP_KEY generated');
    }

    protected function migrate(): void
    {
        $this->checkDatabaseConnection();

        if (!$this->confirm('Run database migrations? This will create tables in the database.', true)) {
            $this->warn('Migrations skipped by user. Run manually: php artisan migrate --force');
            return;
        }

        $this->line('Running migrations...');
        $this->call('migrate', ['--force' => true]);
        file_put_contents($this->basePath . '/.migrations_done', 'done');
        $this->info('Migrations completed');
    }

    protected function seed(): void
    {
        if (!$this->confirm('Seed database with default data? This inserts demo records.', true)) {
            $this->warn('Seeding skipped by user. Run manually: php artisan db:seed --force');
            return;
        }

        $this->line('Seeding database...');
        $this->call('db:seed', ['--force' => true]);
        file_put_contents($this->basePath . '/.seed_done', 'done');
        $this->info('Database seeded');
    }

    protected function permissions(): void
    {
        $paths = [
            $this->basePath . '/storage',
            $this->basePath . '/storage/app',
            $this->basePath . '/storage/app/public',
            $this->basePath . '/storage/framework',
            $this->basePath . '/storage/framework/cache',
            $this->basePath . '/storage/framework/sessions',
            $this->basePath . '/storage/framework/views',
            $this->basePath . '/storage/logs',
            $this->basePath . '/bootstrap/cache',
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            if (!is_writable($path)) {
                chmod($path, 0775);
            }
            if (!is_writable($path)) {
                $this->warn("Path not writable: {$path}");
            }
        }
        $this->info('Storage permissions set');
    }

    protected function finish(): void
    {
        file_put_contents($this->installedFlag, 'installed');

        $env = file_get_contents($this->envFile);
        if (str_contains($env, 'APP_INSTALLED=')) {
            $env = preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $env);
        } else {
            $env .= "\nAPP_INSTALLED=true\n";
        }
        file_put_contents($this->envFile, $env);

        $this->info('Installed flag created');
    }

    protected function checkDatabaseConnection(): void
    {
        $dbHost = env('DB_HOST', $this->option('db-host') ?: '127.0.0.1');
        $dbPort = env('DB_PORT', $this->option('db-port') ?: '3306');
        $dbDatabase = env('DB_DATABASE', $this->option('db-database') ?: '');
        $dbUsername = env('DB_USERNAME', $this->option('db-username') ?: '');
        $dbPassword = env('DB_PASSWORD', $this->option('db-password') ?: '');

        try {
            new PDO(
                "mysql:host={$dbHost};port={$dbPort};dbname={$dbDatabase};charset=utf8mb4",
                $dbUsername,
                $dbPassword,
                [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                "Database connection failed: {$e->getMessage()}\n" .
                "Verify your DB credentials. Use --db-host, --db-database, --db-username, --db-password options."
            );
        }
    }
}
