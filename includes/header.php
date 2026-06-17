<?php
// Pastikan session sudah berjalan untuk mengecek status login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = 'http://localhost/inventaris-barang';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Barang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bolder text-dark d-flex align-items-center gap-2" href="<?= $base_url ?>/index.php" style="font-size: 1.5rem; letter-spacing: -0.5px;">
                <i class="bi bi-box-seam"></i> Inventory
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-4 fw-semibold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/pages/barang/index.php">Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/pages/kategori/index.php">Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/pages/barang_masuk/index.php">Barang Masuk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/pages/barang_keluar/index.php">Barang Keluar</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav align-items-center gap-3">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= htmlspecialchars($_SESSION['nama_lengkap']); ?>">
                                <i class="bi bi-person fs-4 text-dark"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3" aria-labelledby="navbarDropdown">
                                <li>
                                    <span class="dropdown-item-text fw-bold"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= $base_url ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-dark btn-sm fw-bold px-4 rounded-pill mt-1" href="<?= $base_url ?>/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">