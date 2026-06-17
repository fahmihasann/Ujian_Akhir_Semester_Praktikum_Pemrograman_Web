<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

// Query langsung ke tabel (hindari view bermasalah)
$query = "
    SELECT 
        bm.tanggal_masuk AS tanggal, 
        b.nama_barang, 
        bm.jumlah, 
        bm.supplier AS keterangan, 
        u.nama_lengkap 
    FROM barang_masuk bm
    JOIN barang b ON bm.id_barang = b.id_barang
    JOIN user u ON bm.id_user = u.id_user
    ORDER BY bm.tanggal_masuk DESC
";
$result = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Riwayat Barang Masuk</h4>
    <a href="<?= $base_url ?>/pages/barang_masuk/tambah.php" class="btn btn-dark">
        + Catat Barang Masuk
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Jumlah</th>
                        <th>Supplier</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-3"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td class="text-center">
                                    <span class="text-dark fw-bold"><?= htmlspecialchars($row['jumlah']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada riwayat transaksi masuk.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
