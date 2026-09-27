<?php
$pageTitle = 'Dashboard';
require_once 'includes/db.php';
requireLogin();
if(isParent()) { header('Location: '.SITE_URL.'/guardian.php'); exit; }

require_once 'includes/upload.php';
$user = currentUser();
$profile = db()->fetchOne("SELECT * FROM profiles WHERE user_id = ?", [$user['id']]);
$wali = db()->fetchOne("SELECT * FROM walis WHERE user_id = ?", [$user['id']]);

// Stats
$sentInterests = db()->fetchOne("SELECT COUNT(*) as c FROM interests WHERE sender_id = ?", [$user['id']])['c'] ?? 0;
$receivedInterests = db()->fetchOne("SELECT COUNT(*) as c FROM interests WHERE receiver_id = ? AND status = 'pending'", [$user['id']])['c'] ?? 0;
$acceptedMatches = db()->fetchOne("SELECT COUNT(*) as c FROM interests WHERE (sender_id=? OR receiver_id=?) AND status='success'", [$user['id'],$user['id']])['c'] ?? 0;
$unreadMessages = db()->fetchOne("SELECT COUNT(*) as c FROM messages WHERE receiver_id = ? AND is_read = 0", [$user['id']])['c'] ?? 0;

// Suggested matches
$gender_opposite = $user['gender'] === 'male' ? 'female' : 'male';
$suggestions = db()->fetchAll(
    "SELECT u.*, p.* FROM users u
     JOIN profiles p ON u.id = p.user_id
     WHERE u.gender = ? AND u.id != ? AND u.is_active = 1 AND p.wali_approved = 1
     AND u.id NOT IN (SELECT receiver_id FROM interests WHERE sender_id = ?)
     ORDER BY RAND() LIMIT 4",
    [$gender_opposite, $user['id'], $user['id']]
);

// Recent activity
$recentInterests = db()->fetchAll(
    "SELECT i.*, u.full_name, u.gender FROM interests i
     JOIN users u ON i.sender_id = u.id
     WHERE i.receiver_id = ? ORDER BY i.created_at DESC LIMIT 5",
    [$user['id']]
);

// Profile completion percentage
$fields = ['age','city','education','occupation','sect','religiosity','about_me','about_family','height'];
$filled = 0;
if($profile) {
    foreach($fields as $f) { if(!empty($profile[$f])) $filled++; }
}
$completion = $profile ? round(($filled / count($fields)) * 100) : 0;
if($wali) $completion = min(100, $completion + 10);

require_once 'includes/header.php';
?>

<div class="dashboard-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-avatar" style="<?= avatarUrl($profile['photo'] ?? null) ? 'background:none;border:2px solid var(--gold-light);' : '' ?>">
                <?php $sidebarAv = avatarUrl($profile['photo'] ?? null); ?>
                <?php if($sidebarAv): ?>
                <img src="<?= htmlspecialchars($sidebarAv) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                <?php else: ?>
                <?= $user['gender']==='male' ? '👳' : '🧕' ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
            <div style="margin-top:8px">
                <div class="progress-bar"><div class="progress-fill" style="width:<?=$completion?>%"></div></div>
                <div style="font-size:11px;color:var(--text-light);margin-top:4px"><?=$completion?>% Profile Complete</div>
            </div>
        </div>
        <div style="padding:10px 14px 0">
            <a href="<?=SITE_URL?>/upload-photo.php" style="display:flex;align-items:center;gap:8px;padding:9px 14px;background:linear-gradient(135deg,rgba(26,107,60,0.1),rgba(201,168,76,0.07));border:1px solid rgba(26,107,60,0.2);border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;color:var(--primary)">
                <i class="fas fa-camera"></i> Change Profile Picture
            </a>
        </div>
        <div class="sidebar-section-title">Main</div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="matches.php"><i class="fas fa-heart"></i> My Matches
                <?php if($acceptedMatches): ?><span class="badge"><?=$acceptedMatches?></span><?php endif; ?>
            </a>
            <a href="profiles.php"><i class="fas fa-search"></i> Browse Profiles</a>
        </nav>
        <div class="sidebar-section-title">Communication</div>
        <nav class="sidebar-menu">
            <a href="messages.php"><i class="fas fa-comments"></i> Messages
                <?php if($unreadMessages): ?><span class="badge"><?=$unreadMessages?></span><?php endif; ?>
            </a>
            <a href="interests.php"><i class="fas fa-paper-plane"></i> Interests
                <?php if($receivedInterests): ?><span class="badge"><?=$receivedInterests?></span><?php endif; ?>
            </a>
            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        </nav>
        <div class="sidebar-section-title">Account</div>
        <nav class="sidebar-menu">
            <a href="gallery.php"><i class="fas fa-images"></i> My Gallery</a>
            <a href="wali.php"><i class="fas fa-user-shield"></i> Wali (Guardian)</a>
            <a href="preferences.php"><i class="fas fa-sliders-h"></i> Preferences</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php" style="color:#e74c3c"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <?php if(isset($_GET['profile'])): ?>
        <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> Profile saved successfully! JazakAllah Khair.</div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:var(--radius-lg);padding:28px;color:white;margin-bottom:22px;position:relative;overflow:hidden">
            <div style="position:absolute;right:-20px;top:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.05)"></div>
            <div style="font-family:'Amiri',serif;font-size:18px;color:var(--gold-light);margin-bottom:6px">السَّلاَمُ عَلَيْكُمْ</div>
            <h2 style="color:white;font-size:22px;margin-bottom:6px">Welcome, <?= htmlspecialchars(explode(' ',$user['full_name'])[0]) ?>! 🌙</h2>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;margin-bottom:16px">
                <?php if($completion < 70): ?>
                Your profile is <?=$completion?>% complete. Complete it to get more matches!
                <?php else: ?>
                Your profile is active and being shown to compatible matches. May Allah bless your search!
                <?php endif; ?>
            </p>
            <?php if($completion < 100): ?>
            <a href="complete-profile.php" class="btn btn-gold btn-sm"><i class="fas fa-edit"></i> Complete Profile</a>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-heart"></i></div>
                <div class="stat-info">
                    <div class="num"><?= $acceptedMatches ?></div>
                    <div class="label">Active Matches</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-info">
                    <div class="num"><?= $receivedInterests ?></div>
                    <div class="label">New Interests</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
                <div class="stat-info">
                    <div class="num"><?= $unreadMessages ?></div>
                    <div class="label">Unread Messages</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon <?= $wali && $wali['wali_consent'] ? 'green' : 'red' ?>">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <div class="num"><?= $wali ? ($wali['wali_consent'] ? '✓' : '⏳') : '!' ?></div>
                    <div class="label">Wali Status</div>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">

            <!-- Wali Status Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-user-shield"></i> Guardian (Wali) Status</div>
                    <a href="wali.php" class="btn btn-sm btn-outline">Manage</a>
                </div>
                <div class="card-body">
                    <?php if($wali): ?>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
                        <div style="width:48px;height:48px;background:rgba(201,168,76,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;border:2px solid rgba(201,168,76,0.3)">👨</div>
                        <div>
                            <div style="font-weight:600"><?= htmlspecialchars($wali['wali_name']) ?></div>
                            <div style="font-size:13px;color:var(--text-light)"><?= htmlspecialchars($wali['wali_relation']) ?></div>
                        </div>
                        <div style="margin-left:auto">
                            <?php if($wali['wali_consent']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>
                            <?php else: ?>
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="background:rgba(26,107,60,0.05);border-radius:8px;padding:12px;font-size:13px;color:var(--text-medium)">
                        <i class="fas fa-info-circle" style="color:var(--primary)"></i>
                        Your Wali has their own login. They approve your rishtas and can view all your chats. A rishta succeeds only when both guardians approve.
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No Wali added yet. Please add your Guardian details.
                    </div>
                    <a href="complete-profile.php#wali" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> Add Wali</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Interests -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-heart"></i> Recent Interests</div>
                    <a href="interests.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body" style="padding:0">
                    <?php if(empty($recentInterests)): ?>
                    <div style="padding:24px;text-align:center;color:var(--text-light);font-size:14px">
                        <i class="fas fa-heart" style="font-size:28px;margin-bottom:8px;display:block;color:var(--border)"></i>
                        No interests received yet. Complete your profile to get noticed!
                    </div>
                    <?php else: ?>
                    <?php foreach($recentInterests as $int): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;border-bottom:1px solid var(--border)">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:18px;flex-shrink:0">
                            <?= $int['gender']==='male'?'👳':'🧕' ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:14px;font-weight:600"><?= htmlspecialchars($int['full_name']) ?></div>
                            <div style="font-size:12px;color:var(--text-light)"><?= timeAgo($int['created_at']) ?></div>
                        </div>
                        <div>
                            <?php
                            $b = [
                                'pending' => ['warning','Awaiting guardians'],
                                'success' => ['success','Approved'],
                                'rejected'=> ['danger','Rejected'],
                            ][$int['status']] ?? ['info',ucfirst($int['status'])];
                            ?>
                            <span class="badge badge-<?=$b[0]?>"><?=$b[1]?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Suggested Matches -->
        <?php if(!empty($suggestions)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-star"></i> Suggested Matches For You</div>
                <a href="matches.php" class="btn btn-sm btn-primary">View All Matches</a>
            </div>
            <div class="card-body">
                <div class="profiles-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
                    <?php foreach($suggestions as $s): ?>
                    <div class="profile-card">
                        <div class="profile-card-header" style="padding:20px 16px 40px">
                            <div class="profile-avatar" style="width:60px;height:60px;font-size:24px">
                                <?php $sav = avatarUrl($s['photo'] ?? null); ?>
                                <?php if($sav): ?><img src="<?= htmlspecialchars($sav) ?>" alt="Profile"><?php else: ?><?= $s['gender']==='male'?'👳':'🧕' ?><?php endif; ?>
                            </div>
                            <div class="profile-name" style="font-size:15px"><?= htmlspecialchars($s['full_name']) ?></div>
                            <div class="profile-location" style="font-size:12px"><?= htmlspecialchars($s['city'] ?? '') ?></div>
                            <?php if($s['wali_approved']): ?>
                            <div class="wali-badge"><i class="fas fa-shield-alt"></i> Wali OK</div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-card-body" style="padding:12px 14px 14px">
                            <span class="profile-info-pill" style="font-size:11px"><i class="fas fa-birthday-cake"></i> <?=$s['age']?> yrs</span>
                            <span class="profile-info-pill" style="font-size:11px"><i class="fas fa-mosque"></i> <?= ucfirst($s['religiosity']??'') ?></span>
                            <div class="profile-actions" style="margin-top:10px">
                                <a href="view-profile.php?id=<?=$s['user_id']?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">View</a>
                                <a href="send-interest.php?to=<?=$s['user_id']?>" class="btn btn-primary btn-sm"><i class="fas fa-heart"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Islamic reminder -->
        <div style="background:linear-gradient(135deg,rgba(201,168,76,0.08),rgba(26,107,60,0.05));border:1px solid rgba(201,168,76,0.2);border-radius:var(--radius);padding:20px;margin-top:22px;text-align:center">
            <div style="font-family:'Amiri',serif;font-size:22px;color:var(--primary);direction:rtl;margin-bottom:8px">تَزَوَّجُوا الْوَدُودَ الْوَلُودَ</div>
            <p style="font-size:13px;color:var(--text-medium);font-style:italic">"Marry the loving and fertile woman" — Prophet Muhammad ﷺ (Sunan Abu Dawud)</p>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
