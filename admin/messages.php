<?php
$pageTitle = 'Manage Messages';
require_once '../includes/db.php';
requireAdmin();

$filter = $_GET['filter'] ?? '';
$where = $filter === 'flagged' ? 'WHERE m.is_flagged = 1' : '';

$messages = db()->fetchAll(
    "SELECT m.*, u1.full_name as sender_name, u2.full_name as receiver_name
     FROM messages m
     JOIN users u1 ON m.sender_id = u1.id
     JOIN users u2 ON m.receiver_id = u2.id
     $where
     ORDER BY m.created_at DESC LIMIT 50"
);

if(isset($_GET['delete'])) {
    db()->execute("DELETE FROM messages WHERE id=?", [(int)$_GET['delete']]);
    header('Location: messages.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="page-header" style="background:linear-gradient(135deg,#1a1a2e,#16213e)">
    <div class="container">
        <h1><i class="fas fa-comments"></i> Message Monitor</h1>
        <p>Monitor all platform communications for Islamic compliance</p>
    </div>
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
    <div style="display:flex;gap:10px;margin-bottom:18px">
        <a href="messages.php" class="btn <?=$filter===''?'btn-primary':'btn-outline'?> btn-sm">All Messages</a>
        <a href="messages.php?filter=flagged" class="btn <?=$filter==='flagged'?'btn-danger':'btn-outline'?> btn-sm">
            <i class="fas fa-flag"></i> Flagged Only
        </a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Message</th>
                        <th>Flagged</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($messages as $m): ?>
                    <tr style="<?=$m['is_flagged']?'background:rgba(239,68,68,0.04)':''?>">
                        <td>#<?=$m['id']?></td>
                        <td style="font-size:13px;font-weight:500"><?=htmlspecialchars($m['sender_name'])?></td>
                        <td style="font-size:13px"><?=htmlspecialchars($m['receiver_name'])?></td>
                        <td style="font-size:13px;max-width:300px"><?=htmlspecialchars(substr($m['message'],0,100)).(strlen($m['message'])>100?'...':'')?></td>
                        <td><?=$m['is_flagged']?'<span class="badge badge-danger"><i class="fas fa-flag"></i> Flagged</span>':'<span class="badge badge-success">Clean</span>'?></td>
                        <td style="font-size:12px;color:var(--text-light)"><?=timeAgo($m['created_at'])?></td>
                        <td>
                            <a href="?delete=<?=$m['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
