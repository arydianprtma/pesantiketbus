<?php
session_start();
require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bus Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Bus Ticket</h1>
                <p class="text-gray-600">Create your account</p>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md">
                <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <p class="font-bold">Registration failed</p>
                    <p><?= $_GET['error'] ?></p>
                </div>
                <?php endif; ?>

                <form action="../process/auth/register.php" method="POST" class="space-y-4" onsubmit="return validateForm()">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="nama_lengkap" required 
                                   placeholder="Masukkan nama lengkap anda"
                                   class="pl-10 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" required
                                   placeholder="email@example.com"
                                   class="pl-10 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. HP</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="tel" name="no_hp" required pattern="[0-9]{10,13}"
                                   placeholder="08xxxxxxxxxx"
                                   class="pl-10 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIK</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                            <input type="text" name="nik" required pattern="[0-9]{16}"
                                   placeholder="Masukkan 16 digit NIK"
                                   class="pl-10 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" required minlength="8"
                                   placeholder="Minimal 8 karakter"
                                   onkeyup="checkPassword()"
                                   class="pl-10 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 cursor-pointer" onclick="togglePassword('password')"></i>
                            </div>
                        </div>
                        <div id="password-strength" class="mt-1 text-xs"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                                   placeholder="Ulangi password anda"
                                   onkeyup="checkPassword()"
                                   class="pl-10 block w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 cursor-pointer" onclick="togglePassword('password_confirmation')"></i>
                            </div>
                        </div>
                        <div id="password-match" class="mt-1 text-xs"></div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </button>
                    </div>
                </form>

                <p class="mt-4 text-center text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Login disini
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        
        if(password !== confirmPassword) {
            alert('Password tidak sama!');
            return false;
        }
        
        if(password.length < 8) {
            alert('Password minimal 8 karakter!');
            return false;
        }
        
        return true;
    }

    function checkPassword() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        const strengthDiv = document.getElementById('password-strength');
        const matchDiv = document.getElementById('password-match');
        
        // Check password strength
        let strength = 0;
        if(password.match(/[a-z]/)) strength++;
        if(password.match(/[A-Z]/)) strength++;
        if(password.match(/[0-9]/)) strength++;
        if(password.match(/[^a-zA-Z0-9]/)) strength++;
        
        if(password.length > 0) {
            if(strength < 2) {
                strengthDiv.innerHTML = '<span class="text-red-500">Password lemah</span>';
            } else if(strength < 3) {
                strengthDiv.innerHTML = '<span class="text-yellow-500">Password sedang</span>';
            } else {
                strengthDiv.innerHTML = '<span class="text-green-500">Password kuat</span>';
            }
        } else {
            strengthDiv.innerHTML = '';
        }
        
        // Check password match
        if(confirmPassword.length > 0) {
            if(password === confirmPassword) {
                matchDiv.innerHTML = '<span class="text-green-500">Password cocok</span>';
            } else {
                matchDiv.innerHTML = '<span class="text-red-500">Password tidak cocok</span>';
            }
        } else {
            matchDiv.innerHTML = '';
        }
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        if(input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }
    </script>
</body>
</html>
