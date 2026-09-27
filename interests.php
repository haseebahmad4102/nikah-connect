<?php
$pageTitle = 'Interests';
require_once 'includes/db.php';
requireLogin();

// Guardians manage rishtas from their own panel
if(isParent()) { header('Location: '.SITE_URL.'/guardian.php'); exit; }

$user = currentUser();

// Handle visit scheduling actions (approval is done by parents, NOT here)
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $intId = (int)$_GET['id'];
    $interest = db()->fetchOne(
        "SELECT * FROM interests WHERE id=? AND (sender_id=? OR receiver_id=?)",
        [$intId, $user['id'], $user['id']]
    );
    if($interest && $interest['status'] === 'success') {
        $otherId = $interest['sender_id'] == $user['id'] ? $interest['receiver_id'] : $interest['sender_id'];
        if($action === 'propose_visit') {
            $date = $_POST['visit_date'] ?? ($_GET['date'] ?? '');
            if($date && strtotime($date)) {
                db()->execute(
                    "UPDATE interests SET visit_status='proposed', visit_date=?, visit_proposed_by=? WHERE id=?",
                    [date('Y-m-d', strtotime($date)), $user['id'], $intId]
                );
                $niceDate = date('d M Y', strtotime($date));
                addNotification($otherId, 'visit', htmlspecialchars($user['full_name']).' proposed a visit on '.$niceDate.'. Please confirm.', SITE_URL.'/interests.php');
                notifyGuardian($otherId, 'visit', htmlspecialchars($user['full_name']).' proposed a visit on '.$niceDate.'.', SITE_URL.'/guardian.php');
                notifyGuardian($user['id'], 'visit', 'A visit on '.$niceDate.' was proposed for your ward.', SITE_URL.'/guardian.php');
            }
        } elseif($action === 'confirm_visit' && $interest['visit_status'] === 'proposed' && $interest['visit_proposed_by'] != $user['id']) {
            db()->execute("UPDATE interests SET visit_status='confirmed' WHERE id=?", [$intId]);
            $niceDate = date('d M Y', strtotime($interest['visit_date']));
            addNotification($otherId, 'visit', htmlspecialchars($user['full_name']).' confirmed the visit on '.$niceDate.'. May Allah bless it!', SITE_URL.'/interests.php');
            notifyGuardian($otherId, 'visit', 'The visit on '.$niceDate.' is confirmed.', SITE_URL.'/guardian.php');
            notifyGuardian($user['id'], 'visit', 'The visit on '.$niceDate.' is confirmed.', SITE_URL.'/guardian.php');
        }
    }
    header('Location: '.SITE_URL.'/interests.php?tab='.($_GET['tab'] ?? 'received'));
    exit;
}

$tab = $_GET['tab'] ?? 'received';

$received = db()->fetchAll(
    "SELECT i.*, u.full_name, u.gender, p.city, p.age, p.religiosity FROM interests i
     JOIN users u ON i.sender_id = u.id
     LEFT JOIN profiles p ON u.id = p.user_id
     WHERE i.receiver_id = ? ORDER BY i.created_at DESC",
    [$user['id']]
);

$sent = db()->fetchAll(
    "SELECT i.*, u.full_name, u.gender, p.city, p.age FROM interests i
     JOIN users u ON i.receiver_id = u.id
     LEFT JOIN profiles p ON u.id = p.user_id
     WHERE i.sender_id = ? ORDER BY i.created_at DESC",
    [$user['id']]
);

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-heart"></i> Rishta Requests</h1>
        <p>Approval is handled by the guardians of both families</p>
    </div>
</div>

<div class="container" style="padding:28px 20px">

    <div style="background:rgba(26,107,60,0.06);border:1px solid rgba(26,107,60,0.2);border-radius:var(--radius);padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--text-medium)">
        <i class="fas fa-user-shield" style="color:var(--primary)"></i>
        <strong>How approval works:</strong> When you express interest, both guardians are notified and can view your chats.
        A rishta becomes <strong>successful only when both guardians approve</strong>. You cannot approve on your own.
        After success, you can agree on a visit date together.
    </div>

    <div class="tabs">
        <a href="?tab=received" class="tab <?=$tab==='received'?'active':''?>">
            <i class="fas fa-inbox"></i> Received (<?=count($received)?>)
        </a>
        <a href="?tab=sent" class="tab <?=$tab==='sent'?'active':''?>">
            <i class="fas fa-paper-plane"></i> Sent (<?=count($sent)?>)
        </a>
    </div>

    <?php $list = $tab==='received' ? $received : $sent; ?>

    <?php if(empty($list)): ?>
    <div style="text-align:center;padding:60px;background:white;border-radius:var(--radius);border:1px solid var(--border)">
        <div style="font-size:48px;margin-bottom:16px">💌</div>
        <h3>No rishta requests <?=$tab==='received'?'received':'sent'?> yet</h3>
        <p style="color:var(--text-medium);margin-top:8px">
            <?= $tab==='received' ? 'Complete your profile to attract more interests!' : 'Browse profiles and send interests!' ?>
        </p>
        <a href="profiles.php" class="btn btn-primary mt-2"><i class="fas fa-search"></i> Browse Profiles</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px">
        <?php foreach($list as $int): ?>
        <?php
            $profileId = $tab==='received' ? $int['sender_id'] : $int['receiver_id'];
            // My side's parent approval vs. the other side's parent approval
            if($tab==='received') { $myParentOk = $int['receiver_parent_approved']; $otherParentOk = $int['sender_parent_approved']; }
            else { $myParentOk = $int['sender_parent_approved']; $otherParentOk = $int['receiver_parent_approved']; }
            $statusBadge = [
                'pending' => ['warning','clock','Awaiting guardians'],
                'success' => ['success','check-circle','Rishta Approved'],
                'rejected'=> ['danger','times-circle','Rejected'],
                'withdrawn'=>['info','ban','Withdrawn'],
            ][$int['status']] ?? ['info','info-circle',ucfirst($int['status'])];
            $canChat = in_array($int['status'], ['pending','success']);
        ?>
        <div class="card">
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;color:var(--gold)">
                        <?= $int['gender']==='male'?'👳':'🧕' ?>
                    </div>
                    <div style="flex:1">
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                            <span style="font-size:16px;font-weight:600"><?=htmlspecialchars($int['full_name'])?></span>
                            <span class="badge badge-<?=$statusBadge[0]?>"><i class="fas fa-<?=$statusBadge[1]?>"></i> <?=$statusBadge[2]?></span>
                        </div>
                        <div style="font-size:13px;color:var(--text-light);margin-top:3px">
                            <?=htmlspecialchars($int['city']??'')?> · <?=($int['age']??'').' yrs'?> <?= isset($int['religiosity']) ? '· '.ucfirst($int['religiosity']) : '' ?>
                        </div>
                        <?php if($int['message']): ?>
                        <div style="margin-top:8px;background:var(--cream);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--text-medium);font-style:italic">
                            "<?=htmlspecialchars($int['message'])?>"
                        </div>
                        <?php endif; ?>

                        <?php if(in_array($int['status'], ['pending','success'])): ?>
                        <!-- Guardian approval progress -->
                        <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap">
                            <span style="font-size:12px;color:<?=$myParentOk?'var(--primary)':'var(--text-light)'?>">
                                <i class="fas fa-<?=$myParentOk?'check-circle':'clock'?>"></i> Your guardian <?=$myParentOk?'approved':'pending'?>
                            </span>
                            <span style="font-size:12px;color:<?=$otherParentOk?'var(--primary)':'var(--text-light)'?>">
                                <i class="fas fa-<?=$otherParentOk?'check-circle':'clock'?>"></i> Their guardian <?=$otherParentOk?'approved':'pending'?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <div style="font-size:12px;color:var(--text-light);margin-top:6px"><?=timeAgo($int['created_at'])?></div>

                        <?php if($int['status']==='success'): ?>
                        <!-- Visit scheduling -->
                        <div style="margin-top:12px;background:linear-gradient(135deg,rgba(26,107,60,0.06),rgba(201,168,76,0.05));border:1px solid rgba(26,107,60,0.2);border-radius:10px;padding:14px">
                            <div style="font-weight:600;font-size:13px;color:var(--primary);margin-bottom:8px"><i class="fas fa-calendar-check"></i> Family Visit</div>
                            <?php if($int['visit_status']==='confirmed'): ?>
                                <div style="font-size:13px;color:var(--text-dark)"><i class="fas fa-check-circle" style="color:var(--primary)"></i> Visit confirmed for <strong><?=date('d M Y', strtotime($int['visit_date']))?></strong>. May Allah bless it! 🤲</div>
                            <?php elseif($int['visit_status']==='proposed'): ?>
                                <div style="font-size:13px;color:var(--text-dark);margin-bottom:8px">Proposed date: <strong><?=date('d M Y', strtotime($int['visit_date']))?></strong></div>
                                <?php if($int['visit_proposed_by'] != $user['id']): ?>
                                    <a href="?action=confirm_visit&id=<?=$int['id']?>&tab=<?=$tab?>" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Confirm this date</a>
                                    <form method="POST" action="?action=propose_visit&id=<?=$int['id']?>&tab=<?=$tab?>" style="display:inline-flex;gap:6px;align-items:center;margin-left:8px">
                                        <input type="date" name="visit_date" required style="padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px">
                                        <button class="btn btn-outline btn-sm" type="submit">Propose another</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:12px;color:var(--text-light)">Waiting for the other family to confirm…</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <form method="POST" action="?action=propose_visit&id=<?=$int['id']?>&tab=<?=$tab?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                                    <input type="date" name="visit_date" required style="padding:8px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px">
                                    <button class="btn btn-gold btn-sm" type="submit"><i class="fas fa-calendar-plus"></i> Propose visit date</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
                        <a href="view-profile.php?id=<?=$profileId?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View</a>
                        <?php if($canChat): ?>
                        <a href="messages.php?with=<?=$profileId?>" class="btn btn-gold btn-sm"><i class="fas fa-comments"></i> Chat</a>
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
