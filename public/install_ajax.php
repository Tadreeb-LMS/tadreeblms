<?php
header('Content-Type: application/json');
error_reporting(0); // hide warnings/notices

$output = "";
$status = "ok";
$show_db_form = false;
$next = "";

// Paths & Files
$basePath = realpath(__DIR__ . '/..');
$envFile = $basePath . '/.env';
$dbConfigFile = __DIR__ . '/db_config.json';
$migrationDoneFile = $basePath . '/.migrations_done';
$seedDoneFile = $basePath . '/.seed_done';
$installedFlag = $basePath . '/installed';

$steps = ["check", "composer", "db_config", "env", "key", "migrate", "seed", "permissions", "finish"];

$step = $_POST['step'] ?? $_GET['step'] ?? 'check';

ob_start();

try {

    switch ($step) {

        case "check":
            $allGood = true;
            $currentPhp = phpversion();
            echo "<strong>System Check</strong><br>";
            if (version_compare($currentPhp, "8.0", ">=")) {
                echo "✔ PHP $currentPhp OK<br>";
            } else {
                echo "❌ PHP 8.0+ required, found $currentPhp<br>";
                $allGood = false;
            }

            $requiredExtensions = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl', 'gd', 'zip'];
            foreach ($requiredExtensions as $ext) {
                if (extension_loaded($ext)) {
                    echo "✔ $ext enabled<br>";
                } else {
                    echo "❌ Missing extension: $ext<br>";
                    $allGood = false;
                }
            }

            // Composer check
            $composerCmd = null;
            $paths = ['/usr/local/bin/composer', '/usr/bin/composer', 'composer'];
            foreach ($paths as $path) {
                $test = @shell_exec("$path --version 2>&1");
                if ($test && stripos($test, "Composer") !== false) {
                    $composerCmd = $path;
                    break;
                }
            }
            if ($composerCmd) {
                echo "✔ Composer found: $composerCmd<br>";
            } else {
                echo "❌ Composer not found<br>";
                $allGood = false;
            }

            $next = $allGood ? "composer" : "check";
            break;

        case "composer":
            ini_set('max_execution_time', 3000);
            ini_set('memory_limit', '2G');

            $projectPath = $basePath;
            $composerCmd = '/usr/local/bin/composer';
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === "WIN";

            if (!$isWindows) {
                $cmd = "cd \"$projectPath\" && COMPOSER_HOME=/tmp HOME=/tmp $composerCmd update --no-interaction --prefer-dist --ignore-platform-reqs 2>&1";
            } else {
                $cmd = "cd /d \"$projectPath\" && $composerCmd update --no-interaction --prefer-dist --ignore-platform-reqs 2>&1";
            }

            echo "Running Composer...<br>";
            $res = shell_exec($cmd);
            if ($res === null) {
                throw new Exception("Composer cannot run (shell_exec disabled or permission issue)");
            }
            echo "<pre>$res</pre>";
            echo "✔ Composer completed<br>";

            $next = "db_config";
            break;

        case "db_config":
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db_host'])) {
                // Save DB config
                file_put_contents($dbConfigFile, json_encode([
                    'host' => $_POST['db_host'] ?? '',
                    'database' => $_POST['db_name'] ?? '',
                    'username' => $_POST['db_user'] ?? '',
                    'password' => $_POST['db_pass'] ?? ''
                ]));
                echo "✔ DB config saved<br>";
                $next = "env";
                $show_db_form = false;
            } else {
                // Ask JS to show DB form
                echo "Please enter database information<br>";
                $show_db_form = true;
                $next = "db_config"; // keep the step waiting for user input
            }
            break;

        case "env":
            $config = json_decode(file_get_contents($dbConfigFile), true);
            $envExample = file_exists($basePath . '/.env.example') ? file_get_contents($basePath . '/.env.example') : "";
            $env = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $config['host'], $envExample);
            $env = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $config['database'], $env);
            $env = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $config['username'], $env);
            $env = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $config['password'] . '"', $env);
            file_put_contents($envFile, $env);
            echo "✔ .env file created<br>";
            $next = "key";
            break;

        case "key":
            system("php \"$basePath/artisan\" key:generate --force", $ret);
            if ($ret !== 0) throw new Exception("APP_KEY generation failed");
            echo "✔ APP_KEY generated<br>";
            $next = "migrate";
            break;

        case "migrate":
            system("php \"$basePath/artisan\" migrate --force", $ret);
            if ($ret !== 0) throw new Exception("Migration failed");
            file_put_contents($migrationDoneFile, "done");
            echo "✔ Migrations completed<br>";
            $next = "seed";
            break;

        case "seed":
            system("php \"$basePath/artisan\" db:seed --force", $ret);
            if ($ret !== 0) throw new Exception("Seeding failed");
            file_put_contents($seedDoneFile, "done");
            echo "✔ Database seeded<br>";
            $next = "permissions";
            break;

        case "permissions":
            // For Linux, you could chmod storage/cache if needed
            echo "✔ Permissions set (Windows ignored)<br>";
            $next = "finish";
            break;

        case "finish":
            file_put_contents($installedFlag, "installed");
            $env = file_get_contents($envFile);
            if (str_contains($env, 'APP_INSTALLED=')) {
                $env = preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $env);
            } else {
                $env .= "\nAPP_INSTALLED=true\n";
            }
            file_put_contents($envFile, $env);
            echo "✔ Installation complete<br>";
            $next = "";
            break;

        default:
            throw new Exception("Invalid step: $step");
    }
} catch (Exception $e) {
    $status = "error";
    echo "❌ " . $e->getMessage();
}

$output .= ob_get_clean();

// Calculate progress
$percent = round((array_search($step, $steps) + 1) / count($steps) * 100);

// Return JSON
echo json_encode([
    'status' => $status,
    'message' => $output,
    'show_db_form' => $show_db_form,
    'next' => $next,
    'percent' => $percent
]);
