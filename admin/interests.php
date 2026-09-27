<?php
$pageTitle = 'Interests';
require_once '../includes/db.php';
requireAdmin();

$interests = db()->fetchAll(
    "SELECT i.*, u1.full_name as sender_name, u1.gender as sender_gender, u2.full_name as receiver_name, u2.gender as receiver_gender
     FROM interests i JOIN users u1 ON i.sender_id=u1.id JOIN users u2 ON i.receiver_id=u2.id
     ORDER BY i.created_at DESC LIMIT 50"
);

require_once '../includes/header.php';
?>
<div class="page-header" style="background:linear-gradient(135deg,#1a1a2e,#16213e)">
    <div class="container"><h1><i class="fas fa-heart"></i> Interests</h1></div>
</div>
<div style="background:#1a1a2e;padding:0;border-bottom:1px solid #2a2a4a">
    <div class="container" style="display:flex">
        <?php $adminNav=[['index.php','Dashboard'],['users.php','Users'],['profiles.php','Profiles'],['messages.php','Messages'],['interests.php','Interests'],['reports.php','Reports']];
        $curr=basename($_SERVER['PHP_SELF']);
        foreach($adminNav as $n): ?>
        <a href="<?=SITE_URL?>/admin/<?=$n[0]?>" style="padding:14px 18px;color:<?=$curr===$n[0]?'var(--gold-light)':'rgba(255,255,255,0.6)'?>;font-size:13px;font-weight:500;text-decoration:none;border-bottom:2px solid <?=$curr===$n[0]?'var(--gold)':'transparent'?>"><?=$n[1]?></a>
        <?php endforeach; ?>
    </div>
</div>
<div class="container" style="padding:24px 20px">
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>From</th><th>To</th><th>Status</th><th>Wali Notified</th><th>Message</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach($interests as $i): ?>
                    <tr>
                        <td>#<?=$i['id']?></td>
                        <td><span style="font-size:13px;font-weight:600"><?=$i['sender_gender']==='male'?'👳':'🧕'?> <?=htmlspecialchars($i['sender_name'])?></span></td>
                        <td><span style="font-size:13px"><?=$i['receiver_gender']==='male'?'👳':'🧕'?> <?=htmlspecialchars($i['receiver_name'])?></span></td>
                        <td><span class="badge badge-<?=in_array($i['status'],['accepted','success'])?'success':($i['status']==='rejected'?'danger':'warning')?>"><?=ucfirst($i['status'])?></span></td>
                        <td><span class="badge badge-<?=$i['wali_notified']?'success':'warning'?>"><?=$i['wali_notified']?'Yes':'No'?></span></td>
                        <td style="font-size:12px;color:var(--text-medium)"><?=htmlspecialchars(substr($i['message']??'',0,60))?></td>
                        <td style="font-size:12px;color:var(--text-light)"><?=timeAgo($i['created_at'])?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
