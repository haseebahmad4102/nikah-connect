<?php
$pageTitle = 'My Profile';
require_once 'includes/db.php';
requireLogin();
$user = currentUser();
header('Location: '.SITE_URL.'/view-profile.php?id='.$user['id']);
exit;
