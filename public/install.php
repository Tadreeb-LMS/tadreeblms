<?php
$basePath = realpath(__DIR__ . '/..');
$installedFlag = $basePath . '/installed';

if (file_exists($installedFlag)) {
    header("Location: /"); // or public/
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Academy</title>
    <link rel="stylesheet" href="./assets/css/owl.carousel.css">
    <link rel="stylesheet" href="./assets/css/flaticon.css">
    <link rel="stylesheet" type="text/css" href="./assets/css/meanmenu.css">
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/video.min.css">
    <link rel="stylesheet" href="./assets/css/lightbox.css">
    <link rel="stylesheet" href="./assets/css/progess.css">
    <link rel="stylesheet" href="./assets/css/animate.min.css">

    <link rel="stylesheet" href="./css/frontend.css">
    <link rel="stylesheet" href="./assets/css/fontawesome-all.css">

    <link rel="stylesheet" href="./assets/css/responsive.css">

    <link rel="stylesheet" href="./assets/css/colors/switch.css">
    <link href="./assets/css/colors/color-2.css" rel="alternate stylesheet" type="text/css"
        title="color-2">
    <link href="./assets/css/colors/color-3.css" rel="alternate stylesheet" type="text/css"
        title="color-3">
    <link href="./assets/css/colors/color-4.css" rel="alternate stylesheet" type="text/css"
        title="color-4">
    <link href="./assets/css/colors/color-5.css" rel="alternate stylesheet" type="text/css"
        title="color-5">
    <link href="./assets/css/colors/color-6.css" rel="alternate stylesheet" type="text/css"
        title="color-6">
    <link href="./assets/css/colors/color-7.css" rel="alternate stylesheet" type="text/css"
        title="color-7">
    <link href="./assets/css/colors/color-8.css" rel="alternate stylesheet" type="text/css"
        title="color-8">
    <link href="./assets/css/colors/color-9.css" rel="alternate stylesheet" type="text/css"
        title="color-9">


    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

    <style>
        body {
            font-family: Arial;

            padding: 20px;

            background-color: #d0dbb9;
            position: relative;

        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);

        }

        h2 {
            margin-bottom: 20px;
        }

        .progress {
            background: #eee;
            border-radius: 20px;
            height: 20px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #2e7d32, #4caf50, #66bb6a);
            background-size: 200% 100%;
            text-align: center;
            color: #fff;
            line-height: 20px;
            transition: width 0.9s ease;
            animation: progressFlow 2.5s linear infinite;
        }

        .output {
            background: #0f172a;
            color: #e2e8f0;
            padding: 10px;
            height: 220px;
            overflow: auto;
            font-family: monospace;
            border-radius: 8px;
            border: 1px solid #1e293b;
            white-space: pre-wrap;
        }

        .line-ok {
            color: #86efac;
        }

        .line-warn {
            color: #fde68a;
        }

        .line-error {
            color: #fca5a5;
            font-weight: 700;
        }

        .line-step {
            color: #93c5fd;
        }

        @keyframes progressFlow {
            0% { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        .button {
            padding: 10px 20px;
            background: #4caf50;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .hidden {
            display: none;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        .dropdown-toggle::after {
            display: none !important;

        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light">

        <div class="navbar-header float-left">
            <a class="navbar-brand text-uppercase" href="#">
                <img src="./assets/img/logo.png" alt="logo" class="logoimg">
            </a>
        </div>

        <button class="navbar-toggler ham-top-space" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="nav-item px-3 ">
            <a class="nav-link dropdown-toggle nav-link" href="#" role="button"
                aria-haspopup="true" aria-expanded="false">
                <span class="d-md-down-none">Language (EN)</span>
            </a>

            <!-- <div class="dropdown-menu dropdown-menu-right add-dropmenu-position" aria-labelledby="navbarDropdownLanguageLink">





                <small><a href="http://44.251.231.158/lang/ar" class="dropdown-item">Arabic</a></small>
            </div> -->
        </div>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <ul class="navbar-nav ul-li ml-auto sm-rl-space">

                <!-- <li class="px-lg-4 hamburger-top-space sm-tb-space">
                    <form action="/search" method="get" id="searchform">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text searchcourse" id="basic-addon1"><i
                                        class="bi bi-search" onclick="submit()"></i></span>
                            </div>
                            <input type="text" class="form-control" name="q"
                                placeholder="Search for course" aria-label="Username" required
                                aria-describedby="basic-addon1">
                        </div>
                    </form>
                </li> -->


                <li class="sm-tb-space">
                    <div class="log-in">
                        <a id="openLoginModal" data-target="#myModal" href="#">Installation</a>


                    </div>
                </li>
                <!-- <li class="sm-tb-space">
                    <div class="log-in">
                        <a id="openRegisterModal" data-target="#myRegisterModal"
                            href="#">SignUp</a>


                    </div>
                </li> -->
                <li class="sm-tb-space">
                    <div class="cart-search float-lg-right ul-li">
                        <ul class="lock-icon">
                            <li>
                                <a href="#"><i class="fas fa-shopping-bag"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


            </ul>

        </div>
    </nav>
    <div class="container">
        <h2>Installer</h2>

        <div class="progress">
            <div id="bar" class="bar">0%</div>
        </div>

        <div id="log" class="output"></div>

        <!-- DB Form -->
        <div id="dbform" class="hidden">
            <h3>Database Settings</h3>
                <label>App URL (no trailing slash)</label>
                <input type="text" id="app_url" placeholder="http://yourdomain.com" value="http://localhost">
            <input type="text" id="db_host" placeholder="DB Host" value="127.0.0.1">
            <input type="text" id="db_database" placeholder="Database Name">
            <input type="text" id="db_username" placeholder="DB Username">
            <input type="password" id="db_password" placeholder="DB Password">
            <button onclick="saveDB()" class="button">Save & Continue</button>
        </div>

        <button id="startBtn" class="button" onclick="runStep('check')">Start Installation</button>
    </div>

    <script>
    const steps = ["check","composer","db_config","env","key","migrate","seed","permissions","finish"];
    const stepTitles = {
        check: "Checking requirements",
        composer: "Installing dependencies",
        db_config: "Saving database configuration",
        env: "Writing environment settings",
        key: "Generating application key",
        migrate: "Running migrations",
        seed: "Seeding database",
        permissions: "Checking permissions",
        finish: "Finalizing installation"
    };
    const MAX_STEP_RETRIES = 5;
    let currentIndex = 0;

    function runStep(step, attempt = 0) {

        document.getElementById("startBtn").classList.add("hidden");

        appendStepLine("[STEP] " + (stepTitles[step] || step));

        fetch("install_ajax.php?step=" + step)
            .then(res => {
                return res.text().then(body => {
                    let parsed = null;
                    try {
                        parsed = JSON.parse(body);
                    } catch (e) {
                        parsed = null;
                    }

                    if (!res.ok) {
                        throw {
                            type: "http",
                            status: res.status,
                            body: body
                        };
                    }

                    if (!parsed) {
                        throw {
                            type: "invalid_json",
                            status: res.status,
                            body: body
                        };
                    }

                    return parsed;
                });
            })
            .then(res => {

                appendLog(res.message || '');

                // ❌ HARD STOP
                if (res.success === false) {
                    appendError(step, "Installation stopped", res.message || "No additional details provided.");
                    return;
                }

                // Progress bar
                currentIndex = steps.indexOf(step);
                updateBar(Math.round((currentIndex + 1) / steps.length * 100));

                // 🔴 SHOW DB FORM AND STOP FLOW
                if (res.show_db_form) {
                    document.getElementById("dbform").classList.remove("hidden");
                    return;
                }

                document.getElementById("dbform").classList.add("hidden");

                // Continue only if backend says next
                if (res.next) {
                    // Writing .env can briefly restart local dev server (artisan serve),
                    // so give the next request a bit more time after env step.
                    const delay = step === "env" ? 2000 : 500;
                    setTimeout(() => runStep(res.next, 0), delay);
                } else if (res.redirect_url) {
                    appendLog("↪ Redirecting to application in 2 seconds...");
                    setTimeout(() => {
                        window.location.href = res.redirect_url;
                    }, 2000);
                }
            })
            .catch(err => {
                if (attempt < MAX_STEP_RETRIES) {
                    const retryDelay = Math.min(5000, 1000 * (attempt + 1));
                    appendWarn("Temporary connection issue on step '" + step + "'. Retrying in " + (retryDelay / 1000) + "s...");
                    setTimeout(() => runStep(step, attempt + 1), retryDelay);
                    return;
                }

                const reason = (err && err.type)
                    ? (err.type === "http"
                        ? ("HTTP error " + err.status)
                        : "Invalid response from server")
                    : (err && err.message ? err.message : String(err));
                const details = (err && err.body)
                    ? String(err.body).slice(0, 1200)
                    : "The installer endpoint could not be reached or returned unexpected output.";

                appendError(step, reason, details);
            });
    }

    function saveDB() {
        const data = new URLSearchParams({
            db_host: document.getElementById("db_host").value,
            db_database: document.getElementById("db_database").value,
            db_username: document.getElementById("db_username").value,
            db_password: document.getElementById("db_password").value,
            app_url: document.getElementById("app_url").value
        });

        fetch("install_ajax.php?step=db_config", {
            method: "POST",
            body: data
        })
        .then(res => res.json())
        .then(res => {

            appendLog(res.message || '');

            if (res.success === false) {
                appendError("db_config", "Invalid database settings", res.message || "Please verify host, database, user and password.");
                document.getElementById("dbform").classList.remove("hidden");
                return;
            }

            document.getElementById("dbform").classList.add("hidden");

            if (res.next) {
                runStep(res.next, 0);
            }
        })
        .catch(err => appendError("db_config", "Database config request failed", err && err.message ? err.message : String(err)));
    }

    function appendLog(msg) {
        const log = document.getElementById("log");
        log.innerHTML += msg + "\n";
        log.scrollTop = log.scrollHeight;
    }

    function appendStepLine(msg) {
        const log = document.getElementById("log");
        log.innerHTML += '<span class="line-step">' + escapeHtml(msg) + '</span>\n';
        log.scrollTop = log.scrollHeight;
    }

    function appendWarn(msg) {
        const log = document.getElementById("log");
        log.innerHTML += '<span class="line-warn">⚠ ' + escapeHtml(msg) + '</span>\n';
        log.scrollTop = log.scrollHeight;
    }

    function appendError(step, reason, details) {
        const log = document.getElementById("log");
        const title = '[ERROR] Step: ' + step + ' | Reason: ' + reason;
        const body = details ? ('Details: ' + details) : '';
        log.innerHTML += '<span class="line-error">' + escapeHtml(title) + '</span>\n';
        if (body) {
            log.innerHTML += '<span class="line-error">' + escapeHtml(body) + '</span>\n';
        }
        log.scrollTop = log.scrollHeight;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateBar(percent) {
        const bar = document.getElementById("bar");
        bar.style.width = percent + "%";
        bar.innerHTML = percent + "%";
    }
</script>

</body>

</html>