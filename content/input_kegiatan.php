<?php
// Get user info
$nama_petugas = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Handle form submission
$success_message = '';
$error_message = '';

if (isset($_POST['submit_kegiatan'])) {
    $tanggal = mysqli_real_escape_string($db, $_POST['tanggal']);
    $no_surat = mysqli_real_escape_string($db, $_POST['no_surat']);
    $kegiatan = mysqli_real_escape_string($db, $_POST['kegiatan']);
    $lokasi = mysqli_real_escape_string($db, $_POST['lokasi']);
    $materi = mysqli_real_escape_string($db, $_POST['materi']);
    
    // Handle file upload (optional)
    $file_laporan = '';
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $target_dir = "uploads/kegiatan/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_laporan = basename($_FILES['file_laporan']['name']);
        move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target_dir . $file_laporan);
    }
    
    $query = "INSERT INTO kegiatan_ditbinmas (tanggal, no_surat, kegiatan, lokasi, materi, file_laporan, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $tanggal, $no_surat, $kegiatan, $lokasi, $materi, $file_laporan);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = 'Kegiatan berhasil disimpan!';
    } else {
        $error_message = 'Gagal menyimpan kegiatan: ' . mysqli_error($db);
    }
}
?>

<style>
    .form-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .form-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .form-card-header h4 {
        margin: 0;
        font-weight: 700;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #667eea;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box strong {
        color: #667eea;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 40px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>📝 Input Kegiatan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="dash.php?page=laporan-kegiatan">Laporan Kegiatan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="dash.php?page=laporan-kegiatan" class="btn btn-secondary">
                <i class="dw dw-left-arrow"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="dw dw-checked"></i> Berhasil!</strong> <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="dw dw-warning"></i> Error!</strong> <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Info Box -->
<div class="info-box">
    <strong>ℹ️ Informasi:</strong> Kegiatan akan tercatat atas nama <strong><?php echo htmlspecialchars($nama_petugas); ?></strong> 
    dari unit <strong><?php echo ucfirst($role); ?></strong>
</div>

<!-- Form Card -->
<div class="form-card">
    <div class="form-card-header">
        <h4>📋 Form Input Kegiatan</h4>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row">
            
            <!-- Tanggal -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📅 Tanggal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <!-- No Surat -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📄 No Surat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_surat" 
                           placeholder="Contoh: B/001/XI/2025" required>
                </div>
            </div>

            <!-- Kegiatan -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📝 Nama Kegiatan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="kegiatan" rows="3" 
                              placeholder="Jelaskan kegiatan yang dilakukan..." required></textarea>
                </div>
            </div>

            <!-- Lokasi -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📍 Lokasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lokasi" 
                           placeholder="Contoh: SMA Negeri 1 Medan" required>
                </div>
            </div>

            <!-- Materi -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📚 Materi</label>
                    <input type="text" class="form-control" name="materi" 
                           placeholder="Contoh: Bahaya narkoba">
                </div>
            </div>

            <!-- File Laporan -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📎 File Laporan (Optional)</label>
                    <input type="file" class="form-control" name="file_laporan" 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="form-text text-muted">
                        Format: PDF, DOC, DOCX, JPG, PNG (Max 5MB)
                    </small>
                </div>
            </div>

        </div>

        <!-- Submit Button -->
        <div class="form-group text-right mt-4">
            <button type="reset" class="btn btn-secondary">
                <i class="dw dw-refresh"></i> Reset
            </button>
            <button type="submit" name="submit_kegiatan" class="btn btn-primary btn-submit">
                <i class="dw dw-diskette"></i> Simpan Kegiatan
            </button>
        </div>
    </form>
</div>

<!-- Recent Reports -->
<div class="card">
    <div class="card-header" style="background: #f8f9fa;">
        <h5 class="mb-0">📚 Kegiatan Terbaru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Surat</th>
                        <th>Kegiatan</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_recent = "SELECT * FROM kegiatan_ditbinmas 
                                     ORDER BY created_at DESC 
                                     LIMIT 5";
                    $result_recent = mysqli_query($db, $query_recent);
                    
                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td><?php echo htmlspecialchars($row['no_surat']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['kegiatan'], 0, 40)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada kegiatan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Auto dismiss alert
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>