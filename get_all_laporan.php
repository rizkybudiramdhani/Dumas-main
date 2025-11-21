<?php
// Include database connection
require_once 'config/koneksi.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Query to get all laporan for the logged-in user
$query = "SELECT
            id_laporan,
            judul_laporan,
            isi_laporan,
            tanggal_laporan,
            status,
            balasan,
            tanggal_balasan
          FROM tabel_laporan
          WHERE user_id = ?
          ORDER BY tanggal_laporan DESC";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$laporan_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $laporan_list[] = [
        'id_laporan' => $row['id_laporan'],
        'judul_laporan' => $row['judul_laporan'],
        'isi_laporan' => $row['isi_laporan'],
        'tanggal_laporan' => $row['tanggal_laporan'],
        'status' => $row['status'],
        'balasan' => $row['balasan'],
        'tanggal_balasan' => $row['tanggal_balasan']
    ];
}

// Mark all notifications as read
$update_query = "UPDATE tabel_laporan SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$update_stmt = mysqli_prepare($db, $update_query);
mysqli_stmt_bind_param($update_stmt, "i", $user_id);
mysqli_stmt_execute($update_stmt);

echo json_encode($laporan_list);

mysqli_stmt_close($stmt);
mysqli_stmt_close($update_stmt);
mysqli_close($db);
?>
