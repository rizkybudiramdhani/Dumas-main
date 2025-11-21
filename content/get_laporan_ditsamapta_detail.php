<?php
session_start();
require_once('../config/koneksi.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    echo '<div class="alert alert-danger">ID tidak valid!</div>';
    exit;
}

$query = "SELECT * FROM laporan_samapta WHERE id_laporan = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">Laporan tidak ditemukan!</div>';
    exit;
}

$row = mysqli_fetch_assoc($result);

// Status badge styling
$status_bg = '#1a1f3a';
if ($row['status_verifikasi'] == 'baru') $status_bg = '#dc2626';
elseif ($row['status_verifikasi'] == 'diproses') $status_bg = '#ea580c';
elseif ($row['status_verifikasi'] == 'ditindaklanjuti') $status_bg = '#2563eb';
elseif ($row['status_verifikasi'] == 'selesai') $status_bg = '#16a34a';
?>

<style>
    .detail-label {
        color: #1a1f3a;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .detail-value {
        color: #495057;
        font-weight: 500;
        margin-top: 5px;
    }
    .detail-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #1a1f3a;
        margin-bottom: 15px;
    }
</style>

<div class="row">
    <div class="col-md-6">
        <p class="detail-label">📅 Tanggal Lapor:</p>
        <p class="detail-value"><?php echo date('d F Y H:i', strtotime($row['tanggal_lapor'])); ?> WIB</p>
    </div>
    <div class="col-md-6">
        <p class="detail-label">📊 Status:</p>
        <p class="detail-value">
            <span style="background: <?php echo $status_bg; ?>; color: white; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <?php echo ucfirst($row['status_verifikasi']); ?>
            </span>
        </p>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-4">
        <p class="detail-label">👤 Nama Petugas:</p>
        <p class="detail-value"><?php echo htmlspecialchars($row['pangkat_petugas'] . ' ' . $row['nama_petugas']); ?></p>
    </div>
    <div class="col-md-4">
        <p class="detail-label">🔢 NRP:</p>
        <p class="detail-value"><?php echo htmlspecialchars($row['nrp_petugas']); ?></p>
    </div>
    <div class="col-md-4">
        <p class="detail-label">🎯 Jenis Kegiatan:</p>
        <p class="detail-value"><?php echo htmlspecialchars($row['jenis_kegiatan']); ?></p>
    </div>
</div>

<hr style="border-color: #e5e7eb; margin: 20px 0;">

<div class="mb-3">
    <p class="detail-label">📍 Lokasi:</p>
    <p class="detail-value"><?php echo htmlspecialchars($row['lokasi']); ?></p>
</div>

<div class="detail-section">
    <p class="detail-label">📝 Kronologi:</p>
    <p class="detail-value"><?php echo nl2br(htmlspecialchars($row['kronologi'])); ?></p>
</div>

<div class="row">
    <div class="col-md-6">
        <p class="detail-label">👥 Jumlah Tersangka:</p>
        <p class="detail-value"><?php echo $row['jumlah_tersangka']; ?> orang</p>
    </div>
    <div class="col-md-6">
        <p class="detail-label">📦 Barang Bukti:</p>
        <p class="detail-value"><?php echo htmlspecialchars($row['rincian_barang_bukti']); ?></p>
    </div>
</div>

<?php if (!empty($row['file_bukti'])): ?>
<div class="mb-3">
    <p class="detail-label">📎 File Bukti:</p>
    <div class="mt-2">
        <?php
        $file_ext = pathinfo($row['file_bukti'], PATHINFO_EXTENSION);
        if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif'])):
        ?>
            <img src="uploads/bukti_ditsamapta/<?php echo htmlspecialchars($row['file_bukti']); ?>"
                 class="img-fluid" style="max-height: 300px; border-radius: 10px; border: 3px solid #1a1f3a;">
        <?php else: ?>
            <a href="uploads/bukti_ditsamapta/<?php echo htmlspecialchars($row['file_bukti']); ?>"
               target="_blank" class="btn btn-sm" style="background: #1a1f3a; color: white; font-weight: 600;">
                <i class="fa fa-download"></i> Download File
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($row['tanggapan_resnarkoba'])): ?>
<hr style="border-color: #e5e7eb; margin: 20px 0;">
<div class="mb-3 detail-section" style="border-left-color: #16a34a;">
    <p class="detail-label" style="color: #16a34a;">💬 Tanggapan Ditresnarkoba:</p>
    <p class="detail-value"><?php echo nl2br(htmlspecialchars($row['tanggapan_resnarkoba'])); ?></p>
    <small style="color: #6c757d; font-weight: 500;">
        <i class="dw dw-calendar1"></i> Tanggal: <?php echo date('d F Y H:i', strtotime($row['tanggal_tangapan'])); ?> WIB
    </small>
</div>
<?php endif; ?>

<hr style="border-color: #e5e7eb; margin: 20px 0;">
<small style="color: #6c757d; font-weight: 600;">
    <i class="dw dw-building"></i> Asal Laporan: <?php echo htmlspecialchars($row['asal_laporan']); ?>
</small>
