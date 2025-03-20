<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get routes
$query = "SELECT * FROM sopir ORDER BY nama_lengkap";
$stmt = $conn->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sopir - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <span class="text-indigo-600 font-medium">Data Sopir</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Sopir</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola data sopir bus</p>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-800">Data Sopir</h2>
                            <a href="tambah.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Tambah Sopir
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. SIM</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto SIM</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis SIM</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php if ($row['foto_sopir']): ?>
                                            <img src="/Pesan-Tiket-Bus/assets/img/sopir/<?= htmlspecialchars($row['foto_sopir']) ?>" 
                                                 alt="Foto <?= htmlspecialchars($row['nama_lengkap']) ?>"
                                                 class="h-12 w-12 rounded-full object-cover cursor-pointer"
                                                 onclick="showImage('/Pesan-Tiket-Bus/assets/img/sopir/<?= htmlspecialchars($row['foto_sopir']) ?>', 'Foto Driver')">
                                        <?php else: ?>
                                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['nik']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['nomor_sim']) ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['foto_sim']): ?>
                                            <img src="/Pesan-Tiket-Bus/assets/img/sim/<?= htmlspecialchars($row['foto_sim']) ?>" 
                                                 alt="SIM <?= htmlspecialchars($row['nama_lengkap']) ?>"
                                                 class="h-12 w-20 object-cover rounded cursor-pointer"
                                                 onclick="showImage('/Pesan-Tiket-Bus/assets/img/sim/<?= htmlspecialchars($row['foto_sim']) ?>', 'Foto SIM')">
                                        <?php else: ?>
                                            <div class="h-12 w-20 bg-gray-200 flex items-center justify-center rounded">
                                                <span class="text-xs text-gray-500">No Image</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['jenis_sim']) ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $row['status'] == 'AKTIF' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <button onclick="editSopir(<?= $row['id'] ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button onclick="deleteSopir(<?= $row['id'] ?>)" 
                                                class="ml-3 text-red-600 hover:text-red-900">Hapus</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editSopir(id) {
            window.location.href = `edit.php?id=${id}`;
        }

        function deleteSopir(id) {
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Data sopir akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete.php?id=${id}`;
                }
            });
        }

        function showImage(src, title) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const imageTitle = document.getElementById('imageTitle');
            
            modalImage.src = src;
            imageTitle.textContent = title;
            modal.classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeImageModal();
        });
    </script>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full bg-white rounded-lg overflow-hidden">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold" id="imageTitle">Preview Image</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <img src="" alt="Preview" id="modalImage" class="max-h-[80vh] mx-auto">
            </div>
        </div>
    </div>
</body>
</html>
