-- ========================================================================
-- FIX ID_BERITA AUTO INCREMENT
-- Copy paste ini ke phpMyAdmin
-- ========================================================================

USE Ditresnarkoba;

-- Hapus data NULL di id_berita jika ada
DELETE FROM `berita` WHERE `id_berita` IS NULL;

-- Set ulang id_berita untuk data yang sudah ada (berurutan dari 1)
SET @count = 0;
UPDATE `berita` SET `id_berita` = @count:= @count + 1;

-- Ubah kolom id_berita jadi PRIMARY KEY dengan AUTO_INCREMENT
ALTER TABLE `berita`
MODIFY `id_berita` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- DONE! Cek hasilnya:
DESCRIBE `berita`;
SELECT * FROM `berita`;
