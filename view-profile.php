<?php
require_once 'includes/db.php';
require_once 'includes/upload.php';

$id = (int)($_GET['id'] ?? 0);
if(!$id) { header('Location: '.SITE_URL.'/profiles.php'); exit; }

$profileUser = db()->fetchOne("SELECT u.*, p.* FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ? AND u.is_active = 1", [$id]);
if(!$profileUser) { header('Location: '.SITE_URL.'/profiles.php'); exit; }

$pageTitle = htmlspecialchars($profileUser['full_name']) . "'s Profile";

$currentUser = isLoggedIn() ? currentUser() : null;
$myInterest = null;
if($currentUser) {
    $myInterest = db()->fetchOne("SELECT * FROM interests WHERE sender_id = ? AND receiver_id = ?", [$currentUser['id'], $id]);
}

$wali = db()->fetchOne("SELECT wali_name, wali_relation FROM walis WHERE user_id = ?", [$id]);
$galleryPhotos = db()->fetchAll("SELECT * FROM gallery WHERE user_id=? ORDER BY created_at ASC", [$id]);
$avatarUrl = avatarUrl($profileUser['photo'] ?? null);

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="<?=SITE_URL?>/index.php">Home</a> <span>›</span> <a href="<?=SITE_URL?>/profiles.php">Profiles</a> <span>›</span> <?=htmlspecialchars($profileUser['full_name'])?></div>
        <h1><?= htmlspecialchars($profileUser['full_name']) ?>'s Profile</h1>
    </div>
</div>

<div class="container" style="padding:32px 20px">
    <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start">

        <!-- Left Column -->
        <div>
            <!-- Profile Card -->
            <div class="card mb-3">
                <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:32px 20px;text-align:center;position:relative">
                    <div class="profile-avatar" style="width:100px;height:100px;font-size:44px;margin:0 auto 14px;border-width:4px">
                        <?php if($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover">
                        <?php else: ?>
                        <?= $profileUser['gender']==='male'?'👳':'🧕' ?>
                        <?php endif; ?>
                    </div>
                    <h2 style="color:white;font-size:20px;margin-bottom:4px"><?=htmlspecialchars($profileUser['full_name'])?></h2>
                    <div style="color:rgba(255,255,255,0.8);font-size:14px"><i class="fas fa-map-marker-alt"></i> <?=htmlspecialchars($profileUser['city']??'')?>, <?=htmlspecialchars($profileUser['country']??'')?></div>
                    <?php if($profileUser['wali_approved']): ?>
                    <div style="margin-top:12px"><span class="badge badge-gold"><i class="fas fa-shield-alt"></i> Wali Approved</span></div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if($currentUser && $currentUser['id'] !== $id): ?>
                        <?php if($myInterest): ?>
                        <?php $lbl = ['pending'=>'Awaiting guardians\' approval','success'=>'Rishta approved by both guardians','rejected'=>'Rejected'][$myInterest['status']] ?? ucfirst($myInterest['status']); ?>
                        <div class="alert alert-<?= $myInterest['status']==='success'?'success':($myInterest['status']==='rejected'?'error':'info') ?>">
                            <i class="fas fa-info-circle"></i>
                            Rishta status: <strong><?= $lbl ?></strong>
                        </div>
                        <?php else: ?>
                        <a href="send-interest.php?to=<?=$id?>" class="btn btn-gold btn-block mb-2">
                            <i class="fas fa-heart"></i> Send Interest
                        </a>
                        <?php endif; ?>
                        <?php if($myInterest && in_array($myInterest['status'],['pending','success'])): ?>
                        <a href="messages.php?with=<?=$id?>" class="btn btn-primary btn-block">
                            <i class="fas fa-comments"></i> Send Message
                        </a>
                        <?php endif; ?>
                    <?php elseif($currentUser && $currentUser['id'] === $id): ?>
                    <a href="<?= SITE_URL ?>/upload-photo.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-camera"></i> Change Profile Picture
                    </a>
                    <a href="<?= SITE_URL ?>/complete-profile.php" class="btn btn-outline btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline btn-block">
                        <i class="fas fa-images"></i> Manage Gallery
                    </a>
                    <?php elseif(!$currentUser): ?>
                    <div style="text-align:center">
                        <p style="font-size:13px;color:var(--text-medium);margin-bottom:12px">Login to send interest to this profile</p>
                        <a href="login.php" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Login</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="card mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Basic Info</div></div>
                <div class="card-body" style="padding:0">
                    <?php $infos = [
                        ['Age','fas fa-birthday-cake',($profileUser['age']??'N/A').' years'],
                        ['Marital Status','fas fa-ring',ucwords(str_replace('_',' ',$profileUser['marital_status']??''))],
                        ['Height','fas fa-ruler-vertical',$profileUser['height']??'N/A'],
                        ['Complexion','fas fa-palette',$profileUser['complexion']??'N/A'],
                        ['Languages','fas fa-language',$profileUser['languages']??'N/A'],
                        ['Family Type','fas fa-home',ucfirst($profileUser['family_type']??'')],
                    ];
                    foreach($infos as $info): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid var(--border)">
                        <i class="<?=$info[1]?>" style="color:var(--primary);width:16px;font-size:13px"></i>
                        <span style="font-size:13px;color:var(--text-light);width:110px;flex-shrink:0"><?=$info[0]?></span>
                        <span style="font-size:13px;font-weight:500"><?=htmlspecialchars($info[2])?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Wali Info -->
            <?php if($wali): ?>
            <div class="wali-card">
                <h3><i class="fas fa-user-shield"></i> Guardian (Wali)</h3>
                <div style="font-size:14px;color:var(--text-dark)">
                    <strong><?=htmlspecialchars($wali['wali_name'])?></strong>
                    <span style="color:var(--text-light)"> — <?=htmlspecialchars($wali['wali_relation'])?></span>
                </div>
                <div style="font-size:12px;color:var(--text-medium);margin-top:6px">
                    <i class="fas fa-check" style="color:var(--primary)"></i> Wali is involved in the matrimonial process
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Islamic Info -->
            <div class="card mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-mosque"></i> Islamic Background</div></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px">
                        <?php $islamicInfo = [
                            ['Sect/Madhab','fas fa-moon',$profileUser['sect']??'N/A'],
                            ['Religiosity','fas fa-star',ucfirst($profileUser['religiosity']??'N/A')],
                            ['Daily Prayers','fas fa-pray',str_replace('_',' ',$profileUser['prayers_per_day']??'N/A')],
                            ['Dress Code','fas fa-user',$profileUser['gender']==='female'?($profileUser['hijab_niqab']??'N/A'):($profileUser['beard']??'N/A')],
                        ];
                        foreach($islamicInfo as $info): ?>
                        <div style="background:rgba(26,107,60,0.04);border:1px solid rgba(26,107,60,0.1);border-radius:10px;padding:14px;text-align:center">
                            <i class="<?=$info[1]?>" style="color:var(--primary);font-size:20px;margin-bottom:6px;display:block"></i>
                            <div style="font-size:11px;color:var(--text-light);margin-bottom:2px"><?=$info[0]?></div>
                            <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($info[2])?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Education & Career -->
            <div class="card mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-graduation-cap"></i> Education & Career</div></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <?php $careerInfo = [
                            ['Education','fas fa-graduation-cap',$profileUser['education']??'N/A'],
                            ['Occupation','fas fa-briefcase',$profileUser['occupation']??'N/A'],
                            ['Monthly Income','fas fa-money-bill',$profileUser['monthly_income']??'Prefer not to say'],
                            ['Country','fas fa-globe',$profileUser['country']??'N/A'],
                        ];
                        foreach($careerInfo as $ci): ?>
                        <div style="padding:12px;background:var(--cream);border-radius:8px">
                            <div style="font-size:11px;color:var(--text-light);margin-bottom:3px"><i class="<?=$ci[1]?>" style="color:var(--primary)"></i> <?=$ci[0]?></div>
                            <div style="font-size:14px;font-weight:500"><?=htmlspecialchars($ci[2])?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- About Me -->
            <?php if($profileUser['about_me']): ?>
            <div class="card mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-pen"></i> About Me</div></div>
                <div class="card-body">
                    <p style="font-size:14px;line-height:1.8;color:var(--text-medium)"><?=nl2br(htmlspecialchars($profileUser['about_me']))?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- About Family -->
            <?php if($profileUser['about_family']): ?>
            <div class="card mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-home"></i> About My Family</div></div>
                <div class="card-body">
                    <p style="font-size:14px;line-height:1.8;color:var(--text-medium)"><?=nl2br(htmlspecialchars($profileUser['about_family']))?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Photo Gallery -->
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-images"></i> Photo Gallery</div>
                    <?php if($currentUser && $currentUser['id'] === $id): ?>
                    <a href="gallery.php" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Manage</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if(empty($galleryPhotos)): ?>
                    <div style="text-align:center;padding:30px;color:var(--text-light)">
                        <i class="fas fa-camera" style="font-size:32px;display:block;margin-bottom:10px;color:var(--border)"></i>
                        <p style="font-size:13px">No gallery photos uploaded yet.</p>
                        <?php if($currentUser && $currentUser['id'] === $id): ?>
                        <a href="gallery.php" class="btn btn-primary btn-sm mt-2"><i class="fas fa-upload"></i> Upload Photos</a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
                        <?php foreach($galleryPhotos as $photo): ?>
                        <div style="aspect-ratio:1;border-radius:8px;overflow:hidden;cursor:pointer;background:#f0f0f0" onclick="openGallery('<?= htmlspecialchars(galleryUrl($photo['filename'])) ?>')">
                            <img src="<?= htmlspecialchars(galleryUrl($photo['filename'])) ?>"
                                 alt="<?= htmlspecialchars($photo['caption'] ?? '') ?>"
                                 style="width:100%;height:100%;object-fit:cover;transition:transform .3s"
                                 onmouseover="this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align:center;margin-top:10px;font-size:12px;color:var(--text-light)">
                        <?= count($galleryPhotos) ?> photo(s) · Click to enlarge
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Match Score (for logged-in users) -->
            <?php if($currentUser && $currentUser['id'] !== $id): ?>
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-star"></i> Compatibility Score</div></div>
                <div class="card-body">
                    <?php
                    $score = 50;
                    $myProfile = db()->fetchOne("SELECT * FROM profiles WHERE user_id=?", [$currentUser['id']]);
                    if($myProfile) {
                        if(($myProfile['sect']??'') === ($profileUser['sect']??'')) $score += 20;
                        if(($myProfile['religiosity']??'') === ($profileUser['religiosity']??'')) $score += 15;
                        if(($myProfile['country']??'') === ($profileUser['country']??'')) $score += 10;
                        if(($myProfile['family_type']??'') === ($profileUser['family_type']??'')) $score += 5;
                    }
                    $score = min(99, $score);
                    ?>
                    <div style="display:flex;align-items:center;gap:16px">
                        <div style="width:80px;height:80px;border-radius:50%;background:conic-gradient(var(--primary) <?=$score*3.6?>deg, var(--border) 0deg);display:flex;align-items:center;justify-content:center;position:relative">
                            <div style="width:58px;height:58px;border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:var(--primary)"><?=$score?>%</div>
                        </div>
                        <div>
                            <div style="font-size:18px;font-weight:700;color:var(--primary)"><?=$score?>% Compatible</div>
                            <div style="font-size:13px;color:var(--text-medium);margin-top:4px">Based on sect, religiosity, location & family values</div>
                            <div style="font-size:12px;color:var(--text-light);margin-top:6px;font-style:italic">Remember: Compatibility in deen (religion) is most important!</div>
                        </div>
                    </div>
                    <div style="margin-top:14px">
                        <div class="progress-bar"><div class="progress-fill" style="width:<?=$score?>%"></div></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="gallery-lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:9999;align-items:center;justify-content:center" onclick="closeGallery()">
    <img id="gallery-lightbox-img" src="" style="max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 10px 60px rgba(0,0,0,.5)">
    <button onclick="closeGallery()" style="position:absolute;top:20px;right:24px;background:rgba(255,255,255,0.15);border:none;color:white;font-size:28px;cursor:pointer;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center">✕</button>
</div>
<script>
function openGallery(src) {
    document.getElementById('gallery-lightbox-img').src = src;
    document.getElementById('gallery-lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeGallery() {
    document.getElementById('gallery-lightbox').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) { if(e.key==='Escape') closeGallery(); });
</script>
<?php require_once 'includes/footer.php'; ?>
