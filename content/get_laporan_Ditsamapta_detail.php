<?php
// Include database connection
require_once '../config/koneksi.php';

// Get ID from request
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo '<div class="alert alert-danger">ID Laporan tidak valid</div>';
    exit;
}

// Query to get detail laporan
$query = "SELECT * FROM lapsam WHERE id_lapsam = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Format status badge
    $status_class = '';
    $status_text = ucfirst($row['status']);

    switch(strtolower($row['status'])) {
        case 'baru':
            $status_class = 'badge-danger';
            break;
        case 'diproses':
            $status_class = 'badge-warning';
            break;
        case 'ditindaklanjuti':
            $status_class = 'badge-info';
            break;
        case 'selesai':
            $status_class = 'badge-success';
            break;
        default:
            $status_class = 'badge-secondary';
    }
?>

<style>
    .detail-row {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 700;
        color: #1a1f3a;
        margin-bottom: 5px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        color: #495057;
        font-size: 1rem;
        line-height: 1.6;
    }

    .status-badge-large {
        font-size: 1rem;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .info-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-top: 15px;
    }

    .icon-text {
        display: inline-block;
        margin-right: 8px;
    }
</style>

<div class="container-fluid">
    <!-- Header Info -->
    <div class="row mb-3">
        <div class="col-md-8">
            <h5 style="color: #1a1f3a; font-weight: 700;">
                <i class="icon-copy dw dw-notebook icon-text"></i>
                <?php echo htmlspecialchars($row['judul']); ?>
            </h5>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge status-badge-large <?php echo $status_class; ?>">
                <?php echo $status_text; ?>
            </span>
        </div>
    </div>

    <hr style="border-top: 2px solid #1a1f3a; margin: 20px 0;">

    <!-- Detail Information -->
    <div class="detail-row">
        <div class="detail-label">
            <i class="icon-copy dw dw-calendar1 icon-text"></i> Tanggal Kegiatan
        </div>
        <div class="detail-value">
            <?php echo date('d F Y, H:i', strtotime($row['tanggal'])); ?> WIB
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">
            <i class="icon-copy dw dw-user icon-text"></i> Petugas
        </div>
        <div class="detail-value">
            <strong><?php echo htmlspecialchars($row['pangkat']); ?></strong> <?php echo htmlspecialchars($row['petugas']); ?>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">
            <i class="icon-copy dw dw-map icon-text"></i> Lokasi
        </div>
        <div class="detail-value">
            <?php echo htmlspecialchars($row['lokasi']); ?>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">
            <i class="icon-copy dw dw-group icon-text"></i> Jumlah Personil
        </div>
        <div class="detail-value">
            <strong><?php echo $row['personil']; ?></strong> Orang
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">
            <i class="icon-copy dw dw-edit-file icon-text"></i> Uraian Kegiatan
        </div>
        <div class="detail-value info-section">
            <?php echo nl2br(htmlspecialchars($row['kegiatan'])); ?>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="info-section" style="background: #fff3cd; border-left: 4px solid #ffc107;">
        <small>
            <strong><i class="icon-copy dw dw-info icon-text"></i> Informasi Laporan:</strong><br>
            ID Laporan: <strong>#<?php echo str_pad($row['id_lapsam'], 5, '0', STR_PAD_LEFT); ?></strong><br>
            Dibuat pada: <?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?>
        </small>
    </div>
</div>

<?php
} else {
    echo '<div class="alert alert-danger">
            <i class="icon-copy dw dw-warning"></i>
            Laporan tidak ditemukan atau telah dihapus
          </div>';
}

mysqli_stmt_close($stmt);
?>
