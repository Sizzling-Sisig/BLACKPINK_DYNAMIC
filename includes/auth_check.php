<?php
// ── Admin Authentication Guard ──────────────────────────────
// Include this at the TOP of every admin page (before any HTML output).
// Redirects to login page if not authenticated.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching of admin pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../account/login.php");
    exit();
}
?>
