<?php
$pageTitle = 'Login';
require_once 'includes/db.php';

if(isLoggedIn()) { header('Location: '.SITE_URL.'/dashboard.php'); exit; }

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if(!$email || !$pass) {
        $error = 'Please enter your email and password.';
    } else {
        $user = db()->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
        if($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['gender'] = $user['gender'];
            if($user['role']==='admin') $default = SITE_URL.'/admin/index.php';
            elseif($user['role']==='parent') $default = SITE_URL.'/guardian.php';
            else $default = SITE_URL.'/dashboard.php';
            $redirect = $_GET['redirect'] ?? $default;
            header('Location: '.$redirect);
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NikahConnect</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="logo-icon">☪</div>
            <h2>Welcome Back</h2>
            <p>Login to your NikahConnect account</p>
        </div>
        <div class="auth-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>

        <?php if($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['registered'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Registration successful! Please login.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="email">Email Address</label>
                <div class="input-wrap has-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="your@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrap has-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Your password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="form-divider">New to NikahConnect?</div>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-gold btn-block">
                <i class="fas fa-user-plus"></i> Create Free Profile
            </a>
        </form>
        <div style="text-align:center;margin-top:16px;font-size:11px;color:var(--text-light)">
            <a href="<?= SITE_URL ?>/index.php">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
