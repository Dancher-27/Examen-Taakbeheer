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
$categories = $categoryModel->getAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Categoriebeheer – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Categoriebeheer</h1>
        <a href="admin_dashboard.php" class="btn-logout">Terug naar dashboard</a>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <p class="success">Categorie succesvol aangemaakt.</p>
    <?php endif; ?>

    <a href="categorie_aanmaken.php" class="btn-primary" style="margin-bottom: 1.5rem; display: inline-block;">+ Nieuwe categorie aanmaken</a>

    <?php if (empty($categories)): ?>
        <p class="no-items">Nog geen categorieën aangemaakt.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Kleur</th>
                    <th>Aantal taken</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['naam']) ?></td>
                        <td><span class="category-tag" style="background: <?= htmlspecialchars($cat['kleurcode']) ?>;"><?= htmlspecialchars($cat['kleurcode']) ?></span></td>
                        <td><?= (int) $cat['taken_count'] ?></td>
                        <td>
                            <a href="categorie_bewerken.php?id=<?= $cat['idCategory'] ?>">Bewerken</a>
                            | <a href="categorie_verwijderen.php?id=<?= $cat['idCategory'] ?>" onclick="return confirm('Weet je zeker dat je deze categorie wilt verwijderen?')">Verwijderen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
