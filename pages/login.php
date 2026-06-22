<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';

$registered = isset($_GET['registered']);
$error = '';

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'niet_ingelogd') {
        $error = 'Je moet ingelogd zijn om deze pagina te bekijken.';
    } elseif ($_GET['error'] === 'geen_toegang') {
        $error = 'Je hebt geen toegang tot deze pagina.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    $user = new User($db);

    $result = $user->login(
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? ''
    );

    if ($result['success']) {
        if ($result['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <h1>Inloggen</h1>
    <?php if ($registered): ?>
        <p class="success">Registratie gelukt! Je kunt nu inloggen.</p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" novalidate>
        <div class="form-group">
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="password">Wachtwoord</label>
            <input type="password" id="password" name="password">
        </div>
        <button type="submit">Inloggen</button>
    </form>
    <p>Nog geen account? <a href="register.php">Registreren</a></p>
</div>
</body>
</html>
