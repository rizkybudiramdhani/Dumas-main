<?php
session_start();
require_once 'config/koneksi.php';

// Untuk testing, gunakan user_id = 1 atau sesuaikan dengan user yang ada
$test_user_id = $_SESSION['user_id'] ?? 1;

echo "<h2>Testing Data Lapmas untuk User ID: $test_user_id</h2>";
echo "<hr>";

// Query sama seperti di navbar
$query_notif = "SELECT id_lapmas, judul, desk, lokasi, balasan, status, tanggal_lapor, tanggal_balasan
                FROM lapmas
                WHERE Id_akun = ?
                ORDER BY tanggal_lapor DESC LIMIT 5";

$stmt_notif = mysqli_prepare($db, $query_notif);
mysqli_stmt_bind_param($stmt_notif, "i", $test_user_id);
mysqli_stmt_execute($stmt_notif);
$result_notif = mysqli_stmt_get_result($stmt_notif);

if (mysqli_num_rows($result_notif) > 0) {
    echo "<p style='color: green;'>✅ Ditemukan " . mysqli_num_rows($result_notif) . " data lapmas</p>";

    while ($notif = mysqli_fetch_assoc($result_notif)) {
        echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0; background: #f9f9f9;'>";
        echo "<h3>ID: " . $notif['id_lapmas'] . "</h3>";
        echo "<p><strong>Judul:</strong> " . htmlspecialchars($notif['judul']) . "</p>";
        echo "<p><strong>Deskripsi:</strong> " . htmlspecialchars($notif['desk']) . "</p>";
        echo "<p><strong>Lokasi:</strong> " . htmlspecialchars($notif['lokasi']) . "</p>";
        echo "<p><strong>Status:</strong> " . ($notif['status'] ?? 'NULL') . "</p>";
        echo "<p><strong>Balasan:</strong> " . ($notif['balasan'] ?? 'NULL') . "</p>";
        echo "<p><strong>Tanggal Lapor:</strong> " . $notif['tanggal_lapor'] . "</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ Tidak ada data lapmas untuk user ini</p>";

    // Cek total data di tabel
    $query_total = "SELECT COUNT(*) as total FROM lapmas";
    $result_total = mysqli_query($db, $query_total);
    $total = mysqli_fetch_assoc($result_total)['total'];
    echo "<p>Total data di tabel lapmas: <strong>$total</strong></p>";

    // Cek apakah kolom status ada
    $query_check = "SHOW COLUMNS FROM lapmas LIKE 'status'";
    $result_check = mysqli_query($db, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        echo "<p style='color: green;'>✅ Kolom 'status' sudah ada di tabel lapmas</p>";
    } else {
        echo "<p style='color: red;'>❌ Kolom 'status' BELUM ada di tabel lapmas</p>";
        echo "<p><strong>Action:</strong> Jalankan SQL dari file db/FINAL_ADD_STATUS_COLUMN.sql</p>";
    }
}

mysqli_stmt_close($stmt_notif);
mysqli_close($db);
?>
