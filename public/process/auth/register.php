<?php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = trim($_POST['nik']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $password = $_POST['password'];
    $confirm_password = $_POST['password_confirmation'];

    // Validasi NIK
    if (strlen($nik) !== 16 || !is_numeric($nik)) {
        header('Location: ../../auth/register.php?error=NIK harus 16 digit angka');
        exit();
    }

    // Cek NIK sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM user_account WHERE nik = ?");
    $stmt->execute([$nik]);
    if ($stmt->rowCount() > 0) {
        header('Location: ../../auth/register.php?error=NIK sudah terdaftar');
        exit();
    }

    // Validasi input
    if ($password !== $confirm_password) {
        header('Location: ../../auth/register.php?error=Password tidak sama');
        exit();
    }

    // Cek email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM user_account WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header('Location: ../../auth/register.php?error=Email sudah terdaftar');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert user baru dengan NIK
        $sql = "INSERT INTO user_account (nik, nama_lengkap, email, password, no_hp) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nik, $nama_lengkap, $email, $hashed_password, $no_hp]);

        header('Location: ../../auth/login.php?success=1');
    } catch(PDOException $e) {
        header('Location: ../../auth/register.php?error=Gagal mendaftar: ' . $e->getMessage());
    }
}
