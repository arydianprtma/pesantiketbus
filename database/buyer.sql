-- Tables for buyer/customer interface

-- User Account Management
CREATE TABLE user_account (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    alamat TEXT,
    foto_profile VARCHAR(255),
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cart/Keranjang untuk menyimpan tiket yang akan dibeli
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

-- Booking/Pemesanan
CREATE TABLE booking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_booking VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    jadwal_id INT NOT NULL,
    total_tiket INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('PENDING', 'CONFIRMED', 'CANCELLED', 'EXPIRED') DEFAULT 'PENDING',
    metode_pembayaran ENUM('TRANSFER', 'VIRTUAL_ACCOUNT', 'QRIS', 'CREDIT_CARD') NOT NULL,
    batas_pembayaran DATETIME NOT NULL,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_account(id),
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id)
);

-- Detail Booking untuk menyimpan detail penumpang
CREATE TABLE booking_detail (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    kursi_id INT NOT NULL,
    nama_penumpang VARCHAR(100) NOT NULL,
    nomor_identitas VARCHAR(20) NOT NULL,
    tipe_penumpang ENUM('DEWASA', 'ANAK', 'LANSIA') DEFAULT 'DEWASA',
    harga_tiket DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id),
    FOREIGN KEY (kursi_id) REFERENCES kursi(id)
);

-- Pembayaran
CREATE TABLE payment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL UNIQUE,
    kode_pembayaran VARCHAR(50) NOT NULL UNIQUE,
    metode_pembayaran VARCHAR(50) NOT NULL,
    bank_tujuan VARCHAR(50),
    nomor_va VARCHAR(50),
    qr_code VARCHAR(255),
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    status ENUM('PENDING', 'PAID', 'FAILED', 'EXPIRED') DEFAULT 'PENDING',
    bukti_pembayaran VARCHAR(255),
    waktu_pembayaran DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id)
);

-- Review/Rating
CREATE TABLE review (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id),
    UNIQUE KEY unique_booking_review (booking_id)
);

-- Triggers
DELIMITER //

-- Auto update status booking jika pembayaran sukses
CREATE TRIGGER after_payment_status_update
AFTER UPDATE ON payment
FOR EACH ROW
BEGIN
    IF NEW.status = 'PAID' THEN
        UPDATE booking SET status = 'CONFIRMED'
        WHERE id = NEW.booking_id;
    END IF;
END//

-- Auto cancel booking jika melewati batas waktu
CREATE TRIGGER check_booking_expiration
BEFORE UPDATE ON booking
FOR EACH ROW
BEGIN
    IF OLD.status = 'PENDING' AND NOW() > OLD.batas_pembayaran THEN
        SET NEW.status = 'EXPIRED';
    END IF;
END//

DELIMITER ;

-- Views
CREATE VIEW available_schedule AS
SELECT 
    j.*,
    r.kota_asal,
    r.kota_tujuan,
    r.harga_tiket,
    u.jenis_unit,
    u.kapasitas,
    (
        SELECT COUNT(*) 
        FROM booking b 
        WHERE b.jadwal_id = j.id 
        AND b.status = 'CONFIRMED'
    ) as booked_seats
FROM jadwal j
JOIN route r ON j.route_id = r.id
JOIN unit u ON j.unit_id = u.id
WHERE j.status = 'AKTIF'
AND j.waktu_berangkat > NOW();
