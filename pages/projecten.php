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
$projects = $projectModel->getAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Projecten – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Projecten</h1>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <p class="success">Project succesvol aangemaakt.</p>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <p class="success">Project succesvol bewerkt.</p>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <p class="success">Project succesvol verwijderd.</p>
    <?php endif; ?>

    <a href="project_aanmaken.php" class="btn-primary" style="margin-bottom: 1.5rem; display: inline-block;">+ Nieuw project aanmaken</a>

    <?php if (empty($projects)): ?>
        <p class="no-items">Nog geen projecten aangemaakt.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Beschrijving</th>
                    <th>Aantal taken</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= htmlspecialchars($project['naam']) ?></td>
                        <td><?= htmlspecialchars($project['beschrijving'] ?: '-') ?></td>
                        <td><?= (int) $project['taken_count'] ?></td>
                        <td>
                            <a href="project_bewerken.php?id=<?= $project['idProject'] ?>">Bewerken</a>
                            | <a href="project_verwijderen.php?id=<?= $project['idProject'] ?>" onclick="return confirm('Weet je zeker dat je dit project wilt verwijderen?')">Verwijderen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
