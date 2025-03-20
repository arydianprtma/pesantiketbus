<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (empty($_GET['jadwal_id'])) {
    header('Location: ../index.php');
    exit();
}

$jadwal_id = $_GET['jadwal_id'];

// Query untuk mendapatkan detail jadwal
$query = "SELECT j.*, r.kota_asal, r.kota_tujuan, r.harga_tiket,
          u.nomor_unit, u.nama_unit, u.jenis_unit, u.kapasitas
          FROM jadwal j
          JOIN route r ON j.route_id = r.id 
          JOIN unit u ON j.unit_id = u.id
          WHERE j.id = ?";

$stmt = $conn->prepare($query);
$stmt->execute([$jadwal_id]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jadwal) {
    header('Location: ../index.php');
    exit();
}

// Query untuk mendapatkan kursi yang sudah dipesan
$query_kursi = "SELECT nomor_kursi FROM tiket WHERE jadwal_id = ? AND status_pembayaran != 'DIBATALKAN'";
$stmt = $conn->prepare($query_kursi);
$stmt->execute([$jadwal_id]);
$kursi_terpesan = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi - <?= htmlspecialchars($jadwal['nama_unit']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 pt-20">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Pilih Kursi</h2>
            
            <!-- Detail Bus -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-2">Detail Perjalanan</h3>
                        <div class="text-gray-600 space-y-2">
                            <p><i class="fas fa-bus mr-2"></i><?= htmlspecialchars($jadwal['nomor_unit']) ?> - <?= htmlspecialchars($jadwal['nama_unit']) ?></p>
                            <p><i class="fas fa-route mr-2"></i><?= htmlspecialchars($jadwal['kota_asal']) ?> → <?= htmlspecialchars($jadwal['kota_tujuan']) ?></p>
                            <p><i class="fas fa-clock mr-2"></i><?= date('d M Y H:i', strtotime($jadwal['waktu_berangkat'])) ?></p>
                            <p><i class="fas fa-money-bill mr-2"></i>Rp <?= number_format($jadwal['harga_tiket'], 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <!-- Denah Kursi -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-4">Pilih Kursi (Maksimal 4)</h3>
                        <div class="grid grid-cols-4 gap-2">
                            <?php for($i = 1; $i <= $jadwal['kapasitas']; $i++): ?>
                                <button 
                                    data-kursi="<?= $i ?>"
                                    class="seat-btn p-3 rounded <?= in_array($i, $kursi_terpesan) ? 'bg-red-100 text-red-800 cursor-not-allowed' : 'bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer' ?>"
                                    <?= in_array($i, $kursi_terpesan) ? 'disabled' : '' ?>>
                                    <?= $i ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <div class="mt-4 flex gap-4 text-sm">
                            <div><span class="inline-block w-4 h-4 bg-green-100 rounded mr-2"></span>Tersedia</div>
                            <div><span class="inline-block w-4 h-4 bg-blue-500 rounded mr-2"></span>Dipilih</div>
                            <div><span class="inline-block w-4 h-4 bg-red-100 rounded mr-2"></span>Terpesan</div>
                        </div>
                    </div>
                </div>

                <!-- Form Pemesanan -->
                <div>
                    <form action="proses-pembayaran.php" method="POST" id="formPesan">
                        <input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">
                        
                        <div id="passenger-forms" class="space-y-4">
                            <!-- Passenger forms will be added here dynamically -->
                        </div>

                        <div class="mt-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Metode Pembayaran</label>
                            <select name="metode_pembayaran" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500">
                                <option value="">Pilih metode pembayaran</option>
                                <option value="TRANSFER">Transfer Bank</option>
                                <option value="VIRTUAL_ACCOUNT">Virtual Account</option>
                                <option value="QRIS">QRIS</option>
                                <option value="CASH">Tunai</option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <div class="flex justify-between mb-2">
                                <span>Total Kursi:</span>
                                <span id="total-kursi">0</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total Pembayaran:</span>
                                <span id="total-pembayaran">Rp 0</span>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full mt-6 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                id="btnPesan"
                                disabled>
                            Lanjutkan ke Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const selectedSeats = new Set();
        const maxSeats = 4;
        const hargaTiket = <?= $jadwal['harga_tiket'] ?>;

        document.querySelectorAll('.seat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.disabled) return;
                
                const seatNumber = this.dataset.kursi;
                
                if (selectedSeats.has(seatNumber)) {
                    selectedSeats.delete(seatNumber);
                    this.classList.remove('bg-blue-500', 'text-white');
                    this.classList.add('bg-green-100', 'text-green-800');
                } else if (selectedSeats.size < maxSeats) {
                    selectedSeats.add(seatNumber);
                    this.classList.remove('bg-green-100', 'text-green-800');
                    this.classList.add('bg-blue-500', 'text-white');
                }

                updatePassengerForms();
                updateTotalPembayaran();
            });
        });

        function updatePassengerForms() {
            const container = document.getElementById('passenger-forms');
            container.innerHTML = '';
            
            selectedSeats.forEach(seat => {
                container.innerHTML += `
                    <div class="p-4 border rounded-lg">
                        <div class="font-medium mb-2">Kursi #${seat}</div>
                        <input type="hidden" name="kursi[]" value="${seat}">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Penumpang</label>
                                <input type="text" name="nama_penumpang[]" required
                                       class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIK (16 digit)</label>
                                <input type="text" name="nik[]" required 
                                       minlength="16" maxlength="16" 
                                       pattern="[0-9]{16}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)"
                                       class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-blue-500"
                                       placeholder="Masukkan 16 digit NIK">
                                <p class="mt-1 text-xs text-gray-500">NIK harus 16 digit angka</p>
                            </div>
                        </div>
                    </div>
                `;
            });

            document.getElementById('btnPesan').disabled = selectedSeats.size === 0;
            document.getElementById('total-kursi').textContent = selectedSeats.size;
        }

        function updateTotalPembayaran() {
            const total = selectedSeats.size * hargaTiket;
            document.getElementById('total-pembayaran').textContent = 
                `Rp ${total.toLocaleString('id-ID')}`;
        }
    </script>
</body>
</html>