-- Fix tanggal_respon field to auto-populate with current timestamp
ALTER TABLE `respon`
MODIFY COLUMN `tanggal_respon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
