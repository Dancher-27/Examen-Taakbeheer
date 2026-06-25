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

$filters = [
    'user' => $_GET['user'] ?? '',
    'status' => $_GET['status'] ?? '',
    'prioriteit' => $_GET['prioriteit'] ?? '',
    'categorie' => $_GET['categorie'] ?? '',
    'search' => trim($_GET['search'] ?? ''),
];

$tasks = $taskModel->getAllForAdmin($filters);
$users = $userModel->getAll();
$categories = $taskModel->getCategories();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Takenoverzicht – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Takenoverzicht (alle gebruikers)</h1>
    </header>

    <?php if (isset($_GET['created'])): ?>
        <p class="success">Taak succesvol aangemaakt.</p>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <p class="success">Taak succesvol verwijderd.</p>
    <?php endif; ?>

    <a href="taak_aanmaken_admin.php" class="btn-primary" style="margin-bottom: 1.5rem; display: inline-block;">+ Nieuwe taak aanmaken</a>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Zoek op taaknaam" value="<?= htmlspecialchars($filters['search']) ?>">

        <select name="user">
            <option value="">Alle gebruikers</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['idUser'] ?>" <?= $filters['user'] == $user['idUser'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['naam']) ?>
                </option>
            <?php endforeach; ?>
        </select>

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

        <button type="submit" class="btn-primary">Filteren</button>
    </form>

    <?php if (empty($tasks)): ?>
        <p class="no-items">Geen taken gevonden.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Gebruiker</th>
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
                        <td><?= htmlspecialchars($task['gebruiker_naam'] ?? '-') ?></td>
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
