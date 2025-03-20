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
            try {
                $month = $_POST['month'] . '-01';
                
                // Validasi format tanggal
                if (!DateTime::createFromFormat('Y-m-d', $month)) {
                    throw new Exception('Format tanggal tidak valid');
                }

                // Cek duplikasi
                $check = $conn->prepare("SELECT COUNT(*) FROM gaji_sopir WHERE DATE_FORMAT(bulan, '%Y-%m') = ?");
                $check->bind_param('s', $_POST['month']);
                $check->execute();
                $count = $check->get_result()->fetch_row()[0];

                if ($count > 0) {
                    throw new Exception('Gaji bulan ini sudah diproses');
                }

                // Insert gaji_sopir records untuk bulan ini
                $sql = "INSERT INTO gaji_sopir (sopir_id, bulan, gaji_pokok, total_gaji, status_pembayaran)
                        SELECT id, ?, gaji_pokok, gaji_pokok, 'BELUM'
                        FROM sopir WHERE status = 'AKTIF'";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $month);
                $stmt->execute();

                echo json_encode([
                    'success' => true,
                    'message' => 'Gaji berhasil diproses'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;
            
        case 'update_salary':
            // ... kode untuk update gaji ...
            break;
            
        case 'mark_paid':
            try {
                $sopir_id = $_POST['sopir_id'];
                $month = $_POST['month'];
                $bonus = $_POST['bonus'] ?? 0;
                $potongan = $_POST['potongan'] ?? 0;
                
                // Update bonus, potongan, dan total gaji
                $sql = "UPDATE gaji_sopir 
                        SET bonus_perjalanan = ?,
                            potongan = ?,
                            total_gaji = gaji_pokok + ? - ?,
                            status_pembayaran = 'SUDAH',
                            tanggal_pembayaran = CURRENT_DATE
                        WHERE sopir_id = ? 
                        AND DATE_FORMAT(bulan, '%Y-%m') = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ddddss', 
                    $bonus,
                    $potongan,
                    $bonus,
                    $potongan,
                    $sopir_id,
                    $month
                );
                $stmt->execute();

                echo json_encode([
                    'success' => true,
                    'message' => 'Gaji berhasil diperbarui dan ditandai sudah dibayar'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;
    }
}
