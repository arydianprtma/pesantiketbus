<?php
session_start();
require_once '../../config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM user_account WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Budiman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> 
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const profilePhoto = document.querySelector('.profile-photo');
                const previewImage = document.getElementById('preview-image');
                const previewContainer = document.getElementById('preview-container');
                
                reader.onload = function(e) {
                    profilePhoto.src = e.target.result;
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Refresh foto setelah upload berhasil
        document.addEventListener('DOMContentLoaded', function() {
            <?php if(isset($_GET['success']) && isset($_SESSION['new_photo'])): ?>
                const photoPath = '../<?= $_SESSION['new_photo'] ?>';
                document.querySelector('.profile-photo').src = photoPath;
                document.getElementById('preview-image').src = photoPath;
                <?php unset($_SESSION['new_photo']); ?>
            <?php endif; ?>
        });
    </script>
</head>
<body class="bg-gray-50">
    <!-- Fixed Navbar -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
        <?php include '../components/navbar.php'; ?>
    </header>

    <!-- Content wrapper -->
    <div class="pt-16">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 h-48">
            <div class="container mx-auto px-4 h-full flex items-end">
                <div class="relative pb-16">
                    <!-- Foto profil dihapus dari sini -->
                </div>
            </div>
        </div>

        <!-- Main Content dengan margin negatif -->
        <div class="container mx-auto px-4 -mt-6 pb-8"> <!-- Menambahkan pb-8 untuk padding bottom -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <!-- Breadcrumb -->
                <nav class="mb-6">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li><a href="../index.php" class="text-gray-500 hover:text-blue-600">Home</a></li>
                        <li><span class="text-gray-500">/</span></li>
                        <li class="text-blue-600">Edit Profile</li>
                    </ol>
                </nav>

                <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    Profile berhasil diperbarui!
                </div>
                <?php endif; ?>

                <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?= $_GET['error'] ?>
                </div>
                <?php endif; ?>

                <form action="../process/user/update_profile.php" method="POST" enctype="multipart/form-data">
                    <!-- Photo Upload Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Foto Profile</h3>
                        <div class="flex items-center gap-6">
                            <div class="flex items-center space-x-6">
                                <!-- Preview Container -->
                                <div id="preview-container" class="<?= empty($user['foto_profile']) ? 'hidden' : '' ?>">
                                    <img id="preview-image" 
                                         src="<?= !empty($user['foto_profile']) ? '../' . $user['foto_profile'] : '' ?>" 
                                         class="w-24 h-24 object-cover rounded-full border-4 border-white shadow-md"
                                         alt="Preview">
                                </div>
                                
                                <!-- Upload Button -->
                                <label class="flex flex-col items-center gap-2 cursor-pointer">
                                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                                        <i class="fas fa-camera mr-2"></i>Ganti Foto
                                    </span>
                                    <input type="file" name="foto_profile" class="hidden" 
                                           onchange="previewImage(this)" accept="image/*">
                                </label>
                            </div>
                            <span class="text-sm text-gray-500">Format: JPG, PNG (Max. 2MB)</span>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="space-y-8">
                        <h3 class="text-lg font-semibold">Informasi Pribadi</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- NIK -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                                <input type="text" value="<?= $user['nik'] ?>" disabled
                                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-3 px-4 text-gray-500">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" value="<?= $user['email'] ?>" disabled
                                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-3 px-4 text-gray-500">
                            </div>

                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required
                                       class="block w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- No. HP -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                                <input type="tel" name="no_hp" value="<?= $user['no_hp'] ?>" required
                                       class="block w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Tanggal Lahir -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="<?= $user['tanggal_lahir'] ?>"
                                       class="block w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Jenis Kelamin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                                <select name="jenis_kelamin" 
                                        class="block w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="LAKI-LAKI" <?= $user['jenis_kelamin'] == 'LAKI-LAKI' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="PEREMPUAN" <?= $user['jenis_kelamin'] == 'PEREMPUAN' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>

                            <!-- Alamat -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                                <textarea name="alamat" rows="4" 
                                          class="block w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-blue-500 focus:ring-blue-500"><?= $user['alamat'] ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 mt-8 pt-6 border-t">
                        <button type="button" onclick="window.history.back()" 
                                class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>