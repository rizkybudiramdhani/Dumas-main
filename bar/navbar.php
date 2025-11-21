<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg navbar-dark px-lg-5 fixed-top">
        <a href="index.php" class="navbar-brand d-flex align-items-center ms-4 ms-lg-0">
            <img src="imgg/trisula.png" alt="Trisula" style="height: 40px; width: auto; margin-right: 10px;" class="align-self-center">

            <h2 class="mb-0 text-primary text-uppercase" style="margin-top: 12px;">
                Trisula
            </h2>

        </a>


        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav mx-auto p-4 p-lg-0">

            </div>

            <div class="d-none d-lg-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-white me-3">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['nama']); ?>
                    </span>

                    <!-- Notification Bell -->
                    <div class="dropdown me-3">
                        <button class="btn btn-notification position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill"></i>
                            <?php
                            // Check if database connection exists
                            if (isset($db) && $db):
                                $user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';
                                $unread_count = 0;

                                // Untuk user biasa - notifikasi balasan
                                if (strpos($user_role, 'ditsamapta') === false && strpos($user_role, 'ditbinmas') === false && strpos($user_role, 'ditresnarkoba') === false):
                                    $user_id = $_SESSION['user_id'];
                                    $query_count = "SELECT COUNT(*) as total FROM tabel_laporan
                                                  WHERE user_id = ? AND status IN ('Diproses', 'Selesai')
                                                  AND balasan IS NOT NULL AND balasan != ''
                                                  AND is_read = 0";
                                    $stmt_count = mysqli_prepare($db, $query_count);
                                    mysqli_stmt_bind_param($stmt_count, "i", $user_id);
                                    mysqli_stmt_execute($stmt_count);
                                    $result_count = mysqli_stmt_get_result($stmt_count);
                                    $unread_count = mysqli_fetch_assoc($result_count)['total'];

                                // Untuk Ditsamapta - notifikasi laporan yang sudah diproses Ditresnarkoba
                                elseif (strpos($user_role, 'ditsamapta') !== false):
                                    $query_count = "SELECT COUNT(*) as total FROM tabel_laporan
                                                  WHERE status_laporan = 'diproses_ditresnarkoba'
                                                  AND is_notif_ditsamapta = 1";
                                    $result_count = mysqli_query($db, $query_count);
                                    $unread_count = mysqli_fetch_assoc($result_count)['total'];

                                // Untuk Ditbinmas - notifikasi laporan yang sudah diproses Ditresnarkoba
                                elseif (strpos($user_role, 'ditbinmas') !== false):
                                    $query_count = "SELECT COUNT(*) as total FROM tabel_laporan
                                                  WHERE status_laporan = 'diproses_ditresnarkoba'
                                                  AND is_notif_ditbinmas = 1";
                                    $result_count = mysqli_query($db, $query_count);
                                    $unread_count = mysqli_fetch_assoc($result_count)['total'];
                                endif;

                                if ($unread_count > 0):
                            ?>
                                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                            <?php
                                endif;
                            endif;
                            ?>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                            <?php
                            $user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

                            // Notifikasi untuk Ditsamapta atau Ditbinmas
                            if (strpos($user_role, 'ditsamapta') !== false || strpos($user_role, 'ditbinmas') !== false):
                            ?>
                                <li class="dropdown-header">
                                    <strong><i class="bi bi-exclamation-circle-fill me-1"></i> Laporan Baru untuk Ditindaklanjuti</strong>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <?php
                                if (isset($db) && $db):
                                    // Query berdasarkan role
                                    if (strpos($user_role, 'ditsamapta') !== false):
                                        $query_notif = "SELECT id_laporan, judul_laporan, lokasi, tanggal_lapor, tanggal_mulai_diproses
                                                      FROM tabel_laporan
                                                      WHERE status_laporan = 'diproses_ditresnarkoba'
                                                      AND is_notif_ditsamapta = 1
                                                      ORDER BY tanggal_mulai_diproses DESC LIMIT 5";
                                    else:
                                        $query_notif = "SELECT id_laporan, judul_laporan, lokasi, tanggal_lapor, tanggal_mulai_diproses
                                                      FROM tabel_laporan
                                                      WHERE status_laporan = 'diproses_ditresnarkoba'
                                                      AND is_notif_ditbinmas = 1
                                                      ORDER BY tanggal_mulai_diproses DESC LIMIT 5";
                                    endif;

                                    $result_notif = mysqli_query($db, $query_notif);

                                    if (mysqli_num_rows($result_notif) > 0):
                                        while ($notif = mysqli_fetch_assoc($result_notif)):
                                ?>
                                            <li>
                                                <a class="dropdown-item notification-item" href="content/detail_pengaduan.php?id=<?php echo $notif['id_laporan']; ?>">
                                                    <div class="d-flex align-items-start">
                                                        <div class="notification-icon" style="background: #FFD700; color: #1E40AF;">
                                                            <i class="bi bi-megaphone-fill"></i>
                                                        </div>
                                                        <div class="notification-content">
                                                            <div class="notification-title"><?php echo htmlspecialchars(substr($notif['judul_laporan'], 0, 40)); ?>...</div>
                                                            <div class="notification-text">
                                                                <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars(substr($notif['lokasi'], 0, 30)); ?>
                                                            </div>
                                                            <div class="notification-time">
                                                                <i class="bi bi-clock"></i>
                                                                <?php
                                                                if ($notif['tanggal_mulai_diproses']) {
                                                                    echo date('d M Y, H:i', strtotime($notif['tanggal_mulai_diproses']));
                                                                } else {
                                                                    echo date('d M Y, H:i', strtotime($notif['tanggal_lapor']));
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <li class="dropdown-item text-center text-muted py-3">
                                            <i class="bi bi-inbox"></i><br>
                                            Belum ada laporan baru
                                        </li>
                                <?php
                                    endif;
                                endif;
                                ?>
                            <?php
                            // Notifikasi untuk user biasa
                            else:
                            ?>
                                <li class="dropdown-header">
                                    <strong>Notifikasi Balasan</strong>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <?php
                                if (isset($db) && $db):
                                    $user_id = $_SESSION['user_id'];
                                    $query_notif = "SELECT id_laporan, judul_laporan, balasan, status, tanggal_balasan
                                                  FROM tabel_laporan
                                                  WHERE user_id = ? AND status IN ('Diproses', 'Selesai')
                                                  AND balasan IS NOT NULL AND balasan != ''
                                                  ORDER BY tanggal_balasan DESC LIMIT 5";
                                    $stmt_notif = mysqli_prepare($db, $query_notif);
                                    mysqli_stmt_bind_param($stmt_notif, "i", $user_id);
                                    mysqli_stmt_execute($stmt_notif);
                                    $result_notif = mysqli_stmt_get_result($stmt_notif);

                                    if (mysqli_num_rows($result_notif) > 0):
                                        while ($notif = mysqli_fetch_assoc($result_notif)):
                                ?>
                                            <li>
                                                <a class="dropdown-item notification-item" href="dash-user.php?page=detail-laporan&id=<?php echo $notif['id_laporan']; ?>">
                                                    <div class="d-flex align-items-start">
                                                        <div class="notification-icon">
                                                            <i class="bi bi-chat-left-text-fill"></i>
                                                        </div>
                                                        <div class="notification-content">
                                                            <div class="notification-title"><?php echo htmlspecialchars(substr($notif['judul_laporan'], 0, 40)); ?>...</div>
                                                            <div class="notification-text"><?php echo htmlspecialchars(substr($notif['balasan'], 0, 60)); ?>...</div>
                                                            <div class="notification-time">
                                                                <i class="bi bi-clock"></i>
                                                                <?php
                                                                if ($notif['tanggal_balasan']) {
                                                                    echo date('d M Y, H:i', strtotime($notif['tanggal_balasan']));
                                                                } else {
                                                                    echo 'Baru saja';
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <li class="dropdown-item text-center text-muted py-3">
                                            <i class="bi bi-inbox"></i><br>
                                            Belum ada balasan
                                        </li>
                                <?php
                                    endif;
                                endif;
                                ?>
                            <?php endif; ?>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-center text-primary" href="#" id="btnLihatSemuaLaporan">
                                    <strong>Lihat Semua Laporan</strong>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a href="logout.php" class="btn btn-outline-light py-2 px-4 me-3">
                        <i class="bi bi-box-arrow-right me-1"></i> LOGOUT
                    </a>
                <?php else: ?>
                    
                <?php endif; ?>
                
                <a class="btn btn-sm-square btn-outline-primary border-2 ms-1" href="https://twitter.com" target="_blank" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a class="btn btn-sm-square btn-outline-primary border-2 ms-1" href="https://facebook.com" target="_blank" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a class="btn btn-sm-square btn-outline-primary border-2 ms-1" href="https://instagram.com" target="_blank" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a class="btn btn-sm-square btn-outline-primary border-2 ms-1" href="https://youtube.com" target="_blank" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </nav>
</div>

<style>
    /* Notification Button Styling */
    .btn-notification {
        background: transparent;
        border: 2px solid white;
        color: white;
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

    .btn-notification:hover {
        background: white;
        color: #0d6efd;
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        border: 2px solid #0d47a1;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    /* Notification Dropdown */
    .notification-dropdown {
        min-width: 380px;
        max-width: 400px;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        margin-top: 10px;
    }

    .notification-dropdown .dropdown-header {
        background: #0d6efd;
        color: white;
        font-size: 1rem;
        padding: 12px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px 10px 0 0;
    }

    .notification-item {
        padding: 12px 15px;
        transition: all 0.3s ease;
        border-bottom: 1px solid #e9ecef;
    }

    .notification-item:hover {
        background: rgba(13, 110, 253, 0.05);
        border-left: 3px solid #0d6efd;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        background: #0d6efd;
        color: white;
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

    .notification-time {
        color: #0d6efd;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .notification-time i {
        margin-right: 3px;
    }

    .notification-dropdown .dropdown-divider {
        margin: 0;
        border-color: rgba(0, 0, 0, 0.1);
    }

    .notification-dropdown .text-primary {
        color: #0d6efd !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .notification-dropdown .text-primary:hover {
        background: rgba(13, 110, 253, 0.1);
    }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .notification-dropdown {
            min-width: 320px;
            max-width: 350px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const btnLapor = document.querySelector('.btn-lapor-nav');
        if (btnLapor) {
            btnLapor.addEventListener('click', function(e) {
                e.preventDefault();

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(document.getElementById('modalLaporan'));
                    modal.show();
                } else {
                    console.error('Bootstrap JS Modal not loaded.');
                }
            });
        }


        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {

                    const navbarHeight = document.querySelector('.navbar.fixed-top').offsetHeight;
                    const offsetPosition = target.getBoundingClientRect().top + window.scrollY - navbarHeight;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Modal Lihat Semua Laporan
        const btnLihatSemuaLaporan = document.getElementById('btnLihatSemuaLaporan');
        if (btnLihatSemuaLaporan) {
            btnLihatSemuaLaporan.addEventListener('click', function(e) {
                e.preventDefault();
                loadSemuaLaporan();
            });
        }
    });

    function loadSemuaLaporan() {
        // Show modal with loading state
        const modalElement = document.getElementById('modalSemuaLaporan');
        const modal = new bootstrap.Modal(modalElement);

        // Show loading
        document.getElementById('laporanContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Memuat laporan...</p>
            </div>
        `;

        modal.show();

        // Fetch data laporan
        fetch('get_all_laporan.php')
            .then(response => response.json())
            .then(data => {
                displayLaporanData(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('laporanContent').innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Gagal memuat laporan</p>
                    </div>
                `;
            });
    }

    function displayLaporanData(data) {
        const content = document.getElementById('laporanContent');

        if (!data || data.length === 0) {
            content.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                    <p class="mt-3 text-muted">Belum ada laporan</p>
                </div>
            `;
            return;
        }

        let html = '<div class="laporan-list">';

        data.forEach((laporan, index) => {
            const statusColor = getStatusColor(laporan.status);
            const statusIcon = getStatusIcon(laporan.status);

            html += `
                <div class="laporan-card" style="animation-delay: ${index * 0.05}s">
                    <div class="laporan-header">
                        <div>
                            <h5 class="laporan-title">${escapeHtml(laporan.judul_laporan)}</h5>
                            <p class="laporan-date">
                                <i class="bi bi-calendar3"></i>
                                ${formatDate(laporan.tanggal_laporan)}
                            </p>
                        </div>
                        <div>
                            <span class="status-badge" style="background-color: ${statusColor};">
                                <i class="bi ${statusIcon}"></i>
                                ${escapeHtml(laporan.status)}
                            </span>
                        </div>
                    </div>
                    <div class="laporan-body">
                        <p class="laporan-isi">${escapeHtml(laporan.isi_laporan.substring(0, 150))}${laporan.isi_laporan.length > 150 ? '...' : ''}</p>
                        ${laporan.balasan ? `
                            <div class="laporan-balasan">
                                <div class="balasan-header">
                                    <i class="bi bi-chat-left-text-fill"></i>
                                    <strong>Balasan Admin:</strong>
                                </div>
                                <p class="balasan-text">${escapeHtml(laporan.balasan)}</p>
                                <small class="balasan-date">
                                    <i class="bi bi-clock"></i>
                                    ${formatDate(laporan.tanggal_balasan)}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                    <div class="laporan-footer">
                        <a href="dash-user.php?page=detail-laporan&id=${laporan.id_laporan}" class="btn-detail">
                            <i class="bi bi-eye-fill"></i>
                            Lihat Detail
                        </a>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        content.innerHTML = html;
    }

    function getStatusColor(status) {
        switch (status) {
            case 'Menunggu':
                return '#FFD700';
            case 'Diproses':
                return '#0d6efd';
            case 'Selesai':
                return '#28a745';
            case 'Ditolak':
                return '#dc3545';
            default:
                return '#6c757d';
        }
    }

    function getStatusIcon(status) {
        switch (status) {
            case 'Menunggu':
                return 'bi-hourglass-split';
            case 'Diproses':
                return 'bi-gear-fill';
            case 'Selesai':
                return 'bi-check-circle-fill';
            case 'Ditolak':
                return 'bi-x-circle-fill';
            default:
                return 'bi-circle-fill';
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('id-ID', options);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<!-- Modal Semua Laporan -->
<div class="modal fade" id="modalSemuaLaporan" tabindex="-1" aria-labelledby="modalSemuaLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSemuaLaporanLabel">
                    <i class="bi bi-file-text-fill me-2"></i>
                    Semua Laporan Saya
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="laporanContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Semua Laporan Styling */
    #modalSemuaLaporan .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(26, 31, 58, 0.2);
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    #modalSemuaLaporan .modal-header {
        background-color: #1a1f3a;
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
        border-bottom: 3px solid #FFD700;
    }

    #modalSemuaLaporan .modal-title {
        font-weight: 700;
        font-size: 1.3rem;
    }

    #modalSemuaLaporan .btn-close {
        filter: brightness(0) invert(1);
        opacity: 1;
    }

    #modalSemuaLaporan .btn-close:hover {
        transform: rotate(90deg);
        transition: transform 0.3s ease;
    }

    #modalSemuaLaporan .modal-body {
        padding: 2rem;
        background-color: #f8f9fa;
        max-height: 70vh;
    }

    /* Laporan Cards */
    .laporan-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .laporan-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(26, 31, 58, 0.1);
        transition: all 0.3s ease;
        animation: slideInUp 0.4s ease-out forwards;
        opacity: 0;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .laporan-card:hover {
        box-shadow: 0 5px 20px rgba(26, 31, 58, 0.15);
        transform: translateY(-3px);
    }

    .laporan-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1.25rem;
        background-color: #1a1f3a;
        color: white;
    }

    .laporan-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
    }

    .laporan-date {
        margin: 0.5rem 0 0 0;
        font-size: 0.85rem;
        color: #FFD700;
    }

    .laporan-date i {
        margin-right: 5px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        color: white;
    }

    .status-badge i {
        font-size: 1rem;
    }

    .laporan-body {
        padding: 1.25rem;
    }

    .laporan-isi {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .laporan-balasan {
        background-color: #e7f3ff;
        border-left: 4px solid #0d6efd;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .balasan-header {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0d6efd;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .balasan-header i {
        font-size: 1.1rem;
    }

    .balasan-text {
        color: #212529;
        margin: 0.5rem 0;
        line-height: 1.5;
    }

    .balasan-date {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .balasan-date i {
        margin-right: 3px;
    }

    .laporan-footer {
        padding: 1rem 1.25rem;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        text-align: right;
    }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #1a1f3a;
        color: white;
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid #1a1f3a;
    }

    .btn-detail:hover {
        background-color: white;
        color: #1a1f3a;
        transform: translateX(5px);
    }

    .btn-detail i {
        font-size: 1.1rem;
    }

    /* Loading & Empty State */
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #modalSemuaLaporan .modal-body {
            padding: 1rem;
        }

        .laporan-header {
            flex-direction: column;
            gap: 1rem;
        }

        .status-badge {
            align-self: flex-start;
        }

        .laporan-title {
            font-size: 1rem;
        }
    }
</style>