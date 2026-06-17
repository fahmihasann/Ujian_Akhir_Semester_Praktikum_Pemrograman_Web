<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header("Location: " . $base_url . "/pages/barang/index.php?error=" . urlencode("ID barang tidak valid."));
    exit;
}

// Cek barang
$cek = $conn->prepare("SELECT nama_barang FROM barang WHERE id_barang = ?");
$cek->bind_param("i", $id);
$cek->execute();
$result = $cek->get_result();
$barang = $result->fetch_assoc();
$cek->close();

if (!$barang) {
    header("Location: " . $base_url . "/pages/barang/index.php?error=" . urlencode("Barang tidak ditemukan."));
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM barang WHERE id_barang = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $base_url . "/pages/barang/index.php?success=" . urlencode("Barang \"" . $barang['nama_barang'] . "\" berhasil dihapus."));
    exit;
} catch (mysqli_sql_exception $e) {
    header("Location: " . $base_url . "/pages/barang/index.php?error=" . urlencode("Barang tidak bisa dihapus karena memiliki riwayat transaksi masuk/keluar."));
    exit;
}
