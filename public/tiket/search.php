<?php
require_once '../../config/database.php';
session_start();

if (empty($_GET['from']) || empty($_GET['to'])) {
    header('Location: ../index.php');
    exit();
}

$from = $_GET['from'];
$to = $_GET['to'];

// Query untuk mengambil jadwal yang tersedia dari list.php
$query = "SELECT j.*, r.kota_asal, r.kota_tujuan, r.harga_tiket,
          u.nomor_unit, u.nama_unit, u.jenis_unit, u.kapasitas,
          (SELECT COUNT(*) FROM tiket t WHERE t.jadwal_id = j.id AND t.status_pembayaran != 'DIBATALKAN') as tiket_terjual
          FROM jadwal j
          JOIN route r ON j.route_id = r.id 
          JOIN unit u ON j.unit_id = u.id
          WHERE r.kota_asal = ? 
          AND r.kota_tujuan = ?
          AND j.waktu_berangkat >= CURDATE()
          AND j.status = 'AKTIF'
          ORDER BY j.waktu_berangkat ASC";

$stmt = $conn->prepare($query);
$stmt->execute([$from, $to]);
$jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Tiket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 pt-20">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Hasil Pencarian</h2>
            <div class="flex items-center gap-2 text-gray-600">
                <span class="font-medium"><?= htmlspecialchars($from) ?></span>
                <i class="fas fa-arrow-right text-sm"></i>
                <span class="font-medium"><?= htmlspecialchars($to) ?></span>
            </div>
        </div>

        <?php if (empty($jadwal)): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Tidak ada jadwal tersedia untuk rute ini saat ini.
                            <a href="../index.php" class="font-medium underline">Coba rute lain</a>
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($jadwal as $j): 
                    $sisa_kursi = $j['kapasitas'] - $j['tiket_terjual'];
                ?>
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-lg font-semibold">
                                <i class="fas fa-bus text-blue-600 mr-2"></i>
                                <?= htmlspecialchars($j['nomor_unit']) ?> - <?= htmlspecialchars($j['nama_unit']) ?>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="px-2 py-1 rounded-full text-xs
                                    <?= $j['jenis_unit'] === 'EKSEKUTIF' ? 'bg-purple-100 text-purple-800' : 
                                       ($j['jenis_unit'] === 'BISNIS' ? 'bg-blue-100 text-blue-800' : 
                                        'bg-gray-100 text-gray-800') ?>">
                                    <?= $j['jenis_unit'] ?>
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600">
                                Rp <?= number_format($j['harga_tiket'], 0, ',', '.') ?>
                            </div>
                            <div class="text-sm text-gray-500">per tiket</div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-4">
                        <div class="flex gap-8">
                            <div>
                                <div class="text-sm font-medium text-gray-600">Keberangkatan</div>
                                <div class="font-semibold text-lg"><?= date('H:i', strtotime($j['waktu_berangkat'])) ?></div>
                                <div class="text-sm text-gray-500"><?= date('d M Y', strtotime($j['waktu_berangkat'])) ?></div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-600">Kedatangan</div>
                                <div class="font-semibold text-lg"><?= date('H:i', strtotime($j['waktu_tiba'])) ?></div>
                                <div class="text-sm text-gray-500"><?= date('d M Y', strtotime($j['waktu_tiba'])) ?></div>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="mb-2">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    <?= $sisa_kursi > 10 ? 'bg-green-100 text-green-800' : 
                                       ($sisa_kursi > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= $sisa_kursi ?> kursi tersedia
                                </span>
                            </div>
                            <?php if ($sisa_kursi > 0): ?>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <a href="pilih-kursi.php?jadwal_id=<?= $j['id'] ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" 
                                       class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Pilih Kursi
                                    </a>
                                <?php else: ?>
                                    <a href="../auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                                       class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Login untuk Pesan
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button disabled class="px-6 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                    Tiket Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
