<?php
// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/config/koneksi.php';

// Set header to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['Id_akun'])) {
    echo json_encode([
        'error' => true,
        'message' => 'User tidak terautentikasi'
    ]);
    exit;
}

$user_id = $_SESSION['Id_akun'];

try {
    // Query untuk mengambil semua laporan user dengan balasan dari Ditresnarkoba
    $query = "
        SELECT
            l.id_lapmas,
            l.judul,
            l.desk,
            l.lokasi,
            l.status,
            l.tanggal_lapor,
            r.respon as balasan,
            r.tanggal_respon as tanggal_balasan
        FROM lapmas l
        LEFT JOIN respon r ON l.id_lapmas = r.id_lapmas
        LEFT JOIN akun a ON r.a_respon = a.Id_akun
        WHERE l.Id_akun = ?
        AND (r.id_lapmas IS NULL OR a.Role = 'Ditresnarkoba')
        ORDER BY l.tanggal_lapor DESC
    ";

    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan query: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $laporan_list = [];
    $seen_ids = []; // Untuk menghindari duplikasi

    while ($row = mysqli_fetch_assoc($result)) {
        $id_lapmas = $row['id_lapmas'];

        // Jika sudah ada, update balasan jika ada
        if (isset($seen_ids[$id_lapmas])) {
            if (!empty($row['balasan'])) {
                // Tambahkan balasan ke laporan yang sudah ada
                $laporan_list[$seen_ids[$id_lapmas]]['balasan'] = $row['balasan'];
                $laporan_list[$seen_ids[$id_lapmas]]['tanggal_balasan'] = $row['tanggal_balasan'];
            }
        } else {
            // Tambahkan laporan baru
            $laporan_list[] = [
                'id_lapmas' => $row['id_lapmas'],
                'judul' => $row['judul'],
                'desk' => $row['desk'],
                'lokasi' => $row['lokasi'],
                'status' => $row['status'],
                'tanggal_lapor' => $row['tanggal_lapor'],
                'balasan' => $row['balasan'] ?? null,
                'tanggal_balasan' => $row['tanggal_balasan'] ?? null
            ];

            // Simpan index untuk referensi
            $seen_ids[$id_lapmas] = count($laporan_list) - 1;
        }
    }

    mysqli_stmt_close($stmt);

    // Return data sebagai JSON
    echo json_encode($laporan_list);

} catch (Exception $e) {
    // Handle error
    echo json_encode([
        'error' => true,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

// Close database connection
if (isset($db)) {
    mysqli_close($db);
}
?>
