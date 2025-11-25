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
    // Check if this is an edit or insert
    if (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id_kasus'])) {
        // Update existing data
        $id_kasus = mysqli_real_escape_string($db, $_POST['id_kasus']);
        $tersangka = mysqli_real_escape_string($db, $_POST['tersangka']);
        $kasus = mysqli_real_escape_string($db, $_POST['kasus']);
        $kec = mysqli_real_escape_string($db, $_POST['kec']);
        $kel = mysqli_real_escape_string($db, $_POST['kel']);
        $alamat_lengkap = mysqli_real_escape_string($db, $_POST['alamat_lengkap']);

        $query = "UPDATE kasus SET tersangka=?, kasus=?, kec=?, kel=?, alamat_lengkap=? WHERE id_kasus=?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "issssi", $tersangka, $kasus, $kec, $kel, $alamat_lengkap, $id_kasus);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Data kasus berhasil diupdate!';
            echo '<script>setTimeout(function(){ window.location.href = "dash.php?page=input-laporan-Ditresnarkoba&success=2"; }, 2000);</script>';
        } else {
            $error_message = 'Gagal mengupdate data kasus: ' . mysqli_error($db);
        }

        mysqli_stmt_close($stmt);
    } else {
        // Insert new data
        $tersangka = mysqli_real_escape_string($db, $_POST['tersangka']);
        $kasus = mysqli_real_escape_string($db, $_POST['kasus']);
        $kec = mysqli_real_escape_string($db, $_POST['kec']);
        $kel = mysqli_real_escape_string($db, $_POST['kel']);
        $alamat_lengkap = mysqli_real_escape_string($db, $_POST['alamat_lengkap']);

        $query = "INSERT INTO kasus (tersangka, kasus, kec, kel, alamat_lengkap)
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "issss", $tersangka, $kasus, $kec, $kel, $alamat_lengkap);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Data kasus berhasil disimpan!';
            echo '<script>setTimeout(function(){ window.location.href = "dash.php?page=input-laporan-Ditresnarkoba&success=1"; }, 2000);</script>';
        } else {
            $error_message = 'Gagal menyimpan data kasus: ' . mysqli_error($db);
        }

        mysqli_stmt_close($stmt);
    }
}

// Show success message from redirect
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success_message = 'Data kasus berhasil disimpan!';
    } elseif ($_GET['success'] == 2) {
        $success_message = 'Data kasus berhasil diupdate!';
    }
}
?>

<style>
    .form-section {
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .form-section h5 {
        color: #1a1f3a;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #FFD700;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-control:focus,
    .form-control-file:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }

    .btn-submit {
        background: #1a1f3a;
        border: none;
        padding: 12px 40px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #FFD700;
        color: #1a1f3a;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
    }

    .info-box {
        background: #1a1f3a;
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
        border-left: 5px solid #FFD700;
    }

    .info-box h4 {
        margin: 0 0 10px 0;
        font-weight: 700;
        color: #FFD700;
    }

    .info-box p {
        margin: 0;
        color: #ffffff;
    }

    .required-mark {
        color: #dc3545;
        font-weight: bold;
    }

    .input-group-text {
        background: #1a1f3a;
        color: #FFD700;
        border: none;
        font-weight: 600;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Input Data Kasus Ditresnarkoba</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input Data Kasus</li>
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
        <strong><i class="icon-copy dw dw-checked"></i> Berhasil!</strong> <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="icon-copy dw dw-warning"></i> Error!</strong> <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Info Box -->
<div class="info-box">
    <h4>📋 Input Data Kasus Narkoba</h4>
    <p>Form ini digunakan untuk menginput data kasus narkoba yang ditangani oleh Ditresnarkoba, termasuk informasi tersangka, jenis kasus, dan lokasi kejadian.</p>
</div>

<!-- Form Input -->
<form method="POST" id="form-kasus">

    <!-- Section 1: Informasi Kasus -->
    <div class="form-section">
        <h5>🔍 Informasi Kasus</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jenis Kasus <span class="required-mark">*</span></label>
                    <input class="form-control" type="text" name="kasus"
                        placeholder="Contoh: Penyalahgunaan Narkotika Golongan I"
                        required>
                    <small class="form-text text-muted">Sebutkan jenis/kategori kasus narkoba</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Jumlah Tersangka <span class="required-mark">*</span></label>
                    <div class="input-group">
                        <input class="form-control" type="number" name="tersangka"
                            value="0" min="0" required>
                        <div class="input-group-append">
                            <span class="input-group-text">orang</span>
                        </div>
                    </div>
                    <small class="form-text text-muted">Jumlah tersangka yang terlibat dalam kasus ini</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Lokasi Kejadian -->
    <div class="form-section">
        <h5>📍 Lokasi Kejadian</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kecamatan <span class="required-mark">*</span></label>
                    <input class="form-control" type="text" name="kec"
                        placeholder="Nama Kecamatan"
                        required>
                    <small class="form-text text-muted">Kecamatan tempat kejadian</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kelurahan/Desa <span class="required-mark">*</span></label>
                    <input class="form-control" type="text" name="kel"
                        placeholder="Nama Kelurahan/Desa"
                        required>
                    <small class="form-text text-muted">Kelurahan atau desa tempat kejadian</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Alamat Lengkap <span class="required-mark">*</span></label>
            <textarea class="form-control" name="alamat_lengkap" rows="3"
                placeholder="Alamat lengkap lokasi kejadian (RT/RW, nama jalan, patokan, dll)"
                required></textarea>
            <small class="form-text text-muted">Jelaskan alamat lengkap lokasi kejadian secara detail</small>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="form-section text-center">
        <button type="submit" class="btn btn-submit" id="submit-btn">
            <i class="icon-copy dw dw-diskette"></i> Simpan Data Kasus
        </button>
        <button type="reset" class="btn btn-secondary ml-2">
            <i class="icon-copy dw dw-refresh"></i> Reset Form
        </button>
    </div>

</form>

<!-- Recent Data Kasus -->
<div class="card mt-4" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border-top: 4px solid #FFD700;">
    <div class="card-header" style="background: #1a1f3a; color: white; border-radius: 15px 15px 0 0;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: #FFD700;"><i class="icon-copy dw dw-file"></i> Data Kasus</h5>
            <button type="button" class="btn btn-success btn-sm" id="btnExportExcel">
                <i class="icon-copy fa fa-file-excel-o"></i> Export Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="filterKec" style="font-weight: 600;">Filter Kecamatan:</label>
                    <select class="form-control" id="filterKec">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php
                        // Get unique kecamatan
                        $query_kec = "SELECT DISTINCT kec FROM kasus WHERE kec IS NOT NULL AND kec != '' ORDER BY kec ASC";
                        $result_kec = mysqli_query($db, $query_kec);
                        while ($kec_row = mysqli_fetch_assoc($result_kec)):
                        ?>
                            <option value="<?php echo htmlspecialchars($kec_row['kec']); ?>">
                                <?php echo htmlspecialchars($kec_row['kec']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="filterKel" style="font-weight: 600;">Filter Kelurahan:</label>
                    <select class="form-control" id="filterKel">
                        <option value="">-- Semua Kelurahan --</option>
                        <?php
                        // Get unique kelurahan
                        $query_kel = "SELECT DISTINCT kel FROM kasus WHERE kel IS NOT NULL AND kel != '' ORDER BY kel ASC";
                        $result_kel = mysqli_query($db, $query_kel);
                        while ($kel_row = mysqli_fetch_assoc($result_kel)):
                        ?>
                            <option value="<?php echo htmlspecialchars($kel_row['kel']); ?>">
                                <?php echo htmlspecialchars($kel_row['kel']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-weight: 600;">&nbsp;</label>
                    <button type="button" class="btn btn-secondary btn-block" id="btnResetFilter">
                        <i class="icon-copy dw dw-refresh"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="tableKasus">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th width="50">No</th>
                        <th>Jenis Kasus</th>
                        <th width="120">Tersangka</th>
                        <th>Kecamatan</th>
                        <th>Kelurahan</th>
                        <th>Alamat</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyKasus">
                    <?php
                    // Get all data from kasus table
                    $query_recent = "SELECT * FROM kasus ORDER BY id_kasus DESC";
                    $result_recent = mysqli_query($db, $query_recent);

                    $no = 1;
                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                    ?>
                            <tr data-kec="<?php echo htmlspecialchars($row['kec']); ?>"
                                data-kel="<?php echo htmlspecialchars($row['kel']); ?>">
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['kasus']); ?></strong></td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?php echo $row['tersangka']; ?> orang</span>
                                </td>
                                <td><?php echo htmlspecialchars($row['kec']); ?></td>
                                <td><?php echo htmlspecialchars($row['kel']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['alamat_lengkap'], 0, 50)); ?>
                                    <?php if (strlen($row['alamat_lengkap']) > 50) echo '...'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-edit-kasus"
                                        data-id="<?php echo $row['id_kasus']; ?>"
                                        data-kasus="<?php echo htmlspecialchars($row['kasus'], ENT_QUOTES); ?>"
                                        data-tersangka="<?php echo $row['tersangka']; ?>"
                                        data-kec="<?php echo htmlspecialchars($row['kec'], ENT_QUOTES); ?>"
                                        data-kel="<?php echo htmlspecialchars($row['kel'], ENT_QUOTES); ?>"
                                        data-alamat="<?php echo htmlspecialchars($row['alamat_lengkap'], ENT_QUOTES); ?>"
                                        title="Edit">
                                        <i class="icon-copy dw dw-edit2"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr id="noDataRow">
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="icon-copy dw dw-file" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mb-0 mt-2">Belum ada data kasus</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="filterInfo" class="mt-2 text-muted" style="display: none;">
            <small><i class="icon-copy dw dw-info"></i> Menampilkan <strong id="countFiltered">0</strong> dari <strong id="countTotal">0</strong> data</small>
        </div>
    </div>
</div>

<!-- Modal Edit Kasus -->
<div class="modal fade" id="modalEditKasus" tabindex="-1" role="dialog" aria-labelledby="modalEditKasusLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a1f3a; color: white;">
                <h5 class="modal-title" id="modalEditKasusLabel" style="color: #FFD700;">
                    <i class="icon-copy dw dw-edit2"></i> Edit Data Kasus
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" id="formEditKasus">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_kasus" id="edit_id_kasus">

                <div class="modal-body">
                    <!-- Informasi Kasus -->
                    <div class="form-section" style="padding: 20px; margin-bottom: 0;">
                        <h6 style="color: #1a1f3a; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #FFD700; padding-bottom: 8px;">
                            🔍 Informasi Kasus
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Kasus <span class="required-mark">*</span></label>
                                    <input class="form-control" type="text" name="kasus" id="edit_kasus"
                                        placeholder="Contoh: Penyalahgunaan Narkotika Golongan I"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah Tersangka <span class="required-mark">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="number" name="tersangka" id="edit_tersangka"
                                            value="0" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">orang</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi Kejadian -->
                    <div class="form-section" style="padding: 20px; margin-bottom: 0;">
                        <h6 style="color: #1a1f3a; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #FFD700; padding-bottom: 8px;">
                            📍 Lokasi Kejadian
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kecamatan <span class="required-mark">*</span></label>
                                    <input class="form-control" type="text" name="kec" id="edit_kec"
                                        placeholder="Nama Kecamatan"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kelurahan/Desa <span class="required-mark">*</span></label>
                                    <input class="form-control" type="text" name="kel" id="edit_kel"
                                        placeholder="Nama Kelurahan/Desa"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap <span class="required-mark">*</span></label>
                            <textarea class="form-control" name="alamat_lengkap" id="edit_alamat_lengkap" rows="3"
                                placeholder="Alamat lengkap lokasi kejadian (RT/RW, nama jalan, patokan, dll)"
                                required></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="icon-copy dw dw-close"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-submit" id="btnUpdateKasus">
                        <i class="icon-copy dw dw-diskette"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    // Form submit handler
    document.getElementById('form-kasus').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.innerHTML = '<i class="icon-copy dw dw-loading"></i> Menyimpan...';
        submitBtn.disabled = true;
    });

    // Auto dismiss alert
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Form validation
    document.getElementById('form-kasus').addEventListener('submit', function(e) {
        const tersangka = document.querySelector('input[name="tersangka"]').value;
        if (tersangka < 0) {
            e.preventDefault();
            alert('Jumlah tersangka tidak boleh negatif!');
            return false;
        }
    });

    // Filter functionality
    function filterTable() {
        const filterKec = document.getElementById('filterKec').value.toLowerCase();
        const filterKel = document.getElementById('filterKel').value.toLowerCase();
        const tbody = document.getElementById('tbodyKasus');
        const rows = tbody.getElementsByTagName('tr');

        let visibleCount = 0;
        let totalCount = 0;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];

            // Skip no data row
            if (row.id === 'noDataRow') continue;

            totalCount++;

            const kec = (row.getAttribute('data-kec') || '').toLowerCase();
            const kel = (row.getAttribute('data-kel') || '').toLowerCase();

            let showRow = true;

            // Filter by kecamatan
            if (filterKec && kec !== filterKec) {
                showRow = false;
            }

            // Filter by kelurahan
            if (filterKel && kel !== filterKel) {
                showRow = false;
            }

            // Show/hide row
            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }

        // Update row numbers
        updateRowNumbers();

        // Show filter info
        const filterInfo = document.getElementById('filterInfo');
        const countFiltered = document.getElementById('countFiltered');
        const countTotal = document.getElementById('countTotal');

        if (filterKec || filterKel) {
            filterInfo.style.display = 'block';
            countFiltered.textContent = visibleCount;
            countTotal.textContent = totalCount;
        } else {
            filterInfo.style.display = 'none';
        }

        // Show "no data" message if no rows visible
        const noDataRow = document.getElementById('noDataRow');
        if (visibleCount === 0 && totalCount > 0) {
            if (!noDataRow) {
                const newRow = tbody.insertRow();
                newRow.id = 'noDataRowFiltered';
                newRow.innerHTML = `
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="icon-copy dw dw-search" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mb-0 mt-2">Tidak ada data yang sesuai dengan filter</p>
                    </td>
                `;
            }
        } else {
            const filteredNoDataRow = document.getElementById('noDataRowFiltered');
            if (filteredNoDataRow) {
                filteredNoDataRow.remove();
            }
        }
    }

    function updateRowNumbers() {
        const tbody = document.getElementById('tbodyKasus');
        const rows = tbody.getElementsByTagName('tr');
        let num = 1;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.style.display !== 'none' && row.id !== 'noDataRow' && row.id !== 'noDataRowFiltered') {
                const firstCell = row.getElementsByTagName('td')[0];
                if (firstCell) {
                    firstCell.textContent = num++;
                }
            }
        }
    }

    // Event listeners for filters
    document.getElementById('filterKec').addEventListener('change', filterTable);
    document.getElementById('filterKel').addEventListener('change', filterTable);

    // Reset filter
    document.getElementById('btnResetFilter').addEventListener('click', function() {
        document.getElementById('filterKec').value = '';
        document.getElementById('filterKel').value = '';
        filterTable();
    });

    // Export to Excel
    document.getElementById('btnExportExcel').addEventListener('click', function() {
        const table = document.getElementById('tableKasus');
        const rows = table.querySelectorAll('tbody tr');

        // Prepare data for export
        const data = [];

        // Add header
        data.push(['No', 'Jenis Kasus', 'Jumlah Tersangka', 'Kecamatan', 'Kelurahan', 'Alamat Lengkap']);

        // Add visible rows only
        let rowNum = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none' && row.id !== 'noDataRow' && row.id !== 'noDataRowFiltered') {
                const cells = row.getElementsByTagName('td');
                if (cells.length >= 6) {
                    const jenis = cells[1].textContent.trim();
                    const tersangka = cells[2].textContent.replace(' orang', '').trim();
                    const kec = cells[3].textContent.trim();
                    const kel = cells[4].textContent.trim();
                    const alamat = row.querySelector('td:nth-child(6)').textContent.trim();

                    data.push([
                        rowNum++,
                        jenis,
                        tersangka,
                        kec,
                        kel,
                        alamat
                    ]);
                }
            }
        });

        if (data.length <= 1) {
            alert('Tidak ada data untuk di-export!');
            return;
        }

        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);

        // Set column widths
        ws['!cols'] = [
            { wch: 5 },  // No
            { wch: 40 }, // Jenis Kasus
            { wch: 15 }, // Tersangka
            { wch: 20 }, // Kecamatan
            { wch: 20 }, // Kelurahan
            { wch: 50 }  // Alamat
        ];

        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Data Kasus');

        // Generate filename with date
        const now = new Date();
        const dateStr = now.getFullYear() + '-' +
                       String(now.getMonth() + 1).padStart(2, '0') + '-' +
                       String(now.getDate()).padStart(2, '0');
        const filename = 'Data_Kasus_Ditresnarkoba_' + dateStr + '.xlsx';

        // Save file
        XLSX.writeFile(wb, filename);
    });

    // Initialize count on page load
    window.addEventListener('load', function() {
        const tbody = document.getElementById('tbodyKasus');
        const rows = tbody.querySelectorAll('tr:not(#noDataRow)');
        document.getElementById('countTotal').textContent = rows.length;
    });

    // Handle Edit Button Click
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-kasus')) {
            const btn = e.target.closest('.btn-edit-kasus');

            // Get data from button attributes
            const id = btn.getAttribute('data-id');
            const kasus = btn.getAttribute('data-kasus');
            const tersangka = btn.getAttribute('data-tersangka');
            const kec = btn.getAttribute('data-kec');
            const kel = btn.getAttribute('data-kel');
            const alamat = btn.getAttribute('data-alamat');

            // Fill form with data
            document.getElementById('edit_id_kasus').value = id;
            document.getElementById('edit_kasus').value = kasus;
            document.getElementById('edit_tersangka').value = tersangka;
            document.getElementById('edit_kec').value = kec;
            document.getElementById('edit_kel').value = kel;
            document.getElementById('edit_alamat_lengkap').value = alamat;

            // Show modal
            $('#modalEditKasus').modal('show');
        }
    });

    // Handle Edit Form Submit
    document.getElementById('formEditKasus').addEventListener('submit', function(e) {
        const btnUpdate = document.getElementById('btnUpdateKasus');
        btnUpdate.innerHTML = '<i class="icon-copy dw dw-loading"></i> Mengupdate...';
        btnUpdate.disabled = true;
    });
</script>
