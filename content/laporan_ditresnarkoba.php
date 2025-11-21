<?php
// Get filter parameters
$filter_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-d', strtotime('-30 days'));
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

// Build query with filters
$query = "SELECT * FROM laporan_resnarkoba WHERE 1=1";
$params = [];
$types = '';

if (!empty($filter_dari)) {
    $query .= " AND DATE(tanggal_operasi) >= ?";
    $params[] = $filter_dari;
    $types .= 's';
}

if (!empty($filter_sampai)) {
    $query .= " AND DATE(tanggal_operasi) <= ?";
    $params[] = $filter_sampai;
    $types .= 's';
}

if (!empty($filter_jenis)) {
    $query .= " AND jenis_operasi LIKE ?";
    $params[] = "%$filter_jenis%";
    $types .= 's';
}

$query .= " ORDER BY tanggal_operasi DESC";

// Execute query
$stmt = mysqli_prepare($db, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get statistics
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(jumlah_tersangka) as total_tersangka,
    SUM(CASE WHEN jenis_operasi = 'Penangkapan' THEN 1 ELSE 0 END) as operasi_tangkap,
    SUM(CASE WHEN jenis_operasi = 'Penyidikan' THEN 1 ELSE 0 END) as operasi_sidik
FROM laporan_resnarkoba
WHERE DATE(tanggal_operasi) BETWEEN ? AND ?";

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
    }

    .filter-card {
        background: #fff;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .table-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .table-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
    }

    .jenis-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .jenis-penangkapan { background: #dc3545; color: white; }
    .jenis-penyidikan { background: #17a2b8; color: white; }
    .jenis-penyelidikan { background: #ffc107; color: #333; }
    .jenis-razia { background: #fd7e14; color: white; }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>🚔 Laporan Ditresnarkoba</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Laporan Ditresnarkoba</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <?php if($role == 'ditresnarkoba'): ?>
            <a href="dash.php?page=input-laporan-ditresnarkoba" class="btn btn-primary">
                <i class="icon-copy dw dw-add"></i> Input Laporan
            </a>
            <?php endif; ?>
            <button class="btn btn-success" onclick="exportToExcel()">
                <i class="icon-copy fa fa-file-excel-o"></i> Export Excel
            </button>
            <button class="btn btn-info" onclick="window.print()">
                <i class="icon-copy dw dw-print"></i> Print
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row pb-10">
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="icon-copy dw dw-file"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total']; ?></h3>
                <p class="stats-label">Total Operasi</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <i class="icon-copy dw dw-user1"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total_tersangka']; ?></h3>
                <p class="stats-label">Total Tersangka</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);">
                    <i class="icon-copy dw dw-padlock1"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['operasi_tangkap']; ?></h3>
                <p class="stats-label">Penangkapan</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                    <i class="icon-copy dw dw-search"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['operasi_sidik']; ?></h3>
                <p class="stats-label">Penyidikan</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <form method="GET" action="dash.php">
        <input type="hidden" name="page" value="laporan-ditresnarkoba">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="font-weight-600">📅 Dari:</label>
                <input type="date" class="form-control" name="dari" value="<?php echo $filter_dari; ?>">
            </div>
            <div class="col-md-3">
                <label class="font-weight-600">📅 Sampai:</label>
                <input type="date" class="form-control" name="sampai" value="<?php echo $filter_sampai; ?>">
            </div>
            <div class="col-md-4">
                <label class="font-weight-600">🔍 Jenis Operasi:</label>
                <input type="text" class="form-control" name="jenis" value="<?php echo htmlspecialchars($filter_jenis); ?>" placeholder="Cari jenis operasi...">
            </div>
            <div class="col-md-2">
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
        <h4 class="mb-0">📋 Daftar Laporan Operasi</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="laporan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th width="50">No</th>
                        <th width="100">Tanggal</th>
                        <th>Tim</th>
                        <th>Jenis Operasi</th>
                        <th>Lokasi</th>
                        <th>Tersangka</th>
                        <th>Barang Bukti</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        $jenis_class = 'jenis-' . strtolower(str_replace(' ', '-', $row['jenis_operasi']));
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_operasi'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['nama_tim']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['ketua_tim']); ?></small>
                            </td>
                            <td>
                                <span class="jenis-badge <?php echo $jenis_class; ?>">
                                    <?php echo htmlspecialchars($row['jenis_operasi']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td class="text-center"><?php echo $row['jumlah_tersangka']; ?></td>
                            <td><?php echo htmlspecialchars(substr($row['barang_bukti'], 0, 30)) . '...'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetail(<?php echo $row['id_laporan']; ?>)" title="Lihat Detail">
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
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">Detail Laporan Operasi</h5>
                <button type="button" class="close" data-dismiss="modal" style="color: white;">
                    <span>&times;</span>
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
    $('#laporan-table').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: [0, 7],
            orderable: false,
        }],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ laporan",
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
            url: 'ajax/get_laporan_ditresnarkoba_detail.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                $('#modal-content').html(response);
                $('#detailModal').modal('show');
            },
            error: function() {
                alert('Gagal memuat detail laporan');
            }
        });
    }

    // Export to Excel
    function exportToExcel() {
        var table = document.getElementById('laporan-table');
        var wb = XLSX.utils.table_to_book(table, {sheet: "Laporan Ditresnarkoba"});
        
        var today = new Date();
        var filename = 'Laporan_Ditresnarkoba_' + today.toISOString().split('T')[0] + '.xlsx';
        
        XLSX.writeFile(wb, filename);
    }
</script>