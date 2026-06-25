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
require_once __DIR__ . '/../classes/Project.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);
$projectModel = new Project($db);

$projectId = (int) ($_GET['id'] ?? 0);
$project = $projectModel->getById($projectId);

if (!$project) {
    header('Location: tasks.php');
    exit;
}

$tasks = $taskModel->getForUser($_SESSION['user_id'], ['project' => $projectId]);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($project['naam']) ?> – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1><?= htmlspecialchars($project['naam']) ?></h1>
        <a href="tasks.php" class="btn-logout">Terug naar mijn taken</a>
    </header>

    <p class="no-items" style="margin-bottom: 1.5rem;">
        <?= $project['beschrijving'] ? htmlspecialchars($project['beschrijving']) : 'Geen beschrijving.' ?>
    </p>

    <?php if (empty($tasks)): ?>
        <p class="no-items">Je hebt geen taken binnen dit project.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Prioriteit</th>
                    <th>Status</th>
                    <th>Categorie</th>
                    <th>Deadline</th>
                    <th></th>
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
                        <td><a href="taak_details.php?id=<?= $task['idTask'] ?>">Details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
