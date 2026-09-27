<?php
$pageTitle = 'Browse Profiles';
require_once 'includes/db.php';
require_once 'includes/upload.php';

$user = isLoggedIn() ? currentUser() : null;

// Filters
$gender = $_GET['gender'] ?? ($user ? ($user['gender']==='male'?'female':'male') : '');
$country = $_GET['country'] ?? '';
$age_min = (int)($_GET['age_min'] ?? 18);
$age_max = (int)($_GET['age_max'] ?? 60);
$religiosity = $_GET['religiosity'] ?? '';
$sect = $_GET['sect'] ?? '';
$marital = $_GET['marital_status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = ["u.is_active = 1", "u.role = 'user'"];
$params = [];

if($gender) { $where[] = "u.gender = ?"; $params[] = $gender; }
if($country) { $where[] = "p.country = ?"; $params[] = $country; }
if($age_min) { $where[] = "p.age >= ?"; $params[] = $age_min; }
if($age_max) { $where[] = "p.age <= ?"; $params[] = $age_max; }
if($religiosity) { $where[] = "p.religiosity = ?"; $params[] = $religiosity; }
if($sect) { $where[] = "p.sect = ?"; $params[] = $sect; }
if($marital) { $where[] = "p.marital_status = ?"; $params[] = $marital; }
if($search) { $where[] = "(u.full_name LIKE ? OR p.city LIKE ? OR p.occupation LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereStr = 'WHERE ' . implode(' AND ', $where);
$profiles = db()->fetchAll(
    "SELECT u.id, u.full_name, u.gender, u.created_at, p.* FROM users u
     JOIN profiles p ON u.id = p.user_id
     $whereStr
     ORDER BY u.created_at DESC",
    $params
);

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="<?=SITE_URL?>/index.php">Home</a> <span>›</span> Browse Profiles</div>
        <h1><i class="fas fa-search"></i> Browse Profiles</h1>
        <p><?= count($profiles) ?> profiles found — all Guardian-approved & verified</p>
    </div>
</div>

<div class="container" style="padding:28px 20px">
    <div style="display:grid;grid-template-columns:280px 1fr;gap:24px">

        <!-- Filters Sidebar -->
        <div>
            <div class="card" style="position:sticky;top:88px">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-filter"></i> Filter Profiles</div>
                    <a href="profiles.php" style="font-size:12px;color:var(--primary)">Reset</a>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <div class="input-group">
                            <label>Search</label>
                            <div class="input-wrap has-icon">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" placeholder="Name, city, profession..." value="<?=htmlspecialchars($search)?>">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Looking For</label>
                            <select name="gender">
                                <option value="">Any</option>
                                <option value="male" <?=$gender==='male'?'selected':''?>>Brothers (Male)</option>
                                <option value="female" <?=$gender==='female'?'selected':''?>>Sisters (Female)</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Age Range</label>
                            <div style="display:flex;gap:8px;align-items:center">
                                <input type="number" name="age_min" value="<?=$age_min?>" min="18" max="70" style="width:70px">
                                <span style="color:var(--text-light);font-size:13px">to</span>
                                <input type="number" name="age_max" value="<?=$age_max?>" min="18" max="70" style="width:70px">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Country</label>
                            <select name="country">
                                <option value="">Any Country</option>
                                <?php foreach(['Pakistan','India','Bangladesh','UAE','Saudi Arabia','UK','USA','Canada'] as $c): ?>
                                <option value="<?=$c?>" <?=$country===$c?'selected':''?>><?=$c?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Religiosity</label>
                            <select name="religiosity">
                                <option value="">Any</option>
                                <option value="practicing" <?=$religiosity==='practicing'?'selected':''?>>Practicing</option>
                                <option value="moderate" <?=$religiosity==='moderate'?'selected':''?>>Moderate</option>
                                <option value="learning" <?=$religiosity==='learning'?'selected':''?>>Learning</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Sect / Madhab</label>
                            <select name="sect">
                                <option value="">Any</option>
                                <?php foreach(['Sunni','Shia','Deobandi','Barelvi','Salafi','Hanafi'] as $s): ?>
                                <option value="<?=$s?>" <?=$sect===$s?'selected':''?>><?=$s?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Marital Status</label>
                            <select name="marital_status">
                                <option value="">Any</option>
                                <option value="never_married" <?=$marital==='never_married'?'selected':''?>>Never Married</option>
                                <option value="divorced" <?=$marital==='divorced'?'selected':''?>>Divorced</option>
                                <option value="widowed" <?=$marital==='widowed'?'selected':''?>>Widowed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profiles List -->
        <div>
            <?php if(empty($profiles)): ?>
            <div style="text-align:center;padding:60px 20px;background:white;border-radius:var(--radius);border:1px solid var(--border)">
                <div style="font-size:48px;margin-bottom:16px">🔍</div>
                <h3>No profiles found</h3>
                <p style="color:var(--text-medium);margin-top:8px">Try adjusting your filters or <a href="profiles.php" style="color:var(--primary)">reset</a> to see all profiles.</p>
            </div>
            <?php else: ?>
            <div class="profiles-grid">
                <?php foreach($profiles as $p): ?>
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="profile-avatar">
                            <?php $av = avatarUrl($p['photo'] ?? null); ?>
                            <?php if($av): ?><img src="<?= htmlspecialchars($av) ?>" alt="Profile"><?php else: ?><?= $p['gender']==='male'?'👳':'🧕' ?><?php endif; ?>
                        </div>
                        <div class="profile-name"><?= htmlspecialchars($p['full_name']) ?></div>
                        <div class="profile-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['city']??'N/A') ?>, <?= htmlspecialchars($p['country']??'') ?></div>
                        <?php if($p['wali_approved']): ?>
                        <div class="wali-badge"><i class="fas fa-shield-alt"></i> Wali Approved</div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-card-body">
                        <div>
                            <span class="profile-info-pill"><i class="fas fa-birthday-cake"></i> <?=$p['age']?> yrs</span>
                            <span class="profile-info-pill"><i class="fas fa-mosque"></i> <?=ucfirst($p['religiosity']??'')?></span>
                            <?php if($p['education']): ?>
                            <span class="profile-info-pill"><i class="fas fa-graduation-cap"></i> <?=htmlspecialchars($p['education'])?></span>
                            <?php endif; ?>
                            <?php if($p['occupation']): ?>
                            <span class="profile-info-pill"><i class="fas fa-briefcase"></i> <?=htmlspecialchars($p['occupation'])?></span>
                            <?php endif; ?>
                            <?php if($p['marital_status']): ?>
                            <span class="profile-info-pill"><i class="fas fa-ring"></i> <?=ucwords(str_replace('_',' ',$p['marital_status']))?></span>
                            <?php endif; ?>
                        </div>
                        <?php if($p['about_me']): ?>
                        <p class="profile-about"><?=htmlspecialchars($p['about_me'])?></p>
                        <?php endif; ?>
                        <div class="profile-actions">
                            <a href="view-profile.php?id=<?=$p['user_id']?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <?php if($user && $_SESSION['user_id'] != $p['user_id']): ?>
                            <a href="send-interest.php?to=<?=$p['user_id']?>" class="btn btn-gold btn-sm" title="Send Interest">
                                <i class="fas fa-heart"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
