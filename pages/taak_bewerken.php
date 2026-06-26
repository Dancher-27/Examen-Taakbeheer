<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=niet_ingelogd');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Task.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Project.php';

$db = (new Database())->getConnection();
$taskModel = new Task($db);
$projectModel = new Project($db);

$isAdmin = $_SESSION['user_role'] === 'admin';
$taskId = (int) ($_GET['id'] ?? 0);
$task = $taskModel->getById($taskId);
$backUrl = $isAdmin ? 'taken_admin.php' : 'tasks.php';

if (!$task) {
    header("Location: $backUrl");
    exit;
}

if (!$isAdmin && !$taskModel->isAssignedToUser($taskId, $_SESSION['user_id'])) {
    header('Location: login.php?error=geen_toegang');
    exit;
}

$isCreator = (int) $task['User_idUser'] === (int) $_SESSION['user_id'];
$canEditAll = $isAdmin || $isCreator;

$categories = $taskModel->getCategories();
$projects = $projectModel->getAll();
$assignedUsers = $taskModel->getAssignedUsers($taskId);
$assignedIds = array_column($assignedUsers, 'idUser');

$users = [];
if ($isAdmin) {
    $userModel = new User($db);
    $users = $userModel->getAll();
}

$errors = [];
$formData = [
    'titel' => $task['titel'],
    'beschrijving' => $task['beschrijving'] ?? '',
    'prioriteit' => $task['prioriteit'],
    'status' => $task['status'],
    'deadline' => $task['deadline'] ?? '',
    'categorie' => $task['Category_idCategory'] ?? '',
    'project' => $task['Project_idProject'] ?? '',
    'gebruikers' => $assignedIds,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($canEditAll) {
        $formData['titel'] = trim($_POST['titel'] ?? '');
        $formData['beschrijving'] = trim($_POST['beschrijving'] ?? '');
        $formData['prioriteit'] = $_POST['prioriteit'] ?? '';
        $formData['status'] = $_POST['status'] ?? '';
        $formData['deadline'] = $_POST['deadline'] ?? '';
        $formData['categorie'] = $_POST['categorie'] ?? '';
        $formData['project'] = $_POST['project'] ?? '';
        $formData['gebruikers'] = $_POST['gebruikers'] ?? $assignedIds;

        $result = $taskModel->update($taskId, $formData, $_SESSION['user_id'], $isAdmin);
    } else {
        $formData['status'] = $_POST['status'] ?? '';
        $result = $taskModel->updateStatus($taskId, $formData['status']);
    }

    if ($result['success']) {
        header("Location: taak_details.php?id=$taskId&updated=1");
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Taak bewerken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Taak bewerken</h1>
        <a href="taak_details.php?id=<?= $taskId ?>" class="btn-logout">Terug naar details</a>
    </header>

    <div class="auth-container edit-form">
        <form method="POST" novalidate>
            <?php if (!$canEditAll): ?>
                <p class="no-items">Je bent alleen toegewezen aan deze taak — je kunt enkel de status aanpassen.</p>
            <?php endif; ?>

            <div class="form-group">
                <label for="titel">Titel</label>
                <input type="text" id="titel" name="titel" value="<?= htmlspecialchars($formData['titel']) ?>" <?= $canEditAll ? '' : 'disabled' ?>>
                <?php if (!empty($errors['titel'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['titel']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="beschrijving">Beschrijving</label>
                <textarea id="beschrijving" name="beschrijving" rows="4" <?= $canEditAll ? '' : 'disabled' ?>><?= htmlspecialchars($formData['beschrijving']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="prioriteit">Prioriteit</label>
                <select id="prioriteit" name="prioriteit" <?= $canEditAll ? '' : 'disabled' ?>>
                    <option value="laag" <?= $formData['prioriteit'] === 'laag' ? 'selected' : '' ?>>Laag</option>
                    <option value="gemiddeld" <?= $formData['prioriteit'] === 'gemiddeld' ? 'selected' : '' ?>>Gemiddeld</option>
                    <option value="hoog" <?= $formData['prioriteit'] === 'hoog' ? 'selected' : '' ?>>Hoog</option>
                </select>
                <?php if (!empty($errors['prioriteit'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['prioriteit']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="open" <?= $formData['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="in_progress" <?= $formData['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option>
                    <option value="done" <?= $formData['status'] === 'done' ? 'selected' : '' ?>>Done</option>
                </select>
                <?php if (!empty($errors['status'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['status']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="categorie">Categorie</label>
                <select id="categorie" name="categorie" <?= $canEditAll ? '' : 'disabled' ?>>
                    <option value="">Geen categorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['idCategory'] ?>" <?= $formData['categorie'] == $cat['idCategory'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['naam']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="project">Project</label>
                <select id="project" name="project" <?= $canEditAll ? '' : 'disabled' ?>>
                    <option value="">Geen project</option>
                    <?php foreach ($projects as $proj): ?>
                        <option value="<?= $proj['idProject'] ?>" <?= $formData['project'] == $proj['idProject'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proj['naam']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars($formData['deadline']) ?>" <?= $canEditAll ? '' : 'disabled' ?>>
            </div>

            <?php if ($isAdmin): ?>
                <div class="form-group">
                    <label for="gebruikers">Toegewezen gebruikers</label>
                    <select id="gebruikers" name="gebruikers[]" multiple size="5">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['idUser'] ?>" <?= in_array($user['idUser'], $formData['gebruikers']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['naam']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="no-items">Houd Ctrl ingedrukt om meerdere gebruikers te selecteren.</p>
                    <?php if (!empty($errors['gebruikers'])): ?>
                        <span class="error"><?= htmlspecialchars($errors['gebruikers']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <button type="submit">Wijzigingen opslaan</button>
        </form>
    </div>
</div>
</body>
</html>
