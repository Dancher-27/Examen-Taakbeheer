<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=niet_ingelogd');
    exit;
}
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: login.php?error=geen_toegang');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Project.php';

$db = (new Database())->getConnection();
$projectModel = new Project($db);

$projectId = (int) ($_GET['id'] ?? 0);
if ($projectId) {
    $projectModel->delete($projectId);
}

header('Location: projecten.php?deleted=1');
exit;
