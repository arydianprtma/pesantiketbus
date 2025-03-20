<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'process_monthly':
            $month = $_POST['month'] . '-01'; // Tambahkan tanggal untuk format yang valid
            
            try {
                // Cek format tanggal valid
                $date = DateTime::createFromFormat('Y-m-d', $month);
                if (!$date || $date->format('Y-m-d') !== $month) {
                    throw new Exception('Format tanggal tidak valid');
                }

                // Cek apakah sudah ada data gaji bulan ini
                $check = $conn->prepare("SELECT COUNT(*) FROM gaji_karyawan WHERE DATE_FORMAT(bulan, '%Y-%m') = ?");
                $check->bind_param('s', $_POST['month']);
                $check->execute();
                $count = $check->get_result()->fetch_row()[0];

                if ($count > 0) {
                    throw new Exception('Gaji bulan ini sudah diproses');
                }

                // Proses gaji untuk semua karyawan aktif
                $sql = "INSERT INTO gaji_karyawan (karyawan_id, bulan, gaji_pokok, tunjangan, potongan, total_gaji, status_pembayaran)
                        SELECT id, ?, gaji_pokok, tunjangan, potongan_default,
                        (gaji_pokok + tunjangan - potongan_default), 'BELUM'
                        FROM karyawan WHERE status = 'AKTIF'";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $month);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Gaji berhasil diproses']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        case 'get_salary':
            try {
                $karyawan_id = $_POST['karyawan_id'];
                $month = $_POST['month'];
                
                $sql = "SELECT * FROM gaji_karyawan 
                        WHERE karyawan_id = ? AND DATE_FORMAT(bulan, '%Y-%m') = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('is', $karyawan_id, $month);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    echo json_encode($row);
                } else {
                    throw new Exception('Data gaji tidak ditemukan');
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_salary') {
        try {
            $karyawan_id = $_GET['karyawan_id'];
            $month = $_GET['month'];
            
            $sql = "SELECT * FROM gaji_karyawan 
                    WHERE karyawan_id = ? AND DATE_FORMAT(bulan, '%Y-%m') = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('is', $karyawan_id, $month);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode($row);
            } else {
                throw new Exception('Data gaji tidak ditemukan');
            }
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
