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

$id = (int) ($_GET['id'] ?? 0);
$user = $userModel->getById($id);

if (!$user) {
    header('Location: gebruikers.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userModel->update(
        $id,
        trim($_POST['naam'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['rol'] ?? ''
    );

    if ($result['success']) {
        header('Location: gebruikers.php?updated=1');
        exit;
    }

    $errors = $result['errors'];
    $user['naam'] = $_POST['naam'];
    $user['email'] = $_POST['email'];
    $user['rol'] = $_POST['rol'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Gebruiker bewerken – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Gebruiker bewerken</h1>
        <a href="gebruikers.php" class="btn-logout">Terug naar overzicht</a>
    </header>

    <div class="auth-container edit-form">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="naam">Naam</label>
                <input type="text" id="naam" name="naam" value="<?= htmlspecialchars($user['naam']) ?>">
                <?php if (!empty($errors['name'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['name']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                <?php if (!empty($errors['email'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="gebruiker" <?= $user['rol'] === 'gebruiker' ? 'selected' : '' ?>>Gebruiker</option>
                    <option value="admin" <?= $user['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <?php if (!empty($errors['rol'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['rol']) ?></span>
                <?php endif; ?>
            </div>
            <button type="submit">Opslaan</button>
        </form>
    </div>
</div>
</body>
</html>
