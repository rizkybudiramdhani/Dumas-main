<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/koneksi.php');

// Query untuk mengambil data
$query = "SELECT l.*, a.Nama as nama_user, a.Nomor_hp as nomor_hp
         FROM lapmas l
         LEFT JOIN akun a ON l.Id_akun = a.Id_akun
         ORDER BY l.tanggal_lapor DESC";
$result = mysqli_query($db, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Pengaduan Masyarakat</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: landscape;
                margin: 1cm;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1a1f3a;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            color: #1a1f3a;
            font-size: 24px;
            font-weight: bold;
        }

        .header h2 {
            margin: 5px 0 0 0;
            color: #FFD700;
            background: #1a1f3a;
            padding: 8px;
            font-size: 18px;
        }

        .export-info {
            text-align: right;
            margin-bottom: 15px;
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #1a1f3a;
            color: #FFD700;
            font-weight: bold;
            padding: 10px 5px;
            border: 1px solid #333;
            text-align: center;
            font-size: 11px;
        }

        td {
            padding: 8px 5px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }

        .status-baru {
            background-color: #ffc107;
            color: #000;
        }

        .status-diproses {
            background-color: #17a2b8;
            color: #fff;
        }

        .status-selesai {
            background-color: #28a745;
            color: #fff;
        }

        .btn-print {
            background-color: #1a1f3a;
            color: #FFD700;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .btn-print:hover {
            background-color: #FFD700;
            color: #1a1f3a;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa fa-print"></i> Cetak / Simpan PDF
        </button>
        <button class="btn-print" onclick="window.close()" style="background-color: #dc3545;">
            <i class="fa fa-times"></i> Tutup
        </button>
    </div>

    <div class="header">
        <h1>KEPOLISIAN DAERAH</h1>
        <h2>DATA PENGADUAN MASYARAKAT</h2>
    </div>

    <div class="export-info">
        Dicetak tanggal: <?php echo date('d F Y H:i:s'); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="15%">Judul</th>
                <th width="25%">Deskripsi</th>
                <th width="12%">Nama Pelapor</th>
                <th width="10%">Kontak</th>
                <th width="15%">Lokasi</th>
                <th width="10%">Tanggal</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)):
                // Determine status class
                $status_class = 'status-badge';
                if ($row['status'] == 'Baru') {
                    $status_class .= ' status-baru';
                } elseif (strpos($row['status'], 'Diproses') !== false) {
                    $status_class .= ' status-diproses';
                } elseif (strpos($row['status'], 'Selesai') !== false) {
                    $status_class .= ' status-selesai';
                }

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
                    <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['desk']); ?></td>
                    <td><?php echo htmlspecialchars($nama_pelapor); ?></td>
                    <td><?php echo htmlspecialchars($row['nomor_hp']); ?></td>
                    <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                    <td align="center"><?php echo date('d M Y', strtotime($row['tanggal_lapor'])); ?></td>
                    <td align="center">
                        <span class="<?php echo $status_class; ?>">
                            <?php echo ucfirst($status_display); ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak dari Sistem Pengaduan Masyarakat</p>
    </div>

    <script>
        // Auto print dialog saat halaman dibuka (opsional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
<?php
mysqli_close($db);
?>
