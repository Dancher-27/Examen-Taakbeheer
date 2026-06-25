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
$filters = [
    'status' => $_GET['status'] ?? '',
    'prioriteit' => $_GET['prioriteit'] ?? '',
    'categorie' => $_GET['categorie'] ?? '',
    'search' => trim($_GET['search'] ?? ''),
];
$hasFilters = array_filter($filters) !== [];

$tasks = $taskModel->getForUser($_SESSION['user_id'], $filters, $sort);
$categories = $taskModel->getCategories();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mijn taken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Mijn taken</h1>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <p class="success">Taak succesvol aangemaakt.</p>
    <?php endif; ?>

    <a href="taak_aanmaken_gebruiker.php" class="btn-primary" style="margin-bottom: 1.5rem; display: inline-block;">+ Nieuwe taak aanmaken</a>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Zoek op taaknaam" value="<?= htmlspecialchars($filters['search']) ?>">

        <select name="status">
            <option value="">Alle statussen</option>
            <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option>
            <option value="done" <?= $filters['status'] === 'done' ? 'selected' : '' ?>>Done</option>
        </select>

        <select name="prioriteit">
            <option value="">Alle prioriteiten</option>
            <option value="laag" <?= $filters['prioriteit'] === 'laag' ? 'selected' : '' ?>>Laag</option>
            <option value="gemiddeld" <?= $filters['prioriteit'] === 'gemiddeld' ? 'selected' : '' ?>>Gemiddeld</option>
            <option value="hoog" <?= $filters['prioriteit'] === 'hoog' ? 'selected' : '' ?>>Hoog</option>
        </select>

        <select name="categorie">
            <option value="">Alle categorieën</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['idCategory'] ?>" <?= $filters['categorie'] == $cat['idCategory'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['naam']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sort" onchange="this.form.submit()">
            <option value="deadline" <?= $sort === 'deadline' ? 'selected' : '' ?>>Sorteer op deadline</option>
            <option value="prioriteit" <?= $sort === 'prioriteit' ? 'selected' : '' ?>>Sorteer op prioriteit</option>
        </select>

        <button type="submit" class="btn-primary">Filteren</button>
        <?php if ($hasFilters): ?>
            <a href="tasks.php" class="btn-logout">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (empty($tasks)): ?>
        <p class="no-items"><?= $hasFilters ? 'Geen taken gevonden voor deze filters.' : 'Je hebt nog geen taken.' ?></p>
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
                    <tr data-task class="<?php echo Task::getUrgencyByDeadline($task['deadline']); ?>">
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
                        <td data-deadline><?= $task['deadline'] ? (new DateTime($task['deadline']))->format('d-m-Y') : '-' ?></td>
                        <td><a href="taak_details.php?id=<?= $task['idTask'] ?>">Details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function getDeadlineValue(el) {
        return el.textContent.trim();
    }

    function parseDmy(dateStr) {
        const [day, month, year] = dateStr.split('-').map(Number);
        return new Date(year, month - 1, day);
    }

    function daysBetween(today, deadline) {
        const msPerDay = 1000 * 60 * 60 * 24;
        return Math.round((deadline - today) / msPerDay);
    }

    function updateUrgencyClasses() {
        const taskElements = document.querySelectorAll('[data-task]');

        taskElements.forEach(function (taskEl) {
            const deadlineEl = taskEl.querySelector('[data-deadline]');
            if (!deadlineEl) {
                return;
            }

            const deadlineValue = getDeadlineValue(deadlineEl);
            if (deadlineValue === '-') {
                taskEl.removeAttribute('class');
                return;
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const deadlineDate = parseDmy(deadlineValue);
            deadlineDate.setHours(0, 0, 0, 0);

            const diff = daysBetween(today, deadlineDate);

            if (diff <= 0) {
                taskEl.className = 'urgency-high';
                return;
            }

            if (diff < 3) {
                taskEl.className = 'urgency-medium';
            }

            else {
                taskEl.className = 'urgency-low';
            }
        });
    }

    updateUrgencyClasses();
    setInterval(updateUrgencyClasses, 60 * 60 * 1000);
});
</script>
</body>
</html>
