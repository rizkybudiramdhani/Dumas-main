<?php
// Get filter parameters
$filter_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-d', strtotime('-30 days'));
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters - Only show Ditresnarkoba related reports
$query = "SELECT l.*, a.Nama AS nama_pelapor
          FROM lapmas l
          LEFT JOIN akun a ON l.Id_akun = a.Id_akun
          WHERE (l.status LIKE '%Ditresnarkoba%' OR l.status = 'Baru' OR l.status = 'Waiting')";
$params = [];
$types = '';

if (!empty($filter_dari)) {
    $query .= " AND DATE(l.tanggal_lapor) >= ?";
    $params[] = $filter_dari;
    $types .= 's';
}

if (!empty($filter_sampai)) {
    $query .= " AND DATE(l.tanggal_lapor) <= ?";
    $params[] = $filter_sampai;
    $types .= 's';
}

if (!empty($filter_status)) {
    $query .= " AND l.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$query .= " ORDER BY l.tanggal_lapor DESC";

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
    SUM(CASE WHEN status = 'Baru' THEN 1 ELSE 0 END) as baru,
    SUM(CASE WHEN status = 'Diproses Ditresnarkoba' THEN 1 ELSE 0 END) as diproses,
    SUM(CASE WHEN status = 'Selesai Ditresnarkoba' THEN 1 ELSE 0 END) as selesai
FROM lapmas
WHERE (status LIKE '%Ditresnarkoba%' OR status = 'Baru' OR status = 'Waiting')
AND DATE(tanggal_lapor) BETWEEN ? AND ?";

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
        color: #1a1f3a;
    }

    .stats-label {
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border: 2px solid #dc3545;
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
        background: #dc3545;
        color: white;
        padding: 20px;
        border: none;
    }

    .table-card .card-header h4 {
        color: white;
        font-weight: 700;
        margin: 0;
    }

    .page-header .title h4 {
        color: #1a1f3a;
        font-weight: 700;
    }

    .btn-primary {
        background: #dc3545;
        border-color: #dc3545;
        font-weight: 600;
    }

    .btn-primary:hover {
        background: #c82333;
        border-color: #bd2130;
    }

    .btn-success {
        background: #28a745;
        border-color: #28a745;
        font-weight: 600;
    }

    .btn-success:hover {
        background: #218838;
        border-color: #1e7e34;
    }

    .btn-info {
        background: #17a2b8;
        border-color: #17a2b8;
        font-weight: 600;
    }

    .btn-info:hover {
        background: #138496;
        border-color: #117a8b;
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
        background: #FFD700;
        color: #1a1f3a;
    }

    .status-diproses {
        background: #17a2b8;
        color: white;
    }

    .status-selesai {
        background: #28a745;
        color: white;
    }

    .status-ditolak {
        background: #dc3545;
        color: white;
    }

    .status-waiting {
        background: #ffc107;
        color: #1a1f3a;
    }
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
            <?php if($role == 'Ditresnarkoba'): ?>
            <a href="dash.php?page=input-laporan-Ditresnarkoba" class="btn btn-primary">
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
    <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #dc3545;">
                    <i class="icon-copy dw dw-file"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['total']; ?></h3>
                <p class="stats-label">Total Laporan</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #ffc107;">
                    <i class="icon-copy dw dw-inbox"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['baru']; ?></h3>
                <p class="stats-label">Laporan Baru</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center py-4">
                <div class="stats-icon mx-auto" style="background: #17a2b8;">
                    <i class="icon-copy dw dw-loading"></i>
                </div>
                <h3 class="stats-number"><?php echo $stats['diproses']; ?></h3>
                <p class="stats-label">Sedang Diproses</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <form method="GET" action="dash.php">
        <input type="hidden" name="page" value="laporan-Ditresnarkoba">
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
                <label class="font-weight-600">🔍 Status:</label>
                <select class="form-control" name="status">
                    <option value="">-- Semua Status --</option>
                    <option value="Baru" <?php echo $filter_status == 'Baru' ? 'selected' : ''; ?>>Baru</option>
                    <option value="Diproses Ditresnarkoba" <?php echo $filter_status == 'Diproses Ditresnarkoba' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="Selesai Ditresnarkoba" <?php echo $filter_status == 'Selesai Ditresnarkoba' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="Waiting" <?php echo $filter_status == 'Waiting' ? 'selected' : ''; ?>>Waiting</option>
                </select>
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
        <h4 class="mb-0">📋 Daftar Laporan Pengaduan Masyarakat</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="laporan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th width="50">No</th>
                        <th width="100">Tanggal</th>
                        <th>Judul</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            // Determine status class
                            $status_class = 'status-baru';
                            if ($row['status'] == 'Diproses Ditresnarkoba') {
                                $status_class = 'status-diproses';
                            } elseif ($row['status'] == 'Selesai Ditresnarkoba') {
                                $status_class = 'status-selesai';
                            } elseif ($row['status'] == 'Waiting') {
                                $status_class = 'status-waiting';
                            } elseif ($row['status'] == 'Ditolak') {
                                $status_class = 'status-ditolak';
                            }
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_lapor'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['judul']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars(substr($row['desk'], 0, 50)); ?>...</small>
                            </td>
                            <td><?php echo htmlspecialchars($row['nama_pelapor']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['lokasi'], 0, 30)); ?><?php echo strlen($row['lokasi']) > 30 ? '...' : ''; ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="dash.php?page=detail-pengaduan&id=<?php echo $row['id_lapmas']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                    <i class="dw dw-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada laporan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SheetJS for Excel Export -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<script>
    // Initialize DataTable
    $('#laporan-table').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: [0, 6],
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

    // Export to Excel
    function exportToExcel() {
        var table = document.getElementById('laporan-table');
        var wb = XLSX.utils.table_to_book(table, {sheet: "Laporan Ditresnarkoba"});

        var today = new Date();
        var filename = 'Laporan_Ditresnarkoba_' + today.toISOString().split('T')[0] + '.xlsx';

        XLSX.writeFile(wb, filename);
    }
</script>