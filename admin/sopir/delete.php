<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $conn->beginTransaction();

        // Get sopir data for file deletion
        $stmt = $conn->prepare("SELECT foto_sopir, foto_sim FROM sopir WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $sopir = $stmt->fetch();

        // Delete related records in gaji_sopir
        $stmt = $conn->prepare("DELETE FROM gaji_sopir WHERE sopir_id = ?");
        $stmt->execute([$_GET['id']]);

        // Delete sopir record
        $stmt = $conn->prepare("DELETE FROM sopir WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        // Delete physical files
        if ($sopir['foto_sopir']) {
            $foto_path = '../../assets/img/sopir/' . $sopir['foto_sopir'];
            if (file_exists($foto_path)) unlink($foto_path);
        }
        if ($sopir['foto_sim']) {
            $sim_path = '../../assets/img/sim/' . $sopir['foto_sim'];
            if (file_exists($sim_path)) unlink($sim_path);
        }

        $conn->commit();
        $_SESSION['success'] = "Data sopir berhasil dihapus";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Gagal menghapus data sopir: " . $e->getMessage();
    }
}

header('Location: list.php');
exit();
