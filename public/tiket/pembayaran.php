<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || empty($_GET['kode'])) {
    header('Location: ../index.php');
    exit();
}

$kode_pembayaran = $_GET['kode'];

// Query untuk mendapatkan detail pembayaran
$query = "SELECT p.*, 
          GROUP_CONCAT(t.kode_tiket) as kode_tiket,
          GROUP_CONCAT(t.nama_penumpang) as nama_penumpang,
          GROUP_CONCAT(t.nomor_kursi) as nomor_kursi,
          j.waktu_berangkat,
          r.kota_asal, r.kota_tujuan,
          u.nama_unit, u.nomor_unit
          FROM pembayaran p
          JOIN tiket t ON t.pelanggan_id = p.pelanggan_id AND t.status_pembayaran = p.status_pembayaran
          JOIN jadwal j ON t.jadwal_id = j.id
          JOIN route r ON j.route_id = r.id
          JOIN unit u ON j.unit_id = u.id
          WHERE p.kode_pembayaran = ? AND p.pelanggan_id = ?
          GROUP BY p.id";

$stmt = $conn->prepare($query);
$stmt->execute([$kode_pembayaran, $_SESSION['user_id']]);
$pembayaran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pembayaran) {
    header('Location: ../index.php');
    exit();
}

// Konversi string hasil GROUP_CONCAT menjadi array
$kode_tiket_array = explode(',', $pembayaran['kode_tiket']);
$nama_penumpang_array = explode(',', $pembayaran['nama_penumpang']);
$nomor_kursi_array = explode(',', $pembayaran['nomor_kursi']);

// Array informasi rekening untuk pembayaran transfer
$rekening_info = [
    'TRANSFER' => [
        ['bank' => 'BCA', 'nomor' => '1234567890', 'nama' => 'PT BUS TICKET'],
        ['bank' => 'BRI', 'nomor' => '0987654321', 'nama' => 'PT BUS TICKET'],
    ],
    'VIRTUAL_ACCOUNT' => [
        'nomor' => $kode_pembayaran,
        'bank' => 'BCA Virtual Account'
    ],
    'QRIS' => [
        'image' => 'assets/images/qris-code.png'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?= $kode_pembayaran ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 pt-20">
        <div class="max-w-4xl mx-auto">
            <!-- Status Pembayaran -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Detail Pembayaran</h2>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        <?php
                        switch($pembayaran['status_pembayaran']) {
                            case 'PENDING':
                                echo 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'DIBAYAR':
                                echo 'bg-green-100 text-green-800';
                                break;
                            default:
                                echo 'bg-red-100 text-red-800';
                        }
                        ?>">
                        <?= $pembayaran['status_pembayaran'] ?>
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kode Pembayaran:</span>
                        <span class="font-semibold"><?= $kode_pembayaran ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Pembayaran:</span>
                        <span class="font-semibold">Rp <?= number_format($pembayaran['total_pembayaran'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Batas Waktu:</span>
                        <span class="font-semibold"><?= date('d M Y H:i', strtotime($pembayaran['waktu_pembayaran'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Informasi Pembayaran -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Cara Pembayaran</h3>
                <?php if ($pembayaran['metode_pembayaran'] == 'TRANSFER'): ?>
                    <?php foreach ($rekening_info['TRANSFER'] as $rek): ?>
                        <div class="mb-4 p-4 border rounded-lg">
                            <div class="font-semibold mb-2"><?= $rek['bank'] ?></div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-gray-600">Nomor Rekening:</div>
                                    <div class="font-mono text-lg"><?= $rek['nomor'] ?></div>
                                    <div class="text-sm text-gray-500">a.n. <?= $rek['nama'] ?></div>
                                </div>
                                <button onclick="copyToClipboard('<?= $rek['nomor'] ?>')" 
                                        class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($pembayaran['status_pembayaran'] == 'PENDING'): ?>
                        <form action="upload-bukti.php" method="POST" enctype="multipart/form-data" class="mt-6">
                            <input type="hidden" name="kode_pembayaran" value="<?= $kode_pembayaran ?>">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Bukti Pembayaran
                                </label>
                                <input type="file" name="bukti" accept="image/*" required
                                       class="w-full p-2 border rounded-lg">
                            </div>
                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                                Upload Bukti Pembayaran
                            </button>
                        </form>
                    <?php endif; ?>

                <?php elseif ($pembayaran['metode_pembayaran'] == 'VIRTUAL_ACCOUNT'): ?>
                    <div class="p-4 border rounded-lg">
                        <div class="font-semibold mb-2"><?= $rekening_info['VIRTUAL_ACCOUNT']['bank'] ?></div>
                        <div class="flex justify-between items-center">
                            <div class="font-mono text-lg"><?= $rekening_info['VIRTUAL_ACCOUNT']['nomor'] ?></div>
                            <button onclick="copyToClipboard('<?= $rekening_info['VIRTUAL_ACCOUNT']['nomor'] ?>')" 
                                    class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                <?php elseif ($pembayaran['metode_pembayaran'] == 'QRIS'): ?>
                    <div class="text-center">
                        <img src="<?= $rekening_info['QRIS']['image'] ?>" 
                             alt="QRIS Code" 
                             class="mx-auto max-w-xs">
                        <p class="mt-4 text-gray-600">Scan QRIS code di atas menggunakan aplikasi e-wallet Anda</p>
                    </div>

                <?php else: ?>
                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <p class="text-yellow-800">
                            Silahkan melakukan pembayaran di loket terdekat.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detail Tiket -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Detail Perjalanan</h3>
                <div class="space-y-4">
                    <div>
                        <div class="text-gray-600">Bus:</div>
                        <div class="font-semibold"><?= $pembayaran['nomor_unit'] ?> - <?= $pembayaran['nama_unit'] ?></div>
                    </div>
                    <div>
                        <div class="text-gray-600">Rute:</div>
                        <div class="font-semibold"><?= $pembayaran['kota_asal'] ?> → <?= $pembayaran['kota_tujuan'] ?></div>
                    </div>
                    <div>
                        <div class="text-gray-600">Waktu Keberangkatan:</div>
                        <div class="font-semibold"><?= date('d M Y H:i', strtotime($pembayaran['waktu_berangkat'])) ?></div>
                    </div>
                    
                    <div class="border-t pt-4 mt-4">
                        <div class="text-gray-600 mb-2">Detail Penumpang:</div>
                        <div class="space-y-2">
                            <?php foreach($nama_penumpang_array as $i => $nama): ?>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-semibold"><?= $nama ?></div>
                                        <div class="text-sm text-gray-500">Kursi <?= $nomor_kursi_array[$i] ?></div>
                                    </div>
                                    <div class="text-sm text-gray-500"><?= $kode_tiket_array[$i] ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Tersalin ke clipboard!');
            });
        }
    </script>
</body>
</html>
