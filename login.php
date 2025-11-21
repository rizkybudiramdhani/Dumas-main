<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

$login_error = null;
$register_error = null;

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $identifier = mysqli_real_escape_string($db, trim($_POST['identifier']));
    $password = $_POST['password'];

    // Cek apakah input adalah email atau nomor HP
    $query = "SELECT * FROM akun WHERE Email = ? OR Nomor_hp = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        // DEBUG OFF
        // echo "Input: " . $password . "<br>";
        // echo "DB Hash: " . $user['Password'] . "<br>";
        // echo "Hash Length: " . strlen($user['Password']) . "<br>";
        // echo "Verify: " . (password_verify($password, $user['Password']) ? 'true' : 'false'); exit;

        // Cek password
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['Id_akun'];
            $_SESSION['nama'] = $user['Nama'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['nomor_hp'] = $user['Nomor_hp'];

            // Redirect berdasarkan role
            if (in_array($user['Role'], ['Ditresnarkoba', 'Ditsamapta', 'Ditbinmas'])) {
                header("Location: dash.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            // Jika hash tidak valid, coba plain text dan update
            if ($password === $user['Password']) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update = mysqli_prepare($db, "UPDATE akun SET Password = ? WHERE Id_akun = ?");
                mysqli_stmt_bind_param($update, "si", $new_hash, $user['Id_akun']);
                mysqli_stmt_execute($update);

                $_SESSION['user_id'] = $user['Id_akun'];
                $_SESSION['nama'] = $user['Nama'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['nomor_hp'] = $user['Nomor_hp'];

                if (in_array($user['Role'], ['Ditresnarkoba', 'Ditsamapta', 'Ditbinmas'])) {
                    header("Location: dash.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            }
            $login_error = "Password salah!";
        }
    } else {
        $login_error = "Email atau nomor HP tidak ditemukan!";
    }
}

// Handle Register
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    $nama = mysqli_real_escape_string($db, trim($_POST['nama']));
    $email = mysqli_real_escape_string($db, trim($_POST['email']));
    $nomor_hp = mysqli_real_escape_string($db, trim($_POST['nomor_hp']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if ($password !== $confirm_password) {
        $register_error = "Password tidak cocok!";
    } else {
        // Cek email sudah ada
        $check = mysqli_prepare($db, "SELECT Id_akun FROM akun WHERE Email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $register_error = "Email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query_insert = "INSERT INTO akun (Nama, Email, Nomor_hp, Password, Role) VALUES (?, ?, ?, ?, 'masyarakat')";
            $stmt_insert = mysqli_prepare($db, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssss", $nama, $email, $nomor_hp, $hashed_password);

            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['user_id'] = mysqli_insert_id($db);
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'masyarakat';
                $_SESSION['nomor_hp'] = $nomor_hp;

                header("Location: index.php");
                exit;
            } else {
                $register_error = "Gagal mendaftarkan akun!";
            }
        }
    }
}

$active_tab = isset($_POST['action']) && $_POST['action'] == 'register' ? 'register' : 'login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Polda Sumut</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background-color: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .auth-container {
            width: 100%;
            max-width: 450px;
            background-color: #2a2a2a;
            border-radius: 20px;
            border: 1px solid #333;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        .auth-header {
            background-color: #1E40AF;
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid #FFD700;
        }
        .auth-header h1 {
            color: #FFD700;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .auth-header p { color: #e0e0e0; font-size: 0.9rem; }
        .auth-tabs {
            display: flex;
            background-color: #1a1a1a;
        }
        .auth-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            color: #888;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            background: transparent;
        }
        .auth-tab:hover { color: #FFD700; }
        .auth-tab.active {
            color: #FFD700;
            background-color: #2a2a2a;
            border-bottom: 3px solid #FFD700;
        }
        .auth-body { padding: 30px; }
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            color: #FFD700;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background-color: #1a1a1a;
            border: 1px solid #444;
            border-radius: 10px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        .form-control::placeholder { color: #666; }
        .input-group { position: relative; }
        .input-group .form-control { padding-right: 45px; }
        .input-group .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .input-group .toggle-password:hover { color: #FFD700; }
        .btn-primary-custom {
            width: 100%;
            padding: 14px;
            background-color: #FFD700;
            color: #1E40AF;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background-color: #e6c200;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.3);
        }
        .btn-secondary-custom {
            width: 100%;
            padding: 14px;
            background-color: #1E40AF;
            color: #FFD700;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-secondary-custom:hover {
            background-color: #1a3a9e;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(30, 64, 175, 0.3);
        }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .back-link a:hover { color: #FFD700; }
        .back-link a i { margin-right: 5px; }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-danger {
            background-color: rgba(220, 38, 38, 0.2);
            border: 1px solid #DC2626;
            color: #ff6b6b;
        }
        .alert-success {
            background-color: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            color: #4ade80;
        }
        @media (max-width: 480px) {
            .auth-container { border-radius: 15px; }
            .auth-header { padding: 25px 20px; }
            .auth-header h1 { font-size: 1.5rem; }
            .auth-body { padding: 25px 20px; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1><i class="bi bi-shield-lock me-2"></i>Polda Sumut</h1>
            <p>Sistem Pengaduan Masyarakat</p>
        </div>

        <div class="auth-tabs">
            <button class="auth-tab <?= $active_tab == 'login' ? 'active' : '' ?>" data-tab="login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
            <button class="auth-tab <?= $active_tab == 'register' ? 'active' : '' ?>" data-tab="register">
                <i class="bi bi-person-plus me-2"></i>Daftar
            </button>
        </div>

        <div class="auth-body">
            <!-- Login Form -->
            <form method="POST" class="auth-form <?= $active_tab == 'login' ? 'active' : '' ?>" id="loginForm">
                <input type="hidden" name="action" value="login">

                <?php if ($login_error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="loginIdentifier">Email atau No. HP</label>
                    <input type="text" class="form-control" id="loginIdentifier" name="identifier" placeholder="Masukkan email atau nomor HP" required>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" data-target="loginPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>

                <div class="back-link">
                    <a href="index.php"><i class="bi bi-arrow-left"></i>Kembali ke Beranda</a>
                </div>
            </form>

            <!-- Register Form -->
            <form method="POST" class="auth-form <?= $active_tab == 'register' ? 'active' : '' ?>" id="registerForm">
                <input type="hidden" name="action" value="register">

                <?php if ($register_error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($register_error) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="registerName">Nama Lengkap</label>
                    <input type="text" class="form-control" id="registerName" name="nama" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Masukkan email Anda" required>
                </div>

                <div class="form-group">
                    <label for="registerPhone">No. HP</label>
                    <input type="tel" class="form-control" id="registerPhone" name="nomor_hp" placeholder="Masukkan nomor HP" required>
                </div>

                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Buat password" required>
                        <button type="button" class="toggle-password" data-target="registerPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="registerConfirmPassword">Konfirmasi Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password" placeholder="Ulangi password" required>
                        <button type="button" class="toggle-password" data-target="registerConfirmPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-secondary-custom">
                    <i class="bi bi-person-plus me-2"></i>Daftar Akun
                </button>

                <div class="back-link">
                    <a href="index.php"><i class="bi bi-arrow-left"></i>Kembali ke Beranda</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
                document.getElementById(targetTab + 'Form').classList.add('active');
            });
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
    </script>
</body>
</html>
