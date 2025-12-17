<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_laporan = (int)$_GET['id'];

    // Get file path to delete
    $query_file = "SELECT upload FROM lapmas WHERE id_lapmas = ?";
    $stmt_file = mysqli_prepare($db, $query_file);
    mysqli_stmt_bind_param($stmt_file, "i", $id_laporan);
    mysqli_stmt_execute($stmt_file);
    $result_file = mysqli_stmt_get_result($stmt_file);
    $file_data = mysqli_fetch_assoc($result_file);

    // Delete record
    $query_delete = "DELETE FROM lapmas WHERE id_lapmas = ?";
    $stmt_delete = mysqli_prepare($db, $query_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id_laporan);

    if (mysqli_stmt_execute($stmt_delete)) {
        // Delete file if exists
        if ($file_data && !empty($file_data['upload'])) {
            $files = explode(',', $file_data['upload']);
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        echo '<script>alert("Pengaduan berhasil dihapus!"); window.location.href="dash.php?page=dashboard";</script>';
        exit;
    } else {
        echo '<script>alert("Gagal menghapus pengaduan: ' . mysqli_error($db) . '"); window.location.href="dash.php?page=dashboard";</script>';
        exit;
    }
}

// Get statistics from database

// Total laporan dari tabel lapmas
$query_total_pengaduan = "SELECT COUNT(*) as total FROM lapmas";
$result = mysqli_query($db, $query_total_pengaduan);
$total_pengaduan = mysqli_fetch_assoc($result)['total'];

// Total users dari tabel akun (hanya yang role Masyarakat)
$query_total_users = "SELECT COUNT(*) as total FROM akun WHERE Role = 'Masyarakat'";
$result_users = mysqli_query($db, $query_total_users);
$total_users = mysqli_fetch_assoc($result_users)['total'];

// Laporan by status (menggunakan kolom status dari tabel lapmas)
$query_baru = "SELECT COUNT(*) as total FROM lapmas WHERE status = 'Baru'";
$result_baru = mysqli_query($db, $query_baru);
$total_baru = mysqli_fetch_assoc($result_baru)['total'];

// Diproses (gabungan dari semua status 'Diproses')
$query_diproses = "SELECT COUNT(*) as total FROM lapmas WHERE status LIKE '%Diproses%'";
$result_diproses = mysqli_query($db, $query_diproses);
$total_diproses = mysqli_fetch_assoc($result_diproses)['total'];

// Selesai
$query_selesai = "SELECT COUNT(*) as total FROM lapmas WHERE status = 'Selesai'";
$result_selesai = mysqli_query($db, $query_selesai);
$total_selesai = mysqli_fetch_assoc($result_selesai)['total'];

// Data untuk grafik - Laporan per bulan (last 6 months)
$query_chart = "SELECT
    DATE_FORMAT(STR_TO_DATE(tanggal_lapor, '%Y-%m-%d %H:%i:%s'), '%Y-%m') as bulan,
    DATE_FORMAT(STR_TO_DATE(tanggal_lapor, '%Y-%m-%d %H:%i:%s'), '%b %Y') as bulan_text,
    COUNT(*) as jumlah
FROM lapmas
WHERE STR_TO_DATE(tanggal_lapor, '%Y-%m-%d %H:%i:%s') >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(STR_TO_DATE(tanggal_lapor, '%Y-%m-%d %H:%i:%s'), '%Y-%m')
ORDER BY bulan ASC";

$result_chart = mysqli_query($db, $query_chart);

$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($result_chart)) {
    $chart_labels[] = $row['bulan_text'];
    $chart_data[] = (int)$row['jumlah'];
}

// Laporan hari ini
$query_today = "SELECT COUNT(*) as total FROM lapmas WHERE DATE(STR_TO_DATE(tanggal_lapor, '%Y-%m-%d %H:%i:%s')) = CURDATE()";
$result_today = mysqli_query($db, $query_today);
$total_today = mysqli_fetch_assoc($result_today)['total'];

// Role display name
$role_display = ucfirst($role);
if ($role == 'Ditresnarkoba') $role_display = 'Ditresnarkoba';
if ($role == 'Ditsamapta') $role_display = 'Ditsamapta';
if ($role == 'Ditbinmas') $role_display = 'Ditbinmas';

// Welcome message based on time
$hour = date('H');
if ($hour < 12) {
    $greeting = "Selamat Pagi";
} elseif ($hour < 15) {
    $greeting = "Selamat Siang";
} elseif ($hour < 18) {
    $greeting = "Selamat Sore";
} else {
    $greeting = "Selamat Malam";
}


?>

<!-- Custom Styles for Dashboard -->
<style>
    /* Stats Cards Enhancement */
    .stats-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: white;
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0 5px 0;
        color: #1a1f3a;
    }

    .stats-label {
        font-size: 0.9rem;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    /* Welcome Card */
    .welcome-card {
        background: #1a1f3a;
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        border-left: 5px solid #FFD700;
    }

    .welcome-card h2 {
        font-weight: 700;
        margin-bottom: 10px;
        color: #FFD700;
    }

    .welcome-card p {
        color: #ffffff;
        margin-bottom: 0;
    }

    /* Chart Card */
    .chart-card {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }

    /* Table Enhancement */
    .table-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .table-card .card-header {
        background: #1a1f3a;
        color: white;
        border: none;
        padding: 20px;
    }

    .table-card .card-header h4 {
        color: #FFD700;
        font-weight: 700;
    }

    .table-card .card-header p {
        color: #ffffff;
    }

    /* Badge Styles */
    .badge-custom {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Status Badge Styles - Enhanced */
    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    /* Status Baru */
    .status-baru {
        background: #FFD700;
        color: #1a1f3a;
    }

    /* Status Diproses */
    .status-diproses-ditresnarkoba {
        background: #dc3545;
        color: white;
    }

    .status-diproses-ditsamapta {
        background: #1E40AF;
        color: white;
    }

    .status-diproses-ditbinmas {
        background: #28a745;
        color: white;
    }

    /* Status Selesai */
    .status-selesai {
        background: #16a34a;
        color: white;
    }

    .status-selesai-ditresnarkoba {
        background: #b91c1c;
        color: white;
    }

    .status-selesai-ditsamapta {
        background: #1e3a8a;
        color: white;
    }

    .status-selesai-ditbinmas {
        background: #15803d;
        color: white;
    }

    /* Status Lainnya */
    .status-waiting {
        background: #f59e0b;
        color: white;
    }

    .status-ditolak {
        background: #7f1d1d;
        color: white;
    }

    /* Quick Actions */
    .quick-action-btn {
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
        background: white;
    }

    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: #1a1f3a;
        background: #1a1f3a;
    }

    .quick-action-btn:hover .quick-action-icon {
        color: #FFD700;
    }

    .quick-action-btn:hover .text-dark {
        color: #FFD700 !important;
    }

    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #1a1f3a;
        transition: all 0.3s ease;
    }

    /* Progress Bars */
    .progress-custom {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
    }

    .progress-custom .progress-bar {
        border-radius: 10px;
    }

    /* Clickable Row Styling */
    .clickable-row {
        transition: all 0.2s ease;
    }

    .clickable-row:hover {
        background-color: #f8f9fa !important;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .delete-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    /* Feedback Badge Styles */
    .d-flex.gap-2 {
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Modal Styles */
    #modalFeedback .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    #modalFeedback .modal-header {
        border-radius: 15px 15px 0 0;
    }

    #modalFeedback .card {
        transition: all 0.2s ease;
    }

    #modalFeedback .card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        /* Card Header Adjustments */
        .table-card .card-header {
            padding: 15px;
        }

        .table-card .card-header h4 {
            font-size: 1.1rem;
        }

        .table-card .card-header p {
            font-size: 0.8rem;
        }

        /* Welcome Card Mobile */
        .welcome-card {
            padding: 20px;
            text-align: center;
        }

        .welcome-card h2 {
            font-size: 1.3rem;
        }

        /* Stats Card Mobile */
        .stats-number {
            font-size: 2rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            font-size: 25px;
        }

        /* Alert Card Mobile */
        .alert {
            font-size: 0.9rem;
        }

        /* Table Responsive */
        .table-responsive {
            font-size: 0.85rem;
        }

        /* Badge Responsive */
        .badge-custom {
            font-size: 0.7rem !important;
            padding: 5px 10px;
        }

        .status-badge {
            font-size: 0.75rem !important;
            padding: 4px 10px;
        }

        /* Button Adjustments */
        .btn-sm {
            font-size: 0.85rem;
            padding: 8px 12px;
        }
    }

    @media (max-width: 576px) {
        /* Extra small devices */
        .table-card .card-header h4 {
            font-size: 1rem;
        }

        .welcome-card h2 {
            font-size: 1.1rem;
        }

        .stats-number {
            font-size: 1.8rem;
        }

        /* Hide some columns on very small screens */
        #pengaduan-table th:nth-child(3),
        #pengaduan-table td:nth-child(3) {
            display: none;
        }
    }
</style>

<!-- Welcome Card -->
<div class="welcome-card">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($nama); ?>! 👋</h2>
            <p>Selamat datang di Dashboard <?php echo $role_display; ?>. Anda memiliki <?php echo $total_baru; ?> laporan baru yang menunggu untuk ditindaklanjuti.</p>
        </div>
        <div class="col-md-4 text-right">
            <div style="background: #FFD700; padding: 15px; border-radius: 10px; display: inline-block;">
                <div style="font-size: 0.9rem; color: #1a1f3a; font-weight: 600;">Laporan Hari Ini</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: #1a1f3a;"><?php echo $total_today; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row pb-10">

    <!-- Total Laporan -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #1e40af;">
                    <i class="icon-copy dw dw-file"></i>
                </div>
                <div class="stats-number"><?php echo $total_pengaduan; ?></div>
                <div class="stats-label">Total Laporan</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar" role="progressbar" style="width: 100%; background: #1e40af;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Baru -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #dc2626;">
                    <i class="icon-copy dw dw-inbox"></i>
                </div>
                <div class="stats-number"><?php echo $total_baru; ?></div>
                <div class="stats-label">Laporan Baru</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $total_pengaduan > 0 ? ($total_baru / $total_pengaduan * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diproses -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #ea580c;">
                    <i class="icon-copy dw dw-refresh"></i>
                </div>
                <div class="stats-number"><?php echo $total_diproses; ?></div>
                <div class="stats-label">Diproses</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $total_pengaduan > 0 ? ($total_diproses / $total_pengaduan * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selesai -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #16a34a;">
                    <i class="icon-copy dw dw-checked"></i>
                </div>
                <div class="stats-number"><?php echo $total_selesai; ?></div>
                <div class="stats-label">Selesai</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $total_pengaduan > 0 ? ($total_selesai / $total_pengaduan * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Alert Card for Top 5 Critical Areas -->
<?php
// Get top 5 kecamatan with most cases
$query_top5 = "SELECT * FROM kasus ORDER BY `jumlah kasus` DESC LIMIT 5";
$result_top5 = mysqli_query($db, $query_top5);
$top5_areas = [];
while ($top5_row = mysqli_fetch_assoc($result_top5)) {
    $top5_areas[] = $top5_row['kec'];
}
?>

<?php if (count($top5_areas) > 0): ?>
<div class="alert alert-danger mb-30" style="border-radius: 15px; border-left: 5px solid #dc2626; background: #fee2e2; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2);">
    <div class="d-flex align-items-start">
        <div style="font-size: 2rem; margin-right: 15px;">⚠️</div>
        <div style="flex: 1;">
            <h5 style="color: #991b1b; font-weight: 700; margin-bottom: 10px;">
                <i class="icon-copy dw dw-warning"></i> DAERAH PRIORITAS TINGGI - TINDAKAN SEGERA DIPERLUKAN
            </h5>
            <p style="color: #7f1d1d; margin-bottom: 10px; font-weight: 600;">
                Berikut adalah 5 kecamatan dengan jumlah kasus TERBANYAK yang memerlukan perhatian dan tindakan segera dari semua unit:
            </p>
            <div class="row">
                <?php
                mysqli_data_seek($result_top5, 0); // Reset pointer
                $priority_no = 1;
                while ($top5 = mysqli_fetch_assoc($result_top5)):
                ?>
                <div class="col-md-4 mb-2">
                    <div style="background: white; padding: 10px; border-radius: 8px; border-left: 3px solid #dc2626;">
                        <div style="color: #dc2626; font-weight: 700; font-size: 0.75rem;">PRIORITAS #<?php echo $priority_no++; ?></div>
                        <div style="color: #1a1f3a; font-weight: 600;"><?php echo htmlspecialchars($top5['kec']); ?></div>
                        <div style="color: #dc2626; font-size: 0.85rem;">
                            <strong><?php echo $top5['jumlah kasus']; ?></strong> kasus |
                            <strong><?php echo $top5['tersangka']; ?></strong> tersangka
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <small style="color: #7f1d1d; font-style: italic;">
                <i class="icon-copy dw dw-info"></i> Koordinasi dan respon cepat dari Ditbinmas dan Ditsamapta sangat diperlukan untuk daerah-daerah ini.
            </small>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Data Kasus Narkoba Table -->
<?php
// Check for duplicate kecamatan (only for Ditresnarkoba)
$has_duplicates = false;
$duplicate_count = 0;
if ($role == 'Ditresnarkoba') {
    $query_duplicates = "SELECT kec, COUNT(*) as count FROM kasus GROUP BY kec HAVING count > 1";
    $result_duplicates = mysqli_query($db, $query_duplicates);
    $duplicate_count = mysqli_num_rows($result_duplicates);
    $has_duplicates = $duplicate_count > 0;
}
?>

<?php if ($has_duplicates): ?>
<div class="alert alert-warning mb-20" style="border-radius: 15px; border-left: 5px solid #f59e0b; background: #fef3c7; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);">
    <div class="d-flex align-items-start justify-content-between">
        <div style="flex: 1;">
            <h6 style="color: #92400e; font-weight: 700; margin-bottom: 10px;">
                <i class="icon-copy dw dw-warning"></i> Data Duplicate Terdeteksi
            </h6>
            <p style="color: #78350f; margin-bottom: 10px;">
                Ditemukan <strong><?php echo $duplicate_count; ?> kecamatan</strong> dengan data lebih dari satu. Gabungkan data untuk menghindari duplikasi.
            </p>
            <small style="color: #78350f; font-style: italic;">
                <i class="icon-copy dw dw-info"></i> Saat digabung, jumlah kasus dan tersangka akan dijumlahkan otomatis.
            </small>
        </div>
        <button type="button" class="btn btn-warning ml-3" id="btnMergeDuplicates" style="border-radius: 10px; padding: 10px 20px; font-weight: 600; white-space: nowrap;">
            <i class="icon-copy dw dw-merge"></i> Gabung Data Duplicate
        </button>
    </div>
</div>
<?php endif; ?>

<div class="card table-card mb-30">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div class="mb-3 mb-md-0">
                <h4 class="mb-1">🚨 Data Daerah Rawan Narkoba</h4>
                <p class="mb-0 small" style="opacity: 0.9;">Statistik kasus narkoba berdasarkan kecamatan</p>
            </div>
            <?php if ($role == 'Ditresnarkoba'): ?>
            <a href="dash.php?page=input-laporan-Ditresnarkoba" class="btn btn-light btn-sm w-100 w-md-auto">
                <i class="icon-copy dw dw-edit2"></i> Kelola Data
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filterKecKasus" style="font-weight: 600;">Filter Kecamatan:</label>
                    <select class="form-control" id="filterKecKasus">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php
                        // Get unique kecamatan for filter
                        $query_kec_filter = "SELECT DISTINCT kec FROM kasus WHERE kec IS NOT NULL AND kec != '' ORDER BY kec ASC";
                        $result_kec_filter = mysqli_query($db, $query_kec_filter);
                        while ($kec_filter_row = mysqli_fetch_assoc($result_kec_filter)):
                        ?>
                            <option value="<?php echo htmlspecialchars($kec_filter_row['kec']); ?>">
                                <?php echo htmlspecialchars($kec_filter_row['kec']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="sortKasusDashboard" style="font-weight: 600;">Urutkan Berdasarkan:</label>
                    <select class="form-control" id="sortKasusDashboard">
                        <option value="jumlah_desc">Jumlah Kasus (Terbanyak)</option>
                        <option value="jumlah_asc">Jumlah Kasus (Tersedikit)</option>
                        <option value="tersangka_desc">Jumlah Tersangka (Terbanyak)</option>
                        <option value="tersangka_asc">Jumlah Tersangka (Tersedikit)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="itemsPerPageKasus" style="font-weight: 600;">Tampilkan:</label>
                    <select class="form-control" id="itemsPerPageKasus">
                        <option value="5">5 data per halaman</option>
                        <option value="10" selected>10 data per halaman</option>
                        <option value="25">25 data per halaman</option>
                        <option value="all">Semua data</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label style="font-weight: 600;">&nbsp;</label>
                    <button type="button" class="btn btn-secondary btn-block" id="btnResetFilterKasus">
                        <i class="icon-copy dw dw-refresh"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="kasus-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th width="150" class="text-center">Jumlah Kasus</th>
                        <th width="150" class="text-center">Tersangka</th>
                        <th>Kecamatan</th>
                        <th width="200" class="text-center">Respon Unit</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyKasusDashboard">
                    <?php
                    // Get kasus data sorted by jumlah kasus (terbanyak)
                    $query_kasus = "SELECT * FROM kasus ORDER BY `jumlah kasus` DESC";
                    $result_kasus = mysqli_query($db, $query_kasus);

                    $no = 1;
                    if (mysqli_num_rows($result_kasus) > 0):
                        while ($row_kasus = mysqli_fetch_assoc($result_kasus)):
                            // Get feedback count for this kecamatan
                            $kec_name = $row_kasus['kec'];

                            // Check if this kecamatan is in top 5
                            $is_priority = in_array($kec_name, $top5_areas);

                            $query_feedback = "SELECT unit, COUNT(*) as count FROM feedback_kasus WHERE kec = ? GROUP BY unit";
                            $stmt_feedback = mysqli_prepare($db, $query_feedback);
                            mysqli_stmt_bind_param($stmt_feedback, "s", $kec_name);
                            mysqli_stmt_execute($stmt_feedback);
                            $result_feedback = mysqli_stmt_get_result($stmt_feedback);

                            $feedback_ditbinmas = 0;
                            $feedback_ditsamapta = 0;
                            while ($feedback_row = mysqli_fetch_assoc($result_feedback)) {
                                if ($feedback_row['unit'] == 'Ditbinmas') {
                                    $feedback_ditbinmas = $feedback_row['count'];
                                } else if ($feedback_row['unit'] == 'Ditsamapta') {
                                    $feedback_ditsamapta = $feedback_row['count'];
                                }
                            }
                    ?>
                            <tr data-kec="<?php echo htmlspecialchars($row_kasus['kec']); ?>" <?php if ($is_priority): ?>style="background: #fef2f2; border-left: 4px solid #dc2626;"<?php endif; ?>>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-custom" style="font-size: 0.9rem;">
                                        <?php echo $row_kasus['jumlah kasus']; ?> kasus
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary badge-custom" style="font-size: 0.9rem;">
                                        <?php echo $row_kasus['tersangka']; ?> orang
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="font-14 weight-600"><?php echo htmlspecialchars($row_kasus['kec']); ?></div>
                                        <?php if ($is_priority): ?>
                                        <span class="badge badge-danger" style="font-size: 0.7rem; padding: 4px 8px; background: #dc2626;">
                                            <i class="icon-copy dw dw-fire"></i> PRIORITAS
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($feedback_ditbinmas > 0): ?>
                                        <span class="badge badge-success" style="font-size: 0.75rem; padding: 5px 10px;">
                                            <i class="icon-copy dw dw-group"></i> Ditbinmas (<?php echo $feedback_ditbinmas; ?>)
                                        </span>
                                        <?php endif; ?>

                                        <?php if ($feedback_ditsamapta > 0): ?>
                                        <span class="badge badge-warning" style="font-size: 0.75rem; padding: 5px 10px; color: #1a1f3a;">
                                            <i class="icon-copy dw dw-shield"></i> Ditsamapta (<?php echo $feedback_ditsamapta; ?>)
                                        </span>
                                        <?php endif; ?>

                                        <?php if ($feedback_ditbinmas == 0 && $feedback_ditsamapta == 0): ?>
                                        <span class="badge badge-secondary" style="font-size: 0.75rem; padding: 5px 10px;">
                                            <i class="icon-copy dw dw-warning"></i> Belum ada respon
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2" style="gap: 5px;">
                                        <button class="btn btn-sm btn-info view-feedback-btn" data-kec="<?php echo htmlspecialchars($row_kasus['kec']); ?>" style="border-radius: 8px;" title="Lihat Detail">
                                            <i class="icon-copy dw dw-eye"></i>
                                        </button>
                                        <?php if ($role == 'Ditresnarkoba'): ?>
                                        <button class="btn btn-sm btn-warning edit-kasus-btn" data-id="<?php echo $row_kasus['id_kasus']; ?>" data-kec="<?php echo htmlspecialchars($row_kasus['kec']); ?>" data-kasus="<?php echo $row_kasus['jumlah kasus']; ?>" data-tersangka="<?php echo $row_kasus['tersangka']; ?>" style="border-radius: 8px; color: white;" title="Edit Data">
                                            <i class="icon-copy dw dw-edit2"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-kasus-btn" data-id="<?php echo $row_kasus['id_kasus']; ?>" data-kec="<?php echo htmlspecialchars($row_kasus['kec']); ?>" style="border-radius: 8px;" title="Hapus Data">
                                            <i class="icon-copy dw dw-delete-3"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div style="opacity: 0.5;">
                                    <i class="icon-copy dw dw-file" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-0">Belum ada data kasus</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div id="kasusTableInfo" style="padding: 10px; color: #6c757d; font-weight: 600;">
                    Menampilkan 0 - 0 dari 0 data
                </div>
            </div>
            <div class="col-md-6">
                <nav aria-label="Pagination Kasus">
                    <ul class="pagination justify-content-end mb-0" id="kasusPagination">
                        <!-- Pagination buttons will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Recent Laporan Table -->
<div class="card table-card mb-30">
    <div class="card-header">
        <h4 class="mb-0">📋 Laporan Terbaru</h4>
        <p class="mb-0 small" style="opacity: 0.9;">10 laporan terakhir yang masuk</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="pengaduan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Judul Laporan</th>
                        <th>Lokasi</th>
                        <th width="120">Tanggal</th>
                        <th width="150" class="text-center">Diarahkan Ke</th>
                        <th width="120" class="text-center">Status</th>
                        <th width="60" class="text-center">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get recent laporan dari tabel lapmas (filtered by assigned_to)
                    // Ditresnarkoba bisa lihat semua, Ditsamapta/Ditbinmas hanya yang assigned ke mereka
                    // Support multiple assignments (comma-separated)
                    if ($role == 'Ditresnarkoba') {
                        $query_recent = "SELECT l.*, a.Nama as nama_pelapor
                                        FROM lapmas l
                                        LEFT JOIN akun a ON l.Id_akun = a.Id_akun
                                        ORDER BY l.tanggal_lapor DESC
                                        LIMIT 10";
                        $stmt_recent = mysqli_prepare($db, $query_recent);
                    } else {
                        // Gunakan FIND_IN_SET untuk mendukung multiple assignments
                        $query_recent = "SELECT l.*, a.Nama as nama_pelapor
                                        FROM lapmas l
                                        LEFT JOIN akun a ON l.Id_akun = a.Id_akun
                                        WHERE FIND_IN_SET(?, l.assigned_to) > 0
                                        ORDER BY l.tanggal_lapor DESC
                                        LIMIT 10";
                        $stmt_recent = mysqli_prepare($db, $query_recent);
                        mysqli_stmt_bind_param($stmt_recent, "s", $role);
                    }
                    mysqli_stmt_execute($stmt_recent);
                    $result_recent = mysqli_stmt_get_result($stmt_recent);

                    $no = 1;
                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                            // Determine status class - Support all status types with detailed colors
                            $status_class = 'status-baru';

                            switch ($row['status']) {
                                case 'Baru':
                                    $status_class = 'status-baru';
                                    break;

                                // Status Diproses
                                case 'Diproses Ditresnarkoba':
                                    $status_class = 'status-diproses-ditresnarkoba';
                                    break;
                                case 'Diproses Ditsamapta':
                                    $status_class = 'status-diproses-ditsamapta';
                                    break;
                                case 'Diproses Ditbinmas':
                                    $status_class = 'status-diproses-ditbinmas';
                                    break;

                                // Status Selesai
                                case 'Selesai':
                                    $status_class = 'status-selesai';
                                    break;
                                case 'Selesai Ditresnarkoba':
                                    $status_class = 'status-selesai-ditresnarkoba';
                                    break;
                                case 'Selesai Ditsamapta':
                                    $status_class = 'status-selesai-ditsamapta';
                                    break;
                                case 'Selesai Ditbinmas':
                                    $status_class = 'status-selesai-ditbinmas';
                                    break;

                                // Status Lainnya
                                case 'Waiting':
                                    $status_class = 'status-waiting';
                                    break;
                                case 'Ditolak':
                                    $status_class = 'status-ditolak';
                                    break;

                                default:
                                    $status_class = 'status-baru';
                            }

                            // Nama pelapor
                            $nama_pelapor = $row['nama_pelapor'] ? $row['nama_pelapor'] : 'Anonim';
                    ?>
                            <tr class="clickable-row" data-href="dash.php?page=detail-pengaduan&id=<?php echo $row['id_lapmas']; ?>" style="cursor: pointer;">
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td>
                                    <div class="font-14 weight-600"><?php echo htmlspecialchars($row['judul']); ?></div>
                                    <div class="text-muted small"><?php echo substr(htmlspecialchars($row['desk']), 0, 50); ?>...</div>
                                </td>
                                <td><?php echo htmlspecialchars($row['lokasi'] ? $row['lokasi'] : '-'); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tanggal_lapor'])); ?></td>
                                <td class="text-center">
                                    <?php
                                    // Split assigned_to by comma for display (support multiple assignments)
                                    $assigned_units = explode(',', $row['assigned_to']);
                                    foreach ($assigned_units as $unit):
                                        $unit = trim($unit); // Trim any whitespace
                                        $badge_class = 'badge-secondary';

                                        // Set badge color based on unit
                                        if ($unit == 'Ditresnarkoba') {
                                            $badge_class = 'badge-danger';
                                        } elseif ($unit == 'Ditsamapta') {
                                            $badge_class = 'badge-primary';
                                        } elseif ($unit == 'Ditbinmas') {
                                            $badge_class = 'badge-success';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> mr-1 mb-1" style="font-size: 0.75rem;"><?php echo htmlspecialchars($unit); ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id_lapmas']; ?>" style="border-radius: 8px;">
                                        <i class="dw dw-delete-3"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div style="opacity: 0.5;">
                                    <i class="icon-copy dw dw-file" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-0">Belum ada laporan</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Feedback -->
<div class="modal fade" id="modalFeedback" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a1f3a; color: white;">
                <h5 class="modal-title" id="modalFeedbackTitle">
                    <i class="icon-copy dw dw-chat3"></i> Detail Respon Unit
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 style="color: #1a1f3a; font-weight: 700;">Kecamatan: <span id="feedbackKecamatan"></span></h6>
                </div>

                <!-- List Feedback -->
                <div id="feedbackList"></div>

                <!-- Form Tambah Feedback (hanya untuk Ditbinmas dan Ditsamapta) -->
                <?php if ($role == 'Ditbinmas' || $role == 'Ditsamapta'): ?>
                <div class="card mt-3" style="border: 2px solid #FFD700; border-radius: 10px;">
                    <div class="card-body" style="background: #f8f9fa;">
                        <h6 style="color: #1a1f3a; font-weight: 700; margin-bottom: 15px;">
                            <i class="icon-copy dw dw-add"></i> Tambah Respon
                        </h6>
                        <form id="formAddFeedback">
                            <input type="hidden" id="feedbackKecInput" name="kecamatan">
                            <input type="hidden" name="unit" value="<?php echo $role; ?>">

                            <div class="form-group">
                                <label style="font-weight: 600;">Jenis Tindakan:</label>
                                <select class="form-control" name="jenis_tindakan" required>
                                    <option value="">-- Pilih Jenis Tindakan --</option>
                                    <?php if ($role == 'Ditsamapta'): ?>
                                    <option value="Patroli Rutin">Patroli Rutin</option>
                                    <option value="Patroli Khusus">Patroli Khusus</option>
                                    <option value="Pemantauan Wilayah">Pemantauan Wilayah</option>
                                    <option value="Operasi Gabungan">Operasi Gabungan</option>
                                    <option value="Razia">Razia</option>
                                    <?php elseif ($role == 'Ditbinmas'): ?>
                                    <option value="Sosialisasi">Sosialisasi</option>
                                    <option value="Bimbingan Masyarakat">Bimbingan Masyarakat</option>
                                    <option value="Penyuluhan di Sekolah">Penyuluhan di Sekolah</option>
                                    <option value="Pembinaan Kelurahan">Pembinaan Kelurahan</option>
                                    <option value="Pelatihan Kader Anti Narkoba">Pelatihan Kader Anti Narkoba</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600;">Keterangan Tindakan:</label>
                                <textarea class="form-control" name="keterangan" rows="3" placeholder="Jelaskan detail tindakan yang dilakukan..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 600;">Status Tindakan:</label>
                                <select class="form-control" name="status" required>
                                    <option value="Direncanakan">Direncanakan</option>
                                    <option value="Sedang Berlangsung">Sedang Berlangsung</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" style="background: #1a1f3a; border: none; border-radius: 8px;">
                                <i class="icon-copy dw dw-diskette"></i> Simpan Respon
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Kasus -->
<div class="modal fade" id="modalEditKasus" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header" style="background: #1a1f3a; color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title" style="color: #FFD700; font-weight: 700;">
                    <i class="icon-copy dw dw-edit2"></i> Edit Data Kasus
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <form id="formEditKasus">
                    <input type="hidden" id="edit_id_kasus" name="id_kasus">

                    <div class="alert alert-warning" style="background: #fff3cd; border-left: 4px solid #FFD700; color: #856404;">
                        <i class="icon-copy dw dw-info"></i> Pastikan data yang dimasukkan akurat dan terbaru
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600" style="color: #1a1f3a;">
                            <i class="icon-copy dw dw-map"></i> Kecamatan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_kec" name="kec" required
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px;"
                               placeholder="Masukkan nama kecamatan">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600" style="color: #1a1f3a;">
                            <i class="icon-copy dw dw-file"></i> Jumlah Kasus <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="edit_jumlah_kasus" name="jumlah_kasus" required min="0"
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px;"
                               placeholder="Masukkan jumlah kasus">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600" style="color: #1a1f3a;">
                            <i class="icon-copy dw dw-user"></i> Jumlah Tersangka <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="edit_tersangka" name="tersangka" required min="0"
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px;"
                               placeholder="Masukkan jumlah tersangka">
                    </div>

                    <div class="alert" id="editKasusAlert" style="display: none; border-radius: 10px;"></div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 2px solid #e9ecef; padding: 20px 30px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px; padding: 10px 20px;">
                    <i class="icon-copy dw dw-cancel"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="submitEditKasus()" id="btnSubmitEditKasus" style="background: #1a1f3a; border: none; border-radius: 10px; padding: 10px 25px; font-weight: 600;">
                    <i class="icon-copy dw dw-diskette"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if ApexCharts is loaded
        if (typeof ApexCharts === 'undefined') {
            console.warn('ApexCharts library not loaded - chart will not render');
            return;
        }

        // Data dari PHP
        var chartLabels = <?php echo json_encode($chart_labels); ?>;
        var chartData = <?php echo json_encode($chart_data); ?>;

        // Chart options
        var options = {
            series: [{
                name: 'Jumlah Laporan',
                data: chartData
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#1a1f3a'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'solid',
                opacity: 0.3
            },
            xaxis: {
                categories: chartLabels,
                labels: {
                    style: {
                        colors: '#6c757d',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return Math.floor(val);
                    },
                    style: {
                        colors: '#6c757d',
                        fontSize: '12px'
                    }
                }
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 5
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " laporan"
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart-pengaduan"), options);
        chart.render();
    });
</script>

<!-- DataTable Script -->
<script>
    // Wait for jQuery to be loaded
    (function checkjQuery() {
        if (typeof jQuery === 'undefined') {
            setTimeout(checkjQuery, 50);
            return;
        }
        initDashboardTable();
    })();

    function initDashboardTable() {
    $(document).ready(function() {
        // Initialize DataTable for Pengaduan
        var table = $('#pengaduan-table').DataTable({
            scrollCollapse: true,
            autoWidth: false,
            responsive: true,
            columnDefs: [{
                targets: [0, 5],
                orderable: false,
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            "language": {
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ laporan",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "lengthMenu": "Tampilkan _MENU_ data",
                "search": "Cari:",
                "zeroRecords": "Tidak ada data yang cocok",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": '<i class="ion-chevron-right"></i>',
                    "previous": '<i class="ion-chevron-left"></i>'
                }
            },
            "pageLength": 10
        });

        // Initialize DataTable for Kasus - disable default DataTable to use custom filter
        var kasusTable = $('#kasus-table').DataTable({
            scrollCollapse: true,
            autoWidth: false,
            responsive: true,
            paging: false, // Disable pagination to use custom filter
            searching: false, // Disable search to use custom filter
            info: false, // Disable info
            columnDefs: [{
                targets: 0,
                orderable: false,
            }],
            "order": [[1, 'desc']] // Sort by Jumlah Kasus (column 1) descending
        });

        // Pagination variables
        let currentKasusPage = 1;
        let itemsPerPage = 10;

        // Custom Filter and Sort for Kasus Table with Pagination
        function filterKasusTable() {
            const filterKec = document.getElementById('filterKecKasus').value.toLowerCase();
            const sortOption = document.getElementById('sortKasusDashboard').value;
            const tbody = document.getElementById('tbodyKasusDashboard');
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            const itemsPerPageSelect = document.getElementById('itemsPerPageKasus').value;

            // Update items per page
            itemsPerPage = itemsPerPageSelect === 'all' ? 999999 : parseInt(itemsPerPageSelect);

            // Filter rows first
            const filteredRows = [];
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];

                // Skip no data row
                if (!row.getAttribute('data-kec')) continue;

                const kec = (row.getAttribute('data-kec') || '').toLowerCase();
                let showRow = true;

                // Filter by kecamatan
                if (filterKec && kec !== filterKec) {
                    showRow = false;
                }

                if (showRow) {
                    filteredRows.push(row);
                }
            }

            // Sort filtered rows
            if (sortOption && filteredRows.length > 0) {
                filteredRows.sort((a, b) => {
                    let valueA, valueB;

                    if (sortOption.startsWith('jumlah_')) {
                        // Get jumlah kasus from badge text
                        valueA = parseInt(a.cells[1].textContent.replace(' kasus', '').trim()) || 0;
                        valueB = parseInt(b.cells[1].textContent.replace(' kasus', '').trim()) || 0;
                    } else if (sortOption.startsWith('tersangka_')) {
                        // Get jumlah tersangka from badge text
                        valueA = parseInt(a.cells[2].textContent.replace(' orang', '').trim()) || 0;
                        valueB = parseInt(b.cells[2].textContent.replace(' orang', '').trim()) || 0;
                    }

                    if (sortOption.endsWith('_desc')) {
                        return valueB - valueA; // Descending
                    } else {
                        return valueA - valueB; // Ascending
                    }
                });
            }

            // Calculate pagination
            const totalItems = filteredRows.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            // Make sure current page is valid
            if (currentKasusPage > totalPages && totalPages > 0) {
                currentKasusPage = totalPages;
            }
            if (currentKasusPage < 1) {
                currentKasusPage = 1;
            }

            const startIndex = (currentKasusPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

            // Hide all rows first
            rows.forEach(row => row.style.display = 'none');

            // Show only rows for current page
            for (let i = 0; i < filteredRows.length; i++) {
                if (i >= startIndex && i < endIndex) {
                    filteredRows[i].style.display = '';
                    tbody.appendChild(filteredRows[i]); // Reorder
                } else {
                    filteredRows[i].style.display = 'none';
                }
            }

            // Update row numbers
            updateKasusRowNumbers();

            // Update pagination info and controls
            updateKasusPaginationInfo(startIndex + 1, endIndex, totalItems);
            renderKasusPagination(totalPages);
        }

        function updateKasusRowNumbers() {
            const tbody = document.getElementById('tbodyKasusDashboard');
            const rows = tbody.getElementsByTagName('tr');
            const startIndex = (currentKasusPage - 1) * itemsPerPage;
            let num = startIndex + 1;

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                if (row.style.display !== 'none' && row.getAttribute('data-kec')) {
                    const firstCell = row.getElementsByTagName('td')[0];
                    if (firstCell) {
                        firstCell.textContent = num++;
                    }
                }
            }
        }

        function updateKasusPaginationInfo(start, end, total) {
            const infoDiv = document.getElementById('kasusTableInfo');
            if (total === 0) {
                infoDiv.textContent = 'Tidak ada data untuk ditampilkan';
            } else {
                infoDiv.textContent = `Menampilkan ${start} - ${end} dari ${total} data`;
            }
        }

        function renderKasusPagination(totalPages) {
            const pagination = document.getElementById('kasusPagination');
            pagination.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentKasusPage === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" onclick="changeKasusPage(${currentKasusPage - 1}); return false;"><i class="ion-chevron-left"></i></a>`;
            pagination.appendChild(prevLi);

            // Page numbers
            let startPage = Math.max(1, currentKasusPage - 2);
            let endPage = Math.min(totalPages, currentKasusPage + 2);

            // First page
            if (startPage > 1) {
                const firstLi = document.createElement('li');
                firstLi.className = 'page-item';
                firstLi.innerHTML = `<a class="page-link" href="#" onclick="changeKasusPage(1); return false;">1</a>`;
                pagination.appendChild(firstLi);

                if (startPage > 2) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = '<a class="page-link" href="#">...</a>';
                    pagination.appendChild(dotsLi);
                }
            }

            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                const pageLi = document.createElement('li');
                pageLi.className = `page-item ${i === currentKasusPage ? 'active' : ''}`;
                pageLi.innerHTML = `<a class="page-link" href="#" onclick="changeKasusPage(${i}); return false;">${i}</a>`;
                pagination.appendChild(pageLi);
            }

            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = '<a class="page-link" href="#">...</a>';
                    pagination.appendChild(dotsLi);
                }

                const lastLi = document.createElement('li');
                lastLi.className = 'page-item';
                lastLi.innerHTML = `<a class="page-link" href="#" onclick="changeKasusPage(${totalPages}); return false;">${totalPages}</a>`;
                pagination.appendChild(lastLi);
            }

            // Next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentKasusPage === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" onclick="changeKasusPage(${currentKasusPage + 1}); return false;"><i class="ion-chevron-right"></i></a>`;
            pagination.appendChild(nextLi);
        }

        // Global function for pagination
        window.changeKasusPage = function(page) {
            currentKasusPage = page;
            filterKasusTable();
        };

        // Event listeners for kasus filters
        document.getElementById('filterKecKasus').addEventListener('change', function() {
            currentKasusPage = 1;
            filterKasusTable();
        });
        document.getElementById('sortKasusDashboard').addEventListener('change', function() {
            currentKasusPage = 1;
            filterKasusTable();
        });
        document.getElementById('itemsPerPageKasus').addEventListener('change', function() {
            currentKasusPage = 1;
            filterKasusTable();
        });

        // Reset kasus filter
        document.getElementById('btnResetFilterKasus').addEventListener('click', function() {
            document.getElementById('filterKecKasus').value = '';
            document.getElementById('sortKasusDashboard').value = 'jumlah_desc';
            document.getElementById('itemsPerPageKasus').value = '10';
            itemsPerPage = 10;
            currentKasusPage = 1;
            filterKasusTable();
        });

        // Initialize pagination on load
        filterKasusTable();

        // Make table rows clickable - menggunakan event delegation pada tbody
        $('#pengaduan-table tbody').on('click', 'tr.clickable-row', function(e) {
            // Jangan trigger jika yang diklik adalah button atau child dari button
            if ($(e.target).closest('button, .btn').length > 0) {
                return;
            }

            var href = $(this).data('href');
            if (href) {
                window.location.href = href;
            }
        });

        // Debug: log when row is clicked
        console.log('Clickable row handler initialized for #pengaduan-table');

        // Handle delete button click
        $('#pengaduan-table tbody').on('click', '.delete-btn', function(e) {
            e.stopPropagation();
            e.preventDefault();

            const id = $(this).data('id');
            console.log('Delete button clicked, ID:', id);

            if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
                window.location.href = 'dash.php?page=dashboard&action=delete&id=' + id;
            }
        });

        // ==================================
        // FEEDBACK INTERACTION HANDLERS
        // ==================================

        // Handle view feedback button click
        $(document).on('click', '.view-feedback-btn', function(e) {
            e.preventDefault();
            const kecamatan = $(this).data('kec');

            // Set kecamatan in modal
            $('#feedbackKecamatan').text(kecamatan);
            $('#feedbackKecInput').val(kecamatan);

            // Load feedback for this kecamatan
            loadFeedback(kecamatan);

            // Show modal
            $('#modalFeedback').modal('show');
        });

        // Load feedback function
        function loadFeedback(kecamatan) {
            $('#feedbackList').html('<div class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Memuat data...</div>');

            $.ajax({
                url: 'content_a/feedback_handler.php',
                type: 'GET',
                data: {
                    action: 'get_feedback',
                    kecamatan: kecamatan
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayFeedback(response.data);
                    } else {
                        $('#feedbackList').html('<div class="alert alert-danger">Gagal memuat data: ' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#feedbackList').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data</div>');
                }
            });
        }

        // Display feedback function
        function displayFeedback(feedbacks) {
            if (feedbacks.length === 0) {
                $('#feedbackList').html('<div class="alert alert-info"><i class="icon-copy dw dw-info"></i> Belum ada respon untuk kecamatan ini</div>');
                return;
            }

            let html = '';
            feedbacks.forEach(function(feedback) {
                // Status badge
                let statusBadge = '';
                if (feedback.status === 'Direncanakan') {
                    statusBadge = '<span class="badge badge-secondary" style="font-size: 0.75rem;">Direncanakan</span>';
                } else if (feedback.status === 'Sedang Berlangsung') {
                    statusBadge = '<span class="badge badge-warning" style="font-size: 0.75rem; color: #1a1f3a;">Sedang Berlangsung</span>';
                } else if (feedback.status === 'Selesai') {
                    statusBadge = '<span class="badge badge-success" style="font-size: 0.75rem;">Selesai</span>';
                }

                // Unit badge
                let unitBadge = '';
                let unitIcon = '';
                if (feedback.unit === 'Ditbinmas') {
                    unitBadge = '<span class="badge badge-success" style="font-size: 0.75rem;"><i class="icon-copy dw dw-group"></i> Ditbinmas</span>';
                    unitIcon = 'dw dw-group';
                } else if (feedback.unit === 'Ditsamapta') {
                    unitBadge = '<span class="badge badge-warning" style="font-size: 0.75rem; color: #1a1f3a;"><i class="icon-copy dw dw-shield"></i> Ditsamapta</span>';
                    unitIcon = 'dw dw-shield';
                }

                // Format date
                const date = new Date(feedback.tanggal_respon);
                const formattedDate = date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                html += `
                    <div class="card mb-3" style="border-left: 4px solid ${feedback.unit === 'Ditbinmas' ? '#16a34a' : '#ea580c'};">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    ${unitBadge}
                                    ${statusBadge}
                                </div>
                                <small class="text-muted">${formattedDate}</small>
                            </div>
                            <h6 style="color: #1a1f3a; font-weight: 700; margin-top: 10px;">
                                <i class="icon-copy ${unitIcon}"></i> ${feedback.jenis_tindakan}
                            </h6>
                            <p class="mb-2" style="color: #495057;">${feedback.keterangan}</p>
                            ${feedback.nama_user ? '<small class="text-muted">Oleh: ' + feedback.nama_user + '</small>' : ''}

                            <?php if ($role == 'Ditbinmas' || $role == 'Ditsamapta'): ?>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-danger delete-feedback-btn" data-id="${feedback.id}" style="border-radius: 5px;">
                                    <i class="icon-copy dw dw-delete-3"></i> Hapus
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                `;
            });

            $('#feedbackList').html(html);
        }

        // Handle form submission
        $('#formAddFeedback').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize() + '&action=add_feedback';
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: 'content_a/feedback_handler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html('<i class="icon-copy dw dw-diskette"></i> Simpan Respon');

                    if (response.success) {
                        alert(response.message);
                        $('#formAddFeedback')[0].reset();

                        // Reload feedback
                        const kecamatan = $('#feedbackKecInput').val();
                        loadFeedback(kecamatan);

                        // Reload page to update badge counts
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                },
                error: function() {
                    submitBtn.prop('disabled', false).html('<i class="icon-copy dw dw-diskette"></i> Simpan Respon');
                    alert('Terjadi kesalahan saat menyimpan data');
                }
            });
        });

        // Handle delete feedback
        $(document).on('click', '.delete-feedback-btn', function(e) {
            e.preventDefault();

            if (!confirm('Apakah Anda yakin ingin menghapus respon ini?')) {
                return;
            }

            const feedbackId = $(this).data('id');
            const kecamatan = $('#feedbackKecInput').val();

            $.ajax({
                url: 'content_a/feedback_handler.php',
                type: 'POST',
                data: {
                    action: 'delete_feedback',
                    feedback_id: feedbackId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        loadFeedback(kecamatan);

                        // Reload page to update badge counts
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        });

        // ==================================
        // EDIT KASUS HANDLERS
        // ==================================

        // Handle edit kasus button click
        $(document).on('click', '.edit-kasus-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id = $(this).data('id');
            const kec = $(this).data('kec');
            const kasus = $(this).data('kasus');
            const tersangka = $(this).data('tersangka');

            // Populate form
            $('#edit_id_kasus').val(id);
            $('#edit_kec').val(kec);
            $('#edit_jumlah_kasus').val(kasus);
            $('#edit_tersangka').val(tersangka);

            // Show modal
            $('#modalEditKasus').modal('show');
        });

        // ==================================
        // DELETE KASUS HANDLERS
        // ==================================

        // Handle delete kasus button click
        $(document).on('click', '.delete-kasus-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id = $(this).data('id');
            const kec = $(this).data('kec');

            // Confirmation dialog
            if (!confirm(`Apakah Anda yakin ingin menghapus data kasus untuk kecamatan "${kec}"?\n\nPeringatan: Data yang dihapus tidak dapat dikembalikan!`)) {
                return;
            }

            // Send delete request
            $.ajax({
                url: 'content_a/delete_kasus.php',
                type: 'POST',
                data: {
                    action: 'delete_kasus',
                    id_kasus: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        // Reload page to refresh data
                        location.reload();
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        });

        // ==================================
        // MERGE DUPLICATE HANDLERS
        // ==================================

        // Handle merge duplicates button click
        $(document).on('click', '#btnMergeDuplicates', function(e) {
            e.preventDefault();

            // Confirmation dialog
            if (!confirm('Apakah Anda yakin ingin menggabungkan semua data kecamatan yang sama?\n\nProses ini akan:\n1. Menggabungkan data kecamatan yang duplicate\n2. Menjumlahkan jumlah kasus dan tersangka\n3. Menghapus data duplicate\n\nPeringatan: Proses ini tidak dapat dibatalkan!')) {
                return;
            }

            const btnMerge = $(this);
            const originalHTML = btnMerge.html();
            btnMerge.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menggabungkan...');

            // Send merge request
            $.ajax({
                url: 'content_a/merge_kasus.php',
                type: 'POST',
                data: {
                    action: 'merge_duplicates'
                },
                dataType: 'json',
                success: function(response) {
                    btnMerge.prop('disabled', false).html(originalHTML);

                    if (response.success) {
                        alert(response.message + '\n\nDetail:\n' +
                              '- Kecamatan yang digabung: ' + response.merged_count + '\n' +
                              '- Data yang dihapus: ' + response.deleted_count);
                        // Reload page to refresh data
                        location.reload();
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                },
                error: function() {
                    btnMerge.prop('disabled', false).html(originalHTML);
                    alert('Terjadi kesalahan saat menggabungkan data');
                }
            });
        });

    });
    } // End of initDashboardTable()

    // Submit edit kasus function (global scope)
    function submitEditKasus() {
        const form = document.getElementById('formEditKasus');
        const formData = new FormData(form);

        // Validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btnSubmit = document.getElementById('btnSubmitEditKasus');
        const originalHTML = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

        // Prepare data for submission
        const data = {
            action: 'update_kasus',
            id_kasus: formData.get('id_kasus'),
            kec: formData.get('kec'),
            jumlah_kasus: formData.get('jumlah_kasus'),
            tersangka: formData.get('tersangka')
        };

        // AJAX request
        fetch('content_a/update_kasus.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHTML;

            if (result.success) {
                showEditKasusAlert('Data berhasil diperbarui!', 'success');
                setTimeout(() => {
                    $('#modalEditKasus').modal('hide');
                    location.reload();
                }, 1500);
            } else {
                showEditKasusAlert(result.message || 'Gagal mengupdate data', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHTML;
            showEditKasusAlert('Terjadi kesalahan saat menyimpan data', 'danger');
        });
    }

    function showEditKasusAlert(message, type) {
        const alert = document.getElementById('editKasusAlert');
        alert.className = `alert alert-${type}`;
        alert.style.display = 'block';
        alert.innerHTML = `<i class="icon-copy dw dw-${type === 'success' ? 'checked' : 'warning'}"></i> ${message}`;

        if (type === 'success') {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }
    }
</script>