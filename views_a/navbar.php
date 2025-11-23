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
if ($role == 'Ditresnarkoba') $role_display = 'Ditresnarkoba';
if ($role == 'Ditsamapta') $role_display = 'Ditsamapta';
if ($role == 'Ditbinmas') $role_display = 'Ditbinmas';

// Get notification count - Laporan Baru
$query_notif_count = "SELECT COUNT(*) as total FROM lapmas WHERE status = 'Baru'";
$result_notif_count = mysqli_query($db, $query_notif_count);
$notif_count = mysqli_fetch_assoc($result_notif_count)['total'];
?>
<style>
    .user-notification .dropdown-toggle {
        background: transparent;
        border: 2px solid #1E40AF;
        color: #1E40AF;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .user-notification .dropdown-toggle:hover {
        background: #1E40AF;
        color: #FFD700;
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(30, 64, 175, 0.3);
    }

    

    /* Notification Dropdown */
    .user-notification .dropdown-menu {
        min-width: 380px;
        max-width: 400px;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        margin-top: 10px;
        padding: 0;
    }

    .notification-header {
        background: #1E40AF;
        color: white;
        font-size: 1rem;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px 10px 0 0;
    }

    .notification-header h6 {
        margin: 0;
        font-weight: 700;
        color: #FFD700;
        font-size: 1rem;
    }

    .notification-header i {
        margin-right: 8px;
    }

    .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 12px 15px;
        transition: all 0.3s ease;
        border-bottom: 1px solid #e9ecef;
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .notification-item:hover {
        background: rgba(30, 64, 175, 0.05);
        border-left: 3px solid #1E40AF;
        text-decoration: none;
    }

    .notification-icon-wrapper {
        width: 40px;
        height: 40px;
        background: #FFD700;
        color: #1E40AF;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-weight: 700;
        color: #212529;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .notification-text {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .notification-text i {
        margin-right: 5px;
        color: #1E40AF;
    }

    .notification-time {
        color: #1E40AF;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .notification-time i {
        margin-right: 3px;
    }

    .notification-empty {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .notification-empty i {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .notification-empty p {
        margin: 0;
        font-size: 0.9rem;
    }

    .notification-footer {
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 12px 50px;
        text-align: center;
        border-radius: 0 0 10px 10px;
    }

    .notification-footer a {
        color: #1E40AF;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .notification-footer a:hover {
        color: #FFD700;
        background: #1E40AF;
        padding: 8px 15px;
        border-radius: 5px;
        display: inline-block;
    }

    /* Scrollbar untuk notification list */
    .notification-list::-webkit-scrollbar {
        width: 6px;
    }

    .notification-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notification-list::-webkit-scrollbar-thumb {
        background: #1E40AF;
        border-radius: 3px;
    }

    .notification-list::-webkit-scrollbar-thumb:hover {
        background: #FFD700;
    }
</style>
<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu"></div>
    </div>
    
    <div class="header-right">
        <!-- Notification Dropdown -->
        <div class="dashboard-setting user-notification">
            <div class="dropdown">
                <a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown">
                    <i class="icon-copy dw dw-notification"></i>
                    <?php if ($notif_count > 0): ?>
                        <span class="badge notification-badge"><?php echo $notif_count; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="notification-header">
                        <h6><i class="icon-copy dw dw-bell"></i> Notifikasi Laporan</h6>
                    </div>
                    <div class="notification-list">
                        <?php
                        // Get recent laporan baru (5 terakhir)
                        $query_notif = "SELECT l.*, a.Nama as nama_pelapor
                                        FROM lapmas l
                                        LEFT JOIN akun a ON l.Id_akun = a.Id_akun
                                        WHERE l.status = 'Baru'
                                        ORDER BY l.tanggal_lapor DESC
                                        LIMIT 5";
                        $result_notif = mysqli_query($db, $query_notif);

                        if (mysqli_num_rows($result_notif) > 0):
                            while ($notif = mysqli_fetch_assoc($result_notif)):
                                $nama_pelapor = $notif['nama_pelapor'] ? $notif['nama_pelapor'] : 'Anonim';
                        ?>
                                <a href="dash.php?page=detail-pengaduan&id=<?php echo $notif['id_lapmas']; ?>" class="notification-item">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-icon-wrapper">
                                            <i class="icon-copy dw dw-file"></i>
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-title"><?php echo htmlspecialchars(substr($notif['judul'], 0, 40)); ?><?php echo strlen($notif['judul']) > 40 ? '...' : ''; ?></div>
                                            <div class="notification-text">
                                                <i class="icon-copy dw dw-user1"></i> <?php echo htmlspecialchars($nama_pelapor); ?>
                                            </div>
                                            <div class="notification-time">
                                                <i class="icon-copy dw dw-clock1"></i>
                                                <?php echo date('d M Y, H:i', strtotime($notif['tanggal_lapor'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div class="notification-empty">
                                <i class="icon-copy dw dw-inbox"></i>
                                <p>Tidak ada laporan baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="notification-footer">
                        <a href="dash.php?page=lihat-pengaduan">
                            <strong>Lihat Semua Laporan</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Info Dropdown -->
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
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

        <style>
            /* User Info Dropdown Styling */
            .user-info-dropdown {
                margin-left: 15px;
            }

            .user-info-dropdown .dropdown-toggle {
                display: flex;
                align-items: center;
                padding: 8px 15px;
                border-radius: 25px;
                text-decoration: none;
                color: #1E40AF;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .user-info-dropdown .dropdown-toggle:hover {
                color: #FFD700;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(30, 64, 175, 0.3);
            }

            .user-info-dropdown .user-icon {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                overflow: hidden;
                margin-right: 10px;
                border: 2px solid #FFD700;
            }

            .user-info-dropdown .user-icon img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .user-info-dropdown .user-name {
                font-weight: 600;
                font-size: 0.95rem;
                white-space: nowrap;
            }

            .user-info-dropdown .dropdown {
                position: relative;
            }

            .user-info-dropdown .dropdown-menu {
                position: absolute;
                top: 100%;
                right: 0;
                min-width: 220px;
                background: #fff;
                border-radius: 10px;
                border: 1px solid #e0e0e0;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                margin-top: 10px;
                padding: 10px 0;
                display: none;
                z-index: 1000;
            }

            .user-info-dropdown .dropdown-menu.show {
                display: block;
            }

            .user-info-dropdown .dropdown-item {
                
                color: #495057;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                text-decoration: none;
                cursor: pointer;
            }

            .user-info-dropdown .dropdown-item i {
                margin-right: 0px;
                margin-left: 1px;
                font-size: 1.1rem;
                width: 20px;
                text-align: center;
                color: #1E40AF;
            }

            .user-info-dropdown .dropdown-item:hover {
                background: linear-gradient(90deg, rgba(30, 64, 175, 0.1) 0%, rgba(255, 215, 0, 0.1) 100%);
                color: #1E40AF;
                padding-left: 30px;
            }

            .user-info-dropdown .dropdown-item:last-child {
                border-top: 1px solid #e9ecef;
                margin-top: 5px;
                color: #dc3545;
            }

            .user-info-dropdown .dropdown-item:last-child i {
                color: #dc3545;
            }

            .user-info-dropdown .dropdown-item:last-child:hover {
                background: rgba(220, 53, 69, 0.1);
                color: #dc3545;
            }

            /* Mobile responsive */
            @media (max-width: 768px) {
                .user-info-dropdown .user-name {
                    display: none;
                }

                .user-info-dropdown .dropdown-toggle {
                    padding: 5px;
                }
            }
        </style>

        <script>
            // Manual dropdown toggle for user info
            document.addEventListener('DOMContentLoaded', function() {
                const userDropdown = document.querySelector('.user-info-dropdown .dropdown-toggle');
                const userMenu = document.querySelector('.user-info-dropdown .dropdown-menu');

                if (userDropdown && userMenu) {
                    // Toggle dropdown on click
                    userDropdown.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Close other dropdowns
                        document.querySelectorAll('.dropdown-menu').forEach(menu => {
                            if (menu !== userMenu) {
                                menu.classList.remove('show');
                            }
                        });

                        // Toggle current dropdown
                        userMenu.classList.toggle('show');
                        this.setAttribute('aria-expanded', userMenu.classList.contains('show'));
                    });

                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!userDropdown.contains(e.target) && !userMenu.contains(e.target)) {
                            userMenu.classList.remove('show');
                            userDropdown.setAttribute('aria-expanded', 'false');
                        }
                    });

                    // Prevent dropdown from closing when clicking inside
                    userMenu.addEventListener('click', function(e) {
                        // Allow links to work
                        if (e.target.tagName === 'A') {
                            return;
                        }
                        e.stopPropagation();
                    });
                }
            });
        </script>
        
    </div>
</div>

