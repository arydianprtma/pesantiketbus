<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Query to get monthly income
$query = "SELECT DATE_FORMAT(t.created_at, '%Y-%m') as periode, SUM(t.harga) as pendapatan
          FROM tiket t
          WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?
          GROUP BY DATE_FORMAT(t.created_at, '%Y-%m')";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $selected_month);
$stmt->execute();
$result = $stmt->get_result();
$pendapatan = $result->fetch_assoc()['pendapatan'] ?? 0;

// Query to get monthly expenses
$query = "SELECT DATE_FORMAT(p.tanggal, '%Y-%m') as periode, SUM(p.jumlah) as pengeluaran
          FROM pengeluaran p
          WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = ?
          GROUP BY DATE_FORMAT(p.tanggal, '%Y-%m')";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $selected_month);
$stmt->execute();
$result = $stmt->get_result();
$pengeluaran = $result->fetch_assoc()['pengeluaran'] ?? 0;

$laba_kotor = $pendapatan - $pengeluaran;
$laba_bersih = $laba_kotor * 0.8; // Assuming 20% for taxes and other costs
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendapatan Bulanan - Admin Panel</title>
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
                    <span class="text-indigo-600 font-medium">Pendapatan Bulanan</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Pendapatan Bulanan</h1>
                <p class="text-sm text-gray-600 mt-1">Laporan pendapatan bulanan</p>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form action="" method="GET" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Bulan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                                <input type="month" id="bulan" name="bulan" value="<?= $selected_month ?>"
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pendapatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengeluaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Laba Kotor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Laba Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4"><?= htmlspecialchars($selected_month) ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($pendapatan, 0, ',', '.') ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($laba_kotor, 0, ',', '.') ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('bulan').addEventListener('change', function() {
        window.location.href = `?month=${this.value}`;
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