-- phpMyAdmin SQL Dump
-- Database: db_pengolahan_plastik
-- Generated: 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Database: db_pengolahan_plastik
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS db_pengolahan_plastik DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_pengolahan_plastik;

-- --------------------------------------------------------
-- Table structure for table users
-- --------------------------------------------------------
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('penyortir', 'sopir', 'operator_mesin', 'kepala_produksi') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, username, password, nama, role, created_at) VALUES
(1, 'penyortir1', 'penyortir123', 'Budi Santoso', 'penyortir', '2025-02-25 10:00:00'),
(2, 'penyortir2', 'penyortir123', 'Ahmad Hidayat', 'penyortir', '2025-02-25 10:00:00'),
(3, 'sopir1', 'sopir123', 'Ahmad Supriyadi', 'sopir', '2025-02-25 10:00:00'),
(4, 'sopir2', 'sopir123', 'Joko Susilo', 'sopir', '2025-02-25 10:00:00'),
(5, 'operator1', 'operator123', 'Rudi Hermawan', 'operator_mesin', '2025-02-25 10:00:00'),
(6, 'operator2', 'operator123', 'Deni Setiawan', 'operator_mesin', '2025-02-25 10:00:00'),
(7, 'kepala1', 'kepala123', 'Siti Nurhaliza', 'kepala_produksi', '2025-02-25 10:00:00'),
(8, 'kepala2', 'kepala123', 'Bambang Wijaya', 'kepala_produksi', '2025-02-25 10:00:00');

-- --------------------------------------------------------
-- Table structure for table hasil_sortir
-- --------------------------------------------------------
CREATE TABLE hasil_sortir (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    jenis_plastik ENUM('PET', 'HDPE', 'PVC', 'LDPE', 'PP', 'PS') NOT NULL,
    kualitas ENUM('A', 'B', 'C', 'D') NOT NULL,
    jumlah_layak_olah DECIMAL(10,2) NOT NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT hasil_sortir_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hasil_sortir (id, user_id, jenis_plastik, kualitas, jumlah_layak_olah, tanggal, created_at) VALUES
(1, 1, 'PET', 'A', 500.50, '2025-02-20', '2025-02-25 10:00:00'),
(2, 1, 'HDPE', 'B', 300.25, '2025-02-20', '2025-02-25 10:00:00'),
(3, 2, 'PP', 'A', 450.00, '2025-02-21', '2025-02-25 10:00:00'),
(4, 1, 'PVC', 'C', 200.75, '2025-02-22', '2025-02-25 10:00:00'),
(5, 2, 'LDPE', 'B', 350.00, '2025-02-23', '2025-02-25 10:00:00'),
(6, 1, 'PET', 'A', 600.00, '2025-02-24', '2025-02-25 10:00:00'),
(7, 2, 'HDPE', 'A', 425.50, '2025-02-24', '2025-02-25 10:00:00'),
(8, 1, 'PS', 'D', 150.25, '2025-02-25', '2025-02-25 10:00:00');

-- --------------------------------------------------------
-- Table structure for table data_pengiriman
-- --------------------------------------------------------
CREATE TABLE data_pengiriman (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    tanggal_pengiriman DATE NOT NULL,
    tujuan VARCHAR(255) NOT NULL,
    perusahaan VARCHAR(255) NOT NULL,
    jumlah_muatan DECIMAL(10,2) NOT NULL,
    jenis_plastik ENUM('PET', 'HDPE', 'PVC', 'LDPE', 'PP', 'PS') NOT NULL,
    status ENUM('Menunggu', 'Dalam Perjalanan', 'Selesai') DEFAULT 'Menunggu',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT data_pengiriman_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO data_pengiriman (id, user_id, tanggal_pengiriman, tujuan, perusahaan, jumlah_muatan, jenis_plastik, status, created_at) VALUES
(1, 3, '2025-02-20', 'Jakarta', 'PT Maju Jaya', 500.00, 'PET', 'Selesai', '2025-02-25 10:00:00'),
(2, 3, '2025-02-21', 'Surabaya', 'CV Plastik Indah', 300.00, 'HDPE', 'Selesai', '2025-02-25 10:00:00'),
(3, 4, '2025-02-22', 'Bandung', 'PT Bersinar', 450.00, 'PP', 'Dalam Perjalanan', '2025-02-25 10:00:00'),
(4, 3, '2025-02-23', 'Semarang', 'UD Makmur', 200.00, 'PVC', 'Menunggu', '2025-02-25 10:00:00'),
(5, 4, '2025-02-24', 'Yogyakarta', 'CV Karya Muda', 350.00, 'LDPE', 'Dalam Perjalanan', '2025-02-25 10:00:00'),
(6, 3, '2025-02-25', 'Jakarta', 'PT Maju Jaya', 600.00, 'PET', 'Menunggu', '2025-02-25 10:00:00');

-- --------------------------------------------------------
-- Table structure for table hasil_produksi
-- --------------------------------------------------------
CREATE TABLE hasil_produksi (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    jenis_plastik ENUM('PET', 'HDPE', 'PVC', 'LDPE', 'PP', 'PS') NOT NULL,
    nama_mesin VARCHAR(100) NOT NULL,
    operator VARCHAR(100) NOT NULL,
    tanggal_produksi DATE NOT NULL,
    jumlah_hasil DECIMAL(10,2) NOT NULL,
    kondisi_mesin ENUM('Baik', 'Perlu Maintenance', 'Rusak') DEFAULT 'Baik',
    catatan TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT hasil_produksi_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hasil_produksi (id, user_id, jenis_plastik, nama_mesin, operator, tanggal_produksi, jumlah_hasil, kondisi_mesin, catatan, created_at) VALUES
(1, 5, 'PET', 'Mesin Extruder A1', 'Rudi', '2025-02-20', 450.00, 'Baik', 'Produksi lancar', '2025-02-25 10:00:00'),
(2, 5, 'HDPE', 'Mesin Extruder A2', 'Rudi', '2025-02-21', 280.00, 'Perlu Maintenance', 'Mesin agak berisik', '2025-02-25 10:00:00'),
(3, 6, 'PP', 'Mesin Extruder B1', 'Deni', '2025-02-22', 400.00, 'Baik', 'Normal', '2025-02-25 10:00:00'),
(4, 5, 'PVC', 'Mesin Extruder A1', 'Rudi', '2025-02-23', 180.00, 'Rusak', 'Mesin mati total', '2025-02-25 10:00:00'),
(5, 6, 'LDPE', 'Mesin Extruder B2', 'Deni', '2025-02-24', 320.00, 'Baik', 'Produksi baik', '2025-02-25 10:00:00'),
(6, 5, 'PET', 'Mesin Extruder A2', 'Rudi', '2025-02-25', 550.00, 'Baik', 'Setelah diperbaiki', '2025-02-25 10:00:00');

-- --------------------------------------------------------
-- Table structure for table data_pelanggan
-- --------------------------------------------------------
CREATE TABLE data_pelanggan (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama_perusahaan VARCHAR(255) NOT NULL,
    nama_kontak VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO data_pelanggan (id, nama_perusahaan, nama_kontak, email, telepon, alamat, created_at) VALUES
(1, 'PT Maju Jaya', 'Budi Santoso', 'budi@majujaya.com', '021-5550123', 'Jl. Industri Raya No. 45, Jakarta', '2025-02-25 10:00:00'),
(2, 'CV Plastik Indah', 'Siti Aminah', 'siti@plastikindah.com', '031-5550456', 'Jl. Raya Surabaya No. 78, Surabaya', '2025-02-25 10:00:00'),
(3, 'PT Bersinar', 'Agus Hermawan', 'agus@bersinar.com', '022-5550789', 'Jl. Cihampelas No. 123, Bandung', '2025-02-25 10:00:00'),
(4, 'UD Makmur', 'Dewi Lestari', 'dewi@makmur.com', '024-5550321', 'Jl. Pandanaran No. 56, Semarang', '2025-02-25 10:00:00'),
(5, 'CV Karya Muda', 'Hendra Wijaya', 'hendra@karyamuda.com', '0274-5550987', 'Jl. Malioboro No. 34, Yogyakarta', '2025-02-25 10:00:00');

-- --------------------------------------------------------
-- Table structure for table laporan_produksi
-- --------------------------------------------------------
CREATE TABLE laporan_produksi (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    jenis_laporan ENUM('Lengkap', 'Sortir', 'Pengiriman', 'Produksi') NOT NULL,
    periode_awal DATE NOT NULL,
    periode_akhir DATE NOT NULL,
    file_laporan VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT laporan_produksi_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO laporan_produksi (id, user_id, jenis_laporan, periode_awal, periode_akhir, created_at) VALUES
(1, 7, 'Lengkap', '2025-02-01', '2025-02-29', '2025-02-25 10:00:00'),
(2, 8, 'Sortir', '2025-02-01', '2025-02-29', '2025-02-25 10:00:00'),
(3, 7, 'Produksi', '2025-02-01', '2025-02-29', '2025-02-25 10:00:00');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
