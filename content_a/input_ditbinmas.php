<?php
// Get user info
$nama_petugas = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$id_akun = isset($_SESSION['Id_akun']) ? $_SESSION['Id_akun'] : null;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Handle form submission
$success_message = '';
$error_message = '';

if (isset($_POST['submit_kegiatan'])) {
    $judul = mysqli_real_escape_string($db, $_POST['judul']);
    $tanggal = mysqli_real_escape_string($db, $_POST['tanggal']);
    $lokasi = mysqli_real_escape_string($db, $_POST['lokasi']);
    $materi = mysqli_real_escape_string($db, $_POST['materi']);
    $personil = mysqli_real_escape_string($db, $_POST['personil']);
    $pangkat = mysqli_real_escape_string($db, $_POST['pangkat']);
    $status = 'Baru'; // Default status

    // Validasi session
    if ($id_akun === null) {
        $error_message = 'User session tidak valid. Silakan login kembali.';
    } else {
        $query = "INSERT INTO lapbin (Id_akun, judul, status, materi, tanggal, personil, lokasi, petugas, pangkat)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "issssssss", $id_akun, $judul, $status, $materi, $tanggal, $personil, $lokasi, $nama_petugas, $pangkat);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Laporan kegiatan berhasil disimpan!';
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
        background: #1E40AF;
        color: white;
        padding: 20px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        border-bottom: 4px solid #FFD700;
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
        border-color: #1E40AF;
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
    }

    .info-box {
        background: #f8f9fa;
        border-left: 4px solid #FFD700;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box strong {
        color: #1E40AF;
    }

    .btn-submit {
        background: #1E40AF;
        border: none;
        padding: 12px 40px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }

    .btn-submit:hover {
        background: #FFD700;
        color: #1E40AF;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
    }

    .card-header {
        background: #1E40AF !important;
        color: white;
        border-bottom: 3px solid #FFD700;
    }

    .card-header h5 {
        color: white;
        font-weight: 700;
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

    <form method="POST" action="">
        <div class="row">

            <!-- Judul -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📝 Judul Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="judul"
                           placeholder="Contoh: Sosialisasi Bahaya Narkoba di SMA" required>
                </div>
            </div>

            <!-- Tanggal -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📅 Tanggal <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="tanggal"
                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
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
            <div class="col-md-12">
                <div class="form-group">
                    <label>📚 Materi <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="materi" rows="3"
                              placeholder="Jelaskan materi yang disampaikan..." required></textarea>
                </div>
            </div>

            <!-- Personil -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>👥 Jumlah Personil <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="personil"
                           placeholder="Contoh: 5" min="1" required>
                </div>
            </div>

            <!-- Pangkat -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>🎖️ Pangkat Penanggung Jawab <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pangkat"
                           placeholder="Contoh: IPDA" required>
                </div>
            </div>

        </div>

        <!-- Submit Button -->
        <div class="form-group text-right mt-4">
            <button type="reset" class="btn btn-secondary">
                <i class="dw dw-refresh"></i> Reset
            </button>
            <button type="submit" name="submit_kegiatan" class="btn btn-primary btn-submit">
                <i class="dw dw-diskette"></i> Simpan Laporan
            </button>
        </div>
    </form>
</div>

<!-- Recent Reports -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">📚 Laporan Kegiatan Terbaru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Lokasi</th>
                        <th>Personil</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_recent = "SELECT * FROM lapbin
                                     ORDER BY tanggal DESC
                                     LIMIT 5";
                    $result_recent = mysqli_query($db, $query_recent);

                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                            // Tentukan badge status
                            $status_badge = 'secondary';
                            $status_text = $row['status'] ?? 'Baru';

                            switch($status_text) {
                                case 'Baru':
                                    $status_badge = 'warning';
                                    break;
                                case 'Diproses':
                                    $status_badge = 'info';
                                    break;
                                case 'Selesai':
                                    $status_badge = 'success';
                                    break;
                                case 'Ditolak':
                                    $status_badge = 'danger';
                                    break;
                            }
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['judul'], 0, 40)) . (strlen($row['judul']) > 40 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td><span class="badge badge-primary"><?php echo $row['personil']; ?> orang</span></td>
                            <td><span class="badge badge-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="dw dw-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mt-2">Belum ada laporan kegiatan</p>
                            </td>
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