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

-- Data exporting was unselected.

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

-- Data exporting was unselected.

-- Dumping structure for table ditresnarkoba.lapbin
CREATE TABLE IF NOT EXISTS `lapbin` (
  `id_lapbin` int(11) NOT NULL AUTO_INCREMENT,
  `Id_akun` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `kegiatan` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NULL DEFAULT NULL,
  `personil` int(50) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `petugas` varchar(255) DEFAULT NULL,
  `pangkat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_lapbin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table ditresnarkoba.lapmas
CREATE TABLE IF NOT EXISTS `lapmas` (
  `id_lapmas` int(11) NOT NULL AUTO_INCREMENT,
  `Id_akun` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `desk` varchar(255) DEFAULT NULL,
  `lokasi` varchar(2255) DEFAULT NULL,
  `upload` varchar(255) DEFAULT NULL,
  `tanggal_lapor` varchar(255) DEFAULT NULL,
  `status` enum('Baru','Diproses Ditresnarkoba','Diproses Ditsamapta','Diproses Ditbinmas','Selesai','Selesai Ditresnarkoba','Selesai Ditsamapta','Selesai Ditbinmas','Ditolak') NOT NULL DEFAULT 'Baru',
  PRIMARY KEY (`id_lapmas`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table ditresnarkoba.lapsam
CREATE TABLE IF NOT EXISTS `lapsam` (
  `id_lapsam` int(11) NOT NULL AUTO_INCREMENT,
  `Id_akun` int(50) NOT NULL DEFAULT 0,
  `judul` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `kegiatan` varchar(255) DEFAULT NULL,
  `tanggal` int(11) DEFAULT NULL,
  `personil` int(50) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `petugas` varchar(255) DEFAULT NULL,
  `pangkat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_lapsam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table ditresnarkoba.respon
CREATE TABLE IF NOT EXISTS `respon` (
  `id_respon` int(11) NOT NULL AUTO_INCREMENT,
  `id_lapmas` int(11) DEFAULT NULL,
  `respon` varchar(255) DEFAULT NULL,
  `a_respon` varchar(255) DEFAULT NULL,
  `tanggal_respon` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_respon`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table ditresnarkoba.temuan
CREATE TABLE IF NOT EXISTS `temuan` (
  `id_temuan` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) DEFAULT NULL,
  `jumlah` decimal(20,6) DEFAULT NULL,
  PRIMARY KEY (`id_temuan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
