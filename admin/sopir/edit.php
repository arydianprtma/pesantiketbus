<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit();
}

// Get sopir data
$stmt = $conn->prepare("SELECT * FROM sopir WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$sopir = $stmt->get_result()->fetch_assoc();

if (!$sopir) {
    header('Location: list.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle foto sopir upload
        $foto_sopir = $sopir['foto_sopir'];
        if (isset($_FILES['foto_sopir']) && $_FILES['foto_sopir']['error'] == 0) {
            $target_dir = "../../assets/img/sopir/";
            // Delete old file if exists
            if ($foto_sopir && file_exists($target_dir . $foto_sopir)) {
                unlink($target_dir . $foto_sopir);
            }
            $foto_sopir = time() . '_' . basename($_FILES["foto_sopir"]["name"]);
            move_uploaded_file($_FILES["foto_sopir"]["tmp_name"], $target_dir . $foto_sopir);
        }

        // Handle foto SIM upload
        $foto_sim = $sopir['foto_sim'];
        if (isset($_FILES['foto_sim']) && $_FILES['foto_sim']['error'] == 0) {
            $target_dir = "../../assets/img/sim/";
            // Delete old file if exists
            if ($foto_sim && file_exists($target_dir . $foto_sim)) {
                unlink($target_dir . $foto_sim);
            }
            $foto_sim = time() . '_' . basename($_FILES["foto_sim"]["name"]);
            move_uploaded_file($_FILES["foto_sim"]["tmp_name"], $target_dir . $foto_sim);
        }

        $stmt = $conn->prepare("UPDATE sopir SET nik=?, nama_lengkap=?, alamat=?, telepon=?, nomor_sim=?, jenis_sim=?, gaji_pokok=?, status=?, foto_sopir=?, foto_sim=? WHERE id=?");
        
        $stmt->bind_param("ssssssdsssi", 
            $_POST['nik'],
            $_POST['nama_lengkap'],
            $_POST['alamat'],
            $_POST['telepon'],
            $_POST['nomor_sim'],
            $_POST['jenis_sim'],
            $_POST['gaji_pokok'],
            $_POST['status'],
            $foto_sopir,
            $foto_sim,
            $id
        );
        
        $stmt->execute();
        
        $_SESSION['success'] = "Data sopir berhasil diperbarui";
        header('Location: list.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal memperbarui data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sopir - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                    <a href="/Pesan-Tiket-Bus/admin/sopir/list.php" class="text-gray-600 hover:text-indigo-600">Sopir</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">Edit Sopir</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Data Sopir</h1>
                <p class="text-sm text-gray-600 mt-1">Edit data sopir yang sudah ada</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form action="" method="POST" class="space-y-6" enctype="multipart/form-data">
                        <!-- Form fields similar to tambah.php but with values from $sopir -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Similar input fields as tambah.php but with value attributes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                                <input type="text" name="nik" required value="<?= htmlspecialchars($sopir['nik']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <!-- Add other fields here -->
                            // ...existing fields with values...

                            <!-- Foto Section - Berjejer -->
                            <div class="md:col-span-2 grid grid-cols-2 gap-6">
                                <!-- Foto Sopir -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Sopir</label>
                                    <div class="mt-1">
                                        <div id="preview_foto_sopir" class="<?= $sopir['foto_sopir'] ? '' : 'hidden' ?> w-32 h-32 rounded-lg overflow-hidden bg-gray-100 mb-2">
                                            <img class="w-full h-full object-cover" 
                                                 src="<?= $sopir['foto_sopir'] ? '/Pesan-Tiket-Bus/assets/img/sopir/' . htmlspecialchars($sopir['foto_sopir']) : '' ?>" 
                                                 alt="Preview Foto Sopir">
                                        </div>
                                        <input type="file" name="foto_sopir" accept="image/*" 
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                                      file:rounded-full file:border-0 file:text-sm file:font-semibold
                                                      file:bg-indigo-50 file:text-indigo-600
                                                      hover:file:bg-indigo-100"
                                               onchange="previewImage(this, 'preview_foto_sopir')">
                                        <p class="mt-2 text-sm text-gray-500">Format: JPG, PNG. Maksimal 2MB</p>
                                    </div>
                                </div>

                                <!-- Foto SIM -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto SIM</label>
                                    <div class="mt-1">
                                        <div id="preview_foto_sim" class="<?= $sopir['foto_sim'] ? '' : 'hidden' ?> w-32 h-32 rounded-lg overflow-hidden bg-gray-100 mb-2">
                                            <img class="w-full h-full object-cover" 
                                                 src="<?= $sopir['foto_sim'] ? '/Pesan-Tiket-Bus/assets/img/sim/' . htmlspecialchars($sopir['foto_sim']) : '' ?>" 
                                                 alt="Preview Foto SIM">
                                        </div>
                                        <input type="file" name="foto_sim" accept="image/*"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                                      file:rounded-full file:border-0 file:text-sm file:font-semibold
                                                      file:bg-indigo-50 file:text-indigo-600
                                                      hover:file:bg-indigo-100"
                                               onchange="previewImage(this, 'preview_foto_sim')">
                                        <p class="mt-2 text-sm text-gray-500">Format: JPG, PNG. Maksimal 2MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="list.php" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= $_SESSION['error'] ?>',
        });
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>

    <script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const previewImg = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Initialize Flatpickr for dates
    flatpickr("#tanggal_mulai", {
        dateFormat: "Y-m-d",
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
                longhand: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"]
            },
            months: {
                shorthand: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                longhand: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"]
            }
        }
    });
    </script>
</body>
</html>
