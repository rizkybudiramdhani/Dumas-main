<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/koneksi.php');

// Set header untuk download file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Data_Pengaduan_Masyarakat_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Query untuk mengambil data
$query = "SELECT l.*, a.Nama as nama_user, a.Nomor_hp as nomor_hp
         FROM lapmas l
         LEFT JOIN akun a ON l.Id_akun = a.Id_akun
         ORDER BY l.tanggal_lapor DESC";
$result = mysqli_query($db, $query);

// Output HTML table untuk Excel
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Pengaduan Masyarakat</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #1a1f3a;
            color: #FFD700;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #000;
            text-align: center;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .header-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1a1f3a;
        }
        .export-date {
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header-title">DATA PENGADUAN MASYARAKAT</div>
    <div class="export-date">Dicetak tanggal: <?php echo date('d F Y H:i:s'); ?></div>

    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th width="150">Judul</th>
                <th width="200">Deskripsi</th>
                <th width="150">Nama Pelapor</th>
                <th width="100">Kontak</th>
                <th width="150">Lokasi</th>
                <th width="100">Tanggal Lapor</th>
                <th width="100">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)):
                // Singkat status text
                $status_display = $row['status'];
                if (strpos($status_display, 'Diproses') !== false) {
                    $status_display = 'Diproses';
                } elseif (strpos($status_display, 'Selesai') !== false) {
                    $status_display = 'Selesai';
                }

                $nama_pelapor = $row['nama_user'] ? $row['nama_user'] : ($row['nama'] ? $row['nama'] : 'Anonim');
            ?>
                <tr>
                    <td align="center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                    <td><?php echo htmlspecialchars($row['desk']); ?></td>
                    <td><?php echo htmlspecialchars($nama_pelapor); ?></td>
                    <td><?php echo htmlspecialchars($row['nomor_hp']); ?></td>
                    <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                    <td align="center"><?php echo date('d F Y', strtotime($row['tanggal_lapor'])); ?></td>
                    <td align="center"><?php echo ucfirst($status_display); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
<?php
mysqli_close($db);
?>
