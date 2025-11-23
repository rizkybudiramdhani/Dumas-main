<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login sebagai admin, redirect ke dashboard
if (isset($_SESSION['Id_akun']) && in_array($_SESSION['role'] ?? '', ['Ditresnarkoba', 'Ditsamapta', 'Ditbinmas'])) {
    header("Location: dash.php");
    exit;
}

include 'config/koneksi.php';

$login_error = null;

// Handle Login Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'admin_login') {
    $identifier = mysqli_real_escape_string($db, trim($_POST['identifier']));
    $password = $_POST['password'];

    // Cek apakah input adalah email atau nomor HP, dan role harus admin (bukan masyarakat)
    $query = "SELECT * FROM akun WHERE (Email = ? OR Nomor_hp = ?) AND Role IN ('Ditresnarkoba', 'Ditsamapta', 'Ditbinmas')";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Cek password
        if (password_verify($password, $user['Password'])) {
            $_SESSION['Id_akun'] = $user['Id_akun'];
            $_SESSION['nama'] = $user['Nama'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['nomor_hp'] = $user['Nomor_hp'];

            header("Location: dash.php");
            exit;
        } else {
            // Jika hash tidak valid, coba plain text dan update
            if ($password === $user['Password']) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update = mysqli_prepare($db, "UPDATE akun SET Password = ? WHERE Id_akun = ?");
                mysqli_stmt_bind_param($update, "si", $new_hash, $user['Id_akun']);
                mysqli_stmt_execute($update);

                $_SESSION['Id_akun'] = $user['Id_akun'];
                $_SESSION['nama'] = $user['Nama'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['nomor_hp'] = $user['Nomor_hp'];

                header("Location: dash.php");
                exit;
            }
            $login_error = "Password salah!";
        }
    } else {
        $login_error = "Akun admin tidak ditemukan atau Anda tidak memiliki akses!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Polda Sumut</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background-color: #0d1217;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .admin-auth-container {
            width: 100%;
            max-width: 480px;
            background-color: #1a1f3a;
            border-radius: 20px;
            border: 2px solid #FFD700;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7);
        }
        .admin-auth-header {
            background-color: #1E40AF;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 4px solid #FFD700;
        }
        .admin-auth-header .logo {
            width: 80px;
            height: 80px;
            background-color: #FFD700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
        }
        .admin-auth-header .logo i {
            font-size: 2.5rem;
            color: #1E40AF;
        }
        .admin-auth-header h1 {
            color: #FFD700;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .admin-auth-header p {
            color: #e0e0e0;
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        .admin-auth-body {
            padding: 40px 35px;
            background-color: #1a1f3a;
        }
        .admin-badge {
            background-color: #FFD700;
            color: #1E40AF;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 30px;
        }
        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: block;
            color: #FFD700;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 16px 18px;
            background-color: #0d1217;
            border: 2px solid #2a3f5f;
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.15);
        }
        .form-control::placeholder { color: #6c7a89; }
        .input-group { position: relative; }
        .input-group .form-control { padding-right: 50px; }
        .input-group .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c7a89;
            cursor: pointer;
            transition: color 0.3s ease;
            font-size: 1.1rem;
        }
        .input-group .toggle-password:hover { color: #FFD700; }
        .btn-admin-login {
            width: 100%;
            padding: 16px;
            background-color: #FFD700;
            color: #1E40AF;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-admin-login:hover {
            background-color: #ffc700;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        .back-link a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .back-link a:hover { color: #FFD700; }
        .back-link a i { margin-right: 6px; }
        .user-link-container {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #2a3f5f;
        }
        .user-link {
            color: #ffffff;
            background-color: #2a3f5f;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            border: 2px solid #FFD700;
        }
        .user-link:hover {
            background-color: #1E40AF;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
        }
        .user-link i { font-size: 1rem; }
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-danger {
            background-color: #dc2626;
            border: 2px solid #b91c1c;
            color: #ffffff;
        }
        .alert i {
            font-size: 1.2rem;
        }
        @media (max-width: 480px) {
            .admin-auth-container {
                border-radius: 15px;
                border-width: 1px;
            }
            .admin-auth-header { padding: 30px 25px; }
            .admin-auth-header h1 { font-size: 1.7rem; }
            .admin-auth-body { padding: 30px 25px; }
        }
    </style>
</head>
<body>
    <div class="admin-auth-container">
        <div class="admin-auth-header">
            <div class="logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>Admin Panel</h1>
            <p>Sistem Pengaduan Masyarakat Terpadu</p>
        </div>

        <div class="admin-auth-body">
            <div class="text-center">
                <span class="admin-badge">
                    <i class="bi bi-person-badge-fill"></i>
                    Akses Khusus Administrator
                </span>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="admin_login">

                <?php if ($login_error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($login_error) ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="adminIdentifier">
                        <i class="bi bi-envelope-fill me-2"></i>Email atau No. HP
                    </label>
                    <input type="text" class="form-control" id="adminIdentifier" name="identifier" placeholder="Masukkan email atau nomor HP" required autofocus>
                </div>

                <div class="form-group">
                    <label for="adminPassword">
                        <i class="bi bi-key-fill me-2"></i>Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="adminPassword" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" data-target="adminPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-admin-login">
                    <i class="bi bi-shield-check"></i>
                    <span>Login sebagai Admin</span>
                </button>

                <div class="back-link">
                    <a href="index.php">
                        <i class="bi bi-arrow-left"></i>Kembali ke Beranda
                    </a>
                </div>

                <div class="user-link-container">
                    <a href="login.php" class="user-link">
                        <i class="bi bi-person me-2"></i>Login sebagai Masyarakat
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
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