<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT t.*, j.waktu_berangkat, j.waktu_tiba, r.kota_asal, r.kota_tujuan, u.nomor_unit, u.nama_unit 
          FROM tiket t
          JOIN jadwal j ON t.jadwal_id = j.id
          JOIN route r ON j.route_id = r.id 
          JOIN unit u ON j.unit_id = u.id
          WHERE 1=1";

// Add search condition
if ($search !== '') {
    $query .= " AND (t.nama_penumpang LIKE ? OR t.kode_tiket LIKE ?)";
}

// Add status filter if not 'all'
if ($status_filter !== 'all') {
    $query .= " AND t.status_pembayaran = ?";
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);

// Bind parameters based on conditions
if ($search !== '' && $status_filter !== 'all') {
    $search_param = "%$search%";
    $stmt->bind_param('sss', $search_param, $search_param, $status_filter);
} elseif ($search !== '') {
    $search_param = "%$search%";
    $stmt->bind_param('ss', $search_param, $search_param);
} elseif ($status_filter !== 'all') {
    $stmt->bind_param('s', $status_filter);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Online - Admin Panel</title>
    <link rel="icon" type="image/png" href="/Pesan-Tiket-Bus/assets/img/icon/favicon.png">
    <link rel="apple-touch-icon" href="/Pesan-Tiket-Bus/assets/img/icon/apple-touch-icon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="flex-1">
            <!-- Header -->
            <div class="bg-white border-b px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-2 text-sm mb-3">
                            <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                            <span class="text-gray-400">/</span>
                            <span class="text-indigo-600 font-medium">Pesanan Tiket Online</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800">Daftar Pesanan Tiket Online</h1>
                        <p class="text-sm text-gray-600 mt-1">Kelola semua pesanan tiket yang dipesan secara online</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Search Form -->
                        <form action="" method="GET" class="flex gap-2">
                            <?php if ($status_filter !== 'all'): ?>
                                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                            <?php endif; ?>
                            
                            <div class="relative">
                                <input type="text" name="search" 
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="Cari nama/kode tiket..."
                                       class="w-64 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <?php if ($search): ?>
                                    <a href="?<?= $status_filter !== 'all' ? 'status=' . $status_filter : '' ?>" 
                                       class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Cari
                            </button>
                        </form>

                        <!-- Status Filter -->
                        <select onchange="window.location.href='?status='+this.value<?= $search ? '&search='.urlencode($search) : '' ?>" 
                                class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm">
                            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="PENDING" <?= $status_filter == 'PENDING' ? 'selected' : '' ?>>Pending</option>
                            <option value="DIBAYAR" <?= $status_filter == 'DIBAYAR' ? 'selected' : '' ?>>Dibayar</option>
                            <option value="DIBATALKAN" <?= $status_filter == 'DIBATALKAN' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Tiket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Penumpang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rute</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jadwal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kursi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['kode_tiket']) ?></td>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['nama_penumpang']) ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?= htmlspecialchars($row['kota_asal']) ?> → <?= htmlspecialchars($row['kota_tujuan']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?= htmlspecialchars($row['nomor_unit']) ?> - <?= htmlspecialchars($row['nama_unit']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?= date('d/m/Y H:i', strtotime($row['waktu_berangkat'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['nomor_kursi']) ?></td>
                                    <td class="px-6 py-4 text-sm">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch($row['status_pembayaran']) {
                                                case 'DIBAYAR':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'PENDING':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'DIBATALKAN':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                            }
                                            ?>">
                                            <?= $row['status_pembayaran'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if($row['status_pembayaran'] === 'PENDING'): ?>
                                            <button onclick="confirmPayment(<?= $row['id'] ?>)" 
                                                    class="text-green-600 hover:text-green-900">
                                                Konfirmasi
                                            </button>
                                            <button onclick="cancelTicket(<?= $row['id'] ?>)" 
                                                    class="ml-3 text-red-600 hover:text-red-900">
                                                Batalkan
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="viewTicket(<?= $row['id'] ?>)"
                                                class="ml-3 text-indigo-600 hover:text-indigo-900">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmPayment(id) {
        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: "Tandai tiket ini sebagai sudah dibayar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Konfirmasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `confirm_payment.php?id=${id}`;
            }
        });
    }

    function cancelTicket(id) {
        Swal.fire({
            title: 'Batalkan Tiket?',
            text: "Tiket yang dibatalkan tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `cancel_ticket.php?id=${id}`;
            }
        });
    }

    function viewTicket(id) {
        window.location.href = `detail.php?id=${id}`;
    }
    </script>
</body>
</html>
