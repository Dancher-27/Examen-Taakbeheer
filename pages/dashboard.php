<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=niet_ingelogd');
    exit;
}
if ($_SESSION['user_role'] === 'admin') {
    header('Location: login.php?error=geen_toegang');
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Welkom, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        <a href="logout.php" class="btn-logout">Uitloggen</a>
    </header>
</div>
</body>
</html>
