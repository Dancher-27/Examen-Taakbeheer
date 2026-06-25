<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=niet_ingelogd');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Task.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);

$taskId = (int) ($_GET['id'] ?? 0);
$task = $taskModel->getById($taskId);
$isAdmin = $_SESSION['user_role'] === 'admin';
$backUrl = $isAdmin ? 'taken_admin.php' : 'tasks.php';

if (!$task) {
    header("Location: $backUrl");
    exit;
}

$hasAccess = $isAdmin || $taskModel->isAssignedToUser($taskId, $_SESSION['user_id']);
if (!$hasAccess) {
    header('Location: login.php?error=geen_toegang');
    exit;
}

$progressError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voortgang_beschrijving'])) {
    $result = $taskModel->addProgressUpdate($taskId, $_SESSION['user_id'], $_POST['voortgang_beschrijving']);
    if ($result['success']) {
        header("Location: taak_details.php?id=$taskId");
        exit;
    }
    $progressError = $result['error'];
}

$isCreator = (int) $task['User_idUser'] === (int) $_SESSION['user_id'];
$canDelete = $isAdmin || $isCreator;

$assignedUsers = $taskModel->getAssignedUsers($taskId);
$progressUpdates = $taskModel->getProgressUpdates($taskId);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($task['titel']) ?> – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1><?= htmlspecialchars($task['titel']) ?></h1>
        <a href="<?= $backUrl ?>" class="btn-logout">Terug naar overzicht</a>
    </header>

    <?php if (isset($_GET['updated'])): ?>
        <p class="success">Taak succesvol bijgewerkt.</p>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="card">
            <h2>Taakinformatie</h2>
            <p><strong>Beschrijving:</strong><br><?= nl2br(htmlspecialchars($task['beschrijving'] ?: 'Geen beschrijving')) ?></p>
            <p><strong>Prioriteit:</strong> <span class="prio-<?= $task['prioriteit'] ?>"><?= ucfirst($task['prioriteit']) ?></span></p>
            <p><strong>Status:</strong> <span class="status-badge status-<?= $task['status'] ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span></p>
            <p><strong>Categorie:</strong>
                <?php if ($task['categorie_naam']): ?>
                    <span class="category-tag" style="background: <?= htmlspecialchars($task['kleurcode']) ?>;"><?= htmlspecialchars($task['categorie_naam']) ?></span>
                <?php else: ?>
                    Geen categorie
                <?php endif; ?>
            </p>
            <p><strong>Deadline:</strong> <?= $task['deadline'] ? (new DateTime($task['deadline']))->format('d-m-Y') : '-' ?></p>
            <p><strong>Toegewezen gebruikers:</strong>
                <?= !empty($assignedUsers) ? htmlspecialchars(implode(', ', array_column($assignedUsers, 'naam'))) : 'Niemand toegewezen' ?>
            </p>

            <div class="task-actions">
                <a href="taak_bewerken.php?id=<?= $task['idTask'] ?>" class="btn-primary">Bewerken</a>
                <?php if ($canDelete): ?>
                    <a href="taak_verwijderen.php?id=<?= $task['idTask'] ?>" class="btn-logout" onclick="return confirm('Weet je zeker dat je deze taak wilt verwijderen?')">Verwijderen</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>Voortgangsupdates</h2>
            <?php if (empty($progressUpdates)): ?>
                <p class="no-items">Nog geen voortgangsupdates.</p>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($progressUpdates as $update): ?>
                        <li class="task-item upcoming">
                            <span class="task-title"><?= htmlspecialchars($update['gebruiker_naam']) ?>:
                                <?= htmlspecialchars($update['beschrijving']) ?>
                            </span>
                            <span class="task-deadline"><?= (new DateTime($update['created_at']))->format('d-m-Y H:i') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="POST" class="progress-form">
                <div class="form-group">
                    <label for="voortgang_beschrijving">Nieuwe update</label>
                    <textarea id="voortgang_beschrijving" name="voortgang_beschrijving" rows="3" placeholder="Beschrijf je voortgang..."></textarea>
                    <?php if ($progressError): ?>
                        <span class="error"><?= htmlspecialchars($progressError) ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-primary">Update plaatsen</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
