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
require_once __DIR__ . '/../classes/User.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);
$userModel = new User($db);

$statusCounts = $taskModel->getStatusCounts();
$overdueCount = $taskModel->getOverdueCount();
$activeUsers = $userModel->getMostActive();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Beheerdashboard – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Beheerdashboard</h1>
    </header>

    <p class="admin-welcome">Ingelogd als: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>

    <div class="dashboard-grid">
        <div class="card">
            <h2>Taken per status</h2>
            <p><span class="status-badge status-open">Open</span> <?= $statusCounts['open'] ?></p>
            <p><span class="status-badge status-in_progress">In progress</span> <?= $statusCounts['in_progress'] ?></p>
            <p><span class="status-badge status-done">Done</span> <?= $statusCounts['done'] ?></p>
            <p><strong>Verlopen taken:</strong> <?= $overdueCount ?></p>
        </div>

        <div class="card">
            <h2>Meest actieve gebruikers</h2>
            <?php if (empty($activeUsers)): ?>
                <p class="no-items">Nog geen activiteit geregistreerd.</p>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($activeUsers as $user): ?>
                        <li class="task-item upcoming">
                            <span class="task-title"><?= htmlspecialchars($user['naam']) ?></span>
                            <span class="task-deadline"><?= $user['aantal_acties'] ?> acties</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-grid">
        <a href="gebruikers.php" class="admin-card">
            <h2>Gebruikersbeheer</h2>
            <p>Bekijk, bewerk of verwijder gebruikers.</p>
        </a>
        <a href="categorieen.php" class="admin-card">
            <h2>Categoriebeheer</h2>
            <p>Voeg categorieën toe, bewerk of verwijder ze.</p>
        </a>
        <a href="taken_admin.php" class="admin-card">
            <h2>Takenoverzicht</h2>
            <p>Bekijk en filter alle taken van alle gebruikers.</p>
        </a>
    </div>
</div>
</body>
</html>
