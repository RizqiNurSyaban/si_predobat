<?php
// login.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($identifier === '' || $password === '') {
        $error = 'Email/Username dan kata sandi wajib diisi.';
    } else {
        if (login_user($identifier, $password)) {
            redirect('index.php');
        } else {
            $error = 'Email/Username atau kata sandi tidak sesuai.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SI-PREDOBAT RSUD Sehat Sentosa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#14b8a6',
                            600: '#00a884',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#0b3b3c',
                            950: '#072729',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex overflow-hidden font-sans">
    <div class="flex-1 flex flex-col lg:flex-row h-full">
        <!-- Left Hero Banner -->
        <div class="relative hidden lg:flex lg:w-7/12 bg-gradient-to-br from-[#063336] via-[#09474b] to-[#042426] text-white p-12 lg:p-16 flex-col justify-between overflow-hidden">
            <!-- Background Decorative Curves/Rings -->
            <div class="absolute -top-32 -right-32 w-[600px] h-[600px] border border-teal-500/15 rounded-full pointer-events-none"></div>
            <div class="absolute -top-16 -right-16 w-[450px] h-[450px] border border-teal-500/10 rounded-full pointer-events-none"></div>
            <div class="absolute -bottom-40 -left-40 w-[550px] h-[550px] border border-teal-500/15 rounded-full pointer-events-none"></div>
            <div class="absolute inset-0 bg-radial-gradient from-teal-500/10 via-transparent to-transparent pointer-events-none"></div>

            <!-- Header Branding -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#00a884] flex items-center justify-center text-white shadow-lg shadow-teal-950/40">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-extrabold text-xl tracking-tight leading-none">SI-PREDOBAT</h1>
                    <p class="text-xs text-teal-200/80 font-medium mt-1">Sistem Informasi Prediksi Kebutuhan Obat</p>
                </div>
            </div>

            <!-- Center Headline -->
            <div class="relative z-10 my-auto max-w-xl py-12">
                <h2 class="text-3xl lg:text-[42px] font-extrabold leading-[1.2] tracking-tight text-white mb-6">
                    Rencanakan stok obat lebih akurat, hindari kekosongan dan kedaluwarsa.
                </h2>
                <p class="text-base text-teal-100/80 leading-relaxed font-normal">
                    Prediksi kebutuhan obat berbasis data pemakaian historis untuk membantu instalasi farmasi menyusun pengadaan yang tepat.
                </p>
            </div>

            <!-- Bottom 3 Metric Cards (as in screenshot 1) -->
            <div class="relative z-10 grid grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <p class="text-2xl font-bold text-white tracking-tight">92,4%</p>
                    <p class="text-xs text-teal-100/80 mt-1 font-medium">Akurasi model prediksi</p>
                </div>
                <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <p class="text-2xl font-bold text-white tracking-tight">10</p>
                    <p class="text-xs text-teal-100/80 mt-1 font-medium">Kategori obat terpantau</p>
                </div>
                <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                    <p class="text-2xl font-bold text-white tracking-tight">30 hari</p>
                    <p class="text-xs text-teal-100/80 mt-1 font-medium">Cakupan peringatan dini</p>
                </div>
            </div>
        </div>

        <!-- Right Login Form Area -->
        <div class="flex-1 bg-white flex flex-col justify-between p-8 sm:p-12 lg:p-16 overflow-y-auto">
            <div class="max-w-md w-full mx-auto my-auto py-8">
                <!-- Mobile brand logo (only visible on mobile) -->
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-[#00a884] flex items-center justify-center text-white">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    </div>
                    <span class="font-bold text-xl text-slate-800">SI-PREDOBAT</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Selamat datang</h2>
                    <p class="text-sm text-slate-500 mt-2">Masuk untuk mengakses dashboard instalasi farmasi.</p>
                </div>

                <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-5">
                    <div>
                        <label for="identifier" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Email / Username
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </span>
                            <input type="text" 
                                   id="identifier" 
                                   name="identifier" 
                                   value="<?= htmlspecialchars($_POST['identifier'] ?? 'anisa.fitri@rsud-sehat.id') ?>"
                                   required 
                                   placeholder="anisa.fitri@rsud-sehat.id" 
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kata sandi
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   value="password123"
                                   required 
                                   placeholder="••••••••" 
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300">
                            <span>Ingat saya</span>
                        </label>
                        <a href="javascript:void(0)" onclick="alert('Untuk demonstrasi, silakan gunakan akun yang telah disediakan.')" class="font-medium text-teal-600 hover:text-teal-700">
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold text-sm shadow-md shadow-teal-900/10 hover:shadow-lg transition-all duration-200">
                        Masuk
                    </button>
                </form>

                <!-- Demo Account Switcher for Evaluators -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <p class="text-xs font-medium text-slate-500 mb-3 text-center">Akun Demo Cepat (Klik untuk isi otomatis):</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="fillLogin('anisa.fitri@rsud-sehat.id', 'password123')" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-left transition-colors">
                            <p class="font-semibold text-slate-800">Apt. Anisa Fitri</p>
                            <p class="text-[11px] text-slate-500">Apoteker PJ</p>
                        </button>
                        <button type="button" onclick="fillLogin('budi.santoso@rsud-sehat.id', 'password123')" class="p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-left transition-colors">
                            <p class="font-semibold text-slate-800">Budi Santoso</p>
                            <p class="text-[11px] text-slate-500">Asisten Apoteker</p>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer (from screenshot 1) -->
            <div class="text-center pt-8 border-t border-slate-100/80">
                <p class="text-xs text-slate-400">
                    © 2026 SI-PREDOBAT · Instalasi Farmasi RSUD Sehat Sentosa
                </p>
            </div>
        </div>
    </div>

    <script>
        function fillLogin(email, pass) {
            document.getElementById('identifier').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>

