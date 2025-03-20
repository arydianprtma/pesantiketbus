<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    try {
        $stmt = $conn->prepare("UPDATE jadwal SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $_GET['status'], $_GET['id']);
        $stmt->execute();
        
        $_SESSION['success'] = "Status jadwal berhasil diperbarui";
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal memperbarui status: " . $e->getMessage();
    }
}

header('Location: route.php');
exit();
