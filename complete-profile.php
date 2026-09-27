<?php
$pageTitle = 'Complete Profile';
require_once 'includes/db.php';
require_once 'includes/upload.php';
requireLogin();

$user = currentUser();
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = (int)($_POST['age'] ?? 0);
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? 'Pakistan');
    $marital = $_POST['marital_status'] ?? 'never_married';
    $education = trim($_POST['education'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $height = trim($_POST['height'] ?? '');
    $complexion = trim($_POST['complexion'] ?? '');
    $sect = trim($_POST['sect'] ?? '');
    $religiosity = $_POST['religiosity'] ?? 'moderate';
    $prayers = $_POST['prayers_per_day'] ?? '5_times';
    $hijab = trim($_POST['hijab_niqab'] ?? '');
    $beard = trim($_POST['beard'] ?? '');
    $languages = trim($_POST['languages'] ?? '');
    $family_type = $_POST['family_type'] ?? 'nuclear';
    $about_me = trim($_POST['about_me'] ?? '');
    $about_family = trim($_POST['about_family'] ?? '');

    // Wali info
    $wali_name = trim($_POST['wali_name'] ?? '');
    $wali_relation = trim($_POST['wali_relation'] ?? '');
    $wali_phone = trim($_POST['wali_phone'] ?? '');
    $wali_email = trim($_POST['wali_email'] ?? '');

    if($age < 18 || !$city || !$education || !$wali_name || !$wali_relation) {
        $error = 'Please fill in all required fields. Age must be 18 or older.';
    } else {
        // Handle profile photo upload
        $photoFilename = null;
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = uploadImage($_FILES['profile_photo'], 'avatars');
            if (isset($upload['error'])) {
                $error = $upload['error'];
            } else {
                $photoFilename = $upload['filename'];
                // Delete old photo
                $oldProfile = db()->fetchOne("SELECT photo FROM profiles WHERE user_id=?", [$_SESSION['user_id']]);
                if ($oldProfile && $oldProfile['photo']) deleteUpload('avatars/' . $oldProfile['photo']);
            }
        }

        if (!$error) {
        // Upsert profile
        $existing = db()->fetchOne("SELECT id FROM profiles WHERE user_id = ?", [$_SESSION['user_id']]);
        if($existing) {
            $photoSql = $photoFilename ? ", photo=?" : "";
            $params = [$age,$city,$country,$marital,$education,$occupation,$height,$complexion,$sect,$religiosity,$prayers,$hijab,$beard,$languages,$family_type,$about_me,$about_family];
            if ($photoFilename) $params[] = $photoFilename;
            $params[] = $_SESSION['user_id'];
            db()->execute(
                "UPDATE profiles SET age=?,city=?,country=?,marital_status=?,education=?,occupation=?,height=?,complexion=?,sect=?,religiosity=?,prayers_per_day=?,hijab_niqab=?,beard=?,languages=?,family_type=?,about_me=?,about_family=?$photoSql WHERE user_id=?",
                $params
            );
        } else {
            $photoCol = $photoFilename ? ",photo" : "";
            $photoVal = $photoFilename ? ",?" : "";
            $params = [$_SESSION['user_id'],$age,$city,$country,$marital,$education,$occupation,$height,$complexion,$sect,$religiosity,$prayers,$hijab,$beard,$languages,$family_type,$about_me,$about_family];
            if ($photoFilename) $params[] = $photoFilename;
            db()->insert(
                "INSERT INTO profiles (user_id,age,city,country,marital_status,education,occupation,height,complexion,sect,religiosity,prayers_per_day,hijab_niqab,beard,languages,family_type,about_me,about_family$photoCol) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?$photoVal)",
                $params
            );
        }

        // Upsert wali
        $waliEx = db()->fetchOne("SELECT id FROM walis WHERE user_id = ?", [$_SESSION['user_id']]);
        if($waliEx) {
            db()->execute("UPDATE walis SET wali_name=?,wali_relation=?,wali_phone=?,wali_email=? WHERE user_id=?",
                [$wali_name,$wali_relation,$wali_phone,$wali_email,$_SESSION['user_id']]);
        } else {
            db()->insert("INSERT INTO walis (user_id,wali_name,wali_relation,wali_phone,wali_email) VALUES (?,?,?,?,?)",
                [$_SESSION['user_id'],$wali_name,$wali_relation,$wali_phone,$wali_email]);
        }

        db()->execute("UPDATE users SET profile_complete=1 WHERE id=?", [$_SESSION['user_id']]);
        header('Location: '.SITE_URL.'/dashboard.php?profile=1');
        exit;
        } // end if(!$error)
    }
}

$profile = db()->fetchOne("SELECT * FROM profiles WHERE user_id=?", [$_SESSION['user_id']]);
$wali = db()->fetchOne("SELECT * FROM walis WHERE user_id=?", [$_SESSION['user_id']]);
?>
<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-user-edit"></i> Complete Your Profile</h1>
        <p>A complete profile gets 5x more matches. Fill all details for best results.</p>
    </div>
</div>

<div class="container" style="padding:32px 20px">
    <?php if($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <!-- Progress indicator -->
    <div class="card mb-3">
        <div class="card-body" style="padding:16px 22px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:14px;font-weight:600">Profile Completion</span>
                <span style="font-size:14px;color:var(--primary);font-weight:700">Fill all sections</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width:30%"></div></div>
        </div>
    </div>

    <form method="POST" id="profileForm" enctype="multipart/form-data">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

            <!-- Personal Information -->
            <div>
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-user"></i> Personal Information</div>
                    </div>
                    <div class="card-body">
                        <!-- Profile Photo Upload -->
                        <div class="input-group">
                            <label>Profile Photo</label>
                            <div style="display:flex;align-items:center;gap:18px">
                                <div id="photoPreview" style="width:90px;height:90px;border-radius:50%;border:3px solid var(--border);overflow:hidden;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:36px;flex-shrink:0">
                                    <?php if($profile && $profile['photo'] && file_exists(UPLOAD_DIR.'avatars/'.$profile['photo'])): ?>
                                    <img src="<?=UPLOAD_URL.'avatars/'.htmlspecialchars($profile['photo'])?>" style="width:100%;height:100%;object-fit:cover">
                                    <?php else: ?>
                                    <?= $user['gender']==='male' ? '👳' : '🧕' ?>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1">
                                    <label for="profile_photo" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:var(--cream);border:1.5px dashed var(--border);border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:var(--text-medium);transition:all .2s" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-medium)'">
                                        <i class="fas fa-camera" style="font-size:15px"></i> Choose Photo
                                    </label>
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                                    <div style="font-size:11px;color:var(--text-light);margin-top:6px">JPG, PNG or WEBP · Max 5MB</div>
                                </div>
                            </div>
                        </div>
                        <div class="divider" style="margin:14px 0"></div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Age <span class="required">*</span></label>
                                <input type="number" name="age" min="18" max="70" value="<?= $profile['age'] ?? '' ?>" required>
                            </div>
                            <div class="input-group">
                                <label>Marital Status</label>
                                <select name="marital_status">
                                    <?php foreach(['never_married'=>'Never Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l): ?>
                                    <option value="<?=$v?>" <?= ($profile['marital_status']??'')===$v?'selected':'' ?>><?=$l?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>City <span class="required">*</span></label>
                                <input type="text" name="city" placeholder="Lahore, Karachi..." value="<?= htmlspecialchars($profile['city'] ?? '') ?>" required>
                            </div>
                            <div class="input-group">
                                <label>Country</label>
                                <select name="country">
                                    <?php foreach(['Pakistan','India','Bangladesh','UAE','Saudi Arabia','UK','USA','Canada','Australia','Malaysia','Other'] as $c): ?>
                                    <option value="<?=$c?>" <?= ($profile['country']??'Pakistan')===$c?'selected':'' ?>><?=$c?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Height</label>
                                <input type="text" name="height" placeholder="5'8&quot;" value="<?= htmlspecialchars($profile['height'] ?? '') ?>">
                            </div>
                            <div class="input-group">
                                <label>Complexion</label>
                                <select name="complexion">
                                    <?php foreach(['Fair','Wheatish','Brown','Dark'] as $c): ?>
                                    <option value="<?=$c?>" <?= ($profile['complexion']??'')===$c?'selected':'' ?>><?=$c?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Languages</label>
                            <input type="text" name="languages" placeholder="Urdu, English, Arabic..." value="<?= htmlspecialchars($profile['languages'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Islamic Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-mosque"></i> Islamic Background</div>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="input-group">
                                <label>Sect / Madhab</label>
                                <select name="sect">
                                    <?php foreach(['Sunni','Shia','Deobandi','Barelvi','Salafi','Hanafi','Other'] as $s): ?>
                                    <option value="<?=$s?>" <?= ($profile['sect']??'')===$s?'selected':'' ?>><?=$s?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Religiosity</label>
                                <select name="religiosity">
                                    <option value="practicing" <?= ($profile['religiosity']??'')==='practicing'?'selected':'' ?>>Practicing</option>
                                    <option value="moderate" <?= ($profile['religiosity']??'')==='moderate'?'selected':'' ?>>Moderate</option>
                                    <option value="learning" <?= ($profile['religiosity']??'')==='learning'?'selected':'' ?>>Still Learning</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Daily Prayers</label>
                            <select name="prayers_per_day">
                                <option value="5_times" <?= ($profile['prayers_per_day']??'')==='5_times'?'selected':'' ?>>Alhamdulillah — 5 times daily</option>
                                <option value="3_times" <?= ($profile['prayers_per_day']??'')==='3_times'?'selected':'' ?>>3 times daily</option>
                                <option value="1_time" <?= ($profile['prayers_per_day']??'')==='1_time'?'selected':'' ?>>Occasionally</option>
                                <option value="occasionally" <?= ($profile['prayers_per_day']??'')==='occasionally'?'selected':'' ?>>Working on it</option>
                            </select>
                        </div>
                        <?php if($user['gender']==='female'): ?>
                        <div class="input-group">
                            <label>Hijab / Niqab</label>
                            <select name="hijab_niqab">
                                <?php foreach(['Hijab (Full)'=>'Hijab (Full)','Niqab (Full Face)'=>'Niqab (Full Face)','Hijab (Sometimes)'=>'Hijab (Sometimes)','No Hijab Yet'=>'No Hijab Yet'] as $v=>$l): ?>
                                <option value="<?=$v?>" <?= ($profile['hijab_niqab']??'')===$v?'selected':'' ?>><?=$l?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="input-group">
                            <label>Beard</label>
                            <select name="beard">
                                <?php foreach(['Yes (Full Sunnah)'=>'Yes (Full Sunnah)','Yes (Short)'=>'Yes (Short)','Trimmed'=>'Trimmed','No Beard Yet'=>'No Beard Yet'] as $v=>$l): ?>
                                <option value="<?=$v?>" <?= ($profile['beard']??'')===$v?'selected':'' ?>><?=$l?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <!-- Education & Career -->
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-graduation-cap"></i> Education & Career</div>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <label>Highest Education <span class="required">*</span></label>
                            <select name="education" required>
                                <option value="">Select education level</option>
                                <?php foreach(['Hifz / Islamic','Matric','Intermediate','Bachelor\'s Degree','Master\'s Degree','PhD','Diploma/Certificate','Other'] as $e): ?>
                                <option value="<?=$e?>" <?= ($profile['education']??'')===$e?'selected':'' ?>><?=$e?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Occupation / Profession</label>
                            <input type="text" name="occupation" placeholder="Software Engineer, Teacher, Doctor..." value="<?= htmlspecialchars($profile['occupation'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Family Type</label>
                                <select name="family_type">
                                    <option value="nuclear" <?= ($profile['family_type']??'')==='nuclear'?'selected':'' ?>>Nuclear Family</option>
                                    <option value="joint" <?= ($profile['family_type']??'')==='joint'?'selected':'' ?>>Joint Family</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About -->
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-pen"></i> About You</div>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <label>About Me</label>
                            <textarea name="about_me" placeholder="Bismillah. Describe yourself, your Islamic values, what you're looking for in a spouse..." rows="4"><?= htmlspecialchars($profile['about_me'] ?? '') ?></textarea>
                        </div>
                        <div class="input-group">
                            <label>About My Family</label>
                            <textarea name="about_family" placeholder="Describe your family background, their values, religious practices..." rows="3"><?= htmlspecialchars($profile['about_family'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Wali Information -->
                <div class="card wali-card">
                    <div class="card-header" style="background:transparent;border-bottom-color:rgba(201,168,76,0.2)">
                        <div class="card-title" style="color:var(--gold-dark)"><i class="fas fa-user-shield"></i> Guardian (Wali) Details</div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" style="margin-bottom:14px">
                            <i class="fas fa-info-circle"></i>
                            <span>Islam requires a Wali for marriage. Your guardian will be notified of all activity on your profile.</span>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Wali's Full Name <span class="required">*</span></label>
                                <input type="text" name="wali_name" placeholder="Father/Brother name" value="<?= htmlspecialchars($wali['wali_name'] ?? '') ?>" required>
                            </div>
                            <div class="input-group">
                                <label>Relation <span class="required">*</span></label>
                                <select name="wali_relation" required>
                                    <option value="">Select relation</option>
                                    <?php foreach(['Father','Brother','Uncle','Grandfather','Other'] as $r): ?>
                                    <option value="<?=$r?>" <?= ($wali['wali_relation']??'')===$r?'selected':'' ?>><?=$r?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Wali's Phone</label>
                                <input type="tel" name="wali_phone" placeholder="03001234567" value="<?= htmlspecialchars($wali['wali_phone'] ?? '') ?>">
                            </div>
                            <div class="input-group">
                                <label>Wali's Email</label>
                                <input type="email" name="wali_email" placeholder="wali@email.com" value="<?= htmlspecialchars($wali['wali_email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:center;margin-top:24px">
            <button type="submit" class="btn btn-primary btn-lg" style="min-width:220px">
                <i class="fas fa-save"></i> Save Profile & Continue
            </button>
        </div>
    </form>
</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('photoPreview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once 'includes/footer.php'; ?>
