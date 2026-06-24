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

$db = (new Database())->getConnection();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT COUNT(*) AS total FROM tasks WHERE User_idUser = ? AND status != 'done'");
$stmt->execute([$userId]);
$openCount = $stmt->fetch()['total'];

$stmt = $db->prepare("
    SELECT titel, deadline, status, prioriteit
    FROM tasks
    WHERE User_idUser = ?
      AND deadline IS NOT NULL
      AND status != 'done'
      AND deadline <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY deadline ASC
");
$stmt->execute([$userId]);
$urgentTasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Welkom, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
    </header>

    <div class="dashboard-grid">
        <div class="card">
            <h2>Openstaande taken</h2>
            <p class="stat"><?= $openCount ?></p>
        </div>

        <div class="card">
            <h2>Naderende deadlines</h2>
            <?php if (empty($urgentTasks)): ?>
                <p class="no-items">Geen urgente taken.</p>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($urgentTasks as $task): ?>
                        <?php
                            $deadline = new DateTime($task['deadline']);
                            $today = new DateTime('today');
                            $overdue = $deadline < $today;
                        ?>
                        <li class="task-item <?= $overdue ? 'overdue' : 'upcoming' ?>">
                            <span class="task-title"><?= htmlspecialchars($task['titel']) ?></span>
                            <span class="task-deadline">
                                <?= $overdue ? 'Verlopen: ' : 'Deadline: ' ?>
                                <?= $deadline->format('d-m-Y') ?>
                            </span>
                            <span class="task-priority prio-<?= $task['prioriteit'] ?>">
                                <?= ucfirst($task['prioriteit']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <a href="tasks.php" class="btn-primary">Ga naar takenoverzicht</a>
</div>
</body>
</html>
