<?php
$pageTitle = 'Chat Monitor';
require_once 'includes/db.php';
requireLogin();

$a = (int)($_GET['a'] ?? 0);
$b = (int)($_GET['b'] ?? 0);
$me = currentUser();

// Access control: admin, OR a guardian who guards one of the two parties
$allowed = false;
if(($_SESSION['role'] ?? '') === 'admin') {
    $allowed = true;
} elseif(isParent()) {
    $childId = guardedChildId($me['id']);
    if($childId && ($childId == $a || $childId == $b)) $allowed = true;
}

if(!$allowed || !$a || !$b) {
    header('Location: '.SITE_URL.'/guardian.php');
    exit;
}

$userA = db()->fetchOne("SELECT * FROM users WHERE id=?", [$a]);
$userB = db()->fetchOne("SELECT * FROM users WHERE id=?", [$b]);

$messages = db()->fetchAll(
    "SELECT m.*, u.full_name FROM messages m JOIN users u ON m.sender_id=u.id
     WHERE ((m.sender_id=? AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=?)) AND m.wali_can_view=1
     ORDER BY m.created_at ASC",
    [$a, $b, $b, $a]
);

require_once 'includes/header.php';
?>
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-eye"></i> Chat Monitor</h1>
        <p>Read-only view of the conversation between the two parties</p>
    </div>
</div>

<div class="container" style="padding:24px 20px;max-width:760px">
    <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.25);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--text-medium)">
        <i class="fas fa-shield-alt" style="color:var(--gold-dark)"></i>
        Conversation between <strong><?=htmlspecialchars($userA['full_name']??'')?></strong> and <strong><?=htmlspecialchars($userB['full_name']??'')?></strong>. You are viewing as guardian — you cannot send messages here.
    </div>

    <div class="card">
        <div class="card-body" style="max-height:60vh;overflow-y:auto">
            <?php if(empty($messages)): ?>
            <div style="text-align:center;color:var(--text-light);padding:40px 20px;font-size:14px">No messages exchanged yet.</div>
            <?php else: ?>
            <?php foreach($messages as $m): ?>
            <?php $fromA = $m['sender_id'] == $a; ?>
            <div style="display:flex;flex-direction:column;margin-bottom:12px;align-items:<?=$fromA?'flex-start':'flex-end'?>">
                <div style="max-width:75%;background:<?=$fromA?'var(--cream)':'rgba(26,107,60,0.1)'?>;border:1px solid var(--border);border-radius:12px;padding:10px 14px">
                    <div style="font-size:11px;font-weight:600;color:var(--primary);margin-bottom:3px"><?=htmlspecialchars($m['full_name'])?></div>
                    <?php if($m['is_flagged']): ?>
                    <div style="font-size:11px;color:#e74c3c;margin-bottom:3px"><i class="fas fa-exclamation-triangle"></i> Flagged for review</div>
                    <?php endif; ?>
                    <div style="font-size:14px;color:var(--text-dark)"><?=nl2br(htmlspecialchars($m['message']))?></div>
                    <div style="font-size:11px;color:var(--text-light);margin-top:4px"><?=timeAgo($m['created_at'])?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align:center;margin-top:16px">
        <a href="guardian.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Guardian Panel</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
