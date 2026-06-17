<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

// Ambil daftar kategori untuk dropdown
$kategori_list = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

$error   = '';
$success = '';

// Proses form tambah barang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang  = htmlspecialchars(trim($_POST['kode_barang'] ?? ''));
    $nama_barang  = htmlspecialchars(trim($_POST['nama_barang'] ?? ''));
    $stok         = (int)($_POST['stok'] ?? 0);
    $harga_barang = (float)($_POST['harga_barang'] ?? 0);
    $id_kategori  = (int)($_POST['id_kategori'] ?? 0);

    // Validasi server-side
    if (empty($kode_barang) || empty($nama_barang) || $id_kategori === 0) {
        $error = "Semua field wajib diisi.";
    } elseif ($harga_barang <= 0) {
        $error = "Harga barang harus lebih dari 0.";
    } elseif ($stok < 0) {
        $error = "Stok tidak boleh negatif.";
    } else {
        // Cek kode barang duplikat
        $cek = $conn->prepare("SELECT id_barang FROM barang WHERE kode_barang = ?");
        $cek->bind_param("s", $kode_barang);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Kode barang sudah digunakan. Gunakan kode yang berbeda.";
        } else {
            $stmt = $conn->prepare("INSERT INTO barang (kode_barang, nama_barang, stok, harga_barang, id_kategori) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssidi", $kode_barang, $nama_barang, $stok, $harga_barang, $id_kategori);
            if ($stmt->execute()) {
                header("Location: " . $base_url . "/pages/barang/index.php?success=" . urlencode("Barang berhasil ditambahkan."));
                exit;
            } else {
                $error = "Gagal menyimpan data. Silakan coba lagi.";
            }
            $stmt->close();
        }
        $cek->close();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Tambah Barang</h4>
    <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali
    </a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 640px;">
    <div class="card-body p-4">

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" id="server-error">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="formTambah" method="POST" action="" novalidate>

            <!-- Kode Barang -->
            <div class="mb-3">
                <label for="kode_barang" class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="kode_barang" name="kode_barang"
                       placeholder="Contoh: BRG-008"
                       value="<?= htmlspecialchars($_POST['kode_barang'] ?? '') ?>">
                <div class="error-msg text-danger mt-1" id="err-kode" style="display:none; font-size:0.85rem;"></div>
            </div>

            <!-- Nama Barang -->
            <div class="mb-3">
                <label for="nama_barang" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                       placeholder="Contoh: Monitor LG 24 inci"
                       value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>">
                <div class="error-msg text-danger mt-1" id="err-nama" style="display:none; font-size:0.85rem;"></div>
            </div>

            <!-- Stok -->
            <div class="mb-3">
                <label for="stok" class="form-label fw-semibold">Stok Awal <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="stok" name="stok"
                       min="0" placeholder="0"
                       value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>">
                <div class="error-msg text-danger mt-1" id="err-stok" style="display:none; font-size:0.85rem;"></div>
            </div>

            <!-- Harga -->
            <div class="mb-3">
                <label for="harga_barang" class="form-label fw-semibold">Harga Barang (Rp) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="harga_barang" name="harga_barang"
                       min="1" step="500" placeholder="0"
                       value="<?= htmlspecialchars($_POST['harga_barang'] ?? '') ?>">
                <div class="error-msg text-danger mt-1" id="err-harga" style="display:none; font-size:0.85rem;"></div>
            </div>

            <!-- Kategori -->
            <div class="mb-4">
                <label for="id_kategori" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                <select class="form-select" id="id_kategori" name="id_kategori">
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($kat = $kategori_list->fetch_assoc()): ?>
                        <option value="<?= $kat['id_kategori'] ?>"
                            <?= (($_POST['id_kategori'] ?? '') == $kat['id_kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="error-msg text-danger mt-1" id="err-kategori" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
    const form = document.getElementById('formTambah');

    // Helper: tampilkan error inline
    function showError(elId, msg) {
        const el = document.getElementById(elId);
        el.textContent = msg;
        el.style.display = 'block';
    }

    // Helper: sembunyikan error
    function hideError(elId) {
        const el = document.getElementById(elId);
        el.style.display = 'none';
    }

    // Hapus error saat user mulai mengetik (addEventListener - syarat JS #4)
    document.getElementById('kode_barang').addEventListener('input', () => hideError('err-kode'));
    document.getElementById('nama_barang').addEventListener('input', () => hideError('err-nama'));
    document.getElementById('stok').addEventListener('input', () => hideError('err-stok'));
    document.getElementById('harga_barang').addEventListener('input', () => hideError('err-harga'));
    document.getElementById('id_kategori').addEventListener('change', () => hideError('err-kategori'));

    // Validasi sebelum submit (syarat JS #1)
    form.addEventListener('submit', function (e) {
        let valid = true;

        const kode  = document.getElementById('kode_barang').value.trim();
        const nama  = document.getElementById('nama_barang').value.trim();
        const stok  = parseFloat(document.getElementById('stok').value);
        const harga = parseFloat(document.getElementById('harga_barang').value);
        const kat   = document.getElementById('id_kategori').value;

        if (!kode) {
            showError('err-kode', 'Kode barang wajib diisi.');
            valid = false;
        }

        if (!nama) {
            showError('err-nama', 'Nama barang wajib diisi.');
            valid = false;
        }

        if (isNaN(stok) || stok < 0) {
            showError('err-stok', 'Stok tidak boleh negatif.');
            valid = false;
        }

        if (isNaN(harga) || harga <= 0) {
            showError('err-harga', 'Harga harus lebih dari 0.');
            valid = false;
        }

        if (!kat) {
            showError('err-kategori', 'Pilih kategori terlebih dahulu.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
</script>

<?php include_once '../../includes/footer.php'; ?>