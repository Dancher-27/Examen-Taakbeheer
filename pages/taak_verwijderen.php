<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=niet_ingelogd');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Task.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);

$isAdmin = $_SESSION['user_role'] === 'admin';
$taskId = (int) ($_GET['id'] ?? 0);
$task = $taskModel->getById($taskId);
$backUrl = $isAdmin ? 'taken_admin.php' : 'tasks.php';

if (!$task) {
    header("Location: $backUrl");
    exit;
}

$isCreator = (int) $task['User_idUser'] === (int) $_SESSION['user_id'];
if (!$isAdmin && !$isCreator) {
    header('Location: login.php?error=geen_toegang');
    exit;
}

$taskModel->delete($taskId, $_SESSION['user_id']);

header("Location: $backUrl?deleted=1");
exit;
