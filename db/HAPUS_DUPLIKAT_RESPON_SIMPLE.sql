-- Query sederhana untuk hapus duplikasi respon
-- Jalankan di phpMyAdmin atau MySQL client

-- Langkah 1: Lihat duplikasi yang ada
SELECT
    r1.id_respon,
    r1.id_lapmas,
    r1.respon,
    r1.a_respon,
    r1.tanggal_respon,
    'DUPLIKAT' as status
FROM respon r1
INNER JOIN respon r2
    ON r1.id_lapmas = r2.id_lapmas
    AND r1.respon = r2.respon
    AND r1.a_respon = r2.a_respon
    AND r1.id_respon > r2.id_respon
ORDER BY r1.id_lapmas, r1.tanggal_respon;

-- Langkah 2: Hapus duplikat (simpan yang id_respon terkecil)
DELETE r1 FROM respon r1
INNER JOIN respon r2
WHERE r1.id_lapmas = r2.id_lapmas
  AND r1.respon = r2.respon
  AND r1.a_respon = r2.a_respon
  AND r1.id_respon > r2.id_respon;

-- Langkah 3: Verifikasi hasil
SELECT * FROM respon ORDER BY id_lapmas, tanggal_respon;
