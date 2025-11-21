<?php
// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_berita = (int)$_GET['id'];

    // Get file to delete
    $query_file = "SELECT gambar_berita FROM tabel_berita WHERE id_berita = ?";
    $stmt_file = mysqli_prepare($db, $query_file);
    mysqli_stmt_bind_param($stmt_file, "i", $id_berita);
    mysqli_stmt_execute($stmt_file);
    $result_file = mysqli_stmt_get_result($stmt_file);
    $file_data = mysqli_fetch_assoc($result_file);

    // Delete record
    $query_delete = "DELETE FROM tabel_berita WHERE id_berita = ?";
    $stmt_delete = mysqli_prepare($db, $query_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id_berita);

    if (mysqli_stmt_execute($stmt_delete)) {
        // Delete file
        if ($file_data && !empty($file_data['gambar_berita'])) {
            $file_path = 'uploads/berita/' . $file_data['gambar_berita'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        echo '<script>alert("Berita berhasil dihapus!"); window.location.href="dash.php?page=lihat-berita";</script>';
    }
}
?>

<style>
    .news-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 2px solid #FFD700;
        box-shadow: 0 4px 15px rgba(26, 31, 58, 0.15);
        height: 100%;
        background: #fff;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        border-color: #1a1f3a;
    }

    .news-card img {
        height: 250px;
        object-fit: cover;
        width: 100%;
    }

    .news-card .card-body {
        padding: 20px;
    }

    .news-card .card-title {
        font-weight: 700;
        color: #1a1f3a;
        margin-bottom: 10px;
        height: 50px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        line-clamp: 2;
    }

    .news-card .card-text {
        color: #6c757d;
        height: 60px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        line-clamp: 3;
    }

    .news-meta {
        font-size: 0.875rem;
        color: #FFD700;
        font-weight: 600;
        padding-top: 10px;
        border-top: 2px solid #FFD700;
    }

    .filter-section {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(26, 31, 58, 0.15);
        border: 2px solid rgba(255, 215, 0, 0.3);
    }

    .filter-section label {
        color: #1a1f3a;
    }

    .filter-section .form-control {
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .filter-section .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }

    .stats-box {
        background: #1a1f3a;
        border: 2px solid #FFD700;
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .stats-box h2 {
        font-size: 3rem;
        font-weight: 700;
        margin: 0;
        color: #FFD700;
    }

    .stats-box p {
        margin: 10px 0 0 0;
        opacity: 0.95;
        color: #fff;
    }

    .action-dropdown {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 10;
    }

    .action-dropdown .btn {
        background: rgba(255, 215, 0, 0.95);
        color: #1a1f3a;
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .action-dropdown .btn:hover {
        background: #1a1f3a;
        color: #FFD700;
        transform: scale(1.1);
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #1a1f3a;
    }

    .empty-state i {
        font-size: 5rem;
        color: #FFD700;
        opacity: 0.5;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .empty-state i:hover {
        font-size: 5rem;
        color: #1a1f3a;
        opacity: 0.8;
        margin-bottom: 20px;
        transform: scale(1.1);
    }

    .empty-state h4 {
        color: #1a1f3a;
    }

    .btn-primary {
        background: #1a1f3a;
        border: 2px solid #FFD700;
        color: #FFD700;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #FFD700;
        color: #1a1f3a;
        border-color: #1a1f3a;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(255, 215, 0, 0.4);
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

    .card-footer {
        border-top: 2px solid #FFD700 !important;
    }

    .modal-header {
        background: #1a1f3a;
        color: white;
        border-bottom: 3px solid #FFD700;
    }

    .modal-header .modal-title {
        color: #FFD700;
    }

    .modal-header .close {
        color: #FFD700;
        opacity: 1;
    }

    .dropdown-item:hover {
        background-color: #FFD700;
        color: #1a1f3a;
    }

    .dropdown-item i {
        margin-right: 5px;
    }

    .page-header .title h4 {
        color: #1a1f3a;
    }

    .news-card img.error {
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-sm.btn-primary {
        background: #1a1f3a;
        border: 2px solid #1a1f3a;
        color: #1a1f3a;
        font-weight: 600;
    }

    .btn-sm.btn-primary:hover {
        background: #1a1f3a;
        color: #FFD700;
        border-color: #FFD700;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Lihat Berita</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dash.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lihat Berita</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a class="btn btn-primary" href="dash.php?page=input-berita">
                <i class="icon-copy dw dw-add"></i> Buat Berita Baru
            </a>
        </div>
    </div>
</div>

<!-- Stats Box -->
<div class="row">
    <div class="col-md-12">
        <div class="stats-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>
                        <?php
                        $query_total = "SELECT COUNT(*) as total FROM tabel_berita";
                        $result_total = mysqli_query($db, $query_total);
                        echo mysqli_fetch_assoc($result_total)['total'];
                        ?>
                    </h2>
                    <p class="mb-0">📰 Total Berita yang Dipublikasikan</p>
                </div>
                <div class="col-md-4 text-right">
                    <i class="icon-copy dw dw-newspaper" style="font-size: 5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row align-items-end">
        <div class="col-md-4">
            <label class="font-weight-600">Cari Berita:</label>
            <input type="text" class="form-control" id="search-input" placeholder="Cari judul berita...">
        </div>
        <div class="col-md-3">
            <label class="font-weight-600">Dari Tanggal:</label>
            <input type="date" class="form-control" id="filter-dari">
        </div>
        <div class="col-md-3">
            <label class="font-weight-600">Sampai Tanggal:</label>
            <input type="date" class="form-control" id="filter-sampai">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-block" onclick="filterBerita()">
                <i class="icon-copy dw dw-search"></i> Filter
            </button>
        </div>
    </div>
</div>

<!-- News Grid -->
<div class="row" id="news-container">
    <?php
    $query = "SELECT * FROM tabel_berita ORDER BY tanggal_upload DESC";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0):
        while ($row = mysqli_fetch_assoc($result)):
    ?>
            <div class="col-md-4 mb-30 news-item"
                data-title="<?php echo strtolower($row['judul_berita']); ?>"
                data-date="<?php echo date('Y-m-d', strtotime($row['tanggal_upload'])); ?>">
                <div class="card news-card">
                    <div class="position-relative">
                        <?php if ($row['gambar_berita']): ?>
                            <img src="uploads/berita/<?php echo htmlspecialchars($row['gambar_berita']); ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($row['judul_berita']); ?>"
                                onerror="this.src='vendors/images/photo1.jpg'; this.classList.add('error');">
                        <?php else: ?>
                            <img src="vendors/images/photo1.jpg"
                                class="card-img-top"
                                alt="No Image">
                        <?php endif; ?>

                        <div class="action-dropdown">
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="dw dw-more"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); viewDetail(<?php echo $row['id_berita']; ?>)">
                                        <i class="dw dw-eye"></i> Lihat Detail
                                    </a>
                                    <a class="dropdown-item" href="<?php echo htmlspecialchars($row['link_berita']); ?>" target="_blank">
                                        <i class="dw dw-share"></i> Buka Link
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); deleteBerita(<?php echo $row['id_berita']; ?>)">
                                        <i class="dw dw-delete-3"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['judul_berita']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['deskripsi_berita']); ?></p>

                        <div class="news-meta">
                            <i class="icon-copy dw dw-calendar1"></i>
                            <?php echo date('d M Y', strtotime($row['tanggal_upload'])); ?>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top">
                        <button class="btn btn-sm btn-primary btn-block" onclick="viewDetail(<?php echo $row['id_berita']; ?>)" type="button">
                            <i class="dw dw-eye"></i> Lihat Selengkapnya
                        </button>
                    </div>
                </div>
            </div>
        <?php
        endwhile;
    else:
        ?>
        <div class="col-12">
            <div class="empty-state">
                <i class="icon-copy dw dw-newspaper"></i>
                <h4>Belum Ada Berita</h4>
                <p class="text-muted">Mulai publikasikan berita pertama Anda!</p>
                <a href="dash.php?page=input-berita" class="btn btn-primary mt-3">
                    <i class="dw dw-add"></i> Buat Berita Baru
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="no-results" style="display: none;">
    <div class="empty-state">
        <i class="icon-copy dw dw-search"></i>
        <h4>Tidak Ada Hasil</h4>
        <p class="text-muted">Tidak ditemukan berita yang sesuai dengan pencarian Anda</p>
    </div>
</div>

<!-- Modal Detail Berita -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Detail Berita</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <img id="modal-image" src="" class="img-fluid mb-3 rounded"
                    style="width: 100%; max-height: 400px; object-fit: cover; border: 3px solid #FFD700;"
                    onerror="this.src='vendors/images/photo1.jpg';">
                <h4 id="modal-judul" style="color: #1a1f3a;"></h4>
                <p class="text-muted" id="modal-tanggal" style="color: #FFD700; font-weight: 600;"></p>
                <hr style="border-color: #FFD700;">
                <div id="modal-deskripsi" style="color: #1a1f3a;"></div>
                <hr style="border-color: #FFD700;">
                <a id="modal-link" href="#" target="_blank" class="btn btn-primary">
                    <i class="dw dw-share"></i> Buka Link Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Berita data from PHP
    const beritaData = {
        <?php
        $result2 = mysqli_query($db, "SELECT * FROM tabel_berita ORDER BY tanggal_upload DESC");
        if ($result2) {
            $items = [];
            while ($row2 = mysqli_fetch_assoc($result2)):
                $items[] = sprintf(
                    '%d: {
                        gambar: %s,
                        judul: %s,
                        tanggal: %s,
                        deskripsi: %s,
                        link: %s
                    }',
                    $row2['id_berita'],
                    json_encode($row2['gambar_berita']),
                    json_encode($row2['judul_berita']),
                    json_encode(date('d M Y, H:i', strtotime($row2['tanggal_upload']))),
                    json_encode(nl2br($row2['deskripsi_berita'])),
                    json_encode($row2['link_berita'])
                );
            endwhile;

            if (!empty($items)) {
                echo implode(',', $items);
            }
        }
        ?>
    };

    // Search functionality
    document.getElementById('search-input').addEventListener('keyup', function() {
        filterBerita();
    });

    // Filter berita
    function filterBerita() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const dariTanggal = document.getElementById('filter-dari').value;
        const sampaiTanggal = document.getElementById('filter-sampai').value;

        let visibleCount = 0;
        const newsItems = document.querySelectorAll('.news-item');

        newsItems.forEach(function(item) {
            const title = item.getAttribute('data-title');
            const date = item.getAttribute('data-date');

            const matchSearch = !searchTerm || title.includes(searchTerm);
            const matchDari = !dariTanggal || date >= dariTanggal;
            const matchSampai = !sampaiTanggal || date <= sampaiTanggal;

            if (matchSearch && matchDari && matchSampai) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noResults = document.getElementById('no-results');
        if (visibleCount === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    // View detail
    function viewDetail(id) {
        const berita = beritaData[id];
        if (berita) {
            document.getElementById('modal-image').src = 'uploads/berita/' + berita.gambar;
            document.getElementById('modal-judul').textContent = berita.judul;
            document.getElementById('modal-tanggal').innerHTML = '<i class="dw dw-calendar1"></i> ' + berita.tanggal + ' WIB';
            document.getElementById('modal-deskripsi').innerHTML = berita.deskripsi;
            document.getElementById('modal-link').href = berita.link;

            // Show modal using Bootstrap 4 method (check if jQuery is available)
            if (typeof $ !== 'undefined') {
                $('#modalDetail').modal('show');
            } else {
                // Fallback for vanilla JS
                const modal = document.getElementById('modalDetail');
                modal.classList.add('show');
                modal.style.display = 'block';
                document.body.classList.add('modal-open');

                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }
    }

    // Delete berita
    function deleteBerita(id) {
        if (confirm('Apakah Anda yakin ingin menghapus berita ini?')) {
            window.location.href = 'dash.php?page=lihat-berita&action=delete&id=' + id;
        }
    }

    // Close modal handler for vanilla JS
    const modalCloseButtons = document.querySelectorAll('[data-dismiss="modal"]');
    modalCloseButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modal = document.getElementById('modalDetail');
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');

            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        });
    });
</script>