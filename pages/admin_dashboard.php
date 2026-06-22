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
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Beheerdashboard – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Beheerdashboard</h1>
        <a href="logout.php" class="btn-logout">Uitloggen</a>
    </header>

    <p class="admin-welcome">Ingelogd als: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>

    <div class="admin-grid">
        <a href="gebruikers.php" class="admin-card">
            <h2>Gebruikersbeheer</h2>
            <p>Bekijk, bewerk of verwijder gebruikers.</p>
        </a>
        <a href="categorieen.php" class="admin-card">
            <h2>Categoriebeheer</h2>
            <p>Voeg categorieën toe, bewerk of verwijder ze.</p>
        </a>
    </div>
</div>
</body>
</html>
