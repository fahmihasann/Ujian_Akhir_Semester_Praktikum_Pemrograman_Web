<?php
require_once 'includes/config.php';

// Hitung ringkasan dashboard
$query_barang = $conn->query("SELECT COUNT(*) as total FROM barang");
$total_barang = $query_barang ? $query_barang->fetch_assoc()['total'] : 0;

$query_kategori = $conn->query("SELECT COUNT(*) as total FROM kategori");
$total_kategori = $query_kategori ? $query_kategori->fetch_assoc()['total'] : 0;

$query_masuk = $conn->query("SELECT COUNT(*) as total FROM barang_masuk");
$total_masuk = $query_masuk ? $query_masuk->fetch_assoc()['total'] : 0;

$query_keluar = $conn->query("SELECT COUNT(*) as total FROM barang_keluar");
$total_keluar = $query_keluar ? $query_keluar->fetch_assoc()['total'] : 0;

include_once 'includes/header.php';
?>

<div class="p-5 mb-4 bg-white rounded-3 border">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold text-dark">Sistem Pendataan Inventaris Barang</h1>
        <p class="col-md-8 fs-5 text-muted">Selamat datang di aplikasi manajemen inventaris barang kantor. Kelola data
            master barang, kategori, serta pantau pencatatan log barang masuk dan keluar secara terintegrasi.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card h-100 bg-transparent border-thick-dark rounded-0">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-dark mb-1 fw-semibold"
                        style="font-size: 0.85rem; opacity: 0.8;">Total Barang</h6>
                    <h2 class="display-5 fw-bold text-dark mb-0"><?= $total_barang; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 bg-transparent border-thick-dark rounded-0">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-dark mb-1 fw-semibold"
                        style="font-size: 0.85rem; opacity: 0.8;">Total Kategori</h6>
                    <h2 class="display-5 fw-bold text-dark mb-0"><?= $total_kategori; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 bg-transparent border-thick-dark rounded-0">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-dark mb-1 fw-semibold"
                        style="font-size: 0.85rem; opacity: 0.8;">Barang Masuk</h6>
                    <h2 class="display-5 fw-bold text-dark mb-0"><?= $total_masuk; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 bg-transparent border-thick-dark rounded-0">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h6 class="card-title text-uppercase text-dark mb-1 fw-semibold"
                        style="font-size: 0.85rem; opacity: 0.8;">Barang Keluar</h6>
                    <h2 class="display-5 fw-bold text-dark mb-0"><?= $total_keluar; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>