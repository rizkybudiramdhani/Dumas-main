<?php
header('Content-Type: application/json');

// Include database connection
require_once('../config/koneksi.php');

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get form data
$kategori = isset($_POST['kategori']) ? mysqli_real_escape_string($db, trim($_POST['kategori'])) : '';
$judul = isset($_POST['judul']) ? mysqli_real_escape_string($db, trim($_POST['judul'])) : '';
$deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($db, trim($_POST['deskripsi'])) : '';
$lokasi = isset($_POST['lokasi']) ? mysqli_real_escape_string($db, trim($_POST['lokasi'])) : '';
$tanggal_kejadian = isset($_POST['tanggal_kejadian']) ? mysqli_real_escape_string($db, trim($_POST['tanggal_kejadian'])) : '';
$anonim = isset($_POST['anonim']) ? 1 : 0;

// Get user data from session if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$no_hp = isset($_SESSION['no_hp']) ? $_SESSION['no_hp'] : '';

// Validate required fields
if (empty($kategori) || empty($judul) || empty($deskripsi) || empty($lokasi) || empty($tanggal_kejadian)) {
    $response['message'] = 'Mohon lengkapi semua field yang wajib diisi!';
    echo json_encode($response);
    exit;
}

// If not anonymous and not logged in, require name and phone
if (!$anonim && !$user_id) {
    if (empty($nama) || empty($no_hp)) {
        $response['message'] = 'Mohon lengkapi nama dan nomor HP atau pilih kirim anonim!';
        echo json_encode($response);
        exit;
    }
}

// Validate phone number (only numbers)
if (!preg_match('/^[0-9]+$/', $no_hp)) {
    $response['message'] = 'No. HP harus berupa angka!';
    echo json_encode($response);
    exit;
}

// Handle file upload (bukti)
$uploaded_files = [];
if (isset($_FILES['bukti']) && !empty($_FILES['bukti']['name'][0])) {
    $target_dir = "../uploads/";
    
    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $max_files = 5;
    $total_files = count($_FILES['bukti']['name']);
    
    if ($total_files > $max_files) {
        $response['message'] = 'Maksimal 5 file yang dapat diupload!';
        echo json_encode($response);
        exit;
    }
    
    for ($i = 0; $i < $total_files; $i++) {
        if ($_FILES['bukti']['error'][$i] === 0) {
            $file_name = $_FILES['bukti']['name'][$i];
            $file_tmp = $_FILES['bukti']['tmp_name'][$i];
            $file_size = $_FILES['bukti']['size'][$i];
            
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'wmv'];
            
            // Validate file extension
            if (!in_array($file_extension, $allowed_extensions)) {
                $response['message'] = 'Format file tidak diizinkan! Hanya gambar (JPG, JPEG, PNG, GIF) dan video (MP4, AVI, MOV, WMV).';
                echo json_encode($response);
                exit;
            }
            
            // Validate file size (10MB max per file)
            if ($file_size > 10485760) {
                $response['message'] = 'Ukuran file terlalu besar! Maksimal 10MB per file.';
                echo json_encode($response);
                exit;
            }
            
            // Generate unique filename
            $new_filename = 'bukti_' . time() . '_' . rand(0, 9999) . '_' . $i . '.' . $file_extension;
            $target_file = "../uploads/" . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $target_file)) {
                $uploaded_files[] = 'uploads/' . $new_filename;
            } else {
                $response['message'] = 'Gagal mengupload file: ' . $file_name;
                echo json_encode($response);
                exit;
            }
        }
    }
}

// Convert uploaded files to JSON for storage
$gambar = !empty($uploaded_files) ? json_encode($uploaded_files) : '';
$video = ''; // Keep for backward compatibility, but we'll use gambar for all files

// Insert to database (tabel_laporan)
$query = "INSERT INTO tabel_laporan 
          (nama, no_hp, judul_laporan, laporan, lokasi, gambar, video, tanggal_lapor, status_laporan) 
          VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'baru')";

$stmt = mysqli_prepare($db, $query);

if (!$stmt) {
    $response['message'] = 'Database error: ' . mysqli_error($db);
    echo json_encode($response);
    exit;
}

// Bind parameters
mysqli_stmt_bind_param($stmt, "sssssss",
    $nama,
    $no_hp,
    $judul,
    $deskripsi,
    $lokasi,
    $gambar,
    $video
);

// Execute query
if (mysqli_stmt_execute($stmt)) {
    $response['success'] = true;
    $response['message'] = 'Laporan Anda berhasil dikirim! Tim kami akan segera menindaklanjuti. Terima kasih atas partisipasi Anda.';
    
    // Optional: Send notification (uncomment if needed)
    /*
    $to = "admin@ditresnarkoba.com";
    $subject = "Laporan Baru: " . $judul_laporan;
    $message = "Laporan baru dari masyarakat:\n\n";
    $message .= "Nama: " . $nama . "\n";
    $message .= "No. HP: " . $no_hp . "\n";
    $message .= "Judul: " . $judul_laporan . "\n";
    $message .= "Lokasi: " . $lokasi . "\n\n";
    $message .= "Isi Laporan:\n" . $laporan;
    
    mail($to, $subject, $message);
    */
} else {
    $response['message'] = 'Gagal menyimpan laporan: ' . mysqli_stmt_error($stmt);
}

// Close statement and connection
mysqli_stmt_close($stmt);
mysqli_close($db);

// Return JSON response
echo json_encode($response);
?>