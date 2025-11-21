<?php
// Get user info
$nama_petugas = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Handle form submission
$success_message = '';
$error_message = '';

if (isset($_POST['submit_laporan'])) {
    $tanggal_lapor = mysqli_real_escape_string($db, $_POST['tanggal_lapor']);
    $nama_petugas_input = mysqli_real_escape_string($db, $_POST['nama_petugas']);
    $nrp_petugas = mysqli_real_escape_string($db, $_POST['nrp_petugas']);
    $pangkat_petugas = mysqli_real_escape_string($db, $_POST['pangkat_petugas']);
    $jenis_kegiatan = mysqli_real_escape_string($db, $_POST['jenis_kegiatan']);
    $kronologi = mysqli_real_escape_string($db, $_POST['kronologi']);
    $lokasi = mysqli_real_escape_string($db, $_POST['lokasi']);
    $jumlah_tersangka = (int)$_POST['jumlah_tersangka'];
    $rincian_barang_bukti = mysqli_real_escape_string($db, $_POST['rincian_barang_bukti']);
    $asal_laporan = 'Ditsamapta Internal';
    
    // Handle file upload
    $file_bukti = '';
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
        $target_dir = "uploads/bukti_ditsamapta/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['file_bukti']['name'], PATHINFO_EXTENSION);
        $file_bukti = 'bukti_' . uniqid() . '_' . time() . '.' . $file_extension;
        
        if (!move_uploaded_file($_FILES['file_bukti']['tmp_name'], $target_dir . $file_bukti)) {
            $error_message = 'Gagal upload file bukti!';
            $file_bukti = '';
        }
    }
    
    if (empty($error_message)) {
        $query = "INSERT INTO laporan_samapta 
                  (tanggal_lapor, nama_petugas, nrp_petugas, pangkat_petugas, jenis_kegiatan, 
                   kronologi, lokasi, jumlah_tersangka, rincian_barang_bukti, file_bukti, 
                   status_verifikasi, asal_laporan) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'baru', ?)";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssss", 
            $tanggal_lapor, $nama_petugas_input, $nrp_petugas, $pangkat_petugas, $jenis_kegiatan,
            $kronologi, $lokasi, $jumlah_tersangka, $rincian_barang_bukti, $file_bukti, $asal_laporan
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Laporan berhasil disimpan!';
        } else {
            $error_message = 'Gagal menyimpan laporan: ' . mysqli_error($db);
        }
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
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
        border-color: #43e97b;
        box-shadow: 0 0 0 0.2rem rgba(67, 233, 123, 0.25);
    }

    .info-box {
        background: #e7f9f5;
        border-left: 4px solid #43e97b;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box strong {
        color: #43e97b;
    }

    .btn-submit {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        border: none;
        padding: 12px 40px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(67, 233, 123, 0.4);
        color: white;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>📝 Input Laporan Ditsamapta</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="dash.php?page=laporan-ditsamapta">Laporan Ditsamapta</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="dash.php?page=laporan-ditsamapta" class="btn btn-secondary">
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
    <strong>ℹ️ Informasi:</strong> Laporan akan tercatat atas nama <strong><?php echo htmlspecialchars($nama_petugas); ?></strong> 
    dari unit <strong>Ditsamapta</strong>
</div>

<!-- Form Card -->
<div class="form-card">
    <div class="form-card-header">
        <h4>📋 Form Laporan Ditsamapta</h4>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row">
            
            <!-- Tanggal Lapor -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📅 Tanggal Lapor <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="tanggal_lapor" 
                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>
            </div>

            <!-- Jenis Kegiatan -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>🎯 Jenis Kegiatan <span class="text-danger">*</span></label>
                    <select class="form-control" name="jenis_kegiatan" required>
                        <option value="">-- Pilih Jenis Kegiatan --</option>
                        <option value="Patroli">Patroli</option>
                        <option value="Pengawalan">Pengawalan</option>
                        <option value="Pengamanan">Pengamanan</option>
                        <option value="Penangkapan">Penangkapan</option>
                        <option value="Razia">Razia</option>
                        <option value="Operasi Gabungan">Operasi Gabungan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Nama Petugas -->
            <div class="col-md-4">
                <div class="form-group">
                    <label>👤 Nama Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_petugas" 
                           value="<?php echo htmlspecialchars($nama_petugas); ?>" required>
                </div>
            </div>

            <!-- NRP -->
            <div class="col-md-4">
                <div class="form-group">
                    <label>🔢 NRP Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nrp_petugas" 
                           placeholder="Contoh: 85010123" required>
                </div>
            </div>

            <!-- Pangkat -->
            <div class="col-md-4">
                <div class="form-group">
                    <label>⭐ Pangkat <span class="text-danger">*</span></label>
                    <select class="form-control" name="pangkat_petugas" required>
                        <option value="">-- Pilih Pangkat --</option>
                        <option value="Bripka">Bripka</option>
                        <option value="Briptu">Briptu</option>
                        <option value="Brigadir">Brigadir</option>
                        <option value="Aipda">Aipda</option>
                        <option value="Aiptu">Aiptu</option>
                        <option value="Ipda">Ipda</option>
                        <option value="Iptu">Iptu</option>
                        <option value="AKP">AKP</option>
                        <option value="Kompol">Kompol</option>
                        <option value="AKBP">AKBP</option>
                        <option value="Kombes">Kombes</option>
                    </select>
                </div>
            </div>

            <!-- Lokasi -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📍 Lokasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lokasi" 
                           placeholder="Contoh: Jl. Gatot Subroto, Medan" required>
                </div>
            </div>

            <!-- Kronologi -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📝 Kronologi Kejadian <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="kronologi" rows="5" 
                              placeholder="Jelaskan kronologi kejadian secara detail..." required></textarea>
                    <small class="form-text text-muted">
                        Contoh: No. Sprint: SPRINT-001. Kami melakukan patroli rutin...
                    </small>
                </div>
            </div>

            <!-- Jumlah Tersangka -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>👥 Jumlah Tersangka</label>
                    <input type="number" class="form-control" name="jumlah_tersangka" 
                           value="0" min="0">
                </div>
            </div>

            <!-- File Bukti -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📎 File Bukti (Optional)</label>
                    <input type="file" class="form-control" name="file_bukti" 
                           accept="image/*,.pdf,.doc,.docx">
                    <small class="form-text text-muted">
                        Format: JPG, PNG, PDF, DOC, DOCX (Max 5MB)
                    </small>
                </div>
            </div>

            <!-- Rincian Barang Bukti -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📦 Rincian Barang Bukti</label>
                    <textarea class="form-control" name="rincian_barang_bukti" rows="3" 
                              placeholder="Contoh: 20 gram sabu-sabu, 1 unit HP, 1 unit motor..."></textarea>
                </div>
            </div>

        </div>

        <!-- Submit Button -->
        <div class="form-group text-right mt-4">
            <button type="reset" class="btn btn-secondary">
                <i class="dw dw-refresh"></i> Reset
            </button>
            <button type="submit" name="submit_laporan" class="btn btn-primary btn-submit">
                <i class="dw dw-diskette"></i> Simpan Laporan
            </button>
        </div>
    </form>
</div>

<!-- Recent Reports -->
<div class="card">
    <div class="card-header" style="background: #f8f9fa;">
        <h5 class="mb-0">📚 Laporan Terbaru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_recent = "SELECT * FROM laporan_samapta 
                                     ORDER BY tanggal_lapor DESC 
                                     LIMIT 5";
                    $result_recent = mysqli_query($db, $query_recent);
                    
                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_lapor'])); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis_kegiatan']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['lokasi'], 0, 30)) . '...'; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['status_verifikasi'] == 'baru' ? 'warning' : 'info'; ?>">
                                    <?php echo ucfirst($row['status_verifikasi']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada laporan</td>
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
    
    // File size validation
    document.querySelector('input[name="file_bukti"]').addEventListener('change', function() {
        if (this.files[0].size > 5242880) { // 5MB
            alert('File terlalu besar! Maksimal 5MB');
            this.value = '';
        }
    });
</script>