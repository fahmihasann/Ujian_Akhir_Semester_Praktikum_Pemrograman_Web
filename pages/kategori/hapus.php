<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header("Location: " . $base_url . "/pages/kategori/index.php?error=" . urlencode("ID tidak valid."));
    exit;
}

// Cek kategori ada
$cek = $conn->prepare("SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
$cek->bind_param("i", $id);
$cek->execute();
$kategori = $cek->get_result()->fetch_assoc();
$cek->close();

if (!$kategori) {
    header("Location: " . $base_url . "/pages/kategori/index.php?error=" . urlencode("Kategori tidak ditemukan."));
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $base_url . "/pages/kategori/index.php?success=" . urlencode("Kategori \"" . $kategori['nama_kategori'] . "\" berhasil dihapus."));
    exit;
} catch (mysqli_sql_exception $e) {
    // FK constraint: masih ada barang di kategori ini
    header("Location: " . $base_url . "/pages/kategori/index.php?error=" . urlencode("Kategori tidak bisa dihapus karena masih memiliki barang. Hapus atau pindahkan barang terlebih dahulu."));
    exit;
}
