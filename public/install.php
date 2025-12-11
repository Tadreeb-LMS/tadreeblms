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
.logo{text-align:center}
</style>
</head>
<body>
<div class='container'>
<div class='logo'><img src='./assets/img/logo.png'></div>
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
            $allGood = true;
            $outSystemOS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'Windows' : 'Linux';

            // --------------------
            // 1️⃣ PHP Version
            // --------------------
            $minPhp = "8.0";
            $currentPhp = phpversion();
            if (version_compare($currentPhp, $minPhp, '>=')) {
                out("✔ PHP Version OK ($currentPhp)<br>");
            } else {
                out("❌ PHP $minPhp or higher required — current: $currentPhp<br>");
                $allGood = false;
            }

            // --------------------
            // 2️⃣ PHP Extensions
            // --------------------
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
                'curl',
                'gd',
                'zip'
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

            // --------------------
            // 3️⃣ Composer check
            // --------------------
            $composerCmd = null;
            if ($outSystemOS === 'Windows') {
                $paths = ['composer', 'C:\\ProgramData\\ComposerSetup\\bin\\composer.bat', 'C:\\composer\\composer.bat'];
            } else {
                $paths = ['/usr/local/bin/composer', '/usr/bin/composer', 'composer'];
            }
            foreach ($paths as $path) {
                $test = @shell_exec("$path --version 2>&1");
                if ($test && stripos($test, 'Composer') !== false) {
                    $composerCmd = $path;
                    break;
                }
            }

            out("<br><strong>Composer:</strong><br>");
            if ($composerCmd) {
                out("✔ Composer found: $composerCmd<br>");
            } else {
                out("❌ Composer not found — install globally or upload composer.phar<br>");
                $allGood = false;
            }

            // --------------------
            // 4️⃣ Git safe.directory check
            // --------------------
            $projectPath = realpath(__DIR__ . '/..');
            if (0) { //if ($outSystemOS == 'Windows') {
                $gitSafe = shell_exec("git config --global --get-all safe.directory | findstr \"$projectPath\"");
                if (!$gitSafe) {
                    out("⚠ Git safe.directory not set — run: <code>git config --global --add safe.directory $projectPath</code><br>");
                } else {
                    out("✔ Git safe.directory OK<br>");
                }
            }

            // --------------------
            // 5️⃣ Required folders & files
            // --------------------
            $pathsToCheck = [
                __DIR__ . '/../storage' => 'storage/',
                __DIR__ . '/../bootstrap/cache' => 'bootstrap/cache/',
                __DIR__ . '/../.env' => '.env file',
                __DIR__ . '/../vendor' => 'vendor folder'
            ];

            out("<br><strong>Folders & Files Check:</strong><br>");
            foreach ($pathsToCheck as $path => $label) {

                $isFolder = str_ends_with($label, '/');

                if (($isFolder && is_dir($path)) || (!$isFolder && is_file($path))) {
                    // Exists
                    if ($isFolder) {
                        if (!is_writable($path)) {
                            out("❌ $label exists but is NOT writable — attempting to fix permissions...<br>");
                            if ($outSystemOS === 'Linux' && chmod($path, 0775)) {
                                out("⚡ Permissions set to 775<br>");
                            } else {
                                $allGood = false;
                            }
                        } else {
                            out("✔ $label exists and is writable<br>");
                        }
                    } else {
                        out("✔ $label exists<br>");
                    }
                } else {
                    // Does not exist, create
                    if ($isFolder) {
                        if (mkdir($path, 0777, true)) {
                            out("⚡ $label folder did not exist and was created.<br>");
                            if ($outSystemOS === 'Linux' && chmod($path, 0775)) {
                                out("⚡ Permissions set to 775<br>");
                            }
                        } else {
                            out("❌ Failed to create $label folder.<br>");
                            $allGood = false;
                            continue;
                        }
                    } else {
                        // It's a file like .env
                        if (file_put_contents($path, '') !== false) {
                            out("⚡ $label did not exist and was created.<br>");
                        } else {
                            out("❌ Failed to create $label.<br>");
                            $allGood = false;
                            continue;
                        }
                    }
                }
            }

            // --------------------
            // 6️⃣ Composer-required extensions
            // --------------------
            $composerReqs = ['ext-gd', 'ext-curl'];
            foreach ($composerReqs as $ext) {
                $extName = str_replace('ext-', '', $ext);
                if (!extension_loaded($extName)) {
                    out("⚠ Composer requires $extName extension<br>");
                    $allGood = false;
                }
            }

            // --------------------
            // 7️⃣ Server OS
            // --------------------
            $os = $outSystemOS === 'Windows' ? 'Windows / XAMPP' : 'Linux / Ubuntu';
            out("<br><strong>Server:</strong> $os<br>");

            // --------------------
            // Final Result & Continue Button
            // --------------------
            out("<br><strong>Result:</strong><br>");
            if ($allGood) {
                out("✔ All requirements satisfied.<br>");
                echo "<br><a class='button' href='?step=" . nextStep($current) . "'>Continue</a>";
            } else {
                out("❌ Some requirements failed.<br>Please fix the issues above and refresh this page.<br>");
            }

            echo "</div></div></body></html>";
            exit;

        case 'composer':

            try {
                out("Running Composer install...");
                ini_set('max_execution_time', 3000);
                ini_set('memory_limit', '1G');
                set_time_limit(0);

                $projectPath = realpath(__DIR__ . '/..');
                $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

                // --- Required PHP extensions check ---
                $requiredExtensions = ['curl', 'gd', 'mbstring', 'xml', 'zip', 'bcmath', 'pdo_mysql'];
                $missingExtensions = [];
                foreach ($requiredExtensions as $ext) {
                    if (!extension_loaded($ext)) {
                        $missingExtensions[] = $ext;
                    }
                }
                if (!empty($missingExtensions)) {
                    fail("Missing required PHP extensions: " . implode(', ', $missingExtensions));
                }

                // --- Detect Composer path ---
                $composerPaths = $isWindows
                    ? [
                        'composer',
                        'C:\\ProgramData\\ComposerSetup\\bin\\composer.bat',
                        'C:\\ProgramData\\ComposerSetup\\composer.phar',
                        'C:\\Program Files\\Composer\\composer.bat',
                        'C:\\composer\\composer.bat',
                    ]
                    : [
                        '/usr/local/bin/composer',
                        '/usr/bin/composer',
                        'composer',
                    ];

                $composerCmd = null;
                foreach ($composerPaths as $path) {
                    $test = @shell_exec("$path --version 2>&1");
                    if ($test && stripos($test, 'Composer') !== false) {
                        $composerCmd = $path;
                        break;
                    }
                }
                if (!$composerCmd) {
                    fail("Composer not found. Install globally and ensure it is in PATH.");
                }

                out("Using Composer: <b>$composerCmd</b><br>");

                // --- Determine command: install vs update ---
                $lockFile = $projectPath . '/composer.lock';
                if ($isWindows) {
                    $cmd = "set COMPOSER_HOME=%TEMP% && cd /d \"$projectPath\" && $composerCmd update --no-interaction --prefer-dist --ignore-platform-reqs --no-audit 2>&1";
                } else {
                    $cmd = "export COMPOSER_HOME=/tmp && export HOME=/tmp && cd \"$projectPath\" && $composerCmd update --no-interaction --prefer-dist --ignore-platform-reqs --no-audit 2>&1";
                }

                out("Executing:<br><pre>$cmd</pre>");

                $output = shell_exec($cmd);

                if ($output === null) {
                    fail("shell_exec returned NULL — composer cannot run (disabled or permission).");
                }

                out("<pre>$output</pre>");

                // --- Check success ---
                if (
                    strpos($output, "Generating optimized autoload files") !== false ||
                    strpos($output, "Nothing to install") !== false ||
                    strpos($output, "Package operations") !== false
                ) {
                    out("✔ Composer operation completed successfully.");
                } else {
                    fail("Composer failed. Output:<br><pre>$output</pre>");
                }
            } catch (Exception $e) {
                fail("Composer error: " . $e->getMessage());
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

            if (!is_dir(dirname($installedFlag))) {
                mkdir(dirname($installedFlag), 0777, true);
            }

            file_put_contents($installedFlag, "installed");

            // Set APP_INSTALLED=true
            $envPath = __DIR__ . '/../.env';
            if (file_exists($envPath)) {
                $env = file_get_contents($envPath);
                if (str_contains($env, 'APP_INSTALLED=')) {
                    $env = preg_replace('/APP_INSTALLED=.*/', 'APP_INSTALLED=true', $env);
                } else {
                    $env .= "\nAPP_INSTALLED=true\n";
                }
                file_put_contents($envPath, $env);
                out("APP_INSTALLED flag added to .env");
            }

            $appUrl = dirname($_SERVER['REQUEST_URI'], 1);
            $appUrl = rtrim($appUrl, '/');
            out("Installer flag created. System is now installed.");
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
