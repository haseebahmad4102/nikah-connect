<?php
$pageTitle = 'Profile Picture';
require_once 'includes/db.php';
require_once 'includes/upload.php';
requireLogin();

$user    = currentUser();
$profile = db()->fetchOne("SELECT photo FROM profiles WHERE user_id=?", [$user['id']]);
$current = avatarUrl($profile['photo'] ?? null);

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        // Remove current photo
        if ($profile && $profile['photo']) {
            deleteUpload('avatars/' . $profile['photo']);
            db()->execute("UPDATE profiles SET photo=NULL WHERE user_id=?", [$user['id']]);
        }
        header('Location: upload-photo.php?removed=1');
        exit;
    }

    if (!empty($_FILES['photo']['name'])) {
        $result = uploadImage($_FILES['photo'], 'avatars');
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            // Delete old photo file
            if ($profile && $profile['photo']) {
                deleteUpload('avatars/' . $profile['photo']);
            }
            // Save to DB — create profile row if missing
            $exists = db()->fetchOne("SELECT id FROM profiles WHERE user_id=?", [$user['id']]);
            if ($exists) {
                db()->execute("UPDATE profiles SET photo=? WHERE user_id=?", [$result['filename'], $user['id']]);
            } else {
                db()->insert("INSERT INTO profiles (user_id, photo) VALUES (?,?)", [$user['id'], $result['filename']]);
            }
            header('Location: upload-photo.php?saved=1');
            exit;
        }
    }
}

require_once 'includes/header.php';
$current = avatarUrl(db()->fetchOne("SELECT photo FROM profiles WHERE user_id=?", [$user['id']])['photo'] ?? null);
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?=SITE_URL?>/dashboard.php">Dashboard</a> <span>›</span> Profile Picture
        </div>
        <h1><i class="fas fa-camera"></i> Profile Picture</h1>
        <p>Your photo is shown on your profile and suggestion cards</p>
    </div>
</div>

<div class="container" style="padding:36px 20px;max-width:560px">

    <?php if(isset($_GET['saved'])): ?>
    <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> Profile picture updated successfully!</div>
    <?php endif; ?>
    <?php if(isset($_GET['removed'])): ?>
    <div class="alert alert-info mb-3"><i class="fas fa-info-circle"></i> Profile picture removed.</div>
    <?php endif; ?>
    <?php if($error): ?>
    <div class="alert alert-error mb-3"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body" style="padding:32px">

            <!-- Current photo display -->
            <div style="text-align:center;margin-bottom:28px">
                <div id="avatarPreview" style="width:130px;height:130px;border-radius:50%;margin:0 auto 14px;border:4px solid var(--border);overflow:hidden;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-size:52px;position:relative;box-shadow:0 6px 24px rgba(26,107,60,0.2)">
                    <?php if($current): ?>
                    <img id="avatarImg" src="<?= htmlspecialchars($current) ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                    <span id="avatarEmoji" style="color:var(--gold)"><?= $user['gender']==='male' ? '👳' : '🧕' ?></span>
                    <?php endif; ?>
                </div>
                <div style="font-size:13px;color:var(--text-light)">
                    <?= $current ? 'Current profile picture' : 'No profile picture set' ?>
                </div>
            </div>

            <!-- Upload form -->
            <form method="POST" enctype="multipart/form-data" id="photoForm">

                <!-- Drop zone -->
                <div id="dropZone"
                     onclick="document.getElementById('photoInput').click()"
                     ondragover="event.preventDefault();this.classList.add('drag-over')"
                     ondragleave="this.classList.remove('drag-over')"
                     ondrop="handleDrop(event)"
                     style="border:2px dashed var(--border);border-radius:12px;padding:28px 20px;text-align:center;cursor:pointer;transition:all .2s;background:var(--cream);margin-bottom:18px">
                    <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--primary);margin-bottom:10px;display:block"></i>
                    <div style="font-size:14px;font-weight:600;color:var(--text-dark);margin-bottom:4px">Click to choose photo</div>
                    <div style="font-size:12px;color:var(--text-light)">or drag & drop here</div>
                    <div style="font-size:11px;color:var(--text-light);margin-top:6px">JPG, PNG, WEBP · Max 5MB</div>
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                </div>

                <button type="submit" id="uploadBtn" class="btn btn-primary btn-block btn-lg" disabled style="opacity:.5">
                    <i class="fas fa-upload"></i> Upload Profile Picture
                </button>
            </form>

            <!-- Remove button -->
            <?php if($current): ?>
            <form method="POST" style="margin-top:12px" onsubmit="return confirm('Remove your profile picture?')">
                <button type="submit" name="remove" class="btn btn-block" style="background:#fef2f2;color:#e74c3c;border:1.5px solid #fecaca;font-size:14px">
                    <i class="fas fa-trash"></i> Remove Current Photo
                </button>
            </form>
            <?php endif; ?>

            <div style="margin-top:20px;background:rgba(201,168,76,0.07);border:1px solid rgba(201,168,76,0.2);border-radius:8px;padding:12px 14px;font-size:12px;color:var(--text-medium)">
                <i class="fas fa-info-circle" style="color:var(--gold-dark)"></i>
                <strong>Islamic Note:</strong> Please upload a modest photo that represents Islamic values of dignity and respect.
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
        <a href="dashboard.php" class="btn btn-outline btn-sm"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="gallery.php" class="btn btn-outline btn-sm"><i class="fas fa-images"></i> Manage Gallery</a>
        <a href="view-profile.php?id=<?= $user['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View My Profile</a>
    </div>
</div>

<style>
#dropZone.drag-over {
    border-color: var(--primary) !important;
    background: rgba(26,107,60,0.05) !important;
}
</style>

<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('avatarPreview');
        preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover">';
        const btn = document.getElementById('uploadBtn');
        btn.disabled = false;
        btn.style.opacity = '1';
    };
    reader.readAsDataURL(input.files[0]);
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').classList.remove('drag-over');
    const dt = e.dataTransfer;
    if (dt.files && dt.files[0]) {
        const input = document.getElementById('photoInput');
        // Assign the dropped file
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(dt.files[0]);
        input.files = dataTransfer.files;
        previewPhoto(input);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
