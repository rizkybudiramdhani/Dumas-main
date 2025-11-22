<?php
// Session & Auth Check
require_once 'config/koneksi.php';
require_once 'config/gate.php';

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get user data from session
$id_tim = isset($_SESSION['id_tim']) ? $_SESSION['id_tim'] : 0;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html>

<?php include 'views_a/header.php' ?>

<body>

    <?php include 'views_a/navbar.php' ?>

    <?php include 'views_a/sidebar.php' ?>

    <div class="mobile-menu-overlay"></div>

    <?php include 'extension/settingdash.php' ?>

    <div class="main-container">
        <div class="xs-pd-20-10 pd-ltr-20">

            <?php
            // Routing system untuk semua halaman
            switch ($page) {
                    // ============================================
                    // DASHBOARD
                    // ============================================
                case 'dashboard':
                    include 'content_a/dashboard.php';
                    break;

                    // ============================================
                    // PENGUNGKAPAN (Ditresnarkoba only)
                    // ============================================
                case 'input-pengungkapan':
                    if ($role == 'Ditresnarkoba') {
                        include 'content/input_pengungkapan.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.</div>';
                    }
                    break;

                    // ============================================
                    // PENGADUAN MASYARAKAT (Semua Role)
                    // ============================================
                case 'input-pengaduan':
                    include 'content_a/tambah_aduan.php';
                    break;

                case 'lihat-pengaduan':
                    include 'content_a/lihat_aduan.php';
                    break;

                case 'detail-pengaduan':
                    include 'content_a/detail_aduan.php';
                    break;

                    // ============================================
                    // BERITA (Semua Role)
                    // ============================================
                case 'input-berita':
                    include 'content_a/tambah_berita.php';
                    break;

                case 'lihat-berita':
                    include 'content_a/lihat_berita.php';
                    break;

                    // ============================================
                    // LAPORAN Ditsamapta (Semua Role Bisa Akses)
                    // ============================================
                case 'laporan-Ditsamapta':
                    // Semua role bisa lihat laporan Ditsamapta
                    if (in_array($role, ['Ditsamapta', 'Ditbinmas', 'Ditresnarkoba'])) {
                        include 'content/laporan_Ditsamapta.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.</div>';
                    }
                    break;

                case 'input-laporan-Ditsamapta':
                    if ($role == 'Ditsamapta') {
                        include 'content/input_laporan_Ditsamapta.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Hanya Ditsamapta yang dapat menginput laporan.</div>';
                    }
                    break;

                    // ============================================
                    // LAPORAN Ditbinmas (Semua Role Bisa Akses)
                    // ============================================
                case 'laporan-Ditbinmas':
                    // Semua role bisa lihat laporan Ditbinmas
                    if (in_array($role, ['Ditbinmas', 'Ditsamapta', 'Ditresnarkoba'])) {
                        include 'content/laporan_Ditbinmas.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.</div>';
                    }
                    break;

                    // ============================================
                    // INPUT KEGIATAN Ditbinmas
                    // ============================================
                case 'input-kegiatan':
                    if ($role == 'Ditbinmas') {
                        include 'content/input_kegiatan.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Hanya Ditbinmas yang dapat menginput kegiatan.</div>';
                    }
                    break;

                    // ============================================
                    // LAPORAN Ditresnarkoba (Semua Role Bisa Akses)
                    // ============================================
                case 'laporan-Ditresnarkoba':
                    // Semua role bisa lihat laporan Ditresnarkoba
                    if (in_array($role, ['Ditresnarkoba', 'Ditsamapta', 'Ditbinmas'])) {
                        include 'content/laporan_Ditresnarkoba.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.</div>';
                    }
                    break;

                case 'input-laporan-Ditresnarkoba':
                    if ($role == 'Ditresnarkoba') {
                        include 'content/input_laporan_Ditresnarkoba.php';
                    } else {
                        echo '<div class="alert alert-danger">Akses ditolak! Hanya Ditresnarkoba yang dapat menginput laporan.</div>';
                    }
                    break;

                    // ============================================
                    // PROFILE (Semua Role)
                    // ============================================
                case 'profile':
                    include 'content/profile.php';
                    break;

                    // ============================================
                    // 404 - PAGE NOT FOUND
                    // ============================================
                default:
                    ?>
                    <div class="min-height-200px">
                        <div class="error-page d-flex align-items-center flex-wrap justify-content-center pd-20">
                            <div class="pd-10">
                                <div class="error-page-wrap text-center">
                                    <h1>404</h1>
                                    <h3>Halaman Tidak Ditemukan!</h3>
                                    <p>Maaf, halaman yang Anda cari tidak tersedia.<br>Silakan kembali ke halaman utama.</p>
                                    <div class="pt-20">
                                        <a class="btn btn-primary btn-lg" href="dash.php">Kembali ke Dashboard</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                    break;
            }
            ?>

        </div>
    </div>

    <!-- js -->
    <script src="vendors/scripts/core.js"></script>
    <script src="vendors/scripts/script.min.js"></script>
    <script src="vendors/scripts/process.js"></script>
    <script src="vendors/scripts/layout-settings.js"></script>

    <!-- ApexCharts for Dashboard (only load on dashboard page) -->
    <?php if ($page == 'dashboard'): ?>
        <script src="src/plugins/apexcharts/apexcharts.min.js"></script>
    <?php endif; ?>

    <!-- DataTables JS (untuk tabel) -->
    <?php if (in_array($page, ['dashboard', 'lihat-pengaduan', 'lihat-berita', 'laporan-Ditsamapta', 'laporan-Ditbinmas', 'laporan-Ditresnarkoba'])): ?>
        <script src="src/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
        <script src="src/plugins/datatables/js/dataTables.responsive.min.js"></script>
        <script src="src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
    <?php endif; ?>

</body>

</html>