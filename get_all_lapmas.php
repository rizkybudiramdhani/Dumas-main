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

// Query to get all lapmas for the logged-in user
$query = "SELECT
            id_lapmas,
            judul,
            desk,
            lokasi,
            upload,
            tanggal_lapor,
            balasan,
            status,
            tanggal_balasan
          FROM lapmas
          WHERE Id_akun = ?
          ORDER BY tanggal_lapor DESC";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$lapmas_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $lapmas_list[] = [
        'id_lapmas' => $row['id_lapmas'],
        'judul' => $row['judul'],
        'desk' => $row['desk'],
        'lokasi' => $row['lokasi'],
        'upload' => $row['upload'],
        'tanggal_lapor' => $row['tanggal_lapor'],
        'status' => $row['status'] ?? 'Baru',
        'balasan' => $row['balasan'],
        'tanggal_balasan' => $row['tanggal_balasan'] ?? $row['tanggal_lapor']
    ];
}

echo json_encode($lapmas_list);

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
