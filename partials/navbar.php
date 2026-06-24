<?php
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="navbar-brand">Taakbeheer</div>
    <ul class="navbar-links">
        <?php if ($isAdmin): ?>
            <li><a href="admin_dashboard.php" class="<?= $currentPage === 'admin_dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="gebruikers.php" class="<?= $currentPage === 'gebruikers.php' ? 'active' : '' ?>">Gebruikers</a></li>
            <li><a href="categorieen.php" class="<?= $currentPage === 'categorieen.php' ? 'active' : '' ?>">Categorieën</a></li>
            <li><a href="taken_admin.php" class="<?= $currentPage === 'taken_admin.php' ? 'active' : '' ?>">Taken</a></li>
        <?php else: ?>
            <li><a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="tasks.php" class="<?= $currentPage === 'tasks.php' ? 'active' : '' ?>">Mijn taken</a></li>
        <?php endif; ?>
    </ul>
    <a href="logout.php" class="navbar-logout">Uitloggen</a>
</nav>
