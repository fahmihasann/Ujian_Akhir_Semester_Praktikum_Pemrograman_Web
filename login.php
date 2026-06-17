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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #ffffff;
            color: #000000;
            min-height: 100vh;
            overflow-x: hidden;
            font-family: 'JetBrains Mono', monospace;
        }

        .left-pane {
            background: linear-gradient(180deg, #111111 0%, #444444 60%, #e0e0e0 100%);
            border-radius: 1rem;
            margin: 1rem;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: calc(100vh - 2rem);
            position: relative;
            overflow: hidden;
            color: #ffffff;
        }

        .left-pane-content {
            z-index: 2;
        }

        .right-pane {
            padding: 4rem 10%;
        }

        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #000000;
            border-radius: 0.75rem;
            padding: 0.8rem 1.2rem;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #888888;
            color: #000000;
            box-shadow: 0 0 0 0.15rem rgba(0, 0, 0, 0.1);
        }
        
        .form-control::placeholder {
            color: #6c757d;
        }

        .input-group .btn {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-left: none;
            color: #6c757d;
            border-radius: 0 0.75rem 0.75rem 0;
        }
        
        .input-group .btn:hover {
            color: #000000;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 0.15rem rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
        }

        .input-group:focus-within .form-control,
        .input-group:focus-within .btn {
            border-color: #888888;
            background-color: #ffffff;
        }

        .input-group .form-control:focus,
        .input-group .btn:focus {
            box-shadow: none;
        }

        .btn-primary {
            background-color: #111111;
            color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            padding: 0.8rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #333333;
            color: #ffffff;
        }

        .error-message {
            display: none;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 6px;
        }
        
        .form-label {
            font-size: 0.9rem;
            color: #333333;
        }
    </style>
</head>

<body>
    <div class="row g-0 vh-100">
        <!-- Left Pane -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="left-pane">
                <div class="left-pane-content">
                    <h1 class="display-4 fw-bold mb-0">Login to <br>Inventory</h1>
                </div>
            </div>
        </div>

        <!-- Right Pane -->
        <div class="col-12 col-lg-6 d-flex flex-column justify-content-center right-pane">
            <div class="w-100 mx-auto" style="max-width: 420px;">
                <div class="text-center mb-5">
                    <h3 class="fw-bold mb-2">Login Account</h3>
                    <p class="text-muted">Enter your credentials to access the system.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show bg-danger text-white border-0" role="alert" id="server-error">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="" novalidate>
                    <div class="mb-4">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Enter your username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username">
                        <div class="error-message" id="error-username">Username wajib diisi.</div>
                    </div>

                    <div class="mb-5">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="btn" id="btnTogglePassword"
                                title="Tampilkan password"><i class="bi bi-eye-slash"></i></button>
                        </div>
                        <div class="error-message" id="error-password">Password wajib diisi.</div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Log In
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

        // Toggle password visibility
        btnToggle.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                btnToggle.innerHTML = '<i class="bi bi-eye"></i>';
                btnToggle.title = 'Sembunyikan password';
            } else {
                passwordInput.type = 'password';
                btnToggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
                btnToggle.title = 'Tampilkan password';
            }
        });

        // Hapus error saat input berubah
        usernameInput.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                errorUsername.style.display = 'none';
            }
        });

        passwordInput.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                errorPassword.style.display = 'none';
            }
        });

        // Validasi saat form submit
        form.addEventListener('submit', function (e) {
            let valid = true;

            if (usernameInput.value.trim() === '') {
                errorUsername.style.display = 'block';
                valid = false;
            } else {
                errorUsername.style.display = 'none';
            }

            if (passwordInput.value.trim() === '') {
                errorPassword.style.display = 'block';
                valid = false;
            } else {
                errorPassword.style.display = 'none';
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>