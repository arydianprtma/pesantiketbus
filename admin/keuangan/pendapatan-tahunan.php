<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Query to get annual revenue
$query = "SELECT DATE_FORMAT(created_at, '%Y') as tahun, SUM(harga) as total_pendapatan
          FROM tiket
          WHERE DATE_FORMAT(created_at, '%Y') = ?
          GROUP BY tahun";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $selected_year);
$stmt->execute();
$result = $stmt->get_result();
$pendapatan = $result->fetch_assoc();
$total_pendapatan = $pendapatan['total_pendapatan'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendapatan Tahunan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
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
                    <span class="text-indigo-600 font-medium">Pendapatan Tahunan</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Pendapatan Tahunan</h1>
                <p class="text-sm text-gray-600 mt-1">Lihat pendapatan tahunan berdasarkan penjualan tiket</p>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form action="" method="GET" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tahun -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                                <input type="text" id="tahun" name="tahun" value="<?= $selected_year ?>" 
                                       class="flatpickr-year w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4"><?= htmlspecialchars($selected_year) ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    flatpickr("#tahun", {
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y",
                altFormat: "Y",
                theme: "light"
            })
        ],
        defaultDate: "<?= $selected_year ?>-01-01",
        dateFormat: "Y",
        onClose: function(selectedDates, dateStr) {
            window.location.href = `?year=${dateStr}`;
        }
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