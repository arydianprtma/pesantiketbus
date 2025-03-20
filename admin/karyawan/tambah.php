<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = $_POST['nik'];
    $nama_karyawan = $_POST['nama_karyawan'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jabatan = $_POST['jabatan'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $status = $_POST['status'];
    $gaji_pokok = floatval($_POST['gaji_pokok']);
    $tunjangan = floatval($_POST['tunjangan']);
    $potongan_default = floatval($_POST['potongan_default'] ?? 0);
    
    // Handle file upload
    $foto_karyawan = '';
    if (isset($_FILES['foto_karyawan']) && $_FILES['foto_karyawan']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['foto_karyawan']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $newname = uniqid('karyawan_') . '.' . $filetype;
            $upload_dir = '../../assets/img/karyawan/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto_karyawan']['tmp_name'], $upload_dir . $newname)) {
                $foto_karyawan = $newname;
            }
        }
    }

    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert karyawan data with default salary info
        $stmt = $conn->prepare("INSERT INTO karyawan (nik, nama_karyawan, jenis_kelamin, alamat, no_telp, tempat_lahir, tanggal_lahir, jabatan, tanggal_masuk, status, foto_karyawan, gaji_pokok, tunjangan, potongan_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssddd", 
            $nik, $nama_karyawan, $jenis_kelamin, $alamat, $no_telp, 
            $tempat_lahir, $tanggal_lahir, $jabatan, $tanggal_masuk, 
            $status, $foto_karyawan, $gaji_pokok, $tunjangan, $potongan_default
        );
        
        if ($stmt->execute()) {
            $conn->commit();
            header('Location: list.php?success=added');
            exit();
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        @layer components {
            input[type='text'],
            input[type='email'],
            input[type='tel'],
            input[type='date'],
            input[type='number'],
            select,
            textarea {
                @apply mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400;
                @apply focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500;
                @apply disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:shadow-none;
            }

            label {
                @apply block text-sm font-medium text-gray-700 mb-1;
            }

            .form-group {
                @apply mb-4;
            }
        }
    </style>
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
                    <a href="/Pesan-Tiket-Bus/admin/karyawan/list.php" class="text-gray-600 hover:text-indigo-600">Karyawan</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">Tambah Karyawan</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Karyawan Baru</h1>
                <p class="text-sm text-gray-600 mt-1">Tambahkan data karyawan baru ke dalam sistem</p>
            </div>

            <!-- Main Content -->
            <div class="p-8">
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Form Data Karyawan</h2>
                            <a href="list.php" class="text-sm text-gray-600 hover:text-indigo-600">Kembali ke List</a>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="p-4 bg-red-50 text-red-600">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Section 1: Data Pribadi -->
                            <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-900 mb-4">Data Pribadi</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                                    <input type="text" name="nik" maxlength="16" required 
                                           placeholder="Masukkan 16 digit NIK">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                    <input type="text" name="nama_karyawan" required 
                                           placeholder="Masukkan nama lengkap">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" required>
                                        <option value="" disabled>Pilih Jenis Kelamin</option>
                                        <option value="LAKI-LAKI">Laki-laki</option>
                                        <option value="PEREMPUAN">Perempuan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" required 
                                           placeholder="Masukkan tempat lahir">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" required max="<?= date('Y-m-d') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- Section 2: Informasi Kepegawaian -->
                            <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-900 mb-4">Informasi Kepegawaian</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                    <input type="tel" name="no_telp" required 
                                           placeholder="Contoh: 081234567890">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                    <input type="text" name="jabatan" required 
                                           placeholder="Masukkan jabatan">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masuk</label> 
                                    <input type="date" id="tanggal_masuk" name="tanggal_masuk" required max="<?= date('Y-m-d') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" required>
                                        <option value="AKTIF">Aktif</option>
                                        <option value="NONAKTIF">Non-Aktif</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                    <textarea name="alamat" rows="3" required 
                                              placeholder="Masukkan alamat lengkap"></textarea>
                                </div>

                                <!-- Add new fields for salary information -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok</label>
                                    <input type="number" name="gaji_pokok" required 
                                           placeholder="Masukkan gaji pokok"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan</label>
                                    <input type="number" name="tunjangan" required 
                                           placeholder="Masukkan tunjangan"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Potongan Default</label>
                                    <input type="number" name="potongan_default" 
                                           placeholder="Masukkan potongan default (opsional)"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>

                            <!-- Section 3: Foto -->
                            <div class="col-span-full bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-900 mb-4">Foto Karyawan</h3>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Karyawan</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="foto_karyawan" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload foto</span>
                                                <input id="foto_karyawan" name="foto_karyawan" type="file" class="sr-only" accept="image/*">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, WEBP up to 2MB</p>
                                    </div>
                                </div>
                                <div id="image-preview" class="mt-4 hidden">
                                    <img src="#" alt="Preview" class="mx-auto h-32 w-32 object-cover rounded-full">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                            <a href="list.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image preview and drag & drop functionality
        const dropZone = document.querySelector('.border-dashed');
        const fileInput = document.getElementById('foto_karyawan');
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
