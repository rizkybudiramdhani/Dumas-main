-- ========================================================================
-- BUAT TABEL STATISTIK BARU
-- Tabel ini untuk menyimpan data statistik penangkapan dan tim aktif
-- Copy paste ini ke phpMyAdmin atau HeidiSQL
-- ========================================================================

USE Ditresnarkoba;

-- Buat tabel statistik
CREATE TABLE IF NOT EXISTS `statistik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jumlah_penangkapan` int(11) DEFAULT 0,
  `jumlah_tim` int(11) DEFAULT 0,
  `tahun` year(4) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data default untuk tahun ini
INSERT INTO `statistik` (`id`, `jumlah_penangkapan`, `jumlah_tim`, `tahun`)
VALUES (1, 0, 0, YEAR(CURDATE()));

-- Insert data dummy untuk testing (opsional, bisa dihapus nanti)
INSERT INTO `statistik` (`id`, `jumlah_penangkapan`, `jumlah_tim`, `tahun`)
VALUES (2, 145, 12, YEAR(CURDATE()))
ON DUPLICATE KEY UPDATE
  `jumlah_penangkapan` = 145,
  `jumlah_tim` = 12;

-- DONE! Cek hasilnya:
DESCRIBE `statistik`;
SELECT * FROM `statistik`;
