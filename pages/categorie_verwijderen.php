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
require_once __DIR__ . '/../classes/Category.php';

$db = (new Database())->getConnection();
$categoryModel = new Category($db);
$categoryId = (int) ($_GET['id'] ?? 0);
if($categoryId) {
    $categoryDeleted = $categoryModel->delete($categoryId);
    if($categoryDeleted) {
        header('Location: categorieen.php?deleted=1');
        exit;
    }
}

header('Location: categorieen.php?error=no_delete');
exit;
