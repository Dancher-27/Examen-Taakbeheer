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

    <?php if (isset($_GET['updated'])): ?>
        <p class="success">Gebruiker succesvol bijgewerkt.</p>
    <?php endif; ?>
    <?php if (isset($_GET['created'])): ?>
        <p class="success">Gebruiker succesvol aangemaakt.</p>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <p class="success">Gebruiker succesvol verwijderd.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <a href="gebruiker_aanmaken.php" class="btn-primary" style="margin-bottom: 1.5rem; display: inline-block;">+ Nieuwe gebruiker aanmaken</a>

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
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['naam']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="role-badge role-<?= $user['rol'] ?>"><?= ucfirst($user['rol']) ?></span></td>
                        <td>
                            <a href="gebruiker_bewerken.php?id=<?= $user['idUser'] ?>">Bewerken</a>
                            <?php if ($user['idUser'] != $_SESSION['user_id']): ?>
                                | <a href="gebruiker_verwijderen.php?id=<?= $user['idUser'] ?>" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">Verwijderen</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
