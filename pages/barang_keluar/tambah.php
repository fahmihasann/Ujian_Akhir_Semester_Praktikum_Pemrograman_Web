<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang = (int)($_POST['id_barang'] ?? 0);
    $tanggal   = $_POST['tanggal_keluar'] ?? '';
    $jumlah    = (int)($_POST['jumlah'] ?? 0);
    $tujuan    = htmlspecialchars(trim($_POST['tujuan'] ?? ''));
    $id_user   = $_SESSION['id_user'] ?? 0;

    if ($id_barang === 0 || empty($tanggal) || $jumlah <= 0 || empty($tujuan)) {
        $error = "Semua field wajib diisi dengan benar.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO barang_keluar (tanggal_keluar, jumlah, tujuan_distribusi, id_barang, id_user) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisii", $tanggal, $jumlah, $tujuan, $id_barang, $id_user);
            $stmt->execute();
            $stmt->close();

            header("Location: " . $base_url . "/pages/barang_keluar/index.php?success=" . urlencode("Transaksi barang keluar berhasil dicatat."));
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Gagal: " . $e->getMessage();
        }
    }
}

$barang_result = $conn->query("SELECT id_barang, nama_barang, stok FROM barang WHERE stok > 0 ORDER BY nama_barang ASC");

include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Catat Barang Keluar</h4>
    <a href="<?= $base_url ?>/pages/barang_keluar/index.php" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali
    </a>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card-body p-4">

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="formBarangKeluar" method="POST" action="" novalidate>

            <div class="mb-3">
                <label for="id_barang" class="form-label fw-semibold">Pilih Barang <span class="text-danger">*</span></label>
                <select class="form-select" id="id_barang" name="id_barang" required>
                    <option value="" data-stok="0">-- Pilih Barang --</option>
                    <?php while ($b = $barang_result->fetch_assoc()): ?>
                        <option value="<?= $b['id_barang'] ?>" data-stok="<?= $b['stok'] ?>">
                            <?= htmlspecialchars($b['nama_barang']) ?> (Stok: <?= $b['stok'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <div id="infoStok" class="text-muted mt-1" style="font-size: 0.85rem;">Pilih barang untuk melihat stok tersedia.</div>
            </div>

            <div class="mb-3">
                <label for="tanggal_keluar" class="form-label fw-semibold">Tanggal Keluar <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="tanggal_keluar" name="tanggal_keluar"
                       value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-3">
                <label for="jumlah" class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" placeholder="0" required>
                <div id="err-jumlah" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-4">
                <label for="tujuan" class="form-label fw-semibold">Tujuan / Keterangan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tujuan" name="tujuan" placeholder="Tujuan penggunaan barang" required>
                <div id="err-tujuan" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark px-4">Simpan Transaksi</button>
                <a href="<?= $base_url ?>/pages/barang_keluar/index.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
    const formBarangKeluar = document.getElementById('formBarangKeluar');
    const selectBarang     = document.getElementById('id_barang');
    const infoStok         = document.getElementById('infoStok');
    const inputJumlah      = document.getElementById('jumlah');
    const inputTujuan      = document.getElementById('tujuan');

    // Tampilkan stok & batasi max input
    selectBarang.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const maxStok  = selected.getAttribute('data-stok');

        if (this.value !== "") {
            infoStok.innerHTML = `Stok tersedia saat ini: <strong>${maxStok}</strong>`;
            infoStok.className = "text-dark mt-1";
            infoStok.style.fontSize = "0.85rem";
            inputJumlah.max = maxStok;

            if (parseInt(inputJumlah.value) > parseInt(maxStok)) {
                inputJumlah.value = maxStok;
            }
        } else {
            infoStok.innerHTML = "Pilih barang untuk melihat stok tersedia.";
            infoStok.className = "text-muted mt-1";
            infoStok.style.fontSize = "0.85rem";
            inputJumlah.removeAttribute('max');
        }
    });

    inputJumlah.addEventListener('input', function() {
        document.getElementById('err-jumlah').style.display = 'none';
        this.classList.remove('is-invalid');
    });
    inputTujuan.addEventListener('input', function() {
        document.getElementById('err-tujuan').style.display = 'none';
        this.classList.remove('is-invalid');
    });

    // Validasi sebelum submit
    formBarangKeluar.addEventListener('submit', function(e) {
        let valid = true;
        const jumlah  = parseInt(inputJumlah.value);
        const tujuan  = inputTujuan.value.trim();
        const maxStok = parseInt(inputJumlah.max);

        if (isNaN(jumlah) || jumlah < 1) {
            document.getElementById('err-jumlah').textContent = 'Jumlah minimal 1.';
            document.getElementById('err-jumlah').style.display = 'block';
            inputJumlah.classList.add('is-invalid');
            valid = false;
        } else if (!isNaN(maxStok) && jumlah > maxStok) {
            document.getElementById('err-jumlah').textContent = `Jumlah melebihi stok tersedia (${maxStok}).`;
            document.getElementById('err-jumlah').style.display = 'block';
            inputJumlah.classList.add('is-invalid');
            valid = false;
        }

        if (!tujuan) {
            document.getElementById('err-tujuan').textContent = 'Tujuan wajib diisi.';
            document.getElementById('err-tujuan').style.display = 'block';
            inputTujuan.classList.add('is-invalid');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
