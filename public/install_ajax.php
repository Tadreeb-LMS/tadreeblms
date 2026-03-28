<?php
header('Content-Type: application/json');

$basePath = realpath(__DIR__ . '/..');
if (!$basePath) {
    echo json_encode(['success' => false, 'message' => '❌ Base path not resolved']);
    exit;
}

require_once __DIR__ . '/installer_core.php';

$step = $_REQUEST['step'] ?? 'check';
$core = new InstallerCore($basePath);

$result = $core->handle(
    $step,
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_POST
);

echo json_encode($result);
