<?php
require_once 'includes/db.php';
requireLogin();

// Guardians manage approvals, they do not send interests
if(isParent()) { header('Location: '.SITE_URL.'/guardian.php'); exit; }

$toId = (int)($_GET['to'] ?? 0);
if(!$toId || $toId == $_SESSION['user_id']) {
    header('Location: '.SITE_URL.'/profiles.php');
    exit;
}

$toUser = db()->fetchOne("SELECT u.*, p.* FROM users u LEFT JOIN profiles p ON u.id=p.user_id WHERE u.id=? AND u.is_active=1", [$toId]);
if(!$toUser) { header('Location: '.SITE_URL.'/profiles.php'); exit; }

$existing = db()->fetchOne("SELECT * FROM interests WHERE sender_id=? AND receiver_id=?", [$_SESSION['user_id'], $toId]);

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $message = trim($_POST['message'] ?? '');
    if($existing) {
        $error = 'You have already sent an interest to this profile.';
    } else {
        db()->insert(
            "INSERT INTO interests (sender_id, receiver_id, message) VALUES (?,?,?)",
            [$_SESSION['user_id'], $toId, $message]
        );
        $me = currentUser();
        $meName = htmlspecialchars($me['full_name']);
        $toName = htmlspecialchars($toUser['full_name']);
        // Notify the other party
        addNotification($toId, 'interest', $meName.' has expressed interest. Your guardian will review it.', SITE_URL.'/interests.php');
        // Notify BOTH guardians — they are the ones who approve
        notifyGuardian($_SESSION['user_id'], 'interest', $meName.' (your ward) expressed interest in '.$toName.'. Please review.', SITE_URL.'/guardian.php');
        notifyGuardian($toId, 'interest', $meName.' expressed interest in '.$toName.' (your ward). Please review.', SITE_URL.'/guardian.php');
        header('Location: '.SITE_URL.'/view-profile.php?id='.$toId.'&interest=sent');
        exit;
    }
}

$pageTitle = 'Send Interest';
require_once 'includes/header.php';
?>

<div class="auth-page" style="background:linear-gradient(135deg,#1a2e4a,var(--primary-dark))">
    <div class="auth-box" style="max-width:500px">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:70px;height:70px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 14px;border:3px solid var(--gold-light)">
                <?= $toUser['gender']==='male'?'👳':'🧕' ?>
            </div>
            <h2 style="color:var(--primary);font-size:20px">Send Interest</h2>
            <p style="color:var(--text-medium);font-size:14px">To: <strong><?=htmlspecialchars($toUser['full_name'])?></strong></p>
            <p style="color:var(--text-light);font-size:12px;margin-top:4px"><?=htmlspecialchars($toUser['city']??'')?>, <?=htmlspecialchars($toUser['country']??'')?></p>
        </div>

        <?php if($existing): ?>
        <?php $exLbl = ['pending'=>'Awaiting guardians\' approval','success'=>'Approved by both guardians','rejected'=>'Rejected'][$existing['status']] ?? ucfirst($existing['status']); ?>
        <div class="alert alert-<?=$existing['status']==='success'?'success':($existing['status']==='rejected'?'error':'info')?>">
            <i class="fas fa-info-circle"></i>
            You already sent an interest. Status: <strong><?= $exLbl ?></strong>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Your interest request will be sent to this profile along with your Guardian (Wali). They will review your profile before responding.
        </div>

        <form method="POST">
            <div class="input-group">
                <label>Personal Message (Optional)</label>
                <textarea name="message" rows="4" placeholder="As-salamu alaykum. I came across your profile and found it interesting. InshaAllah, I hope to connect with your family..." maxlength="500"></textarea>
                <div style="font-size:11px;color:var(--text-light);margin-top:4px">Keep it modest and Islamic. Max 500 characters.</div>
            </div>
            <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.2);border-radius:8px;padding:12px;margin-bottom:18px;font-size:12px;color:var(--text-medium)">
                <i class="fas fa-shield-alt" style="color:var(--gold-dark)"></i>
                <strong>Islamic Reminder:</strong> This interest will be visible to the recipient's Wali. Maintain honesty and Islamic etiquette.
            </div>
            <button type="submit" class="btn btn-gold btn-block btn-lg">
                <i class="fas fa-heart"></i> Send Interest Request
            </button>
            <div style="text-align:center;margin-top:12px">
                <a href="view-profile.php?id=<?=$toId?>" style="color:var(--text-light);font-size:13px">← Cancel & Go Back</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
