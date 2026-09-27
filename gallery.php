<?php
$pageTitle = 'My Gallery';
require_once 'includes/db.php';
require_once 'includes/upload.php';
requireLogin();

$user = currentUser();
$userId = $user['id'];
$error = '';
$success = '';
$MAX_PHOTOS = 6;

// Delete a photo
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $photo = db()->fetchOne("SELECT * FROM gallery WHERE id=? AND user_id=?", [(int)$_GET['delete'], $userId]);
    if ($photo) {
        deleteUpload('gallery/' . $photo['filename']);
        db()->execute("DELETE FROM gallery WHERE id=?", [$photo['id']]);
        $success = 'Photo deleted successfully.';
    }
}

// Upload new photo(s)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['gallery_photos']['name'][0])) {
    $currentCount = db()->fetchOne("SELECT COUNT(*) as c FROM gallery WHERE user_id=?", [$userId])['c'] ?? 0;
    $files = $_FILES['gallery_photos'];
    $uploaded = 0;

    foreach ($files['name'] as $i => $name) {
        if (empty($name)) continue;
        if ($currentCount + $uploaded >= $MAX_PHOTOS) {
            $error = "Maximum $MAX_PHOTOS photos allowed. Some photos were not uploaded.";
            break;
        }
        $file = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
        $result = uploadImage($file, 'gallery');
        if (isset($result['error'])) {
            $error = $result['error'];
            break;
        }
        $caption = trim($_POST['captions'][$i] ?? '');
        db()->insert(
            "INSERT INTO gallery (user_id, filename, caption) VALUES (?,?,?)",
            [$userId, $result['filename'], $caption]
        );
        $uploaded++;
    }
    if (!$error && $uploaded > 0) {
        $success = $uploaded . ' photo(s) uploaded successfully!';
    }
}

$photos = db()->fetchAll("SELECT * FROM gallery WHERE user_id=? ORDER BY created_at DESC", [$userId]);
$count  = count($photos);
$canUpload = $count < $MAX_PHOTOS;

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="<?=SITE_URL?>/dashboard.php">Dashboard</a> <span>›</span> My Gallery</div>
        <h1><i class="fas fa-images"></i> My Photo Gallery</h1>
        <p><?= $count ?>/<?= $MAX_PHOTOS ?> photos uploaded — visible to profile visitors</p>
    </div>
</div>

<div class="container" style="padding:28px 20px;max-width:900px">

    <?php if($error): ?>
    <div class="alert alert-error mb-3"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
    <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Upload Section -->
    <?php if($canUpload): ?>
    <div class="card mb-3">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-cloud-upload-alt"></i> Upload Photos</div>
            <span style="font-size:12px;color:var(--text-light)"><?= $MAX_PHOTOS - $count ?> slot(s) remaining</span>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="galleryForm">
                <!-- Drop zone -->
                <div id="dropZone" style="border:2px dashed var(--border);border-radius:12px;padding:36px;text-align:center;cursor:pointer;transition:all .2s;background:var(--cream)" onclick="document.getElementById('galleryInput').click()" ondragover="event.preventDefault();this.style.borderColor='var(--primary)';this.style.background='rgba(26,107,60,0.04)'" ondragleave="this.style.borderColor='var(--border)';this.style.background='var(--cream)'" ondrop="handleDrop(event)">
                    <div style="font-size:42px;margin-bottom:12px">🖼️</div>
                    <div style="font-size:15px;font-weight:600;color:var(--text-dark);margin-bottom:6px">Drop photos here or click to browse</div>
                    <div style="font-size:13px;color:var(--text-light)">JPG, PNG, WEBP · Max 5MB each · Up to <?= $MAX_PHOTOS - $count ?> more photo(s)</div>
                    <input type="file" id="galleryInput" name="gallery_photos[]" accept="image/*" multiple style="display:none" onchange="previewGallery(this)">
                </div>

                <!-- Preview grid -->
                <div id="previewGrid" style="display:none;margin-top:18px">
                    <h4 style="font-size:14px;font-weight:600;margin-bottom:12px">Selected photos:</h4>
                    <div id="previewItems" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px"></div>
                    <button type="submit" class="btn btn-primary mt-3" id="uploadBtn">
                        <i class="fas fa-upload"></i> Upload to Gallery
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle"></i>
        You've reached the maximum of <?= $MAX_PHOTOS ?> photos. Delete a photo to upload a new one.
    </div>
    <?php endif; ?>

    <!-- Gallery Grid -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-th"></i> Your Photos (<?= $count ?>)</div>
            <a href="view-profile.php?id=<?= $userId ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-eye"></i> Preview Profile
            </a>
        </div>
        <div class="card-body">
            <?php if(empty($photos)): ?>
            <div style="text-align:center;padding:40px;color:var(--text-light)">
                <div style="font-size:48px;margin-bottom:12px">📷</div>
                <h3>No photos yet</h3>
                <p style="font-size:14px;margin-top:6px">Upload photos to show visitors what you look like</p>
            </div>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
                <?php foreach($photos as $photo): ?>
                <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--border);background:var(--cream)" class="gallery-item">
                    <div style="aspect-ratio:1;overflow:hidden;background:#f0f0f0">
                        <img src="<?= galleryUrl($photo['filename']) ?>" alt="Gallery photo"
                             style="width:100%;height:100%;object-fit:cover;display:block;cursor:pointer"
                             onclick="openLightbox('<?= galleryUrl($photo['filename']) ?>')">
                    </div>
                    <div style="padding:10px 12px">
                        <?php if($photo['caption']): ?>
                        <div style="font-size:12px;color:var(--text-medium);margin-bottom:8px"><?= htmlspecialchars($photo['caption']) ?></div>
                        <?php endif; ?>
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <span style="font-size:11px;color:var(--text-light)"><?= date('d M Y', strtotime($photo['created_at'])) ?></span>
                            <a href="?delete=<?= $photo['id'] ?>"
                               onclick="return confirm('Delete this photo? This cannot be undone.')"
                               style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#e74c3c;text-decoration:none;font-weight:600;padding:4px 8px;border-radius:5px;border:1px solid #fecaca;background:#fef2f2"
                               title="Delete photo">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:9999;align-items:center;justify-content:center" onclick="closeLightbox()">
    <img id="lightboxImg" src="" style="max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 10px 60px rgba(0,0,0,.5)">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:24px;background:rgba(255,255,255,0.15);border:none;color:white;font-size:28px;cursor:pointer;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center">✕</button>
</div>

<script>
function previewGallery(input) {
    const grid = document.getElementById('previewGrid');
    const items = document.getElementById('previewItems');
    items.innerHTML = '';
    const max = <?= $MAX_PHOTOS - $count ?>;

    if (input.files.length === 0) { grid.style.display = 'none'; return; }

    const count = Math.min(input.files.length, max);
    for (let i = 0; i < count; i++) {
        const file = input.files[i];
        const reader = new FileReader();
        reader.onload = (function(idx) {
            return function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'border-radius:8px;overflow:hidden;border:1px solid var(--border);background:var(--cream)';
                div.innerHTML = `
                    <div style="aspect-ratio:1;overflow:hidden;background:#eee">
                        <img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">
                    </div>
                    <div style="padding:6px 8px">
                        <input type="text" name="captions[]" placeholder="Caption (optional)" style="width:100%;font-size:11px;padding:5px 8px;border:1px solid var(--border);border-radius:5px;font-family:inherit">
                    </div>`;
                items.appendChild(div);
            };
        })(i);
        reader.readAsDataURL(file);
    }
    grid.style.display = 'block';

    if (input.files.length > max) {
        alert(`Only ${max} more photo(s) can be uploaded. Only the first ${max} will be used.`);
    }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = 'var(--border)';
    document.getElementById('dropZone').style.background = 'var(--cream)';
    const input = document.getElementById('galleryInput');
    input.files = e.dataTransfer.files;
    previewGallery(input);
}

function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) { if(e.key==='Escape') closeLightbox(); });
</script>

<?php require_once 'includes/footer.php'; ?>
