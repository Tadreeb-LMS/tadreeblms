<?php exit('Installer disabled for local development'); ?>

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
            background: #4caf50;
            text-align: center;
            color: #fff;
            line-height: 20px;
            transition: 0.4s;
        }

        .output {
            background: #fff;
            color: #0f0;
            padding: 10px;
            height: 80px;
            overflow: auto;
            font-family: monospace;
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
    let currentIndex = 0;

    function runStep(step) {

        document.getElementById("startBtn").classList.add("hidden");

        fetch("install_ajax.php?step=" + step)
            .then(res => res.json())
            .then(res => {

                appendLog(res.message || '');

                // ❌ HARD STOP
                if (res.success === false) {
                    appendLog("❌ Installation stopped");
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
                    setTimeout(() => runStep(res.next), 500);
                }
            })
            .catch(err => appendLog("❌ AJAX error: " + err));
    }

    function saveDB() {
        const data = new URLSearchParams({
            db_host: document.getElementById("db_host").value,
            db_database: document.getElementById("db_database").value,
            db_username: document.getElementById("db_username").value,
            db_password: document.getElementById("db_password").value
        });

        fetch("install_ajax.php?step=db_config", {
            method: "POST",
            body: data
        })
        .then(res => res.json())
        .then(res => {

            appendLog(res.message || '');

            if (res.success === false) {
                document.getElementById("dbform").classList.remove("hidden");
                return;
            }

            document.getElementById("dbform").classList.add("hidden");

            if (res.next) {
                runStep(res.next);
            }
        })
        .catch(err => appendLog("❌ DB save error: " + err));
    }

    function appendLog(msg) {
        const log = document.getElementById("log");
        log.innerHTML += msg + "<br>";
        log.scrollTop = log.scrollHeight;
    }

    function updateBar(percent) {
        const bar = document.getElementById("bar");
        bar.style.width = percent + "%";
        bar.innerHTML = percent + "%";
    }
</script>

</body>

</html>