<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);

$base = realpath(__DIR__ . "/..");     // project root
$dbConfig = __DIR__ . "/db_config.json";
$envFile = $base . "/.env";
$envExample = $base . "/.env.example";

// -----------------------------
// Helper Response
// -----------------------------
function send($arr)
{
    echo json_encode($arr);
    exit;
}

// -----------------------------
// Helper: Run shell commands
// -----------------------------
function run_cmd($cmd)
{
    $output = shell_exec($cmd . " 2>&1");
    if (!$output) $output = "(no output)";
    return htmlspecialchars($output);
}

// -----------------------------
// Detect Composer
// -----------------------------
function find_composer()
{
    $paths = [
        "composer",
        "/usr/local/bin/composer",
        "/usr/bin/composer",
        "php composer.phar",
        "composer.bat",
        "C:\\ProgramData\\ComposerSetup\\bin\\composer.bat"
    ];

    foreach ($paths as $p) {
        $v = @shell_exec("$p --version 2>&1");
        if ($v && stripos($v, "Composer") !== false) return $p;
    }
    return null;
}

// -----------------------------
// Read step
// -----------------------------
$step = $_POST["step"] ?? "check";

// -------------------------------------------------------------
// STEP: SAVE DATABASE DETAILS
// -------------------------------------------------------------
if ($step === "db_save") {
    $data = [
        "host" => $_POST["db_host"] ?? "127.0.0.1",
        "name" => $_POST["db_name"] ?? "",
        "user" => $_POST["db_user"] ?? "",
        "pass" => $_POST["db_pass"] ?? ""
    ];

    file_put_contents($dbConfig, json_encode($data, JSON_PRETTY_PRINT));

    send([
        "success" => true,
        "output"  => "✔ Database settings saved<br>",
        "percent" => 30,
        "next"    => "env",
        "show_db_form" => false
    ]);
}

// ===========================================================================
// MAIN INSTALLER STEPS
// ===========================================================================
switch ($step) {

    // -------------------------------------------------------------
    // 1) CHECK SYSTEM
    // -------------------------------------------------------------
    case "check":

        $out = "";

        $out .= "✔ PHP version: " . phpversion() . "<br>";

        $required = ["pdo_mysql", "openssl", "mbstring", "tokenizer", "xml", "ctype", "json", "bcmath", "fileinfo", "curl", "zip"];
        foreach ($required as $e) {
            $out .= extension_loaded($e)
                ? "✔ $e<br>"
                : "❌ Missing: $e<br>";
        }

        $composer = find_composer();
        if ($composer) {
            $out .= "✔ Composer found: $composer<br>";
        } else {
            $out .= "❌ Composer not found<br>";
        }

        send([
            "success" => true,
            "output"  => $out,
            "percent" => 10,
            "next"    => "composer",
            "show_db_form" => false
        ]);
        break;

    // -------------------------------------------------------------
    // 2) COMPOSER INSTALL
    // -------------------------------------------------------------
    case 'composer':

        try {
            out("Running Composer operation...");
            ini_set('max_execution_time', 3000);
            ini_set('memory_limit', '2G');
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

            // --- Detect Composer path dynamically ---
            $composerCmd = null;
            $pathsToTry = $isWindows
                ? ['composer', 'composer.bat', 'composer.phar']
                : ['composer', '/usr/local/bin/composer', '/usr/bin/composer'];

            foreach ($pathsToTry as $path) {
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

            // --- Ensure vendor/ is ready ---
            $vendorPath = $projectPath . '/vendor';
            if (file_exists($vendorPath) && !is_dir($vendorPath)) {
                unlink($vendorPath); // remove file if vendor exists as file
            }
            if (!is_dir($vendorPath)) mkdir($vendorPath, 0775, true);

            // --- Ensure /tmp/composer exists for HOME ---
            if (!$isWindows && !is_dir('/tmp/composer')) mkdir('/tmp/composer', 0777, true);

            // --- Prepare command ---
            if ($isWindows) {
                $cmd = "cd /d \"$projectPath\" && $composerCmd install --no-interaction --prefer-dist";
            } else {
                $cmd = "HOME=/tmp COMPOSER_HOME=/tmp cd \"$projectPath\" && $composerCmd install --no-interaction --prefer-dist";
            }

            out("Executing:<br><pre>$cmd</pre>");

            $output = shell_exec($cmd);

            if ($output === null) {
                fail("shell_exec returned NULL — composer cannot run (disabled or permission).");
            }

            out("<pre>$output</pre>");

            // --- Check success ---
            if (
                stripos($output, "Generating optimized autoload files") !== false ||
                stripos($output, "Nothing to install") !== false ||
                stripos($output, "Package operations") !== false
            ) {
                out("✔ Composer operation completed successfully.");
            } else {
                fail("Composer failed. Output:<br><pre>$output</pre>");
            }
        } catch (Exception $e) {
            fail("Composer error: " . $e->getMessage());
        }
        break;


    // -------------------------------------------------------------
    // 3) CREATE .env FILE
    // -------------------------------------------------------------
    case "env":

        if (!file_exists($dbConfig)) {
            send([
                "success" => false,
                "output"  => "❌ DB configuration missing",
                "percent" => 40,
                "next"    => "db_config",
                "show_db_form" => true
            ]);
        }

        $db = json_decode(file_get_contents($dbConfig), true);

        $env = file_exists($envExample)
            ? file_get_contents($envExample)
            : "";

        $env .= "\nDB_HOST={$db['host']}";
        $env .= "\nDB_DATABASE={$db['name']}";
        $env .= "\nDB_USERNAME={$db['user']}";
        $env .= "\nDB_PASSWORD={$db['pass']}\n";

        file_put_contents($envFile, $env);

        send([
            "success" => true,
            "output"  => "✔ .env created<br>",
            "percent" => 50,
            "next"    => "key"
        ]);
        break;

    // -------------------------------------------------------------
    // 4) GENERATE APP KEY
    // -------------------------------------------------------------
    case "key":

        $out = run_cmd("cd $base && php artisan key:generate --force");

        send([
            "success" => true,
            "output"  => nl2br($out),
            "percent" => 60,
            "next"    => "migrate"
        ]);
        break;

    // -------------------------------------------------------------
    // 5) RUN MIGRATIONS
    // -------------------------------------------------------------
    case "migrate":

        $out = run_cmd("cd $base && php artisan migrate --force");

        send([
            "success" => true,
            "output"  => nl2br($out),
            "percent" => 75,
            "next"    => "seed"
        ]);
        break;

    // -------------------------------------------------------------
    // 6) SEED DATABASE
    // -------------------------------------------------------------
    case "seed":

        $out = run_cmd("cd $base && php artisan db:seed --force");

        send([
            "success" => true,
            "output"  => nl2br($out),
            "percent" => 85,
            "next"    => "permissions"
        ]);
        break;

    // -------------------------------------------------------------
    // 7) SET PERMISSIONS
    // -------------------------------------------------------------
    case "permissions":

        @chmod($base . "/storage", 0777);
        @chmod($base . "/bootstrap/cache", 0777);

        send([
            "success" => true,
            "output"  => "✔ Permissions fixed<br>",
            "percent" => 95,
            "next"    => "finish"
        ]);
        break;

    // -------------------------------------------------------------
    // 8) FINISH
    // -------------------------------------------------------------
    case "finish":

        file_put_contents($base . "/installed", "installed");

        send([
            "success" => true,
            "output"  => "🎉 Installation complete!",
            "percent" => 100,
            "next"    => "finish"
        ]);
        break;

    // -------------------------------------------------------------
    // Unknown step
    // -------------------------------------------------------------
    default:
        send([
            "success" => false,
            "output"  => "Unknown step: $step",
            "next"    => "check"
        ]);
        break;
}
