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
<div class="card table-card mb-30">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">🚨 Data Daerah Rawan Narkoba</h4>
                <p class="mb-0 small" style="opacity: 0.9;">Statistik kasus narkoba berdasarkan kecamatan</p>
            </div>
            <?php if ($role == 'Ditresnarkoba'): ?>
            <a href="dash.php?page=input-laporan-Ditresnarkoba" class="btn btn-light btn-sm">
                <i class="icon-copy dw dw-edit2"></i> Kelola Data
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-4">
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
            <div class="col-md-4">
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
            <div class="col-md-4">
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
                                    <button class="btn btn-sm btn-info view-feedback-btn" data-kec="<?php echo htmlspecialchars($row_kasus['kec']); ?>" style="border-radius: 8px;">
                                        <i class="icon-copy dw dw-eye"></i> Lihat
                                    </button>
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
                        <th width="120" class="text-center">Status</th>
                        <th width="60" class="text-center">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get recent laporan dari tabel lapmas
                    $query_recent = "SELECT l.*, a.Nama as nama_pelapor
                                    FROM lapmas l
                                    LEFT JOIN akun a ON l.Id_akun = a.Id_akun
                                    ORDER BY l.tanggal_lapor DESC
                                    LIMIT 10";
                    $result_recent = mysqli_query($db, $query_recent);

                    $no = 1;
                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                            // Status badge
                            $status_class = 'secondary';
                            $status_text = $row['status'];

                            if ($row['status'] == 'Baru') {
                                $status_class = 'warning';
                                $status_text = 'Baru';
                            } elseif (strpos($row['status'], 'Diproses') !== false) {
                                $status_class = 'info';
                                $status_text = 'Diproses';
                            } elseif ($row['status'] == 'Selesai') {
                                $status_class = 'success';
                                $status_text = 'Selesai';
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
                                    <span class="badge badge-<?php echo $status_class; ?> badge-custom">
                                        <?php echo $status_text; ?>
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
                            <td colspan="7" class="text-center py-5">
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

        // Custom Filter and Sort for Kasus Table
        function filterKasusTable() {
            const filterKec = document.getElementById('filterKecKasus').value.toLowerCase();
            const sortOption = document.getElementById('sortKasusDashboard').value;
            const tbody = document.getElementById('tbodyKasusDashboard');
            const rows = Array.from(tbody.getElementsByTagName('tr'));

            let visibleCount = 0;

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
                    row.style.display = '';
                    filteredRows.push(row);
                    visibleCount++;
                } else {
                    row.style.display = 'none';
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

                // Reorder rows in DOM
                filteredRows.forEach(row => tbody.appendChild(row));
            }

            // Update row numbers
            updateKasusRowNumbers();
        }

        function updateKasusRowNumbers() {
            const tbody = document.getElementById('tbodyKasusDashboard');
            const rows = tbody.getElementsByTagName('tr');
            let num = 1;

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

        // Event listeners for kasus filters
        document.getElementById('filterKecKasus').addEventListener('change', filterKasusTable);
        document.getElementById('sortKasusDashboard').addEventListener('change', filterKasusTable);

        // Reset kasus filter
        document.getElementById('btnResetFilterKasus').addEventListener('click', function() {
            document.getElementById('filterKecKasus').value = '';
            document.getElementById('sortKasusDashboard').value = 'jumlah_desc';
            filterKasusTable();
        });

        // Make table rows clickable - menggunakan event delegation pada tbody
        $('#pengaduan-table tbody').on('click', 'tr.clickable-row', function() {
            var href = $(this).data('href');
            if (href) {
                window.location.href = href;
            }
        });

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

    });
    } // End of initDashboardTable()
</script>