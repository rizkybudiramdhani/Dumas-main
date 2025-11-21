<?php
session_start();
require_once('../config/koneksi.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    echo '<div class="alert alert-danger">ID tidak valid!</div>';
    exit;
}

$query = "SELECT * FROM kegiatan_ditbinmas WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">Kegiatan tidak ditemukan!</div>';
    exit;
}

$row = mysqli_fetch_assoc($result);
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
        <p class="detail-label">📅 Tanggal:</p>
        <p class="detail-value"><?php echo date('d F Y', strtotime($row['tanggal'])); ?></p>
    </div>
    <div class="col-md-6">
        <p class="detail-label">📄 No. Surat:</p>
        <p class="detail-value"><?php echo htmlspecialchars($row['no_surat']); ?></p>
    </div>
</div>

<hr style="border-color: #e5e7eb; margin: 20px 0;">

<div class="mb-3">
    <p class="detail-label">🎯 Kegiatan:</p>
    <p class="detail-value"><?php echo htmlspecialchars($row['kegiatan']); ?></p>
</div>

<div class="mb-3">
    <p class="detail-label">📍 Lokasi:</p>
    <p class="detail-value"><?php echo htmlspecialchars($row['lokasi']); ?></p>
</div>

<div class="detail-section">
    <p class="detail-label">📚 Materi:</p>
    <p class="detail-value"><?php echo nl2br(htmlspecialchars($row['materi'])); ?></p>
</div>

<div class="row">
    <div class="col-md-6">
        <p class="detail-label">👥 Jumlah Peserta:</p>
        <p class="detail-value"><?php echo $row['jumlah_peserta']; ?> orang</p>
    </div>
    <div class="col-md-6">
        <p class="detail-label">👤 Narasumber:</p>
        <p class="detail-value"><?php echo htmlspecialchars($row['narasumber']); ?></p>
    </div>
</div>

<?php if (!empty($row['keterangan'])): ?>
<hr style="border-color: #e5e7eb; margin: 20px 0;">
<div class="detail-section">
    <p class="detail-label">📝 Keterangan:</p>
    <p class="detail-value"><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></p>
</div>
<?php endif; ?>

<?php if (!empty($row['file_dokumentasi'])): ?>
<hr style="border-color: #e5e7eb; margin: 20px 0;">
<div class="mb-3">
    <p class="detail-label">📎 Dokumentasi:</p>
    <div class="mt-2">
        <?php
        $file_ext = pathinfo($row['file_dokumentasi'], PATHINFO_EXTENSION);
        if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif'])):
        ?>
            <img src="uploads/kegiatan/<?php echo htmlspecialchars($row['file_dokumentasi']); ?>"
                 class="img-fluid" style="max-height: 300px; border-radius: 10px; border: 3px solid #1a1f3a;">
        <?php else: ?>
            <a href="uploads/kegiatan/<?php echo htmlspecialchars($row['file_dokumentasi']); ?>"
               target="_blank" class="btn btn-sm" style="background: #1a1f3a; color: white; font-weight: 600;">
                <i class="fa fa-download"></i> Download File
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<hr style="border-color: #e5e7eb; margin: 20px 0;">
<small style="color: #6c757d; font-weight: 600;">
    <i class="dw dw-calendar1"></i> Dibuat: <?php echo date('d F Y H:i', strtotime($row['created_at'])); ?> WIB
</small>
