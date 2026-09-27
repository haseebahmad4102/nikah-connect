<?php
// Upload helper functions

define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/nikahconnect/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

$ALLOWED_TYPES = ['image/jpeg','image/jpg','image/png','image/webp'];
$MAX_SIZE = 5 * 1024 * 1024; // 5MB

function uploadImage($file, $subdir = 'avatars') {
    global $ALLOWED_TYPES, $MAX_SIZE;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed. Please try again.'];
    }
    if ($file['size'] > $MAX_SIZE) {
        return ['error' => 'File too large. Maximum size is 5MB.'];
    }
    if (!in_array($file['type'], $ALLOWED_TYPES)) {
        return ['error' => 'Only JPG, PNG, and WEBP images are allowed.'];
    }

    // Verify it's actually an image
    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        return ['error' => 'Invalid image file.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $dest = UPLOAD_DIR . $subdir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['error' => 'Could not save file. Check folder permissions.'];
    }

    return ['filename' => $filename, 'path' => $subdir . '/' . $filename];
}

function deleteUpload($path) {
    $full = UPLOAD_DIR . $path;
    if (file_exists($full)) {
        unlink($full);
    }
}

function avatarUrl($filename) {
    if ($filename && file_exists(UPLOAD_DIR . 'avatars/' . $filename)) {
        return UPLOAD_URL . 'avatars/' . $filename;
    }
    return null;
}

function galleryUrl($filename) {
    return UPLOAD_URL . 'gallery/' . $filename;
}
