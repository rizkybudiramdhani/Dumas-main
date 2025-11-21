<?php
// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_laporan = (int)$_GET['id'];

    // Get file path to delete
    $query_file = "SELECT gambar FROM tabel_laporan WHERE id_laporan = ?";
    $stmt_file = mysqli_prepare($db, $query_file);
    mysqli_stmt_bind_param($stmt_file, "i", $id_laporan);
    mysqli_stmt_execute($stmt_file);
    $result_file = mysqli_stmt_get_result($stmt_file);
    $file_data = mysqli_fetch_assoc($result_file);

    // Delete record
    $query_delete = "DELETE FROM tabel_laporan WHERE id_laporan = ?";
    $stmt_delete = mysqli_prepare($db, $query_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id_laporan);

    if (mysqli_stmt_execute($stmt_delete)) {
        // Delete file if exists
        if ($file_data && !empty($file_data['gambar'])) {
            $files = explode(',', $file_data['gambar']);
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        echo '<script>alert("Pengaduan berhasil dihapus!"); window.location.href="dash.php?page=lihat-pengaduan";</script>';
    }
}

// Handle update status
if (isset($_POST['update_status'])) {
    $id_laporan = (int)$_POST['id_laporan'];
    $status_baru = mysqli_real_escape_string($db, $_POST['status_baru']);
    $tanggapan = mysqli_real_escape_string($db, $_POST['tanggapan']);

    $query_update = "UPDATE tabel_laporan 
                     SET status_laporan = ?, tanggapan_admin = ?, tanggal_tanggapan = NOW() 
                     WHERE id_laporan = ?";
    $stmt_update = mysqli_prepare($db, $query_update);
    mysqli_stmt_bind_param($stmt_update, "ssi", $status_baru, $tanggapan, $id_laporan);

    if (mysqli_stmt_execute($stmt_update)) {
        echo '<script>alert("Status berhasil diupdate!"); window.location.href="dash.php?page=lihat-pengaduan";</script>';
    }
}
?>

<style>
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .action-btn {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.875rem;
        margin: 2px;
    }

    .card-stats {
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .filter-section {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-top: 4px solid #FFD700;
    }

    .filter-section label {
        color: #1a1f3a;
        font-weight: 600;
    }

    .table-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-top: 4px solid #FFD700;
    }

    .table-card .card-header {
        background: #1a1f3a;
        color: white;
        border: none;
        padding: 20px;
    }

    .table-card .card-header h4 {
        color: #FFD700;
        font-weight: 700;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Lihat Pengaduan Masyarakat</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lihat Pengaduan</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a class="btn btn-primary" href="dash.php?page=input-pengaduan">
                <i class="icon-copy dw dw-add"></i> Buat Pengaduan Baru
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row pb-10">
    <?php
    // Get statistics
    $query_total = "SELECT COUNT(*) as total FROM tabel_laporan";
    $query_baru = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan = 'baru'";
    $query_diproses = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan LIKE '%diproses%'";
    $query_selesai = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan LIKE '%selesai%'";

    $total = mysqli_fetch_assoc(mysqli_query($db, $query_total))['total'];
    $baru = mysqli_fetch_assoc(mysqli_query($db, $query_baru))['total'];
    $diproses = mysqli_fetch_assoc(mysqli_query($db, $query_diproses))['total'];
    $selesai = mysqli_fetch_assoc(mysqli_query($db, $query_selesai))['total'];
    ?>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card card-stats">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto mb-3" style="background: #1a1f3a; border: 3px solid #FFD700;">
                    <i class="icon-copy dw dw-file" style="color: #FFD700;"></i>
                </div>
                <h3 class="mb-0" style="color: #1a1f3a; font-weight: 700;"><?php echo $total; ?></h3>
                <p class="text-muted mb-0 font-weight-600">Total Pengaduan</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card card-stats">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto mb-3" style="background: #ffc107;">
                    <i class="icon-copy dw dw-inbox"></i>
                </div>
                <h3 class="mb-0" style="color: #1a1f3a; font-weight: 700;"><?php echo $baru; ?></h3>
                <p class="text-muted mb-0 font-weight-600">Baru</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card card-stats">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto mb-3" style="background: #17a2b8;">
                    <i class="fa fa-hourglass-half"></i>
                </div>
                <h3 class="mb-0" style="color: #1a1f3a; font-weight: 700;"><?php echo $diproses; ?></h3>
                <p class="text-muted mb-0 font-weight-600">Diproses</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card card-stats">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto mb-3" style="background: #28a745;">
                    <i class="icon-copy dw dw-checked"></i>
                </div>
                <h3 class="mb-0" style="color: #1a1f3a; font-weight: 700;"><?php echo $selesai; ?></h3>
                <p class="text-muted mb-0 font-weight-600">Selesai</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row align-items-end">
        <div class="col-md-3">
            <label class="font-weight-600">Filter Status:</label>
            <select class="form-control" id="filter-status">
                <option value="">Semua Status</option>
                <option value="baru">Baru</option>
                <option value="diproses">Diproses</option>
                <option value="selesai">Selesai</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="font-weight-600">Dari Tanggal:</label>
            <input type="date" class="form-control" id="filter-dari">
        </div>
        <div class="col-md-3">
            <label class="font-weight-600">Sampai Tanggal:</label>
            <input type="date" class="form-control" id="filter-sampai">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-block" onclick="filterData()">
                <i class="icon-copy dw dw-search"></i> Filter
            </button>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card table-card mb-30">
    <div class="card-header">
        <h4 class="mb-0"><i class="icon-copy dw dw-file" style="color: #FFD700;"></i> Daftar Pengaduan Masyarakat</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="pengaduan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th width="50">No</th>
                        <th>Judul</th>
                        <th>Pelapor</th>
                        <th>Kontak</th>
                        <th>Lokasi</th>
                        <th width="110">Tanggal</th>
                        <th width="100">Status</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT l.*, u.nama as nama_user 
                             FROM tabel_laporan l 
                             LEFT JOIN tabel_users u ON l.id_user = u.id_users
                             ORDER BY l.tanggal_lapor DESC";
                    $result = mysqli_query($db, $query);

                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        // Determine status class
                        $status_class = 'secondary';
                        if ($row['status_laporan'] == 'baru') $status_class = 'warning';
                        elseif (strpos($row['status_laporan'], 'diproses') !== false) $status_class = 'info';
                        elseif (strpos($row['status_laporan'], 'selesai') !== false) $status_class = 'success';

                        $nama_pelapor = $row['nama_user'] ? $row['nama_user'] : ($row['nama'] ? $row['nama'] : 'Anonim');
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['judul_laporan']); ?></strong>
                                <br><small class="text-muted"><?php echo substr(htmlspecialchars($row['laporan']), 0, 50); ?>...</small>
                            </td>
                            <td><?php echo htmlspecialchars($nama_pelapor); ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_lapor'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $status_class; ?> status-badge">
                                    <?php echo ucfirst($row['status_laporan']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info action-btn" onclick="viewDetail(<?php echo $row['id_laporan']; ?>)">
                                    <i class="dw dw-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary action-btn" onclick="updateStatus(<?php echo $row['id_laporan']; ?>, '<?php echo htmlspecialchars($row['judul_laporan']); ?>')">
                                    <i class="dw dw-edit2"></i>
                                </button>
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteData(<?php echo $row['id_laporan']; ?>)">
                                    <i class="dw dw-delete-3"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Pengaduan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_laporan" id="modal-id">

                    <div class="form-group">
                        <label class="font-weight-600">Judul Pengaduan:</label>
                        <p id="modal-judul" class="text-muted"></p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600">Status Baru:</label>
                        <select class="form-control" name="status_baru" required>
                            <option value="baru">Baru</option>
                            <option value="diproses ditsamapta">Diproses Ditsamapta</option>
                            <option value="selesai ditsamapta">Selesai Ditsamapta</option>
                            <option value="diproses ditbinmas">Diproses Ditbinmas</option>
                            <option value="selesai ditbinmas">Selesai Ditbinmas</option>
                            <option value="diproses ditresnarkoba">Diproses Ditresnarkoba</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-600">Tanggapan/Keterangan:</label>
                        <textarea class="form-control" name="tanggapan" rows="4"
                            placeholder="Berikan tanggapan atau keterangan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="dw dw-diskette"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" type="text/css" href="src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="src/plugins/datatables/css/responsive.bootstrap4.min.css">
<script src="src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>

<script>
    // Initialize DataTable
    var table = $('#pengaduan-table').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: [0, 7],
            orderable: false,
        }],
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        "language": {
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ pengaduan",
            "infoEmpty": "Tidak ada data",
            "lengthMenu": "Tampilkan _MENU_ data",
            "search": "Cari:",
            "zeroRecords": "Tidak ada data yang cocok",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": '<i class="ion-chevron-right"></i>',
                "previous": '<i class="ion-chevron-left"></i>'
            }
        },
        "pageLength": 10
    });

    // Filter functions
    function filterData() {
        var status = $('#filter-status').val();
        var dari = $('#filter-dari').val();
        var sampai = $('#filter-sampai').val();

        table.column(6).search(status).draw();

        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var date = new Date(data[5]);
                var startDate = dari ? new Date(dari) : null;
                var endDate = sampai ? new Date(sampai) : null;

                if ((startDate === null && endDate === null) ||
                    (startDate === null && date <= endDate) ||
                    (startDate <= date && endDate === null) ||
                    (startDate <= date && date <= endDate)) {
                    return true;
                }
                return false;
            }
        );

        table.draw();
    }

    // View detail
    function viewDetail(id) {
        window.location.href = 'dash.php?page=detail-pengaduan&id=' + id;
    }

    // Update status
    function updateStatus(id, judul) {
        $('#modal-id').val(id);
        $('#modal-judul').text(judul);
        $('#modalUpdateStatus').modal('show');
    }

    // Delete data
    function deleteData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus pengaduan ini?')) {
            window.location.href = 'dash.php?page=lihat-pengaduan&action=delete&id=' + id;
        }
    }
</script>