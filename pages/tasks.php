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

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Task.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);

$sort = $_GET['sort'] ?? 'deadline';
$tasks = $taskModel->getForUser($_SESSION['user_id'], $sort);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mijn taken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Mijn taken</h1>
        <a href="dashboard.php" class="btn-logout">Terug naar dashboard</a>
    </header>

    <form method="GET" class="filter-bar">
        <select name="sort" onchange="this.form.submit()">
            <option value="deadline" <?= $sort === 'deadline' ? 'selected' : '' ?>>Sorteer op deadline</option>
            <option value="prioriteit" <?= $sort === 'prioriteit' ? 'selected' : '' ?>>Sorteer op prioriteit</option>
        </select>
    </form>

    <?php if (empty($tasks)): ?>
        <p class="no-items">Je hebt nog geen taken.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Prioriteit</th>
                    <th>Status</th>
                    <th>Categorie</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['titel']) ?></td>
                        <td><span class="prio-<?= $task['prioriteit'] ?>"><?= ucfirst($task['prioriteit']) ?></span></td>
                        <td><span class="status-badge status-<?= $task['status'] ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span></td>
                        <td>
                            <?php if ($task['categorie_naam']): ?>
                                <span class="category-tag" style="background: <?= htmlspecialchars($task['kleurcode']) ?>;"><?= htmlspecialchars($task['categorie_naam']) ?></span>
                            <?php else: ?>
                                <span class="no-items">Geen categorie</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $task['deadline'] ? (new DateTime($task['deadline']))->format('d-m-Y') : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
