-- Create database if not exists
CREATE DATABASE IF NOT EXISTS db_tiket_bus;
USE db_tiket_bus;

-- Admin table
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Unit/Bus table
CREATE TABLE unit (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_unit VARCHAR(20) NOT NULL UNIQUE,
    nama_unit VARCHAR(100) NOT NULL,
    jenis_unit ENUM('EKONOMI', 'BISNIS', 'EKSEKUTIF') NOT NULL,
    kapasitas INT NOT NULL,
    status ENUM('AKTIF', 'MAINTENANCE', 'NONAKTIF') DEFAULT 'AKTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Karyawan table
CREATE TABLE karyawan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama_karyawan VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('LAKI-LAKI', 'PEREMPUAN') NOT NULL,
    alamat TEXT NOT NULL,
    no_telp VARCHAR(15) NOT NULL,
    tempat_lahir VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jabatan VARCHAR(50) NOT NULL,
    gaji_pokok DECIMAL(10,2) NOT NULL DEFAULT 0,
    tunjangan DECIMAL(10,2) NOT NULL DEFAULT 0,
    potongan_default DECIMAL(10,2) NOT NULL DEFAULT 0,
    tanggal_masuk DATE NOT NULL,
    status ENUM('AKTIF', 'NONAKTIF') DEFAULT 'AKTIF',
    foto_karyawan VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sopir table
CREATE TABLE sopir (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(15),
    nomor_sim VARCHAR(20) NOT NULL UNIQUE,
    jenis_sim VARCHAR(10) NOT NULL,
    gaji_pokok DECIMAL(10,2) NOT NULL,
    status ENUM('AKTIF', 'NONAKTIF') DEFAULT 'AKTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Route table
CREATE TABLE route (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kota_asal VARCHAR(100) NOT NULL,
    kota_tujuan VARCHAR(100) NOT NULL,
    jarak_km DECIMAL(10,2) NOT NULL,
    waktu_tempuh TIME NOT NULL,
    harga_tiket DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_route` (`kota_asal`, `kota_tujuan`)
);

-- Jadwal table
CREATE TABLE jadwal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    unit_id INT NOT NULL,
    route_id INT NOT NULL,
    sopir_id INT NOT NULL,
    waktu_berangkat DATETIME NOT NULL,
    waktu_tiba DATETIME NOT NULL,
    status ENUM('AKTIF', 'MAINTENANCE', 'NONAKTIF') DEFAULT 'AKTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES unit(id),
    FOREIGN KEY (route_id) REFERENCES route(id),
    FOREIGN KEY (sopir_id) REFERENCES sopir(id)
);

-- Drop existing trigger if exists
DROP TRIGGER IF EXISTS after_unit_status_update;

-- Create new improved trigger
DELIMITER //

CREATE TRIGGER after_unit_status_update 
AFTER UPDATE ON unit
FOR EACH ROW 
BEGIN
    IF OLD.status != NEW.status THEN
        UPDATE jadwal 
        SET status = NEW.status
        WHERE unit_id = NEW.id 
        AND waktu_berangkat >= CURDATE();
    END IF;
END//

DELIMITER ;

-- Tiket table
CREATE TABLE tiket (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_tiket VARCHAR(20) NOT NULL UNIQUE,
    jadwal_id INT NOT NULL,
    pelanggan_id INT,
    reservasi_id INT,
    kursi_id INT,
    nama_penumpang VARCHAR(100) NOT NULL,
    nomor_kursi VARCHAR(5) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    tipe_tiket ENUM('DEWASA', 'ANAK', 'LANSIA') DEFAULT 'DEWASA',
    bagasi_kg DECIMAL(5,2) DEFAULT 0,
    biaya_bagasi DECIMAL(10,2) DEFAULT 0,
    biaya_layanan DECIMAL(10,2) DEFAULT 0,
    total_biaya DECIMAL(10,2) AS (harga + biaya_bagasi + biaya_layanan) STORED,
    status_pembayaran ENUM('PENDING', 'DIBAYAR', 'DIBATALKAN', 'EXPIRED') DEFAULT 'PENDING',
    waktu_kedaluwarsa DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id),
    FOREIGN KEY (pelanggan_id) REFERENCES user_account(id),
    FOREIGN KEY (reservasi_id) REFERENCES reservasi(id),
    FOREIGN KEY (kursi_id) REFERENCES kursi(id)
);

-- Rename pelanggan table to user_account and add fields
DROP TABLE IF EXISTS pelanggan;
CREATE TABLE user_account (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    alamat TEXT,
    foto_profile VARCHAR(255),
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('LAKI-LAKI', 'PEREMPUAN'),
    status ENUM('AKTIF', 'NONAKTIF', 'BLOCKED') DEFAULT 'AKTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jadwal_id INT NOT NULL,
    jumlah_tiket INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_account(id),
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id)
);

-- Review table
CREATE TABLE review (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jadwal_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_account(id),
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id),
    UNIQUE KEY unique_user_review (user_id, jadwal_id)
);

-- Pembayaran table
CREATE TABLE pembayaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tiket_id INT NOT NULL,
    pelanggan_id INT NOT NULL,
    kode_pembayaran VARCHAR(20) UNIQUE NOT NULL,
    metode_pembayaran ENUM('TRANSFER', 'VIRTUAL_ACCOUNT', 'QRIS', 'CASH') NOT NULL,
    bukti_pembayaran VARCHAR(255),
    total_pembayaran DECIMAL(10,2) NOT NULL,
    status_pembayaran ENUM('PENDING', 'DIBAYAR', 'DIBATALKAN', 'EXPIRED') DEFAULT 'PENDING',
    waktu_pembayaran DATETIME,
    bukti_pembayaran VARCHAR(255),
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tiket_id) REFERENCES tiket(id),
    FOREIGN KEY (pelanggan_id) REFERENCES user_account(id)
);

-- Kursi table
CREATE TABLE kursi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    unit_id INT NOT NULL,
    nomor_kursi VARCHAR(5) NOT NULL,
    status ENUM('TERSEDIA', 'DIBOOKING', 'TERJUAL') DEFAULT 'TERSEDIA',
    FOREIGN KEY (unit_id) REFERENCES unit(id),
    UNIQUE KEY unique_kursi (unit_id, nomor_kursi)
);

-- Reservasi table
CREATE TABLE reservasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_reservasi VARCHAR(20) UNIQUE NOT NULL,
    pelanggan_id INT NOT NULL,
    jadwal_id INT NOT NULL,
    kursi_id INT NOT NULL,
    status ENUM('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
    waktu_reservasi DATETIME DEFAULT CURRENT_TIMESTAMP,
    waktu_expired DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES user_account(id),
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id),
    FOREIGN KEY (kursi_id) REFERENCES kursi(id)
);

-- Gaji Karyawan table
CREATE TABLE gaji_karyawan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    karyawan_id INT NOT NULL,
    bulan DATE NOT NULL,
    gaji_pokok DECIMAL(10,2) NOT NULL,
    tunjangan DECIMAL(10,2) DEFAULT 0,
    potongan DECIMAL(10,2) DEFAULT 0,
    total_gaji DECIMAL(10,2) NOT NULL,
    status_pembayaran ENUM('BELUM', 'SUDAH') DEFAULT 'BELUM',
    tanggal_pembayaran DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
    UNIQUE KEY `unique_gaji_karyawan` (`karyawan_id`, `bulan`)
);

-- Gaji Sopir table
CREATE TABLE gaji_sopir (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sopir_id INT NOT NULL,
    bulan DATE NOT NULL,
    gaji_pokok DECIMAL(10,2) NOT NULL,
    bonus_perjalanan DECIMAL(10,2) DEFAULT 0,
    potongan DECIMAL(10,2) DEFAULT 0,
    total_gaji DECIMAL(10,2) NOT NULL,
    status_pembayaran ENUM('BELUM', 'SUDAH') DEFAULT 'BELUM',
    tanggal_pembayaran DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sopir_id) REFERENCES sopir(id),
    UNIQUE KEY `unique_gaji_sopir` (`sopir_id`, `bulan`)
);

-- Pengeluaran table
CREATE TABLE pengeluaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    kategori ENUM('OPERASIONAL', 'MAINTENANCE', 'GAJI', 'ASURANSI', 'PAJAK', 'LAINNYA') NOT NULL,
    keterangan TEXT NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    bukti_transaksi VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Laporan Keuangan view
CREATE VIEW laporan_keuangan AS
SELECT 
    DATE_FORMAT(t.created_at, '%Y-%m') as periode,
    SUM(t.harga) as pendapatan,
    COALESCE(SUM(p.jumlah), 0) as pengeluaran,
    SUM(t.harga) - COALESCE(SUM(p.jumlah), 0) as laba_kotor,
    (SUM(t.harga) - COALESCE(SUM(p.jumlah), 0)) * 0.8 as laba_bersih  -- Asumsi 20% untuk pajak dan biaya lain
FROM 
    tiket t
LEFT JOIN 
    pengeluaran p ON DATE_FORMAT(t.created_at, '%Y-%m') = DATE_FORMAT(p.tanggal, '%Y-%m')
GROUP BY 
    DATE_FORMAT(t.created_at, '%Y-%m')
ORDER BY 
    periode DESC;

-- Add triggers for seat management
DELIMITER //

CREATE TRIGGER after_reservasi_insert
AFTER INSERT ON reservasi
FOR EACH ROW
BEGIN
    UPDATE kursi SET status = 'DIBOOKING'
    WHERE id = NEW.kursi_id;
END//

CREATE TRIGGER after_tiket_insert
AFTER INSERT ON tiket
FOR EACH ROW
BEGIN
    UPDATE kursi SET status = 'TERJUAL'
    WHERE id = NEW.kursi_id;
END//

CREATE TRIGGER after_reservasi_cancel
AFTER UPDATE ON reservasi
FOR EACH ROW
BEGIN
    IF NEW.status = 'CANCELLED' THEN
        UPDATE kursi SET status = 'TERSEDIA'
        WHERE id = NEW.kursi_id;
    END IF;
END//

CREATE TRIGGER after_payment_status_update
AFTER UPDATE ON pembayaran
FOR EACH ROW
BEGIN
    IF NEW.status_pembayaran != OLD.status_pembayaran THEN
        UPDATE tiket 
        SET status_pembayaran = NEW.status_pembayaran
        WHERE kode_tiket LIKE CONCAT(NEW.kode_pembayaran, '%');
    END IF;
END//

DELIMITER ;

-- Insert sample admin
INSERT INTO admin (username, password, nama_lengkap, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com');
-- Password: password
