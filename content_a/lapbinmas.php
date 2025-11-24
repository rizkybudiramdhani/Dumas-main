<?php
// Get filter parameters
$filter_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-d', strtotime('-30 days'));
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

// Build query with filters
$query = "SELECT * FROM lapbin WHERE 1=1";
$params = [];
$types = '';

if (!empty($filter_dari)) {
    $query .= " AND DATE(tanggal) >= ?";
    $params[] = $filter_dari;
    $types .= 's';
}

if (!empty($filter_sampai)) {
    $query .= " AND DATE(tanggal) <= ?";
    $params[] = $filter_sampai;
    $types .= 's';
}

if (!empty($filter_status)) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($filter_jenis)) {
    $query .= " AND kegiatan LIKE ?";
    $params[] = "%$filter_jenis%";
    $types .= 's';
}

$query .= " ORDER BY tanggal DESC";

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
    SUM(CASE WHEN status = 'baru' THEN 1 ELSE 0 END) as baru,
    SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as diproses,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
FROM lapbin
WHERE DATE(tanggal) BETWEEN ? AND ?";

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
        background: #1a1f3a;
        color: white;
        padding: 20px;
        border: none;
    }

    .table-card .card-header h4 {
        color: #FFD700;
        font-weight: 700;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-baru {
        background: #dc2626;
        color: white;
    }

    .status-diproses {
        background: #ea580c;
        color: white;
    }

    .status-selesai {
        background: #16a34a;
        color: white;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>👮 Laporan Ditbinmas</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Laporan Ditbinmas</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <?php if($role == 'Ditbinmas'): ?>
            <a href="dash.php?page=input-laporan-Ditbinmas" class="btn btn-primary">
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
                <div class="stats-icon mx-auto" style="background: #1a1f3a;">
                    <i class="icon-copy dw dw-file"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total']; ?></h3>
                <p class="stats-label">Total Laporan</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #dc2626;">
                    <i class="icon-copy dw dw-notebook"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['baru']; ?></h3>
                <p class="stats-label">Laporan Baru</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #ea580c;">
                    <i class="icon-copy dw dw-refresh"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['diproses']; ?></h3>
                <p class="stats-label">Sedang Berjalan</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #16a34a;">
                    <i class="icon-copy dw dw-checked"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['selesai']; ?></h3>
                <p class="stats-label">Selesai</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <form method="GET" action="dash.php">
        <input type="hidden" name="page" value="laporan-Ditbinmas">
        <div class="row align-items-end">
            <div class="col-md-2">
                <label class="font-weight-600">📅 Dari:</label>
                <input type="date" class="form-control" name="dari" value="<?php echo $filter_dari; ?>">
            </div>
            <div class="col-md-2">
                <label class="font-weight-600">📅 Sampai:</label>
                <input type="date" class="form-control" name="sampai" value="<?php echo $filter_sampai; ?>">
            </div>
            <div class="col-md-3">
                <label class="font-weight-600">📊 Status:</label>
                <select class="form-control" name="status">
                    <option value="">Semua Status</option>
                    <option value="baru" <?php echo $filter_status == 'baru' ? 'selected' : ''; ?>>Baru</option>
                    <option value="diproses" <?php echo $filter_status == 'diproses' ? 'selected' : ''; ?>>Sedang Berjalan</option>
                    <option value="selesai" <?php echo $filter_status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="font-weight-600">🔍 Jenis Kegiatan:</label>
                <input type="text" class="form-control" name="jenis" value="<?php echo htmlspecialchars($filter_jenis); ?>" placeholder="Cari jenis kegiatan...">
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
        <h4 class="mb-0">📋 Daftar Laporan Ditbinmas</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="laporan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th width="50">No</th>
                        <th width="100">Tanggal</th>
                        <th>Petugas</th>
                        <th>Judul</th>
                        <th>Jenis Kegiatan</th>
                        <th>Lokasi</th>
                        <th>Personil</th>
                        <th>Status</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        $status_class = 'status-' . strtolower($row['status']);
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['pangkat']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['petugas']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td><?php echo htmlspecialchars($row['materi']); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                            <td class="text-center"><?php echo $row['personil']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetail(<?php echo $row['id_lapbin']; ?>)" title="Lihat Detail">
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
            url: 'content_a/detail_lapbin.php',
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
        var wb = XLSX.utils.table_to_book(table, {sheet: "Laporan Ditbinmas"});

        var today = new Date();
        var filename = 'Laporan_Ditbinmas_' + today.toISOString().split('T')[0] + '.xlsx';

        XLSX.writeFile(wb, filename);
    }
</script>