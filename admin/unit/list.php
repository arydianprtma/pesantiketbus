<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Delete unit if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM unit WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all units - Fix query execution
$query = "SELECT * FROM unit ORDER BY nomor_unit";
$stmt = $conn->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Unit - Admin Panel</title>
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
                    <a href="#" class="text-gray-600 hover:text-indigo-600">Unit</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">List Unit Bus</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">List Unit Bus</h1>
                <p class="text-sm text-gray-600 mt-1">Menampilkan semua data unit bus</p>
            </div>

            <!-- Main content -->
            <main class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Daftar Unit Bus</h2>
                        <a href="tambah.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Tambah Unit Baru
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php if ($row['foto_unit']): ?>
                                            <img src="/Pesan-Tiket-Bus/assets/img/bus/<?php echo htmlspecialchars($row['foto_unit']); ?>" 
                                                 alt="Foto Bus" 
                                                 class="h-16 w-24 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                                 onclick="openImageModal(this.src)">
                                        <?php else: ?>
                                            <div class="h-16 w-24 bg-gray-100 flex items-center justify-center rounded-lg">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['nomor_unit']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['nama_unit']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            echo match($row['jenis_unit']) {
                                                'EKONOMI' => 'bg-gray-100 text-gray-800',
                                                'BISNIS' => 'bg-blue-100 text-blue-800',
                                                'EKSEKUTIF' => 'bg-purple-100 text-purple-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                            <?php echo htmlspecialchars($row['jenis_unit']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['kapasitas']); ?> Kursi</td>
                                    <td class="px-6 py-4">
                                        <select onchange="updateStatus(<?= $row['id'] ?>, this.value)" 
                                                class="text-sm rounded-full px-2 py-1
                                                <?php 
                                                echo match($row['status']) {
                                                    'AKTIF' => 'bg-green-100 text-green-800',
                                                    'MAINTENANCE' => 'bg-yellow-100 text-yellow-800',
                                                    'NONAKTIF' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                ?>">
                                            <option value="AKTIF" <?= $row['status'] == 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
                                            <option value="MAINTENANCE" <?= $row['status'] == 'MAINTENANCE' ? 'selected' : '' ?>>MAINTENANCE</option>
                                            <option value="NONAKTIF" <?= $row['status'] == 'NONAKTIF' ? 'selected' : '' ?>>NONAKTIF</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 flex space-x-3">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus unit ini?')">Hapus</a>
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
            <img id="modalImage" src="" alt="Preview Bus" class="max-h-[80vh] max-w-[90vw] rounded-lg">
        </div>
    </div>

    <script>
        function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modal.classList.remove('hidden');
            
            // Prevent scrolling when modal is open
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            
            // Restore scrolling
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        function updateStatus(unitId, status) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `unit_id=${unitId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
