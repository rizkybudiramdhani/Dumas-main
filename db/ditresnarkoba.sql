-- --------------------------------------------------------
-- Database: ditresnarkoba
-- --------------------------------------------------------

-- Tabel akun
CREATE TABLE IF NOT EXISTS `akun` (
  `Id_akun` int(11) NOT NULL AUTO_INCREMENT,
  `Nama` varchar(100) NOT NULL,
  `Nomor_hp` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Role` enum('ditresnarkoba','ditsamapta','ditbinmas','masyarakat') NOT NULL DEFAULT 'masyarakat',
  `Password` varchar(255) NOT NULL,
  PRIMARY KEY (`Id_akun`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin default (password: admin123)
INSERT INTO `akun` (`Nama`, `Nomor_hp`, `Email`, `Role`, `Password`) VALUES
('Admin Ditresnarkoba', '081234567890', 'ditresnarkoba@poldasumut.id', 'ditresnarkoba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Admin Ditsamapta', '081234567891', 'ditsamapta@poldasumut.id', 'ditsamapta', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Admin Ditbinmas', '081234567892', 'ditbinmas@poldasumut.id', 'ditbinmas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Tabel lapmas (Laporan Masyarakat)
CREATE TABLE IF NOT EXISTS `lapmas` (
  `id_lapmas` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `desk` text NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `upload` text DEFAULT NULL,
  `tanggal_lapor` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_lapmas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
