<?php
$base_url = 'http://localhost/inventaris-barang';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_destroy();
header("Location: " . $base_url . "/login.php");
exit;