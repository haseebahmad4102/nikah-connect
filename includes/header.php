<?php
require_once __DIR__ . '/db.php';
$user = isLoggedIn() ? currentUser() : null;
$notifCount = $user ? count(getUnreadNotifications($user['id'])) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= SITE_NAME ?></title>
    <meta name="description" content="NikahConnect - A Halal, Shariah-compliant Islamic matrimonial platform for Muslim families">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Amiri:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
            <div class="nav-logo-icon">☪</div>
            <div class="nav-logo-text">
                <span class="site-name">NikahConnect</span>
                <span class="tagline">Islamic Matrimonial</span>
            </div>
        </a>

        <div class="nav-links">
            <a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage=='index.php'?'active':'' ?>">Home</a>
            <?php if($user && $user['role']==='parent'): ?>
            <a href="<?= SITE_URL ?>/guardian.php" class="<?= $currentPage=='guardian.php'?'active':'' ?>">Guardian Panel</a>
            <?php else: ?>
            <a href="<?= SITE_URL ?>/profiles.php" class="<?= $currentPage=='profiles.php'?'active':'' ?>">Browse</a>
            <?php if($user): ?>
            <a href="<?= SITE_URL ?>/matches.php" class="<?= $currentPage=='matches.php'?'active':'' ?>">Matches</a>
            <a href="<?= SITE_URL ?>/messages.php" class="<?= $currentPage=='messages.php'?'active':'' ?>">Messages</a>
            <?php if($user['role']==='admin'): ?>
            <a href="<?= SITE_URL ?>/admin/index.php" class="text-gold">Admin Panel</a>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/about.php">About</a>
        </div>

        <div class="nav-actions">
            <?php if($user): ?>
            <a href="<?= SITE_URL ?>/notifications.php" class="notif-btn" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if($notifCount > 0): ?>
                <span class="notif-count"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>
            <div style="position:relative">
                <button onclick="toggleUserMenu()" class="btn btn-outline btn-sm" style="gap:8px">
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>
                    <i class="fas fa-chevron-down" style="font-size:10px"></i>
                </button>
                <div id="userMenu" class="hidden" style="position:absolute;right:0;top:calc(100% + 8px);background:white;border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-hover);width:200px;z-index:999;overflow:hidden">
                    <?php if($user['role']==='parent'): ?>
                    <a href="<?= SITE_URL ?>/guardian.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-user-shield" style="color:var(--primary);width:16px"></i> Guardian Panel
                    </a>
                    <a href="<?= SITE_URL ?>/notifications.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-bell" style="color:var(--primary);width:16px"></i> Notifications
                    </a>
                    <a href="<?= SITE_URL ?>/logout.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:#e74c3c;text-decoration:none">
                        <i class="fas fa-sign-out-alt" style="width:16px"></i> Logout
                    </a>
                    <?php else: ?>
                    <a href="<?= SITE_URL ?>/profile.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-user" style="color:var(--primary);width:16px"></i> My Profile
                    </a>
                    <a href="<?= SITE_URL ?>/upload-photo.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-camera" style="color:var(--primary);width:16px"></i> Profile Picture
                    </a>
                    <a href="<?= SITE_URL ?>/gallery.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-images" style="color:var(--primary);width:16px"></i> My Gallery
                    </a>
                    <a href="<?= SITE_URL ?>/dashboard.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-th-large" style="color:var(--primary);width:16px"></i> Dashboard
                    </a>
                    <a href="<?= SITE_URL ?>/settings.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-dark);text-decoration:none;border-bottom:1px solid var(--border)">
                        <i class="fas fa-cog" style="color:var(--primary);width:16px"></i> Settings
                    </a>
                    <a href="<?= SITE_URL ?>/logout.php" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:#e74c3c;text-decoration:none">
                        <i class="fas fa-sign-out-alt" style="width:16px"></i> Logout
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline btn-sm">Login</a>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Join Free</a>
            <?php endif; ?>
            <button class="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<script>
function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('userMenu');
    if (menu && !e.target.closest('[onclick="toggleUserMenu()"]') && !menu.contains(e.target)) {
        menu.classList.add('hidden');
    }
});
function toggleMobileMenu() {
    document.querySelector('.nav-links').classList.toggle('hidden');
}
</script>
