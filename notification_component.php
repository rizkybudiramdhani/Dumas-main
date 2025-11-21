<?php
// File: views_a/notification_component.php
// Notification System for Navbar

// Get unread notifications (laporan dengan status 'baru')
$query_notif = "SELECT l.*, u.nama as nama_pelapor 
                FROM tabel_laporan l 
                LEFT JOIN tabel_users u ON l.id_user = u.id_users
                WHERE l.status_laporan = 'baru'
                ORDER BY l.tanggal_lapor DESC 
                LIMIT 10";
$result_notif = mysqli_query($db, $query_notif);

// Count unread notifications
$query_count = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan = 'baru'";
$result_count = mysqli_query($db, $query_count);
$notif_count_data = mysqli_fetch_assoc($result_count);
$notif_count = $notif_count_data ? (int)$notif_count_data['total'] : 0;
?>

<!-- Notification Styles -->
<style>
    .notification-dropdown {
        min-width: 350px;
        max-width: 400px;
        max-height: 500px;
        overflow-y: auto;
        border-radius: 10px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        border: none;
    }

    .notification-item {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 3px;
        font-size: 0.875rem;
    }

    .notification-text {
        color: #6c757d;
        font-size: 0.8125rem;
        margin-bottom: 3px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notification-time {
        color: #adb5bd;
        font-size: 0.75rem;
    }

    .notification-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #dc3545;
        color: white;
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 18px;
        text-align: center;
        line-height: 1.2;
    }

    .notification-header {
        padding: 15px;
        border-bottom: 2px solid #e9ecef;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .notification-footer {
        padding: 12px;
        text-align: center;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
    }

    .notification-footer a {
        color: #667eea;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
    }

    .notification-footer a:hover {
        color: #764ba2;
    }

    .notification-empty {
        padding: 40px 20px;
        text-align: center;
        color: #adb5bd;
    }

    .notification-empty i {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.3;
    }

    .notification-bell {
        position: relative;
        font-size: 20px;
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .notification-bell:hover {
        color: #667eea;
    }

    .notification-bell.has-notif {
        animation: ring 2s ease-in-out infinite;
    }

    @keyframes ring {
        0%, 100% { transform: rotate(0deg); }
        10%, 30% { transform: rotate(-10deg); }
        20%, 40% { transform: rotate(10deg); }
    }

    /* Scrollbar custom */
    .notification-dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .notification-dropdown::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notification-dropdown::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    .notification-dropdown::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }
</style>

<!-- Notification Bell Icon & Dropdown -->
<a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
    <i class="dw dw-notification notification-bell <?php echo $notif_count > 0 ? 'has-notif' : ''; ?>"></i>
    <?php if ($notif_count > 0): ?>
        <span class="notification-badge"><?php echo $notif_count > 99 ? '99+' : $notif_count; ?></span>
    <?php endif; ?>
</a>

<div class="dropdown-menu dropdown-menu-right notification-dropdown">
    <div class="notification-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>🔔 Notifikasi</strong>
            </div>
            <div>
                <span class="badge badge-light"><?php echo $notif_count; ?> Baru</span>
            </div>
        </div>
    </div>
    
    <?php if ($result_notif && mysqli_num_rows($result_notif) > 0): ?>
        <?php while ($notif = mysqli_fetch_assoc($result_notif)): 
            // Time ago calculation
            $time_ago = '';
            $timestamp = strtotime($notif['tanggal_lapor']);
            $diff = time() - $timestamp;
            
            if ($diff < 60) {
                $time_ago = 'Baru saja';
            } elseif ($diff < 3600) {
                $time_ago = floor($diff / 60) . ' menit lalu';
            } elseif ($diff < 86400) {
                $time_ago = floor($diff / 3600) . ' jam lalu';
            } else {
                $days = floor($diff / 86400);
                $time_ago = $days . ' hari lalu';
            }
            
            // Get pelapor name
            $nama_pelapor = 'Anonim';
            if (!empty($notif['nama_pelapor'])) {
                $nama_pelapor = $notif['nama_pelapor'];
            } elseif (!empty($notif['nama'])) {
                $nama_pelapor = $notif['nama'];
            }
        ?>
            <div class="notification-item" onclick="window.location.href='dash.php?page=detail-pengaduan&id=<?php echo $notif['id_laporan']; ?>'">
                <div class="d-flex">
                    <div class="notification-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <i class="dw dw-file"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">Laporan Baru</div>
                        <div class="notification-text">
                            <?php echo htmlspecialchars(substr($notif['judul_laporan'], 0, 60)); ?><?php echo strlen($notif['judul_laporan']) > 60 ? '...' : ''; ?>
                        </div>
                        <div class="notification-time">
                            <i class="dw dw-user1"></i> <?php echo htmlspecialchars($nama_pelapor); ?> • 
                            <i class="dw dw-clock"></i> <?php echo $time_ago; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        
        <div class="notification-footer">
            <a href="dash.php?page=lihat-pengaduan">
                Lihat Semua Pengaduan →
            </a>
        </div>
    <?php else: ?>
        <div class="notification-empty">
            <i class="dw dw-notification"></i>
            <div class="font-weight-600 mb-1">Tidak Ada Notifikasi</div>
            <div class="small text-muted">Semua laporan sudah ditindaklanjuti</div>
        </div>
    <?php endif; ?>
</div>

<!-- Auto refresh notification every 30 seconds -->
<script>
    // Auto refresh notification badge
    function refreshNotificationCount() {
        fetch('ajax/get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                const bell = document.querySelector('.notification-bell');
                
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        // Create badge if doesn't exist
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.count > 99 ? '99+' : data.count;
                        bell.parentElement.appendChild(newBadge);
                    }
                    
                    if (bell) {
                        bell.classList.add('has-notif');
                    }
                } else {
                    if (badge) {
                        badge.style.display = 'none';
                    }
                    if (bell) {
                        bell.classList.remove('has-notif');
                    }
                }
            })
            .catch(error => console.log('Error fetching notifications:', error));
    }
    
    // Refresh every 30 seconds
    setInterval(refreshNotificationCount, 30000);
</script>