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
require_once __DIR__ . '/../classes/User.php';

$db = (new Database())->getConnection();
$userModel = new User($db);

$id = (int) ($_GET['id'] ?? 0);
$result = $userModel->delete($id, $_SESSION['user_id']);

if ($result['success']) {
    header('Location: gebruikers.php?deleted=1');
} else {
    header('Location: gebruikers.php?error=' . urlencode($result['error']));
}
exit;
