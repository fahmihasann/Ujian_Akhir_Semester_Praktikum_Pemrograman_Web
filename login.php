<?php
$base_url = 'http://localhost/inventaris-barang';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_user'])) {
    header("Location: " . $base_url . "/index.php");
    exit;
}

require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id_user, nama_lengkap, password, role FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['id_user'] = $row['id_user'];
                $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                $_SESSION['role'] = $row['role'];
                header("Location: " . $base_url . "/index.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventaris Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            background-color: #0d6efd;
            border-radius: 12px 12px 0 0;
            padding: 2rem;
            text-align: center;
        }

        .error-message {
            display: none;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <div class="login-card shadow-lg rounded-3">
        <div class="login-header text-white">
            <h4 class="fw-bold mb-1 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-seam"></i> Inventory
            </h4>
            <p class="mb-0 opacity-75" style="font-size: 0.9rem;">Masuk untuk melanjutkan</p>
        </div>

        <div class="card border-0 rounded-bottom-3">
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="server-error">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="" novalidate>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username">
                        <div class="error-message" id="error-username">Username wajib diisi.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Masukkan password" autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" id="btnTogglePassword"
                                title="Tampilkan password"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="error-message" id="error-password">Password wajib diisi.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            Masuk
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const errorUsername = document.getElementById('error-username');
        const errorPassword = document.getElementById('error-password');
        const btnToggle = document.getElementById('btnTogglePassword');

        // Toggle password visibility (Manipulasi DOM + addEventListener - syarat JS #3 & #4)
        btnToggle.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                btnToggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
                btnToggle.title = 'Sembunyikan password';
            } else {
                passwordInput.type = 'password';
                btnToggle.innerHTML = '<i class="bi bi-eye"></i>';
                btnToggle.title = 'Tampilkan password';
            }
        });

        // Hapus error saat input berubah
        usernameInput.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                errorUsername.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

        passwordInput.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                errorPassword.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

        // Validasi saat form submit (syarat JS #1)
        form.addEventListener('submit', function (e) {
            let valid = true;

            if (usernameInput.value.trim() === '') {
                errorUsername.style.display = 'block';
                usernameInput.classList.add('is-invalid');
                valid = false;
            } else {
                errorUsername.style.display = 'none';
                usernameInput.classList.remove('is-invalid');
            }

            if (passwordInput.value.trim() === '') {
                errorPassword.style.display = 'block';
                passwordInput.classList.add('is-invalid');
                valid = false;
            } else {
                errorPassword.style.display = 'none';
                passwordInput.classList.remove('is-invalid');
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>