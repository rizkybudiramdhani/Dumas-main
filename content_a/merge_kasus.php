<?php
/**
 * Handler untuk Merge/Gabung Data Kasus Duplicate
 * Hanya bisa diakses oleh Ditresnarkoba
 */

session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// Check if user is logged in and is Ditresnarkoba
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Ditresnarkoba') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized! Hanya Ditresnarkoba yang dapat menggabungkan data kasus.'
    ]);
    exit;
}

// Validate POST data
if (!isset($_POST['action']) || $_POST['action'] !== 'merge_duplicates') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

try {
    // Begin transaction
    mysqli_begin_transaction($db);

    // Step 1: Find all duplicate kecamatan (same kec name)
    $query_duplicates = "
        SELECT kec,
               GROUP_CONCAT(id_kasus ORDER BY id_kasus ASC) as id_list,
               SUM(`jumlah kasus`) as total_kasus,
               SUM(tersangka) as total_tersangka,
               COUNT(*) as count
        FROM kasus
        GROUP BY kec
        HAVING count > 1
    ";

    $result_duplicates = mysqli_query($db, $query_duplicates);

    if (!$result_duplicates) {
        throw new Exception('Gagal mengambil data duplicate: ' . mysqli_error($db));
    }

    $merged_count = 0;
    $deleted_count = 0;

    // Step 2: Process each duplicate group
    while ($dup_row = mysqli_fetch_assoc($result_duplicates)) {
        $kec_name = $dup_row['kec'];
        $total_kasus = (int)$dup_row['total_kasus'];
        $total_tersangka = (int)$dup_row['total_tersangka'];
        $id_list = explode(',', $dup_row['id_list']);

        // Keep the first ID, delete the rest
        $keep_id = (int)$id_list[0];
        $delete_ids = array_slice($id_list, 1); // IDs to delete

        // Step 3: Update the first record with summed values
        $query_update = "UPDATE kasus SET `jumlah kasus` = ?, tersangka = ? WHERE id_kasus = ?";
        $stmt_update = mysqli_prepare($db, $query_update);

        if (!$stmt_update) {
            throw new Exception('Gagal prepare statement update: ' . mysqli_error($db));
        }

        mysqli_stmt_bind_param($stmt_update, "iii", $total_kasus, $total_tersangka, $keep_id);

        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception('Gagal update data untuk kecamatan ' . $kec_name . ': ' . mysqli_error($db));
        }

        mysqli_stmt_close($stmt_update);

        // Step 4: Delete duplicate records
        if (!empty($delete_ids)) {
            $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
            $query_delete = "DELETE FROM kasus WHERE id_kasus IN ($placeholders)";
            $stmt_delete = mysqli_prepare($db, $query_delete);

            if (!$stmt_delete) {
                throw new Exception('Gagal prepare statement delete: ' . mysqli_error($db));
            }

            // Bind parameters dynamically
            $types = str_repeat('i', count($delete_ids));
            mysqli_stmt_bind_param($stmt_delete, $types, ...$delete_ids);

            if (!mysqli_stmt_execute($stmt_delete)) {
                throw new Exception('Gagal delete data duplicate untuk kecamatan ' . $kec_name . ': ' . mysqli_error($db));
            }

            $deleted = mysqli_stmt_affected_rows($stmt_delete);
            $deleted_count += $deleted;

            mysqli_stmt_close($stmt_delete);
        }

        $merged_count++;
    }

    // Commit transaction
    mysqli_commit($db);

    if ($merged_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Berhasil menggabungkan data kasus duplicate!',
            'merged_count' => $merged_count,
            'deleted_count' => $deleted_count
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada data duplicate yang ditemukan.'
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
