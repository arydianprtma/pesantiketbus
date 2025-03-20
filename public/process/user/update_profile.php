<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

function validateImage($file) {
    $errors = [];
    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Cek apakah ada error pada upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Ukuran file terlalu besar';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'File hanya terupload sebagian';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Tidak ada file yang dipilih';
                break;
            default:
                $errors[] = 'Terjadi kesalahan saat upload';
        }
        return $errors;
    }

    // Validasi tipe MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed)) {
        $errors[] = "Format file harus JPG atau PNG";
    }

    // Validasi ukuran
    if ($file['size'] > $maxSize) {
        $errors[] = "Ukuran file maksimal 2MB";
    }

    // Validasi dimensi
    list($width, $height) = getimagesize($file['tmp_name']);
    if ($width < 100 || $height < 100) {
        $errors[] = "Dimensi gambar minimal 100x100 pixel";
    }
    
    return $errors;
}

try {
    // Buat direktori jika belum ada
    $uploadDir = '../../assets/images/profile/';
    $relativePath = 'assets/images/profile/'; // Path untuk database
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle foto profile terlebih dahulu
    if (isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors = validateImage($_FILES['foto_profile']);
        
        if (empty($errors)) {
            // Generate nama file yang aman
            $fileExt = strtolower(pathinfo($_FILES['foto_profile']['name'], PATHINFO_EXTENSION));
            $fileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExt;
            $targetFile = $uploadDir . $fileName;
            
            // Hapus foto lama
            $stmt = $conn->prepare("SELECT foto_profile FROM user_account WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $oldPhoto = $stmt->fetchColumn();
            
            if ($oldPhoto && file_exists('../../' . $oldPhoto)) {
                unlink('../../' . $oldPhoto);
            }

            // Upload foto baru
            if (!move_uploaded_file($_FILES['foto_profile']['tmp_name'], $targetFile)) {
                throw new Exception("Gagal mengupload foto");
            }

            $newPhotoPath = $relativePath . $fileName;
            
            // Update foto di database
            $stmt = $conn->prepare("UPDATE user_account SET foto_profile = ? WHERE id = ?");
            if (!$stmt->execute([$newPhotoPath, $_SESSION['user_id']])) {
                throw new Exception("Gagal memperbarui foto profil");
            }

            // Set session untuk update UI
            $_SESSION['new_photo'] = $newPhotoPath;
        } else {
            throw new Exception(implode(", ", $errors));
        }
    }

    // Validasi dan format tanggal lahir
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
    
    // Update data profil dasar
    $stmt = $conn->prepare("UPDATE user_account SET 
        nama_lengkap = ?,
        no_hp = ?,
        tanggal_lahir = ?,
        jenis_kelamin = ?,
        alamat = ?
        WHERE id = ?");
        
    $stmt->execute([
        $_POST['nama_lengkap'],
        $_POST['no_hp'],
        $tanggal_lahir, // Menggunakan variabel yang sudah divalidasi
        $_POST['jenis_kelamin'],
        $_POST['alamat'],
        $_SESSION['user_id']
    ]);

    // Update session jika nama berubah
    $_SESSION['user_name'] = $_POST['nama_lengkap'];
    
    header('Location: ../../user/profile.php?success=true');
} catch (Exception $e) {
    header('Location: ../../user/profile.php?error=' . urlencode($e->getMessage()));
}
