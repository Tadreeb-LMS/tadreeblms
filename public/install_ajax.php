<?php
ob_start();
header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Base Paths
|--------------------------------------------------------------------------
*/
$basePath = realpath(__DIR__ . '/..');
if (!$basePath) {
    echo json_encode(['success' => false, 'message' => 'Base path not resolved']);
    exit;
}

$envFile           = $basePath . '/.env';
$dbConfigFile      = __DIR__ . '/db_config.json';
$migrationDoneFile = $basePath . '/.migrations_done';
$seedDoneFile      = $basePath . '/.seed_done';
$installedFlag     = $basePath . '/installed';

/*
|--------------------------------------------------------------------------
| Prevent reinstall
|--------------------------------------------------------------------------
*/
if (file_exists($installedFlag) && ($_GET['step'] ?? '') !== 'check') {
    echo json_encode(['success' => false, 'message' => '❌ Application already installed']);
    exit;
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/
function fail($msg)
{
    echo json_encode(['success' => false, 'message' => "❌ $msg", 'show_db_form' => false]);
    exit;
}

function nextStep($current)
{
    $steps = ["check", "composer", "db_config", "env", "key", "migrate", "seed", "permissions", "finish"];
    $i = array_search($current, $steps);
    return $steps[$i + 1] ?? null;
}

function vendorExists($basePath)
{
    return file_exists($basePath . '/vendor/autoload.php');
}

function blockIfNoVendor($basePath)
{
    if (!vendorExists($basePath)) {
        echo json_encode([
            'success' => false,
            'message' => "❌ Dependencies not installed.<br><pre>composer install</pre>",
            'next' => 'composer'
        ]);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| PHP & Composer Binaries
|--------------------------------------------------------------------------
*/
$phpBin = trim(shell_exec('which php8.2'));  // <-- ensure PHP 8.2 is installed
if (!$phpBin) fail("PHP 8.2 CLI not found. Please install php8.2-cli");

$composerBin = '/usr/local/bin/composer'; // adjust if composer 2.7 is installed elsewhere
if (!file_exists($composerBin)) fail("Composer not found at $composerBin");

/*
|--------------------------------------------------------------------------
| Current Step
|--------------------------------------------------------------------------
*/
$step = $_REQUEST['step'] ?? 'check';

/*
|--------------------------------------------------------------------------
| Handle DB Config Save
|--------------------------------------------------------------------------
*/
if ($step === 'db_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host     = trim($_POST['db_host'] ?? '');
    $db_database = trim($_POST['db_database'] ?? '');
    $db_username = trim($_POST['db_username'] ?? '');
    $db_password = $_POST['db_password'] ?? '';

    if ($db_host === '' || $db_database === '' || $db_username === '') {
        echo json_encode(['success' => false, 'message' => '❌ All database fields are required', 'show_db_form' => true]);
        exit;
    }

    file_put_contents($dbConfigFile, json_encode([
        'host' => $db_host,
        'database' => $db_database,
        'username' => $db_username,
        'password' => $db_password
    ], JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'message' => '✔ Database configuration saved', 'show_db_form' => false, 'next' => 'env']);
    exit;
}

/*
|--------------------------------------------------------------------------
| Steps
|--------------------------------------------------------------------------
*/
try {

    switch ($step) {

        /*
        | CHECK SYSTEM
        */
        case 'check':
            @unlink($envFile);
            @unlink($dbConfigFile);
            @unlink($migrationDoneFile);
            @unlink($seedDoneFile);

            $msg = "<strong>Checking system requirements...</strong><br>";
            $ok = true;

            // PHP version
            $version = trim(shell_exec("$phpBin -v"));
            preg_match('/PHP\s+([0-9\.]+)/', $version, $matches);
            $phpVer = $matches[1] ?? 'unknown';

            if (version_compare($phpVer, '8.2.0', '>=')) {
                $msg .= "✔ PHP $phpVer OK (8.2.x)<br>";
            } else {
                $msg .= "❌ PHP 8.2+ required, found $phpVer<br>";
                $ok = false;
            }

            // Extensions
            $exts = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'curl', 'gd', 'zip'];
            foreach ($exts as $e) {
                if (!extension_loaded($e)) {
                    $msg .= "❌ Missing extension: $e<br>";
                    $ok = false;
                }
            }

            // Composer version
            $composerVersion = trim(shell_exec("$phpBin $composerBin --version 2>&1"));
            if (strpos($composerVersion, '2.7.8') !== false) {
                $msg .= "✔ Composer $composerVersion OK<br>";
            } else {
                $msg .= "❌ Composer 2.7 required, found $composerVersion<br>";
                $ok = false;
            }

            if (!$ok) fail($msg . "<br>Fix errors and reload");

            echo json_encode(['success' => true, 'message' => $msg . "✔ All requirements OK", 'next' => 'composer']);
            exit;

            /*
        | COMPOSER INSTALL
        */
        case 'composer':
            if (!is_writable($basePath)) {
                fail("Permission issue. Run:<br><pre>sudo chown -R \$USER:www-data $basePath\nsudo chmod -R 775 $basePath</pre>");
            }

            // Use PHP 8.2 + Composer 2.7 with HOME and COMPOSER_HOME
            $cmd = "cd \"$basePath\" && COMPOSER_HOME=/tmp HOME=/tmp $phpBin $composerBin install --no-interaction --prefer-dist 2>&1";
            $output = shell_exec($cmd);

            if (!vendorExists($basePath)) {
                fail("Composer failed:<br><pre>$output</pre>");
            }

            echo json_encode(['success' => true, 'message' => "✔ Dependencies installed", 'next' => 'db_config']);
            exit;


            /*
        | DB CONFIG FORM
        */
        case 'db_config':
            echo json_encode(['message' => 'Please enter database info', 'show_db_form' => true, 'next' => 'env']);
            exit;

            /*
        | ENV SETUP
        */
        case 'env':
            if (!file_exists($dbConfigFile)) fail("DB config missing");
            $config = json_decode(file_get_contents($dbConfigFile), true);

            if (!file_exists($basePath . '/.env.example')) fail(".env.example not found");
            if (!file_exists($envFile)) copy($basePath . '/.env.example', $envFile);
            if (!is_writable($envFile)) fail(".env not writable. Run: sudo chown \$USER:www-data $envFile && sudo chmod 664 $envFile");

            $env = file_get_contents($envFile);
            $env = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $config['host'], $env);
            $env = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $config['database'], $env);
            $env = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $config['username'], $env);
            $env = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $config['password'] . '"', $env);
            file_put_contents($envFile, $env);

            echo json_encode(['message' => '.env created ✔', 'next' => 'key']);
            exit;

            /*
        | APP KEY
        */
        case 'key':
            blockIfNoVendor($basePath);
            exec("$phpBin \"$basePath/artisan\" key:generate --force 2>&1", $out, $ret);
            if ($ret !== 0) fail("APP_KEY generation failed:\n" . implode("\n", $out));
            echo json_encode(['message' => '✔ APP_KEY generated', 'next' => 'migrate']);
            exit;

            /*
        | MIGRATE
        */
        case 'migrate':
            blockIfNoVendor($basePath);
            exec("$phpBin \"$basePath/artisan\" migrate --force 2>&1", $out, $ret);
            if ($ret !== 0) fail("Migration failed:\n" . implode("\n", $out));
            file_put_contents($migrationDoneFile, 'done');
            echo json_encode(['message' => '✔ Migrations completed', 'next' => 'seed']);
            exit;

            /*
        | SEED
        */
        case 'seed':
            blockIfNoVendor($basePath);
            exec("$phpBin \"$basePath/artisan\" db:seed --force 2>&1", $out, $ret);
            if ($ret !== 0) fail("Seeding failed:\n" . implode("\n", $out));
            file_put_contents($seedDoneFile, 'done');
            echo json_encode(['message' => '✔ Database seeded', 'next' => 'permissions']);
            exit;

            /*
        | PERMISSIONS
        */
        case 'permissions':
            foreach (['storage', 'bootstrap/cache'] as $dir) {
                if (!is_writable("$basePath/$dir")) fail("$dir is not writable");
            }
            echo json_encode(['message' => '✔ Permissions OK', 'next' => 'finish']);
            exit;

            /*
        | FINISH
        */
        case 'finish':
            file_put_contents($installedFlag, 'installed');
            $env = file_get_contents($envFile);
            if (!str_contains($env, 'APP_INSTALLED=true')) $env .= "\nAPP_INSTALLED=true\n";
            file_put_contents($envFile, $env);
            echo json_encode(['message' => "✔ Installation complete! <a href='/'>Open Application</a>", 'next' => null]);
            exit;

        default:
            fail("Invalid step");
    }
} catch (Throwable $e) {
    fail($e->getMessage());
}

ob_end_flush();
