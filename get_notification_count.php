<?php
// File: ajax/get_notification_count.php
// Returns notification count in JSON format for auto-refresh

session_start();

// Check if user is logged in
if (!isset($_SESSION['nama']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'count' => 0,
        'status' => 'error',
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Include database connection
require_once '../config/koneksi.php';

// Get count of new reports
$query = "SELECT COUNT(*) as total FROM tabel_laporan WHERE status_laporan = 'baru'";
$result = mysqli_query($db, $query);

if ($result) {
    $data = mysqli_fetch_assoc($result);
    $count = $data ? (int)$data['total'] : 0;
    
    // Return JSON
    header('Content-Type: application/json');
    echo json_encode([
        'count' => $count,
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    // Error
    header('Content-Type: application/json');
    echo json_encode([
        'count' => 0,
        'status' => 'error',
        'message' => 'Database query failed'
    ]);
}
?>