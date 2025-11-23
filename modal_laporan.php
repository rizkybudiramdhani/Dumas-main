<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_ajax_request = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_laporan']);

include_once 'config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_user_session = $_SESSION['Id_akun'] ?? null;
$is_logged_in = !empty($id_user_session);

// Handle POST request
if ($is_ajax_request) {
    $unexpected_output = ob_get_clean();
    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => ''];

    if (!empty($unexpected_output)) {
        error_log("Output tak terduga: " . substr(trim($unexpected_output), 0, 100));
        $response['message'] = 'INTERNAL SERVER ERROR';
        echo json_encode($response);
        exit;
    }

    try {
        if (!isset($db) || !$db) {
            throw new Exception('Koneksi database gagal.');
        }

        $judul = trim($_POST['judul'] ?? '');
        $desk = trim($_POST['desk'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');

        if (empty($judul) || empty($desk) || empty($lokasi)) {
            throw new Exception('Judul, deskripsi, dan lokasi wajib diisi!');
        }

        // Upload file
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_files = [];
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'pdf'];
        $max_file_size = 10485760; // 10MB

        if (isset($_FILES['upload']) && !empty($_FILES['upload']['name'][0])) {
            $total = count($_FILES['upload']['name']);

            if ($total > 5) {
                throw new Exception('Maksimal 5 file!');
            }

            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['upload']['error'][$i] === 0) {
                    $tmp = $_FILES['upload']['tmp_name'][$i];
                    $size = $_FILES['upload']['size'][$i];
                    $ext = strtolower(pathinfo($_FILES['upload']['name'][$i], PATHINFO_EXTENSION));

                    if ($size > $max_file_size) {
                        throw new Exception("Ukuran file maksimal 10MB!");
                    }

                    if (!in_array($ext, $allowed_ext)) {
                        throw new Exception("Format file '$ext' tidak didukung!");
                    }

                    $filename = 'lapmas_' . time() . '_' . uniqid() . '.' . $ext;
                    $destination = $upload_dir . $filename;

                    if (move_uploaded_file($tmp, $destination)) {
                        $upload_files[] = $destination;
                    }
                }
            }
        }

        $upload_str = implode(',', $upload_files);

        // Insert ke tabel lapmas
        $sql = "INSERT INTO lapmas (Id_akun, judul, desk, lokasi, upload, tanggal_lapor, status) VALUES (?, ?, ?, ?, ?, NOW(), 'Baru')";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("issss", $id_user_session, $judul, $desk, $lokasi, $upload_str);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Laporan berhasil dikirim!';
        } else {
            throw new Exception('Gagal menyimpan laporan: ' . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}
?>

<style>
    #modalLaporan .modal-content {
        background: #1a1a1a;
        border-radius: 20px;
        border: 1px solid #333;
        color: #ffffff;
    }
    #modalLaporan .modal-header {
        background: #1E40AF;
        border-radius: 20px 20px 0 0;
        padding: 25px;
        border-bottom: 3px solid #FFD700;
    }
    #modalLaporan .modal-title {
        color: #FFD700;
        font-weight: 700;
    }
    #modalLaporan .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    #modalLaporan .modal-body {
        padding: 30px;
    }
    #laporanForm .form-label {
        color: #FFD700;
        font-weight: 600;
        margin-bottom: 8px;
    }
    #laporanForm .form-control {
        background: #2a2a2a;
        border: 1px solid #444;
        color: #ffffff;
        border-radius: 10px;
        padding: 12px;
    }
    #laporanForm .form-control:focus {
        background: #2a2a2a;
        border-color: #FFD700;
        color: #ffffff;
        box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.15);
    }
    #laporanForm .form-control::placeholder {
        color: #666;
    }
    #laporanForm textarea {
        min-height: 120px;
    }
    #submitBtn {
        background: #FFD700;
        border: none;
        color: #1E40AF;
        font-weight: 700;
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
    }
    #submitBtn:hover {
        background: #1E40AF;
        color: #FFD700;
    }
    #submitBtn:disabled {
        opacity: 0.6;
        background: #6c757d;
        color: #ffffff;
    }
    .alert {
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #10b981;
        color: #ffffff;
    }
    .alert-danger {
        background: #ef4444;
        color: #ffffff;
    }
    .form-text {
        color: #999 !important;
        font-size: 0.85rem;
    }
</style>

<div class="modal fade" id="modalLaporan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-megaphone-fill me-2"></i>Laporan Pengaduan Masyarakat
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="laporanMessage"></div>

                <form id="laporanForm" enctype="multipart/form-data">
                    <input type="hidden" name="submit_laporan" value="1">

                    <div class="mb-3">
                        <label for="judul" class="form-label">
                            <i class="bi bi-file-text me-2"></i>Judul Laporan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="judul" name="judul" placeholder="Masukkan judul laporan" required>
                    </div>

                    <div class="mb-3">
                        <label for="desk" class="form-label">
                            <i class="bi bi-card-text me-2"></i>Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="desk" name="desk" placeholder="Jelaskan detail laporan Anda..." required></textarea>
                        <div class="form-text">Semakin detail informasi, semakin mudah ditindaklanjuti</div>
                    </div>

                    <div class="mb-3">
                        <label for="lokasi" class="form-label">
                            <i class="bi bi-geo-alt-fill me-2"></i>Lokasi <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Masukkan lokasi kejadian" required>
                    </div>

                    <div class="mb-3">
                        <label for="upload" class="form-label">
                            <i class="bi bi-cloud-upload me-2"></i>Upload Bukti
                        </label>
                        <input type="file" class="form-control" id="upload" name="upload[]" multiple accept="image/*,video/*,.pdf">
                        <div class="form-text">Maksimal 5 file, masing-masing 10MB (gambar, video, PDF)</div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none me-2"></span>
                            <i class="bi bi-send-fill me-2"></i>
                            <span id="btnText">Kirim Laporan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const laporanForm = document.getElementById('laporanForm');
    const modalLaporan = document.getElementById('modalLaporan');
    const submitBtn = document.getElementById('submitBtn');

    if (!laporanForm || !modalLaporan || !submitBtn) return;

    modalLaporan.addEventListener('hidden.bs.modal', function() {
        document.getElementById('laporanMessage').innerHTML = '';
        laporanForm.reset();
        submitBtn.disabled = false;
        submitBtn.querySelector('.spinner-border').classList.add('d-none');
        document.getElementById('btnText').textContent = 'Kirim Laporan';
    });

    laporanForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const btnText = document.getElementById('btnText');
        const spinner = submitBtn.querySelector('.spinner-border');
        const messageDiv = document.getElementById('laporanMessage');

        const fileInput = document.getElementById('upload');
        if (fileInput.files.length > 5) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Maksimal 5 file!</div>';
            return;
        }

        for (let i = 0; i < fileInput.files.length; i++) {
            if (fileInput.files[i].size > 10485760) {
                messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Ukuran file maksimal 10MB!</div>';
                return;
            }
        }

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Mengirim...';
        messageDiv.innerHTML = '';

        fetch('modal_laporan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>' + data.message + '</div>';
                laporanForm.reset();
                setTimeout(() => {
                    const modalInstance = bootstrap.Modal.getInstance(modalLaporan);
                    if (modalInstance) modalInstance.hide();
                }, 2000);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill me-2"></i>' + data.message + '</div>';
            }
        })
        .catch(error => {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi kesalahan!</div>';
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Kirim Laporan';
        });
    });
});
</script>

<?php
if (!$is_ajax_request) {
    ob_end_flush();
}
?>
