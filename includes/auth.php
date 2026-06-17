<?php
// Pastikan session sudah berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, redirect ke halaman login
if (!isset($_SESSION['id_user'])) {
    header("Location: " . $base_url . "/login.php");
    exit;
}