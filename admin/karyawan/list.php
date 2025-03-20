<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Delete karyawan if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Start transaction
    $conn->begin_transaction();
    try {
        // Get current photo filename
        $stmt = $conn->prepare("SELECT foto_karyawan FROM karyawan WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $karyawan = $result->fetch_assoc();
        
        // Delete related gaji_karyawan records first
        $stmt = $conn->prepare("DELETE FROM gaji_karyawan WHERE karyawan_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Delete the photo file if exists
        if ($karyawan['foto_karyawan']) {
            $foto_path = '../../assets/img/karyawan/' . $karyawan['foto_karyawan'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
        
        // Delete karyawan record
        $stmt = $conn->prepare("DELETE FROM karyawan WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

// Fetch all karyawan
$query = "SELECT * FROM karyawan ORDER BY nama_karyawan";
$stmt = $conn->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Karyawan - Admin Panel</title>
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
                    <a href="#" class="text-gray-600 hover:text-indigo-600">Karyawan</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">List Karyawan</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Daftar Karyawan</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola data karyawan perusahaan</p>
            </div>

            <!-- Main content -->
            <main class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">Data Karyawan</h2>
                        <a href="tambah.php" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-4 rounded-lg">
                            Tambah Karyawan
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php if ($row['foto_karyawan']): ?>
                                            <img src="/Pesan-Tiket-Bus/assets/img/karyawan/<?php echo htmlspecialchars($row['foto_karyawan']); ?>" 
                                                 alt="Foto Karyawan" 
                                                 class="h-10 w-10 rounded-full object-cover cursor-pointer hover:opacity-75 transition-opacity"
                                                 onclick="openImageModal(this.src)">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['nik']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['nama_karyawan']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $row['status'] === 'AKTIF' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 flex space-x-3">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="relative">
            <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="modalImage" src="" alt="Preview" class="max-h-[80vh] max-w-[90vw] rounded-lg">
        </div>
    </div>

    <script>
        function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) closeImageModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeImageModal();
        });
    </script>
</body>
</html>
