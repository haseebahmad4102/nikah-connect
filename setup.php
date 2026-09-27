<?php
/**
 * NikahConnect Setup Helper
 * Run this ONCE at: http://localhost/nikahconnect/setup.php
 * Then DELETE this file!
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
$messages = [];

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;color:red;padding:30px;max-width:600px;margin:auto">
        <h2>⚠ MySQL Connection Failed</h2>
        <p style="margin-top:10px">' . $conn->connect_error . '</p>
        <p style="margin-top:10px;color:#666">Make sure XAMPP MySQL is started!</p>
    </div>');
}

// Step 1: Create database
$conn->query("CREATE DATABASE IF NOT EXISTS nikahconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$messages[] = ['success', 'Database "nikahconnect" ready'];
$conn->select_db('nikahconnect');
$conn->set_charset('utf8mb4');

// Step 2: Create all tables one by one
$tables = [];

$tables[] = ["users", "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male','female') NOT NULL,
    role ENUM('user','wali','parent','admin') DEFAULT 'user',
    child_id INT NULL,
    guardian_relation VARCHAR(50) NULL,
    is_verified TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    profile_complete TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB"];

$tables[] = ["profiles", "CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    age INT,
    date_of_birth DATE,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Pakistan',
    marital_status ENUM('never_married','divorced','widowed') DEFAULT 'never_married',
    education VARCHAR(150),
    occupation VARCHAR(150),
    monthly_income VARCHAR(100),
    height VARCHAR(20),
    complexion VARCHAR(50),
    sect VARCHAR(100),
    religiosity ENUM('practicing','moderate','learning') DEFAULT 'moderate',
    prayers_per_day ENUM('5_times','3_times','1_time','occasionally') DEFAULT '5_times',
    hijab_niqab VARCHAR(50),
    beard VARCHAR(50),
    languages VARCHAR(200),
    family_type ENUM('joint','nuclear') DEFAULT 'nuclear',
    about_me TEXT,
    about_family TEXT,
    photo VARCHAR(255),
    wali_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["preferences", "CREATE TABLE IF NOT EXISTS preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    pref_age_min INT DEFAULT 18,
    pref_age_max INT DEFAULT 45,
    pref_city VARCHAR(100),
    pref_country VARCHAR(100),
    pref_marital_status VARCHAR(100),
    pref_education VARCHAR(100),
    pref_occupation VARCHAR(100),
    pref_religiosity VARCHAR(100),
    pref_sect VARCHAR(100),
    pref_family_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["walis", "CREATE TABLE IF NOT EXISTS walis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    wali_name VARCHAR(100) NOT NULL,
    wali_relation VARCHAR(50) NOT NULL,
    wali_phone VARCHAR(20),
    wali_email VARCHAR(150),
    wali_consent TINYINT(1) DEFAULT 0,
    consent_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["gallery", "CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    caption VARCHAR(200),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["interests", "CREATE TABLE IF NOT EXISTS interests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending','accepted','success','rejected','withdrawn') DEFAULT 'pending',
    message TEXT,
    wali_notified TINYINT(1) DEFAULT 0,
    sender_parent_approved TINYINT(1) DEFAULT 0,
    receiver_parent_approved TINYINT(1) DEFAULT 0,
    sender_parent_approved_at TIMESTAMP NULL,
    receiver_parent_approved_at TIMESTAMP NULL,
    visit_status ENUM('none','proposed','confirmed') DEFAULT 'none',
    visit_date DATE NULL,
    visit_proposed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_interest (sender_id, receiver_id),
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["messages", "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    is_flagged TINYINT(1) DEFAULT 0,
    wali_can_view TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["notifications", "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB"];

$tables[] = ["admin_logs", "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(200),
    target_user_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB"];

$errors = 0;
foreach ($tables as $t) {
    if ($conn->query($t[1])) {
        $messages[] = ['success', 'Table "' . $t[0] . '" ready'];
    } else {
        $messages[] = ['error', 'Failed to create table "' . $t[0] . '": ' . $conn->error];
        $errors++;
    }
}

// Step 3: Insert sample data
if ($errors === 0) {
    // Correct bcrypt hashes generated fresh
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $userHash  = password_hash('user123',  PASSWORD_DEFAULT);

    $inserts = [
        // Users
        ["INSERT IGNORE INTO users (full_name,email,password,gender,role,is_verified,is_active,profile_complete) VALUES (?,?,?,'male','admin',1,1,1)",
         ['Admin', 'admin@nikahconnect.com', $adminHash]],

        ["INSERT IGNORE INTO users (full_name,email,password,gender,role,is_verified,is_active,profile_complete) VALUES (?,?,?,'male','user',1,1,1)",
         ['Ahmed Ali', 'ahmed@example.com', $userHash]],

        ["INSERT IGNORE INTO users (full_name,email,password,gender,role,is_verified,is_active,profile_complete) VALUES (?,?,?,'female','user',1,1,1)",
         ['Fatima Zahra', 'fatima@example.com', $userHash]],
    ];

    foreach ($inserts as $ins) {
        $stmt = $conn->prepare($ins[0]);
        $stmt->bind_param(str_repeat('s', count($ins[1])), ...$ins[1]);
        if (!$stmt->execute()) {
            $messages[] = ['warning', 'Insert skipped (may already exist): ' . $conn->error];
        }
    }

    // Get user IDs
    $adminId  = $conn->query("SELECT id FROM users WHERE email='admin@nikahconnect.com'")->fetch_assoc()['id'];
    $ahmedId  = $conn->query("SELECT id FROM users WHERE email='ahmed@example.com'")->fetch_assoc()['id'];
    $fatimaId = $conn->query("SELECT id FROM users WHERE email='fatima@example.com'")->fetch_assoc()['id'];

    // Helper to safely escape and quote a value
    $e = function($v) use ($conn) { return "'" . $conn->real_escape_string($v) . "'"; };

    // Profiles
    $profiles = [
        [
            'user_id'       => (int)$ahmedId,
            'age'           => 28,
            'city'          => 'Lahore',
            'country'       => 'Pakistan',
            'marital_status'=> 'never_married',
            'education'     => 'BS Computer Science',
            'occupation'    => 'Software Engineer',
            'height'        => "5'10\"",
            'complexion'    => 'Fair',
            'sect'          => 'Sunni',
            'religiosity'   => 'practicing',
            'prayers_per_day'=> '5_times',
            'hijab_niqab'   => '',
            'beard'         => 'Yes (short)',
            'languages'     => 'Urdu, English',
            'family_type'   => 'nuclear',
            'about_me'      => 'Alhamdulillah, I am a practicing Muslim looking for a pious life partner who shares my Islamic values. I believe in building a family on the foundations of Islam.',
            'about_family'  => 'We are a small, educated and religious family based in Lahore. My parents are very supportive and involved in this process.',
            'wali_approved' => 1,
        ],
        [
            'user_id'       => (int)$fatimaId,
            'age'           => 24,
            'city'          => 'Karachi',
            'country'       => 'Pakistan',
            'marital_status'=> 'never_married',
            'education'     => 'BS Education',
            'occupation'    => 'Teacher',
            'height'        => "5'4\"",
            'complexion'    => 'Fair',
            'sect'          => 'Sunni',
            'religiosity'   => 'practicing',
            'prayers_per_day'=> '5_times',
            'hijab_niqab'   => 'Hijab',
            'beard'         => '',
            'languages'     => 'Urdu, English, Arabic',
            'family_type'   => 'joint',
            'about_me'      => 'Bismillah. I am a modest and practicing Muslimah seeking a righteous life partner. I value deen above all else and wish to build a peaceful Islamic home.',
            'about_family'  => 'We are a joint family with strong Islamic values. My wali (father) is actively involved in this process.',
            'wali_approved' => 1,
        ],
    ];

    foreach ($profiles as $p) {
        $uid = $p['user_id'];
        if ($conn->query("SELECT id FROM profiles WHERE user_id=$uid")->num_rows === 0) {
            $sql = "INSERT INTO profiles (user_id,age,city,country,marital_status,education,occupation,height,complexion,sect,religiosity,prayers_per_day,hijab_niqab,beard,languages,family_type,about_me,about_family,wali_approved) VALUES ("
                . $uid . ','
                . $p['age'] . ','
                . $e($p['city']) . ','
                . $e($p['country']) . ','
                . $e($p['marital_status']) . ','
                . $e($p['education']) . ','
                . $e($p['occupation']) . ','
                . $e($p['height']) . ','
                . $e($p['complexion']) . ','
                . $e($p['sect']) . ','
                . $e($p['religiosity']) . ','
                . $e($p['prayers_per_day']) . ','
                . $e($p['hijab_niqab']) . ','
                . $e($p['beard']) . ','
                . $e($p['languages']) . ','
                . $e($p['family_type']) . ','
                . $e($p['about_me']) . ','
                . $e($p['about_family']) . ','
                . $p['wali_approved']
                . ')';
            $conn->query($sql);
        }
    }

    // Walis
    $walis = [
        [(int)$ahmedId,  'Muhammad Ali', 'Father', '03001234567', 'mali@example.com',   1],
        [(int)$fatimaId, 'Usman Zahra',  'Father', '03007654321', 'uzahra@example.com', 1],
    ];
    foreach ($walis as $w) {
        if ($conn->query("SELECT id FROM walis WHERE user_id={$w[0]}")->num_rows === 0) {
            $conn->query("INSERT INTO walis (user_id,wali_name,wali_relation,wali_phone,wali_email,wali_consent) VALUES ({$w[0]},{$e($w[1])},{$e($w[2])},{$e($w[3])},{$e($w[4])},{$w[5]})");
        }
    }

    // Preferences
    $prefs = [
        [(int)$ahmedId,  20, 28, 'Pakistan', 'practicing', 'Sunni'],
        [(int)$fatimaId, 25, 35, 'Pakistan', 'practicing', 'Sunni'],
    ];
    foreach ($prefs as $p) {
        if ($conn->query("SELECT id FROM preferences WHERE user_id={$p[0]}")->num_rows === 0) {
            $conn->query("INSERT INTO preferences (user_id,pref_age_min,pref_age_max,pref_country,pref_religiosity,pref_sect) VALUES ({$p[0]},{$p[1]},{$p[2]},{$e($p[3])},{$e($p[4])},{$e($p[5])})");
        }
    }

    $messages[] = ['success', 'Sample data inserted — Ahmed Ali &amp; Fatima Zahra profiles ready'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>NikahConnect Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#1a6b3c,#134f2d);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .box{background:#fff;border-radius:20px;padding:40px;max-width:700px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
        .logo{text-align:center;margin-bottom:28px}
        .logo-icon{width:68px;height:68px;background:linear-gradient(135deg,#1a6b3c,#134f2d);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 12px;box-shadow:0 6px 20px rgba(26,107,60,.3)}
        .logo h1{color:#1a6b3c;font-size:24px}
        .logo p{color:#888;font-size:13px;margin-top:4px}
        .msg{padding:9px 14px;border-radius:7px;font-size:13px;margin-bottom:6px;display:flex;align-items:flex-start;gap:8px}
        .msg.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
        .msg.error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
        .msg.warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
        hr{border:none;border-top:1px solid #eee;margin:24px 0}
        .result-banner{text-align:center;padding:24px;border-radius:12px;margin-bottom:20px}
        .result-banner.ok{background:linear-gradient(135deg,rgba(26,107,60,.08),rgba(201,168,76,.05));border:1px solid rgba(26,107,60,.2)}
        .result-banner.fail{background:#fef2f2;border:1px solid #fecaca}
        .creds{background:#f8f4ec;border:1px solid #e8d5a0;border-radius:12px;padding:20px;margin-bottom:20px}
        .creds h3{color:#a8882e;margin-bottom:14px;font-size:15px;display:flex;align-items:center;gap:8px}
        .cred-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e8d5a0;font-size:13px}
        .cred-row:last-child{border:none;padding-bottom:0}
        .cred-label{color:#666}
        .cred-val{font-weight:600;color:#1a6b3c;font-family:monospace;background:#fff;padding:2px 8px;border-radius:4px;border:1px solid #e8d5a0}
        .btns{display:flex;gap:12px;flex-wrap:wrap}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s}
        .btn-green{background:linear-gradient(135deg,#1a6b3c,#134f2d);color:#fff;box-shadow:0 3px 12px rgba(26,107,60,.3)}
        .btn-gold{background:linear-gradient(135deg,#c9a84c,#a8882e);color:#fff;box-shadow:0 3px 12px rgba(201,168,76,.3)}
        .btn:hover{transform:translateY(-1px)}
        .warn-box{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:14px;font-size:12px;color:#991b1b;margin-top:16px}
        .arabic{font-family:'Amiri',serif;font-size:22px;color:#1a6b3c;margin-bottom:8px}
        @import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap');
    </style>
</head>
<body>
<div class="box">
    <div class="logo">
        <div class="logo-icon">☪</div>
        <h1>NikahConnect Setup</h1>
        <p>Islamic Matrimonial Platform — Database Initialization</p>
    </div>

    <!-- Messages -->
    <div style="max-height:280px;overflow-y:auto;margin-bottom:16px">
        <?php foreach ($messages as $m): ?>
        <div class="msg <?= $m[0] ?>">
            <?= $m[0]==='success' ? '✓' : ($m[0]==='error' ? '✗' : '⚠') ?>
            <?= htmlspecialchars($m[1]) ?>
        </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <?php if ($errors === 0): ?>

    <div class="result-banner ok">
        <div style="font-size:40px;margin-bottom:10px">🎉</div>
        <div class="arabic">الْحَمْدُ لِلَّهِ</div>
        <h2 style="color:#1a6b3c;font-size:20px">Setup Complete!</h2>
        <p style="color:#666;font-size:13px;margin-top:6px">Database created, tables ready, and sample data loaded successfully.</p>
    </div>

    <div class="creds">
        <h3>🔑 Login Credentials</h3>
        <div class="cred-row">
            <span class="cred-label">👤 Admin Email</span>
            <span class="cred-val">admin@nikahconnect.com</span>
        </div>
        <div class="cred-row">
            <span class="cred-label">🔒 Admin Password</span>
            <span class="cred-val">admin123</span>
        </div>
        <div class="cred-row">
            <span class="cred-label">👳 Male User</span>
            <span class="cred-val">ahmed@example.com / user123</span>
        </div>
        <div class="cred-row">
            <span class="cred-label">🧕 Female User</span>
            <span class="cred-val">fatima@example.com / user123</span>
        </div>
    </div>

    <div class="btns">
        <a href="http://localhost/nikahconnect/index.php" class="btn btn-green">🏠 Open NikahConnect</a>
        <a href="http://localhost/nikahconnect/login.php" class="btn btn-gold">🔑 Login Now</a>
        <a href="http://localhost/nikahconnect/admin/index.php" class="btn btn-green" style="background:linear-gradient(135deg,#1a1a2e,#2a2a4e)">⚙ Admin Panel</a>
    </div>

    <div class="warn-box">
        <strong>⚠ Security Notice:</strong> Please <strong>delete this setup.php file</strong> after setup to prevent unauthorized database access.
    </div>

    <?php else: ?>

    <div class="result-banner fail">
        <div style="font-size:36px;margin-bottom:10px">⚠</div>
        <h2 style="color:#991b1b"><?= $errors ?> Error(s) Occurred</h2>
        <p style="color:#666;font-size:13px;margin-top:6px">
            Make sure XAMPP MySQL is running, then <a href="setup.php" style="color:#1a6b3c">try again</a>.
        </p>
    </div>

    <div style="font-size:13px;color:#666;background:#f8f8f8;border-radius:8px;padding:16px">
        <strong>Troubleshooting:</strong><br>
        1. Open XAMPP Control Panel → Start "MySQL"<br>
        2. Visit <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a> to verify MySQL is running<br>
        3. Refresh this page
    </div>

    <?php endif; ?>
</div>
</body>
</html>
