<?php
// Get id laporan from URL
$id_laporan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_laporan == 0) {
    echo '<div class="alert alert-danger">ID Laporan tidak valid!</div>';
    exit;
}

// Get laporan detail
$query = "SELECT 
            l.*, 
            a.Nama AS nama_pelapor, 
            a.Email AS email_pelapor, 
            a.Nomor_hp AS nomor_hp,
            r.id_respon,
            r.respon,
            r.a_respon,
            r.tanggal_respon
          FROM lapmas l
          LEFT JOIN akun a ON l.Id_akun = a.Id_Akun
          LEFT JOIN respon r ON r.id_lapmas = l.id_lapmas
          WHERE l.id_lapmas = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id_laporan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">Laporan tidak ditemukan!</div>';
    exit;
}

$laporan = mysqli_fetch_assoc($result);

// Get telepon from laporan table (no_hp column)
$telp_pelapor = $laporan['nomor_hp'];

// Handle update status
$success_message = '';
$error_message = '';

if (isset($_POST['update_status'])) {
    $status_baru = mysqli_real_escape_string($db, $_POST['status']);
    $tanggapan = mysqli_real_escape_string($db, $_POST['respon']);
    $petugas = isset($_SESSION['Nama']) ? $_SESSION['Nama'] : 'Admin';
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

    // Tentukan siapa yang memproses berdasarkan role
    $sedang_diproses_oleh = '';
    if (strpos($role, 'Ditresnarkoba') !== false) {
        $sedang_diproses_oleh = 'Ditresnarkoba';
    } elseif (strpos($role, 'Ditsamapta') !== false) {
        $sedang_diproses_oleh = 'Ditsamapta';
    } elseif (strpos($role, 'Ditbinmas') !== false) {
        $sedang_diproses_oleh = 'Ditbinmas';
    }


    // Tambahkan entry baru ke timeline
    $new_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'status_dari' => $laporan['status'],
        'status_ke' => $status_baru,
        'diproses_oleh' => $sedang_diproses_oleh,
        'nama_petugas' => $petugas,
        'tanggapan' => $tanggapan
    ];
    $timeline_array[] = $new_entry;
    $timeline_json = json_encode($timeline_array, JSON_UNESCAPED_UNICODE);

    // Update tabel_laporan dengan timeline baru
    $query_update = "UPDATE tabel_laporan
                     SET status_laporan = ?,
                         tanggapan_admin = ?,
                         tanggal_tanggapan = NOW(),
                         sedang_diproses_oleh = ?,
                         timeline_json = ?
                     WHERE id_laporan = ?";
    $stmt_update = mysqli_prepare($db, $query_update);
    mysqli_stmt_bind_param($stmt_update, "ssssi", $status_baru, $tanggapan, $sedang_diproses_oleh, $timeline_json, $id_laporan);

    if (mysqli_stmt_execute($stmt_update)) {
        // Jika status = diproses_Ditresnarkoba, set notifikasi untuk Ditsamapta dan Ditbinmas
        if ($status_baru == 'diproses_Ditresnarkoba') {
            $query_notif = "UPDATE tabel_laporan
                           SET is_notif_Ditsamapta = 1, is_notif_Ditbinmas = 1
                           WHERE id_laporan = ?";
            $stmt_notif = mysqli_prepare($db, $query_notif);
            mysqli_stmt_bind_param($stmt_notif, "i", $id_laporan);
            mysqli_stmt_execute($stmt_notif);
        }

        // Jika Ditsamapta atau Ditbinmas mengambil, clear notifikasi mereka
        if ($status_baru == 'diproses_Ditsamapta') {
            $query_clear = "UPDATE tabel_laporan SET is_notif_Ditsamapta = 0 WHERE id_laporan = ?";
            $stmt_clear = mysqli_prepare($db, $query_clear);
            mysqli_stmt_bind_param($stmt_clear, "i", $id_laporan);
            mysqli_stmt_execute($stmt_clear);
        } elseif ($status_baru == 'diproses_Ditbinmas') {
            $query_clear = "UPDATE tabel_laporan SET is_notif_Ditbinmas = 0 WHERE id_laporan = ?";
            $stmt_clear = mysqli_prepare($db, $query_clear);
            mysqli_stmt_bind_param($stmt_clear, "i", $id_laporan);
            mysqli_stmt_execute($stmt_clear);
        }

        $success_message = 'Status berhasil diupdate!';

        // Refresh data
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $laporan = mysqli_fetch_assoc($result);
    } else {
        $error_message = 'Gagal update status!';
    }
}

// Status badge class
$status_class = 'secondary';
$status_icon = 'dw-file';
if ($laporan['status'] == 'baru') {
    $status_class = 'warning';
    $status_icon = 'dw-inbox';
} elseif (strpos($laporan['status'], 'diproses') !== false) {
    $status_class = 'info';
    $status_icon = 'dw-loading';
} elseif (strpos($laporan['status'], 'selesai') !== false) {
    $status_class = 'success';
    $status_icon = 'dw-checked';
}

// Parse images
$images = [];
if (!empty($laporan['gambar'])) {
    $images = explode(',', $laporan['gambar']);
}

// Get nama pelapor
$nama_pelapor = 'Anonim';
if (!empty($laporan['nama_pelapor'])) {
    $nama_pelapor = $laporan['nama_pelapor'];
} elseif (!empty($laporan['nama'])) {
    $nama_pelapor = $laporan['nama'];
}
?>

<style>
    .detail-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 20px;
    }

    .detail-header {
        background: #1E40AF;
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        border-bottom: 4px solid #FFD700;
    }

    .detail-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        opacity: 0.9;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-badge-large {
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .badge-warning {
        background-color: #FFD700;
        color: #1E40AF;
    }

    .badge-info {
        background-color: #1E40AF;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }

    .info-section {
        margin-bottom: 30px;
    }

    .info-section h5 {
        color: #1E40AF;
        font-weight: 700;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #FFD700;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .info-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #1E40AF;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 600;
        color: #495057;
        font-size: 1rem;
    }

    .content-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #1E40AF;
        line-height: 1.8;
    }

    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .gallery-item:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    .gallery-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding: 15px 20px;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .timeline-item {
        position: relative;
        padding-left: 60px;
        padding-top: 10px;
        padding-bottom: 20px;
        margin-bottom: 10px;
    }

    .timeline-badge {
        position: absolute;
        left: -32px;
        top: 15px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        z-index: 2;
    }

    .timeline-badge.bg-warning {
        background: #FFD700;
        color: #1E40AF;
    }

    .timeline-badge.bg-info {
        background: #1E40AF;
    }

    .timeline-badge.bg-success {
        background: #28a745;
    }

    .timeline-badge.bg-danger {
        background: #dc3545;
    }

    .timeline-badge.bg-secondary {
        background: #6c757d;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-primary.btn-action {
        background: #1E40AF;
        border-color: #1E40AF;
        color: white;
    }

    .btn-primary.btn-action:hover {
        background: #FFD700;
        border-color: #FFD700;
        color: #1E40AF;
    }

    .btn-secondary.btn-action {
        background: #6c757d;
        border-color: #6c757d;
        color: white;
    }

    .btn-secondary.btn-action:hover {
        background: #5a6268;
        border-color: #545b62;
    }

    .btn-primary.btn-block {
        background: #1E40AF;
        border-color: #1E40AF;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-primary.btn-block:hover {
        background: #FFD700;
        border-color: #FFD700;
        color: #1E40AF;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .tanggapan-box {
        background: #e7f3ff;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #1E40AF;
    }

    .no-tanggapan {
        text-align: center;
        padding: 30px;
        color: #adb5bd;
    }

    /* Lightbox for images */
    .lightbox {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
    }

    .lightbox.active {
        display: flex;
    }

    .lightbox img {
        max-width: 90%;
        max-height: 90%;
        border-radius: 10px;
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 40px;
        cursor: pointer;
        z-index: 10000;
    }

    @media print {

        .action-buttons,
        .no-print {
            display: none !important;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header no-print">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Detail Pengaduan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="dash.php?page=lihat-pengaduan">Lihat Pengaduan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <div class="action-buttons">
                <a href="dash.php?page=lihat-pengaduan" class="btn btn-secondary btn-action">
                    <i class="dw dw-left-arrow"></i> Kembali
                </a>
                <button class="btn btn-primary btn-action" onclick="window.print()">
                    <i class="dw dw-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
        <strong><i class="dw dw-checked"></i> Berhasil!</strong> <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
        <strong><i class="dw dw-warning"></i> Error!</strong> <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Detail Header -->
<div class="detail-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="detail-title"><?php echo htmlspecialchars($laporan['judul']); ?></div>
            <div class="detail-meta">
                <div class="meta-item">
                    <i class="dw dw-calendar1"></i>
                    <span><?php echo date('d F Y, H:i', strtotime($laporan['tanggal_lapor'])); ?> WIB</span>
                </div>
                <div class="meta-item">
                    <i class="dw dw-user1"></i>
                    <span><?php echo htmlspecialchars($nama_pelapor); ?></span>
                </div>
                <div class="meta-item">
                    <i class="dw dw-map"></i>
                    <span><?php echo htmlspecialchars($laporan['lokasi']); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="status-badge-large badge-<?php echo $status_class; ?>">
                <i class="dw <?php echo $status_icon; ?>"></i>
                <?php echo ucfirst($laporan['status']); ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-md-8">

        <!-- Isi Laporan -->
        <div class="detail-card">
            <div class="info-section">
                <h5>📝 Isi Laporan</h5>
                <div class="content-box">
                    <?php echo nl2br(htmlspecialchars($laporan['desk'])); ?>
                </div>
            </div>
        </div>

        <!-- Bukti Foto -->
        <?php if (!empty($images)): ?>
            <div class="detail-card">
                <div class="info-section">
                    <h5>📷 Bukti Foto/Dokumen</h5>
                    <div class="image-gallery">
                        <?php foreach ($images as $image):
                            $image = trim($image);
                            if (!empty($image) && file_exists($image)):
                        ?>
                                <div class="gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($image); ?>')">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Bukti">
                                </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tanggapan Admin -->
        <div class="detail-card">
            <div class="info-section">
                <h5>💬 Tanggapan Petugas</h5>
                <?php if (!empty($laporan['respon'])): ?>
                    <div class="tanggapan-box">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Tanggapan:</strong>
                            <small class="text-muted">
                                <?php
                                if ($laporan['tanggal_tanggapan'] != '0000-00-00 00:00:00') {
                                    echo date('d M Y, H:i', strtotime($laporan['tanggal_tanggapan']));
                                }
                                ?>
                            </small>
                        </div>
                        <div><?php echo nl2br(htmlspecialchars($laporan['tanggapan_admin'])); ?></div>
                    </div>
                <?php else: ?>
                    <div class="no-tanggapan">
                        <i class="dw dw-chat" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mb-0 mt-2">Belum ada tanggapan dari petugas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Right Column -->
    <div class="col-md-4">

        <!-- Informasi Pelapor -->
        <div class="detail-card">
            <div class="info-section">
                <h5>👤 Informasi Pelapor</h5>

                <div class="info-item mb-3">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value"><?php echo htmlspecialchars($nama_pelapor); ?></div>
                </div>

                <div class="info-item mb-3">
                    <div class="info-label">No. HP/Telepon</div>
                    <div class="info-value"><?php echo htmlspecialchars($laporan['nomor_hp']); ?></div>
                </div>

                <?php if (!empty($laporan['email_pelapor'])): ?>
                    <div class="info-item mb-3">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($laporan['email_pelapor']); ?></div>
                    </div>
                <?php endif; ?>

                <div class="info-item">
                    <div class="info-label">Tanggal Lapor</div>
                    <div class="info-value">
                        <?php echo date('d M Y', strtotime($laporan['tanggal_lapor'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="detail-card">
            <div class="info-section">
                <h5>📊 Timeline Status</h5>
                <div class="timeline">
                    <!-- Laporan Dibuat -->
                    <div class="timeline-item">
                        <div class="timeline-badge bg-warning">
                            <i class="dw dw-inbox"></i>
                        </div>
                        <strong>Laporan Dibuat</strong>
                        <div class="small text-muted">
                            <i class="dw dw-calendar1"></i>
                            <?php echo date('d M Y, H:i', strtotime($laporan['tanggal_lapor'])); ?> WIB
                        </div>
                        <div class="mt-2">Status: <span class="badge badge-warning">Baru</span></div>
                    </div>

                    <?php
                    // Get timeline dari JSON
                    $timeline_json = $laporan['timeline_json'] ?? '[]';
                    $timeline_array = json_decode($timeline_json, true);

                    if (is_array($timeline_array) && count($timeline_array) > 0):
                        foreach ($timeline_array as $timeline):
                            // Determine badge color based on status
                            $badge_color = 'secondary';
                            $badge_icon = 'dw-loading';
                            $timeline_bg = 'bg-secondary';

                            if (strpos($timeline['status_ke'], 'diproses') !== false) {
                                $badge_color = 'info';
                                $badge_icon = 'dw-loading';
                                $timeline_bg = 'bg-info';
                            }
                            if (strpos($timeline['status_ke'], 'selesai') !== false) {
                                $badge_color = 'success';
                                $badge_icon = 'dw-checked';
                                $timeline_bg = 'bg-success';
                            }
                            if (strpos($timeline['status_ke'], 'ditolak') !== false) {
                                $badge_color = 'danger';
                                $badge_icon = 'dw-warning';
                                $timeline_bg = 'bg-danger';
                            }

                            // Format status ke title case
                            $status_display = str_replace('_', ' ', $timeline['status_ke']);
                            $status_display = ucwords($status_display);

                            // role badge
                            $role_badge = '';
                            if ($timeline['diproses_oleh'] == 'Ditresnarkoba') {
                                $role_badge = '<span class="badge badge-dark ml-2">Ditresnarkoba</span>';
                            } elseif ($timeline['diproses_oleh'] == 'Ditsamapta') {
                                $role_badge = '<span class="badge badge-primary ml-2">Ditsamapta</span>';
                            } elseif ($timeline['diproses_oleh'] == 'Ditbinmas') {
                                $role_badge = '<span class="badge badge-success ml-2">Ditbinmas</span>';
                            }
                    ?>
                        <div class="timeline-item">
                            <div class="timeline-badge <?php echo $timeline_bg; ?>">
                                <i class="dw <?php echo $badge_icon; ?>"></i>
                            </div>
                            <strong><?php echo $status_display; ?></strong>
                            <?php echo $role_badge; ?>
                            <div class="small text-muted mt-1">
                                <i class="dw dw-user1"></i> <?php echo htmlspecialchars($timeline['nama_petugas']); ?>
                            </div>
                            <div class="small text-muted">
                                <i class="dw dw-calendar1"></i>
                                <?php echo date('d M Y, H:i', strtotime($timeline['timestamp'])); ?> WIB
                            </div>
                            <?php if (!empty($timeline['tanggapan'])): ?>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small><i class="dw dw-chat"></i> <?php echo nl2br(htmlspecialchars($timeline['tanggapan'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>

        <!-- Update Status (Only for Admin/Petugas) -->
        <div class="detail-card no-print">
            <div class="info-section">
                <h5>⚙️ Update Status</h5>
                <form method="POST">
                    <div class="form-group">
                        <label class="font-weight-600">Status Baru</label>
                        <select class="form-control" name="status_baru" required>
                            <?php
                            $session_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                            echo "<script>console.log('session_role: " . addslashes($session_role) . "');</script>";
                            $current_status = $laporan['status'];

                            // Ditresnarkoba - Aktor pertama
                            if (strpos($session_role, 'Ditresnarkoba') !== false) {
                                echo '<option value="Baru" ' . ($current_status == 'Baru' ? 'selected' : '') . '>Baru</option>';
                                echo '<option value="Diproses Ditresnarkoba" ' . ($current_status == 'Diproses Ditresnarkoba' ? 'selected' : '') . '>Diproses Ditresnarkoba</option>';
                                echo '<option value="Selesai" ' . ($current_status == 'selesai' ? 'selected' : '') . '>Selesai</option>';
                                echo '<option value="Ditolak" ' . ($current_status == 'ditolak' ? 'selected' : '') . '>Ditolak</option>';
                            }

                            // Ditsamapta - Aktor kedua (bisa akses jika sudah diproses_Ditresnarkoba)
                            if (strpos($session_role, 'Ditsamapta') !== false) {
                                // Cek apakah sudah diproses Ditresnarkoba
                                $bisa_akses = ($current_status == 'Diproses Ditresnarkoba' ||
                                              $current_status == 'Diproses Ditsamapta' ||
                                              $current_status == 'Diproses Ditbinmas' ||
                                              $current_status == 'Selesai Ditsamapta' ||
                                              $current_status == 'Selesai Ditbinmas');

                                if ($bisa_akses) {
                                    // Hanya tampilkan "diproses" jika belum selesai Ditsamapta
                                    if ($current_status != 'Selesai Ditsamapta') {
                                        echo '<option value="Diproses Ditsamapta" ' . ($current_status == 'Diproses Ditsamapta' ? 'selected' : '') . '>Diproses Ditsamapta</option>';
                                    }
                                    // Hanya bisa selesai jika sudah diproses Ditsamapta
                                    if ($current_status == 'Diproses Ditsamapta') {
                                        echo '<option value="Selesai Ditsamapta" ' . ($current_status == 'Selesai Ditsamapta' ? 'selected' : '') . '>Selesai Ditsamapta</option>';
                                    }
                                    if ($current_status == 'Selesai Ditsamapta') {
                                        echo '<option value="Selesai Ditsamapta" selected disabled>Selesai Ditsamapta</option>';
                                    }
                                } else {
                                    echo '<option value="" disabled selected>Belum dapat diproses (tunggu Ditresnarkoba)</option>';
                                }
                            }

                            // Ditbinmas - Aktor kedua (bisa akses jika sudah diproses_Ditresnarkoba)
                            if (strpos($session_role, 'Ditbinmas') !== false) {
                                // Cek apakah sudah diproses Ditresnarkoba
                                $bisa_akses = ($current_status == 'Diproses Ditresnarkoba' ||
                                              $current_status == 'Diproses Ditsamapta' ||
                                              $current_status == 'Diproses Ditbinmas' ||
                                              $current_status == 'Selesai Ditsamapta' ||
                                              $current_status == 'Selesai Ditbinmas');

                                if ($bisa_akses) {
                                    // Hanya tampilkan "diproses" jika belum selesai Ditbinmas
                                    if ($current_status != 'Selesai Ditbinmas') {
                                        echo '<option value="diproses_Ditbinmas" ' . ($current_status == 'diproses_Ditbinmas' ? 'selected' : '') . '>Diproses Ditbinmas</option>';
                                    }
                                    // Hanya bisa selesai jika sudah diproses Ditbinmas
                                    if ($current_status == 'diproses_Ditbinmas') {
                                        echo '<option value="Selesai Ditbinmas" ' . ($current_status == 'Selesai Ditbinmas' ? 'selected' : '') . '>Selesai Ditbinmas</option>';
                                    }
                                    if ($current_status == 'Selesai Ditbinmas') {
                                        echo '<option value="Selesai Ditbinmas" selected disabled>Selesai Ditbinmas</option>';
                                    }
                                } else {
                                    echo '<option value="" disabled selected>Belum dapat diproses (tunggu Ditresnarkoba)</option>';
                                }
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">
                            <?php if (strpos($session_role, 'Ditsamapta') !== false || strpos($session_role, 'Ditbinmas') !== false): ?>
                                <i class="bi bi-info-circle"></i> Anda dapat mengambil laporan ini setelah Ditresnarkoba memproses terlebih dahulu
                            <?php endif; ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600">Tanggapan/Keterangan</label>
                        <textarea class="form-control" name="tanggapan" rows="4"
                            placeholder="Berikan tanggapan atau keterangan..."
                            required><?php echo htmlspecialchars($laporan['respon']); ?></textarea>
                    </div>

                    <button type="submit" name="update_status" class="btn btn-primary btn-block">
                        <i class="dw dw-diskette"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img src="" id="lightbox-img" alt="Preview">
</div>

<script>
    function openLightbox(imageSrc) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');

        if (lightbox && lightboxImg) {
            lightbox.classList.add('active');
            lightboxImg.src = imageSrc;
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        if (lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }

    // Close lightbox on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });

    // Prevent lightbox close when clicking on image
    document.addEventListener('DOMContentLoaded', function() {
        const lightboxImg = document.getElementById('lightbox-img');
        if (lightboxImg) {
            lightboxImg.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Auto dismiss alert
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);

        // Validate form before submit
        const updateForm = document.querySelector('form[method="POST"]');
        if (updateForm) {
            updateForm.addEventListener('submit', function(e) {
                const statusSelect = this.querySelector('select[name="status_baru"]');
                const tanggapanTextarea = this.querySelector('textarea[name="tanggapan"]');

                if (!statusSelect.value || !tanggapanTextarea.value.trim()) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang diperlukan!');
                    return false;
                }
            });
        }

        // Add confirmation for update status
        const updateBtn = document.querySelector('button[name="update_status"]');
        if (updateBtn) {
            updateBtn.addEventListener('click', function(e) {
                const confirmed = confirm('Apakah Anda yakin ingin mengupdate status laporan ini?');
                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
</script>