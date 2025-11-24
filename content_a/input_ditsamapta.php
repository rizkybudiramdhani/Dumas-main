<?php
// Get user info
$nama_petugas = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$id_akun = isset($_SESSION['Id_akun']) ? $_SESSION['Id_akun'] : 0;

// Handle form submission
$success_message = '';
$error_message = '';

if (isset($_POST['submit_laporan'])) {
    $judul = mysqli_real_escape_string($db, $_POST['judul']);
    $tanggal = mysqli_real_escape_string($db, $_POST['tanggal']);
    $nama_petugas_input = mysqli_real_escape_string($db, $_POST['nama_petugas']);
    $pangkat_petugas = mysqli_real_escape_string($db, $_POST['pangkat_petugas']);
    $kegiatan = mysqli_real_escape_string($db, $_POST['kegiatan']);
    $lokasi = mysqli_real_escape_string($db, $_POST['lokasi']);
    $personil = (int)$_POST['personil'];
    $status = 'Baru';

    if (empty($error_message)) {
        $query = "INSERT INTO lapsam
                  (Id_akun, judul, status, kegiatan, tanggal, personil, lokasi, petugas, pangkat)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "isssiisss",
            $id_akun, $judul, $status, $kegiatan, strtotime($tanggal), $personil, $lokasi, $nama_petugas_input, $pangkat_petugas
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
        background: #1a1f3a;
        color: white;
        padding: 20px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        border-bottom: 4px solid #FFD700;
    }

    .form-card-header h4 {
        margin: 0;
        font-weight: 700;
        color: #FFD700;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-control:focus {
        border-color: #1a1f3a;
        box-shadow: 0 0 0 0.2rem rgba(26, 31, 58, 0.25);
    }

    .info-box {
        background: #f8f9fa;
        border-left: 4px solid #1a1f3a;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box strong {
        color: #1a1f3a;
    }

    .btn-submit {
        background: #1a1f3a;
        border: none;
        padding: 12px 40px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: white;
    }

    .btn-submit:hover {
        background: #FFD700;
        border-color: #FFD700;
        color: #1a1f3a;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
    }

    .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #1a1f3a;
    }

    .card-header h5 {
        color: #1a1f3a;
        font-weight: 700;
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
                    <li class="breadcrumb-item"><a href="dash.php?page=laporan-Ditsamapta">Laporan Ditsamapta</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="dash.php?page=laporan-Ditsamapta" class="btn btn-secondary">
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

    <form method="POST" action="">
        <div class="row">

            <!-- Judul -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📋 Judul Laporan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="judul"
                           placeholder="Contoh: Patroli Rutin Wilayah Kota" required>
                </div>
            </div>

            <!-- Tanggal -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>📅 Tanggal Kegiatan <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="tanggal"
                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>
            </div>

            <!-- Jumlah Personil -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>👥 Jumlah Personil <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="personil"
                           value="1" min="1" required>
                </div>
            </div>

            <!-- Nama Petugas -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>👤 Nama Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_petugas"
                           value="<?php echo htmlspecialchars($nama_petugas); ?>" required>
                </div>
            </div>

            <!-- Pangkat -->
            <div class="col-md-6">
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

            <!-- Kegiatan -->
            <div class="col-md-12">
                <div class="form-group">
                    <label>📝 Uraian Kegiatan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="kegiatan" rows="5"
                              placeholder="Jelaskan detail kegiatan yang dilakukan..." required></textarea>
                    <small class="form-text text-muted">
                        Contoh: Melakukan patroli rutin di wilayah Kota untuk menjaga keamanan dan ketertiban...
                    </small>
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
                        <th>Judul</th>
                        <th>Lokasi</th>
                        <th>Personil</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_recent = "SELECT * FROM lapsam
                                     WHERE Id_akun = ?
                                     ORDER BY tanggal DESC
                                     LIMIT 5";
                    $stmt_recent = mysqli_prepare($db, $query_recent);
                    mysqli_stmt_bind_param($stmt_recent, "i", $id_akun);
                    mysqli_stmt_execute($stmt_recent);
                    $result_recent = mysqli_stmt_get_result($stmt_recent);

                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', $row['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['lokasi'], 0, 30)); ?><?php echo strlen($row['lokasi']) > 30 ? '...' : ''; ?></td>
                            <td><?php echo $row['personil']; ?> orang</td>
                            <td>
                                <span class="badge badge-<?php echo $row['status'] == 'Baru' ? 'warning' : 'info'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada laporan</td>
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