<?php
// File: views_a/navbar.php

include 'config/koneksi.php';
include 'config/gate.php';

// Get user data from session
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Get full user data
$query_user = "SELECT nama, role FROM akun WHERE nama = ? AND role = ?";
$stmt = mysqli_prepare($db, $query_user);
mysqli_stmt_bind_param($stmt, "ss", $_SESSION['nama'], $_SESSION['role']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

// Set nama from database if available
if ($user_data) {
    $nama = $user_data['nama'];
    $role = $user_data['role'];
}

// Format role untuk display
$role_display = ucfirst($role);
if ($role == 'ditresnarkoba') $role_display = 'Ditresnarkoba';
if ($role == 'ditsamapta') $role_display = 'Ditsamapta';
if ($role == 'ditbinmas') $role_display = 'Ditbinmas';
?>

<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu"></div>
    </div>
    
    <div class="header-right">
        <!-- Notification Dropdown -->
        <div class="dashboard-setting user-notification">
            <div class="dropdown">
                <?php include 'notification_component.php'; ?>
            </div>
        </div>
        
        <!-- User Info Dropdown -->
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
                        <img src="vendors/images/photo1.jpg" alt="<?php echo htmlspecialchars($nama); ?>">
                    </span>
                    <span class="user-name"><?php echo htmlspecialchars($nama); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <a class="dropdown-item" href="dash.php?page=profile">
                        <i class="dw dw-user1"></i> Profile
                    </a>
                    <a class="dropdown-item" href="dash.php">
                        <i class="dw dw-settings2"></i> Dashboard
                    </a>
                    <a class="dropdown-item" href="dash.php?page=lihat-pengaduan">
                        <i class="dw dw-file"></i> Pengaduan
                    </a>
                    <a class="dropdown-item" href="logout.php">
                        <i class="dw dw-logout"></i> Log Out
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</div>