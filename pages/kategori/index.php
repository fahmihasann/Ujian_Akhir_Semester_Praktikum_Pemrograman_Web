<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

$success = htmlspecialchars($_GET['success'] ?? '');
$error = htmlspecialchars($_GET['error'] ?? '');

$query = "
    SELECT k.id_kategori, k.nama_kategori, k.deskripsi,
           COUNT(b.id_barang) AS jumlah_barang
    FROM kategori k
    LEFT JOIN barang b ON k.id_kategori = b.id_kategori
    GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi
    ORDER BY k.nama_kategori ASC
";
$kategori_list = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Data Kategori</h4>
    <a href="<?= $base_url ?>/pages/kategori/tambah.php" class="btn btn-dark">+ Tambah Kategori</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $error ?>
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
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah Barang</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($kategori_list->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = $kategori_list->fetch_assoc()): ?>
                            <?php
                            $extra = '';
                            if ($row['jumlah_barang'] > 0) {
                                $extra = 'Peringatan: Kategori ini masih memiliki ' . $row['jumlah_barang'] . ' barang. Hapus atau pindahkan barang terlebih dahulu.';
                            }
                            ?>
                            <tr>
                                <td class="ps-3"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></td>
                                <td class="text-center">
                                    <?= $row['jumlah_barang'] ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= $base_url ?>/pages/kategori/edit.php?id=<?= $row['id_kategori'] ?>"
                                        class="btn btn-sm btn-outline-dark me-1">Edit</a>
                                    <a href="<?= $base_url ?>/pages/kategori/hapus.php?id=<?= $row['id_kategori'] ?>"
                                        class="btn btn-sm btn-secondary btn-hapus"
                                        data-nama="<?= htmlspecialchars($row['nama_kategori']) ?>"
                                        data-extra="<?= htmlspecialchars($extra) ?>">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada data kategori.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
