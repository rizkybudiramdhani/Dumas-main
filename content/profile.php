<?php
// Get user data from session
$id_tim = isset($_SESSION['id_tim']) ? $_SESSION['id_tim'] : 0;
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Get full user data from database
$query_user = "SELECT * FROM tabel_tim WHERE id_tim = ?";
$stmt = mysqli_prepare($db, $query_user);
mysqli_stmt_bind_param($stmt, "i", $id_tim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

// Handle update profile
$success_message = '';
$error_message = '';

if (isset($_POST['update_profile'])) {
    $nama_baru = mysqli_real_escape_string($db, $_POST['nama']);
    $username_baru = mysqli_real_escape_string($db, $_POST['username']);
    
    // Check if username already exists (except current user)
    $query_check = "SELECT id_tim FROM tabel_tim WHERE username = ? AND id_tim != ?";
    $stmt_check = mysqli_prepare($db, $query_check);
    mysqli_stmt_bind_param($stmt_check, "si", $username_baru, $id_tim);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        $error_message = 'Username sudah digunakan!';
    } else {
        $query_update = "UPDATE tabel_tim SET nama = ?, username = ? WHERE id_tim = ?";
        $stmt_update = mysqli_prepare($db, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $nama_baru, $username_baru, $id_tim);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['nama'] = $nama_baru;
            $_SESSION['username'] = $username_baru;
            $success_message = 'Profile berhasil diupdate!';
            
            // Refresh user data
            $user_data['nama'] = $nama_baru;
            $user_data['username'] = $username_baru;
        } else {
            $error_message = 'Gagal update profile!';
        }
    }
}

// Handle change password
if (isset($_POST['change_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $password_confirm = $_POST['password_confirm'];
    
    // Verify old password
    if (password_verify($password_lama, $user_data['password'])) {
        if ($password_baru === $password_confirm) {
            if (strlen($password_baru) >= 6) {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                
                $query_pass = "UPDATE tabel_tim SET password = ? WHERE id_tim = ?";
                $stmt_pass = mysqli_prepare($db, $query_pass);
                mysqli_stmt_bind_param($stmt_pass, "si", $password_hash, $id_tim);
                
                if (mysqli_stmt_execute($stmt_pass)) {
                    $success_message = 'Password berhasil diubah!';
                } else {
                    $error_message = 'Gagal mengubah password!';
                }
            } else {
                $error_message = 'Password baru minimal 6 karakter!';
            }
        } else {
            $error_message = 'Konfirmasi password tidak cocok!';
        }
    } else {
        $error_message = 'Password lama salah!';
    }
}

// Get user stats
$query_stats = "SELECT COUNT(*) as total FROM tabel_laporan";
$result_stats = mysqli_query($db, $query_stats);
$total_laporan = mysqli_fetch_assoc($result_stats)['total'];

// Role display
$role_display = ucfirst($role);
if ($role == 'ditresnarkoba') $role_display = 'Ditresnarkoba';
if ($role == 'ditsamapta') $role_display = 'Ditsamapta';
if ($role == 'ditbinmas') $role_display = 'Ditbinmas';
?>

<style>
    .profile-card {
        border-radius: 15px;
        border: 2px solid #FFD700;
        box-shadow: 0 4px 15px rgba(26, 31, 58, 0.15);
        overflow: hidden;
        background: #fff;
    }

    .profile-header {
        background: #1a1f3a;
        border-bottom: 3px solid #FFD700;
        color: white;
        padding: 40px 30px;
        text-align: center;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #FFD700;
        border: 3px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 3rem;
        font-weight: 700;
        color: #1a1f3a;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
    }

    .profile-name {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .profile-role {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 20px;
    }

    .profile-stats {
        display: flex;
        justify-content: space-around;
        padding: 20px;
        background: rgba(255, 215, 0, 0.1);
        border-radius: 10px;
        border: 1px solid rgba(255, 215, 0, 0.3);
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #FFD700;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #fff;
        opacity: 0.95;
    }

    .form-section {
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(26, 31, 58, 0.15);
        border: 2px solid rgba(255, 215, 0, 0.3);
        margin-bottom: 20px;
    }

    .form-section h5 {
        color: #1a1f3a;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #FFD700;
    }

    .info-row {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #1a1f3a;
        width: 200px;
        flex-shrink: 0;
    }

    .info-value {
        color: #495057;
        flex: 1;
    }

    .btn-update {
        background: #FFD700;
        border: 2px solid #1a1f3a;
        padding: 12px 40px;
        border-radius: 8px;
        color: #1a1f3a;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
        background: #1a1f3a;
        color: #FFD700;
        border-color: #FFD700;
    }

    .activity-item {
        padding: 15px;
        border-left: 3px solid #FFD700;
        background: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #fff;
        border-left-color: #1a1f3a;
        box-shadow: 0 2px 10px rgba(255, 215, 0, 0.2);
    }

    .activity-date {
        font-size: 0.875rem;
        color: #FFD700;
        font-weight: 600;
    }

    .form-control {
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }

    .form-group label {
        color: #1a1f3a;
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #1a1f3a;
        color: #FFD700;
        transform: translateY(-2px);
    }

    .badge-primary {
        background: #1a1f3a;
        color: #FFD700;
        border: 1px solid #FFD700;
    }

    .badge-success {
        background: #28a745;
        color: white;
    }

    .breadcrumb-item.active {
        color: #FFD700;
    }

    .breadcrumb-item a {
        color: #1a1f3a;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #FFD700;
    }

    .page-header .title h4 {
        color: #1a1f3a;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #FFD700;
        color: #1a1f3a;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #FFD700;
        color: #1a1f3a;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <div class="title">
                <h4>Profile Pengguna</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="icon-copy dw dw-checked"></i> Berhasil!</strong> <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="icon-copy dw dw-warning"></i> Error!</strong> <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    
    <!-- Left Column: Profile Card -->
    <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
        <div class="card profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user_data['nama'], 0, 2)); ?>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user_data['nama']); ?></div>
                <div class="profile-role">
                    <i class="icon-copy dw dw-user1"></i> <?php echo $role_display; ?>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_laporan; ?></div>
                        <div class="stat-label">Total Laporan</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">
                            <?php 
                            $days = 0;
                            if ($user_data['last_login'] != '0000-00-00 00:00:00') {
                                $last = new DateTime($user_data['last_login']);
                                $now = new DateTime();
                                $diff = $now->diff($last);
                                $days = $diff->days;
                            }
                            echo $days;
                            ?>
                        </div>
                        <div class="stat-label">Hari Aktif</div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <h6 class="mb-3 font-weight-600">Informasi Akun</h6>
                
                <div class="info-row">
                    <div class="info-label">Username:</div>
                    <div class="info-value">@<?php echo htmlspecialchars($user_data['username']); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Role:</div>
                    <div class="info-value">
                        <span class="badge badge-primary"><?php echo $role_display; ?></span>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Last Login:</div>
                    <div class="info-value">
                        <?php 
                        if ($user_data['last_login'] != '0000-00-00 00:00:00') {
                            echo date('d M Y, H:i', strtotime($user_data['last_login'])) . ' WIB';
                        } else {
                            echo '-';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="form-section mt-3">
            <h5>📊 Aktivitas Terakhir</h5>
            <?php
            $query_activity = "SELECT * FROM tabel_laporan ORDER BY tanggal_lapor DESC LIMIT 3";
            $result_activity = mysqli_query($db, $query_activity);
            
            if (mysqli_num_rows($result_activity) > 0):
                while ($activity = mysqli_fetch_assoc($result_activity)):
            ?>
                <div class="activity-item">
                    <div class="font-weight-600"><?php echo htmlspecialchars($activity['judul_laporan']); ?></div>
                    <div class="activity-date">
                        <i class="dw dw-calendar1"></i> <?php echo date('d M Y', strtotime($activity['tanggal_lapor'])); ?>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p class="text-muted text-center py-3">Belum ada aktivitas</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Edit Forms -->
    <div class="col-xl-8 col-lg-8 col-md-12 mb-30">
        
        <!-- Edit Profile -->
        <div class="form-section">
            <h5>✏️ Edit Profile</h5>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-600">Nama Lengkap <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="nama" 
                                   value="<?php echo htmlspecialchars($user_data['nama']); ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-600">Username <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="username" 
                                   value="<?php echo htmlspecialchars($user_data['username']); ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="font-weight-600">Role</label>
                    <input class="form-control" type="text" 
                           value="<?php echo $role_display; ?>" 
                           disabled>
                    <small class="form-text text-muted">Role tidak dapat diubah</small>
                </div>
                
                <div class="form-group mb-0">
                    <button type="submit" name="update_profile" class="btn btn-update">
                        <i class="icon-copy dw dw-diskette"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="form-section">
            <h5>🔐 Ubah Password</h5>
            <form method="POST" id="form-password">
                <div class="form-group">
                    <label class="font-weight-600">Password Lama <span class="text-danger">*</span></label>
                    <input class="form-control" type="password" name="password_lama" 
                           placeholder="Masukkan password lama" 
                           required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-600">Password Baru <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" name="password_baru" 
                                   id="password-baru"
                                   placeholder="Minimal 6 karakter" 
                                   minlength="6"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-600">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" name="password_confirm" 
                                   id="password-confirm"
                                   placeholder="Ketik ulang password baru" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-0">
                    <button type="submit" name="change_password" class="btn btn-update">
                        <i class="icon-copy dw dw-padlock"></i> Ubah Password
                    </button>
                    <button type="reset" class="btn btn-secondary ml-2">
                        <i class="icon-copy dw dw-refresh"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Security -->
        <div class="form-section">
            <h5>🛡️ Keamanan Akun</h5>
            <div class="alert alert-info mb-3">
                <strong><i class="icon-copy dw dw-info"></i> Tips Keamanan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Gunakan password yang kuat (kombinasi huruf, angka, simbol)</li>
                    <li>Jangan gunakan password yang sama dengan akun lain</li>
                    <li>Ubah password secara berkala (setiap 3-6 bulan)</li>
                    <li>Jangan share password Anda kepada siapapun</li>
                    <li>Logout setelah selesai menggunakan sistem</li>
                </ul>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded">
                        <h6 class="font-weight-600 mb-2">Status Akun</h6>
                        <span class="badge badge-success">✓ Aktif</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded">
                        <h6 class="font-weight-600 mb-2">Sesi Login</h6>
                        <span class="badge badge-primary">✓ Aman</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    // Password match validation
    document.getElementById('form-password').addEventListener('submit', function(e) {
        var passwordBaru = document.getElementById('password-baru').value;
        var passwordConfirm = document.getElementById('password-confirm').value;

        if (passwordBaru !== passwordConfirm) {
            e.preventDefault();
            alert('Password baru dan konfirmasi password tidak cocok!');
            return false;
        }

        if (passwordBaru.length < 6) {
            e.preventDefault();
            alert('Password minimal 6 karakter!');
            return false;
        }
    });

    // Auto dismiss alert
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
</script>