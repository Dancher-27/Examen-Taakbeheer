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

$categories = $taskModel->getCategories();
$users = $userModel->getAll();

$errors = [];
$formData = [
    'titel' => '',
    'beschrijving' => '',
    'prioriteit' => 'gemiddeld',
    'status' => 'open',
    'deadline' => '',
    'categorie' => '',
    'gebruikers' => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['titel'] = trim($_POST['titel'] ?? '');
    $formData['beschrijving'] = trim($_POST['beschrijving'] ?? '');
    $formData['prioriteit'] = $_POST['prioriteit'] ?? '';
    $formData['status'] = $_POST['status'] ?? '';
    $formData['deadline'] = $_POST['deadline'] ?? '';
    $formData['categorie'] = $_POST['categorie'] ?? '';
    $formData['gebruikers'] = $_POST['gebruikers'] ?? [];

    $result = $taskModel->create($formData, $_SESSION['user_id']);

    if ($result['success']) {
        header('Location: taken_admin.php?created=1');
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Taak aanmaken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Nieuwe taak aanmaken</h1>
        <a href="taken_admin.php" class="btn-logout">Terug naar overzicht</a>
    </header>

    <div class="auth-container edit-form">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="titel">Titel</label>
                <input type="text" id="titel" name="titel" value="<?= htmlspecialchars($formData['titel']) ?>">
                <?php if (!empty($errors['titel'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['titel']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="beschrijving">Beschrijving</label>
                <textarea id="beschrijving" name="beschrijving" rows="4"><?= htmlspecialchars($formData['beschrijving']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="prioriteit">Prioriteit</label>
                <select id="prioriteit" name="prioriteit">
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
                <select id="categorie" name="categorie">
                    <option value="">Geen categorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['idCategory'] ?>" <?= $formData['categorie'] == $cat['idCategory'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['naam']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars($formData['deadline']) ?>">
            </div>

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

            <button type="submit">Taak aanmaken</button>
        </form>
    </div>
</div>
</body>
</html>
