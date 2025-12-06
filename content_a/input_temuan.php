<?php
// Cek apakah user punya akses (hanya Ditresnarkoba)
if ($role != 'Ditresnarkoba') {
    echo '<div class="alert alert-danger">Akses ditolak! Hanya Ditresnarkoba yang dapat mengakses halaman ini.</div>';
    exit;
}

// Proses form submit
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // Proses tambah/update data temuan
    if ($action == 'add' || $action == 'update') {
        $id_temuan = isset($_POST['id_temuan']) ? mysqli_real_escape_string($db, $_POST['id_temuan']) : '';
        $jenis = mysqli_real_escape_string($db, $_POST['jenis']);
        $jumlah = mysqli_real_escape_string($db, trim($_POST['jumlah']));

        if ($action == 'update' && !empty($id_temuan)) {
            // Update data yang sudah ada
            $query = "UPDATE temuan SET jenis = ?, jumlah = ? WHERE id_temuan = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $jenis, $jumlah, $id_temuan);
        } else {
            // Insert data baru
            $query = "INSERT INTO temuan (jenis, jumlah) VALUES (?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ss", $jenis, $jumlah);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Data temuan berhasil disimpan!';
        } else {
            $error_message = 'Gagal menyimpan data: ' . mysqli_error($db);
        }
        mysqli_stmt_close($stmt);
    }

    // Proses hapus data temuan
    if ($action == 'delete') {
        $id_temuan = mysqli_real_escape_string($db, $_POST['id_temuan']);
        $query = "DELETE FROM temuan WHERE id_temuan = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_temuan);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Data temuan berhasil dihapus!';
        } else {
            $error_message = 'Gagal menghapus data: ' . mysqli_error($db);
        }
        mysqli_stmt_close($stmt);
    }
}

// Ambil semua data temuan untuk ditampilkan
$query_temuan = "SELECT * FROM temuan ORDER BY jenis ASC";
$result_temuan = mysqli_query($db, $query_temuan);
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

    .btn-edit {
        background: #0d6efd;
        border: none;
        color: white;
        padding: 5px 15px;
        border-radius: 5px;
        font-size: 0.875rem;
    }

    .btn-edit:hover {
        background: #0b5ed7;
        color: white;
    }

    .btn-delete {
        background: #dc3545;
        border: none;
        color: white;
        padding: 5px 15px;
        border-radius: 5px;
        font-size: 0.875rem;
    }

    .btn-delete:hover {
        background: #bb2d3b;
        color: white;
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

    .page-header .title h4 {
        color: #1a1f3a;
        font-weight: 700;
    }

    .form-text {
        color: #6c757d;
        font-weight: 500;
    }

    .table-temuan {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .table-temuan thead {
        background: #1a1f3a;
        color: white;
    }

    .table-temuan thead th {
        font-weight: 700;
        border: none;
        padding: 15px;
    }

    .table-temuan tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-color: #e5e7eb;
    }

    .table-temuan tbody tr:hover {
        background: #f8f9fa;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Input Hasil Sitaan BB Narkoba</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input BB</li>
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
    <h4>📊 Hasil Sitaan BB Narkoba</h4>
    <p>Form ini digunakan untuk menginput atau mengupdate data temuan barang bukti narkoba yang ditangani oleh Ditresnarkoba</p>
</div>

<!-- Form Input Temuan -->
<div class="form-section">
    <h5>➕ Tambah / Edit </h5>
    <form method="POST" id="form-temuan">
        <input type="hidden" name="action" id="action" value="add">
        <input type="hidden" name="id_temuan" id="id_temuan" value="">

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Nama Barang Bukti <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="jenis" id="jenis"
                           placeholder="Contoh: Sabu, Ganja, Ekstasi, dll" required>
                    <small class="form-text text-muted">Masukkan jenis/nama barang bukti</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Jumlah <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="jumlah" id="jumlah"
                           placeholder="Contoh: 100 gram, 2.5 kilogram, 50 butir, 10 botol, dll" required>
                    <small class="form-text text-muted">Masukkan jumlah beserta satuannya (gram, kilogram, butir, botol, dll)</small>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-save" id="btn-submit">
                <i class="icon-copy dw dw-diskette"></i> <span id="btn-text">Simpan Data</span>
            </button>
            <button type="button" class="btn btn-secondary ml-2" id="btn-cancel" onclick="resetForm()" style="display: none;">
                <i class="icon-copy dw dw-cancel"></i> Batal Edit
            </button>
        </div>
    </form>
</div>

<!-- Tabel Daftar Temuan -->
<div class="form-section">
    <h5>📋 Daftar BB Narkoba</h5>
    <?php if (mysqli_num_rows($result_temuan) > 0): ?>
        <div class="table-responsive">
            <table class="table table-temuan table-hover">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="45%">Nama Barang Bukti</th>
                        <th width="30%">Jumlah</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    mysqli_data_seek($result_temuan, 0); // Reset pointer
                    while ($row = mysqli_fetch_assoc($result_temuan)):
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['jenis']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-edit btn-sm"
                                        onclick="editTemuan(<?php echo $row['id_temuan']; ?>, '<?php echo htmlspecialchars($row['jenis'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['jumlah'], ENT_QUOTES); ?>')">
                                    <i class="icon-copy dw dw-edit2"></i> Edit
                                </button>
                                <button type="button" class="btn btn-delete btn-sm"
                                        onclick="deleteTemuan(<?php echo $row['id_temuan']; ?>, '<?php echo htmlspecialchars($row['jenis'], ENT_QUOTES); ?>')">
                                    <i class="icon-copy dw dw-delete-3"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="icon-copy dw dw-info"></i> Belum ada data temuan. Silakan tambahkan data menggunakan form di atas.
        </div>
    <?php endif; ?>
</div>

<script>
    // Form validation & confirmation
    document.getElementById('form-temuan').addEventListener('submit', function(e) {
        const action = document.getElementById('action').value;
        const message = action === 'update'
            ? 'Apakah Anda yakin ingin mengupdate data temuan ini?'
            : 'Apakah Anda yakin ingin menyimpan data temuan ini?';

        if (!confirm(message)) {
            e.preventDefault();
        }
    });

    // Fungsi untuk edit data temuan
    function editTemuan(id, jenis, jumlah) {
        document.getElementById('id_temuan').value = id;
        document.getElementById('jenis').value = jenis;
        document.getElementById('jumlah').value = jumlah;
        document.getElementById('action').value = 'update';
        document.getElementById('btn-text').textContent = 'Update Data Temuan';
        document.getElementById('btn-cancel').style.display = 'inline-block';

        // Scroll ke form
        document.getElementById('form-temuan').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Fungsi untuk reset form
    function resetForm() {
        document.getElementById('form-temuan').reset();
        document.getElementById('id_temuan').value = '';
        document.getElementById('action').value = 'add';
        document.getElementById('btn-text').textContent = 'Simpan Data Temuan';
        document.getElementById('btn-cancel').style.display = 'none';
    }

    // Fungsi untuk hapus data temuan
    function deleteTemuan(id, jenis) {
        if (confirm('Apakah Anda yakin ingin menghapus data temuan "' + jenis + '"?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_temuan" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto dismiss alert after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>