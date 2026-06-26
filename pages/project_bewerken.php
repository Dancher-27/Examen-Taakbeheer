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

$projectId = (int) ($_GET['id'] ?? 0);
$project = $projectModel->getById($projectId);

if (!$project) {
    header('Location: projecten.php');
    exit;
}

$errors = [];
$formData = [
    'naam' => $project['naam'],
    'beschrijving' => $project['beschrijving'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['naam'] = trim($_POST['naam'] ?? '');
    $formData['beschrijving'] = trim($_POST['beschrijving'] ?? '');

    $result = $projectModel->update($projectId, $formData['naam'], $formData['beschrijving']);

    if ($result['success']) {
        header('Location: projecten.php?updated=1');
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Project "<?= htmlspecialchars($formData['naam']) ?>" bewerken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Project "<?= htmlspecialchars($formData['naam']) ?>" bewerken</h1>
        <a href="projecten.php" class="btn-logout">Terug naar overzicht</a>
    </header>

    <div class="auth-container edit-form">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="naam">Naam</label>
                <input type="text" id="naam" name="naam" value="<?= htmlspecialchars($formData['naam']) ?>">
                <?php if (!empty($errors['naam'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['naam']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="beschrijving">Beschrijving</label>
                <textarea id="beschrijving" name="beschrijving" rows="4"><?= htmlspecialchars($formData['beschrijving']) ?></textarea>
            </div>
            <button type="submit">Project bewerken</button>
        </form>
    </div>
</div>
</body>
</html>
