<?php
// Include database connection
require_once __DIR__ . '/../config/koneksi.php';
?>
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
                <?php if (isset($_SESSION['Id_akun'])): ?>
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
                                // Untuk user biasa - notifikasi laporan dengan status update
                                if (strpos($user_role, 'Ditsamapta') === false && strpos($user_role, 'Ditbinmas') === false && strpos($user_role, 'Ditresnarkoba') === false):
                                    $user_id = $_SESSION['Id_akun'];
                                    $query_count = "SELECT COUNT(*) as total FROM lapmas
                                                    WHERE Id_akun = ?
                                                    AND status IN ( 'Diproses Ditresnarkoba', 'Selesai', 'Ditolak')";
                                    $stmt_count = mysqli_prepare($db, $query_count);
                                    mysqli_stmt_bind_param($stmt_count, "i", $user_id);
                                    mysqli_stmt_execute($stmt_count);
                                    $result_count = mysqli_stmt_get_result($stmt_count);
                                    $unread_count = mysqli_fetch_assoc($result_count)['total'];

                                // Untuk Ditsamapta - notifikasi laporan yang sudah diproses Ditresnarkoba
                                elseif (strpos($user_role, 'Ditsamapta') !== false):
                                    $query_count = "SELECT COUNT(*) as total FROM tabel_laporan
                                                WHERE status_laporan = 'diproses_Ditresnarkoba'
                                                AND is_notif_Ditsamapta = 1";
                                    $result_count = mysqli_query($db, $query_count);
                                    $unread_count = mysqli_fetch_assoc($result_count)['total'];

                                // Untuk Ditbinmas - notifikasi laporan yang sudah diproses Ditresnarkoba
                                elseif (strpos($user_role, 'Ditbinmas') !== false):
                                    $query_count = "SELECT COUNT(*) as total FROM tabel_laporan
                                                WHERE status_laporan = 'diproses_Ditresnarkoba'
                                                AND is_notif_Ditbinmas = 1";
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
                            if (strpos($user_role, 'Ditsamapta') !== false || strpos($user_role, 'Ditbinmas') !== false):
                            ?>
                                <li class="dropdown-header">
                                    <strong><i class="bi bi-exclamation-circle-fill me-1"></i> Laporan Baru untuk Ditindaklanjuti</strong>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                
                            <?php
                            // Notifikasi untuk user biasa
                            else:
                            ?>
                                <li class="dropdown-header">
                                    <strong><i class="bi bi-chat-dots-fill me-2"></i>Pesan dan Balasan</strong>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <?php
                                if (isset($db) && $db):
                                    $user_id = $_SESSION['Id_akun'];

                                    // Ambil semua laporan user (dengan status)
                                    $query_notif = "SELECT
                                            l.id_lapmas,
                                            l.judul,
                                            l.desk,
                                            l.lokasi,
                                            l.status,
                                            l.tanggal_lapor
                                        FROM lapmas l
                                        WHERE l.Id_akun = ?
                                        AND (l.status = 'Diproses Ditresnarkoba' 
                                            OR l.status = 'selesai')
                                        ORDER BY l.tanggal_lapor DESC
                                        LIMIT 5
                                    ";

                                    $stmt_notif = mysqli_prepare($db, $query_notif);
                                    mysqli_stmt_bind_param($stmt_notif, "i", $user_id);
                                    mysqli_stmt_execute($stmt_notif);
                                    $result_notif = mysqli_stmt_get_result($stmt_notif);

                                    if (mysqli_num_rows($result_notif) > 0):
                                        while ($notif = mysqli_fetch_assoc($result_notif)):
                                            // Tentukan icon dan warna berdasarkan status
                                            $status = $notif['status'] ?? 'Baru';

                                            switch($status) {
                                                case 'Baru':
                                                    $icon_class = 'bi-file-earmark-plus';
                                                    $icon_bg = 'background: #6c757d;'; // Abu-abu
                                                    $status_text = 'Laporan Baru';
                                                    break;
                                                case 'Waiting':
                                                    $icon_class = 'bi-hourglass-split';
                                                    $icon_bg = 'background: #ffc107;'; // Kuning
                                                    $status_text = 'Waiting (Menunggu Final)';
                                                    break;
                                                case 'Diproses Ditresnarkoba':
                                                    $icon_class = 'bi-gear-fill';
                                                    $icon_bg = 'background: #0d6efd;'; // Biru
                                                    $status_text = 'Diproses Ditresnarkoba';
                                                    break;
                                                case 'Diproses Ditsamapta':
                                                    $icon_class = 'bi-gear-fill';
                                                    $icon_bg = 'background: #0dcaf0;'; // Cyan
                                                    $status_text = 'Diproses Ditsamapta';
                                                    break;
                                                case 'Diproses Ditbinmas':
                                                    $icon_class = 'bi-gear-fill';
                                                    $icon_bg = 'background: #fd7e14;'; // Orange
                                                    $status_text = 'Diproses Ditbinmas';
                                                    break;
                                                case 'Selesai Ditsamapta':
                                                    $icon_class = 'bi-check-circle-fill';
                                                    $icon_bg = 'background: #0dcaf0;'; // Cyan
                                                    $status_text = 'Selesai Ditsamapta';
                                                    break;
                                                case 'Selesai Ditbinmas':
                                                    $icon_class = 'bi-check-circle-fill';
                                                    $icon_bg = 'background: #fd7e14;'; // Orange
                                                    $status_text = 'Selesai Ditbinmas';
                                                    break;
                                                case 'Selesai':
                                                    $icon_class = 'bi-check-circle-fill';
                                                    $icon_bg = 'background: #28a745;'; // Hijau
                                                    $status_text = 'Selesai';
                                                    break;
                                                case 'Ditolak':
                                                    $icon_class = 'bi-x-circle-fill';
                                                    $icon_bg = 'background: #dc3545;'; // Merah
                                                    $status_text = 'Ditolak';
                                                    break;
                                                default:
                                                    $icon_class = 'bi-hourglass-split';
                                                    $icon_bg = 'background: #ffc107;'; // Kuning
                                                    $status_text = 'Menunggu';
                                            }

                                            // Ambil balasan dari Ditresnarkoba dengan status Diproses Ditresnarkoba atau Selesai
                                            $query_respon = "
                                                SELECT r.respon, r.a_respon, r.tanggal_respon, a.Role, l.status
                                                FROM respon r
                                                LEFT JOIN akun a ON r.a_respon = a.Id_akun
                                                LEFT JOIN lapmas l ON r.id_lapmas = l.id_lapmas
                                                WHERE r.id_lapmas = ?
                                                AND a.Role = 'Ditresnarkoba'
                                                AND (l.status = 'Diproses Ditresnarkoba' OR l.status = 'Selesai')
                                                ORDER BY r.tanggal_respon ASC
                                            ";
                                            $stmt_respon = mysqli_prepare($db, $query_respon);
                                            mysqli_stmt_bind_param($stmt_respon, "i", $notif['id_lapmas']);
                                            mysqli_stmt_execute($stmt_respon);
                                            $result_respon = mysqli_stmt_get_result($stmt_respon);

                                            $semua_balasan = [];
                                            while ($respon_row = mysqli_fetch_assoc($result_respon)) {
                                                $semua_balasan[] = $respon_row;
                                            }
                                ?>
                                            <li>
                                                <div class="dropdown-item notification-item-custom">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-start flex-grow-1">
                                                            <div class="notification-icon" style="<?php echo $icon_bg; ?>">
                                                                <i class="bi <?php echo $icon_class; ?>"></i>
                                                            </div>
                                                            <div class="notification-content">
                                                                <div class="notification-title">
                                                                    <?php echo htmlspecialchars(substr($notif['judul'], 0, 30)); ?>
                                                                    <?php if (strlen($notif['judul']) > 30) echo '...'; ?>
                                                                </div>
                                                                <div class="notification-text">
                                                                    <span class="badge" style="background-color: <?php echo str_replace('background: ', '', $icon_bg); ?>; font-size: 0.65rem;">
                                                                        <?php echo $status_text; ?>
                                                                    </span>
                                                                </div>
                                                                <div class="notification-time">
                                                                    <i class="bi bi-calendar3"></i>
                                                                    <?php

                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-detail-lapmas"
                                                                data-id="<?php echo $notif['id_lapmas']; ?>"
                                                                data-judul="<?php echo htmlspecialchars($notif['judul'] ?? '', ENT_QUOTES); ?>"
                                                                data-desk="<?php echo htmlspecialchars($notif['desk'] ?? '', ENT_QUOTES); ?>"
                                                                data-lokasi="<?php echo htmlspecialchars($notif['lokasi'] ?? '-', ENT_QUOTES); ?>"
                                                                data-balasan="<?php echo htmlspecialchars(json_encode($semua_balasan), ENT_QUOTES); ?>"
                                                                data-status="<?php echo htmlspecialchars($status_text ?? 'Baru', ENT_QUOTES); ?>"
                                                                data-warna="<?php echo str_replace('background: ', '', $icon_bg ?? 'background: #6c757d'); ?>"
                                                                data-tanggal="<?php echo date('d M Y, H:i', strtotime($notif['tanggal_lapor'])); ?>">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <li class="dropdown-item text-center text-muted py-3">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                                            Belum ada laporan
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

    .notification-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 700;
        color: white;
        margin: 5px 0;
    }

    .notification-status-badge i {
        font-size: 0.85rem;
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
        // Event listener untuk semua tombol detail lapmas
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-detail-lapmas')) {
                e.preventDefault();
                e.stopPropagation();

                const btn = e.target.closest('.btn-detail-lapmas');

                // Ambil data dari button
                const judul = btn.getAttribute('data-judul') || '';
                const desk = btn.getAttribute('data-desk') || '';
                const lokasi = btn.getAttribute('data-lokasi') || '';
                const balasan = btn.getAttribute('data-balasan') || '';
                const status = btn.getAttribute('data-status') || 'Baru';
                const warna = btn.getAttribute('data-warna') || '#6c757d';
                const tanggal = btn.getAttribute('data-tanggal') || '';

                // Set data ke modal (htmlspecialchars sudah di-decode otomatis oleh browser)
                const elJudul = document.getElementById('detailJudul');
                const elDesk = document.getElementById('detailDesk');
                const elLokasi = document.getElementById('detailLokasi');
                const elTanggal = document.getElementById('detailTanggal');
                const elStatus = document.getElementById('detailStatus');

                if (elJudul) elJudul.textContent = judul || '-';
                if (elDesk) elDesk.textContent = desk || '-';
                if (elLokasi) elLokasi.textContent = lokasi || '-';
                if (elTanggal) elTanggal.textContent = tanggal || '-';
                if (elStatus) elStatus.textContent = status || 'Baru';

                // Set warna status badge (remove semicolon if exists)
                const statusBadge = document.getElementById('detailStatusBadge');
                const cleanWarna = (warna || '#6c757d').replace(';', '');
                if (statusBadge) statusBadge.style.backgroundColor = cleanWarna;

                // Tampilkan balasan jika ada
                const sectionBalasan = document.getElementById('sectionBalasan');
                const detailBalasan = document.getElementById('detailBalasan');

                try {
                    const balasanArray = JSON.parse(balasan);

                    if (balasanArray && Array.isArray(balasanArray) && balasanArray.length > 0) {
                        sectionBalasan.style.display = 'block';

                        // Data sudah difilter dari database (hanya Ditresnarkoba dengan status tertentu)
                        let balasanHTML = '';
                        balasanArray.forEach((item, index) => {
                            const timBadge = '<span class="badge bg-primary me-2">Ditresnarkoba</span>';

                            const tanggalBalasan = item.tanggal_respon ? new Date(item.tanggal_respon).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : '';

                            balasanHTML += `
                                <div class="balasan-item ${index > 0 ? 'mt-3' : ''}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        ${timBadge}
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> ${tanggalBalasan}
                                        </small>
                                    </div>
                                    <div class="balasan-text-content">
                                        ${item.respon || '-'}
                                    </div>
                                </div>
                            `;
                        });

                        detailBalasan.innerHTML = balasanHTML;
                    } else {
                        sectionBalasan.style.display = 'none';
                    }
                } catch (e) {
                    // Jika bukan JSON array, tampilkan sebagai text biasa (fallback)
                    if (balasan && balasan.trim() !== '' && balasan !== 'null' && balasan !== 'NULL') {
                        sectionBalasan.style.display = 'block';
                        detailBalasan.textContent = balasan;
                    } else {
                        sectionBalasan.style.display = 'none';
                    }
                }

                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('modalDetailLapmas'));
                modal.show();
            }
        });

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
                const href = this.getAttribute('href');

                // Skip if href is just "#" or empty
                if (!href || href === '#') {
                    return;
                }

                e.preventDefault();
                const target = document.querySelector(href);
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

        // Manual close button handlers untuk memastikan modal bisa ditutup
        const modalSemuaLaporan = document.getElementById('modalSemuaLaporan');
        const modalDetailLapmas = document.getElementById('modalDetailLapmas');

        // Close button untuk Modal Semua Laporan
        if (modalSemuaLaporan) {
            const closeBtn = modalSemuaLaporan.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Hapus class show dan backdrop
                    modalSemuaLaporan.classList.remove('show');
                    modalSemuaLaporan.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    // Hapus backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                });
            }
        }

        // Close button untuk Modal Detail Lapmas
        if (modalDetailLapmas) {
            const closeBtn = modalDetailLapmas.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Hapus class show dan backdrop
                    modalDetailLapmas.classList.remove('show');
                    modalDetailLapmas.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    // Hapus backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                });
            }
        }

        // Close modal saat klik backdrop (area di luar modal)
        if (modalSemuaLaporan) {
            modalSemuaLaporan.addEventListener('click', function(e) {
                if (e.target === modalSemuaLaporan) {
                    modalSemuaLaporan.classList.remove('show');
                    modalSemuaLaporan.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            });
        }

        if (modalDetailLapmas) {
            modalDetailLapmas.addEventListener('click', function(e) {
                if (e.target === modalDetailLapmas) {
                    modalDetailLapmas.classList.remove('show');
                    modalDetailLapmas.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
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

        // Fetch data lapmas
        fetch('get_all_lapmas.php')
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
            const statusInfo = getStatusInfo(laporan.status);
            const statusColor = statusInfo.color;
            const statusIcon = statusInfo.icon;
            const statusText = statusInfo.text;

            // Prepare balasan data for modal
            const balasanData = laporan.balasan ? JSON.stringify([{
                respon: laporan.balasan,
                tanggal_respon: laporan.tanggal_balasan,
                Role: 'Ditresnarkoba'
            }]) : '[]';

            // Escape data balasan untuk HTML attribute
            const escapedBalasanData = balasanData.replace(/"/g, '&quot;');

            html += `
                <div class="laporan-card" style="animation-delay: ${index * 0.05}s">
                    <div class="laporan-header">
                        <div>
                            <h5 class="laporan-title">${escapeHtml(laporan.judul)}</h5>
                            <p class="laporan-date">
                                <i class="bi bi-calendar3"></i>
                                ${formatDate(laporan.tanggal_lapor)}
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
                        <p class="laporan-isi">${escapeHtml(laporan.desk ? laporan.desk.substring(0, 150) : '')}${laporan.desk && laporan.desk.length > 150 ? '...' : ''}</p>
                        ${laporan.lokasi ? `
                            <p class="laporan-lokasi">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <strong>Lokasi:</strong> ${escapeHtml(laporan.lokasi)}
                            </p>
                        ` : ''}
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
                        <button type="button" class="btn-detail btn-detail-lapmas"
                                data-id="${laporan.id_lapmas}"
                                data-judul="${escapeHtml(laporan.judul || '')}"
                                data-desk="${escapeHtml(laporan.desk || '')}"
                                data-lokasi="${escapeHtml(laporan.lokasi || '-')}"
                                data-balasan="${escapedBalasanData}"
                                data-status="${escapeHtml(statusText)}"
                                data-warna="${statusColor}"
                                data-tanggal="${formatDate(laporan.tanggal_lapor)}">
                            <i class="bi bi-eye-fill"></i>
                            Lihat Detail
                        </button>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        content.innerHTML = html;
    }

    function getStatusInfo(status) {
        switch (status) {
            case 'Baru':
                return {
                    color: '#6c757d',
                    icon: 'bi-file-earmark-plus',
                    text: 'Laporan Baru'
                };
            case 'Waiting':
                return {
                    color: '#ffc107',
                    icon: 'bi-hourglass-split',
                    text: 'Waiting (Menunggu Final)'
                };
            case 'Diproses Ditresnarkoba':
                return {
                    color: '#0d6efd',
                    icon: 'bi-gear-fill',
                    text: 'Diproses Ditresnarkoba'
                };
            case 'Diproses Ditsamapta':
                return {
                    color: '#0dcaf0',
                    icon: 'bi-gear-fill',
                    text: 'Diproses Ditsamapta'
                };
            case 'Diproses Ditbinmas':
                return {
                    color: '#fd7e14',
                    icon: 'bi-gear-fill',
                    text: 'Diproses Ditbinmas'
                };
            case 'Selesai Ditsamapta':
                return {
                    color: '#0dcaf0',
                    icon: 'bi-check-circle-fill',
                    text: 'Selesai Ditsamapta'
                };
            case 'Selesai Ditbinmas':
                return {
                    color: '#fd7e14',
                    icon: 'bi-check-circle-fill',
                    text: 'Selesai Ditbinmas'
                };
            case 'Selesai':
                return {
                    color: '#28a745',
                    icon: 'bi-check-circle-fill',
                    text: 'Selesai'
                };
            case 'Ditolak':
                return {
                    color: '#dc3545',
                    icon: 'bi-x-circle-fill',
                    text: 'Ditolak'
                };
            default:
                return {
                    color: '#ffc107',
                    icon: 'bi-hourglass-split',
                    text: 'Menunggu'
                };
        }
    }

    function getStatusColor(status) {
        return getStatusInfo(status).color;
    }

    function getStatusIcon(status) {
        return getStatusInfo(status).icon;
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

<!-- Modal Detail Lapmas -->
<div class="modal fade" id="modalDetailLapmas" tabindex="-1" aria-labelledby="modalDetailLapmasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLapmasLabel">
                    <i class="bi bi-file-text-fill me-2"></i>
                    Detail Laporan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Status Badge -->
                <div class="detail-section text-center">
                    <span class="status-badge-large" id="detailStatusBadge">
                        <i class="bi bi-circle-fill"></i>
                        <span id="detailStatus">Status</span>
                    </span>
                </div>

                <!-- Judul -->
                <div class="detail-section">
                    <div class="detail-label">
                        <i class="bi bi-card-heading"></i>
                        Judul Laporan
                    </div>
                    <div class="detail-value" id="detailJudul">-</div>
                </div>

                <!-- Deskripsi -->
                <div class="detail-section">
                    <div class="detail-label">
                        <i class="bi bi-file-text"></i>
                        Deskripsi
                    </div>
                    <div class="detail-value" id="detailDesk">-</div>
                </div>

                <!-- Lokasi -->
                <div class="detail-section">
                    <div class="detail-label">
                        <i class="bi bi-geo-alt-fill"></i>
                        Lokasi
                    </div>
                    <div class="detail-value" id="detailLokasi">-</div>
                </div>

                <!-- Tanggal -->
                <div class="detail-section">
                    <div class="detail-label">
                        <i class="bi bi-calendar-event"></i>
                        Tanggal Lapor
                    </div>
                    <div class="detail-value" id="detailTanggal">-</div>
                </div>

                <!-- Balasan (jika ada) -->
                <div class="detail-section" id="sectionBalasan" style="display: none;">
                    <div class="detail-label">
                        <i class="bi bi-reply-fill text-success"></i>
                        Balasan
                    </div>
                    <div class="detail-value has-balasan" id="detailBalasan">-</div>
                </div>
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
        position: relative;
        z-index: 1;
    }

    #modalSemuaLaporan .modal-title {
        font-weight: 700;
        font-size: 1.3rem;
    }

    #modalSemuaLaporan .btn-close {
        filter: brightness(0) invert(1);
        opacity: 1;
        z-index: 1050;
        position: relative;
        pointer-events: auto;
    }

    #modalSemuaLaporan .btn-close:hover {
        transform: rotate(90deg);
        transition: transform 0.3s ease;
        opacity: 0.8;
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

    .laporan-lokasi {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 1rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .laporan-lokasi i {
        margin-right: 5px;
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
        cursor: pointer;
    }

    .btn-detail:hover {
        background-color: white;
        color: #1a1f3a;
        transform: translateX(5px);
    }

    .btn-detail i {
        font-size: 1.1rem;
    }

    /* Override untuk button di modal Semua Laporan */
    .laporan-footer .btn-detail.btn-detail-lapmas {
        background-color: #1a1f3a;
        color: white;
        padding: 0.6rem 1.5rem;
        font-size: 1rem;
        border-radius: 8px;
        border: 2px solid #1a1f3a;
    }

    .laporan-footer .btn-detail.btn-detail-lapmas:hover {
        background-color: white;
        color: #1a1f3a;
        transform: translateX(5px);
        box-shadow: none;
    }

    .laporan-footer .btn-detail.btn-detail-lapmas i {
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

    /* Style untuk notifikasi dengan button detail */
    .notification-item-custom {
        padding: 10px 15px;
        border-bottom: 1px solid #e9ecef;
        cursor: default;
    }

    .notification-item-custom:hover {
        background: rgba(13, 110, 253, 0.03);
    }

    .btn-detail-lapmas {
        background: #0d6efd;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-detail-lapmas:hover {
        background: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
    }

    .btn-detail-lapmas i {
        font-size: 0.85rem;
    }

    /* Modal Detail Lapmas */
    #modalDetailLapmas .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    #modalDetailLapmas .modal-header {
        background: #1a1f3a;
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
        border-bottom: 3px solid #FFD700;
        position: relative;
        z-index: 1;
    }

    #modalDetailLapmas .modal-title {
        font-weight: 700;
        font-size: 1.3rem;
    }

    #modalDetailLapmas .btn-close {
        filter: brightness(0) invert(1);
        z-index: 1050;
        position: relative;
        pointer-events: auto;
        opacity: 1;
    }

    #modalDetailLapmas .btn-close:hover {
        opacity: 0.8;
        transform: rotate(90deg);
        transition: transform 0.3s ease;
    }

    #modalDetailLapmas .detail-section {
        margin-bottom: 1.5rem;
    }

    #modalDetailLapmas .detail-label {
        font-weight: 700;
        color: #495057;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #modalDetailLapmas .detail-value {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #0d6efd;
        line-height: 1.6;
        color: #000000;
    }

    #modalDetailLapmas .detail-value.has-balasan {
        background: #f8f9fa;
        border-left-color: #28a745;
        padding: 0.5rem;
    }

    /* Styling untuk setiap balasan item */
    .balasan-item {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .balasan-item .badge {
        font-size: 0.75rem;
        font-weight: 600;
    }

    .balasan-text-content {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 6px;
        line-height: 1.6;
        color: #212529;
        border-left: 3px solid #28a745;
    }

    #modalDetailLapmas .status-badge-large {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.6rem 1.2rem;
        border-radius: 25px;
        font-weight: 700;
        color: white;
        font-size: 0.95rem;
    }
</style>