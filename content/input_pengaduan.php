<?php
// Proses form submit
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $judul_laporan = mysqli_real_escape_string($db, $_POST['judul_laporan']);
    $laporan = mysqli_real_escape_string($db, $_POST['laporan']);
    $lokasi = mysqli_real_escape_string($db, $_POST['lokasi']);
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($db, $_POST['no_hp']);
    
    // Handle file upload (gambar)
    $gambar_paths = [];
    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'][0])) {
        $upload_dir = 'uploads/pengaduan/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $total_files = count($_FILES['gambar']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar']['error'][$i] == 0) {
                $file_name = $_FILES['gambar']['name'][$i];
                $file_tmp = $_FILES['gambar']['tmp_name'][$i];
                $file_size = $_FILES['gambar']['size'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Validasi extension
                $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
                if (in_array($file_ext, $allowed_ext)) {
                    // Validasi size (max 5MB)
                    if ($file_size <= 5242880) {
                        $new_file_name = 'gambar_' . uniqid() . '_' . time() . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            $gambar_paths[] = $upload_path;
                        }
                    } else {
                        $error_message = 'Ukuran file terlalu besar (max 5MB)!';
                    }
                } else {
                    $error_message = 'Format file tidak diizinkan! Hanya JPG, JPEG, PNG, GIF.';
                }
            }
        }
    }
    
    // Convert array ke string (sesuai dengan struktur DB yang ada)
    $gambar_string = !empty($gambar_paths) ? implode(',', $gambar_paths) : '';
    
    // Ambil id_user dari session (jika user login) atau NULL jika anonim
    $id_user = isset($_SESSION['id_users']) ? $_SESSION['id_users'] : NULL;
    
    // Insert ke database
    if (empty($error_message)) {
        $query = "INSERT INTO tabel_laporan 
                  (id_user, judul_laporan, laporan, lokasi, gambar, video, tanggal_lapor, status_laporan, nama, no_hp) 
                  VALUES (?, ?, ?, ?, ?, '', NOW(), 'baru', ?, ?)";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "isssssi", $id_user, $judul_laporan, $laporan, $lokasi, $gambar_string, $nama, $no_hp);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Pengaduan berhasil dikirim! Tim kami akan segera menindaklanjuti laporan Anda.';
            
            // Reset form (redirect to prevent resubmit)
            echo '<script>setTimeout(function(){ window.location.href = "dash.php?page=input-pengaduan&success=1"; }, 2000);</script>';
        } else {
            $error_message = 'Gagal mengirim pengaduan: ' . mysqli_error($db);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Show success message from redirect
if (isset($_GET['success'])) {
    $success_message = 'Pengaduan berhasil dikirim!';
}
?>

<style>
    .form-section {
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .form-section h5 {
        color: #1a1f3a;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #FFD700;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }

    .btn-submit {
        background: #1a1f3a;
        border: none;
        padding: 12px 40px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #FFD700;
        color: #1a1f3a;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
    }

    .btn-submit:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
    }

    .info-box {
        background: #1a1f3a;
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
        border-left: 5px solid #FFD700;
    }

    .info-box h4 {
        margin: 0 0 10px 0;
        font-weight: 700;
        color: #FFD700;
    }

    .info-box p {
        margin: 0;
        color: #ffffff;
        line-height: 1.6;
    }

    .info-box ul {
        margin: 10px 0 0 20px;
        color: #ffffff;
    }

    .file-upload-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }

    .file-upload-wrapper input[type=file] {
        position: absolute;
        left: -9999px;
    }

    .file-upload-label {
        display: block;
        padding: 15px 20px;
        background: #f8f9fa;
        border: 2px dashed #FFD700;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-label:hover {
        background: #fffef0;
        border-color: #1a1f3a;
    }

    .file-upload-label i {
        font-size: 2rem;
        color: #1a1f3a;
        margin-bottom: 10px;
    }

    .preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }

    .preview-item {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #FFD700;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 0, 0, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .required-mark {
        color: #dc3545;
        font-weight: bold;
    }

    .char-counter {
        font-size: 0.875rem;
        color: #6c757d;
        float: right;
        margin-top: 5px;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Input Pengaduan Masyarakat</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input Pengaduan</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a class="btn btn-secondary" href="dash.php?page=lihat-pengaduan">
                <i class="icon-copy dw dw-list"></i> Lihat Semua Pengaduan
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="icon-copy dw dw-checked"></i> Berhasil!</strong> <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="icon-copy dw dw-warning"></i> Error!</strong> <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Info Box -->
<div class="info-box">
    <h4>📢 Informasi Penting</h4>
    <p>Laporkan segala bentuk kejahatan narkoba yang Anda ketahui. Identitas Anda akan kami jaga kerahasiaannya.</p>
    <ul>
        <li>✅ Semua laporan akan ditindaklanjuti dalam 1x24 jam</li>
        <li>✅ Anda dapat melaporkan secara anonim</li>
        <li>✅ Lampirkan bukti foto jika ada (maksimal 5 foto)</li>
        <li>✅ Tim kami akan menghubungi Anda untuk informasi lebih lanjut</li>
    </ul>
</div>

<!-- Form Input -->
<form method="POST" enctype="multipart/form-data" id="form-pengaduan">
    
    <!-- Section 1: Informasi Pelapor -->
    <div class="form-section">
        <h5>👤 Informasi Pelapor</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required-mark">*</span></label>
                    <input class="form-control" type="text" name="nama" 
                           placeholder="Masukkan nama lengkap Anda" 
                           required>
                    <small class="form-text text-muted">Isi dengan nama lengkap atau "Anonim" jika ingin merahasiakan identitas</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nomor Telepon / WhatsApp <span class="required-mark">*</span></label>
                    <input class="form-control" type="tel" name="no_hp" 
                           placeholder="Contoh: 081234567890" 
                           pattern="[0-9]{10,13}" 
                           required>
                    <small class="form-text text-muted">Nomor yang dapat dihubungi (10-13 digit)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Detail Pengaduan -->
    <div class="form-section">
        <h5>📝 Detail Pengaduan</h5>
        
        <div class="form-group">
            <label>Judul Laporan <span class="required-mark">*</span></label>
            <input class="form-control" type="text" name="judul_laporan" 
                   placeholder="Contoh: Peredaran Narkoba di Kampung Baru" 
                   maxlength="200"
                   required>
            <small class="form-text text-muted">Ringkasan singkat tentang laporan Anda (maks 200 karakter)</small>
        </div>

        <div class="form-group">
            <label>Isi Laporan <span class="required-mark">*</span></label>
            <textarea class="form-control" name="laporan" rows="8" 
                      placeholder="Jelaskan secara detail kejadian yang Anda laporkan, termasuk:&#10;- Apa yang terjadi?&#10;- Siapa yang terlibat? (jika diketahui)&#10;- Kapan kejadian berlangsung?&#10;- Bagaimana kronologinya?&#10;- Informasi tambahan lainnya"
                      id="laporan-text"
                      required></textarea>
            <span class="char-counter">0 / 5000 karakter</span>
        </div>

        <div class="form-group">
            <label>Lokasi Kejadian <span class="required-mark">*</span></label>
            <input class="form-control" type="text" name="lokasi" 
                   placeholder="Contoh: Jl. Merdeka No. 123, Kelurahan ABC, Kecamatan XYZ, Medan" 
                   required>
            <small class="form-text text-muted">Alamat lengkap lokasi kejadian</small>
        </div>
    </div>

    <!-- Section 3: Upload Bukti -->
    <div class="form-section">
        <h5>📷 Upload Bukti (Opsional)</h5>
        <p class="text-muted mb-3">Lampirkan foto atau dokumen pendukung jika ada. Maksimal 5 file, masing-masing maksimal 5MB.</p>
        
        <div class="file-upload-wrapper">
            <input type="file" name="gambar[]" id="file-input" accept="image/*" multiple>
            <label for="file-input" class="file-upload-label">
                <i class="icon-copy dw dw-upload"></i>
                <div><strong>Klik untuk upload gambar</strong></div>
                <div class="small">atau drag & drop file di sini</div>
                <div class="small mt-2 text-muted">Format: JPG, JPEG, PNG, GIF | Max: 5MB per file</div>
            </label>
        </div>

        <div class="preview-container" id="preview-container"></div>
    </div>

    <!-- Section 4: Konfirmasi -->
    <div class="form-section">
        <h5>✅ Konfirmasi</h5>
        <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" class="custom-control-input" id="confirm-checkbox" required>
            <label class="custom-control-label" for="confirm-checkbox">
                Saya menyatakan bahwa informasi yang saya berikan adalah <strong>benar</strong> dan dapat <strong>dipertanggungjawabkan</strong>. Saya memahami bahwa laporan palsu dapat dikenakan sanksi sesuai hukum yang berlaku.
            </label>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-submit" id="submit-btn" disabled>
                <i class="icon-copy dw dw-send"></i> Kirim Pengaduan
            </button>
            <button type="reset" class="btn btn-secondary ml-2">
                <i class="icon-copy dw dw-refresh"></i> Reset Form
            </button>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="icon-copy dw dw-padlock1"></i> Data Anda aman dan dijaga kerahasiaannya
            </small>
        </div>
    </div>

</form>

<!-- Success Info -->
<div class="card mt-4" style="border-radius: 15px; border: 2px solid #e9ecef;">
    <div class="card-body text-center py-4">
        <h5 class="text-primary mb-3">💡 Apa yang terjadi setelah Anda melaporkan?</h5>
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="p-3">
                    <div class="h1 text-primary">1️⃣</div>
                    <strong>Laporan Diterima</strong>
                    <p class="small text-muted mb-0">Tim kami menerima laporan Anda</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="p-3">
                    <div class="h1 text-info">2️⃣</div>
                    <strong>Verifikasi</strong>
                    <p class="small text-muted mb-0">Laporan diverifikasi oleh petugas</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="p-3">
                    <div class="h1 text-warning">3️⃣</div>
                    <strong>Tindak Lanjut</strong>
                    <p class="small text-muted mb-0">Tim turun ke lapangan</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="p-3">
                    <div class="h1 text-success">4️⃣</div>
                    <strong>Selesai</strong>
                    <p class="small text-muted mb-0">Kasus ditangani dan diselesaikan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Character counter for textarea
    document.getElementById('laporan-text').addEventListener('input', function() {
        const maxLength = 5000;
        const currentLength = this.value.length;
        const counter = document.querySelector('.char-counter');
        counter.textContent = currentLength + ' / ' + maxLength + ' karakter';
        
        if (currentLength >= maxLength) {
            counter.style.color = '#dc3545';
        } else if (currentLength >= maxLength * 0.9) {
            counter.style.color = '#ffc107';
        } else {
            counter.style.color = '#6c757d';
        }
    });

    // Enable submit button when checkbox is checked
    document.getElementById('confirm-checkbox').addEventListener('change', function() {
        document.getElementById('submit-btn').disabled = !this.checked;
    });

    // File preview
    document.getElementById('file-input').addEventListener('change', function(e) {
        const files = e.target.files;
        const previewContainer = document.getElementById('preview-container');
        
        // Clear previous previews
        previewContainer.innerHTML = '';
        
        // Limit to 5 files
        const maxFiles = 5;
        const filesToProcess = Math.min(files.length, maxFiles);
        
        if (files.length > maxFiles) {
            alert('Maksimal 5 file! File kelebihan akan diabaikan.');
        }
        
        for (let i = 0; i < filesToProcess; i++) {
            const file = files[i];
            
            // Check file size (5MB)
            if (file.size > 5242880) {
                alert(file.name + ' terlalu besar! Maksimal 5MB per file.');
                continue;
            }
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                
                const img = document.createElement('img');
                img.src = event.target.result;
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'preview-remove';
                removeBtn.innerHTML = '×';
                removeBtn.type = 'button';
                removeBtn.onclick = function() {
                    previewItem.remove();
                };
                
                previewItem.appendChild(img);
                previewItem.appendChild(removeBtn);
                previewContainer.appendChild(previewItem);
            };
            
            reader.readAsDataURL(file);
        }
    });

    // Form validation before submit
    document.getElementById('form-pengaduan').addEventListener('submit', function(e) {
        const nama = document.querySelector('input[name="nama"]').value;
        const no_hp = document.querySelector('input[name="no_hp"]').value;
        const judul = document.querySelector('input[name="judul_laporan"]').value;
        const laporan = document.querySelector('textarea[name="laporan"]').value;
        const lokasi = document.querySelector('input[name="lokasi"]').value;
        
        if (!nama || !no_hp || !judul || !laporan || !lokasi) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }
        
        if (laporan.length < 50) {
            e.preventDefault();
            alert('Isi laporan terlalu singkat! Minimal 50 karakter.');
            return false;
        }
        
        // Show loading
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.innerHTML = '<i class="icon-copy dw dw-loading"></i> Mengirim...';
        submitBtn.disabled = true;
    });

    // Auto dismiss alert after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>