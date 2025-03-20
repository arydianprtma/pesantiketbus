<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM route WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        $_SESSION['success'] = "Rute berhasil dihapus";
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal menghapus rute: " . $e->getMessage();
    }
}

header('Location: list.php');
exit();
