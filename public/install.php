<!DOCTYPE html>
<html>
<head>
<title>Installer</title>
<style>
body{font-family:Arial;background:#f2f2f2;padding:20px;}
.container{max-width:700px;margin:50px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
h2{margin-bottom:20px;}
.progress{background:#eee;border-radius:20px;height:20px;margin-bottom:20px;overflow:hidden;}
.bar{height:100%;width:0;background:#4caf50;text-align:center;color:#fff;line-height:20px;transition:0.4s;}
.output{background:#000;color:#0f0;padding:10px;height:300px;overflow:auto;font-family:monospace;}
.button{padding:10px 20px;background:#4caf50;color:#fff;border-radius:5px;cursor:pointer;margin-top:10px;}
.hidden{display:none;}
input{width:100%;padding:8px;margin-bottom:10px;}
</style>
</head>
<body>
<div class="container">
    <h2>Installer</h2>

    <div class="progress"><div id="bar" class="bar">0%</div></div>

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
let steps = ["check","composer","db_config","env","key","migrate","seed","permissions","finish"];
let currentIndex = 0;

function runStep(step){
    document.getElementById("startBtn").classList.add("hidden");
    let isDBStep = step === "db_config";

    fetch("install_ajax.php?step="+step, {
        method: isDBStep ? "POST" : "GET",
        body: isDBStep ? new URLSearchParams({
            db_host: document.getElementById("db_host").value,
            db_database: document.getElementById("db_database").value,
            db_username: document.getElementById("db_username").value,
            db_password: document.getElementById("db_password").value,
        }) : null
    })
    .then(res => res.json())
    .then(res => {
        appendLog(res.message || JSON.stringify(res));

        // Progress bar
        currentIndex = steps.indexOf(step);
        let percent = Math.round((currentIndex+1)/steps.length*100);
        updateBar(percent);

        // Show DB form if needed
        if(step === "db_config" && !res.status === "ok"){
            document.getElementById("dbform").classList.remove("hidden");
            return;
        } else {
            document.getElementById("dbform").classList.add("hidden");
        }

        // Next step
        if(step !== "finish"){
            let nextStep = steps[currentIndex+1];
            setTimeout(()=>runStep(nextStep), 500);
        }
    })
    .catch(err=>{
        appendLog("❌ AJAX error: " + err);
    });
}

function saveDB(){
    runStep("db_config");
}

function appendLog(msg){
    let log = document.getElementById("log");
    log.innerHTML += msg + "<br>";
    log.scrollTop = log.scrollHeight;
}

function updateBar(percent){
    let bar = document.getElementById("bar");
    bar.style.width = percent+"%";
    bar.innerHTML = percent+"%";
}
</script>

</body>
</html>
