<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || empty($_POST['kode_pembayaran']) || empty($_FILES['bukti'])) {
    header('Location: ../index.php');
    exit();
}

try {
    $kode_pembayaran = $_POST['kode_pembayaran'];
    $file = $_FILES['bukti'];

    // Validasi file
    $allowed = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
    }

    // Generate nama file unik
    $filename = 'bukti_' . $kode_pembayaran . '_' . date('YmdHis') . '.' . $ext;
    $target_dir = "../uploads/bukti_pembayaran/";
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        throw new Exception('Gagal mengupload file.');
    }

    // Update database
    $stmt = $conn->prepare("UPDATE pembayaran SET bukti_pembayaran = ?, status_pembayaran = 'PENDING' WHERE kode_pembayaran = ? AND pelanggan_id = ?");
    $stmt->execute([$filename, $kode_pembayaran, $_SESSION['user_id']]);

    $_SESSION['success'] = 'Bukti pembayaran berhasil diupload. Admin akan memverifikasi pembayaran Anda.';
    header('Location: pembayaran.php?kode=' . $kode_pembayaran);

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: pembayaran.php?kode=' . $kode_pembayaran);
}
