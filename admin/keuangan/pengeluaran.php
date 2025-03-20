<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle file upload
        $bukti_transaksi = '';
        if (isset($_FILES['bukti_transaksi']) && $_FILES['bukti_transaksi']['error'] == 0) {
            $target_dir = "../../assets/img/pengeluaran/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $bukti_transaksi = time() . '_' . basename($_FILES["bukti_transaksi"]["name"]);
            move_uploaded_file($_FILES["bukti_transaksi"]["tmp_name"], $target_dir . $bukti_transaksi);
        }

        $stmt = $conn->prepare("INSERT INTO pengeluaran (tanggal, kategori, keterangan, jumlah, bukti_transaksi) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", 
            $_POST['tanggal'],
            $_POST['kategori'],
            $_POST['keterangan'],
            $_POST['jumlah'],
            $bukti_transaksi
        );
        
        $stmt->execute();
        $_SESSION['success'] = "Pengeluaran berhasil ditambahkan";
        header('Location: pengeluaran.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal menambahkan pengeluaran: " . $e->getMessage();
    }
}

// Get pengeluaran data
$query = "SELECT * FROM pengeluaran ORDER BY tanggal DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - Admin Panel</title>
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
                    <span class="text-indigo-600 font-medium">Pengeluaran</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengeluaran</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola data pengeluaran perusahaan</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form action="" method="POST" class="space-y-6" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                <input type="text" id="tanggal" name="tanggal" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <select name="kategori" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="" selected disabled>Pilih Kategori</option>
                                    <option value="OPERASIONAL">Operasional</option>
                                    <option value="MAINTENANCE">Maintenance</option>
                                    <option value="GAJI">Gaji</option>
                                    <option value="ASURANSI">Asuransi</option>
                                    <option value="PAJAK">Pajak</option>
                                    <option value="LAINNYA">Lainnya</option>
                                </select>
                            </div>

                            <!-- Jumlah -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                                <input type="number" name="jumlah" required placeholder="Contoh: 1500000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <!-- Bukti Transaksi -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transaksi</label>
                                <input type="file" name="bukti_transaksi" accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                              file:rounded-full file:border-0 file:text-sm file:font-semibold
                                              file:bg-indigo-50 file:text-indigo-600
                                              hover:file:bg-indigo-100">
                                <p class="mt-2 text-sm text-gray-500">Format: JPG, PNG. Maksimal 2MB</p>
                            </div>

                            <!-- Keterangan -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <textarea name="keterangan" rows="3" required placeholder="Masukkan keterangan"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3">
                            <button type="reset"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                                Reset
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bukti</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['kategori']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td class="px-6 py-4">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['bukti_transaksi']): ?>
                                            <img src="/Pesan-Tiket-Bus/assets/img/pengeluaran/<?= htmlspecialchars($row['bukti_transaksi']) ?>" 
                                                 alt="Bukti Transaksi" class="h-12 w-12 object-cover rounded cursor-pointer"
                                                 onclick="showImage('/Pesan-Tiket-Bus/assets/img/pengeluaran/<?= htmlspecialchars($row['bukti_transaksi']) ?>', 'Bukti Transaksi')">
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <button onclick="editPengeluaran(<?= $row['id'] ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button onclick="deletePengeluaran(<?= $row['id'] ?>)" 
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

    <script>
    function editPengeluaran(id) {
        window.location.href = `edit_pengeluaran.php?id=${id}`;
    }

    function deletePengeluaran(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data pengeluaran akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus_pengeluaran.php?id=${id}`;
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

    // Initialize Flatpickr
    flatpickr("#tanggal", {
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

    <?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $_SESSION['success'] ?>',
            timer: 2000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success']); ?>
    </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= $_SESSION['error'] ?>'
        });
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>
</body>
</html>