<?php
/**
 * Handler untuk Update Data Kasus
 * Hanya bisa diakses oleh Ditresnarkoba
 */

session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Check if user is logged in and is Ditresnarkoba
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Ditresnarkoba') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized! Hanya Ditresnarkoba yang dapat mengupdate data kasus.'
    ]);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['action']) || $data['action'] !== 'update_kasus') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

if (!isset($data['id_kasus']) || !isset($data['kec']) || !isset($data['jumlah_kasus']) || !isset($data['tersangka'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap!'
    ]);
    exit;
}

$id_kasus = (int)$data['id_kasus'];
$kec = trim($data['kec']);
$jumlah_kasus = (int)$data['jumlah_kasus'];
$tersangka = (int)$data['tersangka'];

// Validation
if (empty($kec)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nama kecamatan tidak boleh kosong!'
    ]);
    exit;
}

if ($jumlah_kasus < 0 || $tersangka < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Jumlah kasus dan tersangka harus >= 0'
    ]);
    exit;
}

try {
    // Update data kasus
    $query = "UPDATE kasus SET kec = ?, `jumlah kasus` = ?, tersangka = ? WHERE id_kasus = ?";
    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        throw new Exception('Gagal prepare statement: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, "siii", $kec, $jumlah_kasus, $tersangka, $id_kasus);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal mengupdate data: ' . mysqli_error($db));
    }

    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Data kasus berhasil diperbarui!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada perubahan data atau ID tidak ditemukan'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($db);
?>
