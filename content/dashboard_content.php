<?php
// Get statistics from database (disesuaikan dengan struktur database asli)

// Total laporan dari tabel_laporan (belum punya kategori direktorat, jadi ambil semua)
$query_total_pengaduan = "SELECT COUNT(*) as total FROM tabel_laporan";
$result = mysqli_query($db, $query_total_pengaduan);
$total_pengaduan = mysqli_fetch_assoc($result)['total'];

// Total users dari tabel_users
$query_total_users = "SELECT COUNT(*) as total FROM tabel_users WHERE telepon = true";
$result_users = mysqli_query($db, $query_total_users);
$total_users = mysqli_fetch_assoc($result_users)['total'];

// Laporan by status (menggunakan status_laporan dari tabel_laporan)
$query_baru = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan = 'baru'";
$result_baru = mysqli_query($db, $query_baru);
$total_baru = mysqli_fetch_assoc($result_baru)['total'];

// Diproses (gabungan dari semua status 'diproses')
$query_diproses = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan LIKE '%diproses%'";
$result_diproses = mysqli_query($db, $query_diproses);
$total_diproses = mysqli_fetch_assoc($result_diproses)['total'];

// Selesai
$query_selesai = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan LIKE '%selesai%' OR status_laporan = 'selesai'";
$result_selesai = mysqli_query($db, $query_selesai);
$total_selesai = mysqli_fetch_assoc($result_selesai)['total'];

// Data untuk grafik - Laporan per bulan (last 6 months)
$query_chart = "SELECT 
    DATE_FORMAT(tanggal_lapor, '%Y-%m') as bulan,
    DATE_FORMAT(tanggal_lapor, '%b %Y') as bulan_text,
    COUNT(*) as jumlah
FROM tabel_laporan
WHERE tanggal_lapor >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(tanggal_lapor, '%Y-%m')
ORDER BY bulan ASC";

$result_chart = mysqli_query($db, $query_chart);

$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($result_chart)) {
    $chart_labels[] = $row['bulan_text'];
    $chart_data[] = (int)$row['jumlah'];
}

// Laporan hari ini
$query_today = "SELECT COUNT(*) as total FROM tabel_laporan WHERE DATE(tanggal_lapor) = CURDATE()";
$result_today = mysqli_query($db, $query_today);
$total_today = mysqli_fetch_assoc($result_today)['total'];

// Role display name
$role_display = ucfirst($role);
if ($role == 'ditresnarkoba') $role_display = 'Ditresnarkoba';
if ($role == 'ditsamapta') $role_display = 'Ditsamapta';
if ($role == 'ditbinmas') $role_display = 'Ditbinmas';

// Welcome message based on time
$hour = date('H');
if ($hour < 12) {
    $greeting = "Selamat Pagi";
} elseif ($hour < 15) {
    $greeting = "Selamat Siang";
} elseif ($hour < 18) {
    $greeting = "Selamat Sore";
} else {
    $greeting = "Selamat Malam";
}

// Get statistik from tabel_statistik
$query_stats = "SELECT * FROM tabel_statistik WHERE id = 2";
$result_stats = mysqli_query($db, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>

<!-- Custom Styles for Dashboard -->
<style>
    /* Stats Cards Enhancement */
    .stats-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: white;
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0 5px 0;
        color: #1a1f3a;
    }

    .stats-label {
        font-size: 0.9rem;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    /* Welcome Card */
    .welcome-card {
        background: #1a1f3a;
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        border-left: 5px solid #FFD700;
    }

    .welcome-card h2 {
        font-weight: 700;
        margin-bottom: 10px;
        color: #FFD700;
    }

    .welcome-card p {
        color: #ffffff;
        margin-bottom: 0;
    }

    /* Chart Card */
    .chart-card {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }

    /* Table Enhancement */
    .table-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

    .table-card .card-header p {
        color: #ffffff;
    }

    /* Badge Styles */
    .badge-custom {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Quick Actions */
    .quick-action-btn {
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
        background: white;
    }

    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: #1a1f3a;
        background: #1a1f3a;
    }

    .quick-action-btn:hover .quick-action-icon {
        color: #FFD700;
    }

    .quick-action-btn:hover .text-dark {
        color: #FFD700 !important;
    }

    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #1a1f3a;
        transition: all 0.3s ease;
    }

    /* Progress Bars */
    .progress-custom {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
    }

    .progress-custom .progress-bar {
        border-radius: 10px;
    }
</style>

<!-- Welcome Card -->
<div class="welcome-card">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($nama); ?>! 👋</h2>
            <p>Selamat datang di Dashboard <?php echo $role_display; ?>. Anda memiliki <?php echo $total_baru; ?> laporan baru yang menunggu untuk ditindaklanjuti.</p>
        </div>
        <div class="col-md-4 text-right">
            <div style="background: #FFD700; padding: 15px; border-radius: 10px; display: inline-block;">
                <div style="font-size: 0.9rem; color: #1a1f3a; font-weight: 600;">Laporan Hari Ini</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: #1a1f3a;"><?php echo $total_today; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row pb-10">

    <!-- Total Laporan -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #1e40af;">
                    <i class="icon-copy dw dw-file"></i>
                </div>
                <div class="stats-number"><?php echo $total_pengaduan; ?></div>
                <div class="stats-label">Total Laporan</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar" role="progressbar" style="width: 100%; background: #1e40af;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Baru -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #dc2626;">
                    <i class="icon-copy dw dw-inbox"></i>
                </div>
                <div class="stats-number"><?php echo $total_baru; ?></div>
                <div class="stats-label">Laporan Baru</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $total_pengaduan > 0 ? ($total_baru / $total_pengaduan * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diproses -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #ea580c;">
                    <i class="icon-copy dw dw-refresh"></i>
                </div>
                <div class="stats-number"><?php echo $total_diproses; ?></div>
                <div class="stats-label">Diproses</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $total_pengaduan > 0 ? ($total_diproses / $total_pengaduan * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selesai -->
    <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-3" style="background: #16a34a;">
                    <i class="icon-copy dw dw-checked"></i>
                </div>
                <div class="stats-number"><?php echo $total_selesai; ?></div>
                <div class="stats-label">Selesai</div>
                <div class="progress-custom mt-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $total_pengaduan > 0 ? ($total_selesai / $total_pengaduan * 100) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Charts & Quick Actions Row -->
<div class="row">

    <!-- Grafik Laporan Per Bulan -->
    <div class="col-xl-8 col-lg-8 col-md-12 mb-20">
        <div class="card chart-card">
            <div class="card-body pd-20">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3">
                    <div>
                        <h5 class="mb-0" style="color: #1a1f3a; font-weight: 700;">📊 Grafik Laporan Per Bulan</h5>
                        <p class="mb-0 text-muted small" style="font-weight: 600;">Statistik 6 bulan terakhir</p>
                    </div>
                    <div class="form-group mb-0">
                        <select class="form-control form-control-sm" style="border-radius: 10px; border: 2px solid #1a1f3a; color: #1a1f3a; font-weight: 600;">
                            <option value="">Last 6 Months</option>
                            <option value="">Last 12 Months</option>
                            <option value="">This Year</option>
                        </select>
                    </div>
                </div>
                <div id="chart-pengaduan" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & User Stats -->
    <div class="col-xl-4 col-lg-4 col-md-12 mb-20">

        <!-- Total Pengguna Card -->
        <div class="card mb-20" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none; background: #1a1f3a;">
            <div class="card-body text-center pd-20">
                <div class="widget-icon mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #FFD700;">
                    <i class="icon-copy dw dw-user1" style="font-size: 40px; color: #1a1f3a;"></i>
                </div>
                <h2 class="weight-700 mb-2" style="color: #FFD700; font-size: 3rem;"><?php echo $total_users; ?></h2>
                <p class="font-16 mb-3" style="color: #ffffff; font-weight: 600;">Total Masyarakat Terdaftar</p>

                <?php if ($stats): ?>
                    <div class="row text-center pt-2">
                        <div class="col-6">
                            <div style="background: rgba(255, 215, 0, 0.2); padding: 15px; border-radius: 10px; border: 2px solid #FFD700;">
                                <div class="font-24 weight-700" style="color: #FFD700;">
                                    <?php echo $stats['jumlah_penangkapan']; ?>
                                </div>
                                <div class="font-12" style="color: #ffffff;">Penangkapan</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: rgba(255, 215, 0, 0.2); padding: 15px; border-radius: 10px; border: 2px solid #FFD700;">
                                <div class="font-24 weight-700" style="color: #FFD700;">
                                    <?php echo $stats['jumlah_tim']; ?>
                                </div>
                                <div class="font-12" style="color: #ffffff;">Tim Aktif</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border: none;">
            <div class="card-body pd-20">
                <h5 class="mb-3" style="color: #1a1f3a; font-weight: 700;">⚡ Quick Actions</h5>
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="dash.php?page=input-pengaduan" class="quick-action-btn d-block text-decoration-none">
                            <div class="quick-action-icon">
                                <i class="icon-copy dw dw-add-file"></i>
                            </div>
                            <div class="text-dark font-14 weight-500">Buat Laporan</div>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="dash.php?page=lihat-pengaduan" class="quick-action-btn d-block text-decoration-none">
                            <div class="quick-action-icon">
                                <i class="icon-copy dw dw-list"></i>
                            </div>
                            <div class="text-dark font-14 weight-500">Lihat Semua</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="dash.php?page=input-berita" class="quick-action-btn d-block text-decoration-none">
                            <div class="quick-action-icon">
                                <i class="icon-copy dw dw-newspaper"></i>
                            </div>
                            <div class="text-dark font-14 weight-500">Tulis Berita</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="dash.php?page=profile" class="quick-action-btn d-block text-decoration-none">
                            <div class="quick-action-icon">
                                <i class="icon-copy dw dw-user1"></i>
                            </div>
                            <div class="text-dark font-14 weight-500">Profile</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Recent Laporan Table -->
<div class="card table-card mb-30">
    <div class="card-header">
        <h4 class="mb-0">📋 Laporan Terbaru</h4>
        <p class="mb-0 small" style="opacity: 0.9;">10 laporan terakhir yang masuk</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="pengaduan-table">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Judul Laporan</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th width="120">Tanggal</th>
                        <th width="120" class="text-center">Status</th>
                        <th width="100" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get recent laporan (disesuaikan dengan struktur tabel_laporan)
                    $query_recent = "SELECT l.*, u.nama as nama_pelapor 
                                    FROM tabel_laporan l 
                                    LEFT JOIN tabel_users u ON l.id_user = u.id_users
                                    ORDER BY l.tanggal_lapor DESC 
                                    LIMIT 10";
                    $result_recent = mysqli_query($db, $query_recent);

                    $no = 1;
                    if (mysqli_num_rows($result_recent) > 0):
                        while ($row = mysqli_fetch_assoc($result_recent)):
                            // Status badge
                            $status_class = 'secondary';
                            $status_text = $row['status_laporan'];

                            if ($row['status_laporan'] == 'baru') {
                                $status_class = 'warning';
                                $status_text = 'Baru';
                            } elseif (strpos($row['status_laporan'], 'diproses') !== false) {
                                $status_class = 'info';
                                $status_text = 'Diproses';
                            } elseif (strpos($row['status_laporan'], 'selesai') !== false) {
                                $status_class = 'success';
                                $status_text = 'Selesai';
                            }

                            // Nama pelapor
                            $nama_pelapor = $row['nama_pelapor'] ? $row['nama_pelapor'] : ($row['nama'] ? $row['nama'] : 'Anonim');
                    ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td>
                                    <div class="font-14 weight-600"><?php echo htmlspecialchars($row['judul_laporan']); ?></div>
                                    <div class="text-muted small"><?php echo substr(htmlspecialchars($row['laporan']), 0, 50); ?>...</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar mr-2" style="width: 35px; height: 35px; border-radius: 50%; background: #667eea; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                            <?php echo strtoupper(substr($nama_pelapor, 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-14"><?php echo htmlspecialchars($nama_pelapor); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tanggal_lapor'])); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?php echo $status_class; ?> badge-custom">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-outline-primary dropdown-toggle" href="#" role="button" data-toggle="dropdown" style="border-radius: 8px;">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="dash.php?page=detail-pengaduan&id=<?php echo $row['id_laporan']; ?>">
                                                <i class="dw dw-eye"></i> View Detail
                                            </a>
                                            <a class="dropdown-item" href="#"><i class="dw dw-edit2"></i> Edit</a>
                                            <a class="dropdown-item text-danger" href="#"><i class="dw dw-delete-3"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div style="opacity: 0.5;">
                                    <i class="icon-copy dw dw-file" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-0">Belum ada laporan</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ApexCharts Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari PHP
        var chartLabels = <?php echo json_encode($chart_labels); ?>;
        var chartData = <?php echo json_encode($chart_data); ?>;

        // Chart options
        var options = {
            series: [{
                name: 'Jumlah Laporan',
                data: chartData
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#1a1f3a'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'solid',
                opacity: 0.3
            },
            xaxis: {
                categories: chartLabels,
                labels: {
                    style: {
                        colors: '#6c757d',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return Math.floor(val);
                    },
                    style: {
                        colors: '#6c757d',
                        fontSize: '12px'
                    }
                }
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 5
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " laporan"
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart-pengaduan"), options);
        chart.render();
    });
</script>

<!-- DataTable Script -->
<link rel="stylesheet" type="text/css" href="src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="src/plugins/datatables/css/responsive.bootstrap4.min.css">
<script src="src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script>
    $('#pengaduan-table').DataTable({
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
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ laporan",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(filtered from _MAX_ total entries)",
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
</script>