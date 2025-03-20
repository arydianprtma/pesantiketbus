<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$unit = null;

// Get unit data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM unit WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $unit = $result->fetch_assoc();
    $stmt->close();

    if (!$unit) {
        header('Location: list.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nomor_unit = $_POST['nomor_unit'];
    $nama_unit = $_POST['nama_unit'];
    $jenis_unit = $_POST['jenis_unit'];
    $kapasitas = $_POST['kapasitas'];
    $status = $_POST['status'];
    
    // Handle file upload
    $foto_unit = $unit['foto_unit']; // Keep existing photo by default
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
                // Delete old photo if exists
                if ($unit['foto_unit'] && file_exists($upload_dir . $unit['foto_unit'])) {
                    unlink($upload_dir . $unit['foto_unit']);
                }
                $foto_unit = $newname;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE unit SET nomor_unit=?, nama_unit=?, jenis_unit=?, kapasitas=?, status=?, foto_unit=? WHERE id=?");
    $stmt->bind_param("sssissi", $nomor_unit, $nama_unit, $jenis_unit, $kapasitas, $status, $foto_unit, $id);

    if ($stmt->execute()) {
        $message = "Unit berhasil diperbarui!";
        // Refresh unit data
        $result = $conn->query("SELECT * FROM unit WHERE id = $id");
        $unit = $result->fetch_assoc();
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
    <title>Edit Unit - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/sidebar.php'; ?>

        <div class="flex-1 overflow-x-hidden">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4">
                <div class="flex items-center gap-2 text-sm mb-3">
                    <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <a href="/Pesan-Tiket-Bus/admin/unit/list.php" class="text-gray-600 hover:text-indigo-600">Unit</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">Edit Unit Bus</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Unit Bus</h1>
                <p class="text-sm text-gray-600 mt-1">Formulir edit data unit bus</p>
            </div>

            <!-- Main content -->
            <main class="p-8">
                <div class="bg-white rounded-lg shadow-md">
                    <!-- Form Header -->
                    <div class="px-8 py-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Form Edit Unit</h2>
                        <p class="mt-1 text-sm text-gray-600">Silahkan edit data dibawah ini dengan benar</p>
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
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($unit['id']); ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nomor Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="nomor_unit">
                                        Nomor Unit
                                    </label>
                                    <input type="text" name="nomor_unit" required
                                           value="<?php echo htmlspecialchars($unit['nomor_unit']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Nama Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="nama_unit">
                                        Nama Unit
                                    </label>
                                    <input type="text" name="nama_unit" required
                                           value="<?php echo htmlspecialchars($unit['nama_unit']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Jenis Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="jenis_unit">
                                        Jenis Unit
                                    </label>
                                    <select name="jenis_unit" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="EKONOMI" <?php echo $unit['jenis_unit'] == 'EKONOMI' ? 'selected' : ''; ?>>Ekonomi</option>
                                        <option value="BISNIS" <?php echo $unit['jenis_unit'] == 'BISNIS' ? 'selected' : ''; ?>>Bisnis</option>
                                        <option value="EKSEKUTIF" <?php echo $unit['jenis_unit'] == 'EKSEKUTIF' ? 'selected' : ''; ?>>Eksekutif</option>
                                    </select>
                                </div>

                                <!-- Kapasitas -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="kapasitas">
                                        Kapasitas
                                    </label>
                                    <input type="number" name="kapasitas" required
                                           value="<?php echo htmlspecialchars($unit['kapasitas']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="status">
                                        Status
                                    </label>
                                    <select name="status" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="AKTIF" <?php echo $unit['status'] == 'AKTIF' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="MAINTENANCE" <?php echo $unit['status'] == 'MAINTENANCE' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="NONAKTIF" <?php echo $unit['status'] == 'NONAKTIF' ? 'selected' : ''; ?>>Non Aktif</option>
                                    </select>
                                </div>

                                <!-- Foto Unit -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Foto Unit Bus
                                    </label>
                                    <?php if ($unit['foto_unit']): ?>
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-500 mb-2">Foto Saat Ini:</p>
                                            <img src="/Pesan-Tiket-Bus/assets/img/bus/<?php echo htmlspecialchars($unit['foto_unit']); ?>" 
                                                 alt="Current Bus Photo" 
                                                 class="h-48 object-cover rounded-lg">
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="foto_unit" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload foto baru</span>
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
                                        <p class="text-sm text-gray-500 mb-2">Preview:</p>
                                        <img src="#" alt="Preview" class="max-h-48 rounded-lg">
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
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('foto_unit').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
