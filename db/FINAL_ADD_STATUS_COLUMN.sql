-- ========================================================================
-- TAMBAH KOLOM STATUS DI TABEL LAPMAS
-- Copy paste ini ke phpMyAdmin
-- ========================================================================

USE ditresnarkoba;

-- Tambah kolom status (default: 'Baru')
ALTER TABLE `lapmas`
ADD COLUMN `status` ENUM('Baru', 'Diproses Ditresnarkoba', 'Diproses Ditsamapta', 'Diproses Ditbinmas', 'Selesai')
NOT NULL DEFAULT 'Baru'
AFTER `balasan`;

-- Tambah kolom tanggal_balasan
ALTER TABLE `lapmas`
ADD COLUMN `tanggal_balasan` DATETIME NULL
AFTER `status`;

-- Update data lama yang sudah ada balasan
UPDATE `lapmas`
SET `status` = 'Selesai'
WHERE `balasan` IS NOT NULL AND `balasan` != '';

-- Tambah index
ALTER TABLE `lapmas`
ADD INDEX `idx_status` (`status`);

-- DONE! Cek hasilnya:
DESCRIBE `lapmas`;
SELECT id_lapmas, judul, status FROM `lapmas`;
