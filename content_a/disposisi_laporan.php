<?php
/**
 * Handler untuk Disposisi Laporan
 * Mengubah assigned_to, status, dan menyimpan tanggapan
 */

session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Check if user is logged in and is Ditresnarkoba
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Ditresnarkoba') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized! Hanya Ditresnarkoba yang dapat melakukan disposisi.'
    ]);
    exit;
}

// Validate POST data
if (!isset($_POST['id_lapmas']) || !isset($_POST['assigned_to'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap!'
    ]);
    exit;
}

$id_lapmas = (int)$_POST['id_lapmas'];
$assigned_to_raw = $_POST['assigned_to'];

// Handle multiple selections (array or single)
if (is_array($assigned_to_raw)) {
    $assigned_to_array = array_map('trim', $assigned_to_raw);
} else {
    $assigned_to_array = [trim($assigned_to_raw)];
}

// Validate assigned_to values
$allowed_targets = ['Ditsamapta', 'Ditbinmas'];
foreach ($assigned_to_array as $target) {
    if (!in_array($target, $allowed_targets)) {
        echo json_encode([
            'success' => false,
            'message' => 'Target disposisi tidak valid!'
        ]);
        exit;
    }
}

// Convert to comma-separated string for storage (without spaces for FIND_IN_SET compatibility)
$assigned_to = implode(',', $assigned_to_array);

// Set status based on number of targets
// Pastikan status yang di-set sesuai dengan ENUM di database
if (count($assigned_to_array) == 1) {
    // Jika hanya satu unit, set status "Diproses [Nama Unit]"
    $status = 'Diproses ' . $assigned_to_array[0];
} else {
    // Jika multiple unit, set status "Diproses Multiple Unit"
    $status = 'Diproses Multiple Unit';
}

$tanggapan = isset($_POST['tanggapan']) ? trim($_POST['tanggapan']) : '';

// Get user ID from session
$id_akun = isset($_SESSION['Id_akun']) ? $_SESSION['Id_akun'] : null;

if ($id_akun === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Session tidak valid. Silakan login kembali.'
    ]);
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($db);

    // Update assigned_to dan status
    $query_update = "UPDATE lapmas
                     SET assigned_to = ?,
                         status = ?
                     WHERE id_lapmas = ?";

    $stmt_update = mysqli_prepare($db, $query_update);
    mysqli_stmt_bind_param($stmt_update, "ssi", $assigned_to, $status, $id_lapmas);

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Gagal update laporan: ' . mysqli_error($db));
    }

    mysqli_stmt_close($stmt_update);

    // Insert tanggapan disposisi ke tabel respon (jika ada tanggapan)
    if (!empty($tanggapan)) {
        $query_respon = "INSERT INTO respon (id_lapmas, respon, a_respon, tanggal_respon)
                         VALUES (?, ?, ?, NOW())";

        $stmt_respon = mysqli_prepare($db, $query_respon);
        mysqli_stmt_bind_param($stmt_respon, "isi", $id_lapmas, $tanggapan, $id_akun);

        if (!mysqli_stmt_execute($stmt_respon)) {
            throw new Exception('Gagal menyimpan tanggapan: ' . mysqli_error($db));
        }

        mysqli_stmt_close($stmt_respon);
    }

    // Commit transaction
    mysqli_commit($db);

    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil didisposisi ke ' . $assigned_to
    ]);

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
