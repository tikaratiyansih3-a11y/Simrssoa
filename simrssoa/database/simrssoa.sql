-- ========================================================
-- DATABASE SCHEMA: simrssoa (Server B)
-- ========================================================
CREATE DATABASE IF NOT EXISTS `simrssoa` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `simrssoa`;

-- 1. Tabel Master: pengguna (Admin, Perekam Medis, Staff)
CREATE TABLE IF NOT EXISTS `pengguna` (
    `id_pengguna` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `peran` ENUM('admin', 'pendaftaran', 'dokter', 'perawat', 'kasir') NOT NULL DEFAULT 'pendaftaran',
    `status_aktif` ENUM('Y', 'N') NOT NULL DEFAULT 'Y',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel Master: poliklinik
CREATE TABLE IF NOT EXISTS `poliklinik` (
    `id_poliklinik` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_poli` VARCHAR(10) NOT NULL UNIQUE,
    `nama_poli` VARCHAR(100) NOT NULL,
    `gedung` VARCHAR(50) NULL,
    `status_aktif` ENUM('Y', 'N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB;

-- 3. Tabel Master: dokter
CREATE TABLE IF NOT EXISTS `dokter` (
    `id_dokter` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_dokter` VARCHAR(10) NOT NULL UNIQUE,
    `nama_dokter` VARCHAR(120) NOT NULL,
    `spesialisasi` VARCHAR(100) NOT NULL,
    `nomor_sip` VARCHAR(50) NOT NULL,
    `no_telepon` VARCHAR(20) NULL,
    `status_aktif` ENUM('Y', 'N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB;

-- 4. Tabel Master: asuransi (Penjamin Layanan)
CREATE TABLE IF NOT EXISTS `asuransi` (
    `id_asuransi` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_asuransi` VARCHAR(15) NOT NULL UNIQUE,
    `nama_asuransi` VARCHAR(100) NOT NULL,
    `tipe_penjamin` ENUM('BPJS', 'SWASTA', 'UMUM') NOT NULL DEFAULT 'UMUM',
    `status_aktif` ENUM('Y', 'N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB;

-- 5. Tabel Master: pasien
CREATE TABLE IF NOT EXISTS `pasien` (
    `id_pasien` INT AUTO_INCREMENT PRIMARY KEY,
    `no_rkm_medis` VARCHAR(15) NOT NULL UNIQUE,
    `nik` CHAR(16) NOT NULL UNIQUE,
    `nama_pasien` VARCHAR(120) NOT NULL,
    `jenis_kelamin` ENUM('L', 'P') NOT NULL,
    `tanggal_lahir` DATE NOT NULL,
    `alamat` TEXT NOT NULL,
    `no_telepon` VARCHAR(20) NULL,
    `id_asuransi_utama` INT NULL,
    `no_kartu_asuransi` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pasien_asuransi_utama` 
        FOREIGN KEY (`id_asuransi_utama`) REFERENCES `asuransi` (`id_asuransi`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Tabel Asosiatif 1: dokter_poliklinik (Jadwal Praktek Dokter di Poli)
CREATE TABLE IF NOT EXISTS `dokter_poliklinik` (
    `id_dokter_poli` INT AUTO_INCREMENT PRIMARY KEY,
    `id_dokter` INT NOT NULL,
    `id_poliklinik` INT NOT NULL,
    `hari_praktik` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    `jam_mulai` TIME NOT NULL,
    `jam_selesai` TIME NOT NULL,
    `kuota_pasien` INT DEFAULT 30,
    CONSTRAINT `fk_dp_dokter` 
        FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_dp_poli` 
        FOREIGN KEY (`id_poliklinik`) REFERENCES `poliklinik` (`id_poliklinik`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `unique_jadwal` (`id_dokter`, `id_poliklinik`, `hari_praktik`, `jam_mulai`)
) ENGINE=InnoDB;

-- 7. Tabel Asosiatif Transaksional: pendaftaran
CREATE TABLE IF NOT EXISTS `pendaftaran` (
    `id_pendaftaran` INT AUTO_INCREMENT PRIMARY KEY,
    `no_registrasi` VARCHAR(20) NOT NULL UNIQUE,
    `tanggal_kunjungan` DATE NOT NULL,
    `id_pasien` INT NOT NULL,
    `id_poliklinik` INT NOT NULL,
    `id_dokter` INT NOT NULL,
    `id_asuransi` INT NOT NULL,
    `id_petugas` INT NOT NULL,
    `nomor_antrean` INT NOT NULL,
    `status_pelayanan` ENUM('Menunggu', 'Pemeriksaan', 'Selesai', 'Batal') NOT NULL DEFAULT 'Menunggu',
    `keluhan_awal` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reg_pasien` 
        FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_poli` 
        FOREIGN KEY (`id_poliklinik`) REFERENCES `poliklinik` (`id_poliklinik`) 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_dokter` 
        FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_asuransi` 
        FOREIGN KEY (`id_asuransi`) REFERENCES `asuransi` (`id_asuransi`) 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_pengguna` 
        FOREIGN KEY (`id_petugas`) REFERENCES `pengguna` (`id_pengguna`) 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ========================================================
-- DATA AWAL / SEEDER
-- ========================================================
-- Password default: 'password123'
INSERT INTO `pengguna` (`id_pengguna`, `username`, `password_hash`, `nama_lengkap`, `peran`) VALUES
(1, 'admin', '$2y$10$tZc4sSg1c2s21mZ1qX7P2.eY.NeqyZfUeXj7dY7qf09zMqmzG99q6', 'Administrator SIMRS', 'admin'),
(2, 'petugas1', '$2y$10$tZc4sSg1c2s21mZ1qX7P2.eY.NeqyZfUeXj7dY7qf09zMqmzG99q6', 'Siti Rahmawati, A.Md.RMIK', 'pendaftaran'),
(3, 'dokter1', '$2y$10$tZc4sSg1c2s21mZ1qX7P2.eY.NeqyZfUeXj7dY7qf09zMqmzG99q6', 'dr. Budi Santoso, Sp.PD', 'dokter')
ON DUPLICATE KEY UPDATE `id_pengguna`=`id_pengguna`;

INSERT INTO `poliklinik` (`id_poliklinik`, `kode_poli`, `nama_poli`, `gedung`) VALUES
(1, 'POL-INT', 'Poliklinik Penyakit Dalam', 'Gedung A Lantai 1'),
(2, 'POL-GIG', 'Poliklinik Gigi & Mulut', 'Gedung A Lantai 2'),
(3, 'POL-ANA', 'Poliklinik Anak', 'Gedung B Lantai 1'),
(4, 'POL-UMU', 'Poliklinik Umum', 'Gedung A Lantai 1'),
(5, 'POL-MAT', 'Poliklinik Mata', 'Gedung B Lantai 2')
ON DUPLICATE KEY UPDATE `id_poliklinik`=`id_poliklinik`;

INSERT INTO `dokter` (`id_dokter`, `kode_dokter`, `nama_dokter`, `spesialisasi`, `nomor_sip`, `no_telepon`) VALUES
(1, 'DR-001', 'dr. Budi Santoso, Sp.PD', 'Spesialis Penyakit Dalam', 'SIP.449/120/DISKES/2023', '081234567801'),
(2, 'DR-002', 'drg. Maya Indah, Sp.KG', 'Dokter Gigi Spesialis Konservasi Gigi', 'SIP.449/121/DISKES/2022', '081234567802'),
(3, 'DR-003', 'dr. Hendra Pratama, Sp.A', 'Spesialis Anak', 'SIP.449/122/DISKES/2021', '081234567803'),
(4, 'DR-004', 'dr. Rina Suryani', 'Dokter Umum', 'SIP.449/123/DISKES/2024', '081234567804')
ON DUPLICATE KEY UPDATE `id_dokter`=`id_dokter`;

INSERT INTO `asuransi` (`id_asuransi`, `kode_asuransi`, `nama_asuransi`, `tipe_penjamin`) VALUES
(1, 'ASR-UMUM', 'Pasien Umum (Bayar Mandiri)', 'UMUM'),
(2, 'ASR-BPJS', 'BPJS Kesehatan', 'BPJS'),
(3, 'ASR-INHL', 'Mandiri Inhealth', 'SWASTA'),
(4, 'ASR-PRUD', 'Prudential Life Assurance', 'SWASTA')
ON DUPLICATE KEY UPDATE `id_asuransi`=`id_asuransi`;

INSERT INTO `pasien` (`id_pasien`, `no_rkm_medis`, `nik`, `nama_pasien`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `no_telepon`, `id_asuransi_utama`, `no_kartu_asuransi`) VALUES
(1, 'RM-000001', '3201012304890001', 'Ahmad Hidayat', 'L', '1989-04-23', 'Jl. Merdeka No. 12, Jakarta', '081198765432', 2, '0001234567890'),
(2, 'RM-000002', '3201015509930002', 'Dewi Lestari', 'P', '1993-09-15', 'Jl. Kenanga Blok C No. 5, Bogor', '081287654321', 1, NULL),
(3, 'RM-000003', '3201011112020003', 'Rian Alamsyah', 'L', '2002-12-11', 'Komp. Graha Indah No. 40, Depok', '081376543210', 3, 'INH-88776655')
ON DUPLICATE KEY UPDATE `id_pasien`=`id_pasien`;

INSERT INTO `dokter_poliklinik` (`id_dokter`, `id_poliklinik`, `hari_praktik`, `jam_mulai`, `jam_selesai`, `kuota_pasien`) VALUES
(1, 1, 'Senin', '08:00:00', '12:00:00', 30),
(1, 1, 'Rabu', '08:00:00', '12:00:00', 30),
(2, 2, 'Senin', '09:00:00', '13:00:00', 20),
(3, 3, 'Selasa', '08:30:00', '12:30:00', 25),
(4, 4, 'Senin', '07:30:00', '14:00:00', 50)
ON DUPLICATE KEY UPDATE `id_dokter_poli`=`id_dokter_poli`;

INSERT INTO `pendaftaran` (`no_registrasi`, `tanggal_kunjungan`, `id_pasien`, `id_poliklinik`, `id_dokter`, `id_asuransi`, `id_petugas`, `nomor_antrean`, `status_pelayanan`, `keluhan_awal`) VALUES
('REG-20261119-001', CURDATE(), 1, 1, 1, 2, 2, 1, 'Menunggu', 'Demam tinggi dan nyeri ulu hati 3 hari'),
('REG-20261119-002', CURDATE(), 2, 2, 2, 1, 2, 1, 'Menunggu', 'Sakit gigi geraham belakang berdenyut')
ON DUPLICATE KEY UPDATE `id_pendaftaran`=`id_pendaftaran`;
