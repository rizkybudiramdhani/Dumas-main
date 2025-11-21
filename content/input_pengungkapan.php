<?php
// Cek apakah user punya akses (hanya ditresnarkoba)
if ($role != 'ditresnarkoba') {
    echo '<div class="alert alert-danger">Akses ditolak! Hanya Ditresnarkoba yang dapat mengakses halaman ini.</div>';
    exit;
}

// Proses form submit
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $jumlah_laporan = mysqli_real_escape_string($db, $_POST['jumlah_laporan']);
    $tersangka_penyidikan = mysqli_real_escape_string($db, $_POST['tersangka_penyidikan']);
    $tersangka_rehabilitasi = mysqli_real_escape_string($db, $_POST['tersangka_rehabilitasi']);
    $bb_sabu = mysqli_real_escape_string($db, $_POST['bb_sabu']);
    $bb_ekstasi = mysqli_real_escape_string($db, $_POST['bb_ekstasi']);
    $bb_ganja = mysqli_real_escape_string($db, $_POST['bb_ganja']);
    $bb_pohon_ganja = mysqli_real_escape_string($db, $_POST['bb_pohon_ganja']);
    $bb_kokain = mysqli_real_escape_string($db, $_POST['bb_kokain']);
    $bb_heroin = mysqli_real_escape_string($db, $_POST['bb_heroin']);
    $bb_happy_five = mysqli_real_escape_string($db, $_POST['bb_happy_five']);
    $bb_pil_alprazolam = mysqli_real_escape_string($db, $_POST['bb_pil_alprazolam']);
    $bb_ketamin = mysqli_real_escape_string($db, $_POST['bb_ketamin']);
    $bb_liquid_vape = mysqli_real_escape_string($db, $_POST['bb_liquid_vape']);

    // Cek apakah sudah ada data (id=1)
    $check_query = "SELECT * FROM tabel_pengungkapan WHERE id = 1";
    $check_result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update data yang sudah ada
        $query = "UPDATE tabel_pengungkapan SET 
                  jumlah_laporan = ?,
                  tersangka_penyidikan = ?,
                  tersangka_rehabilitasi = ?,
                  bb_sabu = ?,
                  bb_ekstasi = ?,
                  bb_ganja = ?,
                  bb_pohon_ganja = ?,
                  bb_kokain = ?,
                  bb_heroin = ?,
                  bb_happy_five = ?,
                  bb_pil_alprazolam = ?,
                  bb_ketamin = ?,
                  bb_liquid_vape = ?,
                  last_updated = NOW()
                  WHERE id = 1";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param(
            $stmt,
            "iiidddddddiii",
            $jumlah_laporan,
            $tersangka_penyidikan,
            $tersangka_rehabilitasi,
            $bb_sabu,
            $bb_ekstasi,
            $bb_ganja,
            $bb_pohon_ganja,
            $bb_kokain,
            $bb_heroin,
            $bb_happy_five,
            $bb_pil_alprazolam,
            $bb_ketamin,
            $bb_liquid_vape
        );
    } else {
        // Insert data baru
        $query = "INSERT INTO tabel_pengungkapan 
                  (jumlah_laporan, tersangka_penyidikan, tersangka_rehabilitasi, bb_sabu, bb_ekstasi, bb_ganja, bb_pohon_ganja, bb_kokain, bb_heroin, bb_happy_five, bb_pil_alprazolam, bb_ketamin, bb_liquid_vape, last_updated) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param(
            $stmt,
            "iiidddddddiiii",
            $jumlah_laporan,
            $tersangka_penyidikan,
            $tersangka_rehabilitasi,
            $bb_sabu,
            $bb_ekstasi,
            $bb_ganja,
            $bb_pohon_ganja,
            $bb_kokain,
            $bb_heroin,
            $bb_happy_five,
            $bb_pil_alprazolam,
            $bb_ketamin,
            $bb_liquid_vape
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        $success_message = 'Data pengungkapan berhasil disimpan!';
    } else {
        $error_message = 'Gagal menyimpan data: ' . mysqli_error($db);
    }

    mysqli_stmt_close($stmt);
}

// Ambil data pengungkapan terakhir untuk ditampilkan di form
$query_current = "SELECT * FROM tabel_pengungkapan WHERE id = 1";
$result_current = mysqli_query($db, $query_current);
$current_data = mysqli_fetch_assoc($result_current);
?>

<style>
    .form-section {
        background: #fff;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px solid #e5e7eb;
    }

    .form-section h5 {
        color: #1a1f3a;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #FFD700;
    }

    .form-group label {
        font-weight: 700;
        color: #1a1f3a;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-weight: 500;
    }

    .form-control:focus {
        border-color: #1a1f3a;
        box-shadow: 0 0 0 0.2rem rgba(26, 31, 58, 0.25);
    }

    .btn-save {
        background: #1a1f3a;
        border: none;
        padding: 12px 40px;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        background: #FFD700;
        color: #1a1f3a;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
        font-weight: 600;
    }

    .btn-secondary:hover {
        background: #5a6268;
        border-color: #5a6268;
    }

    .input-group-text {
        background: #1a1f3a;
        color: #FFD700;
        border: none;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .info-box {
        background: #1a1f3a;
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        border-left: 5px solid #FFD700;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .info-box h4 {
        margin: 0;
        font-weight: 700;
        color: #FFD700;
    }

    .info-box p {
        margin: 5px 0 0 0;
        color: #ffffff;
    }

    .info-box small {
        color: #ffffff;
        opacity: 0.9;
    }

    .page-header .title h4 {
        color: #1a1f3a;
        font-weight: 700;
    }

    .form-text {
        color: #6c757d;
        font-weight: 500;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Input Data Pengungkapan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input Pengungkapan</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a class="btn btn-secondary" href="dash.php">
                <i class="icon-copy dw dw-left-arrow"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Sukses!</strong> <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Info Box -->
<div class="info-box">
    <h4>📊 Data Pengungkapan Narkoba</h4>
    <p>Form ini digunakan untuk menginput atau mengupdate data statistik pengungkapan kasus narkoba yang ditangani oleh Ditresnarkoba</p>
    <?php if ($current_data): ?>
        <p><small>📅 Terakhir diupdate: <?php echo date('d F Y H:i', strtotime($current_data['last_updated'])); ?> WIB</small></p>
    <?php endif; ?>
</div>

<!-- Form Input -->
<form method="POST" id="form-pengungkapan">
    
    <!-- Section 1: Data Laporan & Tersangka -->
    <div class="form-section">
        <h5>📋 Data Laporan & Tersangka</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Jumlah Laporan <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" name="jumlah_laporan" 
                           value="<?php echo $current_data ? $current_data['jumlah_laporan'] : '0'; ?>" 
                           min="0" required>
                    <small class="form-text text-muted">Total laporan yang masuk</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tersangka Penyidikan <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" name="tersangka_penyidikan" 
                           value="<?php echo $current_data ? $current_data['tersangka_penyidikan'] : '0'; ?>" 
                           min="0" required>
                    <small class="form-text text-muted">Jumlah tersangka dalam proses penyidikan</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tersangka Rehabilitasi <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" name="tersangka_rehabilitasi" 
                           value="<?php echo $current_data ? $current_data['tersangka_rehabilitasi'] : '0'; ?>" 
                           min="0" required>
                    <small class="form-text text-muted">Jumlah tersangka yang direhabilitasi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Barang Bukti Narkotika (Berat) -->
    <div class="form-section">
        <h5>💊 Barang Bukti - Narkotika (dalam gram/kg)</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Sabu-sabu</label>
                    <div class="input-group">
                        <input class="form-control" type="number" step="0.01" name="bb_sabu" 
                               value="<?php echo $current_data ? $current_data['bb_sabu'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">gram</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Ganja</label>
                    <div class="input-group">
                        <input class="form-control" type="number" step="0.001" name="bb_ganja" 
                               value="<?php echo $current_data ? $current_data['bb_ganja'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Pohon Ganja</label>
                    <div class="input-group">
                        <input class="form-control" type="number" step="0.001" name="bb_pohon_ganja" 
                               value="<?php echo $current_data ? $current_data['bb_pohon_ganja'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">batang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Kokain</label>
                    <div class="input-group">
                        <input class="form-control" type="number" step="0.001" name="bb_kokain" 
                               value="<?php echo $current_data ? $current_data['bb_kokain'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">gram</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Heroin</label>
                    <div class="input-group">
                        <input class="form-control" type="number" step="0.001" name="bb_heroin" 
                               value="<?php echo $current_data ? $current_data['bb_heroin'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">gram</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Ekstasi</label>
                    <div class="input-group">
                        <input class="form-control" type="number" step="0.001" name="bb_ekstasi" 
                               value="<?php echo $current_data ? $current_data['bb_ekstasi'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">butir</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Barang Bukti Obat Terlarang (Jumlah) -->
    <div class="form-section">
        <h5>💉 Barang Bukti - Obat Terlarang (dalam butir/botol)</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Happy Five</label>
                    <div class="input-group">
                        <input class="form-control" type="number" name="bb_happy_five" 
                               value="<?php echo $current_data ? $current_data['bb_happy_five'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">butir</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Pil Alprazolam</label>
                    <div class="input-group">
                        <input class="form-control" type="number" name="bb_pil_alprazolam" 
                               value="<?php echo $current_data ? $current_data['bb_pil_alprazolam'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">butir</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Ketamin</label>
                    <div class="input-group">
                        <input class="form-control" type="number" name="bb_ketamin" 
                               value="<?php echo $current_data ? $current_data['bb_ketamin'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">botol</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Liquid Vape</label>
                    <div class="input-group">
                        <input class="form-control" type="number" name="bb_liquid_vape" 
                               value="<?php echo $current_data ? $current_data['bb_liquid_vape'] : '0'; ?>" 
                               min="0">
                        <div class="input-group-append">
                            <span class="input-group-text">botol</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="form-section text-center">
        <button type="submit" class="btn btn-save">
            <i class="icon-copy dw dw-diskette"></i> Simpan Data Pengungkapan
        </button>
        <button type="reset" class="btn btn-secondary ml-2">
            <i class="icon-copy dw dw-refresh"></i> Reset Form
        </button>
    </div>

</form>

<!-- Summary Card (jika ada data) -->
<?php if ($current_data): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;">
            <div class="card-header" style="background: #1a1f3a; color: white; border-radius: 15px 15px 0 0; border-bottom: 3px solid #FFD700; padding: 20px;">
                <h5 class="mb-0" style="color: #FFD700; font-weight: 700;">📊 Ringkasan Data Pengungkapan Saat Ini</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px; border-left: 4px solid #1a1f3a;">
                            <h6 style="color: #6c757d; font-weight: 600; margin-bottom: 10px;">Total Laporan</h6>
                            <h3 class="mb-0" style="color: #1a1f3a; font-weight: 700;"><?php echo number_format($current_data['jumlah_laporan']); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px; border-left: 4px solid #ea580c;">
                            <h6 style="color: #6c757d; font-weight: 600; margin-bottom: 10px;">Tersangka Penyidikan</h6>
                            <h3 class="mb-0" style="color: #ea580c; font-weight: 700;"><?php echo number_format($current_data['tersangka_penyidikan']); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px; border-left: 4px solid #16a34a;">
                            <h6 style="color: #6c757d; font-weight: 600; margin-bottom: 10px;">Tersangka Rehabilitasi</h6>
                            <h3 class="mb-0" style="color: #16a34a; font-weight: 700;"><?php echo number_format($current_data['tersangka_rehabilitasi']); ?></h3>
                        </div>
                    </div>
                </div>
                <hr style="border-color: #e5e7eb; margin: 20px 0;">
                <h6 class="mb-3" style="color: #1a1f3a; font-weight: 700;">💊 Total Barang Bukti:</h6>
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-2" style="color: #495057; font-weight: 500;">
                        <strong style="color: #1a1f3a;">Sabu:</strong> <?php echo number_format($current_data['bb_sabu'], 2); ?> gram
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2" style="color: #495057; font-weight: 500;">
                        <strong style="color: #1a1f3a;">Ganja:</strong> <?php echo number_format($current_data['bb_ganja'], 3); ?> kg
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2" style="color: #495057; font-weight: 500;">
                        <strong style="color: #1a1f3a;">Ekstasi:</strong> <?php echo number_format($current_data['bb_ekstasi'], 0); ?> butir
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2" style="color: #495057; font-weight: 500;">
                        <strong style="color: #1a1f3a;">Happy Five:</strong> <?php echo number_format($current_data['bb_happy_five']); ?> butir
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Form validation & confirmation
    document.getElementById('form-pengungkapan').addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin menyimpan data pengungkapan ini?')) {
            e.preventDefault();
        }
    });

    // Auto dismiss alert after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>