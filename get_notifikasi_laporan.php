<?php
// ============================================
// Notifikasi Laporan untuk Ditsamapta dan Ditbinmas
// Ketika Ditresnarkoba sudah set status menjadi "diproses_ditresnarkoba"
// ============================================

require_once 'config/koneksi.php';

// Set header JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = strtolower($_SESSION['role']);
$response = [
    'success' => true,
    'count' => 0,
    'laporan' => []
];

try {
    // Query berdasarkan role
    if ($role === 'ditsamapta') {
        // Get laporan yang sudah diproses ditresnarkoba dan belum diambil oleh ditsamapta
        $query = "SELECT
                    l.id_laporan,
                    l.judul_laporan,
                    l.lokasi,
                    l.tanggal_lapor,
                    l.status_laporan,
                    l.sedang_diproses_oleh,
                    l.tanggal_mulai_diproses
                  FROM tabel_laporan l
                  WHERE l.status_laporan = 'diproses_ditresnarkoba'
                    AND l.is_notif_ditsamapta = 1
                    AND (l.sedang_diproses_oleh IS NULL OR l.sedang_diproses_oleh = '')
                  ORDER BY l.tanggal_mulai_diproses DESC";

    } elseif ($role === 'ditbinmas') {
        // Get laporan yang sudah diproses ditresnarkoba dan belum diambil oleh ditbinmas
        $query = "SELECT
                    l.id_laporan,
                    l.judul_laporan,
                    l.lokasi,
                    l.tanggal_lapor,
                    l.status_laporan,
                    l.sedang_diproses_oleh,
                    l.tanggal_mulai_diproses
                  FROM tabel_laporan l
                  WHERE l.status_laporan = 'diproses_ditresnarkoba'
                    AND l.is_notif_ditbinmas = 1
                    AND (l.sedang_diproses_oleh IS NULL OR l.sedang_diproses_oleh = '')
                  ORDER BY l.tanggal_mulai_diproses DESC";

    } else {
        // Bukan ditsamapta atau ditbinmas
        echo json_encode($response);
        exit;
    }

    $result = mysqli_query($db, $query);

    if ($result) {
        $laporan_list = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $laporan_list[] = [
                'id_laporan' => $row['id_laporan'],
                'judul_laporan' => $row['judul_laporan'],
                'lokasi' => $row['lokasi'],
                'tanggal_lapor' => $row['tanggal_lapor'],
                'status_laporan' => $row['status_laporan'],
                'tanggal_mulai_diproses' => $row['tanggal_mulai_diproses']
            ];
        }

        $response['count'] = count($laporan_list);
        $response['laporan'] = $laporan_list;
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
mysqli_close($db);
?>
