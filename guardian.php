<?php
$pageTitle = 'Guardian Panel';
require_once 'includes/db.php';
requireParent();

$parent = currentUser();
$childId = guardedChildId($parent['id']);
$child = $childId ? db()->fetchOne("SELECT u.*, p.* FROM users u LEFT JOIN profiles p ON u.id=p.user_id WHERE u.id=?", [$childId]) : null;

if(!$child) {
    // No linked child — nothing to guard
    require_once 'includes/header.php';
    echo '<div class="container" style="padding:40px 20px"><div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> This guardian account is not linked to any profile.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

// Handle approve / reject actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $intId = (int)$_GET['id'];
    // Only interests where the guarded child is a party
    $interest = db()->fetchOne(
        "SELECT * FROM interests WHERE id=? AND (sender_id=? OR receiver_id=?)",
        [$intId, $childId, $childId]
    );
    if($interest && in_array($interest['status'], ['pending'])) {
        $childIsSender = ($interest['sender_id'] == $childId);
        $otherId = $childIsSender ? $interest['receiver_id'] : $interest['sender_id'];

        if($action === 'approve') {
            if($childIsSender) {
                db()->execute("UPDATE interests SET sender_parent_approved=1, sender_parent_approved_at=NOW() WHERE id=?", [$intId]);
            } else {
                db()->execute("UPDATE interests SET receiver_parent_approved=1, receiver_parent_approved_at=NOW() WHERE id=?", [$intId]);
            }
            // Re-check: both approved => success
            $fresh = db()->fetchOne("SELECT * FROM interests WHERE id=?", [$intId]);
            if($fresh['sender_parent_approved'] && $fresh['receiver_parent_approved'] && $fresh['status']==='pending') {
                db()->execute("UPDATE interests SET status='success' WHERE id=?", [$intId]);
                // Notify boy & girl
                addNotification($fresh['sender_id'], 'rishta_success', 'MashaAllah! Both guardians approved your rishta. You may now agree on a visit date.', SITE_URL.'/interests.php?tab=sent');
                addNotification($fresh['receiver_id'], 'rishta_success', 'MashaAllah! Both guardians approved your rishta. You may now agree on a visit date.', SITE_URL.'/interests.php?tab=received');
                // Notify both guardians
                notifyGuardian($fresh['sender_id'], 'rishta_success', 'Rishta approved by both families. A visit can now be arranged.', SITE_URL.'/guardian.php');
                notifyGuardian($fresh['receiver_id'], 'rishta_success', 'Rishta approved by both families. A visit can now be arranged.', SITE_URL.'/guardian.php');
            } else {
                // Let the child and the other guardian know one side approved
                addNotification($childId, 'interest', 'Your guardian approved this rishta. Awaiting the other family.', SITE_URL.'/interests.php');
                notifyGuardian($otherId, 'interest', htmlspecialchars($child['full_name']).'\'s guardian approved. Your approval is awaited.', SITE_URL.'/guardian.php');
            }
        } elseif($action === 'reject') {
            db()->execute("UPDATE interests SET status='rejected' WHERE id=?", [$intId]);
            addNotification($childId, 'interest', 'Your guardian declined a rishta.', SITE_URL.'/interests.php');
            addNotification($otherId, 'interest', 'A rishta was not approved by the guardian.', SITE_URL.'/interests.php');
            notifyGuardian($otherId, 'interest', 'A rishta involving your ward was declined by the other guardian.', SITE_URL.'/guardian.php');
        }
    }
    header('Location: '.SITE_URL.'/guardian.php');
    exit;
}

// All rishtas involving the child (newest first)
$rishtas = db()->fetchAll(
    "SELECT i.*,
        su.full_name AS sender_name, su.gender AS sender_gender,
        ru.full_name AS receiver_name, ru.gender AS receiver_gender
     FROM interests i
     JOIN users su ON i.sender_id = su.id
     JOIN users ru ON i.receiver_id = ru.id
     WHERE (i.sender_id=? OR i.receiver_id=?) AND i.status <> 'withdrawn'
     ORDER BY FIELD(i.status,'pending','success','rejected'), i.created_at DESC",
    [$childId, $childId]
);

$pendingCount = 0;
foreach($rishtas as $r) if($r['status']==='pending') $pendingCount++;

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-user-shield"></i> Guardian (Wali) Panel</h1>
        <p>Review, approve and monitor rishtas on behalf of your ward</p>
    </div>
</div>

<div class="container" style="padding:28px 20px;max-width:900px">

    <!-- Ward card -->
    <div class="card mb-3">
        <div class="card-body" style="display:flex;align-items:center;gap:16px">
            <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-size:26px;color:var(--gold);flex-shrink:0">
                <?= $child['gender']==='male'?'👳':'🧕' ?>
            </div>
            <div style="flex:1">
                <div style="font-size:18px;font-weight:700"><?=htmlspecialchars($child['full_name'])?></div>
                <div style="font-size:13px;color:var(--text-light)">
                    Your ward (<?=htmlspecialchars($parent['guardian_relation'] ?? 'Guardian')?>)
                    · <?=htmlspecialchars($child['city']??'')?> <?= isset($child['age']) && $child['age'] ? '· '.$child['age'].' yrs' : '' ?>
                </div>
            </div>
            <a href="view-profile.php?id=<?=$childId?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View Profile</a>
        </div>
    </div>

    <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.25);border-radius:var(--radius);padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--text-medium)">
        <i class="fas fa-shield-alt" style="color:var(--gold-dark)"></i>
        A rishta becomes <strong>successful only when both guardians approve</strong>. You can read every message your ward exchanges. Please act with taqwa and care.
    </div>

    <h3 style="font-size:16px;margin-bottom:14px"><i class="fas fa-heart" style="color:var(--primary)"></i> Rishta Requests
        <?php if($pendingCount): ?><span class="badge badge-warning"><?=$pendingCount?> pending</span><?php endif; ?>
    </h3>

    <?php if(empty($rishtas)): ?>
    <div style="text-align:center;padding:50px;background:white;border-radius:var(--radius);border:1px solid var(--border)">
        <div style="font-size:44px;margin-bottom:12px">💌</div>
        <h3>No rishta requests yet</h3>
        <p style="color:var(--text-medium);margin-top:8px">When your ward expresses (or receives) interest, it will appear here for your approval.</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px">
        <?php foreach($rishtas as $r): ?>
        <?php
            $childIsSender = ($r['sender_id'] == $childId);
            $otherId = $childIsSender ? $r['receiver_id'] : $r['sender_id'];
            $otherName = $childIsSender ? $r['receiver_name'] : $r['sender_name'];
            $otherGender = $childIsSender ? $r['receiver_gender'] : $r['sender_gender'];
            $myApproval = $childIsSender ? $r['sender_parent_approved'] : $r['receiver_parent_approved'];
            $otherApproval = $childIsSender ? $r['receiver_parent_approved'] : $r['sender_parent_approved'];
            $statusBadge = [
                'pending' => ['warning','clock','Awaiting approval'],
                'success' => ['success','check-circle','Rishta Approved'],
                'rejected'=> ['danger','times-circle','Rejected'],
            ][$r['status']] ?? ['info','info-circle',ucfirst($r['status'])];
        ?>
        <div class="card">
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--gold);flex-shrink:0">
                        <?= $otherGender==='male'?'👳':'🧕' ?>
                    </div>
                    <div style="flex:1">
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                            <span style="font-size:16px;font-weight:600"><?=htmlspecialchars($otherName)?></span>
                            <span class="badge badge-<?=$statusBadge[0]?>"><i class="fas fa-<?=$statusBadge[1]?>"></i> <?=$statusBadge[2]?></span>
                            <span style="font-size:12px;color:var(--text-light)"><?= $childIsSender ? 'Your ward expressed interest' : 'Expressed interest in your ward' ?></span>
                        </div>
                        <?php if($r['message']): ?>
                        <div style="margin-top:8px;background:var(--cream);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--text-medium);font-style:italic">"<?=htmlspecialchars($r['message'])?>"</div>
                        <?php endif; ?>
                        <?php if(in_array($r['status'],['pending','success'])): ?>
                        <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap">
                            <span style="font-size:12px;color:<?=$myApproval?'var(--primary)':'var(--text-light)'?>">
                                <i class="fas fa-<?=$myApproval?'check-circle':'clock'?>"></i> You <?=$myApproval?'approved':'(pending)'?>
                            </span>
                            <span style="font-size:12px;color:<?=$otherApproval?'var(--primary)':'var(--text-light)'?>">
                                <i class="fas fa-<?=$otherApproval?'check-circle':'clock'?>"></i> Other guardian <?=$otherApproval?'approved':'(pending)'?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if($r['status']==='success' && $r['visit_status']!=='none'): ?>
                        <div style="font-size:13px;color:var(--text-dark);margin-top:8px">
                            <i class="fas fa-calendar-check" style="color:var(--primary)"></i>
                            Visit <?=$r['visit_status']?> <?= $r['visit_date'] ? 'for '.date('d M Y', strtotime($r['visit_date'])) : '' ?>
                        </div>
                        <?php endif; ?>
                        <div style="font-size:12px;color:var(--text-light);margin-top:6px"><?=timeAgo($r['created_at'])?></div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
                        <a href="view-profile.php?id=<?=$otherId?>" class="btn btn-outline btn-sm"><i class="fas fa-user"></i> Profile</a>
                        <a href="view-chat.php?a=<?=$r['sender_id']?>&b=<?=$r['receiver_id']?>" class="btn btn-outline btn-sm"><i class="fas fa-comments"></i> View Chat</a>
                        <?php if($r['status']==='pending' && !$myApproval): ?>
                        <a href="?action=approve&id=<?=$r['id']?>" class="btn btn-primary btn-sm" onclick="return confirm('Approve this rishta on behalf of your ward?')"><i class="fas fa-check"></i> Approve</a>
                        <a href="?action=reject&id=<?=$r['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Decline this rishta?')"><i class="fas fa-times"></i> Reject</a>
                        <?php elseif($r['status']==='pending' && $myApproval): ?>
                        <span class="badge badge-success" style="text-align:center"><i class="fas fa-check"></i> You approved</span>
                        <a href="?action=reject&id=<?=$r['id']?>" class="btn btn-outline btn-sm" onclick="return confirm('Withdraw your approval and decline this rishta?')">Decline</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
