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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.berita: ~0 rows (approximately)

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.feedback_kasus: ~0 rows (approximately)

-- Dumping structure for table ditresnarkoba.kasus
CREATE TABLE IF NOT EXISTS `kasus` (
  `id_kasus` int(11) NOT NULL AUTO_INCREMENT,
  `tersangka` int(50) DEFAULT NULL,
  `jumlah kasus` int(50) DEFAULT NULL,
  `kec` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_kasus`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.kasus: ~30 rows (approximately)
INSERT INTO `kasus` (`id_kasus`, `tersangka`, `jumlah kasus`, `kec`) VALUES
	(1, 3, 1, 'Medan Tembung'),
	(2, 9, 3, 'Medan Denai'),
	(3, 5, 3, 'Medan Marelan'),
	(4, 6, 4, 'Medan Helvetia'),
	(5, 2, 2, 'Medan Kota'),
	(6, 6, 2, 'Medan Baru'),
	(7, 7, 4, 'Medan Sunggal'),
	(8, 7, 3, 'Medan Belawan'),
	(9, 9, 5, 'Medan Labuhan'),
	(10, 10, 23, 'Medan Amplas'),
	(11, 5, 2, 'Medan Area'),
	(12, 2, 2, 'Medan Johor'),
	(13, 1, 1, 'Medan Polonia'),
	(14, 12, 24, 'Medan Selayang'),
	(15, 4, 2, 'Medan Tuntungan'),
	(16, 3, 1, 'Medan Maimun'),
	(17, 5, 2, 'Medan Petisah'),
	(18, 2, 1, 'Medan Perjuangan'),
	(19, 4, 3, 'Medan Timur'),
	(20, 1, 1, 'Medan Barat'),
	(21, 6, 2, 'Medan Deli');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapbin: ~0 rows (approximately)

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
  `status` enum('Baru','Diproses Ditresnarkoba','Diproses Ditsamapta','Diproses Ditbinmas','Diproses Multiple Unit','Selesai','Selesai Ditresnarkoba','Selesai Ditsamapta','Selesai Ditbinmas','Waiting','Ditolak') NOT NULL DEFAULT 'Baru',
  `assigned_to` varchar(50) DEFAULT 'Ditresnarkoba',
  PRIMARY KEY (`id_lapmas`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapmas: ~3 rows (approximately)
INSERT INTO `lapmas` (`id_lapmas`, `Id_akun`, `judul`, `desk`, `lokasi`, `upload`, `tanggal_lapor`, `status`, `assigned_to`) VALUES
	(2, 4, 'tess', 'tess', 'tess', '', '2025-12-11 21:02:32', 'Diproses Ditsamapta', 'Ditsamapta'),
	(3, 4, 'tes2', 'tes2', 'tes2', '', '2025-12-11 21:51:38', 'Diproses Multiple Unit', 'Ditsamapta,Ditbinmas'),
	(4, 4, 'tes 3', 'tes 3', 'tes 3', '', '2025-12-12 07:31:06', 'Diproses Ditbinmas', 'Ditsamapta,Ditbinmas');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.lapsam: ~0 rows (approximately)

-- Dumping structure for table ditresnarkoba.respon
CREATE TABLE IF NOT EXISTS `respon` (
  `id_respon` int(11) NOT NULL AUTO_INCREMENT,
  `id_lapmas` int(11) DEFAULT NULL,
  `respon` varchar(255) DEFAULT NULL,
  `a_respon` varchar(255) DEFAULT NULL,
  `tanggal_respon` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_respon`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.respon: ~3 rows (approximately)
INSERT INTO `respon` (`id_respon`, `id_lapmas`, `respon`, `a_respon`, `tanggal_respon`) VALUES
	(1, 2, 'lanjutkan ditsamapta', '3', '2025-12-11 14:13:33'),
	(2, 2, 'sedang diproses pak', '2', '2025-12-11 14:26:20'),
	(3, 3, 'lanjutkan', '3', '2025-12-11 15:18:25'),
	(4, 4, 'tes 3', '3', '2025-12-12 00:45:15'),
	(5, 4, 'baik pak', '1', '2025-12-12 01:18:10');

-- Dumping structure for table ditresnarkoba.temuan
CREATE TABLE IF NOT EXISTS `temuan` (
  `id_temuan` int(11) NOT NULL AUTO_INCREMENT,
  `jenis` varchar(255) NOT NULL,
  `jumlah` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_temuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ditresnarkoba.temuan: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
