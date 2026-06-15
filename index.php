<?php
// Memuat konfigurasi database
require_once 'includes/config.php';

// Mengambil total data dari masing-masing tabel menggunakan COUNT(*)
// 1. Total Barang
$query_barang = $conn->query("SELECT COUNT(*) as total FROM barang");
$total_barang = $query_barang ? $query_barang->fetch_assoc()['total'] : 0;

// 2. Total Kategori
$query_kategori = $conn->query("SELECT COUNT(*) as total FROM kategori");
$total_kategori = $query_kategori ? $query_kategori->fetch_assoc()['total'] : 0;

// 3. Total Transaksi Masuk
$query_masuk = $conn->query("SELECT COUNT(*) as total FROM barang_masuk");
$total_masuk = $query_masuk ? $query_masuk->fetch_assoc()['total'] : 0;

// 4. Total Transaksi Keluar
$query_keluar = $conn->query("SELECT COUNT(*) as total FROM barang_keluar");
$total_keluar = $query_keluar ? $query_keluar->fetch_assoc()['total'] : 0;

// Memuat header halaman
include_once 'includes/header.php';
?>

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold text-primary">Sistem Pendataan Inventaris Barang</h1>
        <p class="col-md-8 fs-5 text-muted">Selamat datang di aplikasi manajemen inventaris barang kantor. Kelola data master barang, kategori, serta pantau pencatatan log barang masuk dan keluar secara terintegrasi.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-primary text-white">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-white-50 mb-1 fw-semibold" style="font-size: 0.85rem;">Total Barang</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= $total_barang; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-success text-white">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-white-50 mb-1 fw-semibold" style="font-size: 0.85rem;">Total Kategori</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= $total_kategori; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-info text-white">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-white-50 mb-1 fw-semibold" style="font-size: 0.85rem;">Barang Masuk</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= $total_masuk; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-warning text-white">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-white-50 mb-1 fw-semibold" style="font-size: 0.85rem;">Barang Keluar</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= $total_keluar; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Memuat footer halaman
include_once 'includes/footer.php';
?>