-- Script untuk membersihkan duplikasi respon di tabel respon
-- Jalankan script ini jika ada respon yang muncul berkali-kali

-- 1. Cek duplikasi terlebih dahulu
SELECT
    id_lapmas,
    respon,
    tanggal_respon,
    COUNT(*) as jumlah_duplikat
FROM respon
GROUP BY id_lapmas, respon, tanggal_respon
HAVING COUNT(*) > 1;

-- 2. Hapus duplikasi, simpan hanya 1 record terbaru per kombinasi unik
-- HATI-HATI: Backup database terlebih dahulu sebelum menjalankan query ini!

-- Membuat temporary table untuk menyimpan ID yang harus dipertahankan
CREATE TEMPORARY TABLE respon_keep AS
SELECT MIN(id_respon) as id_respon
FROM respon
GROUP BY id_lapmas, respon, a_respon, DATE(tanggal_respon);

-- Hapus semua respon yang tidak ada di list 'keep'
DELETE FROM respon
WHERE id_respon NOT IN (SELECT id_respon FROM respon_keep);

-- Drop temporary table
DROP TEMPORARY TABLE respon_keep;

-- 3. Verifikasi hasil
SELECT * FROM respon ORDER BY tanggal_respon DESC;
