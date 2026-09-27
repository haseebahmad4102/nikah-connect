<?php
$pageTitle = 'Reports';
require_once '../includes/db.php';
requireAdmin();

$totalUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='user'")['c']??0;
$maleUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE gender='male' AND role='user'")['c']??0;
$femaleUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE gender='female' AND role='user'")['c']??0;
$activeUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='user' AND is_active=1")['c']??0;
$totalMatches = db()->fetchOne("SELECT COUNT(*) as c FROM interests WHERE status='success'")['c']??0;
$totalMessages = db()->fetchOne("SELECT COUNT(*) as c FROM messages")['c']??0;
$flagged = db()->fetchOne("SELECT COUNT(*) as c FROM messages WHERE is_flagged=1")['c']??0;
$waliApproved = db()->fetchOne("SELECT COUNT(*) as c FROM profiles WHERE wali_approved=1")['c']??0;

$monthlyUsers = db()->fetchAll(
    "SELECT DATE_FORMAT(created_at,'%b %Y') as month, COUNT(*) as count FROM users WHERE role='user' GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY created_at DESC LIMIT 6"
);

require_once '../includes/header.php';
?>
<div class="page-header" style="background:linear-gradient(135deg,#1a1a2e,#16213e)">
    <div class="container"><h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1></div>
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
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:24px">
        <?php $stats=[
            ['Total Registered',$totalUsers,'fas fa-users','green'],
            ['Male Members',$maleUsers,'fas fa-male','blue'],
            ['Female Members',$femaleUsers,'fas fa-female','gold'],
            ['Active Profiles',$activeUsers,'fas fa-user-check','green'],
            ['Successful Matches',$totalMatches,'fas fa-heart','gold'],
            ['Total Messages',$totalMessages,'fas fa-comments','blue'],
            ['Flagged Messages',$flagged,'fas fa-flag','red'],
            ['Wali Approved',$waliApproved,'fas fa-shield-alt','green'],
        ];
        foreach($stats as $s): ?>
        <div class="stat-card">
            <div class="stat-icon <?=$s[3]?>"><i class="<?=$s[2]?>"></i></div>
            <div class="stat-info"><div class="num"><?=$s[1]?></div><div class="label"><?=$s[0]?></div></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-chart-pie"></i> Gender Distribution</div></div>
            <div class="card-body">
                <div style="display:flex;gap:20px;align-items:center">
                    <div style="flex:1">
                        <?php $total = max(1,$totalUsers); ?>
                        <div style="margin-bottom:14px">
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
                                <span>👳 Brothers (Male)</span>
                                <span style="font-weight:600"><?=$maleUsers?> (<?=round($maleUsers/$total*100)?>%)</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill" style="width:<?=round($maleUsers/$total*100)?>%;background:linear-gradient(to right,#3b82f6,#1d4ed8)"></div></div>
                        </div>
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
                                <span>🧕 Sisters (Female)</span>
                                <span style="font-weight:600"><?=$femaleUsers?> (<?=round($femaleUsers/$total*100)?>%)</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill" style="width:<?=round($femaleUsers/$total*100)?>%;background:linear-gradient(to right,var(--gold),var(--gold-dark))"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-calendar"></i> Monthly Registrations</div></div>
            <div class="card-body">
                <?php foreach(array_reverse($monthlyUsers) as $m): ?>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                    <span style="font-size:12px;color:var(--text-light);width:70px"><?=$m['month']?></span>
                    <div class="progress-bar" style="flex:1"><div class="progress-fill" style="width:<?=min(100,$m['count']*10)?>%"></div></div>
                    <span style="font-size:13px;font-weight:600;width:30px;text-align:right"><?=$m['count']?></span>
                </div>
                <?php endforeach; ?>
                <?php if(empty($monthlyUsers)): ?>
                <p style="color:var(--text-light);font-size:13px;text-align:center">No data yet</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-check-circle"></i> Platform Health</div></div>
            <div class="card-body">
                <?php $health = [
                    ['Wali Approval Rate', $totalUsers>0?round($waliApproved/$totalUsers*100):0, 'success'],
                    ['Match Success Rate', $totalUsers>0?min(99,round($totalMatches/$totalUsers*100)):0, 'gold'],
                    ['Message Safety Rate', $totalMessages>0?round(($totalMessages-$flagged)/$totalMessages*100):100, 'blue'],
                    ['Profile Completion', $totalUsers>0?round($activeUsers/$totalUsers*100):0, 'green'],
                ];
                foreach($health as $h): ?>
                <div style="margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px">
                        <span><?=$h[0]?></span>
                        <span style="font-weight:700;color:var(--primary)"><?=$h[1]?>%</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width:<?=$h[1]?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-info-circle"></i> Platform Summary</div></div>
            <div class="card-body">
                <div style="background:linear-gradient(135deg,rgba(26,107,60,0.08),rgba(201,168,76,0.05));border-radius:10px;padding:18px;margin-bottom:14px">
                    <div style="font-family:'Amiri',serif;font-size:20px;color:var(--primary);text-align:center;margin-bottom:8px">الْحَمْدُ لِلَّهِ</div>
                    <p style="font-size:13px;color:var(--text-medium);text-align:center;line-height:1.6">
                        NikahConnect has facilitated <strong><?=$totalMatches?> successful connections</strong> between Muslim families,
                        with <?=$waliApproved?> Wali-approved profiles maintaining Islamic standards.
                    </p>
                </div>
                <div style="font-size:12px;color:var(--text-light);border-top:1px solid var(--border);padding-top:12px">
                    Report generated: <?=date('d M Y, H:i')?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
