<?php
// Get filter parameters
$filter_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-d', strtotime('-30 days'));
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters - Filter by assigned_to (role-based access)
// Ditresnarkoba bisa lihat semua laporan, Ditsamapta/Ditbinmas hanya yang assigned ke mereka
// Support multiple assignments (comma-separated)
$query = "SELECT l.*, a.Nama AS nama_pelapor
          FROM lapmas l
          LEFT JOIN akun a ON l.Id_akun = a.Id_akun
          WHERE 1=1";

$params = [];
$types = '';

// Filter berdasarkan role - Gunakan FIND_IN_SET untuk mendukung multiple assignments
if ($role != 'Ditresnarkoba') {
    $query .= " AND FIND_IN_SET(?, l.assigned_to) > 0";
    $params[] = $role;
    $types .= 's';
}

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
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get statistics (filtered by assigned_to)
// Ditresnarkoba bisa lihat semua, Ditsamapta/Ditbinmas hanya yang assigned ke mereka
// Support multiple assignments (comma-separated)
if ($role == 'Ditresnarkoba') {
    $query_stats = "SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Baru' THEN 1 ELSE 0 END) as baru,
        SUM(CASE WHEN status = 'Diproses Ditresnarkoba' THEN 1 ELSE 0 END) as diproses,
        SUM(CASE WHEN status = 'Selesai Ditresnarkoba' THEN 1 ELSE 0 END) as selesai
    FROM lapmas
    WHERE DATE(tanggal_lapor) BETWEEN ? AND ?";

    $stmt_stats = mysqli_prepare($db, $query_stats);
    mysqli_stmt_bind_param($stmt_stats, "ss", $filter_dari, $filter_sampai);
} else {
    // Gunakan FIND_IN_SET untuk mendukung multiple assignments
    $query_stats = "SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Baru' THEN 1 ELSE 0 END) as baru,
        SUM(CASE WHEN status = 'Diproses Ditresnarkoba' THEN 1 ELSE 0 END) as diproses,
        SUM(CASE WHEN status = 'Selesai Ditresnarkoba' THEN 1 ELSE 0 END) as selesai
    FROM lapmas
    WHERE FIND_IN_SET(?, assigned_to) > 0
    AND DATE(tanggal_lapor) BETWEEN ? AND ?";

    $stmt_stats = mysqli_prepare($db, $query_stats);
    mysqli_stmt_bind_param($stmt_stats, "sss", $role, $filter_dari, $filter_sampai);
}
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

    /* Status Baru */
    .status-baru {
        background: #FFD700;
        color: #1a1f3a;
    }

    /* Status Diproses */
    .status-diproses-ditresnarkoba {
        background: #dc3545;
        color: white;
    }

    .status-diproses-ditsamapta {
        background: #1E40AF;
        color: white;
    }

    .status-diproses-ditbinmas {
        background: #28a745;
        color: white;
    }

    /* Status Selesai */
    .status-selesai {
        background: #16a34a;
        color: white;
    }

    .status-selesai-ditresnarkoba {
        background: #b91c1c;
        color: white;
    }

    .status-selesai-ditsamapta {
        background: #1e3a8a;
        color: white;
    }

    .status-selesai-ditbinmas {
        background: #15803d;
        color: white;
    }

    /* Status Lainnya */
    .status-waiting {
        background: #f59e0b;
        color: white;
    }

    .status-ditolak {
        background: #7f1d1d;
        color: white;
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
                        <th>Diarahkan Ke</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            // Determine status class - Support all status types
                            $status_class = 'status-baru';

                            switch ($row['status']) {
                                case 'Baru':
                                    $status_class = 'status-baru';
                                    break;

                                // Status Diproses
                                case 'Diproses Ditresnarkoba':
                                    $status_class = 'status-diproses-ditresnarkoba';
                                    break;
                                case 'Diproses Ditsamapta':
                                    $status_class = 'status-diproses-ditsamapta';
                                    break;
                                case 'Diproses Ditbinmas':
                                    $status_class = 'status-diproses-ditbinmas';
                                    break;

                                // Status Selesai
                                case 'Selesai':
                                    $status_class = 'status-selesai';
                                    break;
                                case 'Selesai Ditresnarkoba':
                                    $status_class = 'status-selesai-ditresnarkoba';
                                    break;
                                case 'Selesai Ditsamapta':
                                    $status_class = 'status-selesai-ditsamapta';
                                    break;
                                case 'Selesai Ditbinmas':
                                    $status_class = 'status-selesai-ditbinmas';
                                    break;

                                // Status Lainnya
                                case 'Waiting':
                                    $status_class = 'status-waiting';
                                    break;
                                case 'Ditolak':
                                    $status_class = 'status-ditolak';
                                    break;

                                default:
                                    $status_class = 'status-baru';
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
                                <?php
                                // Split assigned_to by comma for display (support multiple assignments)
                                $assigned_units = explode(',', $row['assigned_to']);
                                foreach ($assigned_units as $unit):
                                    $unit = trim($unit); // Trim any whitespace
                                    $badge_class = 'badge-secondary';

                                    // Set badge color based on unit
                                    if ($unit == 'Ditresnarkoba') {
                                        $badge_class = 'badge-danger';
                                    } elseif ($unit == 'Ditsamapta') {
                                        $badge_class = 'badge-primary';
                                    } elseif ($unit == 'Ditbinmas') {
                                        $badge_class = 'badge-success';
                                    }
                                ?>
                                <span class="badge <?php echo $badge_class; ?> mr-1 mb-1"><?php echo htmlspecialchars($unit); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="dash.php?page=detail-pengaduan&id=<?php echo $row['id_lapmas']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                    <i class="dw dw-eye"></i>
                                </a>

                                <?php if ($role == 'Ditresnarkoba' && $row['status'] == 'Baru'): ?>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-toggle="dropdown" title="Disposisi Laporan">
                                        <i class="dw dw-share"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item disposisi-btn" href="#" data-id="<?php echo $row['id_lapmas']; ?>" data-target="Ditsamapta">
                                            <i class="dw dw-right-arrow"></i> Ke Ditsamapta
                                        </a>
                                        <a class="dropdown-item disposisi-btn" href="#" data-id="<?php echo $row['id_lapmas']; ?>" data-target="Ditbinmas">
                                            <i class="dw dw-right-arrow"></i> Ke Ditbinmas
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
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

    // Handle Disposisi Laporan
    $(document).on('click', '.disposisi-btn', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        var target = $(this).data('target');
        var targetText = target === 'Ditsamapta' ? 'Ditsamapta' : 'Ditbinmas';

        if (confirm('Yakin ingin mengarahkan laporan ini ke ' + targetText + '?')) {
            $.ajax({
                url: 'content_a/disposisi_laporan.php',
                method: 'POST',
                data: {
                    id_lapmas: id,
                    assigned_to: target
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Laporan berhasil diarahkan ke ' + targetText);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat memproses disposisi');
                }
            });
        }
    });
</script>