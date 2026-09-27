<?php
$pageTitle = 'Manage Profiles';
require_once '../includes/db.php';
requireAdmin();

if(isset($_GET['approve_wali'])) {
    db()->execute("UPDATE profiles SET wali_approved=1 WHERE user_id=?", [(int)$_GET['approve_wali']]);
    header('Location: profiles.php');
    exit;
}

$filter = $_GET['filter'] ?? '';
$where = $filter === 'pending_wali' ? 'WHERE p.wali_approved = 0' : '';

$profiles = db()->fetchAll(
    "SELECT u.full_name, u.email, u.gender, p.*, w.wali_name, w.wali_consent
     FROM profiles p
     JOIN users u ON p.user_id = u.id
     LEFT JOIN walis w ON p.user_id = w.user_id
     $where
     ORDER BY p.created_at DESC"
);

require_once '../includes/header.php';
?>

<div class="page-header" style="background:linear-gradient(135deg,#1a1a2e,#16213e)">
    <div class="container"><h1><i class="fas fa-id-card"></i> Manage Profiles</h1><p>Review and manage user profiles</p></div>
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
        <a href="profiles.php" class="btn <?=$filter===''?'btn-primary':'btn-outline'?> btn-sm">All Profiles</a>
        <a href="profiles.php?filter=pending_wali" class="btn <?=$filter==='pending_wali'?'btn-gold':'btn-outline'?> btn-sm">
            <i class="fas fa-user-shield"></i> Pending Wali Approval
        </a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Gender</th><th>Age/City</th><th>Education</th><th>Religiosity</th><th>Wali</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($profiles as $p): ?>
                    <tr>
                        <td>#<?=$p['user_id']?></td>
                        <td>
                            <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($p['full_name'])?></div>
                            <div style="font-size:11px;color:var(--text-light)"><?=htmlspecialchars($p['email'])?></div>
                        </td>
                        <td><span class="badge badge-<?=$p['gender']==='male'?'info':'gold'?>"><?=ucfirst($p['gender'])?></span></td>
                        <td><?=($p['age']??'?').' · '.htmlspecialchars($p['city']??'N/A')?></td>
                        <td style="font-size:13px"><?=htmlspecialchars($p['education']??'N/A')?></td>
                        <td><span class="badge badge-success"><?=ucfirst($p['religiosity']??'N/A')?></span></td>
                        <td>
                            <?=$p['wali_name']?htmlspecialchars($p['wali_name']):'<span class="badge badge-danger">None</span>'?>
                            <?php if($p['wali_name']): ?>
                            <span class="badge badge-<?=$p['wali_consent']?'success':'warning'?>"><?=$p['wali_consent']?'✓':'Pending'?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?=$p['wali_approved']?'success':'warning'?>"><?=$p['wali_approved']?'Approved':'Pending'?></span></td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="<?=SITE_URL?>/view-profile.php?id=<?=$p['user_id']?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                                <?php if(!$p['wali_approved']): ?>
                                <a href="?approve_wali=<?=$p['user_id']?>" class="btn btn-primary btn-sm" onclick="return confirm('Approve Wali for this profile?')"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
