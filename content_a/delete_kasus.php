<?php
/**
 * Handler untuk Hapus Data Kasus
 * Hanya bisa diakses oleh Ditresnarkoba
 */

session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Check if user is logged in and is Ditresnarkoba
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Ditresnarkoba') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized! Hanya Ditresnarkoba yang dapat menghapus data kasus.'
    ]);
    exit;
}

// Validate POST data
if (!isset($_POST['action']) || $_POST['action'] !== 'delete_kasus') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

if (!isset($_POST['id_kasus'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID kasus tidak ditemukan!'
    ]);
    exit;
}

$id_kasus = (int)$_POST['id_kasus'];

// Validation
if ($id_kasus <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID kasus tidak valid!'
    ]);
    exit;
}

try {
    // Begin transaction
    mysqli_begin_transaction($db);

    // First, check if kasus exists
    $query_check = "SELECT kec FROM kasus WHERE id_kasus = ?";
    $stmt_check = mysqli_prepare($db, $query_check);

    if (!$stmt_check) {
        throw new Exception('Gagal prepare statement check: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt_check, "i", $id_kasus);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) == 0) {
        mysqli_stmt_close($stmt_check);
        throw new Exception('Data kasus tidak ditemukan!');
    }

    $kasus_data = mysqli_fetch_assoc($result_check);
    $kec_name = $kasus_data['kec'];
    mysqli_stmt_close($stmt_check);

    // Delete related feedback data first (if any)
    $query_delete_feedback = "DELETE FROM feedback_kasus WHERE kec = ?";
    $stmt_delete_feedback = mysqli_prepare($db, $query_delete_feedback);

    if (!$stmt_delete_feedback) {
        throw new Exception('Gagal prepare statement delete feedback: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt_delete_feedback, "s", $kec_name);
    mysqli_stmt_execute($stmt_delete_feedback);
    mysqli_stmt_close($stmt_delete_feedback);

    // Delete kasus data
    $query_delete = "DELETE FROM kasus WHERE id_kasus = ?";
    $stmt_delete = mysqli_prepare($db, $query_delete);

    if (!$stmt_delete) {
        throw new Exception('Gagal prepare statement delete: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt_delete, "i", $id_kasus);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception('Gagal menghapus data: ' . mysqli_error($db));
    }

    $affected_rows = mysqli_stmt_affected_rows($stmt_delete);
    mysqli_stmt_close($stmt_delete);

    if ($affected_rows > 0) {
        // Commit transaction
        mysqli_commit($db);

        echo json_encode([
            'success' => true,
            'message' => 'Data kasus untuk kecamatan "' . $kec_name . '" berhasil dihapus!'
        ]);
    } else {
        // Rollback if no rows affected
        mysqli_rollback($db);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data. ID tidak ditemukan.'
        ]);
    }

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($db);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($db);
?>
