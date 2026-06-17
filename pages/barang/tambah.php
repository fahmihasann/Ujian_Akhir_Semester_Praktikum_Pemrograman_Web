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

    // Validasi field teks
    if (empty($kode_barang) || empty($nama_barang) || $id_kategori === 0) {
        $error = "Semua field wajib diisi.";
    } elseif ($harga_barang <= 0) {
        $error = "Harga barang harus lebih dari 0.";
    } elseif ($stok < 0) {
        $error = "Stok tidak boleh negatif.";
    } else {
        // Proses upload gambar
        $nama_file_gambar = null;

        if (!empty($_FILES['gambar']['name'])) {
            $file       = $_FILES['gambar'];
            $ekstensi   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed    = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size   = 2 * 1024 * 1024; // 2MB

            if (!in_array($ekstensi, $allowed)) {
                $error = "Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.";
            } elseif ($file['size'] > $max_size) {
                $error = "Ukuran gambar maksimal 2MB.";
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = "Terjadi kesalahan saat upload gambar.";
            } else {
                // Buat nama file unik: kode_barang + timestamp
                $nama_file_gambar = preg_replace('/[^a-zA-Z0-9]/', '_', $kode_barang)
                                    . '_' . time() . '.' . $ekstensi;
                $upload_path = '../../assets/img/barang/' . $nama_file_gambar;

                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $error = "Gagal menyimpan gambar. Pastikan folder assets/img/barang/ dapat ditulis.";
                    $nama_file_gambar = null;
                }
            }
        }

        // Lanjut simpan ke database kalau tidak ada error upload
        if (empty($error)) {
            // Cek kode duplikat
            $cek = $conn->prepare("SELECT id_barang FROM barang WHERE kode_barang = ?");
            $cek->bind_param("s", $kode_barang);
            $cek->execute();
            $cek->store_result();

            if ($cek->num_rows > 0) {
                // Hapus gambar yang sudah terlanjur diupload
                if ($nama_file_gambar) {
                    unlink('../../assets/img/barang/' . $nama_file_gambar);
                }
                $error = "Kode barang sudah digunakan. Gunakan kode yang berbeda.";
            } else {
                $stmt = $conn->prepare("INSERT INTO barang (kode_barang, nama_barang, stok, harga_barang, id_kategori, gambar) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssidis", $kode_barang, $nama_barang, $stok, $harga_barang, $id_kategori, $nama_file_gambar);

                if ($stmt->execute()) {
                    header("Location: " . $base_url . "/pages/barang/index.php?success=" . urlencode("Barang berhasil ditambahkan."));
                    exit;
                } else {
                    $error = "Gagal menyimpan data.";
                }
                $stmt->close();
            }
            $cek->close();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Tambah Barang</h4>
    <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card-body p-4">

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="formTambah" method="POST" action="" enctype="multipart/form-data" novalidate>

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
            <div class="mb-3">
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

            <!-- Upload Gambar -->
            <div class="mb-4">
                <label for="gambar" class="form-label fw-semibold">Gambar Barang <span class="text-muted fw-normal">(opsional)</span></label>
                <input type="file" class="form-control" id="gambar" name="gambar"
                       accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
                <div id="err-gambar" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
                <!-- Preview gambar sebelum upload -->
                <div id="preview-container" class="mt-2" style="display:none;">
                    <img id="preview-img" src="#" alt="Preview"
                         style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #dee2e6; object-fit: cover;">
                    <div class="mt-1">
                        <small id="preview-info" class="text-muted"></small>
                        <button type="button" id="btn-hapus-preview" class="btn btn-sm btn-outline-danger ms-2">Hapus</button>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark px-4">Simpan</button>
                <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
    const form        = document.getElementById('formTambah');
    const inputGambar = document.getElementById('gambar');
    const previewCont = document.getElementById('preview-container');
    const previewImg  = document.getElementById('preview-img');
    const previewInfo = document.getElementById('preview-info');
    const errGambar   = document.getElementById('err-gambar');
    const btnHapus    = document.getElementById('btn-hapus-preview');

    // Helper
    function showError(elId, msg) {
        const el = document.getElementById(elId);
        el.textContent = msg;
        el.style.display = 'block';
    }
    function hideError(elId) {
        document.getElementById(elId).style.display = 'none';
    }

    // Preview gambar saat dipilih (addEventListener - syarat JS #4)
    inputGambar.addEventListener('change', function () {
        const file = this.files[0];
        errGambar.style.display = 'none';

        if (!file) {
            previewCont.style.display = 'none';
            return;
        }

        // Validasi tipe file di sisi client
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            errGambar.textContent = 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            errGambar.style.display = 'block';
            this.value = '';
            previewCont.style.display = 'none';
            return;
        }

        // Validasi ukuran file (2MB)
        if (file.size > 2 * 1024 * 1024) {
            errGambar.textContent = 'Ukuran gambar maksimal 2MB.';
            errGambar.style.display = 'block';
            this.value = '';
            previewCont.style.display = 'none';
            return;
        }

        // Tampilkan preview
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewInfo.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            previewCont.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Tombol hapus preview
    btnHapus.addEventListener('click', function () {
        inputGambar.value = '';
        previewCont.style.display = 'none';
        previewImg.src = '#';
    });

    // Hapus error saat user input
    document.getElementById('kode_barang').addEventListener('input', () => hideError('err-kode'));
    document.getElementById('nama_barang').addEventListener('input', () => hideError('err-nama'));
    document.getElementById('stok').addEventListener('input', () => hideError('err-stok'));
    document.getElementById('harga_barang').addEventListener('input', () => hideError('err-harga'));
    document.getElementById('id_kategori').addEventListener('change', () => hideError('err-kategori'));

    // Validasi sebelum submit
    form.addEventListener('submit', function (e) {
        let valid = true;

        const kode  = document.getElementById('kode_barang').value.trim();
        const nama  = document.getElementById('nama_barang').value.trim();
        const stok  = parseFloat(document.getElementById('stok').value);
        const harga = parseFloat(document.getElementById('harga_barang').value);
        const kat   = document.getElementById('id_kategori').value;

        if (!kode)  { showError('err-kode',     'Kode barang wajib diisi.');         valid = false; }
        if (!nama)  { showError('err-nama',     'Nama barang wajib diisi.');         valid = false; }
        if (isNaN(stok) || stok < 0)   { showError('err-stok',  'Stok tidak boleh negatif.'); valid = false; }
        if (isNaN(harga) || harga <= 0) { showError('err-harga', 'Harga harus lebih dari 0.'); valid = false; }
        if (!kat)   { showError('err-kategori', 'Pilih kategori terlebih dahulu.');  valid = false; }

        if (!valid) e.preventDefault();
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
