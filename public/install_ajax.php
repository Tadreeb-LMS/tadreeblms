<?php
ob_start();
header('Content-Type: application/json');

// --------------------
// Paths & files
// --------------------
$basePath = realpath(__DIR__ . '/..');
$envFile = $basePath . '/.env';
$dbConfigFile = __DIR__ . '/db_config.json';
$migrationDoneFile = $basePath . '/.migrations_done';
$seedDoneFile = $basePath . '/.seed_done';
$installedFlag = $basePath . '/installed';

// --------------------
// Already installed check
// --------------------
if (file_exists($installedFlag) && ($_GET['step'] ?? '') !== 'check') {
    echo json_encode([
        'success' => false,
        'message' => '❌ Application already installed'
    ]);
    exit;
}

// --------------------
// Helpers
// --------------------
function fail($msg) {
    echo json_encode(['message' => "❌ $msg", 'show_db_form' => false]);
    exit;
}

function nextStep($current) {
    $steps = ["check", "composer", "db_config", "env", "key", "migrate", "seed", "permissions", "finish"];
    $i = array_search($current, $steps);
    return $steps[$i + 1] ?? 'finish';
}

// --------------------
// Get step
// --------------------
$step = $_REQUEST['step'] ?? 'check';

// --------------------
// Handle DB save
// --------------------
if ($step === 'db_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $db_host = trim($_POST['db_host'] ?? '');
    $db_database = trim($_POST['db_database'] ?? '');
    $db_username = trim($_POST['db_username'] ?? '');
    $db_password = $_POST['db_password'] ?? '';

    if ($db_host === '' || $db_database === '' || $db_username === '') {
        echo json_encode([
            'success' => false,
            'message' => '❌ All database fields are required',
            'show_db_form' => true
        ]);
        exit;
    }

    $data = [
        'host' => $db_host,
        'database' => $db_database,
        'username' => $db_username,
        'password' => $db_password
    ];

    file_put_contents($dbConfigFile, json_encode($data, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'message' => '✔ Database configuration saved',
        'show_db_form' => false,
        'next' => 'env'
    ]);
    exit;
}

// --------------------
// Steps
// --------------------
try {
    switch ($step) {
        case 'check':
            $msg = "<strong>Checking system requirements...</strong><br>";
            $allGood = true;

            $resetFiles = [$envFile, $dbConfigFile, $migrationDoneFile, $seedDoneFile];
            foreach ($resetFiles as $file) if (file_exists($file)) @unlink($file);

            // PHP version
            if (version_compare(PHP_VERSION, '8.4.0', '>=')) {
                $msg .= "✔ PHP " . PHP_VERSION . " OK<br>";
            } else {
                $msg .= "❌ PHP 8.4+ required, current: " . PHP_VERSION . "<br>";
                $allGood = false;
            }

            // PHP extensions
            $required = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl', 'gd', 'zip'];
            foreach ($required as $ext) {
                if (!extension_loaded($ext)) {
                    $msg .= "❌ Missing extension: $ext<br>";
                    $allGood = false;
                }
            }

            // Composer check
            $composerOutput = shell_exec('composer --version 2>&1');
            if ($composerOutput && preg_match('/version\s+([0-9\.]+)/i', $composerOutput, $m)) {
                $composerVersion = $m[1];
                if (version_compare($composerVersion, '2.9.0', '>=')) {
                    $msg .= "✔ Composer $composerVersion OK<br>";
                } else {
                    $msg .= "❌ Composer >=2.9 required, current: $composerVersion<br>";
                    $allGood = false;
                }
            } else {
                $msg .= "❌ Composer not found<br>";
                $allGood = false;
            }

            if ($allGood) {
                echo json_encode(['message' => $msg . '✔ All requirements OK', 'show_db_form' => false, 'next' => nextStep($step)]);
            } else {
                echo json_encode(['success' => false, 'message' => $msg . "<br><strong>❌ Fix errors and refresh page</strong>"]);
            }
            exit;

        case 'composer':
            ini_set('max_execution_time', 3000);
            ini_set('memory_limit', '2G');

            $projectPath = $basePath;
            $composerCmd = 'composer';
            $cmd = "cd \"$projectPath\" && COMPOSER_HOME=/tmp HOME=/tmp $composerCmd update --no-interaction --prefer-dist --ignore-platform-reqs 2>&1";
            $output = shell_exec($cmd) ?: "Composer cannot run (shell_exec disabled or permission issue)\n";
            
            echo json_encode([
                'message' => "Running Composer...\n$output\n✔ Composer completed",
                'show_db_form' => false,
                'next' => nextStep($step)
            ]);
            exit;

        case 'db_config':
            echo json_encode([
                'message' => file_exists($dbConfigFile) ? 'Database config already exists ✔' : 'Please enter database info',
                'show_db_form' => !file_exists($dbConfigFile),
                'next' => nextStep($step)
            ]);
            exit;

        case 'env':
            if (!file_exists($dbConfigFile)) fail("DB config missing");
            $config = json_decode(file_get_contents($dbConfigFile), true);
            if (!$config) fail("DB config invalid");

            $envExample = $basePath . '/.env.example';
            if (!file_exists($envExample)) fail(".env.example not found");

            $env = file_get_contents($envExample);
            $env = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $config['host'], $env);
            $env = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $config['database'], $env);
            $env = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $config['username'], $env);
            $env = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $config['password'] . '"', $env);
            file_put_contents($envFile, $env);

            echo json_encode(['message' => '.env created ✔', 'show_db_form' => false, 'next' => nextStep($step)]);
            exit;

        case 'key':
            exec("php $basePath/artisan key:generate --force 2>&1", $output, $ret);
            if ($ret !== 0) fail("APP_KEY generation failed:\n" . implode("\n", $output));
            echo json_encode(['message' => "✔ APP_KEY generated\n" . implode("\n", $output), 'show_db_form' => false, 'next' => nextStep($step)]);
            exit;

        case 'migrate':
            exec("php $basePath/artisan migrate --force 2>&1", $output, $ret);
            if ($ret !== 0) fail("Migration failed:\n" . implode("\n", $output));
            file_put_contents($migrationDoneFile, "done");
            echo json_encode(['message' => "✔ Migrations completed\n" . implode("\n", $output), 'show_db_form' => false, 'next' => nextStep($step)]);
            exit;

        case 'seed':
            exec("php $basePath/artisan db:seed --force 2>&1", $output, $ret);
            if ($ret !== 0) fail("Seeding failed:\n" . implode("\n", $output));
            file_put_contents($seedDoneFile, "done");
            echo json_encode(['message' => "✔ Database seeded\n" . implode("\n", $output), 'show_db_form' => false, 'next' => nextStep($step)]);
            exit;

        case 'permissions':
            $paths = [$basePath . '/storage', $basePath . '/bootstrap/cache'];
            $errors = [];
            foreach ($paths as $path) if (!is_writable($path)) $errors[] = "$path is not writable";
            if ($errors) fail("Permission errors:<br>" . implode("<br>", $errors));
            echo json_encode(['success' => true, 'message' => '✔ Folder permissions OK', 'show_db_form' => false, 'next' => nextStep($step)]);
            exit;

        case 'finish':
            file_put_contents($installedFlag, "installed");
            $env = file_get_contents($envFile);
            $env = str_contains($env, 'APP_INSTALLED=') ? preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $env) : $env . "\nAPP_INSTALLED=true\n";
            file_put_contents($envFile, $env);
            echo json_encode(['message' => "✔ Installation complete! <a href='/'>Open Application</a>", 'show_db_form' => false, 'next' => null]);
            exit;

        default:
            fail("Invalid step: $step");
    }
} catch (Exception $e) {
    fail($e->getMessage());
}

ob_end_flush();
