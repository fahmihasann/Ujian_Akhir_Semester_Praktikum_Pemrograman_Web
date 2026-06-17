<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = htmlspecialchars(trim($_POST['nama_kategori'] ?? ''));
    $deskripsi     = htmlspecialchars(trim($_POST['deskripsi'] ?? ''));

    if (empty($nama_kategori)) {
        $error = "Nama kategori wajib diisi.";
    } else {
        // Cek duplikat nama kategori
        $cek = $conn->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
        $cek->bind_param("s", $nama_kategori);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Nama kategori sudah ada. Gunakan nama yang berbeda.";
        } else {
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_kategori, $deskripsi);
            if ($stmt->execute()) {
                header("Location: " . $base_url . "/pages/kategori/index.php?success=" . urlencode("Kategori berhasil ditambahkan."));
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
    <h4 class="fw-bold mb-0">Tambah Kategori</h4>
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

        <form id="formTambahKategori" method="POST" action="" novalidate>

            <div class="mb-3">
                <label for="nama_kategori" class="form-label fw-semibold">
                    Nama Kategori <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori"
                       placeholder="Contoh: Perlengkapan Dapur"
                       value="<?= htmlspecialchars($_POST['nama_kategori'] ?? '') ?>">
                <div id="err-nama" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-4">
                <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi"
                          rows="3" placeholder="Deskripsi singkat kategori (opsional)"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                <div id="char-count" class="text-muted mt-1" style="font-size:0.8rem;">0 / 100 karakter</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="<?= $base_url ?>/pages/kategori/index.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
    const form       = document.getElementById('formTambahKategori');
    const namaInput  = document.getElementById('nama_kategori');
    const deskInput  = document.getElementById('deskripsi');
    const charCount  = document.getElementById('char-count');
    const errNama    = document.getElementById('err-nama');

    // Counter karakter deskripsi (addEventListener - syarat JS #4)
    deskInput.addEventListener('input', function () {
        const len = this.value.length;
        charCount.textContent = len + ' / 100 karakter';
        charCount.style.color = len > 100 ? '#dc3545' : '#6c757d';
    });

    // Hapus error saat user mengetik
    namaInput.addEventListener('input', function () {
        if (this.value.trim() !== '') {
            errNama.style.display = 'none';
            this.classList.remove('is-invalid');
        }
    });

    // Validasi sebelum submit
    form.addEventListener('submit', function (e) {
        const nama = namaInput.value.trim();
        const desk = deskInput.value.trim();

        if (!nama) {
            errNama.textContent = 'Nama kategori wajib diisi.';
            errNama.style.display = 'block';
            namaInput.classList.add('is-invalid');
            e.preventDefault();
            return;
        }

        if (desk.length > 100) {
            alert('Deskripsi maksimal 100 karakter.');
            e.preventDefault();
            return;
        }
    });
</script>

<?php include_once '../../includes/footer.php'; ?>