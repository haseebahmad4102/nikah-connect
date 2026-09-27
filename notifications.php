<?php
$pageTitle = 'Notifications';
require_once 'includes/db.php';
requireLogin();
$user = currentUser();

db()->execute("UPDATE notifications SET is_read=1 WHERE user_id=?", [$user['id']]);

$notifications = db()->fetchAll(
    "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50",
    [$user['id']]
);

require_once 'includes/header.php';
?>
<div class="page-header">
    <div class="container"><h1><i class="fas fa-bell"></i> Notifications</h1></div>
</div>
<div class="container" style="padding:28px 20px;max-width:700px">
    <?php if(empty($notifications)): ?>
    <div style="text-align:center;padding:60px;background:white;border-radius:var(--radius);border:1px solid var(--border)">
        <div style="font-size:48px;margin-bottom:16px">🔔</div>
        <h3>No notifications yet</h3>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach($notifications as $n): ?>
        <?php $icons=['interest'=>'fas fa-heart','interest_accepted'=>'fas fa-check-circle','message'=>'fas fa-comment']; ?>
        <div style="background:white;border:1px solid var(--border);border-radius:var(--radius);padding:16px;display:flex;gap:12px;align-items:flex-start">
            <div style="width:40px;height:40px;border-radius:50%;background:rgba(26,107,60,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="<?=$icons[$n['type']]??'fas fa-bell'?>" style="color:var(--primary)"></i>
            </div>
            <div style="flex:1">
                <div style="font-size:14px;color:var(--text-dark)"><?=htmlspecialchars($n['message'])?></div>
                <div style="font-size:12px;color:var(--text-light);margin-top:4px"><?=timeAgo($n['created_at'])?></div>
            </div>
            <?php if($n['link']): ?>
            <a href="<?=htmlspecialchars($n['link'])?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
