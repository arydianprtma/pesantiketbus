<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/Pesan-Tiket-Bus/public');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Refresh user data setiap kali navbar dimuat
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM user_account WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
    }
}
?>
<!-- Navbar -->
<nav class="bg-white shadow-lg fixed w-full z-10">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-12">
        <div class="flex justify-between h-16">
            <!-- Logo dan Menu Utama -->
            <div class="flex">
                <a href="/Pesan-Tiket-Bus/public/index.php" class="flex items-center lg:ml-8">
                    <img src="/Pesan-Tiket-Bus/public/assets/images/logo.png" alt="Bus Ticket Logo" class="h-8 w-auto sm:h-10">
                </a>
                <div class="hidden lg:flex items-center space-x-8 ml-16">
                    <a href="/Pesan-Tiket-Bus/public/index.php" class="py-4 px-2 text-gray-500 font-semibold hover:text-blue-500 transition duration-300">Home</a>
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
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" 
                                    class="flex items-center space-x-3 py-2 px-4 rounded-md bg-gray-100 hover:bg-gray-200 focus:outline-none">
                                <!-- Foto profil -->
                                <img src="<?= isset($user['foto_profile']) && !empty($user['foto_profile']) 
                                    ? BASE_PATH . '/' . $user['foto_profile'] 
                                    : BASE_PATH . '/assets/images/default-avatar.png' ?>" 
                                     class="h-8 w-8 rounded-full object-cover border-2 border-white"
                                     alt="Profile">
                                <span class="text-gray-700"><?= $_SESSION['user_name'] ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-sm"></i>
                            </button>
                            <div x-show="open" 
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5" 
                                 style="display: none;">
                                <a href="/Pesan-Tiket-Bus/public/user/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                <a href="/Pesan-Tiket-Bus/public/user/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profile</a>
                                <a href="/Pesan-Tiket-Bus/public/user/bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pesanan Saya</a>
                                <hr class="my-1">
                                <a href="/Pesan-Tiket-Bus/public/process/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center">
                    <button class="mobile-menu-button">
                        <i class="fas fa-bars text-gray-500 text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="hidden mobile-menu lg:hidden">
        // ...existing mobile menu code...
    </div>
</nav>

<script>
    // Mobile menu toggle
    const btn = document.querySelector(".mobile-menu-button");
    const menu = document.querySelector(".mobile-menu");
    
    btn.addEventListener("click", () => {
        menu.classList.toggle("hidden");
    });
</script>
