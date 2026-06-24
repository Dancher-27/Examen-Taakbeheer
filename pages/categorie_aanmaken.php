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
require_once __DIR__ . '/../classes/Category.php';

$db = (new Database())->getConnection();
$categoryModel = new Category($db);

$errors = [];
$formData = ['naam' => '', 'kleurcode' => '#4a90e2'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['naam'] = trim($_POST['naam'] ?? '');
    $formData['kleurcode'] = trim($_POST['kleurcode'] ?? '');

    $result = $categoryModel->create($formData['naam'], $formData['kleurcode']);

    if ($result['success']) {
        header('Location: categorieen.php?created=1');
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Categorie aanmaken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Categorie aanmaken</h1>
        <a href="categorieen.php" class="btn-logout">Terug naar overzicht</a>
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
                <label for="kleurcode">Kleurcode</label>
                <input type="color" id="kleurcode" name="kleurcode" value="<?= htmlspecialchars($formData['kleurcode']) ?>">
                <?php if (!empty($errors['kleurcode'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['kleurcode']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit">Categorie aanmaken</button>
        </form>
    </div>
</div>
</body>
</html>
