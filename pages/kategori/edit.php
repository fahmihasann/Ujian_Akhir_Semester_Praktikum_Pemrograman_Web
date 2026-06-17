<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    header("Location: " . $base_url . "/pages/kategori/index.php?error=" . urlencode("ID kategori tidak valid."));
    exit;
}

// Ambil data kategori
$stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$kategori = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kategori) {
    header("Location: " . $base_url . "/pages/kategori/index.php?error=" . urlencode("Kategori tidak ditemukan."));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = htmlspecialchars(trim($_POST['nama_kategori'] ?? ''));
    $deskripsi     = htmlspecialchars(trim($_POST['deskripsi'] ?? ''));

    if (empty($nama_kategori)) {
        $error = "Nama kategori wajib diisi.";
    } else {
        // Cek duplikat (kecuali milik kategori ini)
        $cek = $conn->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ? AND id_kategori != ?");
        $cek->bind_param("si", $nama_kategori, $id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Nama kategori sudah digunakan kategori lain.";
        } else {
            $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
            $stmt->bind_param("ssi", $nama_kategori, $deskripsi, $id);
            if ($stmt->execute()) {
                header("Location: " . $base_url . "/pages/kategori/index.php?success=" . urlencode("Kategori berhasil diperbarui."));
                exit;
            } else {
                $error = "Gagal memperbarui data.";
            }
            $stmt->close();
        }
        $cek->close();
    }

    $kategori['nama_kategori'] = $_POST['nama_kategori'] ?? $kategori['nama_kategori'];
    $kategori['deskripsi']     = $_POST['deskripsi'] ?? $kategori['deskripsi'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Kategori</h4>
    <a href="<?= $base_url ?>/pages/kategori/index.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body p-4">

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="formEditKategori" method="POST" action="" novalidate>

            <div class="mb-3">
                <label for="nama_kategori" class="form-label fw-semibold">
                    Nama Kategori <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori"
                       value="<?= htmlspecialchars($kategori['nama_kategori']) ?>">
                <div id="err-nama" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-4">
                <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi"
                          rows="3"><?= htmlspecialchars($kategori['deskripsi'] ?? '') ?></textarea>
                <div id="char-count" class="text-muted mt-1" style="font-size:0.8rem;"></div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4 fw-semibold">Perbarui</button>
                <a href="<?= $base_url ?>/pages/kategori/index.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
    const form      = document.getElementById('formEditKategori');
    const namaInput = document.getElementById('nama_kategori');
    const deskInput = document.getElementById('deskripsi');
    const charCount = document.getElementById('char-count');
    const errNama   = document.getElementById('err-nama');

    // Inisialisasi counter
    charCount.textContent = deskInput.value.length + ' / 100 karakter';

    deskInput.addEventListener('input', function () {
        const len = this.value.length;
        charCount.textContent = len + ' / 100 karakter';
        charCount.style.color = len > 100 ? '#dc3545' : '#6c757d';
    });

    namaInput.addEventListener('input', function () {
        if (this.value.trim() !== '') {
            errNama.style.display = 'none';
            this.classList.remove('is-invalid');
        }
    });

    form.addEventListener('submit', function (e) {
        const nama = namaInput.value.trim();

        if (!nama) {
            errNama.textContent = 'Nama kategori wajib diisi.';
            errNama.style.display = 'block';
            namaInput.classList.add('is-invalid');
            e.preventDefault();
            return;
        }

        if (!confirm('Yakin ingin memperbarui kategori ini?')) {
            e.preventDefault();
        }
    });
</script>

<?php include_once '../../includes/footer.php'; ?>