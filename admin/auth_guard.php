<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Force the browser to clear cache for secure admin views
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 2. The standard login security check block you already have
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>