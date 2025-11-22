<?php
// Query berita terkini - SESUAI DATABASE
include_once 'config/koneksi.php';

$query_berita = "SELECT id_berita, judul, gambar, link, desk, tanggal
                FROM berita 
                ORDER BY tanggal DESC 
                LIMIT 4";

$result_berita = mysqli_query($db, $query_berita);
$berita_data = [];

if ($result_berita) {
    while ($row = mysqli_fetch_assoc($result_berita)) {
        $berita_data[] = $row;
    }
}
?>

<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">

            <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                <div class="mb-4">
                    <h2 class="section-title mb-0 text-white">
                        Selamat Datang di Lapor Trisula!
                    </h2>
                </div>

                <div class="d-flex align-items-center mb-4 text-white">
                    <div class="feature-icon text-primary me-3">
                        <i class="bi bi-shield-check fs-2"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold text-white">Menerima Laporan Masyarakat</h5>
                        <p class="mb-0 text-light">Setiap informasi Anda sangat berharga</p>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4 text-white">
                    <div class="feature-icon text-primary me-3">
                        <i class="bi bi-award fs-2"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold text-white">Memberantas Narkoba</h5>
                        <p class="mb-0 text-light">Bersama menciptakan lingkungan sehat</p>
                    </div>
                </div>

                <p class="lead mb-4 text-light">
                    Mari bersama-sama menciptakan lingkungan yang lebih aman dan sehat.
                    Berani melapor, berarti berkontribusi untuk masa depan yang lebih baik!
                </p>

            </div>

            <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.3s">
                <h4 class="fw-bold mb-4 text-white border-bottom border-primary pb-2">
                    <i class="bi bi-newspaper me-2 text-primary"></i>Berita Terkini
                </h4>

                <div class="row g-3">
                    <?php if (!empty($berita_data)): ?>
                        <?php foreach ($berita_data as $berita): ?>
                            <div class="col-6">
                                <a href="<?= htmlspecialchars($berita['link_berita']); ?>"
                                    target="_blank"
                                    class="berita-card border border-secondary rounded shadow-sm overflow-hidden d-block text-decoration-none"
                                    title="<?= htmlspecialchars($berita['judul_berita']); ?>">

                                    <div class="position-relative">
                                        <img src="uploads/berita/<?= htmlspecialchars($berita['gambar_berita']); ?>"
                                            alt="<?= htmlspecialchars($berita['judul_berita']); ?>"
                                            class="w-100 img-fluid berita-img">

                                        <div class="berita-overlay bg-dark bg-opacity-75 p-2 text-white">
                                            <small class="text-warning fw-bold d-block mb-1">Berita</small>
                                            <div class="berita-title small fw-semibold">
                                                <?= htmlspecialchars(substr($berita['judul_berita'], 0, 50)); ?>
                                                <?= strlen($berita['judul_berita']) > 50 ? '...' : ''; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <?php
                        // Placeholder untuk sisa box jika berita kurang dari 4
                        $sisa = 4 - count($berita_data);
                        for ($i = 0; $i < $sisa; $i++):
                        ?>
                            <div class="col-6">
                                <div class="berita-placeholder bg-dark p-3 rounded h-100 d-flex flex-column align-items-center justify-content-center border border-secondary">
                                    <i class="bi bi-newspaper fs-1 text-primary"></i>
                                    <p class="text-light small mb-0 mt-2">Belum ada berita</p>
                                </div>
                            </div>
                        <?php endfor; ?>

                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-dark text-white border-secondary rounded text-center">
                                <i class="bi bi-info-circle me-2 text-warning"></i>
                                Belum ada berita tersedia
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($berita_data)): ?>
                    <div class="text-end mt-3">
                        <a href="https://www.kompas.id/label/Ditresnarkoba" class="text-primary fw-semibold text-decoration-none">
                            Lihat Semua Berita <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>