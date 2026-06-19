CREATE DATABASE IF NOT EXISTS reservasi_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reservasi_lab;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reservasi;
DROP TABLE IF EXISTS slot_waktu;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS matakuliah;
DROP TABLE IF EXISTS ruangan;
DROP TABLE IF EXISTS dosen;
DROP TABLE IF EXISTS admin;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nidn VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE matakuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    sks INT DEFAULT 2,
    jurusan VARCHAR(50) DEFAULT 'Informatika',
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(20) NOT NULL,
    jurusan VARCHAR(50) DEFAULT 'Informatika',
    semester INT DEFAULT 1,
    tahun_ajaran VARCHAR(20),
    kapasitas INT DEFAULT 30,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kapasitas INT DEFAULT 30,
    lokasi VARCHAR(150),
    fasilitas TEXT,
    status ENUM('aktif','nonaktif','maintenance') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE slot_waktu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesi INT NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    label VARCHAR(30) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE reservasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_reservasi VARCHAR(25) NOT NULL UNIQUE,
    dosen_id INT NOT NULL,
    ruangan_id INT NOT NULL,
    matakuliah_id INT NOT NULL,
    kelas_id INT NOT NULL,
    slot_waktu_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jurusan VARCHAR(50) DEFAULT 'Informatika',
    keterangan TEXT,
    status ENUM('pending','disetujui','ditolak','dibatalkan') DEFAULT 'pending',
    catatan_admin VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id),
    FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id),
    FOREIGN KEY (kelas_id) REFERENCES kelas(id),
    FOREIGN KEY (slot_waktu_id) REFERENCES slot_waktu(id),
    UNIQUE KEY unique_booking (ruangan_id, tanggal, slot_waktu_id)
) ENGINE=InnoDB;

INSERT INTO matakuliah (kode,nama,sks,jurusan) VALUES
('IF101','Analisis Algoritma',3,'Informatika'),
('IF102','Struktur Data',3,'Informatika'),
('IF201','Pemrograman Web',3,'Informatika'),
('IF202','Basis Data',3,'Informatika'),
('IF203','Jaringan Komputer',3,'Informatika'),
('IF301','Pemrograman Berorientasi Objek',3,'Informatika'),
('IF302','Kecerdasan Buatan',3,'Informatika'),
('IF303','Rekayasa Perangkat Lunak',3,'Informatika'),
('IF304','Pengolahan Citra Digital',3,'Informatika'),
('IF401','Machine Learning',3,'Informatika'),
('IF402','Sistem Operasi',3,'Informatika'),
('IF403','Keamanan Informasi',3,'Informatika');

INSERT INTO kelas (nama,jurusan,semester,tahun_ajaran,kapasitas) VALUES
('INF-A-23','Informatika',6,'2023/2024',32),
('INF-B-23','Informatika',6,'2023/2024',30),
('INF-C-23','Informatika',6,'2023/2024',27),
('INF-A-24','Informatika',4,'2024/2025',30),
('INF-B-24','Informatika',4,'2024/2025',28),
('INF-C-24','Informatika',4,'2024/2025',28),
('INF-D-24','Informatika',4,'2024/2025',26),
('INF-A-25','Informatika',2,'2025/2026',30),
('INF-B-25','Informatika',2,'2025/2026',28),
('INF-C-25','Informatika',2,'2025/2026',25);

INSERT INTO ruangan (kode,nama,kapasitas,lokasi,fasilitas,status) VALUES
('LAB-303','Lab. Komputer - 303',30,'Gedung Laboratorium Lantai 3','PC 30 unit, Proyektor, AC, WiFi','aktif'),
('LAB-304','Lab. Komputer - 304',32,'Gedung Laboratorium Lantai 3','PC 32 unit, Proyektor, AC, WiFi','aktif'),
('LAB-305','Lab. Komputer - 305',28,'Gedung Laboratorium Lantai 3','PC 28 unit, Proyektor, AC, WiFi','aktif'),
('LAB-FT','Lab. Komputer FT',30,'Gedung Fakultas Teknik','PC 30 unit, Proyektor, AC, WiFi, Printer','aktif');

INSERT INTO slot_waktu (sesi,jam_mulai,jam_selesai,label) VALUES
(1,'07:30:00','09:10:00','07.30 - 09.10'),
(2,'09:25:00','11:05:00','09.25 - 11.05'),
(3,'11:20:00','13:00:00','11.20 - 13.00'),
(4,'13:15:00','14:55:00','13.15 - 14.55'),
(5,'15:10:00','16:50:00','15.10 - 16.50'),
(6,'17:05:00','18:45:00','17.05 - 18.45');

