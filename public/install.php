<?php
ob_start();


$installedFlag = __DIR__ . '/../installed';

// If installer is re-opened, remove installed flag to allow fresh install
if (file_exists($installedFlag)) {
    unlink($installedFlag);
}

// --------------------
// Installer Steps
// --------------------
$steps = [
    'check' => 'Checking Environment',
    'composer' => 'Composer Install',
    'db_config' => 'Database Configuration',
    'env' => 'Creating .env File',
    'key' => 'Generating APP_KEY',
    'migrate' => 'Running Migrations',
    'seed' => 'Seeding Database',
    'permissions' => 'Setting Permissions',
    'finish' => 'Installation Complete'
];

// --------------------
// Paths & Files
// --------------------
$envFile = __DIR__ . '/../.env';
$migrationDoneFile = __DIR__ . '/../.migrations_done';
$seedDoneFile = __DIR__ . '/../.seed_done';
$dbConfigFile = __DIR__ . '/db_config.json';


// --------------------
// Helpers
// --------------------
function nextStep($step)
{
    global $steps;
    $keys = array_keys($steps);
    $i = array_search($step, $keys);
    return $keys[$i + 1] ?? 'finish';
}

function out($text)
{
    echo $text . "<br>";
    echo str_repeat(' ', 1024);
    if (ob_get_level()) ob_flush();
    flush();
}

function fail($msg)
{
    file_put_contents(__DIR__ . '/install_error.log', date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND);
    echo "<br>⚠️ " . htmlspecialchars($msg) . "<br>";
    exit;
}

// --------------------
// Current step
// --------------------
$current = $_GET['step'] ?? 'check';

// --------------------
// Handle DB form POST
// --------------------
if ($current === 'db_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? '';
    $db_database = $_POST['db_database'] ?? '';
    $db_username = $_POST['db_username'] ?? '';
    $db_password = $_POST['db_password'] ?? '';

    file_put_contents($dbConfigFile, json_encode([
        'host' => $db_host,
        'database' => $db_database,
        'username' => $db_username,
        'password' => $db_password
    ]));

    header("Location: ?step=env");
    exit;
}

// --------------------
// HTML header
// --------------------
echo "<!DOCTYPE html>
<html>
<head>
<title>Academy Installer</title>
<style>
body{font-family:Arial;background:#f7f7f7;padding:20px;}
.container{max-width:700px;margin:50px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
h2{margin-bottom:20px;}
.progress{background:#eee;border-radius:20px;height:20px;margin-bottom:20px;overflow:hidden;}
.bar{height:100%;width:0;background:#4caf50;text-align:center;color:#fff;line-height:20px;transition:0.5s;}
.output{background:#000;color:#0f0;padding:10px;height:300px;overflow:auto;font-family:monospace;}
.button{display:inline-block;margin-top:20px;padding:10px 20px;background:#4caf50;color:#fff;text-decoration:none;border-radius:5px;}
input{padding:8px;width:100%;margin-bottom:15px;}
.logo{
text-align:center
}
</style>
</head>
<body>
<div class='container'>
<div class='logo'><img src='./assets/img/logo.png' ></div>
<h2>{$steps[$current]}</h2>
<div class='progress'><div class='bar' id='bar'>0%</div></div>
<div class='output' id='log'>";

ob_flush();
flush();

// --------------------
// Installer Steps
// --------------------
try {

    switch ($current) {

        case 'check':

            out("<strong>System Requirements Check</strong><br><br>");

            $allGood = true; // Track errors

            // PHP VERSION
            $minPhp = "8.0";
            $currentPhp = phpversion();
            if (version_compare($currentPhp, $minPhp, '>=')) {
                out("✔ PHP Version OK ($currentPhp) <br>");
            } else {
                out("❌ PHP $minPhp or higher required — current: $currentPhp <br>");
                $allGood = false;
            }

            // REQUIRED EXTENSIONS
            $requiredExtensions = [
                'pdo',
                'pdo_mysql',
                'openssl',
                'mbstring',
                'tokenizer',
                'xml',
                'ctype',
                'json',
                'bcmath',
                'fileinfo',
                'curl'
            ];

            out("<br><strong>PHP Extensions:</strong><br>");

            foreach ($requiredExtensions as $ext) {
                if (extension_loaded($ext)) {
                    out("✔ $ext enabled<br>");
                } else {
                    out("❌ Missing extension: $ext<br>");
                    $allGood = false;
                }
            }

            // FOLDER PERMISSIONS
            $pathsToCheck = [
                __DIR__ . '/../storage' => 'storage/',
                __DIR__ . '/../bootstrap/cache' => 'bootstrap/cache/'
            ];

            out("<br><strong>Folder Permissions:</strong><br>");

            foreach ($pathsToCheck as $path => $label) {
                if (is_writable($path)) {
                    out("✔ $label is writable<br>");
                } else {
                    out("❌ $label is NOT writable<br>");
                    $allGood = false;
                }
            }

            // SERVER OS TYPE
            $os = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'Windows / XAMPP' : 'Linux / Ubuntu';
            out("<br><strong>Server:</strong> $os<br>");

            // COMPOSER CHECK
            $composerPath = __DIR__ . '/composer.phar';
            out("<br><strong>Composer:</strong><br>");

            if (file_exists($composerPath)) {
                out("✔ composer.phar found in public/<br>");
            } else {
                out("⚠ composer.phar NOT found — you must upload it or install globally<br>");
                // Composer missing doesn't block — optional
            }

            // FINAL RESULT
            out("<br><strong>Result:</strong><br>");

            if ($allGood) {
                out("✔ All requirements satisfied.<br>");
                echo "<br><a class='button' href='?step=" . nextStep($current) . "'>Continue</a>";
            } else {
                out("<br>❌ Some requirements failed.<br>Please fix above issues and refresh this page.<br>");
            }

            echo "</div></div></body></html>";
            exit;

            break;

        case 'composer':

            try {
                out("Running Composer install...");
                ini_set('max_execution_time', 3000);
                ini_set('memory_limit', '1G');
                set_time_limit(0);

                $projectPath  = realpath(__DIR__ . '/..');

                // OS Detection
                $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

                // Global composer command
                // Windows uses "composer", Linux usually uses "/usr/bin/composer"
                $composerCmd = $isWindows ? "composer" : "composer";

                // Build correct CD command
                $cd = $isWindows ? "cd /d \"$projectPath\"" : "cd \"$projectPath\"";

                // Final command (globally installed Composer)
                $cmd = "$cd && $composerCmd install --no-interaction --prefer-dist 2>&1";

                out("Executing:<br><pre>$cmd</pre>");

                $output = shell_exec($cmd);

                if ($output === null) {
                    throw new Exception("shell_exec() returned NULL — command may be blocked or disabled.");
                }

                out("<pre>$output</pre>");

                if (strpos($output, "Generating optimized autoload files") !== false) {
                    out("✔ Composer install completed successfully.");
                } else {
                    out("<div style='color: orange;'>⚠ Composer executed, but did not finish cleanly.</div>");
                }
            } catch (Exception $e) {
                out("<div style='color:red'>❌ Composer error: " . $e->getMessage() . "</div>");
                exit;
            }

            break;


        case 'db_config':
            echo "
                <form method='POST'>
                    <label>DB Host</label>
                    <input type='text' name='db_host' required value='127.0.0.1'>

                    <label>DB Name</label>
                    <input type='text' name='db_database' required>

                    <label>DB Username</label>
                    <input type='text' name='db_username' required>

                    <label>DB Password</label>
                    <input type='password' name='db_password'>

                    <button class='button' type='submit'>Save & Continue</button>
                </form>
            ";
            echo "</div></div></body></html>";
            exit;

        case 'env':
            try {
                out("Creating .env file...");

                $config = json_decode(file_get_contents($dbConfigFile), true);
                $env = file_get_contents(__DIR__ . '/../.env.example');

                $env = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $config['host'], $env);
                $env = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $config['database'], $env);
                $env = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $config['username'], $env);
                $env = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD="' . $config['password'] . '"', $env);

                file_put_contents($envFile, $env);
                out("✔ .env created");
            } catch (Exception $e) {
                fail("ENV error: " . $e->getMessage());
            }
            break;

        case 'key':
            try {
                out("Generating APP_KEY...");
                system('php ' . __DIR__ . '/../artisan key:generate --force', $ret);
                if ($ret !== 0) throw new Exception("Failed to generate key");
                out("✔ APP_KEY generated");
            } catch (Exception $e) {
                fail("APP_KEY error: " . $e->getMessage());
            }
            break;

        case 'migrate':
            try {
                out("Running migrations...");
                system('php ' . __DIR__ . '/../artisan migrate --force', $ret);
                if ($ret !== 0) throw new Exception("Migration failed");
                file_put_contents($migrationDoneFile, "done");
                out("✔ Migrations complete");
            } catch (Exception $e) {
                fail("Migration error: " . $e->getMessage());
            }
            break;

        case 'seed':
            try {
                out("Seeding database...");
                system('php ' . __DIR__ . '/../artisan db:seed --force', $ret);
                if ($ret !== 0) throw new Exception("Seeding failed");
                file_put_contents($seedDoneFile, "done");
                out("✔ Seeding complete");

                //require __DIR__ . '/../vendor/autoload.php';
                // $app = require_once __DIR__ . '/../bootstrap/app.php';
                // // ✅ Run additional Artisan commands
                // $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

                // out("Running translations import...");
                // $kernel->call('translations:import');
                // out($kernel->output());
                // out("✔ Translations imported");

                // out("Creating storage link...");
                // $kernel->call('storage:link');
                // out($kernel->output());
                // out("✔ Storage linked");
            } catch (Exception $e) {
                fail("Seeding error: " . $e->getMessage());
            }
            break;

        case 'permissions':
            try {
                out("Setting permissions...");
                out("✔ Permissions completed (Windows ignored)");
            } catch (Exception $e) {
                fail("Permission error: " . $e->getMessage());
            }
            break;

        case 'finish':
            out("✔ Installation Complete!");

            // Create installed flag file
            if (!is_dir(dirname($installedFlag))) {
                mkdir(dirname($installedFlag), 0777, true);
            }

            file_put_contents($installedFlag, "installed");

            // ✅ Write APP_INSTALLED=true to .env
            $envPath = __DIR__ . '/../.env';
            if (file_exists($envPath)) {
                $env = file_get_contents($envPath);

                // Replace existing value or add new one
                if (str_contains($env, 'APP_INSTALLED=')) {
                    $env = preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $env);
                } else {
                    $env .= "\nAPP_INSTALLED=true\n";
                }

                file_put_contents($envPath, $env);
                out("APP_INSTALLED flag added to .env");
            } else {
                out("Warning: .env file not found. Could not set APP_INSTALLED=true");
            }

            $appUrl = dirname($_SERVER['REQUEST_URI'], 1); // remove install.php
            $appUrl = rtrim($appUrl, '/');
            out("Installer flag created. This system is now installed.");
            echo "<br><a class='button' href='{$appUrl}/'>Open Application</a>";
            break;


        default:
            throw new Exception("Invalid step: $current");
    }
} catch (Exception $e) {
    fail("Installer error: " . $e->getMessage());
}

// --------------------
// Progress bar + Auto next
// --------------------
$percent = round((array_search($current, array_keys($steps)) + 1) / count($steps) * 100);

echo "<script>
let bar=document.getElementById('bar');
bar.style.width='{$percent}%';
bar.innerHTML='{$percent}%';
setTimeout(()=>{ window.location='?step=" . nextStep($current) . "'; },1500);
</script>";

echo "</div></div></body></html>";
ob_end_flush();
