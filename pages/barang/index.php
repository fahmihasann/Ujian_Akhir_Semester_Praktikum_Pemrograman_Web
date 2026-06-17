<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

$keyword = htmlspecialchars(trim($_GET['q'] ?? ''));

$limit       = 5;
$page        = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page - 1) * $limit;

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

if ($keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM v_stok_barang WHERE nama_barang LIKE ? OR kode_barang LIKE ? LIMIT ? OFFSET ?");
    $like = '%' . $keyword . '%';
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM v_stok_barang LIMIT ? OFFSET ?");
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
    <a href="<?= $base_url ?>/pages/barang/tambah.php" class="btn btn-primary">
        + Tambah Barang
    </a>
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" id="searchForm">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
                    id="searchInput"
                    name="q"
                    placeholder="Cari nama atau kode barang..."
                    value="<?= $keyword ?>"
                >
                <button class="btn btn-primary" type="submit">Cari</button>
                <?php if ($keyword): ?>
                    <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
            <div id="search-info" class="text-muted mt-1" style="font-size: 0.85rem;"></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Nilai Inventaris</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($barang_list->num_rows > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = $barang_list->fetch_assoc()): ?>

                            <tr>
                                <td class="ps-3"><?= $no++ ?></td>
                                <td><code><?= htmlspecialchars($row['kode_barang']) ?></code></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                <td><?= htmlspecialchars($row['stok']) ?></td>
                                <td>Rp <?= number_format($row['harga_barang'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['nilai_inventaris'], 0, ',', '.') ?></td>
                                <td>
                                    <?= htmlspecialchars($row['status_stok']) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= $base_url ?>/pages/barang/edit.php?id=<?= $row['id_barang'] ?>"
                                       class="btn btn-sm btn-warning me-1">Edit</a>
                                    <a href="<?= $base_url ?>/pages/barang/hapus.php?id=<?= $row['id_barang'] ?>"
                                       class="btn btn-sm btn-danger btn-hapus"
                                       data-nama="<?= htmlspecialchars($row['nama_barang']) ?>">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <?= $keyword ? "Tidak ada barang dengan kata kunci \"$keyword\"." : "Belum ada data barang." ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
        Menampilkan <?= min($offset + 1, $total_data) ?>-<?= min($offset + $limit, $total_data) ?>
        dari <?= $total_data ?> data
        <?= $keyword ? "untuk \"$keyword\"" : "" ?>
    </small>

    <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $i ?><?= $keyword ? '&q=' . urlencode($keyword) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    // Live character counter pada search input (addEventListener selain onclick - syarat JS #4)
    const searchInput = document.getElementById('searchInput');
    const searchInfo  = document.getElementById('search-info');

    if (searchInput && searchInfo) {
        searchInput.addEventListener('input', function () {
            const len = this.value.length;
            searchInfo.textContent = len > 0 ? len + ' karakter diketik' : '';
        });
    }
</script>

<?php include_once '../../includes/footer.php'; ?>