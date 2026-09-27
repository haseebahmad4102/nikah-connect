<?php
$pageTitle = 'My Preferences';
require_once 'includes/db.php';
requireLogin();
$user = currentUser();
$prefs = db()->fetchOne("SELECT * FROM preferences WHERE user_id=?", [$user['id']]);
$success = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $data = [
        (int)$_POST['pref_age_min'],$_POST['pref_age_max'],
        $_POST['pref_country'],$_POST['pref_marital_status'],
        $_POST['pref_education'],$_POST['pref_occupation'],
        $_POST['pref_religiosity'],$_POST['pref_sect'],$_POST['pref_family_type'],
        $user['id']
    ];
    if($prefs) {
        db()->execute("UPDATE preferences SET pref_age_min=?,pref_age_max=?,pref_country=?,pref_marital_status=?,pref_education=?,pref_occupation=?,pref_religiosity=?,pref_sect=?,pref_family_type=? WHERE user_id=?", $data);
    } else {
        db()->insert("INSERT INTO preferences (pref_age_min,pref_age_max,pref_country,pref_marital_status,pref_education,pref_occupation,pref_religiosity,pref_sect,pref_family_type,user_id) VALUES (?,?,?,?,?,?,?,?,?,?)", $data);
    }
    $success = 'Preferences saved!';
    $prefs = db()->fetchOne("SELECT * FROM preferences WHERE user_id=?", [$user['id']]);
}

require_once 'includes/header.php';
?>
<div class="page-header">
    <div class="container"><h1><i class="fas fa-sliders-h"></i> Match Preferences</h1><p>Set your ideal partner criteria for better matches</p></div>
</div>
<div class="container" style="padding:28px 20px;max-width:700px">
    <?php if($success): ?><div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-heart"></i> Partner Preferences</div></div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="input-group">
                        <label>Minimum Age</label>
                        <input type="number" name="pref_age_min" value="<?=$prefs['pref_age_min']??18?>" min="18" max="70">
                    </div>
                    <div class="input-group">
                        <label>Maximum Age</label>
                        <input type="number" name="pref_age_max" value="<?=$prefs['pref_age_max']??45?>" min="18" max="70">
                    </div>
                </div>
                <div class="form-row">
                    <div class="input-group">
                        <label>Preferred Country</label>
                        <select name="pref_country">
                            <option value="">Any Country</option>
                            <?php foreach(['Pakistan','India','Bangladesh','UAE','Saudi Arabia','UK','USA','Canada','Malaysia'] as $c): ?>
                            <option value="<?=$c?>" <?=($prefs['pref_country']??'')===$c?'selected':''?>><?=$c?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Marital Status</label>
                        <select name="pref_marital_status">
                            <option value="">Any</option>
                            <?php foreach(['never_married'=>'Never Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l): ?>
                            <option value="<?=$v?>" <?=($prefs['pref_marital_status']??'')===$v?'selected':''?>><?=$l?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="input-group">
                        <label>Preferred Sect</label>
                        <select name="pref_sect">
                            <option value="">Any Sect</option>
                            <?php foreach(['Sunni','Shia','Deobandi','Barelvi','Salafi','Hanafi'] as $s): ?>
                            <option value="<?=$s?>" <?=($prefs['pref_sect']??'')===$s?'selected':''?>><?=$s?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Religiosity</label>
                        <select name="pref_religiosity">
                            <option value="">Any</option>
                            <option value="practicing" <?=($prefs['pref_religiosity']??'')==='practicing'?'selected':''?>>Practicing</option>
                            <option value="moderate" <?=($prefs['pref_religiosity']??'')==='moderate'?'selected':''?>>Moderate</option>
                            <option value="learning" <?=($prefs['pref_religiosity']??'')==='learning'?'selected':''?>>Learning</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="input-group">
                        <label>Education</label>
                        <select name="pref_education">
                            <option value="">Any</option>
                            <?php foreach(['Matric','Intermediate','Bachelor\'s','Master\'s','PhD'] as $e): ?>
                            <option value="<?=$e?>" <?=($prefs['pref_education']??'')===$e?'selected':''?>><?=$e?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Family Type</label>
                        <select name="pref_family_type">
                            <option value="">Any</option>
                            <option value="nuclear" <?=($prefs['pref_family_type']??'')==='nuclear'?'selected':''?>>Nuclear</option>
                            <option value="joint" <?=($prefs['pref_family_type']??'')==='joint'?'selected':''?>>Joint</option>
                        </select>
                    </div>
                </div>
                <div class="input-group">
                    <label>Preferred Occupation</label>
                    <input type="text" name="pref_occupation" placeholder="Teacher, Engineer, Doctor..." value="<?=htmlspecialchars($prefs['pref_occupation']??'')?>">
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Save Preferences & Get Matches
                </button>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
