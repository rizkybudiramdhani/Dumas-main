-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 12:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Ditresnarkoba`
--

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `Id_akun` int(11) NOT NULL,
  `Nomor_hp` varchar(255) DEFAULT NULL,
  `Nama` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Role` enum('Masyarakat','Ditresnarkoba','Ditsamapta','Ditbinmas') DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`Id_akun`, `Nomor_hp`, `Nama`, `Email`, `Role`, `Password`) VALUES
(1, '081234567890', 'Tomy Adrian', 'Tomiadrian@gmail.com', 'Ditbinmas', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq'),
(2, '089876543210', 'Reza Ahlim', 'Rezaahlim@gmail.com', 'Ditsamapta', '$2a$12$svOZWP9iF619eohz1zXabuRXwL2G3rdP/2Ck57o4zza'),
(3, '09524376897', 'Putra Siahan', 'Putrasiahan@gmail.com', 'Ditresnarkoba', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq'),
(4, '085837633968', 'Rizky Budi Ramdhani', 'rizkymedan04@gmail.com', 'Masyarakat', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq'),
(5, '085183223968', 'iwan', 'iwan@gmail.com', 'Masyarakat', '$2y$10$xgOFTl843ftFX7yno0GXdOKwCznSIAmdiuFHuGW/K0nSrNhknz4Dq');

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id_berita` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `desk` varchar(255) DEFAULT NULL,
  `tanggal` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lapmas`
--

CREATE TABLE `lapmas` (
  `id_lapmas` int(11) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `desk` varchar(255) DEFAULT NULL,
  `lokasi` varchar(2255) DEFAULT NULL,
  `upload` varchar(255) DEFAULT NULL,
  `tanggal_lapor` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lapmas`
--

INSERT INTO `lapmas` (`id_lapmas`, `judul`, `desk`, `lokasi`, `upload`, `tanggal_lapor`) VALUES
(1, 'tes', 'tes', NULL, '', '2025-11-21 15:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `temuan`
--

CREATE TABLE `temuan` (
  `id_temuan` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `jumlah` decimal(20,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`Id_akun`);

--
-- Indexes for table `lapmas`
--
ALTER TABLE `lapmas`
  ADD PRIMARY KEY (`id_lapmas`);

--
-- Indexes for table `temuan`
--
ALTER TABLE `temuan`
  ADD PRIMARY KEY (`id_temuan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `akun`
--
ALTER TABLE `akun`
  MODIFY `Id_akun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `lapmas`
--
ALTER TABLE `lapmas`
  MODIFY `id_lapmas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `temuan`
--
ALTER TABLE `temuan`
  MODIFY `id_temuan` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
