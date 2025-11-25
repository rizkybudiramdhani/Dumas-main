<?php
// Query PHP sudah dibersihkan dari karakter spasi non-breaking.
include_once 'config/koneksi.php';

// Pastikan variabel koneksi $db tersedia dari koneksi.php
if (!isset($db) || $db === false) {
    // Jika koneksi gagal, set $data_rinci ke default dan hentikan eksekusi query
    $data_rinci = [
        'jumlah_laporan' => 0,
        'tersangka_penyidikan' => 0,
        'tersangka_rehabilitasi' => 0,
        'barang_bukti' => [],
        'last_updated' => date('Y-m-d H:i:s')
    ];
} else {
    // Query jumlah laporan dari tabel lapmas
    $query_laporan = "SELECT COUNT(*) as total_laporan FROM lapmas WHERE status NOT IN ('Ditolak')";
    $result_laporan = mysqli_query($db, $query_laporan);
    $data_laporan = mysqli_fetch_assoc($result_laporan);
    

    // Query untuk menghitung barang bukti berdasarkan jenis (dinamis)
    $query_bb = "SELECT jenis, SUM(CAST(jumlah AS DECIMAL(10,2))) as total_jumlah FROM temuan WHERE jenis IS NOT NULL AND jenis != '' GROUP BY jenis ORDER BY jenis ASC";
    $result_bb = mysqli_query($db, $query_bb);

    // Array untuk menyimpan barang bukti dinamis
    $barang_bukti = [];
    while($row = mysqli_fetch_assoc($result_bb)) {
        $barang_bukti[] = [
            'jenis' => $row['jenis'],
            'jumlah' => $row['total_jumlah']
        ];
    }

    // Susun data_rinci
    $data_rinci = [
        'jumlah_laporan' => $data_laporan['total_laporan'] ?? 0,
        'tersangka_penyidikan' => $data_tersangka['total_tersangka'] ?? 0,
        'tersangka_rehabilitasi' => 0, // Sesuaikan jika ada data rehabilitasi
        'barang_bukti' => $barang_bukti,
        'last_updated' => date('Y-m-d H:i:s')
    ];
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

                        <div class="col-md-6 mb-3">
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

                        <div class="col-md-6 mb-3">
                            <div class="data-box p-3 rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted-light mb-1 small">Tersangka</p>
                                    <h4 class="fw-bold mb-0 text-primary">
                                        <?= number_format($data_rinci['tersangka_penyidikan']); ?>
                                    </h4>
                                </div>
                                <i class="bi bi-person-fill fs-2 text-muted-light"></i>
                            </div>
                        </div>

                    </div>

                    <h4 class="data-title border-bottom border-secondary pb-2 mb-4">B. Barang Bukti Narkotika</h4>
                    <div class="row">
                        <?php
                        // Function untuk menentukan ikon berdasarkan jenis narkoba
                        function getIcon($jenis) {
                            $jenis_lower = strtolower($jenis);
                            if(strpos($jenis_lower, 'sabu') !== false) return 'bi-capsule';
                            if(strpos($jenis_lower, 'ekstasi') !== false) return 'bi-circle-fill';
                            if(strpos($jenis_lower, 'ganja') !== false && strpos($jenis_lower, 'pohon') === false) return 'bi-flower1';
                            if(strpos($jenis_lower, 'pohon') !== false) return 'bi-tree';
                            if(strpos($jenis_lower, 'kokain') !== false) return 'bi-capsule';
                            if(strpos($jenis_lower, 'heroin') !== false) return 'bi-droplet';
                            if(strpos($jenis_lower, 'happy') !== false) return 'bi-emoji-dizzy';
                            if(strpos($jenis_lower, 'alprazolam') !== false) return 'bi-capsule';
                            if(strpos($jenis_lower, 'ketamin') !== false) return 'bi-droplet';
                            if(strpos($jenis_lower, 'vape') !== false || strpos($jenis_lower, 'liquid') !== false) return 'bi-cloud';
                            return 'bi-capsule'; // default icon
                        }

                        // Function untuk menentukan satuan (semua dalam gram)
                        function getSatuan() {
                            return 'gram'; // semua dalam satuan gram
                        }

                        // Tampilkan barang bukti dinamis
                        if(!empty($data_rinci['barang_bukti'])) {
                            $total_items = count($data_rinci['barang_bukti']);
                            $half = ceil($total_items / 2);

                            // Kolom kiri
                            echo '<div class="col-md-6">';
                            for($i = 0; $i < $half; $i++) {
                                if(isset($data_rinci['barang_bukti'][$i])) {
                                    $item = $data_rinci['barang_bukti'][$i];
                                    $icon = getIcon($item['jenis']);
                                    $satuan = getSatuan();
                                    $jumlah = number_format($item['jumlah'], 0);

                                    echo '<div class="data-row d-flex justify-content-between py-2 border-bottom border-light">';
                                    echo '<span><i class="bi '.$icon.' me-2 text-muted-light"></i>'.htmlspecialchars($item['jenis']).'</span>';
                                    echo '<strong class="data-value">'.$jumlah.' '.$satuan.'</strong>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';

                            // Kolom kanan
                            echo '<div class="col-md-6">';
                            for($i = $half; $i < $total_items; $i++) {
                                if(isset($data_rinci['barang_bukti'][$i])) {
                                    $item = $data_rinci['barang_bukti'][$i];
                                    $icon = getIcon($item['jenis']);
                                    $satuan = getSatuan();
                                    $jumlah = number_format($item['jumlah'], 0);

                                    echo '<div class="data-row d-flex justify-content-between py-2 border-bottom border-light">';
                                    echo '<span><i class="bi '.$icon.' me-2 text-muted-light"></i>'.htmlspecialchars($item['jenis']).'</span>';
                                    echo '<strong class="data-value">'.$jumlah.' '.$satuan.'</strong>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="col-12 text-center text-muted-light py-3">';
                            echo '<i class="bi bi-info-circle me-2"></i>Belum ada data barang bukti';
                            echo '</div>';
                        }
                        ?>
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