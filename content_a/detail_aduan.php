<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug session info
error_log("=== DEBUG DETAIL ADUAN ===");
error_log("Session Id_akun: " . (isset($_SESSION['Id_akun']) ? $_SESSION['Id_akun'] : 'NOT SET'));
error_log("Session nama: " . (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'NOT SET'));
error_log("Session role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));
error_log("All session data: " . print_r($_SESSION, true));

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
    $status_baru = mysqli_real_escape_string($db, $_POST['status_baru']);
    $tanggapan = mysqli_real_escape_string($db, $_POST['respon']);
    $petugas = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin';
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

    // Ambil ID akun dari session
    $id_akun = isset($_SESSION['Id_akun']) ? $_SESSION['Id_akun'] : null;

    // Validasi session
    if ($id_akun === null) {
        $error_message = 'User session not valid. Silakan login kembali.';
    } else {
        // Build timeline JSON
        $timeline_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'status_dari' => $laporan['status'],
            'status_ke' => $status_baru,
            'diproses_oleh' => $sedang_diproses_oleh,
            'nama_petugas' => $petugas,
            'tanggapan' => $tanggapan
        ];

        // Get existing timeline or create new array
        $existing_timeline = !empty($laporan['timeline_json']) ? json_decode($laporan['timeline_json'], true) : [];
        if (!is_array($existing_timeline)) {
            $existing_timeline = [];
        }

        // Add new entry
        $existing_timeline[] = $timeline_entry;
        $timeline_json = json_encode($existing_timeline, JSON_UNESCAPED_UNICODE);

        // UPDATE status dan timeline pada lapmas
        $query_update = "UPDATE lapmas
                        SET status = ?, timeline_json = ?
                        WHERE id_lapmas = ?";

        $stmt_update = mysqli_prepare($db, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $status_baru, $timeline_json, $id_laporan);

        if (mysqli_stmt_execute($stmt_update)) {
            // Cek apakah sudah ada respon yang sama dalam 1 menit terakhir (untuk mencegah duplikasi)
            $query_check = "SELECT COUNT(*) as count FROM respon
                           WHERE id_lapmas = ?
                           AND a_respon = ?
                           AND respon = ?
                           AND tanggal_respon >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
            $stmt_check = mysqli_prepare($db, $query_check);
            mysqli_stmt_bind_param($stmt_check, "iis", $id_laporan, $id_akun, $tanggapan);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $check_data = mysqli_fetch_assoc($result_check);

            // Hanya insert jika tidak ada duplikasi
            if ($check_data['count'] == 0) {
                // Insert respon ke tabel respon
                $query_insert = "INSERT INTO respon (id_lapmas, respon, a_respon, tanggal_respon)
                                VALUES (?, ?, ?, NOW())";
                $stmt_insert = mysqli_prepare($db, $query_insert);
                mysqli_stmt_bind_param($stmt_insert, "isi", $id_laporan, $tanggapan, $id_akun);

                if (mysqli_stmt_execute($stmt_insert)) {
                    $success_message = 'Status dan tanggapan berhasil disimpan!';
                } else {
                    $success_message = 'Status berhasil diupdate, tapi gagal menyimpan tanggapan: ' . mysqli_error($db);
                }
            } else {
                $success_message = 'Status berhasil diupdate! (Tanggapan sudah ada sebelumnya)';
            }

            // Refresh data laporan
            $query_refresh = "SELECT
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

            $stmt_refresh = mysqli_prepare($db, $query_refresh);
            mysqli_stmt_bind_param($stmt_refresh, "i", $id_laporan);
            mysqli_stmt_execute($stmt_refresh);
            $result_refresh = mysqli_stmt_get_result($stmt_refresh);
            $laporan = mysqli_fetch_assoc($result_refresh);

            // Simpan pesan di session untuk ditampilkan setelah redirect
            $_SESSION['form_success'] = true;

            // Redirect menggunakan JavaScript untuk mencegah form resubmission
            echo '<script>window.location.href = "dash.php?page=detail-pengaduan&id=' . $id_laporan . '";</script>';
            exit;
        } else {
            $error_message = 'Gagal mengupdate status: ' . mysqli_error($db);
        }
    }
}

// Handle success message dari session
if (isset($_SESSION['form_success']) && $_SESSION['form_success'] === true) {
    $success_message = 'Status dan tanggapan berhasil disimpan!';
    unset($_SESSION['form_success']); // Hapus setelah ditampilkan
}

// Status badge class
$status_class = 'secondary';
$status_icon = 'dw-file';
$status_laporan = isset($laporan['status']) ? $laporan['status'] : 'Baru';

if ($status_laporan == 'Baru') {
    $status_class = 'warning';
    $status_icon = 'dw-inbox';
} elseif (strpos($status_laporan, 'Diproses Ditresnarkoba') !== false) {
    $status_class = 'info';
    $status_icon = 'dw-loading';
} elseif (strpos($status_laporan, 'selesai') !== false) {
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
        justify-content: flex-end;
        margin-right: 20px;
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

    /* Chat Bubble Styles */
    .chat-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 10px;
    }

    .chat-container::-webkit-scrollbar {
        width: 8px;
    }

    .chat-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .chat-container::-webkit-scrollbar-thumb {
        background: #1E40AF;
        border-radius: 10px;
    }

    .chat-container::-webkit-scrollbar-thumb:hover {
        background: #FFD700;
    }

    .chat-bubble {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #1E40AF;
        transition: all 0.3s ease;
    }

    .chat-bubble:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateX(5px);
    }

    .chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .chat-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #1E40AF;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 10px;
        font-size: 1.2rem;
    }

    .chat-message {
        background: white;
        padding: 12px 15px;
        border-radius: 10px;
        line-height: 1.6;
        color: #495057;
        font-size: 0.95rem;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .badge-primary {
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
                <?php echo ucfirst(isset($laporan['status']) ? $laporan['status'] : 'Baru'); ?>
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
                <?php
                // Query untuk mengambil semua respon (diurutkan dari terlama ke terbaru)
                $query_respon = "SELECT DISTINCT
                                    r.id_respon,
                                    r.respon,
                                    r.a_respon,
                                    r.tanggal_respon,
                                    a.Nama as nama_petugas,
                                    a.Role as role_petugas
                                FROM respon r
                                LEFT JOIN akun a ON r.a_respon = a.Id_akun
                                WHERE r.id_lapmas = ?
                                GROUP BY r.id_respon
                                ORDER BY r.tanggal_respon ASC";

                $stmt_respon = mysqli_prepare($db, $query_respon);
                mysqli_stmt_bind_param($stmt_respon, "i", $id_laporan);
                mysqli_stmt_execute($stmt_respon);
                $result_respon = mysqli_stmt_get_result($stmt_respon);

                $has_respon = false;
                ?>

                <div class="chat-container">
                    <?php
                    while ($resp = mysqli_fetch_assoc($result_respon)):
                        if (!empty($resp['respon'])):
                            $has_respon = true;

                            // Tentukan warna badge berdasarkan role
                            $role_badge_class = 'badge-secondary';
                            $role_display = $resp['role_petugas'];

                            if ($resp['role_petugas'] == 'Ditresnarkoba') {
                                $role_badge_class = 'badge-danger';
                            } elseif ($resp['role_petugas'] == 'Ditsamapta') {
                                $role_badge_class = 'badge-primary';
                            } elseif ($resp['role_petugas'] == 'Ditbinmas') {
                                $role_badge_class = 'badge-success';
                            }
                    ?>
                        <div class="chat-bubble">
                            <div class="chat-header">
                                <div class="d-flex align-items-center">
                                    <div class="chat-avatar">
                                        <i class="dw dw-user1"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($resp['nama_petugas'] ?? 'Petugas'); ?></strong>
                                        <span class="badge <?php echo $role_badge_class; ?> ml-2"><?php echo htmlspecialchars($role_display); ?></span>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?php
                                    if ($resp['tanggal_respon'] && $resp['tanggal_respon'] != '0000-00-00 00:00:00') {
                                        echo date('d M Y, H:i', strtotime($resp['tanggal_respon'])) . ' WIB';
                                    }
                                    ?>
                                </small>
                            </div>
                            <div class="chat-message">
                                <?php echo nl2br(htmlspecialchars($resp['respon'])); ?>
                            </div>
                        </div>
                    <?php
                        endif;
                    endwhile;

                    if (!$has_respon):
                    ?>
                        <div class="no-tanggapan">
                            <i class="dw dw-chat" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mb-0 mt-2">Belum ada tanggapan dari petugas</p>
                        </div>
                    <?php endif; ?>
                </div>
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
                        <div class="mt-2">Status: <span class="badge badge-warning"><?php echo isset($laporan['status']) ? htmlspecialchars($laporan['status']) : 'Baru'; ?></span></div>
                        <?php echo "<script>console.log('status: " . addslashes(isset($laporan['status']) ? $laporan['status'] : 'Baru') . "');</script>";?>
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
                            $current_status = isset($laporan['status']) ? $laporan['status'] : 'Baru';
                            echo "<script>console.log('current_status: " . addslashes($current_status) . "');</script>";

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
                        <textarea class="form-control" name="respon" rows="4"
                            placeholder="Berikan tanggapan atau keterangan..."
                            required><?php echo htmlspecialchars(isset($laporan['respon']) ? $laporan['respon'] : ''); ?></textarea>
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

        // Debug: Log current session Id_akun to console
        console.log('Current session Id_akun:', <?php echo json_encode($_SESSION['Id_akun'] ?? null); ?>);

        // Auto scroll to latest chat bubble
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            const lastBubble = chatContainer.querySelector('.chat-bubble:last-child');
            if (lastBubble) {
                lastBubble.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }
        }
    });
</script>
