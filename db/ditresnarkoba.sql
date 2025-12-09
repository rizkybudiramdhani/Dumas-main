-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.13.0.7147
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for ditresnarkoba
CREATE DATABASE IF NOT EXISTS `ditresnarkoba` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `ditresnarkoba`;

-- Dumping structure for table ditresnarkoba.akun
CREATE TABLE IF NOT EXISTS `akun` (
  `Id_akun` int(11) NOT NULL AUTO_INCREMENT,
  `Nomor_hp` varchar(255) DEFAULT NULL,
  `Nama` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Role` enum('Masyarakat','Ditresnarkoba','Ditsamapta','Ditbinmas') DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Id_akun`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.akun: ~5 rows (approximately)
INSERT INTO `akun` (`Id_akun`, `Nomor_hp`, `Nama`, `Email`, `Role`, `Password`) VALUES
	(1, '111111111111', 'Tomy Adrian', 'Tomiadrian@gmail.com', 'Ditbinmas', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq'),
	(2, '222222222222', 'Reza Ahlim', 'Rezaahlim@gmail.com', 'Ditsamapta', '$2a$12$5VR6rJZK/ODWfCWEcDause.RBixjyMxm9P01RbsJHYMN8P/pIBHvS'),
	(3, '333333333333', 'Putra Siahan', 'Putrasiahan@gmail.com', 'Ditresnarkoba', '$2y$10$7JWhR.mRJpgtVIEKLvrDbuONOx6qd6CpfWgbmsHroW8FJglRJ5h.y'),
	(4, '085837633968', 'Rizky Budi Ramdhani', 'rizkymedan04@gmail.com', 'Masyarakat', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq'),
	(5, '085183223969', 'iwan', 'iwan@gmail.com', 'Masyarakat', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq');

-- Dumping structure for table ditresnarkoba.berita
CREATE TABLE IF NOT EXISTS `berita` (
  `id_berita` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `desk` varchar(255) DEFAULT NULL,
  `tanggal` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_berita`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.berita: ~0 rows (approximately)
INSERT INTO `berita` (`id_berita`, `judul`, `gambar`, `link`, `desk`, `tanggal`) VALUES
	(1, 'tes1', '1763825535_Screenshot (10).png', 'https://google.com', 'tes1dasdadsassaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2025-11-22 16:32:16');

-- Dumping structure for table ditresnarkoba.feedback_kasus
CREATE TABLE IF NOT EXISTS `feedback_kasus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kec` varchar(255) NOT NULL,
  `unit` enum('Ditbinmas','Ditsamapta') NOT NULL,
  `jenis_tindakan` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `status` enum('Direncanakan','Sedang Berlangsung','Selesai') NOT NULL DEFAULT 'Direncanakan',
  `tanggal_respon` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kec` (`kec`),
  KEY `unit` (`unit`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.feedback_kasus: ~8 rows (approximately)
INSERT INTO `feedback_kasus` (`id`, `kec`, `unit`, `jenis_tindakan`, `keterangan`, `status`, `tanggal_respon`, `updated_at`, `user_id`) VALUES
	(1, 'Kecamatan Contoh', 'Ditsamapta', 'Patroli Rutin', 'Melakukan patroli rutin di area rawan narkoba setiap hari Senin-Jumat pukul 19.00-22.00', 'Sedang Berlangsung', '2024-12-01 08:00:00', NULL, NULL),
	(2, 'Kecamatan Contoh', 'Ditbinmas', 'Sosialisasi dan Bimbingan', 'Mengadakan sosialisasi bahaya narkoba di sekolah-sekolah dan kelurahan', 'Sedang Berlangsung', '2024-12-02 09:00:00', NULL, NULL),
	(3, 'Kecamatan Contoh', 'Ditsamapta', 'Patroli Rutin', 'Melakukan patroli rutin di area rawan narkoba setiap hari Senin-Jumat pukul 19.00-22.00', 'Sedang Berlangsung', '2024-12-01 08:00:00', NULL, NULL),
	(4, 'Kecamatan Contoh', 'Ditbinmas', 'Sosialisasi dan Bimbingan', 'Mengadakan sosialisasi bahaya narkoba di sekolah-sekolah dan kelurahan', 'Sedang Berlangsung', '2024-12-02 09:00:00', NULL, NULL),
	(5, 'Kecamatan Contoh', 'Ditsamapta', 'Patroli Rutin', 'Melakukan patroli rutin di area rawan narkoba setiap hari Senin-Jumat pukul 19.00-22.00', 'Sedang Berlangsung', '2024-12-01 08:00:00', NULL, NULL),
	(6, 'Kecamatan Contoh', 'Ditbinmas', 'Sosialisasi dan Bimbingan', 'Mengadakan sosialisasi bahaya narkoba di sekolah-sekolah dan kelurahan', 'Sedang Berlangsung', '2024-12-02 09:00:00', NULL, NULL),
	(7, 'Kecamatan Contoh', 'Ditsamapta', 'Patroli Rutin', 'Melakukan patroli rutin di area rawan narkoba setiap hari Senin-Jumat pukul 19.00-22.00', 'Sedang Berlangsung', '2024-12-01 08:00:00', NULL, NULL),
	(8, 'Kecamatan Contoh', 'Ditbinmas', 'Sosialisasi dan Bimbingan', 'Mengadakan sosialisasi bahaya narkoba di sekolah-sekolah dan kelurahan', 'Sedang Berlangsung', '2024-12-02 09:00:00', NULL, NULL),
	(9, 'Deli serdang', 'Ditsamapta', 'Patroli Khusus', 'ss', 'Sedang Berlangsung', '2025-12-09 17:48:27', NULL, 2);

-- Dumping structure for table ditresnarkoba.kasus
CREATE TABLE IF NOT EXISTS `kasus` (
  `id_kasus` int(11) NOT NULL AUTO_INCREMENT,
  `tersangka` int(50) DEFAULT NULL,
  `jumlah kasus` int(50) DEFAULT NULL,
  `kec` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_kasus`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.kasus: ~2 rows (approximately)
INSERT INTO `kasus` (`id_kasus`, `tersangka`, `jumlah kasus`, `kec`) VALUES
	(1, 10, 11, 'Deli serdang'),
	(2, 22, 11, 'Deli serdang');

-- Dumping structure for table ditresnarkoba.lapbin
CREATE TABLE IF NOT EXISTS `lapbin` (
  `id_lapbin` int(11) NOT NULL AUTO_INCREMENT,
  `Id_akun` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `materi` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NULL DEFAULT NULL,
  `personil` int(50) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `petugas` varchar(255) DEFAULT NULL,
  `pangkat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_lapbin`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapbin: ~0 rows (approximately)
INSERT INTO `lapbin` (`id_lapbin`, `Id_akun`, `judul`, `status`, `materi`, `tanggal`, `personil`, `lokasi`, `petugas`, `pangkat`) VALUES
	(1, 1, 'b', 'Baru', 'b', '2025-11-23 12:46:00', 12, 'b', 'Tomy Adrian', 'IPDA');

-- Dumping structure for table ditresnarkoba.lapditres
CREATE TABLE IF NOT EXISTS `lapditres` (
  `id_lapditres` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id_lapditres`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapditres: ~0 rows (approximately)

-- Dumping structure for table ditresnarkoba.lapmas
CREATE TABLE IF NOT EXISTS `lapmas` (
  `id_lapmas` int(11) NOT NULL AUTO_INCREMENT,
  `Id_akun` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `desk` varchar(255) DEFAULT NULL,
  `lokasi` varchar(2255) DEFAULT NULL,
  `upload` varchar(255) DEFAULT NULL,
  `tanggal_lapor` varchar(255) DEFAULT NULL,
  `status` enum('Baru','Diproses Ditresnarkoba','Diproses Ditsamapta','Diproses Ditbinmas','Selesai','Selesai Ditresnarkoba','Selesai Ditsamapta','Selesai Ditbinmas','Waiting','Ditolak') NOT NULL DEFAULT 'Baru',
  PRIMARY KEY (`id_lapmas`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapmas: ~4 rows (approximately)
INSERT INTO `lapmas` (`id_lapmas`, `Id_akun`, `judul`, `desk`, `lokasi`, `upload`, `tanggal_lapor`, `status`) VALUES
	(8, 5, 'c', 'c', 'c', '', '2025-11-23 01:17:46', 'Diproses Ditresnarkoba'),
	(9, 4, 'd', 'd', 'd', '', '2025-11-23 18:57:34', 'Diproses Ditresnarkoba'),
	(11, 4, 'tes', 'tes', 'tes', '', '2025-11-24 19:21:19', 'Diproses Ditsamapta'),
	(12, 4, 's', 's', 's', '', '2025-11-25 08:18:47', 'Selesai');

-- Dumping structure for table ditresnarkoba.lapsam
CREATE TABLE IF NOT EXISTS `lapsam` (
  `id_lapsam` int(11) NOT NULL AUTO_INCREMENT,
  `Id_akun` int(50) NOT NULL DEFAULT 0,
  `judul` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `kegiatan` varchar(255) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `personil` int(50) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `petugas` varchar(255) DEFAULT NULL,
  `pangkat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_lapsam`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapsam: ~9 rows (approximately)
INSERT INTO `lapsam` (`id_lapsam`, `Id_akun`, `judul`, `status`, `kegiatan`, `tanggal`, `personil`, `lokasi`, `petugas`, `pangkat`) VALUES
	(1, 2, 'e', 'Baru', 'sasdasdasd', '0000-00-00 00:00:00', 1, 'e', 'Reza Ahlim', 'Briptu'),
	(2, 2, 'tes dis', 'Baru', 'tes', '0000-00-00 00:00:00', 2, 'tes dis', 'Reza Ahlim', 'Kombes'),
	(3, 2, 'tes dis', 'Baru', 'tes', '0000-00-00 00:00:00', 2, 'tes dis', 'Reza Ahlim', 'Kombes'),
	(4, 2, 'tes dis', 'Baru', 'tes', '0000-00-00 00:00:00', 2, 'tes dis', 'Reza Ahlim', 'Kombes'),
	(5, 2, 'tes dis', 'Baru', 'tes', '0000-00-00 00:00:00', 2, 'tes dis', 'Reza Ahlim', 'Kombes'),
	(6, 2, 'tes dis 2', 'Baru', 'tes 2', '0000-00-00 00:00:00', 2, 'tes dis 2', 'Reza Ahlim', 'Kombes'),
	(7, 2, 'tes dis 2', 'Baru', 'tes 2', '0000-00-00 00:00:00', 2, 'tes dis 2', 'Reza Ahlim', 'Kombes'),
	(8, 2, 'tes dis 2', 'Baru', 'tes 2', '0000-00-00 00:00:00', 2, 'tes dis 2', 'Reza Ahlim', 'Kombes'),
	(9, 2, 'tes dis 2', 'Baru', 'tes 2', '0000-00-00 00:00:00', 2, 'tes dis 2', 'Reza Ahlim', 'Kombes'),
	(10, 2, 'tes 3', 'Baru', 'tes 3', '0000-00-00 00:00:00', 1, 'tes 3', 'Reza Ahlim', 'Kompol'),
	(11, 2, 'tes 3', 'Baru', 'tes 3', '2025-11-26 02:47:00', 1, 'tes 3', 'Reza Ahlim', 'Kompol');

-- Dumping structure for table ditresnarkoba.respon
CREATE TABLE IF NOT EXISTS `respon` (
  `id_respon` int(11) NOT NULL AUTO_INCREMENT,
  `id_lapmas` int(11) DEFAULT NULL,
  `respon` varchar(255) DEFAULT NULL,
  `a_respon` varchar(255) DEFAULT NULL,
  `tanggal_respon` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_respon`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.respon: ~8 rows (approximately)
INSERT INTO `respon` (`id_respon`, `id_lapmas`, `respon`, `a_respon`, `tanggal_respon`) VALUES
	(15, 9, 'ok', '3', '2025-11-23 13:24:40'),
	(21, 4, 'ok sep', '3', '2025-11-23 13:44:26'),
	(22, 9, 'ok meluncur', '2', '2025-11-23 15:44:51'),
	(23, 9, 'ok siap', '1', '2025-11-23 18:01:20'),
	(24, 9, 'ok, mantap', '3', '2025-11-23 18:20:54'),
	(25, 8, 'tes', '3', '2025-11-24 01:33:26'),
	(26, 12, 'ok', '1', '2025-11-25 01:21:37'),
	(27, 12, 'ok, selesai', '3', '2025-11-25 01:22:02');

-- Dumping structure for table ditresnarkoba.temuan
CREATE TABLE IF NOT EXISTS `temuan` (
  `id_temuan` int(11) NOT NULL AUTO_INCREMENT,
  `jenis` varchar(255) NOT NULL,
  `jumlah` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_temuan`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.temuan: ~1 rows (approximately)
INSERT INTO `temuan` (`id_temuan`, `jenis`, `jumlah`) VALUES
	(2, 'ganja', '100 gram');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
