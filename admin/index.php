<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/db.php';
requireAdmin();

$admin = currentUser();

// Stats
$totalUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='user'")['c'] ?? 0;
$activeUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='user' AND is_active=1")['c'] ?? 0;
$maleUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE gender='male' AND role='user'")['c'] ?? 0;
$femaleUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE gender='female' AND role='user'")['c'] ?? 0;
$totalProfiles = db()->fetchOne("SELECT COUNT(*) as c FROM profiles")['c'] ?? 0;
$pendingWali = db()->fetchOne("SELECT COUNT(*) as c FROM profiles WHERE wali_approved=0")['c'] ?? 0;
$totalInterests = db()->fetchOne("SELECT COUNT(*) as c FROM interests")['c'] ?? 0;
$successfulMatches = db()->fetchOne("SELECT COUNT(*) as c FROM interests WHERE status='success'")['c'] ?? 0;
$totalMessages = db()->fetchOne("SELECT COUNT(*) as c FROM messages")['c'] ?? 0;
$flaggedMessages = db()->fetchOne("SELECT COUNT(*) as c FROM messages WHERE is_flagged=1")['c'] ?? 0;

// Recent users
$recentUsers = db()->fetchAll(
    "SELECT u.*, p.age, p.city FROM users u LEFT JOIN profiles p ON u.id=p.user_id WHERE u.role='user' ORDER BY u.created_at DESC LIMIT 8"
);

require_once '../includes/header.php';
?>

<div class="page-header" style="background:linear-gradient(135deg,#1a1a2e,#16213e)">
    <div class="container">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <p>NikahConnect — Platform Management & Monitoring</p>
    </div>
</div>

<div style="background:#1a1a2e;padding:0;border-bottom:1px solid #2a2a4a">
    <div class="container" style="display:flex;align-items:center;gap:0">
        <?php $adminNav = [
            ['index.php','fas fa-th-large','Dashboard'],
            ['users.php','fas fa-users','Users'],
            ['profiles.php','fas fa-id-card','Profiles'],
            ['messages.php','fas fa-comments','Messages'],
            ['interests.php','fas fa-heart','Interests'],
            ['reports.php','fas fa-chart-bar','Reports'],
        ];
        $curr = basename($_SERVER['PHP_SELF']);
        foreach($adminNav as $n): ?>
        <a href="<?=SITE_URL?>/admin/<?=$n[0]?>" style="padding:14px 18px;color:<?=$curr===$n[0]?'var(--gold-light)':'rgba(255,255,255,0.6)'?>;font-size:13px;font-weight:500;text-decoration:none;border-bottom:2px solid <?=$curr===$n[0]?'var(--gold)':'transparent'?>;display:flex;align-items:center;gap:7px;transition:all 0.2s">
            <i class="<?=$n[1]?>"></i> <?=$n[2]?>
        </a>
        <?php endforeach; ?>
        <div style="margin-left:auto;padding:10px 18px">
            <a href="<?=SITE_URL?>/index.php" style="color:rgba(255,255,255,0.5);font-size:12px"><i class="fas fa-external-link-alt"></i> View Site</a>
        </div>
    </div>
</div>

<div class="container" style="padding:28px 20px">

    <!-- Stats Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin-bottom:26px">
        <?php $stats = [
            ['Total Users',$totalUsers,'fas fa-users','green'],
            ['Active Users',$activeUsers,'fas fa-user-check','blue'],
            ['Male / Female',$maleUsers.' / '.$femaleUsers,'fas fa-venus-mars','gold'],
            ['Successful Matches',$successfulMatches,'fas fa-heart','green'],
            ['Total Interests',$totalInterests,'fas fa-paper-plane','blue'],
            ['Pending Wali Approval',$pendingWali,'fas fa-clock','red'],
            ['Total Messages',$totalMessages,'fas fa-comments','gold'],
            ['Flagged Messages',$flaggedMessages,'fas fa-flag','red'],
        ];
        foreach($stats as $s): ?>
        <div class="stat-card">
            <div class="stat-icon <?=$s[3]?>"><i class="<?=$s[2]?>"></i></div>
            <div class="stat-info">
                <div class="num"><?=$s[0]?></div>
                <div class="label"><?=$s[1]?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:22px">

        <!-- Recent Users -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-users"></i> Recent Registrations</div>
                <a href="users.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Location</th>
                            <th>Age</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentUsers as $u): ?>
                        <tr>
                            <td style="color:var(--text-light)">#<?=$u['id']?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-size:14px">
                                        <?=$u['gender']==='male'?'👳':'🧕'?>
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($u['full_name'])?></div>
                                        <div style="font-size:11px;color:var(--text-light)"><?=htmlspecialchars($u['email'])?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-<?=$u['gender']==='male'?'info':'gold'?>"><?=ucfirst($u['gender'])?></span></td>
                            <td style="font-size:13px"><?=htmlspecialchars($u['city']??'N/A')?></td>
                            <td style="font-size:13px"><?=$u['age']??'N/A'?></td>
                            <td><span class="badge badge-<?=$u['is_active']?'success':'danger'?>"><?=$u['is_active']?'Active':'Inactive'?></span></td>
                            <td style="font-size:12px;color:var(--text-light)"><?=date('d M y',strtotime($u['created_at']))?></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="<?=SITE_URL?>/view-profile.php?id=<?=$u['id']?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="users.php?toggle=<?=$u['id']?>&active=<?=$u['is_active']?>" class="btn btn-<?=$u['is_active']?'danger':'primary'?> btn-sm">
                                        <i class="fas fa-<?=$u['is_active']?'ban':'check'?>"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions & Alerts -->
        <div>
            <div class="card mb-3">
                <div class="card-header"><div class="card-title"><i class="fas fa-bolt"></i> Quick Actions</div></div>
                <div class="card-body">
                    <?php $actions = [
                        ['View Flagged Messages','fas fa-flag','messages.php?filter=flagged','danger'],
                        ['Pending Wali Approvals','fas fa-user-shield','profiles.php?filter=pending_wali','warning'],
                        ['Unverified Profiles','fas fa-id-card','profiles.php?filter=unverified','info'],
                        ['Generate Report','fas fa-chart-bar','reports.php','primary'],
                    ];
                    foreach($actions as $a): ?>
                    <a href="<?=$a[2]?>" style="display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text-dark);font-size:13px;font-weight:500">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(26,107,60,0.1);display:flex;align-items:center;justify-content:center">
                            <i class="<?=$a[1]?>" style="color:var(--primary);font-size:13px"></i>
                        </div>
                        <?=$a[0]?>
                        <i class="fas fa-chevron-right" style="margin-left:auto;font-size:11px;color:var(--text-light)"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if($flaggedMessages > 0): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong><?=$flaggedMessages?> flagged messages</strong> need review.
                    <a href="messages.php?filter=flagged" style="color:inherit;font-weight:600;display:block;margin-top:4px">Review now →</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if($pendingWali > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-user-shield"></i>
                <div>
                    <strong><?=$pendingWali?> profiles</strong> pending Wali approval.
                    <a href="profiles.php?filter=pending_wali" style="color:inherit;font-weight:600;display:block;margin-top:4px">Review →</a>
                </div>
            </div>
            <?php endif; ?>

            <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));border-radius:var(--radius);padding:20px;color:white;text-align:center">
                <div style="font-family:'Amiri',serif;font-size:20px;color:var(--gold-light);margin-bottom:8px">اللَّهُمَّ بَارِكْ</div>
                <p style="font-size:12px;color:rgba(255,255,255,0.8)">May Allah bless this platform and help Muslim families find righteous partners.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
