<?php
session_start();
require_once '../config/database.php';

// Ambil data user jika sudah login
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM user_account WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}

// Ambil daftar kota (unik) dari tabel route
$kota_query = "SELECT DISTINCT kota_asal FROM route ORDER BY kota_asal";
$kota_asal = $conn->query($kota_query)->fetchAll(PDO::FETCH_COLUMN);

$kota_query = "SELECT DISTINCT kota_tujuan FROM route ORDER BY kota_tujuan";
$kota_tujuan = $conn->query($kota_query)->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Bus Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100"></body>
    <!-- Navbar -->
    <nav class="bg-white shadow-lg fixed w-full z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-12">
            <div class="flex justify-between h-16">
                <!-- Logo dan Menu Utama -->
                <div class="flex">
                    <a href="index.php" class="flex items-center lg:ml-8">
                        <img src="assets/images/logo.png" alt="Bus Ticket Logo" class="h-8 w-auto sm:h-10">
                    </a>
                    <div class="hidden lg:flex items-center space-x-8 ml-16">
                        <a href="index.php" class="py-4 px-2 text-blue-500 border-b-4 border-blue-500 font-semibold">Home</a>
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Jadwal</a>
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Rute</a>
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Tentang</a>
                        <a href="#" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Kontak</a>
                    </div>
                </div>

                <!-- Right Side Menu -->
                <div class="flex items-center lg:space-x-6">    
                    <!-- Desktop Menu -->
                    <div class="hidden lg:flex items-center space-x-4 mr-8">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="relative group" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" 
                                        class="flex items-center space-x-3 py-2 px-4 rounded-md bg-gray-100 hover:bg-gray-200 focus:outline-none">
                                    <!-- Ganti icon dengan foto profil -->
                                    <img src="<?= !empty($user['foto_profile']) ? $user['foto_profile'] : 'assets/images/default-avatar.png' ?>" 
                                         class="h-8 w-8 rounded-full object-cover border-2 border-white"
                                         alt="Profile">
                                    <span class="text-gray-700"><?= $_SESSION['user_name'] ?></span>
                                    <i class="fas fa-chevron-down text-gray-500 text-sm transition-transform duration-200" 
                                       :class="{ 'transform rotate-180': open }"></i>
                                </button>
                                <div class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 transition-all duration-200 ease-in-out transform opacity-0 scale-95 origin-top-right"
                                     :class="{ 'transform opacity-100 scale-100': open, 'invisible': !open }"
                                     style="display: none;"
                                     x-show="open">
                                    <a href="user/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                    </a>
                                    <a href="user/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user-edit mr-2"></i> Edit Profile
                                    </a>
                                    <a href="user/bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-ticket-alt mr-2"></i> Pesanan Saya
                                    </a>
                                    <hr class="my-1">
                                    <a href="process/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="hidden sm:flex items-center space-x-4">
                                <a href="auth/login.php" class="py-2 px-4 bg-white text-blue-500 border border-blue-500 rounded-md hover:bg-blue-500 hover:text-white transition duration-300">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                                </a>
                                <a href="auth/register.php" class="py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300">
                                    <i class="fas fa-user-plus mr-2"></i> Register
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="lg:hidden flex items-center">
                        <button class="mobile-menu-button p-2 rounded-md hover:bg-gray-100 focus:outline-none">
                            <i class="fas fa-bars text-gray-500 text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden mobile-menu lg:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-blue-500 bg-blue-50">Home</a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">Jadwal</a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">Rute</a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">Tentang</a>
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">Kontak</a>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-5">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle text-gray-400 text-2xl"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800"><?= $_SESSION['user_name'] ?></div>
                            <div class="text-sm font-medium text-gray-500"><?= $_SESSION['user_email'] ?></div>
                        </div>
                    </div>
                    <div class="mt-3 px-2 space-y-1">
                        <a href="user/dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                        <a href="user/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profile
                        </a>
                        <a href="user/bookings.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-500 hover:bg-blue-50">
                            <i class="fas fa-ticket-alt mr-2"></i> Pesanan Saya
                        </a>
                        <a href="process/auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center justify-center space-x-4 px-4">
                        <a href="auth/login.php" class="w-full py-2 px-4 bg-white text-blue-500 border border-blue-500 rounded-md hover:bg-blue-500 hover:text-white text-center transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                        <a href="auth/register.php" class="w-full py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-center transition duration-300">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-white mb-4">Pesan Tiket Bus Online</h1>
                    <p class="text-xl text-blue-100 mb-8">Perjalanan nyaman dengan armada terpercaya</p>
                </div>

                <!-- Search Box -->
                <div class="max-w-3xl mx-auto">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <form action="tiket/search.php" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kota Asal</label>
                                    <select name="from" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Pilih Kota Asal</option>
                                        <?php foreach($kota_asal as $kota): ?>
                                            <option value="<?= htmlspecialchars($kota) ?>">
                                                <?= htmlspecialchars($kota) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kota Tujuan</label>
                                    <select name="to" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Pilih Kota Tujuan</option>
                                        <?php foreach($kota_tujuan as $kota): ?>
                                            <option value="<?= htmlspecialchars($kota) ?>">
                                                <?= htmlspecialchars($kota) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-search mr-2"></i> Cari Tiket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full p-4 mx-auto w-16 h-16 flex items-center justify-center mb-4">
                            <i class="fas fa-ticket-alt text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Pemesanan Mudah</h3>
                        <p class="text-gray-600">Pesan tiket bus dengan cepat dan mudah</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full p-4 mx-auto w-16 h-16 flex items-center justify-center mb-4">
                            <i class="fas fa-bus text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Armada Terbaik</h3>
                        <p class="text-gray-600">Bus nyaman dengan fasilitas lengkap</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full p-4 mx-auto w-16 h-16 flex items-center justify-center mb-4">
                            <i class="fas fa-clock text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">24/7 Support</h3>
                        <p class="text-gray-600">Layanan pelanggan siap membantu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Routes Section -->
        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-8">Rute Populer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Add popular routes cards here -->
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile menu JavaScript -->
    <script>
        const btn = document.querySelector("button.mobile-menu-button");
        const menu = document.querySelector(".mobile-menu");

        btn.addEventListener("click", () => {
            menu.classList.toggle("hidden");
        });
    </script>
</body>
</html>
