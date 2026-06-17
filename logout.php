<?php
$base_url = 'http://localhost/inventaris-barang';

// Mulai session agar bisa dihapus
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data session
$_SESSION = [];
session_destroy();

// Redirect ke halaman login
header("Location: " . $base_url . "/login.php");
exit;