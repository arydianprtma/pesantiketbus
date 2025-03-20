<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch statistics
$stats = [
    'units' => $conn->query("SELECT COUNT(*) as total FROM unit")->fetch_assoc()['total'] ?? 0,
    'employees' => $conn->query("SELECT COUNT(*) as total FROM karyawan")->fetch_assoc()['total'] ?? 0,
    'tickets' => $conn->query("SELECT COUNT(*) as total FROM tiket")->fetch_assoc()['total'] ?? 0,
    'revenue' => $conn->query("SELECT SUM(harga) as total FROM tiket")->fetch_assoc()['total'] ?? 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Pemesanan Tiket Bus</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin Budiman</h1>
                    <p class="text-sm text-gray-600">Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600"><?php echo date('l, d F Y'); ?></span>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Unit Bus</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['units']; ?></p>
                            </div>
                            <div class="p-3 bg-blue-500 bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="unit/list.php" class="text-sm text-blue-500 hover:text-blue-600">Lihat detail →</a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Karyawan</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['employees']; ?></p>
                            </div>
                            <div class="p-3 bg-green-500 bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="karyawan/list.php" class="text-sm text-green-500 hover:text-green-600">Lihat detail →</a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Tiket Terjual</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['tickets']; ?></p>
                            </div>
                            <div class="p-3 bg-purple-500 bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="tiket/list.php" class="text-sm text-purple-500 hover:text-purple-600">Lihat detail →</a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                                <p class="text-2xl font-bold text-gray-800">Rp <?php echo number_format($stats['revenue'], 0, ',', '.'); ?></p>
                            </div>
                            <div class="p-3 bg-yellow-500 bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="laporan/pendapatan.php" class="text-sm text-yellow-500 hover:text-yellow-600">Lihat detail →</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h2>
                    <div class="divide-y divide-gray-200">
                        <!-- Add your recent activities here -->
                        <p class="py-3 text-gray-500 text-sm">Belum ada aktivitas terbaru</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
