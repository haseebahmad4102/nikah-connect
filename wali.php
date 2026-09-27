<?php
$pageTitle = 'Wali (Guardian)';
require_once 'includes/db.php';
requireLogin();

$user = currentUser();
$wali = db()->fetchOne("SELECT * FROM walis WHERE user_id=?", [$user['id']]);
$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $wali_name = trim($_POST['wali_name'] ?? '');
    $wali_relation = trim($_POST['wali_relation'] ?? '');
    $wali_phone = trim($_POST['wali_phone'] ?? '');
    $wali_email = trim($_POST['wali_email'] ?? '');
    $wali_consent = isset($_POST['wali_consent']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');

    if(!$wali_name || !$wali_relation) {
        $error = 'Wali name and relation are required.';
    } else {
        if($wali) {
            db()->execute(
                "UPDATE walis SET wali_name=?,wali_relation=?,wali_phone=?,wali_email=?,wali_consent=?,notes=?,consent_date=? WHERE user_id=?",
                [$wali_name,$wali_relation,$wali_phone,$wali_email,$wali_consent,$notes,$wali_consent?date('Y-m-d H:i:s'):null,$user['id']]
            );
        } else {
            db()->insert(
                "INSERT INTO walis (user_id,wali_name,wali_relation,wali_phone,wali_email,wali_consent,notes,consent_date) VALUES (?,?,?,?,?,?,?,?)",
                [$user['id'],$wali_name,$wali_relation,$wali_phone,$wali_email,$wali_consent,$notes,$wali_consent?date('Y-m-d H:i:s'):null]
            );
        }
        $success = 'Wali information updated successfully!';
        $wali = db()->fetchOne("SELECT * FROM walis WHERE user_id=?", [$user['id']]);
    }
}

// Messages visible to wali
$waliMessages = db()->fetchAll(
    "SELECT m.*, u.full_name as sender_name, u2.full_name as receiver_name
     FROM messages m
     JOIN users u ON m.sender_id = u.id
     JOIN users u2 ON m.receiver_id = u2.id
     WHERE (m.sender_id=? OR m.receiver_id=?) AND m.wali_can_view=1
     ORDER BY m.created_at DESC LIMIT 20",
    [$user['id'],$user['id']]
);

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-user-shield"></i> Guardian (Wali) Panel</h1>
        <p>Manage your Guardian information and view activity visible to your Wali</p>
    </div>
</div>

<div class="container" style="padding:28px 20px">

    <!-- Islamic importance -->
    <div style="background:linear-gradient(135deg,rgba(201,168,76,0.1),rgba(26,107,60,0.08));border:1px solid rgba(201,168,76,0.3);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px">
        <div style="display:flex;gap:16px;align-items:flex-start">
            <div style="font-size:32px">🌙</div>
            <div>
                <h3 style="color:var(--primary);margin-bottom:8px">The Role of Wali in Islamic Marriage</h3>
                <p style="font-size:14px;color:var(--text-medium);line-height:1.7">
                    In Islam, a woman's Wali (guardian) plays a crucial role in the marriage process. The Prophet ﷺ said:
                    <em>"There is no marriage except with a guardian (wali)" (Abu Dawud 2085)</em>.
                    Your Wali can view all messages and activity on this platform to ensure Islamic compliance.
                </p>
            </div>
        </div>
    </div>

    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?=$error?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

        <!-- Wali Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-user-edit"></i> Wali Information</div>
                <?php if($wali && $wali['wali_consent']): ?>
                <span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="input-group">
                        <label>Wali's Full Name <span class="required">*</span></label>
                        <input type="text" name="wali_name" value="<?=htmlspecialchars($wali['wali_name']??'')?>" placeholder="Father/Brother/Uncle Name" required>
                    </div>
                    <div class="input-group">
                        <label>Relation to You <span class="required">*</span></label>
                        <select name="wali_relation" required>
                            <option value="">Select Relation</option>
                            <?php foreach(['Father','Brother','Uncle (Paternal)','Uncle (Maternal)','Grandfather','Other'] as $r): ?>
                            <option value="<?=$r?>" <?= ($wali['wali_relation']??'')===$r?'selected':'' ?>><?=$r?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <label>Wali's Phone Number</label>
                            <input type="tel" name="wali_phone" value="<?=htmlspecialchars($wali['wali_phone']??'')?>" placeholder="03001234567">
                        </div>
                        <div class="input-group">
                            <label>Wali's Email</label>
                            <input type="email" name="wali_email" value="<?=htmlspecialchars($wali['wali_email']??'')?>" placeholder="wali@email.com">
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Notes / Additional Info</label>
                        <textarea name="notes" rows="3" placeholder="Any additional information about your wali or family..."><?=htmlspecialchars($wali['notes']??'')?></textarea>
                    </div>
                    <div style="background:rgba(26,107,60,0.05);border:1px solid rgba(26,107,60,0.15);border-radius:8px;padding:14px;margin-bottom:16px">
                        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:14px;color:var(--text-dark)">
                            <input type="checkbox" name="wali_consent" <?=$wali&&$wali['wali_consent']?'checked':''?> style="width:auto;margin-top:2px">
                            <span>
                                <strong>Wali's Consent Confirmed</strong><br>
                                <span style="font-size:12px;color:var(--text-medium)">I confirm that my Guardian (Wali) is aware of and approves this matrimonial profile.</span>
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Save Wali Information
                    </button>
                </form>
            </div>
        </div>

        <!-- Wali Activity -->
        <div>
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-eye"></i> Wali Can See</div>
                </div>
                <div class="card-body">
                    <?php $permissions = [
                        ['fas fa-user','Your profile information','Always visible'],
                        ['fas fa-heart','Interest requests sent/received','Always visible'],
                        ['fas fa-comments','All messages you send/receive','Always visible'],
                        ['fas fa-search','Profiles you have viewed','Always visible'],
                        ['fas fa-bell','All notifications','Always visible'],
                    ];
                    foreach($permissions as $p): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
                        <div style="width:34px;height:34px;background:rgba(26,107,60,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center">
                            <i class="<?=$p[0]?>" style="color:var(--primary);font-size:14px"></i>
                        </div>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:500"><?=$p[1]?></div>
                            <div style="font-size:11px;color:var(--text-light)"><?=$p[2]?></div>
                        </div>
                        <i class="fas fa-check-circle" style="color:var(--primary)"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Messages for Wali View -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-history"></i> Recent Communication (Wali View)</div>
                </div>
                <div class="card-body" style="padding:0">
                    <?php if(empty($waliMessages)): ?>
                    <div style="padding:20px;text-align:center;color:var(--text-light);font-size:13px">No messages yet</div>
                    <?php else: ?>
                    <?php foreach(array_slice($waliMessages,0,6) as $msg): ?>
                    <div style="padding:12px 18px;border-bottom:1px solid var(--border)">
                        <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                            <span style="font-size:12px;font-weight:600;color:var(--text-dark)"><?=htmlspecialchars($msg['sender_name'])?> → <?=htmlspecialchars($msg['receiver_name'])?></span>
                            <span style="font-size:11px;color:var(--text-light)"><?=timeAgo($msg['created_at'])?></span>
                        </div>
                        <div style="font-size:13px;color:var(--text-medium)"><?=htmlspecialchars(substr($msg['message'],0,80)).(strlen($msg['message'])>80?'...':'')?></div>
                        <?php if($msg['is_flagged']): ?>
                        <span class="badge badge-danger" style="margin-top:4px"><i class="fas fa-flag"></i> Flagged</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
