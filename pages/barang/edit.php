<?php
$base_url = 'http://localhost/inventaris-barang';
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
include_once '../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    header("Location: " . $base_url . "/pages/barang/index.php?error=" . urlencode("ID barang tidak valid."));
    exit;
}

// Ambil data barang
$stmt = $conn->prepare("SELECT * FROM barang WHERE id_barang = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$barang) {
    header("Location: " . $base_url . "/pages/barang/index.php?error=" . urlencode("Barang tidak ditemukan."));
    exit;
}

// Ambil kategori
$kategori_list = $conn->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang  = htmlspecialchars(trim($_POST['kode_barang'] ?? ''));
    $nama_barang  = htmlspecialchars(trim($_POST['nama_barang'] ?? ''));
    $stok         = (int)($_POST['stok'] ?? 0);
    $harga_barang = (float)($_POST['harga_barang'] ?? 0);
    $id_kategori  = (int)($_POST['id_kategori'] ?? 0);
    $hapus_gambar = isset($_POST['hapus_gambar']);

    if (empty($kode_barang) || empty($nama_barang) || $id_kategori === 0) {
        $error = "Semua field wajib diisi.";
    } elseif ($harga_barang <= 0) {
        $error = "Harga barang harus lebih dari 0.";
    } elseif ($stok < 0) {
        $error = "Stok tidak boleh negatif.";
    } else {
        $gambar_baru = $barang['gambar']; // default: pakai gambar lama

        // Jika user centang "hapus gambar"
        if ($hapus_gambar && $barang['gambar']) {
            $path_lama = '../../assets/img/barang/' . $barang['gambar'];
            if (file_exists($path_lama)) unlink($path_lama);
            $gambar_baru = null;
        }

        // Jika ada file gambar baru diupload
        if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['gambar'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size = 2 * 1024 * 1024;

            if (!in_array($ekstensi, $allowed)) {
                $error = "Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.";
            } elseif ($file['size'] > $max_size) {
                $error = "Ukuran gambar maksimal 2MB.";
            } else {
                // Hapus gambar lama dulu
                if ($barang['gambar']) {
                    $path_lama = '../../assets/img/barang/' . $barang['gambar'];
                    if (file_exists($path_lama)) unlink($path_lama);
                }
                // Simpan gambar baru
                $gambar_baru = preg_replace('/[^a-zA-Z0-9]/', '_', $kode_barang)
                               . '_' . time() . '.' . $ekstensi;
                if (!move_uploaded_file($file['tmp_name'], '../../assets/img/barang/' . $gambar_baru)) {
                    $error    = "Gagal menyimpan gambar.";
                    $gambar_baru = $barang['gambar']; // rollback ke gambar lama
                }
            }
        }

        if (empty($error)) {
            // Cek duplikat kode (kecuali milik barang ini)
            $cek = $conn->prepare("SELECT id_barang FROM barang WHERE kode_barang = ? AND id_barang != ?");
            $cek->bind_param("si", $kode_barang, $id);
            $cek->execute();
            $cek->store_result();

            if ($cek->num_rows > 0) {
                $error = "Kode barang sudah digunakan barang lain.";
            } else {
                $stmt = $conn->prepare("UPDATE barang SET kode_barang=?, nama_barang=?, stok=?, harga_barang=?, id_kategori=?, gambar=? WHERE id_barang=?");
                $stmt->bind_param("ssidisi", $kode_barang, $nama_barang, $stok, $harga_barang, $id_kategori, $gambar_baru, $id);
                if ($stmt->execute()) {
                    header("Location: " . $base_url . "/pages/barang/index.php?success=" . urlencode("Barang berhasil diperbarui."));
                    exit;
                } else {
                    $error = "Gagal memperbarui data.";
                }
                $stmt->close();
            }
            $cek->close();

            // Update tampilan form dengan data terbaru
            $barang['gambar'] = $gambar_baru;
        }
    }

    $barang['kode_barang']  = $_POST['kode_barang']  ?? $barang['kode_barang'];
    $barang['nama_barang']  = $_POST['nama_barang']  ?? $barang['nama_barang'];
    $barang['stok']         = $_POST['stok']         ?? $barang['stok'];
    $barang['harga_barang'] = $_POST['harga_barang'] ?? $barang['harga_barang'];
    $barang['id_kategori']  = $_POST['id_kategori']  ?? $barang['id_kategori'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Barang</h4>
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

        <form id="formEdit" method="POST" action="" enctype="multipart/form-data" novalidate>

            <div class="mb-3">
                <label for="kode_barang" class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="kode_barang" name="kode_barang"
                       value="<?= htmlspecialchars($barang['kode_barang']) ?>">
                <div id="err-kode" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-3">
                <label for="nama_barang" class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                       value="<?= htmlspecialchars($barang['nama_barang']) ?>">
                <div id="err-nama" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-3">
                <label for="stok" class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="stok" name="stok"
                       min="0" value="<?= htmlspecialchars($barang['stok']) ?>">
                <div id="err-stok" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-3">
                <label for="harga_barang" class="form-label fw-semibold">Harga Barang (Rp) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="harga_barang" name="harga_barang"
                       min="1" step="500" value="<?= htmlspecialchars($barang['harga_barang']) ?>">
                <div id="err-harga" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <div class="mb-3">
                <label for="id_kategori" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                <select class="form-select" id="id_kategori" name="id_kategori">
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($kat = $kategori_list->fetch_assoc()): ?>
                        <option value="<?= $kat['id_kategori'] ?>"
                            <?= ($barang['id_kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div id="err-kategori" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
            </div>

            <!-- Gambar saat ini -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Gambar Saat Ini</label>
                <?php if ($barang['gambar']): ?>
                    <div id="gambar-sekarang">
                        <img src="<?= $base_url ?>/assets/img/barang/<?= htmlspecialchars($barang['gambar']) ?>"
                             alt="Gambar Barang"
                             style="max-width: 180px; max-height: 180px; border-radius: 8px; border: 1px solid #dee2e6; object-fit: cover; display: block; margin-bottom: 8px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="hapus_gambar" name="hapus_gambar">
                            <label class="form-check-label text-danger" for="hapus_gambar">
                                Hapus gambar ini
                            </label>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-1" style="font-size:0.9rem;">Belum ada gambar.</p>
                <?php endif; ?>
            </div>

            <!-- Upload Gambar Baru -->
            <div class="mb-4">
                <label for="gambar" class="form-label fw-semibold">
                    <?= $barang['gambar'] ? 'Ganti Gambar' : 'Upload Gambar' ?>
                    <span class="text-muted fw-normal">(opsional)</span>
                </label>
                <input type="file" class="form-control" id="gambar" name="gambar"
                       accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
                <div id="err-gambar" class="text-danger mt-1" style="display:none; font-size:0.85rem;"></div>
                <div id="preview-container" class="mt-2" style="display:none;">
                    <img id="preview-img" src="#" alt="Preview"
                         style="max-width: 180px; max-height: 180px; border-radius: 8px; border: 1px solid #dee2e6; object-fit: cover;">
                    <div class="mt-1">
                        <small id="preview-info" class="text-muted"></small>
                        <button type="button" id="btn-hapus-preview" class="btn btn-sm btn-outline-danger ms-2">Batal</button>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark px-4 fw-semibold">Perbarui</button>
                <a href="<?= $base_url ?>/pages/barang/index.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
    const form        = document.getElementById('formEdit');
    const inputGambar = document.getElementById('gambar');
    const previewCont = document.getElementById('preview-container');
    const previewImg  = document.getElementById('preview-img');
    const previewInfo = document.getElementById('preview-info');
    const errGambar   = document.getElementById('err-gambar');
    const btnHapus    = document.getElementById('btn-hapus-preview');

    function showError(elId, msg) {
        const el = document.getElementById(elId);
        el.textContent = msg;
        el.style.display = 'block';
    }
    function hideError(elId) {
        document.getElementById(elId).style.display = 'none';
    }

    // Preview gambar baru
    inputGambar.addEventListener('change', function () {
        const file = this.files[0];
        errGambar.style.display = 'none';

        if (!file) { previewCont.style.display = 'none'; return; }

        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            errGambar.textContent = 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            errGambar.style.display = 'block';
            this.value = '';
            previewCont.style.display = 'none';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            errGambar.textContent = 'Ukuran gambar maksimal 2MB.';
            errGambar.style.display = 'block';
            this.value = '';
            previewCont.style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            previewInfo.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            previewCont.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    btnHapus.addEventListener('click', function () {
        inputGambar.value = '';
        previewCont.style.display = 'none';
    });

    // Hapus error saat input
    document.getElementById('kode_barang').addEventListener('input',   () => hideError('err-kode'));
    document.getElementById('nama_barang').addEventListener('input',   () => hideError('err-nama'));
    document.getElementById('stok').addEventListener('input',          () => hideError('err-stok'));
    document.getElementById('harga_barang').addEventListener('input',  () => hideError('err-harga'));
    document.getElementById('id_kategori').addEventListener('change',  () => hideError('err-kategori'));

    // Validasi + konfirmasi sebelum submit
    form.addEventListener('submit', function (e) {
        let valid = true;

        const kode  = document.getElementById('kode_barang').value.trim();
        const nama  = document.getElementById('nama_barang').value.trim();
        const stok  = parseFloat(document.getElementById('stok').value);
        const harga = parseFloat(document.getElementById('harga_barang').value);
        const kat   = document.getElementById('id_kategori').value;

        if (!kode)  { showError('err-kode',     'Kode barang wajib diisi.');         valid = false; }
        if (!nama)  { showError('err-nama',     'Nama barang wajib diisi.');         valid = false; }
        if (isNaN(stok) || stok < 0)    { showError('err-stok',  'Stok tidak boleh negatif.'); valid = false; }
        if (isNaN(harga) || harga <= 0) { showError('err-harga', 'Harga harus lebih dari 0.'); valid = false; }
        if (!kat)   { showError('err-kategori', 'Pilih kategori terlebih dahulu.');  valid = false; }

        if (!valid) { e.preventDefault(); return; }

        if (!confirm('Yakin ingin memperbarui data barang ini?')) e.preventDefault();
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
