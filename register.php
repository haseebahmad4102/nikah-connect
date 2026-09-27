<?php
$pageTitle = 'Register';
require_once 'includes/db.php';

if(isLoggedIn()) { header('Location: '.SITE_URL.'/dashboard.php'); exit; }

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Parent (Wali / Guardian) account fields
    $pName = trim($_POST['parent_name'] ?? '');
    $pRelation = trim($_POST['parent_relation'] ?? '');
    $pEmail = trim($_POST['parent_email'] ?? '');
    $pPass = $_POST['parent_password'] ?? '';

    if(!$name || !$email || !$pass || !$gender) {
        $error = 'All your account fields are required.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif(strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif($pass !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif(!in_array($gender, ['male','female'])) {
        $error = 'Please select a valid gender.';
    } elseif(!$pName || !$pRelation || !$pEmail || !$pPass) {
        $error = 'All Guardian (parent) account fields are required.';
    } elseif(!filter_var($pEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid Guardian email address.';
    } elseif(strlen($pPass) < 6) {
        $error = 'Guardian password must be at least 6 characters.';
    } elseif(strcasecmp($email, $pEmail) === 0) {
        $error = 'Your email and your Guardian\'s email must be different.';
    } else {
        $existing = db()->fetchOne("SELECT id FROM users WHERE email = ? OR email = ?", [$email, $pEmail]);
        if($existing) {
            $error = 'An account with one of these emails already exists. <a href="login.php">Login here</a>.';
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $userId = db()->insert(
                "INSERT INTO users (full_name, email, password, gender, is_active) VALUES (?,?,?,?,1)",
                [$name, $email, $hashed, $gender]
            );
            if($userId) {
                // Create the linked parent (guardian) account
                $pHashed = password_hash($pPass, PASSWORD_DEFAULT);
                db()->insert(
                    "INSERT INTO users (full_name, email, password, gender, role, child_id, guardian_relation, is_verified, is_active, profile_complete)
                     VALUES (?,?,?,?, 'parent', ?, ?, 1, 1, 1)",
                    [$pName, $pEmail, $pHashed, $gender, $userId, $pRelation]
                );
                // Keep a wali record in sync for legacy displays
                db()->insert(
                    "INSERT INTO walis (user_id, wali_name, wali_relation, wali_email, wali_consent) VALUES (?,?,?,?,1)",
                    [$userId, $pName, $pRelation, $pEmail]
                );
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'user';
                $_SESSION['gender'] = $gender;
                header('Location: '.SITE_URL.'/complete-profile.php?new=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NikahConnect</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-box" style="max-width:520px">
        <div class="auth-logo">
            <div class="logo-icon">☪</div>
            <h2>NikahConnect</h2>
            <p>Create your halal profile — it's free</p>
        </div>
        <div class="auth-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>

        <?php if($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="input-group">
                <label>I am a <span class="required">*</span></label>
                <div class="gender-select">
                    <div class="gender-option">
                        <input type="radio" name="gender" id="male" value="male" <?= ($_POST['gender']??'')=='male'?'checked':'' ?>>
                        <label class="gender-label" for="male">
                            <span class="icon">👳</span>
                            <span class="text">Brother (Male)</span>
                        </label>
                    </div>
                    <div class="gender-option">
                        <input type="radio" name="gender" id="female" value="female" <?= ($_POST['gender']??'')=='female'?'checked':'' ?>>
                        <label class="gender-label" for="female">
                            <span class="icon">🧕</span>
                            <span class="text">Sister (Female)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <div class="input-wrap has-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="full_name" name="full_name" placeholder="Muhammad Ali / Fatima Zahra"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="input-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <div class="input-wrap has-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="your@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group" style="margin-bottom:0">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="input-wrap has-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                    </div>
                </div>
                <div class="input-group" style="margin-bottom:0">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <div class="input-wrap has-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                    </div>
                </div>
            </div>
            <div id="passMatch" style="font-size:12px;margin:6px 0 16px;display:none"></div>

            <div class="form-divider" style="margin-top:8px">Guardian (Wali) Account</div>
            <div style="background:rgba(26,107,60,0.06);border:1px solid rgba(26,107,60,0.2);border-radius:8px;padding:12px;margin-bottom:18px;font-size:12px;color:var(--text-medium)">
                <i class="fas fa-user-shield" style="color:var(--primary)"></i>
                <strong>Guardian approval is required.</strong> Create a separate login for your Wali (father/brother/guardian).
                On NikahConnect, <strong>only the guardians approve a rishta</strong> — you cannot approve on your own.
                Your guardian will also be able to view your chats.
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label for="parent_name">Guardian's Full Name <span class="required">*</span></label>
                    <div class="input-wrap has-icon">
                        <i class="fas fa-user-shield"></i>
                        <input type="text" id="parent_name" name="parent_name" placeholder="e.g. Muhammad Ali"
                               value="<?= htmlspecialchars($_POST['parent_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="parent_relation">Relation to You <span class="required">*</span></label>
                    <select id="parent_relation" name="parent_relation" required>
                        <option value="">Select</option>
                        <?php foreach(['Father','Mother','Brother','Uncle (Paternal)','Uncle (Maternal)','Grandfather','Guardian'] as $r): ?>
                        <option value="<?=$r?>" <?= ($_POST['parent_relation']??'')===$r?'selected':'' ?>><?=$r?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="input-group" style="margin-bottom:0">
                    <label for="parent_email">Guardian's Email <span class="required">*</span></label>
                    <div class="input-wrap has-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="parent_email" name="parent_email" placeholder="guardian@email.com"
                               value="<?= htmlspecialchars($_POST['parent_email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="input-group" style="margin-bottom:0">
                    <label for="parent_password">Guardian's Password <span class="required">*</span></label>
                    <div class="input-wrap has-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="parent_password" name="parent_password" placeholder="Min. 6 characters" required>
                    </div>
                </div>
            </div>
            <div style="font-size:11px;color:var(--text-light);margin:6px 0 16px">Share these credentials with your guardian so they can log in separately.</div>

            <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.25);border-radius:8px;padding:12px;margin-bottom:18px;font-size:12px;color:var(--text-medium)">
                <i class="fas fa-shield-alt" style="color:var(--gold-dark)"></i>
                <strong>Islamic Promise:</strong> We are committed to a Shariah-compliant platform. By registering, you agree to maintain Islamic conduct and involve your Wali (Guardian) in the process.
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-user-plus"></i> Create My Halal Profile
            </button>

            <div class="form-divider">Already have an account?</div>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline btn-block">
                <i class="fas fa-sign-in-alt"></i> Login to Your Account
            </a>
        </form>

        <div style="text-align:center;margin-top:16px;font-size:11px;color:var(--text-light)">
            <a href="<?= SITE_URL ?>/index.php">← Back to Home</a>
        </div>
    </div>
</div>
<script>
const pass = document.getElementById('password');
const confirm = document.getElementById('confirm_password');
const msg = document.getElementById('passMatch');
confirm.addEventListener('input', function() {
    msg.style.display = 'block';
    if(pass.value === confirm.value) {
        msg.innerHTML = '<i class="fas fa-check" style="color:#166534"></i> <span style="color:#166534">Passwords match</span>';
    } else {
        msg.innerHTML = '<i class="fas fa-times" style="color:#991b1b"></i> <span style="color:#991b1b">Passwords do not match</span>';
    }
});
</script>
</body>
</html>
