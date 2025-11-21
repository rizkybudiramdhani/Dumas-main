<?php
// Query PHP sudah dibersihkan dari karakter spasi non-breaking.
include_once 'config/koneksi.php';

// Pastikan variabel koneksi $db tersedia dari koneksi.php
if (!isset($db) || $db === false) {
    // Jika koneksi gagal, set $data_rinci ke default dan hentikan eksekusi query
    $data_rinci = [
        'jumlah_laporan' => 0, 'tersangka_penyidikan' => 0, 'tersangka_rehabilitasi' => 0,
        'bb_sabu' => 0, 'bb_ekstasi' => 0, 'bb_ganja' => 0, 'bb_pohon_ganja' => 0,
        'bb_kokain' => 0, 'bb_heroin' => 0, 'bb_happy_five' => 0, 'bb_pil_alprazolam' => 0,
        'bb_ketamin' => 0, 'bb_liquid_vape' => 0, 'last_updated' => date('Y-m-d H:i:s')
    ];
} else {
    // Query data pengungkapan
    $query_rinci = "SELECT * FROM temuan WHERE id_temuan  = 1";
    $result_rinci = mysqli_query($db, $query_rinci);
    $data_rinci = mysqli_fetch_assoc($result_rinci);

    if (!$data_rinci) {
        $data_rinci = [
            'jumlah_laporan' => 0, 'tersangka_penyidikan' => 0, 'tersangka_rehabilitasi' => 0,
            'bb_sabu' => 0, 'bb_ekstasi' => 0, 'bb_ganja' => 0, 'bb_pohon_ganja' => 0,
            'bb_kokain' => 0, 'bb_heroin' => 0, 'bb_happy_five' => 0, 'bb_pil_alprazolam' => 0,
            'bb_ketamin' => 0, 'bb_liquid_vape' => 0, 'last_updated' => date('Y-m-d H:i:s')
        ];
    }
}
?>

<style>
/* Definisikan variabel untuk teks muted agar kontras di latar belakang gelap */
.text-muted-light {
    color: #b0b0b0 !important; /* Abu-abu muda untuk teks kecil */
}

/* Style untuk data-section (Latar Belakang Utama) */
.data-section {
    background-color: #0d1217; /* Latar belakang sangat gelap */
    color: white; /* Default font color */
}

/* Style untuk garis tengah pada judul section */
.section-title-center::before,
.section-title-center::after {
    position: absolute;
    content: "";
    width: 60px;
    height: 2px;
    bottom: -5px;
    /* Menggunakan warna primary/aksen untuk garis */
    background: var(--bs-primary); 
}

.section-title-center::before {
    left: 50%;
    margin-left: -70px; 
}

.section-title-center::after {
    right: 50%;
    margin-right: -70px; 
}

.section-title {
    position: relative;
    padding-bottom: 10px;
}

/* 1. DATA CARD: Warna Latar Belakang Diubah menjadi Hitam */
.data-card {
    background-color: #1a1e23 !important; /* Warna hitam soft untuk card */
    color: white; 
    border: 1px solid #333; /* Border abu-abu tua */
}

/* 2. DATA BOX: Kotak Laporan/Tersangka diubah menjadi abu-abu gelap */
.data-box {
    background-color: #212529 !important; /* Warna lebih gelap dari light */
    transition: all 0.3s ease;
    border: 1px solid #333;
}

.data-box:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    border-color: var(--bs-primary);
}

/* Penyesuaian warna ikon dan teks kecil di dalam data-box */
.data-box .text-muted {
    color: #b0b0b0 !important;
}
.data-box .text-primary {
    /* Pastikan angka utama tetap menonjol */
    color: var(--bs-primary) !important; 
}

/* Penyesuaian warna judul H4 di dalam card */
.data-title {
    color: white !important;
    border-bottom-color: #333 !important;
}

/* Penyesuaian warna garis pemisah di daftar barang bukti */
.data-row {
    border-bottom-color: #333 !important;
}

/* Penyesuaian warna teks barang bukti */
.data-row span, .data-row strong {
    color: white !important;
}
.data-row i {
    color: #b0b0b0 !important; /* Ikon barang bukti */
}
.border-top {
    border-top-color: #333 !important;
}
</style>

<div class="data-section py-5"> 
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="text-center mb-5">
                    <h2 class="section-title section-title-center text-white">
                        Data Pengungkapan
                    </h2>
                    <p class="text-light mt-4">Rekapitulasi total hasil penindakan dan barang bukti</p>
                </div>

                <div class="data-card p-4 rounded shadow">
                    
                    <h4 class="data-title border-bottom border-secondary pb-2 mb-4">A. Jumlah Laporan dan Tersangka</h4>
                    <div class="row mb-5">
                        
                        <div class="col-md-4 mb-3">
                            <div class="data-box p-3 rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted-light mb-1 small">Laporan Polisi</p>
                                    <h4 class="fw-bold mb-0 text-primary">
                                        <?= number_format($data_rinci['jumlah_laporan']); ?>
                                    </h4>
                                </div>
                                <i class="bi bi-file-earmark-bar-graph fs-2 text-muted-light"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="data-box p-3 rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted-light mb-1 small">Tersangka (Penyidikan)</p>
                                    <h4 class="fw-bold mb-0 text-primary">
                                        <?= number_format($data_rinci['tersangka_penyidikan']); ?>
                                    </h4>
                                </div>
                                <i class="bi bi-person-fill fs-2 text-muted-light"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="data-box p-3 rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted-light mb-1 small">Tersangka (Rehabilitasi)</p>
                                    <h4 class="fw-bold mb-0 text-primary">
                                        <?= number_format($data_rinci['tersangka_rehabilitasi']); ?>
                                    </h4>
                                </div>
                                <i class="bi bi-person-check-fill fs-2 text-muted-light"></i>
                            </div>
                        </div>
                    </div>

                    <h4 class="data-title border-bottom border-secondary pb-2 mb-4">B. Barang Bukti Narkotika</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-capsule me-2 text-muted-light"></i>Sabu-sabu</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_sabu'], 2); ?> g</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-circle-fill me-2 text-muted-light"></i>Ekstasi</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_ekstasi']); ?> butir</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-flower1 me-2 text-muted-light"></i>Ganja</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_ganja'], 2); ?> g</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-tree me-2 text-muted-light"></i>Pohon Ganja</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_pohon_ganja']); ?> btg</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-capsule me-2 text-muted-light"></i>Kokain</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_kokain'], 2); ?> g</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-droplet me-2 text-muted-light"></i>Heroin</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_heroin'], 2); ?> g</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-emoji-dizzy me-2 text-muted-light"></i>Happy Five</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_happy_five']); ?> butir</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-capsule me-2 text-muted-light"></i>Alprazolam</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_pil_alprazolam']); ?> butir</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-droplet me-2 text-muted-light"></i>Ketamin</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_ketamin']); ?> g</strong>
                            </div>
                            <div class="data-row d-flex justify-content-between py-2 border-bottom border-light">
                                <span><i class="bi bi-cloud me-2 text-muted-light"></i>Liquid Vape</span>
                                <strong class="data-value"><?= number_format($data_rinci['bb_liquid_vape'], 2); ?> ml</strong>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-3 border-top">
                        <small class="text-muted-light">
                            <i class="bi bi-clock me-1"></i>
                            Update: <strong><?= date('d-m-Y H:i', strtotime($data_rinci['last_updated'])); ?></strong>
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>