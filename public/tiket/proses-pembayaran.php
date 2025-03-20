<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || empty($_POST)) {
    header('Location: ../index.php');
    exit();
}

try {
    $conn->beginTransaction();

    $jadwal_id = $_POST['jadwal_id'];
    $kursi_array = $_POST['kursi'];
    $nama_penumpang_array = $_POST['nama_penumpang'];
    $nik_array = $_POST['nik'];
    $metode_pembayaran = $_POST['metode_pembayaran'];

    // Generate kode pembayaran
    $kode_pembayaran = 'PAY-' . date('Ymd') . '-' . substr(str_shuffle('0123456789'), 0, 4);

    // Get ticket price
    $stmt = $conn->prepare("SELECT r.harga_tiket FROM jadwal j JOIN route r ON j.route_id = r.id WHERE j.id = ?");
    $stmt->execute([$jadwal_id]);
    $harga_tiket = $stmt->fetch(PDO::FETCH_COLUMN);

    $total_pembayaran = $harga_tiket * count($kursi_array);

    // Insert pembayaran
    $stmt = $conn->prepare("INSERT INTO pembayaran (pelanggan_id, kode_pembayaran, metode_pembayaran, total_pembayaran, status_pembayaran, waktu_pembayaran) 
                           VALUES (?, ?, ?, ?, 'PENDING', DATE_ADD(NOW(), INTERVAL 24 HOUR))");
    $stmt->execute([$_SESSION['user_id'], $kode_pembayaran, $metode_pembayaran, $total_pembayaran]);

    // Insert tickets
    foreach ($kursi_array as $index => $kursi) {
        $kode_tiket = 'TIK-' . date('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO tiket (kode_tiket, jadwal_id, pelanggan_id, nama_penumpang, nomor_kursi, harga, status_pembayaran) 
                               VALUES (?, ?, ?, ?, ?, ?, 'PENDING')");
        $stmt->execute([
            $kode_tiket,
            $jadwal_id,
            $_SESSION['user_id'],
            $nama_penumpang_array[$index],
            $kursi,
            $harga_tiket
        ]);

        // Get last inserted tiket_id
        $tiket_id = $conn->lastInsertId();

        // Update pembayaran with tiket_id
        $stmt = $conn->prepare("UPDATE pembayaran SET tiket_id = ? WHERE kode_pembayaran = ?");
        $stmt->execute([$tiket_id, $kode_pembayaran]);
    }

    $conn->commit();
    
    // Redirect to payment page with success message
    $_SESSION['success'] = 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.';
    header('Location: pembayaran.php?kode=' . $kode_pembayaran);
    exit();

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: pilih-kursi.php?jadwal_id=' . $jadwal_id);
    exit();
}
