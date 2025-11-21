<?php
// BARIS KRUSIAL: Memulai sesi PHP agar variabel $_SESSION dapat digunakan
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

$error = null; // Inisialisasi variabel error
$success = null; // Inisialisasi variabel success

// Mengatur header untuk AJAX response (agar tidak ada masalah CORS/encoding)
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($db, trim($_POST['nama']));
    $username = mysqli_real_escape_string($db, trim($_POST['username']));
    $email = mysqli_real_escape_string($db, trim($_POST['email']));
    $telepon = mysqli_real_escape_string($db, trim($_POST['telepon']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru
    $query_insert = "INSERT INTO tabel_users (nama, username, email, telepon, password, status) VALUES (?, ?, ?, ?, ?, 'user')";
    $stmt_insert = mysqli_prepare($db, $query_insert);
    mysqli_stmt_bind_param($stmt_insert, "sssss", $nama, $username, $email, $telepon, $hashed_password);

    if (mysqli_stmt_execute($stmt_insert)) {
        $success = "Registrasi berhasil! Silakan login.";

        // Setup Session setelah registrasi berhasil (opsional, langsung login)
        $_SESSION['user_id'] = mysqli_insert_id($db);
        $_SESSION['nama'] = $nama;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['status'] = 'user';
        $_SESSION['telepon'] = $telepon;

        // Respon untuk AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        } else {
            // Redirect untuk non-AJAX request
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Gagal mendaftarkan akun. Silakan coba lagi.";
    }

    // Respon Error untuk AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Trisula</title>

    <link rel="apple-touch-icon" sizes="180x180" href="vendors/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="vendors/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="vendors/images/favicon-16x16.png">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="vendors/styles/core.css">
    <link rel="stylesheet" type="text/css" href="vendors/styles/icon-font.min.css">
    <link rel="stylesheet" type="text/css" href="vendors/styles/style.css">

    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-119386393-1');
    </script>
</head>

<body class="login-page">

    <div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 col-lg-7">
                    <img src="vendors/images/login-page-img.png" alt="">
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="login-box bg-white box-shadow border-radius-10">
                        <div class="login-title">
                            <h2 class="text-center text-primary">Register Account</h2>
                        </div>
                        <form method="POST" action="">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <div class="input-group custom">
                                <input type="text" name="nama" class="form-control form-control-lg" placeholder="Nama Lengkap">
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="icon-copy dw dw-user1"></i></span>
                                </div>
                            </div>
                            <div class="input-group custom">
                                <input type="text" name="username" class="form-control form-control-lg" placeholder="Username">
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="icon-copy dw dw-user1"></i></span>
                                </div>
                            </div>
                            <div class="input-group custom">
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="Email">
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="dw dw-email"></i></span>
                                </div>
                            </div>
                            <div class="input-group custom">
                                <input type="tel" name="telepon" class="form-control form-control-lg" placeholder="Nomor Telepon">
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="dw dw-phone"></i></span>
                                </div>
                            </div>
                            <div class="input-group custom">
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="Password">
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
                                </div>
                            </div>
                            <div class="input-group custom">
                                <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Konfirmasi Password">
                                <div class="input-group-append custom">
                                    <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group mb-0">
                                        <input class="btn btn-primary btn-lg btn-block" type="submit" value="Register">
                                    </div>
                                    <div class="font-16 weight-600 pt-10 pb-10 text-center" data-color="#707373"><a href="index.php" style="text-decoration : none">Back</a></div>
                                    <div class="font-16 weight-600 pt-10 pb-10 text-center" data-color="#707373">OR</div>
                                    <div class="input-group mb-0">
                                        <a class="btn btn-outline-primary btn-lg btn-block" href="login.php">Login To Your Account</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendors/scripts/core.js"></script>
    <script src="vendors/scripts/script.min.js"></script>
    <script src="vendors/scripts/process.js"></script>
    <script src="vendors/scripts/layout-settings.js"></script>
</body>

</html>