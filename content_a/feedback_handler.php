<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('../config/koneksi.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['Id_akun'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$role = $_SESSION['role'] ?? '';  // FIXED: lowercase 'role'

// GET FEEDBACK BY KECAMATAN
if ($action == 'get_feedback') {
    $kecamatan = $_GET['kecamatan'] ?? '';

    if (empty($kecamatan)) {
        echo json_encode(['success' => false, 'message' => 'Kecamatan tidak valid']);
        exit;
    }

    $query = "SELECT f.*, a.Nama as nama_user
              FROM feedback_kasus f
              LEFT JOIN akun a ON f.user_id = a.Id_akun
              WHERE f.kec = ?
              ORDER BY f.tanggal_respon DESC";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $kecamatan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $feedbacks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $feedbacks[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $feedbacks]);
    exit;
}

// ADD FEEDBACK
if ($action == 'add_feedback') {
    // Check if user is Ditbinmas or Ditsamapta (case insensitive)
    $role_lower = strtolower(trim($role));
    if ($role_lower != 'ditbinmas' && $role_lower != 'ditsamapta') {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menambahkan feedback. Role Anda: ' . $role]);
        exit;
    }

    $kecamatan = $_POST['kecamatan'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $jenis_tindakan = $_POST['jenis_tindakan'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status = $_POST['status'] ?? 'Direncanakan';
    $user_id = $_SESSION['Id_akun'];

    // Validation
    if (empty($kecamatan) || empty($unit) || empty($jenis_tindakan) || empty($keterangan)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }

    // Ensure unit matches role
    if ($unit != $role) {
        echo json_encode(['success' => false, 'message' => 'Unit tidak sesuai dengan role Anda']);
        exit;
    }

    $query = "INSERT INTO feedback_kasus (kec, unit, jenis_tindakan, keterangan, status, user_id)
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $kecamatan, $unit, $jenis_tindakan, $keterangan, $status, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Respon berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan respon: ' . mysqli_error($db)]);
    }
    exit;
}

// UPDATE FEEDBACK STATUS
if ($action == 'update_status') {
    $feedback_id = $_POST['feedback_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($feedback_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        exit;
    }

    // Check if user owns this feedback or is Ditresnarkoba (can view all)
    $query_check = "SELECT * FROM feedback_kasus WHERE id = ?";
    $stmt_check = mysqli_prepare($db, $query_check);
    mysqli_stmt_bind_param($stmt_check, "i", $feedback_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $feedback = mysqli_fetch_assoc($result_check);

    if (!$feedback) {
        echo json_encode(['success' => false, 'message' => 'Feedback tidak ditemukan']);
        exit;
    }

    // Only the unit that created the feedback can update it
    if ($feedback['unit'] != $role && $role != 'Ditresnarkoba') {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengupdate feedback ini']);
        exit;
    }

    $query = "UPDATE feedback_kasus SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $feedback_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status: ' . mysqli_error($db)]);
    }
    exit;
}

// DELETE FEEDBACK
if ($action == 'delete_feedback') {
    $feedback_id = $_POST['feedback_id'] ?? '';

    if (empty($feedback_id)) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }

    // Check if user owns this feedback
    $query_check = "SELECT * FROM feedback_kasus WHERE id = ?";
    $stmt_check = mysqli_prepare($db, $query_check);
    mysqli_stmt_bind_param($stmt_check, "i", $feedback_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $feedback = mysqli_fetch_assoc($result_check);

    if (!$feedback) {
        echo json_encode(['success' => false, 'message' => 'Feedback tidak ditemukan']);
        exit;
    }

    // Only the unit that created the feedback or Ditresnarkoba can delete it
    if ($feedback['unit'] != $role && $role != 'Ditresnarkoba') {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus feedback ini']);
        exit;
    }

    $query = "DELETE FROM feedback_kasus WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $feedback_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Feedback berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus feedback: ' . mysqli_error($db)]);
    }
    exit;
}

// Invalid action
echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
?>
