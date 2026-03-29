<?php require_once 'branding.php'; ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <?php if ($brand_logo): ?>
            <img src="<?= $brand_logo ?>" alt="Logo" style="width:28px; height:28px; border-radius:6px;">
        <?php else: ?>
            <i class="fa-solid fa-chart-line icon-gradient"></i>
        <?php endif; ?>
        <span><?= $brand_name ?></span>
    </div>

    <div class="nav-section">
        <div class="nav-title">Analytics</div>
        <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-border-all"></i> Overview</a>
        <a href="clicks.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'clicks.php' ? 'active' : '' ?>"><i class="fa-solid fa-mouse-pointer"></i> Click Log</a>
    </div>

    <div class="nav-section">
        <div class="nav-title">Links</div>
        <a href="create.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'create.php' ? 'active' : '' ?>"><i class="fa-solid fa-bolt"></i> Link Builder</a>
        <a href="links.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'links.php' ? 'active' : '' ?>"><i class="fa-solid fa-link"></i> My Links</a>
    </div>

    <div class="nav-section">
        <div class="nav-title">Account</div>
        <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i> Settings</a>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link" style="color:#ef4444;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
    </div>
</aside>
