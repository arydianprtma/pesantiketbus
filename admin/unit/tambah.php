<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_unit = $_POST['nomor_unit'];
    $nama_unit = $_POST['nama_unit'];
    $jenis_unit = $_POST['jenis_unit'];
    $kapasitas = $_POST['kapasitas'];
    $status = $_POST['status'];
    
    // Handle file upload
    $foto_unit = '';
    if (isset($_FILES['foto_unit']) && $_FILES['foto_unit']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['foto_unit']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            // Create unique filename
            $newname = uniqid('bus_') . '.' . $filetype;
            $upload_dir = '../../assets/img/bus/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto_unit']['tmp_name'], $upload_dir . $newname)) {
                $foto_unit = $newname;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO unit (nomor_unit, nama_unit, jenis_unit, kapasitas, status, foto_unit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $nomor_unit, $nama_unit, $jenis_unit, $kapasitas, $status, $foto_unit);

    if ($stmt->execute()) {
        $message = "Unit berhasil ditambahkan!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Unit - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Include sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <div class="flex-1 overflow-x-hidden">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4">
                <div class="flex items-center gap-2 text-sm mb-3">
                    <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <a href="/Pesan-Tiket-Bus/admin/unit/list.php" class="text-gray-600 hover:text-indigo-600">Unit</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">Tambah Unit Bus</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Unit Bus</h1>
                <p class="text-sm text-gray-600 mt-1">Formulir penambahan unit bus baru</p>
            </div>

            <!-- Main content -->
            <main class="p-8">
                <div class="bg-white rounded-lg shadow-md">
                    <!-- Form Header -->
                    <div class="px-8 py-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Form Tambah Unit</h2>
                        <p class="mt-1 text-sm text-gray-600">Silahkan isi form dibawah ini dengan benar</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="px-8 py-4 <?php echo strpos($message, 'Error') !== false ? 'bg-red-50' : 'bg-green-50'; ?>">
                            <div class="flex items-center p-4 mb-4 text-sm <?php echo strpos($message, 'Error') !== false ? 'text-red-800' : 'text-green-800'; ?> rounded-lg" role="alert">
                                <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                </svg>
                                <span class="font-medium"><?php echo $message; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form Content -->
                    <div class="p-8">
                        <form method="POST" class="space-y-6" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nomor Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="nomor_unit">
                                        Nomor Unit
                                    </label>
                                    <input type="text" name="nomor_unit" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Nama Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="nama_unit">
                                        Nama Unit
                                    </label>
                                    <input type="text" name="nama_unit" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Jenis Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="jenis_unit">
                                        Jenis Unit
                                    </label>
                                    <select name="jenis_unit" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">Pilih Jenis Unit</option>
                                        <option value="EKONOMI">Ekonomi</option>
                                        <option value="BISNIS">Bisnis</option>
                                        <option value="EKSEKUTIF">Eksekutif</option>
                                    </select>
                                </div>

                                <!-- Kapasitas -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="kapasitas">
                                        Kapasitas
                                    </label>
                                    <input type="number" name="kapasitas" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="status">
                                        Status
                                    </label>
                                    <select name="status" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">Pilih Status</option>
                                        <option value="AKTIF">Aktif</option>
                                        <option value="MAINTENANCE">Maintenance</option>
                                        <option value="NONAKTIF">Non Aktif</option>
                                    </select>
                                </div>

                                <!-- Foto Unit -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Foto Unit Bus
                                    </label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="foto_unit" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload foto</span>
                                                    <input id="foto_unit" name="foto_unit" type="file" class="sr-only" accept="image/*">
                                                </label>
                                                <p class="pl-1">atau drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, WEBP hingga 5MB
                                            </p>
                                        </div>
                                    </div>
                                    <div id="image-preview" class="mt-4 hidden">
                                        <img src="#" alt="Preview" class="max-h-48 rounded-lg mx-auto">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-4 mt-8">
                                <a href="list.php" 
                                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Kembali
                                </a>
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Simpan Unit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const dropZone = document.querySelector('.border-dashed');
        const fileInput = document.getElementById('foto_unit');
        const preview = document.getElementById('image-preview');

        function handleFiles(files) {
            const file = files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
                
                // Update the file input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
            }
        }

        // File Input Change
        fileInput.addEventListener('change', function(e) {
            handleFiles(this.files);
        });

        // Drag & Drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-indigo-500', 'bg-indigo-50');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-indigo-500', 'bg-indigo-50');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-indigo-500', 'bg-indigo-50');
            handleFiles(e.dataTransfer.files);
        });
    </script>
</body>
</html>
