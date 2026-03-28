<?php

class InstallerCore
{
    private $basePath;
    private $envFile;
    private $installerDir;
    private $dbConfigFile;
    private $migrationDoneFile;
    private $seedDoneFile;
    private $installedFlag;
    private $phpBin;
    private $composerBin;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->envFile = $basePath . '/.env';
        $this->installerDir = $basePath . '/storage/app/installer';
        $this->dbConfigFile = $this->installerDir . '/db_config.json';
        $this->migrationDoneFile = $basePath . '/.migrations_done';
        $this->seedDoneFile = $basePath . '/.seed_done';
        $this->installedFlag = $basePath . '/installed';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->phpBin = trim((string) shell_exec('where php'));
            $this->composerBin = trim((string) shell_exec('composer --version 2>&1'));
        } else {
            $this->phpBin = trim((string) shell_exec('which php'));
            $this->composerBin = $this->detectComposerBin();
        }
    }

    private function detectComposerBin()
    {
        // Try `which` first — works when composer is anywhere on $PATH
        $which = trim((string) shell_exec('which composer 2>/dev/null'));
        if ($which !== '' && is_executable($which)) {
            return $which;
        }

        // Fall back through common install locations
        $candidates = [
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            getenv('HOME') . '/.local/bin/composer',
            getenv('HOME') . '/.composer/vendor/bin/composer',
            $this->basePath . '/composer.phar',
        ];

        foreach ($candidates as $path) {
            if ($path !== '' && is_executable((string) $path)) {
                return (string) $path;
            }
        }

        return '';
    }

    public function handle($step, $requestMethod = 'GET', array $input = [])
    {
        if (!$this->basePath) {
            return $this->fail('Base path not resolved');
        }

        if (!$this->phpBin) {
            return $this->fail('PHP 8.2 CLI not found. Please install php8.2-cli');
        }

        if ($this->isInstalled()) {
            return $this->fail('Application already installed');
        }

        if ($step === 'db_config' && strtoupper($requestMethod) === 'POST') {
            return $this->saveDbConfig($input);
        }

        try {
            switch ($step) {
                case 'check':
                    return $this->stepCheck();
                case 'composer':
                    return $this->stepComposer();
                case 'db_config':
                    return ['success' => true, 'message' => 'Please enter database info', 'show_db_form' => true, 'next' => 'env'];
                case 'env':
                    return $this->stepEnv();
                case 'key':
                    return $this->stepKey();
                case 'migrate':
                    return $this->stepMigrate();
                case 'seed':
                    return $this->stepSeed();
                case 'permissions':
                    return $this->stepPermissions();
                case 'finish':
                    return $this->stepFinish();
                default:
                    return $this->fail('Invalid step');
            }
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    private function isInstalled()
    {
        return file_exists($this->installedFlag);
    }

    private function fail($message, $showDbForm = false)
    {
        return [
            'success' => false,
            'message' => '❌ ' . $message,
            'show_db_form' => $showDbForm,
        ];
    }

    private function vendorExists()
    {
        return file_exists($this->basePath . '/vendor/autoload.php');
    }

    private function blockIfNoVendor()
    {
        if (!$this->vendorExists()) {
            return $this->fail('Dependencies not installed.<br><pre>composer install</pre>');
        }

        return null;
    }

    private function setEnvValue($env, $key, $value)
    {
        $line = $key . '=' . $value;
        if (preg_match('/^' . preg_quote($key, '/') . '=.*$/m', $env)) {
            return (string) preg_replace('/^' . preg_quote($key, '/') . '=.*$/m', $line, $env);
        }

        return rtrim($env) . "\n" . $line . "\n";
    }

    private function ensureDatabaseExists(array $dbCfg)
    {
        $host = (string) ($dbCfg['host'] ?? '127.0.0.1');
        $port = (string) ($dbCfg['port'] ?? 3306);
        $database = (string) ($dbCfg['database'] ?? '');
        $username = (string) ($dbCfg['username'] ?? '');
        $password = (string) ($dbCfg['password'] ?? '');

        if ($database === '') {
            throw new RuntimeException('Database name is empty.');
        }

        $pdo = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $quotedDb = str_replace('`', '``', $database);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$quotedDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    private function saveDbConfig(array $input)
    {
        $dbHost = trim((string) ($input['db_host'] ?? ''));
        $dbDatabase = trim((string) ($input['db_database'] ?? ''));
        $dbUsername = trim((string) ($input['db_username'] ?? ''));
        $dbPassword = (string) ($input['db_password'] ?? '');
        $appUrl = trim((string) ($input['app_url'] ?? 'http://localhost'));

        if ($dbHost === '' || $dbDatabase === '' || $dbUsername === '') {
            return $this->fail('All database fields are required', true);
        }

        if (!filter_var($appUrl, FILTER_VALIDATE_URL)) {
            return $this->fail('APP_URL must be a valid URL (e.g. http://yourdomain.com)', true);
        }

        if (!is_dir($this->installerDir)) {
            mkdir($this->installerDir, 0750, true);
        }

        file_put_contents($this->dbConfigFile, json_encode([
            'host' => $dbHost,
            'database' => $dbDatabase,
            'username' => $dbUsername,
            'password' => $dbPassword,
            'app_url' => $appUrl,
        ], JSON_PRETTY_PRINT), LOCK_EX);

        return [
            'success' => true,
            'message' => '✔ Database configuration saved',
            'show_db_form' => false,
            'next' => 'env',
        ];
    }

    private function stepCheck()
    {
        @unlink($this->dbConfigFile);
        @unlink($this->migrationDoneFile);
        @unlink($this->seedDoneFile);

        $msg = '<strong>Checking system requirements...</strong><br>';
        $ok = true;

        $version = trim((string) shell_exec("{$this->phpBin} -v"));
        preg_match('/PHP\s+([0-9\.]+)/', $version, $matches);
        $phpVer = $matches[1] ?? 'unknown';

        if (version_compare($phpVer, '8.2.0', '>=')) {
            $msg .= "✔ PHP {$phpVer} OK (8.2.x)<br>";
        } else {
            $msg .= "❌ PHP 8.2+ required, found {$phpVer}<br>";
            $ok = false;
        }

        $exts = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'curl', 'gd', 'zip', 'fileinfo'];
        foreach ($exts as $ext) {
            if (!extension_loaded($ext)) {
                $msg .= "❌ Missing extension: {$ext}<br>";
                $ok = false;
            }
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $composerOutput = trim((string) shell_exec('composer --version 2>&1'));
            if ($composerOutput === '') {
                $msg .= '❌ Composer not found or not available in PATH<br>';
                $ok = false;
            } elseif (preg_match('/Composer version ([0-9.]+)/', $composerOutput, $m)) {
                if (version_compare($m[1], '2.0.0', '>=')) {
                    $msg .= "✔ Composer {$m[1]} OK<br>";
                } else {
                    $msg .= "❌ Composer 2.x required, found {$m[1]}<br>";
                    $ok = false;
                }
            } else {
                $msg .= '❌ Unable to detect Composer version<br>';
                $ok = false;
            }
        } else {
            if ($this->composerBin === '' || !is_executable($this->composerBin)) {
                $msg .= '❌ Composer not found. Install it globally or place composer.phar in the project root.<br>';
                $ok = false;
            } else {
                $composerVersion = trim((string) shell_exec("{$this->phpBin} {$this->composerBin} --version 2>&1"));
                if (preg_match('/Composer version ([0-9.]+)/', $composerVersion, $cm)) {
                    if (version_compare($cm[1], '2.0.0', '>=')) {
                        $msg .= "✔ Composer {$cm[1]} OK<br>";
                    } else {
                        $msg .= "❌ Composer 2.x required, found {$cm[1]}<br>";
                        $ok = false;
                    }
                } else {
                    $msg .= '❌ Unable to detect Composer version<br>';
                    $ok = false;
                }
            }
        }

        if (!$ok) {
            return $this->fail($msg . '<br>Fix errors and reload');
        }

        return ['success' => true, 'message' => $msg . '✔ All requirements OK', 'next' => 'composer'];
    }

    private function stepComposer()
    {
        if (!is_writable($this->basePath)) {
            if (stripos(PHP_OS, 'WIN') === 0) {
                return $this->fail('Permission issue. Please ensure the project folder is writable (Windows user permissions).');
            }

            return $this->fail("Permission issue. Run:<br><pre>sudo chown -R \$USER:www-data {$this->basePath}\nsudo chmod -R 775 {$this->basePath}</pre>");
        }

        if (stripos(PHP_OS, 'WIN') === 0) {
            $cmd = 'cd /d "' . $this->basePath . '" && composer install --no-interaction --prefer-dist 2>&1';
        } else {
            $composerCmd = $this->composerBin !== '' ? $this->composerBin : 'composer';
            $cmd = 'cd "' . $this->basePath . '" && COMPOSER_HOME=/tmp HOME=/tmp ' . $composerCmd . ' install --no-interaction --prefer-dist 2>&1';
        }

        $output = (string) shell_exec($cmd);

        if (!$this->vendorExists()) {
            return $this->fail('Composer failed:<br><pre>' . $output . '</pre>');
        }

        return ['success' => true, 'message' => '✔ Dependencies installed', 'next' => 'db_config'];
    }

    private function stepEnv()
    {
        if (!file_exists($this->dbConfigFile)) {
            return $this->fail('DB config missing');
        }

        $config = json_decode((string) file_get_contents($this->dbConfigFile), true);
        if (!is_array($config)) {
            return $this->fail('Invalid DB config');
        }

        $envExample = $this->basePath . '/.env.example';
        if (!file_exists($envExample)) {
            return $this->fail('.env.example not found');
        }

        if (!file_exists($this->envFile) && !copy($envExample, $this->envFile)) {
            return $this->fail('Failed to create .env from .env.example');
        }

        if (!is_readable($this->envFile)) {
            return $this->fail('.env not readable');
        }

        if (!is_writable($this->envFile)) {
            return $this->fail('.env not writable. Run: sudo chown $USER:www-data ' . $this->envFile . ' && sudo chmod 664 ' . $this->envFile);
        }

        $env = (string) file_get_contents($this->envFile);

        $replacements = [
            'DB_HOST' => $config['host'] ?? '',
            'DB_DATABASE' => $config['database'] ?? '',
            'DB_USERNAME' => $config['username'] ?? '',
            'DB_PASSWORD' => $config['password'] ?? '',
            'APP_URL' => $config['app_url'] ?? 'http://localhost',
        ];

        foreach ($replacements as $key => $value) {
            $escapedValue = $key === 'DB_PASSWORD' ? '"' . addcslashes((string) $value, "\\\"") . '"' : (string) $value;

            if (preg_match('/^' . preg_quote($key, '/') . '=.*$/m', $env)) {
                $env = preg_replace('/^' . preg_quote($key, '/') . '=.*$/m', $key . '=' . $escapedValue, $env);
            } else {
                $env .= "\n{$key}={$escapedValue}";
            }
        }

        if (strpos($env, 'ZOOM_INTEGRATION=') === false) {
            $env .= "\nZOOM_INTEGRATION=false";
        }

        // Generate APP_KEY here so we only ever write .env once.
        // A second atomic rename (in stepKey) would trigger a second artisan serve
        // restart and kill the in-flight HTTP response, causing a NetworkError.
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $env = $this->setEnvValue($env, 'APP_KEY', $appKey);

        $env .= "\n";

        $tmpEnvFile = $this->envFile . '.tmp';
        if (file_put_contents($tmpEnvFile, $env, LOCK_EX) === false) {
            return $this->fail('Failed to write temporary .env');
        }

        if (!rename($tmpEnvFile, $this->envFile)) {
            @unlink($tmpEnvFile);
            return $this->fail('Failed to replace .env');
        }

        return ['success' => true, 'message' => '.env created ✔', 'next' => 'key'];
    }

    private function stepKey()
    {
        // APP_KEY is written by stepEnv() in the same atomic .env write to avoid
        // triggering a second artisan-serve restart that would kill this response.
        // This step just verifies the key is present.
        if (!file_exists($this->envFile)) {
            return $this->fail('.env file not found.');
        }

        $key = $this->readEnvValue('APP_KEY');
        if (!$key || !str_starts_with($key, 'base64:')) {
            return $this->fail('APP_KEY missing or invalid in .env — try rerunning the env step.');
        }

        return ['success' => true, 'message' => '✔ APP_KEY verified', 'next' => 'migrate'];
    }

    private function stepMigrate()
    {
        $vendorError = $this->blockIfNoVendor();
        if ($vendorError) {
            return $vendorError;
        }

        $dbCfg = @json_decode((string) @file_get_contents($this->dbConfigFile), true) ?: [];
        try {
            $this->ensureDatabaseExists($dbCfg);
            $pdo = new PDO(
                'mysql:host=' . ($dbCfg['host'] ?? '127.0.0.1') . ';port=' . ($dbCfg['port'] ?? 3306) . ';dbname=' . ($dbCfg['database'] ?? ''),
                $dbCfg['username'] ?? '',
                $dbCfg['password'] ?? '',
                [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            unset($pdo);
        } catch (PDOException $e) {
            return $this->fail('Cannot connect to database: ' . $e->getMessage() . '<br>Check your credentials and go back to the db_config step.');
        }

        exec("{$this->phpBin} \"{$this->basePath}/artisan\" migrate --force 2>&1", $out, $ret);
        if ($ret !== 0) {
            return $this->fail("Migration failed:\n" . implode("\n", $out));
        }

        file_put_contents($this->migrationDoneFile, 'done');

        return ['success' => true, 'message' => '✔ Migrations completed', 'next' => 'seed'];
    }

    private function stepSeed()
    {
        $vendorError = $this->blockIfNoVendor();
        if ($vendorError) {
            return $vendorError;
        }

        exec("{$this->phpBin} \"{$this->basePath}/artisan\" db:seed --force 2>&1", $out, $ret);
        if ($ret !== 0) {
            return $this->fail("Seeding failed:\n" . implode("\n", $out));
        }

        file_put_contents($this->seedDoneFile, 'done');

        exec("{$this->phpBin} \"{$this->basePath}/artisan\" storage:link --force 2>&1", $lnOut, $lnRet);
        $lnMsg = $lnRet === 0 ? '✔ Storage link created' : '⚠ storage:link: ' . implode(' ', $lnOut);

        return ['success' => true, 'message' => "✔ Database seeded<br>{$lnMsg}", 'next' => 'permissions'];
    }

    private function stepPermissions()
    {
        foreach (['storage', 'bootstrap/cache'] as $dir) {
            if (!is_writable("{$this->basePath}/{$dir}")) {
                return $this->fail("{$dir} is not writable");
            }
        }

        return ['success' => true, 'message' => '✔ Permissions OK', 'next' => 'finish'];
    }

    private function readEnvValue($key, $default = null)
    {
        if (!file_exists($this->envFile)) {
            return $default;
        }

        $env = file($this->envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($env === false) {
            return $default;
        }

        foreach ($env as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$k, $v] = explode('=', $line, 2);
            if (trim($k) === $key) {
                return trim((string) $v, " \t\n\r\0\x0B\"");
            }
        }

        return $default;
    }

    private function stepFinish()
    {
        file_put_contents($this->installedFlag, 'installed');

        $env = (string) file_get_contents($this->envFile);
        if (strpos($env, 'APP_INSTALLED=') === false) {
            $env .= "\nAPP_INSTALLED=true\n";
        } else {
            $env = preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $env);
        }

        file_put_contents($this->envFile, $env);

        @unlink($this->dbConfigFile);
        @rmdir($this->installerDir);

        $postNote = '<br><br><strong>⚙ Post-Installation Checklist</strong><br>'
            . '<small>'
            . '• <b>Queue worker</b>: run <code>php artisan queue:work</code> (or configure Supervisor/systemd)<br>'
            . '• <b>Scheduler</b>: add to crontab: <code>* * * * * ' . $this->phpBin . ' ' . $this->basePath . '/artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code><br>'
            . '• <b>Security</b>: delete <code>install.php</code>, <code>install_ajax.php</code>, and <code>install-b.php</code> from <code>public/</code><br>'
            . '</small>';

        $appUrl = (string) $this->readEnvValue('APP_URL', '/');
        if ($appUrl === '') {
            $appUrl = '/';
        }
        $openAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        if ($openAppUrl === '') {
            $openAppUrl = '/';
        }

        return [
            'success' => true,
            'message' => "✔ Installation complete! <a href='{$openAppUrl}'>Open Application</a>{$postNote}",
            'next' => null,
            'redirect_url' => $openAppUrl,
        ];
    }
}
