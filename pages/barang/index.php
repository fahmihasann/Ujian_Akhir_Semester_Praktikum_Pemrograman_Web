<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

$keyword     = htmlspecialchars(trim($_GET['q'] ?? ''));
$limit       = 5;
$page        = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page - 1) * $limit;

// Hitung total
if ($keyword !== '') {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM v_stok_barang WHERE nama_barang LIKE ? OR kode_barang LIKE ?");
    $like = '%' . $keyword . '%';
    $stmt_count->bind_param("ss", $like, $like);
} else {
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM v_stok_barang");
}
$stmt_count->execute();
$total_data  = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();

// Ambil data + kolom gambar dari tabel barang langsung (view tidak punya kolom gambar)
if ($keyword !== '') {
    $stmt = $conn->prepare("
        SELECT v.*, b.gambar
        FROM v_stok_barang v
        JOIN barang b ON v.id_barang = b.id_barang
        WHERE v.nama_barang LIKE ? OR v.kode_barang LIKE ?
        LIMIT ? OFFSET ?
    ");
    $like = '%' . $keyword . '%';
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT v.*, b.gambar
        FROM v_stok_barang v
        JOIN barang b ON v.id_barang = b.id_barang
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$barang_list = $stmt->get_result();
$stmt->close();

$success = htmlspecialchars($_GET['success'] ?? '');
$error   = htmlspecialchars($_GET['error'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Data Barang</h4>
    <a href="<?= $base_url ?>/pages/barang/tambah.php" class="btn btn-dark">+ Tambah Barang</a>
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

<!-- Search -->
<div class="mb-4">
    <form method="GET" action="">
        <div class="input-group">
            <input type="text" class="form-control" id="searchInput" name="q"
                   placeholder="Cari nama atau kode barang..."
                   value="<?= $keyword ?>">
            <button class="btn btn-dark" type="submit">Cari</button>
            <?php if ($keyword): ?>
                <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </div>
        <div id="search-info" class="text-muted mt-1" style="font-size: 0.85rem;"></div>
    </form>
</div>

<!-- Grid Card -->
<?php if ($barang_list->num_rows > 0): ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4 mb-4">
        <?php while ($row = $barang_list->fetch_assoc()): ?>
            <?php
                $img_src = $row['gambar']
                    ? $base_url . '/assets/img/barang/' . htmlspecialchars($row['gambar'])
                    : $base_url . '/assets/img/no-image.svg';
            ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?= $img_src ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_barang']) ?>" style="height: 200px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= $base_url ?>/assets/img/no-image.svg'">
                    <div class="card-body flex-grow-1 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                            <h5 class="card-title fw-bold mb-0"><?= htmlspecialchars($row['nama_barang']) ?></h5>
                            <span class="badge bg-dark text-nowrap"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                        </div>
                        <p class="card-text text-muted small mb-3">Kode: <code><?= htmlspecialchars($row['kode_barang']) ?></code></p>
                        
                        <div class="mt-auto" style="display: grid; grid-template-columns: auto auto 1fr; row-gap: 0.5rem; column-gap: 0.5rem; font-size: 0.9rem;">
                            <!-- Stok -->
                            <div class="fw-semibold text-nowrap d-flex align-items-center">Stok</div>
                            <div></div>
                            <div class="text-end d-flex justify-content-end align-items-center">
                                <?= htmlspecialchars($row['stok']) ?>
                                <?php
                                    $status_color = 'bg-success';
                                    if ($row['status_stok'] == 'Kritis') $status_color = 'bg-danger';
                                    elseif ($row['status_stok'] == 'Normal') $status_color = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $status_color ?> ms-1"><?= htmlspecialchars($row['status_stok']) ?></span>
                            </div>

                            <!-- Harga -->
                            <div class="fw-semibold text-nowrap d-flex align-items-center">Harga</div>
                            <div class="d-flex align-items-center">Rp</div>
                            <div class="text-end"><?= number_format($row['harga_barang'], 0, ',', '.') ?></div>

                            <!-- Total Nilai -->
                            <div class="fw-semibold text-nowrap d-flex align-items-center">Total Nilai</div>
                            <div class="d-flex align-items-center fw-bold text-dark">Rp</div>
                            <div class="text-dark fw-bold text-end"><?= number_format($row['nilai_inventaris'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex gap-2 p-3 pt-0">
                        <a href="<?= $base_url ?>/pages/barang/edit.php?id=<?= $row['id_barang'] ?>" class="btn btn-outline-dark btn-sm flex-grow-1">Edit</a>
                        <a href="<?= $base_url ?>/pages/barang/hapus.php?id=<?= $row['id_barang'] ?>" class="btn btn-secondary btn-sm flex-grow-1 btn-hapus" data-nama="<?= htmlspecialchars($row['nama_barang']) ?>">Hapus</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-box-seam display-4 d-block mb-3 text-secondary"></i>
            <h5><?= $keyword ? "Tidak ada barang dengan kata kunci \"$keyword\"." : "Belum ada data barang." ?></h5>
        </div>
    </div>
<?php endif; ?>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
        Menampilkan <?= min($offset + 1, $total_data) ?>-<?= min($offset + $limit, $total_data) ?>
        dari <?= $total_data ?> data
    </small>
    <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $keyword ? '&q=' . urlencode($keyword) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.btn-hapus').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const nama = this.getAttribute('data-nama');
            if (!confirm('Yakin ingin menghapus barang "' + nama + '"?\nData yang sudah dihapus tidak bisa dikembalikan.')) {
                e.preventDefault();
            }
        });
    });

    const searchInput = document.getElementById('searchInput');
    const searchInfo  = document.getElementById('search-info');
    searchInput.addEventListener('input', function () {
        const len = this.value.length;
        searchInfo.textContent = len > 0 ? len + ' karakter diketik' : '';
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
