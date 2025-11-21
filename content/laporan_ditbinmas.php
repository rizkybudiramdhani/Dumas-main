<?php
// Get filter parameters
$filter_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-d');
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');

// Build query - PAKAI TABEL kegiatan_ditbinmas
$query = "SELECT * FROM kegiatan_ditbinmas WHERE 1=1";
$params = [];
$types = '';

if (!empty($filter_dari)) {
    $query .= " AND tanggal >= ?";
    $params[] = $filter_dari;
    $types .= 's';
}

if (!empty($filter_sampai)) {
    $query .= " AND tanggal <= ?";
    $params[] = $filter_sampai;
    $types .= 's';
}

$query .= " ORDER BY tanggal DESC, created_at DESC";

// Execute query
$stmt = mysqli_prepare($db, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get statistics
$query_stats = "SELECT COUNT(*) as total FROM kegiatan_ditbinmas WHERE tanggal BETWEEN ? AND ?";
$stmt_stats = mysqli_prepare($db, $query_stats);
mysqli_stmt_bind_param($stmt_stats, "ss", $filter_dari, $filter_sampai);
mysqli_stmt_execute($stmt_stats);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stats));
?>

<style>
    .stats-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .stats-card:hover {
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
        margin-bottom: 15px;
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        color: #1a1f3a;
    }

    .stats-label {
        color: #495057;
        font-size: 0.9rem;
        margin: 0;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border: 2px solid #1a1f3a;
    }

    .filter-card label {
        color: #1a1f3a;
        font-weight: 700;
    }

    .table-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .table-card .card-header {
        background: #1a1f3a;
        color: white;
        padding: 20px;
        border: none;
    }

    .table-card .card-header h4 {
        color: #FFD700;
        font-weight: 700;
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .page-header .title h4 {
        color: #1a1f3a;
        font-weight: 700;
    }

    .btn-primary {
        background: #1a1f3a;
        border-color: #1a1f3a;
        font-weight: 600;
    }

    .btn-primary:hover {
        background: #FFD700;
        border-color: #FFD700;
        color: #1a1f3a;
    }

    .btn-success {
        background: #16a34a;
        border-color: #16a34a;
        font-weight: 600;
    }

    .btn-success:hover {
        background: #15803d;
        border-color: #15803d;
    }

    .btn-info {
        background: #2563eb;
        border-color: #2563eb;
        font-weight: 600;
    }

    .btn-info:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
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
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>📊 Laporan Kegiatan</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Laporan Kegiatan</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <div class="export-buttons">
                <a href="dash.php?page=input-kegiatan" class="btn btn-primary">
                    <i class="icon-copy dw dw-add"></i> Input Kegiatan
                </a>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="icon-copy fa fa-file-excel-o"></i> Export Excel
                </button>
                <button class="btn btn-info" onclick="window.print()">
                    <i class="icon-copy dw dw-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row pb-10">
    <div class="col-xl-12 col-lg-12 col-md-12 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #1a1f3a;">
                    <i class="icon-copy dw dw-file"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total']; ?></h3>
                <p class="stats-label">Total Kegiatan</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <form method="GET" action="dash.php">
        <input type="hidden" name="page" value="laporan-kegiatan">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label class="font-weight-600">📅 Dari Tanggal:</label>
                <input type="date" class="form-control" name="dari" value="<?php echo $filter_dari; ?>">
            </div>
            <div class="col-md-4">
                <label class="font-weight-600">📅 Sampai Tanggal:</label>
                <input type="date" class="form-control" name="sampai" value="<?php echo $filter_sampai; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="icon-copy dw dw-search"></i> Filter
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card table-card mb-30">
    <div class="card-header">
        <h4 class="mb-0">📋 Daftar Kegiatan</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="kegiatan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th width="50">No</th>
                        <th width="100">Tanggal</th>
                        <th width="120">No Surat</th>
                        <th>Kegiatan</th>
                        <th>Lokasi</th>
                        <th>Materi</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td><?php echo htmlspecialchars($row['no_surat']); ?></td>
                            <td><?php echo htmlspecialchars($row['kegiatan']); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td><?php echo htmlspecialchars($row['materi']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetail(<?php echo $row['id']; ?>)">
                                    <i class="dw dw-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a1f3a; color: white; border-bottom: 3px solid #FFD700;">
                <h5 class="modal-title" style="color: #FFD700; font-weight: 700;">📋 Detail Kegiatan Ditbinmas</h5>
                <button type="button" class="close" data-dismiss="modal" style="color: #FFD700; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="src/plugins/datatables/css/responsive.bootstrap4.min.css">
<script src="src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>

<!-- SheetJS for Excel Export -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<script>
    // Initialize DataTable
    $('#kegiatan-table').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: [0, 6],
            orderable: false,
        }],
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        "language": {
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ kegiatan",
            "lengthMenu": "Tampilkan _MENU_ data",
            "search": "Cari:",
            "paginate": {
                "next": '<i class="ion-chevron-right"></i>',
                "previous": '<i class="ion-chevron-left"></i>'
            }
        }
    });

    // View Detail
    function viewDetail(id) {
        $.ajax({
            url: 'ajax/get_kegiatan_detail.php',
            type: 'GET',
            data: {
                id: id
            },
            success: function(response) {
                $('#modal-content').html(response);
                $('#detailModal').modal('show');
            },
            error: function() {
                alert('Gagal memuat detail kegiatan');
            }
        });
    }

    // Export to Excel
    function exportToExcel() {
        var table = document.getElementById('kegiatan-table');
        var wb = XLSX.utils.table_to_book(table, {
            sheet: "Laporan Kegiatan"
        });

        var today = new Date();
        var filename = 'Laporan_Kegiatan_' + today.toISOString().split('T')[0] + '.xlsx';

        XLSX.writeFile(wb, filename);
    }
</script>