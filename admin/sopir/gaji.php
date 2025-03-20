<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Query untuk mengambil data gaji sopir
$query = "SELECT s.*, 
          COALESCE(g.gaji_pokok, s.gaji_pokok) as gaji_pokok,
          COALESCE(g.bonus_perjalanan, 0) as bonus_perjalanan,
          COALESCE(g.potongan, 0) as potongan,
          COALESCE(g.total_gaji, s.gaji_pokok) as total_gaji,
          g.status_pembayaran,
          g.tanggal_pembayaran
          FROM sopir s
          LEFT JOIN gaji_sopir g ON s.id = g.sopir_id 
          AND DATE_FORMAT(g.bulan, '%Y-%m') = ?
          WHERE s.status = 'AKTIF'
          ORDER BY s.nama_lengkap";

$stmt = $conn->prepare($query);
$stmt->execute([$current_month]); // Changed from bind_param to execute with array
$result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Changed from get_result to fetchAll

// Get months list - fixed PDO query
$month_query = "SELECT DISTINCT DATE_FORMAT(bulan, '%Y-%m') as bulan 
               FROM gaji_sopir 
               ORDER BY bulan DESC";
$months_stmt = $conn->query($month_query);
$months = $months_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaji Sopir - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="flex-1">
            <!-- Header + Month Selector -->
            <div class="bg-white border-b px-8 py-4">
                <div class="flex items-center gap-2 text-sm mb-3">
                    <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                    <span class="text-gray-400">/</span>
                    <a href="/Pesan-Tiket-Bus/admin/sopir/list.php" class="text-gray-600 hover:text-indigo-600">Sopir</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-indigo-600 font-medium">Gaji Sopir</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Penggajian Sopir</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola data gaji sopir per bulan</p>
                <div class="mt-4 flex justify-between items-center">
                    <select id="month-selector" 
                            class="flatpickr rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <?php
                        $current_in_list = false;
                        $current_month_str = date('F Y', strtotime($current_month . '-01'));
                        echo "<option value='{$current_month}'>{$current_month_str}</option>";

                        // Changed foreach loop for months
                        foreach($months as $month): 
                            if($month['bulan'] == $current_month) continue;
                            $month_str = date('F Y', strtotime($month['bulan'] . '-01'));
                        ?>
                            <option value="<?= $month['bulan'] ?>">
                                <?= $month_str ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button onclick="processMonthlyPayroll()"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Proses Gaji Bulan Ini
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. SIM</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bonus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Potongan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($row['foto_sopir']): ?>
                                            <img src="/Pesan-Tiket-Bus/assets/img/sopir/<?= htmlspecialchars($row['foto_sopir']) ?>"
                                                 class="h-10 w-10 rounded-full object-cover"
                                                 alt="<?= htmlspecialchars($row['nama_lengkap']) ?>">
                                        <?php else: ?>
                                            <span class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($row['nama_lengkap']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($row['nik']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nomor_sim']) ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($row['gaji_pokok'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($row['bonus_perjalanan'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($row['potongan'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $row['status_pembayaran'] == 'SUDAH' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= $row['status_pembayaran'] == 'SUDAH' ? 'Sudah Dibayar' : 'Belum Dibayar' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if($row['status_pembayaran'] != 'SUDAH'): ?>
                                        <button onclick="markAsPaid(<?= $row['id'] ?>)"
                                                class="text-green-600 hover:text-green-900">
                                            Tandai Sudah Dibayar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Proses Pembayaran Gaji</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="paymentForm" class="p-6 space-y-4">
                <input type="hidden" id="sopirId" name="sopir_id">
                <input type="hidden" id="monthValue" name="month">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bonus Perjalanan</label>
                    <input type="number" id="bonus" name="bonus" value="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Potongan</label>
                    <input type="number" id="potongan" name="potongan" value="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closePaymentModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                        Simpan & Tandai Dibayar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function markAsPaid(id) {
        document.getElementById('sopirId').value = id;
        document.getElementById('monthValue').value = document.getElementById('month-selector').value;
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentForm').reset();
    }

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'mark_paid');

        fetch('ajax_gaji.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.error || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat memproses pembayaran'
            });
        });
    });

    function changeMonth(value) {
        window.location.href = `?month=${value}`;
    }

    function processMonthlyPayroll() {
        Swal.fire({
            title: 'Proses Gaji?',
            text: "Ini akan menghitung gaji semua sopir untuk bulan ini",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Proses!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.error
                        });
                    }
                });
            }
        });
    }

    flatpickr("#month-selector", {
        plugins: [],
        defaultDate: "<?= $current_month ?>-01",
        dateFormat: "Y-m",
        altFormat: "F Y",
        locale: {
            months: {
                shorthand: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                longhand: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"]
            }
        },
        onChange: function(selectedDates, dateStr) {
            window.location.href = `?month=${dateStr}`;
        }
    });

    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePaymentModal();
    });

    // Close modal when clicking outside
    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) closePaymentModal();
    });
    </script>
</body>
</html>
