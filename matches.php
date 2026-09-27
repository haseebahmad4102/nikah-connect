<?php
$pageTitle = 'My Matches';
require_once 'includes/db.php';
requireLogin();
if(isParent()) { header('Location: '.SITE_URL.'/guardian.php'); exit; }

$user = currentUser();
$myProfile = db()->fetchOne("SELECT * FROM profiles WHERE user_id=?", [$user['id']]);
$myPrefs = db()->fetchOne("SELECT * FROM preferences WHERE user_id=?", [$user['id']]);

$gender_opposite = $user['gender'] === 'male' ? 'female' : 'male';

// Build smart matching query
$where = ["u.gender = ?", "u.id != ?", "u.is_active = 1", "u.role = 'user'"];
$params = [$gender_opposite, $user['id']];

if($myProfile) {
    if($myProfile['sect']) { $where[] = "p.sect = ?"; $params[] = $myProfile['sect']; }
}
if($myPrefs) {
    if($myPrefs['pref_age_min']) { $where[] = "p.age >= ?"; $params[] = $myPrefs['pref_age_min']; }
    if($myPrefs['pref_age_max']) { $where[] = "p.age <= ?"; $params[] = $myPrefs['pref_age_max']; }
    if($myPrefs['pref_country']) { $where[] = "p.country = ?"; $params[] = $myPrefs['pref_country']; }
    if($myPrefs['pref_religiosity']) { $where[] = "p.religiosity = ?"; $params[] = $myPrefs['pref_religiosity']; }
}

$matches = db()->fetchAll(
    "SELECT u.*, p.* FROM users u JOIN profiles p ON u.id = p.user_id WHERE " . implode(' AND ', $where) . " ORDER BY p.wali_approved DESC, RAND()",
    $params
);

// Accepted matches (already connected)
$connected = db()->fetchAll(
    "SELECT u.*, p.*, i.id as interest_id FROM interests i
     JOIN users u ON (CASE WHEN i.sender_id=? THEN i.receiver_id ELSE i.sender_id END) = u.id
     JOIN profiles p ON u.id = p.user_id
     WHERE (i.sender_id=? OR i.receiver_id=?) AND i.status IN ('pending','success')",
    [$user['id'], $user['id'], $user['id']]
);

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-heart"></i> My Matches</h1>
        <p>Smart preference-based matches for you</p>
    </div>
</div>

<div class="container" style="padding:28px 20px">

    <!-- Connected Matches -->
    <?php if(!empty($connected)): ?>
    <div class="card mb-3">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-handshake"></i> Active Connections (<?=count($connected)?>)</div>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
                <?php foreach($connected as $c): ?>
                <div style="background:linear-gradient(135deg,rgba(26,107,60,0.06),rgba(201,168,76,0.04));border:1px solid rgba(26,107,60,0.2);border-radius:var(--radius);padding:16px;display:flex;gap:12px;align-items:center">
                    <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                        <?=$c['gender']==='male'?'👳':'🧕'?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600"><?=htmlspecialchars($c['full_name'])?></div>
                        <div style="font-size:12px;color:var(--text-light)"><?=htmlspecialchars($c['city']??'')?></div>
                        <div style="display:flex;gap:6px;margin-top:8px">
                            <a href="messages.php?with=<?=$c['id']?>" class="btn btn-primary btn-sm" style="font-size:11px;padding:4px 10px"><i class="fas fa-comments"></i> Chat</a>
                            <a href="view-profile.php?id=<?=$c['id']?>" class="btn btn-outline btn-sm" style="font-size:11px;padding:4px 10px"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Suggested Matches -->
    <div class="card-header" style="background:transparent;border:none;padding:0 0 16px">
        <div class="card-title" style="font-size:18px"><i class="fas fa-star" style="color:var(--gold)"></i> Suggested Matches (<?=count($matches)?>)</div>
        <div style="font-size:13px;color:var(--text-medium)">Based on your preferences, sect, and Islamic values</div>
    </div>

    <?php if(empty($matches)): ?>
    <div style="text-align:center;padding:60px;background:white;border-radius:var(--radius);border:1px solid var(--border)">
        <div style="font-size:48px;margin-bottom:16px">🔍</div>
        <h3>No matches found with current preferences</h3>
        <p style="color:var(--text-medium);margin-top:8px">Try updating your preferences or browse all profiles.</p>
        <div style="display:flex;gap:12px;justify-content:center;margin-top:16px">
            <a href="preferences.php" class="btn btn-outline"><i class="fas fa-sliders-h"></i> Update Preferences</a>
            <a href="profiles.php" class="btn btn-primary"><i class="fas fa-search"></i> Browse All</a>
        </div>
    </div>
    <?php else: ?>
    <div class="profiles-grid">
        <?php foreach($matches as $m): ?>
        <?php
        // Calculate match score
        $score = 50;
        if($myProfile) {
            if(($myProfile['sect']??'') === ($m['sect']??'')) $score += 20;
            if(($myProfile['religiosity']??'') === ($m['religiosity']??'')) $score += 15;
            if(($myProfile['country']??'') === ($m['country']??'')) $score += 10;
            if(($myProfile['family_type']??'') === ($m['family_type']??'')) $score += 5;
        }
        $score = min(99, $score);
        ?>
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-avatar"><?=$m['gender']==='male'?'👳':'🧕'?></div>
                <div class="profile-name"><?=htmlspecialchars($m['full_name'])?></div>
                <div class="profile-location"><i class="fas fa-map-marker-alt"></i> <?=htmlspecialchars($m['city']??'')?></div>
                <?php if($m['wali_approved']): ?>
                <div class="wali-badge"><i class="fas fa-shield-alt"></i> Wali OK</div>
                <?php endif; ?>
            </div>
            <div class="profile-card-body">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;background:rgba(26,107,60,0.06);border-radius:8px;padding:8px 12px">
                    <span style="font-size:12px;color:var(--text-medium)">Match Score</span>
                    <span style="font-size:18px;font-weight:700;color:var(--primary)"><?=$score?>%</span>
                </div>
                <span class="profile-info-pill"><i class="fas fa-birthday-cake"></i> <?=$m['age']?> yrs</span>
                <span class="profile-info-pill"><i class="fas fa-mosque"></i> <?=ucfirst($m['religiosity']??'')?></span>
                <span class="profile-info-pill"><i class="fas fa-moon"></i> <?=htmlspecialchars($m['sect']??'')?></span>
                <?php if($m['occupation']): ?><span class="profile-info-pill"><i class="fas fa-briefcase"></i> <?=htmlspecialchars($m['occupation'])?></span><?php endif; ?>
                <?php if($m['about_me']): ?>
                <p class="profile-about" style="margin-top:10px"><?=htmlspecialchars($m['about_me'])?></p>
                <?php endif; ?>
                <div class="profile-actions">
                    <a href="view-profile.php?id=<?=$m['user_id']?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center"><i class="fas fa-eye"></i> View</a>
                    <a href="send-interest.php?to=<?=$m['user_id']?>" class="btn btn-gold btn-sm"><i class="fas fa-heart"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
