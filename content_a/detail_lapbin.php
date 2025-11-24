<?php
// Include database connection
require_once __DIR__ . '/../config/koneksi.php';

// Get id lapbin from URL
$id_lapbin = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_lapbin == 0) {
    echo '<div class="alert alert-danger">ID Laporan tidak valid!</div>';
    exit;
}

// Get lapbin detail with user info
$query = "SELECT
            lb.*,
            a.Nama AS nama_pelapor,
            a.Email AS email_pelapor,
            a.Nomor_hp AS nomor_hp,
            a.Role AS role_pelapor
          FROM lapbin lb
          LEFT JOIN akun a ON lb.Id_akun = a.Id_akun
          WHERE lb.id_lapbin = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id_lapbin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">Laporan tidak ditemukan!</div>';
    exit;
}

$laporan = mysqli_fetch_assoc($result);

// Status badge class
$status_class = 'secondary';
$status_icon = 'dw-file';
$status_laporan = isset($laporan['status']) ? $laporan['status'] : 'Baru';

if ($status_laporan == 'Baru') {
    $status_class = 'warning';
    $status_icon = 'dw-inbox';
} elseif ($status_laporan == 'Diproses') {
    $status_class = 'info';
    $status_icon = 'dw-loading';
} elseif ($status_laporan == 'Selesai') {
    $status_class = 'success';
    $status_icon = 'dw-checked';
} elseif ($status_laporan == 'Ditolak') {
    $status_class = 'danger';
    $status_icon = 'dw-cancel';
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

    .status-badge-large.badge-warning {
        background-color: #FFD700;
        color: #1E40AF;
    }

    .status-badge-large.badge-info {
        background-color: #1E40AF;
        color: white;
    }

    .status-badge-large.badge-success {
        background-color: #28a745;
        color: white;
    }

    .status-badge-large.badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .status-badge-large.badge-secondary {
        background-color: #6c757d;
        color: white;
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

    .badge-danger {
        background-color: #dc3545;
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
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Detail Laporan Kegiatan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="dash.php?page=laporan-kegiatan">Laporan Kegiatan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="dash.php?page=laporan-Ditbinmas" class="btn btn-secondary">
                <i class="dw dw-left-arrow"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Detail Header -->
<div class="detail-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="detail-title"><?php echo htmlspecialchars($laporan['judul']); ?></div>
            <div class="detail-meta">
                <div class="meta-item">
                    <i class="dw dw-calendar1"></i>
                    <span><?php echo date('d F Y, H:i', strtotime($laporan['tanggal'])); ?> WIB</span>
                </div>
                <div class="meta-item">
                    <i class="dw dw-user1"></i>
                    <span><?php echo htmlspecialchars($laporan['petugas']); ?></span>
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
                <?php echo ucfirst($status_laporan); ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-md-8">

        <!-- Informasi Kegiatan -->
        <div class="detail-card">
            <div class="info-section">
                <h5>📋 Informasi Kegiatan</h5>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">📅 Tanggal Kegiatan</div>
                        <div class="info-value"><?php echo date('d F Y, H:i', strtotime($laporan['tanggal'])); ?> WIB</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">📍 Lokasi</div>
                        <div class="info-value"><?php echo htmlspecialchars($laporan['lokasi']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">👥 Jumlah Personil</div>
                        <div class="info-value"><?php echo $laporan['personil']; ?> orang</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">🎖️ Pangkat</div>
                        <div class="info-value"><?php echo htmlspecialchars($laporan['pangkat']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materi Kegiatan -->
        <div class="detail-card">
            <div class="info-section">
                <h5>📚 Materi Kegiatan</h5>
                <div class="content-box">
                    <?php echo nl2br(htmlspecialchars($laporan['materi'])); ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column -->
    <div class="col-md-4">

        <!-- Informasi Petugas -->
        <div class="detail-card">
            <div class="info-section">
                <h5>👤 Informasi Petugas</h5>

                <div class="info-item mb-3">
                    <div class="info-label">Nama Petugas</div>
                    <div class="info-value"><?php echo htmlspecialchars($laporan['petugas']); ?></div>
                </div>

                <div class="info-item mb-3">
                    <div class="info-label">Pangkat</div>
                    <div class="info-value"><?php echo htmlspecialchars($laporan['pangkat']); ?></div>
                </div>

                <?php if (!empty($laporan['role_pelapor'])): ?>
                <div class="info-item mb-3">
                    <div class="info-label">Unit/Bagian</div>
                    <div class="info-value"><?php echo htmlspecialchars($laporan['role_pelapor']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($laporan['email_pelapor'])): ?>
                <div class="info-item mb-3">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($laporan['email_pelapor']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($laporan['nomor_hp'])): ?>
                <div class="info-item mb-3">
                    <div class="info-label">Nomor HP</div>
                    <div class="info-value"><?php echo htmlspecialchars($laporan['nomor_hp']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status & Timeline -->
        <div class="detail-card">
            <div class="info-section">
                <h5>📊 Status Laporan</h5>

                <div class="info-item">
                    <div class="info-label">Status Saat Ini</div>
                    <div class="info-value">
                        <span class="badge badge-<?php echo $status_class; ?>">
                            <i class="dw <?php echo $status_icon; ?>"></i>
                            <?php echo ucfirst($status_laporan); ?>
                        </span>
                    </div>
                </div>

                <div class="info-item mt-3">
                    <div class="info-label">Tanggal Dibuat</div>
                    <div class="info-value"><?php echo date('d F Y, H:i', strtotime($laporan['tanggal'])); ?></div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Auto scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
</script>
