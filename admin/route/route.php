<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get units - perbaikan query untuk mendapatkan unit yang aktif
$units_query = "SELECT * FROM unit WHERE status = 'AKTIF' ORDER BY nomor_unit";
$units_stmt = $conn->prepare($units_query);
$units_stmt->execute();
$units = $units_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get routes - perbaikan query rute
$routes_query = "SELECT * FROM route ORDER BY kota_asal";
$routes_stmt = $conn->prepare($routes_query);
$routes_stmt->execute();
$routes = $routes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sopir - perbaikan query sopir
$sopir_query = "SELECT * FROM sopir WHERE status = 'AKTIF' ORDER BY nama_lengkap";
$sopir_stmt = $conn->prepare($sopir_query);
$sopir_stmt->execute();
$sopir = $sopir_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get waktu_tempuh from route
        $route_query = "SELECT waktu_tempuh FROM route WHERE id = ?";
        $route_stmt = $conn->prepare($route_query);
        $route_stmt->execute([$_POST['route_id']]);
        $waktu_tempuh = $route_stmt->fetch(PDO::FETCH_COLUMN);
        
        // Combine tanggal and jam for waktu_berangkat
        $tanggal = $_POST['tanggal_berangkat'];
        $jam = $_POST['jam_berangkat'];
        $waktu_berangkat = $tanggal . ' ' . $jam;
        
        // Calculate waktu_tiba 
        list($hours, $minutes) = explode(':', $waktu_tempuh);
        $total_minutes = ($hours * 60) + $minutes;
        $waktu_tiba = date('Y-m-d H:i:s', strtotime($waktu_berangkat . ' + ' . $total_minutes . ' minutes'));
        
        $stmt = $conn->prepare("INSERT INTO jadwal (unit_id, route_id, sopir_id, waktu_berangkat, waktu_tiba) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['unit_id'],
            $_POST['route_id'],
            $_POST['sopir_id'],
            $waktu_berangkat,
            $waktu_tiba
        ]);
        
        $_SESSION['success'] = "Jadwal berhasil ditambahkan";
        header('Location: route.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal menambahkan jadwal: " . $e->getMessage();
    }
}

// Get existing schedules - tambah filter untuk jadwal yang belum lewat
$schedule_query = "SELECT j.*, u.nomor_unit, u.nama_unit, r.kota_asal, r.kota_tujuan, s.nama_lengkap as nama_sopir
                  FROM jadwal j
                  JOIN unit u ON j.unit_id = u.id
                  JOIN route r ON j.route_id = r.id
                  JOIN sopir s ON j.sopir_id = s.id
                  WHERE j.waktu_berangkat >= CURDATE()
                  ORDER BY j.waktu_berangkat ASC";
$schedule_stmt = $conn->prepare($schedule_query);
$schedule_stmt->execute();
$schedules = $schedule_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Unit - Admin Panel</title>
    <link rel="icon" type="image/png" href="/Pesan-Tiket-Bus/assets/img/icon/favicon.png">
    <link rel="apple-touch-icon" href="/Pesan-Tiket-Bus/assets/img/icon/apple-touch-icon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Hapus import Flatpickr -->
    <meta http-equiv="refresh" content="30">  <!-- Auto refresh setiap 30 detik -->
    <style>
        .countdown-timer {
            position: absolute;
            top: 1rem;
            right: 2rem;
            background: rgba(17, 24, 39, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 50;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="flex-1">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4 relative">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-2 text-sm mb-3">
                            <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                            <span class="text-gray-400">/</span>
                            <span class="text-indigo-600 font-medium">Route Unit</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800">Management Route Unit</h1>
                        <p class="text-sm text-gray-600 mt-1">Atur jadwal perjalanan bus</p>
                    </div>
                    <div id="countdown" class="countdown-timer">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Refresh dalam 30s</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Form Tambah Jadwal -->
                    <form action="" method="POST" class="mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Unit Bus Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Bus</label>
                                <select name="unit_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500">
                                    <option value="" disabled selected>Pilih Unit Bus</option>
                                    <?php foreach($units as $unit): ?>
                                        <option value="<?= $unit['id'] ?>">
                                            <?= htmlspecialchars($unit['nomor_unit']) ?> - <?= htmlspecialchars($unit['nama_unit']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Route Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rute</label>
                                <select name="route_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500">
                                    <option value="" disabled selected>Pilih Rute</option>
                                    <?php foreach($routes as $route): ?>
                                        <option value="<?= $route['id'] ?>">
                                            <?= htmlspecialchars($route['kota_asal']) ?> → <?= htmlspecialchars($route['kota_tujuan']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sopir Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sopir</label>
                                <select name="sopir_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500">
                                    <option value="" disabled selected>Pilih Sopir</option>
                                    <?php foreach($sopir as $driver): ?>
                                        <option value="<?= $driver['id'] ?>">
                                            <?= htmlspecialchars($driver['nama_lengkap']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Waktu Keberangkatan -->
                            <div class="flex gap-4">
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Keberangkatan</label>
                                    <input type="date" id="tanggal_berangkat" name="tanggal_berangkat" required 
                                           min="<?= date('Y-m-d') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Keberangkatan</label>
                                    <select id="jam_berangkat" name="jam_berangkat" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="" selected disabled>Pilih Jam</option>
                                        <?php
                                        // Generate options dari jam 01:00 sampai 24:00 dengan interval 30 menit
                                        $start = strtotime('01:00');
                                        $end = strtotime('24:00'); 
                                        for ($time = $start; $time <= $end; $time = strtotime('+30 minutes', $time)) {
                                            echo '<option value="' . date('H:i', $time) . '">' . date('H:i', $time) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="md:col-span-3">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    Tambah Jadwal
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Jadwal -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Bus</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rute</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sopir</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keberangkatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kedatangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($schedules as $schedule): ?>
                                    <tr>
                                        <td class="px-6 py-4 text-sm">
                                            <?= htmlspecialchars($schedule['nomor_unit']) ?> - 
                                            <?= htmlspecialchars($schedule['nama_unit']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?= htmlspecialchars($schedule['kota_asal']) ?> → 
                                            <?= htmlspecialchars($schedule['kota_tujuan']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?= htmlspecialchars($schedule['nama_sopir']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?= date('d/m/Y H:i', strtotime($schedule['waktu_berangkat'])) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?= date('d/m/Y H:i', strtotime($schedule['waktu_tiba'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                echo match($schedule['status']) {
                                                    'AKTIF' => 'bg-green-100 text-green-800',
                                                    'MAINTENANCE' => 'bg-yellow-100 text-yellow-800',
                                                    'NONAKTIF' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                ?>">
                                                <?= $schedule['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateStatus(id, status) {
        Swal.fire({
            title: 'Update Status?',
            text: `Apakah anda yakin ingin mengubah status menjadi ${status.replace('_', ' ')}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `update_status.php?id=${id}&status=${status}`;
            }
        });
    }

    // Handle form submit untuk menggabungkan tanggal dan jam
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const tanggal = document.getElementById('tanggal_berangkat').value;
        const jam = document.getElementById('jam_berangkat').value;
        const waktuBerangkat = `${tanggal} ${jam}:00`;
        
        // Buat hidden input untuk mengirim waktu keberangkatan lengkap
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'waktu_berangkat';
        hiddenInput.value = waktuBerangkat;
        this.appendChild(hiddenInput);
        
        this.submit();
    });

    // Add countdown timer display
    function startCountdown() {
        let timeLeft = 30;
        const countdownText = document.querySelector('#countdown span');
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownText.textContent = `Refresh dalam ${timeLeft}s`;

            if (timeLeft <= 5) {
                document.getElementById('countdown').style.backgroundColor = 'rgba(220, 38, 38, 0.9)';
            }

            if (timeLeft < 0) {
                clearInterval(countdown);
            }
        }, 1000);
    }

    startCountdown();
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