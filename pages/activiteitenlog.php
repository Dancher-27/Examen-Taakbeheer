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
require_once __DIR__ . '/../classes/ActivityLog.php';
require_once __DIR__ . '/../classes/User.php';

$db = (new Database())->getConnection();
$logModel = new ActivityLog($db);
$userModel = new User($db);

$userList = $userModel->getAllPlucked();
$filter = $_GET ?? [];
$activityLog = $logModel->getAll($filter);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Activiteitenlog - Taakbeheer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Activiteitenlog</h1>
        <?php

        if (isset($_GET['error'])) {
            ?>
            <span class="error"><?php
            ?></span>
            <?php
        }

        ?>
    </header>

    <form method="GET" class="filter-bar">
        <select name="user" onchange="this.form.submit()">
            <option value="">Alle gebruikers</option>
            <?php
            
            foreach($userList as $userId => $userName) {
                ?><option value="<?php echo $userId; ?>" <?php if($userId == ($filter['user'] ?? 0)) {
                    echo 'selected';
                } ?>><?php echo htmlspecialchars($userName); ?></option><?php
            }

            ?>
        </select>

        <select name="action" onchange="this.form.submit()">
            <option value="">Alle acties</option>
            <option <?php if(($filter['action'] ?? '') == 'aangemaakt') {
                echo 'selected';
            } ?> value="aangemaakt">aangemaakt</option>
            <option <?php if(($filter['action'] ?? '') == 'bewerkt') {
                echo 'selected';
            } ?> value="bewerkt">bewerkt</option>
            <option <?php if(($filter['action'] ?? '') == 'verwijderd') {
                echo 'selected';
            } ?> value="verwijderd">verwijderd</option>
        </select>

        <button type="submit" class="btn-primary">Filteren</button>
    </form>

    <?php if (empty($activityLog)): ?>
        <p class="no-items">
            Geen activiteiten<?php
            echo !empty($filter) ? ' met deze criteria' : '';
            ?> gevonden.</p>
    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Datum en tijd</th>
                    <th>Actitiveit</th>
                    <th>Door</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activityLog as $entry): ?>
                    <tr>
                        <td><?php echo (new DateTime($entry['created_at']))->format('d-m-Y H:i:s'); ?></td>
                        <td><?php echo $entry['entiteit'] . ' ' . $logModel->getRelatedEntityName($entry) . ' ' . $entry['actie']; ?></td>
                        <td><?php echo $userList[$entry['User_idUser']] ?? ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
