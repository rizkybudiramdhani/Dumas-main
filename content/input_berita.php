<?php
// Proses form submit
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $judul_berita = mysqli_real_escape_string($db, $_POST['judul_berita']);
    $link_berita = mysqli_real_escape_string($db, $_POST['link_berita']);
    $deskripsi_berita = mysqli_real_escape_string($db, $_POST['deskripsi_berita']);
    
    // Handle file upload (gambar)
    $gambar_berita = '';
    if (isset($_FILES['gambar_berita']) && $_FILES['gambar_berita']['error'] == 0) {
        $upload_dir = 'uploads/berita/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['gambar_berita']['name'];
        $file_tmp = $_FILES['gambar_berita']['tmp_name'];
        $file_size = $_FILES['gambar_berita']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validasi extension
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_ext, $allowed_ext)) {
            // Validasi size (max 5MB)
            if ($file_size <= 5242880) {
                $timestamp = time();
                $new_file_name = $timestamp . '_' . $file_name;
                $upload_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $gambar_berita = $new_file_name;
                } else {
                    $error_message = 'Gagal mengupload gambar!';
                }
            } else {
                $error_message = 'Ukuran gambar terlalu besar (max 5MB)!';
            }
        } else {
            $error_message = 'Format gambar tidak diizinkan! Hanya JPG, JPEG, PNG, GIF.';
        }
    } else {
        $error_message = 'Gambar berita wajib diupload!';
    }
    
    // Insert ke database
    if (empty($error_message)) {
        $query = "INSERT INTO tabel_berita 
                  (judul_berita, link_berita, deskripsi_berita, gambar_berita, tanggal_upload) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $judul_berita, $link_berita, $deskripsi_berita, $gambar_berita);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Berita berhasil dipublikasikan!';
            
            // Redirect to prevent resubmit
            echo '<script>setTimeout(function(){ window.location.href = "dash.php?page=input-berita&success=1"; }, 2000);</script>';
        } else {
            $error_message = 'Gagal menyimpan berita: ' . mysqli_error($db);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Show success message from redirect
if (isset($_GET['success'])) {
    $success_message = 'Berita berhasil dipublikasikan!';
}
?>

<style>
    .form-section {
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(26, 31, 58, 0.15);
        border: 1px solid rgba(255, 215, 0, 0.2);
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
        color: #1a1f3a;
        margin-bottom: 8px;
    }

    .form-control {
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }

    .btn-publish {
        background: #FFD700;
        border: 2px solid #1a1f3a;
        padding: 12px 40px;
        border-radius: 8px;
        color: #1a1f3a;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-publish:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
        background: #1a1f3a;
        color: #FFD700;
        border-color: #FFD700;
    }

    .btn-publish:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .info-box {
        background: #1a1f3a;
        border: 2px solid #FFD700;
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .info-box h4 {
        margin: 0 0 10px 0;
        font-weight: 700;
        color: #FFD700;
    }

    .info-box p {
        margin: 0;
        opacity: 0.95;
        color: #fff;
    }

    .image-preview-wrapper {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 20px auto;
        display: none;
    }

    .image-preview {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 10px;
        border: 3px solid #FFD700;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
    }

    .remove-preview {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        cursor: pointer;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .remove-preview:hover {
        background: rgba(220, 53, 69, 1);
        transform: scale(1.1);
    }

    .file-upload-label {
        display: block;
        padding: 40px 20px;
        background: #f8f9fa;
        border: 2px dashed #FFD700;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-label:hover {
        background: #fff;
        border-color: #1a1f3a;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2);
    }

    .file-upload-label i {
        font-size: 3rem;
        color: #FFD700;
        margin-bottom: 15px;
    }

    .file-upload-label:hover i {
        color: #1a1f3a;
    }

    .char-counter {
        font-size: 0.875rem;
        color: #6c757d;
        float: right;
        margin-top: 5px;
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #1a1f3a;
        color: #FFD700;
        transform: translateY(-2px);
    }

    .news-card {
        border: 2px solid #FFD700;
        border-radius: 10px;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        border-color: #1a1f3a;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #FFD700;
        color: #1a1f3a;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }

    .breadcrumb-item.active {
        color: #FFD700;
    }

    .breadcrumb-item a {
        color: #1a1f3a;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #FFD700;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Input Berita</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Input Berita</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a class="btn btn-secondary" href="dash.php?page=lihat-berita">
                <i class="icon-copy dw dw-newspaper"></i> Lihat Semua Berita
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
    <h4>📰 Publikasi Berita</h4>
    <p>Form ini digunakan untuk mempublikasikan berita, informasi, atau pengumuman terkait kegiatan Ditresnarkoba kepada masyarakat.</p>
</div>

<!-- Form Input -->
<form method="POST" enctype="multipart/form-data" id="form-berita">
    
    <!-- Section 1: Informasi Berita -->
    <div class="form-section">
        <h5>📝 Informasi Berita</h5>
        
        <div class="form-group">
            <label>Judul Berita <span class="text-danger">*</span></label>
            <input class="form-control" type="text" name="judul_berita" 
                   placeholder="Masukkan judul berita yang menarik dan informatif" 
                   maxlength="500"
                   required>
            <small class="form-text text-muted">Maksimal 500 karakter</small>
        </div>

        <div class="form-group">
            <label>Link Berita Eksternal (Opsional)</label>
            <input class="form-control" type="url" name="link_berita" 
                   placeholder="https://contoh.com/berita-lengkap" 
                   value="https://google.com">
            <small class="form-text text-muted">Link ke artikel lengkap jika ada (default: https://google.com)</small>
        </div>

        <div class="form-group">
            <label>Deskripsi Berita <span class="text-danger">*</span></label>
            <textarea class="form-control" name="deskripsi_berita" rows="10" 
                      placeholder="Tulis deskripsi lengkap berita di sini..."
                      id="deskripsi-text"
                      required></textarea>
            <span class="char-counter">0 / 10000 karakter</span>
            <small class="form-text text-muted">Jelaskan berita secara detail dan lengkap</small>
        </div>
    </div>

    <!-- Section 2: Upload Gambar -->
    <div class="form-section">
        <h5>📷 Gambar Berita</h5>
        <p class="text-muted mb-3">Upload gambar berita yang menarik dan relevan. Maksimal 5MB.</p>
        
        <input type="file" name="gambar_berita" id="file-input" accept="image/*" required style="display: none;">
        <label for="file-input" class="file-upload-label" id="upload-label">
            <i class="icon-copy dw dw-image"></i>
            <div><strong>Klik untuk upload gambar berita</strong></div>
            <div class="small mt-2 text-muted">Format: JPG, JPEG, PNG, GIF | Max: 5MB</div>
            <div class="small text-muted">Rekomendasi: 1200x630px untuk tampilan optimal</div>
        </label>

        <div class="image-preview-wrapper" id="preview-wrapper">
            <img src="" alt="Preview" class="image-preview" id="image-preview">
            <button type="button" class="remove-preview" id="remove-btn">×</button>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="form-section text-center">
        <button type="submit" class="btn btn-publish" id="submit-btn">
            <i class="icon-copy dw dw-send"></i> Publikasikan Berita
        </button>
        <button type="reset" class="btn btn-secondary ml-2">
            <i class="icon-copy dw dw-refresh"></i> Reset Form
        </button>
    </div>

</form>

<!-- Recent News -->
<div class="card mt-4" style="border-radius: 15px; box-shadow: 0 4px 15px rgba(26, 31, 58, 0.15); border: 2px solid #FFD700;">
    <div class="card-header" style="background: #1a1f3a; color: white; border-radius: 13px 13px 0 0; border-bottom: 3px solid #FFD700;">
        <h5 class="mb-0" style="color: #FFD700;">📰 Berita Terbaru</h5>
    </div>
    <div class="card-body" style="background: #fff;">
        <div class="row">
            <?php
            // Get recent news
            $query_recent = "SELECT * FROM tabel_berita ORDER BY tanggal_upload DESC LIMIT 3";
            $result_recent = mysqli_query($db, $query_recent);
            
            if (mysqli_num_rows($result_recent) > 0):
                while ($row = mysqli_fetch_assoc($result_recent)):
            ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 news-card">
                        <?php if ($row['gambar_berita']): ?>
                            <img src="uploads/berita/<?php echo htmlspecialchars($row['gambar_berita']); ?>"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;"
                                 alt="<?php echo htmlspecialchars($row['judul_berita']); ?>">
                        <?php endif; ?>
                        <div class="card-body" style="background: #fff;">
                            <h6 class="card-title" style="color: #1a1f3a; font-weight: 700;"><?php echo htmlspecialchars(substr($row['judul_berita'], 0, 60)); ?>...</h6>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars(substr($row['deskripsi_berita'], 0, 100)); ?>...
                            </p>
                            <small style="color: #FFD700; font-weight: 600;">
                                <i class="icon-copy dw dw-calendar1"></i>
                                <?php echo date('d M Y', strtotime($row['tanggal_upload'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile;
            else: 
            ?>
                <div class="col-12 text-center py-5">
                    <i class="icon-copy dw dw-newspaper" style="font-size: 3rem; color: #FFD700; opacity: 0.5;"></i>
                    <p class="mb-0 mt-2" style="color: #1a1f3a;">Belum ada berita</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Character counter
    document.getElementById('deskripsi-text').addEventListener('input', function() {
        const maxLength = 10000;
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

    // Image preview
    document.getElementById('file-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format gambar tidak diizinkan! Hanya JPG, JPEG, PNG, GIF.');
                this.value = '';
                return;
            }

            // Validate size
            if (file.size > 5242880) {
                alert('Ukuran gambar terlalu besar! Maksimal 5MB.');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('image-preview').src = event.target.result;
                document.getElementById('preview-wrapper').style.display = 'block';
                document.getElementById('upload-label').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove preview
    document.getElementById('remove-btn').addEventListener('click', function() {
        document.getElementById('file-input').value = '';
        document.getElementById('preview-wrapper').style.display = 'none';
        document.getElementById('upload-label').style.display = 'block';
    });

    // Form submit
    document.getElementById('form-berita').addEventListener('submit', function(e) {
        const judul = document.querySelector('input[name="judul_berita"]').value;
        const deskripsi = document.querySelector('textarea[name="deskripsi_berita"]').value;
        const gambar = document.getElementById('file-input').files[0];

        if (!judul || !deskripsi || !gambar) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }

        if (deskripsi.length < 100) {
            e.preventDefault();
            alert('Deskripsi berita terlalu singkat! Minimal 100 karakter.');
            return false;
        }

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Mempublikasikan...';
        submitBtn.disabled = true;
    });

    // Reset form handler
    const resetButtons = document.querySelectorAll('button[type="reset"]');
    resetButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('file-input').value = '';
            document.getElementById('preview-wrapper').style.display = 'none';
            document.getElementById('upload-label').style.display = 'block';
            document.querySelector('.char-counter').textContent = '0 / 10000 karakter';
        });
    });

    // Auto dismiss alert
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
</script>