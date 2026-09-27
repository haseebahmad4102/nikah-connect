<?php
$pageTitle = 'Settings';
require_once 'includes/db.php';
requireLogin();
$user = currentUser();
$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';
    if($action === 'update_name') {
        $name = trim($_POST['full_name'] ?? '');
        if($name) { db()->execute("UPDATE users SET full_name=? WHERE id=?", [$name, $user['id']]); $success = 'Name updated!'; }
    } elseif($action === 'change_password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if(password_verify($old, $user['password']) && strlen($new)>=6) {
            db()->execute("UPDATE users SET password=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            $success = 'Password changed!';
        } else { $error = 'Old password incorrect or new password too short.'; }
    } elseif($action === 'toggle_active') {
        $newStatus = $user['is_active'] ? 0 : 1;
        db()->execute("UPDATE users SET is_active=? WHERE id=?", [$newStatus, $user['id']]);
        $success = 'Profile visibility updated!';
    }
    $user = currentUser();
}

require_once 'includes/header.php';
?>
<div class="page-header">
    <div class="container"><h1><i class="fas fa-cog"></i> Settings</h1></div>
</div>
<div class="container" style="padding:28px 20px;max-width:680px">
    <?php if($success): ?><div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error mb-3"><i class="fas fa-exclamation-circle"></i> <?=$error?></div><?php endif; ?>

    <div class="card mb-3">
        <div class="card-header"><div class="card-title"><i class="fas fa-user"></i> Account Information</div></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_name">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?=htmlspecialchars($user['full_name'])?>">
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" value="<?=htmlspecialchars($user['email'])?>" disabled style="background:var(--cream)">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Update Name</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><div class="card-title"><i class="fas fa-lock"></i> Change Password</div></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="input-group"><label>Current Password</label><input type="password" name="old_password" required></div>
                <div class="input-group"><label>New Password</label><input type="password" name="new_password" required minlength="6"></div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-key"></i> Change Password</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><div class="card-title"><i class="fas fa-eye"></i> Profile Visibility</div></div>
        <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div style="font-size:14px;font-weight:500">Profile Active</div>
                    <div style="font-size:13px;color:var(--text-light)">Current status: <strong style="color:<?=$user['is_active']?'var(--primary)':'#e74c3c'?>"><?=$user['is_active']?'Visible':'Hidden'?></strong></div>
                </div>
                <form method="POST"><input type="hidden" name="action" value="toggle_active">
                    <button type="submit" class="btn <?=$user['is_active']?'btn-danger':'btn-primary'?> btn-sm"><?=$user['is_active']?'Hide Profile':'Show Profile'?></button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display:flex;gap:10px">
                <a href="complete-profile.php" class="btn btn-outline"><i class="fas fa-edit"></i> Edit Full Profile</a>
                <a href="preferences.php" class="btn btn-outline"><i class="fas fa-sliders-h"></i> Match Preferences</a>
                <a href="wali.php" class="btn btn-outline"><i class="fas fa-user-shield"></i> Wali Settings</a>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
