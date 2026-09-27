<?php
$pageTitle = 'Manage Users';
require_once '../includes/db.php';
requireAdmin();

// Toggle active status
if(isset($_GET['toggle']) && isset($_GET['active'])) {
    $userId = (int)$_GET['toggle'];
    $newStatus = $_GET['active'] == '1' ? 0 : 1;
    db()->execute("UPDATE users SET is_active=? WHERE id=? AND role='user'", [$newStatus, $userId]);
    header('Location: users.php');
    exit;
}

// Approve wali
if(isset($_GET['approve_wali'])) {
    $userId = (int)$_GET['approve_wali'];
    db()->execute("UPDATE profiles SET wali_approved=1 WHERE user_id=?", [$userId]);
    db()->execute("UPDATE walis SET wali_consent=1, consent_date=NOW() WHERE user_id=?", [$userId]);
    header('Location: users.php');
    exit;
}

$filter = $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');
$gender = $_GET['gender'] ?? '';

$where = ["u.role = 'user'"];
$params = [];

if($search) { $where[] = "(u.full_name LIKE ? OR u.email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if($gender) { $where[] = "u.gender = ?"; $params[] = $gender; }
if($filter === 'active') $where[] = "u.is_active = 1";
elseif($filter === 'inactive') $where[] = "u.is_active = 0";
elseif($filter === 'pending_wali') $where[] = "p.wali_approved = 0";

$users = db()->fetchAll(
    "SELECT u.*, p.age, p.city, p.wali_approved, w.wali_name, w.wali_consent
     FROM users u
     LEFT JOIN profiles p ON u.id = p.user_id
     LEFT JOIN walis w ON u.id = w.user_id
     WHERE " . implode(' AND ', $where) . " ORDER BY u.created_at DESC",
    $params
);

require_once '../includes/header.php';
?>

<div class="page-header" style="background:linear-gradient(135deg,#1a1a2e,#16213e)">
    <div class="container">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <p><?=count($users)?> users found</p>
    </div>
</div>

<div style="background:#1a1a2e;padding:0;border-bottom:1px solid #2a2a4a">
    <div class="container" style="display:flex;align-items:center;gap:0">
        <?php $adminNav=[['index.php','Dashboard'],['users.php','Users'],['profiles.php','Profiles'],['messages.php','Messages'],['interests.php','Interests'],['reports.php','Reports']];
        $curr=basename($_SERVER['PHP_SELF']);
        foreach($adminNav as $n): ?>
        <a href="<?=SITE_URL?>/admin/<?=$n[0]?>" style="padding:14px 18px;color:<?=$curr===$n[0]?'var(--gold-light)':'rgba(255,255,255,0.6)'?>;font-size:13px;font-weight:500;text-decoration:none;border-bottom:2px solid <?=$curr===$n[0]?'var(--gold)':'transparent'?>">
            <?=$n[1]?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="container" style="padding:24px 20px">

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body" style="padding:14px 18px">
            <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
                <div>
                    <label style="font-size:12px;color:var(--text-light);display:block;margin-bottom:4px">Search</label>
                    <input type="text" name="search" value="<?=htmlspecialchars($search)?>" placeholder="Name or email..." style="padding:8px 12px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;font-family:inherit">
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-light);display:block;margin-bottom:4px">Gender</label>
                    <select name="gender" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;font-family:inherit">
                        <option value="">All Genders</option>
                        <option value="male" <?=$gender==='male'?'selected':''?>>Male</option>
                        <option value="female" <?=$gender==='female'?'selected':''?>>Female</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-light);display:block;margin-bottom:4px">Filter</label>
                    <select name="filter" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;font-family:inherit">
                        <option value="">All Users</option>
                        <option value="active" <?=$filter==='active'?'selected':''?>>Active</option>
                        <option value="inactive" <?=$filter==='inactive'?'selected':''?>>Inactive</option>
                        <option value="pending_wali" <?=$filter==='pending_wali'?'selected':''?>>Pending Wali</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
                <a href="users.php" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Age / City</th>
                        <th>Wali</th>
                        <th>Profile</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-light)">No users found</td></tr>
                    <?php else: ?>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td style="color:var(--text-light);font-size:12px">#<?=$u['id']?></td>
                        <td>
                            <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($u['full_name'])?></div>
                            <div style="font-size:11px;color:var(--text-light)"><?=htmlspecialchars($u['email'])?></div>
                        </td>
                        <td><span class="badge badge-<?=$u['gender']==='male'?'info':'gold'?>"><?=$u['gender']==='male'?'👳':'🧕'?> <?=ucfirst($u['gender'])?></span></td>
                        <td style="font-size:13px"><?=($u['age']??'?').' yrs · '.htmlspecialchars($u['city']??'N/A')?></td>
                        <td>
                            <?php if($u['wali_name']): ?>
                            <div style="font-size:12px;font-weight:500"><?=htmlspecialchars($u['wali_name'])?></div>
                            <span class="badge badge-<?=$u['wali_consent']?'success':'warning'?>"><?=$u['wali_consent']?'Approved':'Pending'?></span>
                            <?php else: ?>
                            <span class="badge badge-danger">No Wali</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?=$u['wali_approved']?'success':'warning'?>">
                                <?=$u['wali_approved']?'Approved':'Pending'?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?=$u['is_active']?'success':'danger'?>">
                                <?=$u['is_active']?'Active':'Inactive'?>
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-light)"><?=date('d M Y',strtotime($u['created_at']))?></td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="<?=SITE_URL?>/view-profile.php?id=<?=$u['id']?>" class="btn btn-outline btn-sm" title="View Profile"><i class="fas fa-eye"></i></a>
                                <?php if(!$u['wali_approved']): ?>
                                <a href="?approve_wali=<?=$u['id']?>" class="btn btn-primary btn-sm" title="Approve Wali" onclick="return confirm('Approve Wali for this profile?')"><i class="fas fa-shield-alt"></i></a>
                                <?php endif; ?>
                                <a href="?toggle=<?=$u['id']?>&active=<?=$u['is_active']?>" class="btn btn-<?=$u['is_active']?'danger':'primary'?> btn-sm" title="<?=$u['is_active']?'Deactivate':'Activate'?>" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-<?=$u['is_active']?'ban':'check'?>"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
