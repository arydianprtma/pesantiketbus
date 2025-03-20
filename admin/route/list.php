<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get routes
$query = "SELECT * FROM route ORDER BY kota_asal";
$stmt = $conn->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rute - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="flex-1">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-2 text-sm mb-3">
                            <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                            <span class="text-gray-400">/</span>
                            <span class="text-indigo-600 font-medium">Rute</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800">Manajemen Rute</h1>
                        <p class="text-sm text-gray-600 mt-1">Kelola data rute perjalanan bus</p>
                    </div>
                    <a href="tambah.php" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Tambah Rute
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kota Asal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kota Tujuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jarak (KM)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Tempuh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Tiket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['kota_asal']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['kota_tujuan']) ?></td>
                                    <td class="px-6 py-4"><?= number_format($row['jarak_km'], 1) ?> KM</td>
                                    <td class="px-6 py-4"><?= date('H:i', strtotime($row['waktu_tempuh'])) ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['harga_tiket'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <button onclick="editRoute(<?= $row['id'] ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button onclick="deleteRoute(<?= $row['id'] ?>)" 
                                                class="ml-3 text-red-600 hover:text-red-900">Hapus</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editRoute(id) {
        window.location.href = `edit.php?id=${id}`;
    }

    function deleteRoute(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data rute akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus.php?id=${id}`;
            }
        });
    }
    </script>

    <?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $_SESSION['success'] ?>',
            timer: 2000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success']); ?>
    </script>
    <?php endif; ?>
</body>
</html>
