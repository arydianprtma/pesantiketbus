<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get current month and year
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch all employees with their salary data for selected month
$query = "SELECT k.*, 
          COALESCE(g.gaji_pokok, k.gaji_pokok) as gaji_pokok,
          COALESCE(g.tunjangan, k.tunjangan) as tunjangan,
          COALESCE(g.potongan, k.potongan_default) as potongan,
          COALESCE(g.total_gaji, k.gaji_pokok + k.tunjangan - k.potongan_default) as total_gaji,
          g.status_pembayaran,
          g.tanggal_pembayaran
          FROM karyawan k
          LEFT JOIN gaji_karyawan g ON k.id = g.karyawan_id 
          AND DATE_FORMAT(g.bulan, '%Y-%m') = ?
          WHERE k.status = 'AKTIF'
          ORDER BY k.nama_karyawan";

$stmt = $conn->prepare($query);
$stmt->execute([$current_month]); // Using PDO array parameter binding
$result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Using PDO fetch method

// Get list of months with salary data
$month_query = "SELECT DISTINCT DATE_FORMAT(bulan, '%Y-%m') as bulan FROM gaji_karyawan ORDER BY bulan DESC";
$months_stmt = $conn->query($month_query);
$months = $months_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaji Karyawan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <span class="text-indigo-600 font-medium">Gaji Karyawan</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Penggajian Karyawan</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola data gaji karyawan per bulan</p>
            </div>

            <!-- Main Content -->
            <main class="p-8">
                <div class="bg-white rounded-lg shadow-md">
                    <!-- Header Actions -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <select id="month-selector" onchange="changeMonth(this.value)" 
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <?php 
                                    // Add current month if not in database
                                    $current_in_list = false;
                                    $formatted_current = date('Y-m');
                                    
                                    // First Option - Current Selection
                                    echo "<option value='{$current_month}'>" . date('F Y', strtotime($current_month . '-01')) . "</option>";
                                    
                                    // List from database
                                    foreach($months as $month): 
                                        if($month['bulan'] == $current_month) continue;
                                        if($month['bulan'] == $formatted_current) $current_in_list = true;
                                    ?>
                                        <option value="<?= $month['bulan'] ?>">
                                            <?= date('F Y', strtotime($month['bulan'] . '-01')) ?>
                                        </option>
                                    <?php endforeach; 
                                    
                                    // Add current month if not found
                                    if(!$current_in_list && $formatted_current != $current_month): ?>
                                        <option value="<?= $formatted_current ?>">
                                            <?= date('F Y') ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                                <button onclick="processMonthlyPayroll()" 
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    Proses Gaji Bulan Ini
                                </button>
                            </div>
                            <button onclick="exportToExcel()" 
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                Export Excel
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tunjangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Potongan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($result as $row): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php if ($row['foto_karyawan']): ?>
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img class="h-10 w-10 rounded-full object-cover" 
                                                         src="/Pesan-Tiket-Bus/assets/img/karyawan/<?= htmlspecialchars($row['foto_karyawan']) ?>" 
                                                         alt="Foto <?= htmlspecialchars($row['nama_karyawan']) ?>">
                                                </div>
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <div class="font-medium text-gray-900"><?= htmlspecialchars($row['nama_karyawan']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($row['nik']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($row['jabatan']) ?></td>
                                    <td class="px-6 py-4"><?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4"><?= number_format($row['tunjangan'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4"><?= number_format($row['potongan'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 font-medium"><?= number_format($row['total_gaji'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $row['status_pembayaran'] == 'SUDAH' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= $row['status_pembayaran'] == 'SUDAH' ? 'Sudah Dibayar' : 'Belum Dibayar' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <button onclick="editGaji(<?= $row['id'] ?>)" 
                                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <?php if($row['status_pembayaran'] != 'SUDAH'): ?>
                                            <button onclick="markAsPaid(<?= $row['id'] ?>)"
                                                    class="ml-3 text-green-600 hover:text-green-900">Tandai Dibayar</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit Salary Modal -->
    <div id="editSalaryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Edit Informasi Gaji</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editSalaryForm" class="p-6 space-y-4">
                <input type="hidden" id="editKaryawanId" name="karyawan_id">
                <input type="hidden" id="editMonth" name="month">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok</label>
                    <input type="number" id="editGajiPokok" name="gaji_pokok" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan</label>
                    <input type="number" id="editTunjangan" name="tunjangan" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Potongan</label>
                    <input type="number" id="editPotongan" name="potongan" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function changeMonth(value) {
            window.location.href = `?month=${value}`;
        }

        function showLoading(message = 'Loading...') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
            });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function processMonthlyPayroll() {
            Swal.fire({
                title: 'Proses Gaji?',
                text: "Ini akan menghitung ulang gaji semua karyawan untuk bulan ini",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Memproses gaji...');
                    const month = document.getElementById('month-selector').value;
                    
                    fetch('ajax_gaji.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=process_monthly&month=${month}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message);
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showError(data.error || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        showError(error.message);
                    });
                }
            });
        }

        function editGaji(id) {
            showLoading('Mengambil data...');
            const month = document.getElementById('month-selector').value;
            
            fetch(`ajax_gaji.php?action=get_salary&karyawan_id=${id}&month=${month}`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.error) {
                        showError(data.error);
                        return;
                    }
                    
                    document.getElementById('editKaryawanId').value = id;
                    document.getElementById('editMonth').value = month;
                    document.getElementById('editGajiPokok').value = data.gaji_pokok || '';
                    document.getElementById('editTunjangan').value = data.tunjangan || '';
                    document.getElementById('editPotongan').value = data.potongan || '';
                    
                    document.getElementById('editSalaryModal').classList.remove('hidden');
                })
                .catch(error => {
                    showError('Terjadi kesalahan saat mengambil data gaji');
                });
        }

        function markAsPaid(id) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: "Tandai gaji ini sebagai sudah dibayar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Tandai!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Memproses pembayaran...');
                    const month = document.getElementById('month-selector').value;
                    
                    fetch('ajax_gaji.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=mark_paid&karyawan_id=${id}&month=${month}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message);
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showError(data.error || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        showError('Terjadi kesalahan saat memperbarui status pembayaran');
                    });
                }
            });
        }

        document.getElementById('editSalaryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoading('Menyimpan perubahan...');
            const formData = new FormData(this);
            formData.append('action', 'update_salary');

            fetch('ajax_gaji.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showError(data.error || 'Terjadi kesalahan');
                }
            })
            .catch(error => {
                showError('Terjadi kesalahan saat memperbarui data gaji');
            });
        });

        function closeEditModal() {
            document.getElementById('editSalaryModal').classList.add('hidden');
            // Reset form
            document.getElementById('editSalaryForm').reset();
        }

        // Tambahkan event listener untuk click di luar modal
        document.getElementById('editSalaryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Close modal with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>
</body>
</html>

<?php
// Update gaji section
if (isset($gaji_pokok) && isset($karyawan_id)) {
    $stmt = $conn->prepare("UPDATE karyawan SET gaji_pokok = ? WHERE id = ?");
    $stmt->execute([$gaji_pokok, $karyawan_id]); // Using PDO array parameter binding
}
?>
