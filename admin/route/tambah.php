<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("INSERT INTO route (kota_asal, kota_tujuan, jarak_km, waktu_tempuh, harga_tiket) VALUES (?, ?, ?, ?, ?)");
        
        // Change bind_param to PDO execute with array
        $stmt->execute([
            $_POST['kota_asal'],
            $_POST['kota_tujuan'],
            $_POST['jarak_km'],
            $_POST['waktu_tempuh'],
            $_POST['harga_tiket']
        ]);
        
        $_SESSION['success'] = "Rute berhasil ditambahkan";
        header('Location: list.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal menambahkan rute: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rute - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Hapus import Flatpickr -->
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="flex-1">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4">
                <div class="flex items-center gap-2 text-sm mb-3">
                    <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <a href="/Pesan-Tiket-Bus/admin/route/list.php" class="text-gray-600 hover:text-indigo-600">Rute</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">Tambah Rute</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Rute Baru</h1>
                <p class="text-sm text-gray-600 mt-1">Tambahkan data rute perjalanan baru</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kota Asal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota Asal</label>
                                <input type="text" name="kota_asal" required placeholder="Masukkan kota asal"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <!-- Kota Tujuan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota Tujuan</label>
                                <input type="text" name="kota_tujuan" required placeholder="Masukkan kota tujuan"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <!-- Jarak KM -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jarak (KM)</label>
                                <input type="number" step="0.1" name="jarak_km" required placeholder="Contoh: 150.5"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <!-- Waktu Tempuh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Tempuh</label>
                                <input type="time" id="waktu_tempuh" name="waktu_tempuh" required step="1800"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <!-- Harga Tiket -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Tiket</label>
                                <input type="number" name="harga_tiket" required placeholder="Contoh: 150000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="list.php" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= $_SESSION['error'] ?>',
        });
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
