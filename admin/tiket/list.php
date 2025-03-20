<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify query to show available tickets from jadwal
$query = "SELECT j.*, r.kota_asal, r.kota_tujuan, r.harga_tiket,
          u.nomor_unit, u.nama_unit, u.kapasitas,
          (SELECT COUNT(*) FROM tiket t WHERE t.jadwal_id = j.id AND t.status_pembayaran != 'DIBATALKAN') as tiket_terjual
          FROM jadwal j
          JOIN route r ON j.route_id = r.id 
          JOIN unit u ON j.unit_id = u.id
          WHERE j.waktu_berangkat >= CURDATE()
          AND j.status = 'AKTIF'
          ORDER BY j.waktu_berangkat ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tiket - Admin Panel</title>
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
                            <a href="../dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                            <span class="text-gray-400">/</span>
                            <span class="text-indigo-600 font-medium">Tiket</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800">Daftar Tiket</h1>
                        <p class="text-sm text-gray-600 mt-1">Kelola semua tiket yang telah dipesan</p>
                    </div>

                    <!-- Search and Filter -->
                    <div class="flex items-center gap-4">
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Cari kode/nama..."
                                   class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <select name="status" 
                                    onchange="this.form.submit()"
                                    class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua Status</option>
                                <option value="PENDING" <?= $status_filter == 'PENDING' ? 'selected' : '' ?>>Pending</option>
                                <option value="DIBAYAR" <?= $status_filter == 'DIBAYAR' ? 'selected' : '' ?>>Dibayar</option>
                                <option value="DIBATALKAN" <?= $status_filter == 'DIBATALKAN' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                Cari
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rute</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Bus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Berangkat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sisa Kursi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($tickets as $ticket): 
                                $sisa_kursi = $ticket['kapasitas'] - $ticket['tiket_terjual'];
                            ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($ticket['kota_asal']) ?> → 
                                    <?= htmlspecialchars($ticket['kota_tujuan']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($ticket['nomor_unit']) ?> - 
                                    <?= htmlspecialchars($ticket['nama_unit']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= date('d/m/Y H:i', strtotime($ticket['waktu_berangkat'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    Rp <?= number_format($ticket['harga_tiket'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?= $sisa_kursi > 10 ? 'bg-green-100 text-green-800' : 
                                           ($sisa_kursi > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= $sisa_kursi ?> kursi
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?= $ticket['status'] == 'AKTIF' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $ticket['status'] ?>
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

    <script>
    function confirmTicket(id) {
        Swal.fire({
            title: 'Konfirmasi Tiket',
            text: "Apakah anda yakin ingin mengkonfirmasi tiket ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Konfirmasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `confirm.php?id=${id}`;
            }
        });
    }

    function cancelTicket(id) {
        Swal.fire({
            title: 'Batalkan Tiket',
            text: "Tiket yang dibatalkan tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `cancel.php?id=${id}`;
            }
        });
    }
    </script>
</body>
</html>
