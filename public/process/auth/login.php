<?php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    try {
        // Cek user dengan email
        $stmt = $conn->prepare("SELECT * FROM user_account WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];

            // Set cookie jika remember me dicentang
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 hari

                // Update remember token di database
                $stmt = $conn->prepare("UPDATE user_account SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
            }

            header('Location: ../../index.php');
        } else {
            header('Location: ../../auth/login.php?error=Email atau password salah');
        }
    } catch(PDOException $e) {
        header('Location: ../../auth/login.php?error=Gagal login: ' . $e->getMessage());
    }
}
