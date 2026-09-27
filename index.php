<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get recent profiles for homepage preview
$recentProfiles = db()->fetchAll(
    "SELECT u.*, p.* FROM users u
     JOIN profiles p ON u.id = p.user_id
     WHERE u.is_active = 1 AND p.wali_approved = 1 AND u.role = 'user'
     ORDER BY u.created_at DESC LIMIT 6"
);

$totalUsers = db()->fetchOne("SELECT COUNT(*) as c FROM users WHERE role='user' AND is_active=1")['c'] ?? 0;
$totalMatches = db()->fetchOne("SELECT COUNT(*) as c FROM interests WHERE status='success'")['c'] ?? 0;
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-pattern"></div>
    <div class="hero-circles"></div>
    <div class="hero-content">
        <div class="hero-text animate-in">
            <div class="hero-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
            <div class="hero-badge"><i class="fas fa-star"></i> Shariah-Compliant Platform</div>
            <h1 class="hero-title">Find Your <span>Halal</span> Life Partner</h1>
            <p class="hero-subtitle">
                NikahConnect is a trusted Islamic matrimonial platform that puts your <strong>deen first</strong>.
                With Guardian (Wali) involvement, halal communication, and preference-based matching —
                we help Muslim families find righteous life partners the Islamic way.
            </p>
            <div class="hero-cta">
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-gold btn-lg">
                    <i class="fas fa-user-plus"></i> Create Profile Free
                </a>
                <a href="<?= SITE_URL ?>/profiles.php" class="btn btn-lg" style="background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.3)">
                    <i class="fas fa-search"></i> Browse Profiles
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="num"><?= number_format($totalUsers + 500) ?>+</div>
                    <div class="label">Registered Muslims</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <div class="num"><?= number_format($totalMatches + 120) ?>+</div>
                    <div class="label">Successful Matches</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <div class="num">100%</div>
                    <div class="label">Halal & Shariah-Safe</div>
                </div>
            </div>
        </div>

        <div class="hero-card animate-in delay-2">
            <h3><i class="fas fa-search"></i> Quick Search</h3>
            <form class="quick-search-form" action="<?= SITE_URL ?>/profiles.php" method="GET">
                <div class="form-group">
                    <select name="gender">
                        <option value="">I am looking for...</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="country">
                        <option value="">Select Country</option>
                        <option value="Pakistan" selected>Pakistan</option>
                        <option value="India">India</option>
                        <option value="Bangladesh">Bangladesh</option>
                        <option value="UK">United Kingdom</option>
                        <option value="USA">United States</option>
                        <option value="Canada">Canada</option>
                        <option value="UAE">UAE</option>
                        <option value="Saudi Arabia">Saudi Arabia</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="form-group">
                        <select name="age_min">
                            <option value="18">Age Min: 18</option>
                            <?php for($i=19;$i<=50;$i++): ?>
                            <option value="<?=$i?>"><?=$i?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="age_max">
                            <?php for($i=20;$i<=60;$i++): ?>
                            <option value="<?=$i?>" <?=$i==35?'selected':''?>><?=$i?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <select name="religiosity">
                        <option value="">Religiosity</option>
                        <option value="practicing">Practicing (Alhamdulillah)</option>
                        <option value="moderate">Moderate Muslim</option>
                        <option value="learning">Learning Deen</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-gold btn-block">
                    <i class="fas fa-search"></i> Search Profiles
                </button>
            </form>
            <div style="text-align:center;margin-top:14px;font-size:12px;color:rgba(255,255,255,0.6)">
                <i class="fas fa-shield-alt" style="color:var(--gold-light)"></i>
                All profiles are Guardian-approved
            </div>
        </div>
    </div>
</section>

<!-- QURAN VERSE -->
<div class="quran-banner">
    <div class="quran-arabic">وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا</div>
    <div class="quran-translation">"And of His signs is that He created for you from yourselves mates that you may find tranquility in them"</div>
    <div class="quran-reference">— Quran 30:21</div>
</div>

<!-- FEATURES -->
<section class="section" style="background:var(--white)">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Why NikahConnect</div>
            <h2 class="section-title">Built on <span>Islamic Principles</span></h2>
            <p class="section-subtitle">Every feature is designed to align with Shariah guidelines and respect Islamic values of modesty and family participation</p>
        </div>
        <div class="features-grid">
            <div class="feature-card animate-in">
                <div class="feature-icon">🛡️</div>
                <h3>Guardian (Wali) System</h3>
                <p>Every profile requires Wali (Guardian) approval. All interactions are visible to the designated Guardian, ensuring proper Islamic oversight.</p>
            </div>
            <div class="feature-card animate-in delay-1">
                <div class="feature-icon">💬</div>
                <h3>Halal Communication</h3>
                <p>Our restricted messaging system prevents inappropriate conversations. Messages follow Islamic guidelines with modesty filters active.</p>
            </div>
            <div class="feature-card animate-in delay-2">
                <div class="feature-icon">🤝</div>
                <h3>Preference Matching</h3>
                <p>Smart preference-based matching suggests compatible profiles based on deen, education, family values, and other Islamic criteria.</p>
            </div>
            <div class="feature-card animate-in">
                <div class="feature-icon">🔒</div>
                <h3>Privacy Protected</h3>
                <p>Female profiles have enhanced privacy by default. Contact information is only shared after Wali approval and mutual interest.</p>
            </div>
            <div class="feature-card animate-in delay-1">
                <div class="feature-icon">👨‍👩‍👧</div>
                <h3>Family-Oriented</h3>
                <p>Designed for family involvement from day one. Parents and guardians are integral to the matchmaking process — as Islam teaches.</p>
            </div>
            <div class="feature-card animate-in delay-2">
                <div class="feature-icon">✅</div>
                <h3>Verified Profiles</h3>
                <p>Every profile is reviewed by our team for authenticity. We reject fake, inappropriate, or non-serious profiles immediately.</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" style="background:var(--cream)">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Process</div>
            <h2 class="section-title">How <span>NikahConnect</span> Works</h2>
            <p class="section-subtitle">A simple, transparent, and halal process from registration to Nikah</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create Your Profile</h3>
                <p>Register and create a detailed profile with your preferences, Islamic values, and family background.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Add Your Wali</h3>
                <p>Add your Guardian (Wali) details — father, brother, or uncle. They receive notifications for all activity.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Receive Matches</h3>
                <p>Our system suggests compatible profiles based on your preferences and Islamic criteria.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Express Interest</h3>
                <p>Send a respectful interest request. The other family reviews your profile with their Wali's guidance.</p>
            </div>
            <div class="step-card">
                <div class="step-number">5</div>
                <h3>Halal Communication</h3>
                <p>If interest is mutual, families communicate through our monitored, Wali-visible messaging system.</p>
            </div>
            <div class="step-card">
                <div class="step-number">6</div>
                <h3>Nikah Mubarak! 🌙</h3>
                <p>Families meet in person and proceed to Nikah with the blessing of Allah. May Allah bless the union!</p>
            </div>
        </div>
    </div>
</section>

<!-- RECENT PROFILES -->
<?php if(!empty($recentProfiles)): ?>
<section class="section" style="background:var(--white)">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Profiles</div>
            <h2 class="section-title">Recently Joined <span>Members</span></h2>
            <p class="section-subtitle">All profiles are verified and Guardian-approved</p>
        </div>
        <div class="profiles-grid">
            <?php foreach($recentProfiles as $p): ?>
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-avatar">
                        <?= $p['gender']==='male' ? '👳' : '🧕' ?>
                    </div>
                    <div class="profile-name"><?= htmlspecialchars($p['full_name']) ?></div>
                    <div class="profile-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['city'] ?? 'Pakistan') ?></div>
                    <?php if($p['wali_approved']): ?>
                    <div class="wali-badge"><i class="fas fa-shield-alt"></i> Wali Approved</div>
                    <?php endif; ?>
                </div>
                <div class="profile-card-body">
                    <div>
                        <span class="profile-info-pill"><i class="fas fa-birthday-cake"></i> <?= $p['age'] ?? 'N/A' ?> yrs</span>
                        <span class="profile-info-pill"><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($p['education'] ?? 'N/A') ?></span>
                        <span class="profile-info-pill"><i class="fas fa-mosque"></i> <?= ucfirst($p['religiosity'] ?? 'Practicing') ?></span>
                        <span class="profile-info-pill"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($p['occupation'] ?? 'N/A') ?></span>
                    </div>
                    <?php if($p['about_me']): ?>
                    <p class="profile-about"><?= htmlspecialchars($p['about_me']) ?></p>
                    <?php endif; ?>
                    <div class="profile-actions">
                        <a href="<?= SITE_URL ?>/view-profile.php?id=<?= $p['user_id'] ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
                            <i class="fas fa-eye"></i> View Profile
                        </a>
                        <?php if(isLoggedIn() && $_SESSION['user_id'] != $p['user_id']): ?>
                        <a href="<?= SITE_URL ?>/send-interest.php?to=<?= $p['user_id'] ?>" class="btn btn-gold btn-sm">
                            <i class="fas fa-heart"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:36px">
            <a href="<?= SITE_URL ?>/profiles.php" class="btn btn-primary btn-lg">
                <i class="fas fa-users"></i> View All Profiles
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TESTIMONIAL / HADITH -->
<section class="section" style="background:linear-gradient(135deg,#f8f4ec,#f0ebe0)">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Islamic Guidance</div>
            <h2 class="section-title">The Prophet ﷺ Said</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px">
            <?php
            $hadiths = [
                ['arabic'=>'النِّكَاحُ مِنْ سُنَّتِي','translation'=>'"Marriage is from my Sunnah (way of life)."','ref'=>'— Sunan Ibn Majah 1846'],
                ['arabic'=>'خَيْرُكُمْ خَيْرُكُمْ لِأَهْلِهِ','translation'=>'"The best of you is the one who is best to his wife."','ref'=>'— Sunan al-Tirmidhī 3895'],
                ['arabic'=>'تَزَوَّجُوا الْوَدُودَ الْوَلُودَ','translation'=>'"Marry the loving and fertile woman."','ref'=>'— Sunan Abu Dawud 2050'],
            ];
            foreach($hadiths as $h): ?>
            <div style="background:white;border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;text-align:center;box-shadow:var(--shadow)">
                <div style="font-family:'Amiri',serif;font-size:24px;color:var(--primary);direction:rtl;margin-bottom:14px"><?= $h['arabic'] ?></div>
                <p style="font-size:14px;color:var(--text-medium);font-style:italic;line-height:1.7;margin-bottom:8px"><?= $h['translation'] ?></p>
                <span style="font-size:12px;color:var(--gold-dark);font-weight:600"><?= $h['ref'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));padding:70px 20px;text-align:center">
    <div class="container">
        <div style="font-family:'Amiri',serif;font-size:30px;color:var(--gold-light);margin-bottom:12px">وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَى</div>
        <h2 style="color:white;font-size:32px;margin-bottom:14px">Start Your Halal Journey Today</h2>
        <p style="color:rgba(255,255,255,0.85);font-size:16px;max-width:560px;margin:0 auto 30px;line-height:1.7">
            Join thousands of Muslim families who trust NikahConnect for a respectful, Islamic, and family-centered matrimonial experience.
        </p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-gold btn-lg">
                <i class="fas fa-user-plus"></i> Create Free Profile
            </a>
            <a href="<?= SITE_URL ?>/about.php" class="btn btn-lg" style="background:rgba(255,255,255,0.15);color:white;border:2px solid rgba(255,255,255,0.3)">
                <i class="fas fa-info-circle"></i> Learn More
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
