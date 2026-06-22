<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    $user = new User($db);

    $result = $user->register(
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? ''
    );

    if ($result['success']) {
        header('Location: login.php?registered=1');
        exit;
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <h1>Registreren</h1>
    <form method="POST" novalidate>
        <div class="form-group">
            <label for="name">Naam</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <?php if (!empty($errors['name'])): ?>
                <span class="error"><?= htmlspecialchars($errors['name']) ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
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
        <button type="submit">Registreren</button>
    </form>
    <p>Al een account? <a href="login.php">Inloggen</a></p>
</div>
</body>
</html>
