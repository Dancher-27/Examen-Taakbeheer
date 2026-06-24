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
require_once __DIR__ . '/../classes/User.php';

$db = (new Database())->getConnection();
$userModel = new User($db);

$errors = [];
$formData = ['naam' => '', 'email' => '', 'rol' => 'gebruiker'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['naam'] = trim($_POST['naam'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['rol'] = $_POST['rol'] ?? 'gebruiker';
    $password = $_POST['password'] ?? '';

    $result = $userModel->create($formData['naam'], $formData['email'], $password, $formData['rol']);

    if ($result['success']) {
        header('Location: gebruikers.php?created=1');
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Gebruiker aanmaken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Gebruiker aanmaken</h1>
        <a href="gebruikers.php" class="btn-logout">Terug naar overzicht</a>
    </header>

    <div class="auth-container edit-form">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="naam">Naam</label>
                <input type="text" id="naam" name="naam" value="<?= htmlspecialchars($formData['naam']) ?>">
                <?php if (!empty($errors['name'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>">
                <?php if (!empty($errors['email'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password">
                <?php if (!empty($errors['password'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="gebruiker" <?= $formData['rol'] === 'gebruiker' ? 'selected' : '' ?>>Gebruiker</option>
                    <option value="admin" <?= $formData['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <?php if (!empty($errors['rol'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['rol']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit">Gebruiker aanmaken</button>
        </form>
    </div>
</div>
</body>
</html>
