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

$search = trim($_GET['search'] ?? '');
$roleFilter = $_GET['rol'] ?? '';

$users = $userModel->getAll($search, $roleFilter);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Gebruikersbeheer – Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Gebruikersbeheer</h1>
        <a href="admin_dashboard.php" class="btn-logout">Terug naar dashboard</a>
    </header>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Zoek op naam of e-mail" value="<?= htmlspecialchars($search) ?>">
        <select name="rol">
            <option value="">Alle rollen</option>
            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="gebruiker" <?= $roleFilter === 'gebruiker' ? 'selected' : '' ?>>Gebruiker</option>
        </select>
        <button type="submit" class="btn-primary">Filteren</button>
    </form>

    <?php if (empty($users)): ?>
        <p class="no-items">Geen gebruikers gevonden.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>E-mailadres</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['naam']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="role-badge role-<?= $user['rol'] ?>"><?= ucfirst($user['rol']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
