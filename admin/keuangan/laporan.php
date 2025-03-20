<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$dari_tanggal = isset($_GET['dari_tanggal']) ? $_GET['dari_tanggal'] : date('Y-m-01');
$sampai_tanggal = isset($_GET['sampai_tanggal']) ? $_GET['sampai_tanggal'] : date('Y-m-t');

// Get laporan data
$query = "SELECT * FROM laporan_keuangan WHERE tanggal BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $dari_tanggal, $sampai_tanggal);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <span class="text-indigo-600 font-medium">Laporan Keuangan</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Keuangan</h1>
                <p class="text-sm text-gray-600 mt-1">Lihat dan kelola laporan keuangan perusahaan</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form action="" method="GET" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Dari Tanggal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                                <input type="date" id="dari_tanggal" name="dari_tanggal" value="<?= htmlspecialchars($dari_tanggal) ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <!-- Sampai Tanggal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                                <input type="date" id="sampai_tanggal" name="sampai_tanggal" value="<?= htmlspecialchars($sampai_tanggal) ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3">
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                                Tampilkan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pendapatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengeluaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Laba Kotor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Laba Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['pendapatan'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['pengeluaran'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['laba_kotor'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['laba_bersih'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Add validation for date range
    document.getElementById('dari_tanggal').addEventListener('change', function() {
        document.getElementById('sampai_tanggal').min = this.value;
    });

    document.getElementById('sampai_tanggal').addEventListener('change', function() {
        document.getElementById('dari_tanggal').max = this.value;
    });
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

    <?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= $_SESSION['error'] ?>'
        });
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>
</body>
</html>