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
require_once __DIR__ . '/../classes/Task.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);

$taskId = (int) ($_GET['id'] ?? 0);
$taskModel->delete($taskId, $_SESSION['user_id']);

header('Location: taken_admin.php?deleted=1');
exit;
