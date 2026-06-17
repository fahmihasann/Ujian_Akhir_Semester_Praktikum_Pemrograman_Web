-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 17, 2026 at 12:47 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_inventaris`
--
CREATE DATABASE IF NOT EXISTS `db_inventaris` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_inventaris`;

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `fn_nilai_kategori`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_nilai_kategori` (`p_id_kategori` INT) RETURNS DECIMAL(20,2) DETERMINISTIC BEGIN
    DECLARE total DECIMAL(20,2);
    SELECT SUM(stok * harga_barang) INTO total
    FROM Barang
    WHERE id_kategori = p_id_kategori;
    RETURN IFNULL(total, 0);
END$$

DROP FUNCTION IF EXISTS `fn_status_stok`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_status_stok` (`p_stok` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE status VARCHAR(20);
    IF p_stok = 0 THEN
        SET status = 'Habis';
    ELSEIF p_stok <= 5 THEN
        SET status = 'Kritis';
    ELSEIF p_stok <= 20 THEN
        SET status = 'Normal';
    ELSE
        SET status = 'Aman';
    END IF;
    RETURN status;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `kode_barang` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `harga_barang` decimal(15,2) NOT NULL,
  `id_kategori` int NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `nama_barang`, `stok`, `harga_barang`, `id_kategori`, `gambar`) VALUES
(1, 'BRG-001', 'Laptop Lenovo', 10, 8500000.00, 1, NULL),
(2, 'BRG-002', 'Printer Canon', 5, 1200000.00, 1, NULL),
(3, 'BRG-003', 'Meja Kerja', 8, 750000.00, 2, NULL),
(4, 'BRG-004', 'Kursi Kantor', 15, 450000.00, 2, NULL),
(5, 'BRG-005', 'Pulpen Box', 50, 25000.00, 3, NULL),
(6, 'BRG-006', 'Switch 24 Port', 3, 2300000.00, 4, NULL),
(7, 'BRG-007', 'Kertas A4 (Rim)', 30, 55000.00, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

DROP TABLE IF EXISTS `barang_keluar`;
CREATE TABLE `barang_keluar` (
  `id_keluar` int NOT NULL,
  `tanggal_keluar` date NOT NULL,
  `jumlah` int NOT NULL,
  `tujuan_distribusi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_barang` int NOT NULL,
  `id_user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang_keluar`
--

INSERT INTO `barang_keluar` (`id_keluar`, `tanggal_keluar`, `jumlah`, `tujuan_distribusi`, `id_barang`, `id_user`) VALUES
(1, '2025-02-20', 2, 'Ruang Kepala', 1, 2),
(2, '2025-03-01', 1, 'Bagian Keuangan', 2, 2),
(3, '2025-03-10', 5, 'Ruang Rapat', 5, 3),
(4, '2025-04-01', 1, 'Lab Komputer', 6, 1),
(5, '2025-04-10', 10, 'Distribusi ATK', 7, 3);

--
-- Triggers `barang_keluar`
--
DROP TRIGGER IF EXISTS `trg_after_barang_keluar`;
DELIMITER $$
CREATE TRIGGER `trg_after_barang_keluar` AFTER INSERT ON `barang_keluar` FOR EACH ROW BEGIN
    UPDATE Barang
    SET stok = stok - NEW.jumlah
    WHERE id_barang = NEW.id_barang;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_before_barang_keluar`;
DELIMITER $$
CREATE TRIGGER `trg_before_barang_keluar` BEFORE INSERT ON `barang_keluar` FOR EACH ROW BEGIN
    DECLARE stok_tersedia INT;
    SELECT stok INTO stok_tersedia
    FROM Barang
    WHERE id_barang = NEW.id_barang;

    IF NEW.jumlah > stok_tersedia THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Jumlah keluar melebihi stok yang tersedia';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk`
--

DROP TABLE IF EXISTS `barang_masuk`;
CREATE TABLE `barang_masuk` (
  `id_masuk` int NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `jumlah` int NOT NULL,
  `supplier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_barang` int NOT NULL,
  `id_user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang_masuk`
--

INSERT INTO `barang_masuk` (`id_masuk`, `tanggal_masuk`, `jumlah`, `supplier`, `id_barang`, `id_user`) VALUES
(1, '2025-01-10', 10, 'PT Lenovo Indonesia', 1, 1),
(2, '2025-01-12', 5, 'CV Printer Jaya', 2, 1),
(3, '2025-02-01', 8, 'UD Furnitur Makmur', 3, 2),
(4, '2025-02-05', 15, 'UD Furnitur Makmur', 4, 2),
(5, '2025-02-10', 50, 'Toko ATK Murah', 5, 3),
(6, '2025-03-01', 3, 'PT Network Solution', 6, 1),
(7, '2025-03-15', 30, 'Toko ATK Murah', 7, 3);

--
-- Triggers `barang_masuk`
--
DROP TRIGGER IF EXISTS `trg_after_barang_masuk`;
DELIMITER $$
CREATE TRIGGER `trg_after_barang_masuk` AFTER INSERT ON `barang_masuk` FOR EACH ROW BEGIN
    UPDATE Barang
    SET stok = stok + NEW.jumlah
    WHERE id_barang = NEW.id_barang;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Elektronik', 'Perangkat elektronik dan komputer'),
(2, 'Furnitur', 'Peralatan dan furniture kantor'),
(3, 'ATK', 'Alat tulis dan keperluan kantor'),
(4, 'Jaringan', 'Perangkat jaringan dan kabel'),
(5, 'Kebersihan', 'Perlengkapan kebersihan kantor');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama_lengkap`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '$2y$10$e1zr7oltOrIgTmtjQnXQs.rUFod9WaeKIT7nvUuYpe2TXIPao5.ke', 'admin'),
(2, 'Budi Santoso', 'budi', '$2y$10$44pS7lPLQVgReLD3FPrtZOClEeq7V1Q4kyH4Ir3m/mcVPTLS21ZpC', 'staff'),
(3, 'Citra Dewi', 'citra', '$2y$10$Ltwye28ujlRd4MZMbaQrz.YjT9rVHDTWODaXMguttpKXNpzwAIMl.', 'staff'),
(4, 'Dewi Lestari', 'dewi', 'dewi123', 'staff'),
(5, 'Eko Prasetyo', 'eko', 'eko123', 'staff');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_riwayat_transaksi`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_riwayat_transaksi`;
CREATE TABLE `v_riwayat_transaksi` (
`jenis` varchar(6)
,`jumlah` int
,`keterangan` varchar(100)
,`nama_barang` varchar(100)
,`petugas` varchar(100)
,`tanggal` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stok_barang`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_stok_barang`;
CREATE TABLE `v_stok_barang` (
`harga_barang` decimal(15,2)
,`id_barang` int
,`kode_barang` varchar(50)
,`nama_barang` varchar(100)
,`nama_kategori` varchar(100)
,`nilai_inventaris` decimal(25,2)
,`status_stok` varchar(20)
,`stok` int
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id_keluar`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`id_masuk`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_keluar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id_masuk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `v_riwayat_transaksi`
--
DROP TABLE IF EXISTS `v_riwayat_transaksi`;

DROP VIEW IF EXISTS `v_riwayat_transaksi`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_riwayat_transaksi`  AS SELECT 'MASUK' AS `jenis`, `bm`.`tanggal_masuk` AS `tanggal`, `b`.`nama_barang` AS `nama_barang`, `bm`.`jumlah` AS `jumlah`, `bm`.`supplier` AS `keterangan`, `u`.`nama_lengkap` AS `petugas` FROM ((`barang_masuk` `bm` join `barang` `b` on((`bm`.`id_barang` = `b`.`id_barang`))) join `user` `u` on((`bm`.`id_user` = `u`.`id_user`)))union all select 'KELUAR' AS `KELUAR`,`bk`.`tanggal_keluar` AS `tanggal_keluar`,`b`.`nama_barang` AS `nama_barang`,`bk`.`jumlah` AS `jumlah`,`bk`.`tujuan_distribusi` AS `tujuan_distribusi`,`u`.`nama_lengkap` AS `nama_lengkap` from ((`barang_keluar` `bk` join `barang` `b` on((`bk`.`id_barang` = `b`.`id_barang`))) join `user` `u` on((`bk`.`id_user` = `u`.`id_user`))) order by `tanggal` desc  ;

-- --------------------------------------------------------

--
-- Structure for view `v_stok_barang`
--
DROP TABLE IF EXISTS `v_stok_barang`;

DROP VIEW IF EXISTS `v_stok_barang`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stok_barang`  AS SELECT `b`.`id_barang` AS `id_barang`, `b`.`kode_barang` AS `kode_barang`, `b`.`nama_barang` AS `nama_barang`, `k`.`nama_kategori` AS `nama_kategori`, `b`.`stok` AS `stok`, `b`.`harga_barang` AS `harga_barang`, (`b`.`stok` * `b`.`harga_barang`) AS `nilai_inventaris`, `fn_status_stok`(`b`.`stok`) AS `status_stok` FROM (`barang` `b` join `kategori` `k` on((`b`.`id_kategori` = `k`.`id_kategori`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `barang_keluar_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `barang_keluar_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD CONSTRAINT `barang_masuk_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `barang_masuk_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
