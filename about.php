<?php
$pageTitle = 'About';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container"><h1><i class="fas fa-info-circle"></i> About NikahConnect</h1><p>A Halal, Shariah-Compliant Matrimonial Platform for Muslim Families</p></div>
</div>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center">
            <div>
                <div class="section-badge">Our Mission</div>
                <h2 class="section-title">A <span>Halal</span> Alternative for Muslim Families</h2>
                <p style="color:var(--text-medium);line-height:1.8;margin-bottom:16px">
                    NikahConnect was born from the recognition that Muslim families deserve a matrimonial platform that genuinely respects and follows Islamic principles — not just claims to.
                </p>
                <p style="color:var(--text-medium);line-height:1.8;margin-bottom:16px">
                    Most existing platforms promote dating culture, free mixing, and anonymous interactions — all of which conflict with Islamic values of modesty, Guardian involvement, and family participation.
                </p>
                <p style="color:var(--text-medium);line-height:1.8">
                    NikahConnect addresses this by building a platform where <strong>every feature</strong> is designed around Shariah principles.
                </p>
            </div>
            <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));border-radius:var(--radius-lg);padding:36px;color:white;text-align:center">
                <div style="font-family:'Amiri',serif;font-size:32px;color:var(--gold-light);margin-bottom:16px">وَمِنْ آيَاتِهِ</div>
                <p style="font-size:15px;line-height:1.8;color:rgba(255,255,255,0.9)">
                    "And of His signs is that He created for you from yourselves mates that you may find tranquility in them; and He placed between you affection and mercy."
                </p>
                <p style="color:var(--gold-light);font-size:14px;margin-top:10px">— Quran 30:21</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--cream-dark)">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Project Details</div>
            <h2 class="section-title">About This <span>Project</span></h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:22px">
            <?php $details = [
                ['Project Title','NikahConnect — Islamic Matrimonial Platform','fas fa-project-diagram'],
                ['Platform Type','Halal Shariah-Compliant Matchmaking','fas fa-heart'],
                ['Technology Stack','PHP, MySQL, HTML5, CSS3, JavaScript','fas fa-code'],
                ['Architecture','Procedural PHP with Prepared SQL Statements','fas fa-cubes'],
                ['Guardian Support','Integrated Wali & Family Involvement System','fas fa-user-shield'],
                ['SDLC Model','Agile (Sprint-based development)','fas fa-sync'],
                ['Communication','Monitored Halal Chat with Modesty Filters','fas fa-comments'],
                ['Security','Role-based Access & Password Hashing','fas fa-shield-alt'],
            ];
            foreach($details as $d): ?>
            <div style="background:white;border:1px solid var(--border);border-radius:var(--radius);padding:20px">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                    <i class="<?=$d[2]?>" style="color:var(--primary);font-size:16px"></i>
                    <span style="font-size:12px;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:0.5px"><?=$d[0]?></span>
                </div>
                <div style="font-size:14px;font-weight:500;color:var(--text-dark)"><?=$d[1]?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Key <span>Features</span> & Objectives</h2>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <?php $features=[
                ['Guardian (Wali) Involvement','Every profile requires a Wali (Guardian). All activity is transparent to them.',  'fas fa-user-shield'],
                ['Halal Communication','Messages are monitored, modesty filters active, Wali can view all chats.','fas fa-comments'],
                ['Preference Matching','Smart algorithm matches based on deen, education, location, and family values.','fas fa-heart'],
                ['Admin Supervision','Complete platform oversight with flagging, approval, and reporting tools.','fas fa-shield-alt'],
                ['Privacy Protection','Female profiles have enhanced privacy. No personal info shared without consent.','fas fa-lock'],
                ['Family-Oriented','Designed for entire family participation, not individual dating.','fas fa-home'],
            ];
            foreach($features as $f): ?>
            <div style="display:flex;gap:14px;padding:18px;background:white;border:1px solid var(--border);border-radius:var(--radius)">
                <div style="width:42px;height:42px;border-radius:10px;background:rgba(26,107,60,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="<?=$f[2]?>" style="color:var(--primary)"></i>
                </div>
                <div>
                    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><?=$f[0]?></div>
                    <div style="font-size:13px;color:var(--text-medium);line-height:1.5"><?=$f[1]?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
