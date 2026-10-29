<?php
require_once 'config/db.php';
ini_set("session.cookie_lifetime", 2592000);
ini_set("session.gc_maxlifetime", 2592000);
ini_set("session.cookie_lifetime", 2592000);
ini_set("session.gc_maxlifetime", 2592000);
session_set_cookie_params([
    "lifetime" => 2592000,
    "path" => "/",
    "domain" => ".dirasah.my.id",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Lax"
]);
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$v = time();
$fav_v = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
$remembered_email = $_COOKIE['dirasah_remember_email'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            setcookie('dirasah_remember_email', $email, time() + (86400 * 30), "/");
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama_lengkap'];
            $_SESSION['role']    = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau kata sandi tidak cocok.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - Dirasah.id</title>
    <!-- Favicon Tab Browser (putih.png) -->
    <link rel="icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="apple-touch-icon" href="assets/putih.png?v=<?= $fav_v; ?>">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body, input, textarea, button, select, p, span, h1, h2, h3, h4, h5, h6, a, div {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 400 !important;
        }
        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }
        a, button:not(#toggle-eye-btn) {
            transition: none !important;
            transform: none !important;
        }
    </style>
<link rel="manifest" href="/manifest.json">
<script>
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("/sw.js");
  });
}
</script>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Dirasah">
<link rel="apple-touch-icon" href="/assets/img/icon-192.png">

<meta name="author" content="Shahzada Rakhshaa Aprihansyah">
<meta name="creator" content="Shahzada Rakhshaa Aprihansyah">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Dirasah",
  "url": "https://dirasah.my.id",
  "creator": {
    "@type": "Person",
    "name": "Shahzada Rakhshaa Aprihansyah",
    "url": "https://github.com/srakss255-prog"
  }
}
</script>
</head>
<body class="bg-white text-slate-900 min-h-screen lg:h-screen overflow-x-hidden overflow-y-auto lg:overflow-hidden flex flex-col antialiased selection:bg-blue-100 selection:text-blue-900 font-normal">

    <!-- Header / Navbar -->
    <header class="w-full bg-white border-b border-slate-200 shrink-0 z-40 font-normal">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-16 flex items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-2 sm:gap-4 flex-1">
                <a href="index.php" class="flex items-center gap-1 sm:gap-1.5 text-xs sm:text-base font-normal text-[#1865f2] py-2">
                    <i class="fa-solid fa-arrow-left text-xs mr-1"></i>
                    <span>Beranda</span>
                </a>
            </div>

            <!-- Brand Logo Persis Tipisnya dengan index.php -->
            <div id="brand-logo" class="flex items-center justify-center">
                <a href="index.php" class="flex items-center gap-2 sm:gap-2.5">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center text-[#182335]">
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <path d="M52 8C48 9.5 45 13.5 45 18C45 23.5 49.5 28 55 28C57.5 28 59.8 27.1 61.5 25.5C59.5 29.5 55 32 50 32C42.8 32 37 26.2 37 19C37 13.2 40.8 8.3 46 6.5C47.8 5.8 50 6.5 52 8Z" fill="#182335"/>
                            <polygon points="62,13 63.5,16 66.5,16.5 64.3,18.5 64.9,21.5 62,20 59.1,21.5 59.7,18.5 57.5,16.5 60.5,16" fill="#182335"/>
                            <path d="M14 41L11 68L22 75" stroke="#182335" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M86 41L89 68L78 75" stroke="#182335" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 37L15 65L25 72" stroke="#182335" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M83 37L85 65L75 72" stroke="#182335" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M50 42C36 30 22 30 19 33V63C22 60 36 60 50 72C64 60 78 60 81 63V33C78 30 64 30 50 42Z" stroke="#182335" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M50 42V72" stroke="#182335" stroke-width="5" stroke-linecap="round"/>
                            <path d="M43 73.5C43 78 57 78 57 73.5" stroke="#182335" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M10 74C25 68 38 68 47 74" stroke="#182335" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M90 74C75 68 62 68 53 74" stroke="#182335" stroke-width="4.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-lg sm:text-[1.45rem] font-normal tracking-[-0.015em] text-[#182335]">
                        Dirasah.id
                    </span>
                </a>
            </div>

            <div class="flex items-center justify-end gap-3 sm:gap-6 flex-1">
                <a href="index.php" class="bg-[#1865f2] text-white px-3.5 sm:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-base font-normal shadow-sm">
                    Daftar
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-8 py-5 sm:py-8 lg:py-0 flex-1 flex items-center justify-center font-normal">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 w-full items-center justify-items-center">
            
            <!-- Kolom Kiri: Slider Visual (Identik dengan index.php) -->
            <div class="w-full max-w-lg lg:max-w-none lg:col-span-7 relative h-[360px] sm:h-[480px] lg:h-[600px] rounded-md overflow-hidden shadow-md bg-slate-950 mx-auto font-normal">
                
                <!-- Slide 1 -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-100 flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik1.png?v=<?= $v; ?>" alt="Slide 1" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-xl sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Masuk kembali ke ruang belajar dan kajian Anda.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Lanjutkan tadarus, penelusuran sanad riwayat, dan akses silabus materi yang tersimpan di akun Anda.
                        </p>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik2.png?v=<?= $v; ?>" alt="Slide 2" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-xl sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Akses materi terpadu dari mana saja.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Tersedia rekaman hafalan, catatan mutaba'ah, dan referensi kitab digital langsung di satu dasbor.
                        </p>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik3.png?v=<?= $v; ?>" alt="Slide 3" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-xl sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Terhubung bersama komunitas pembelajar.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Diskusikan matan hadits dan syarah kitab bersama ustadz serta sesama penuntut ilmu syar'i.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan: Langkah Masuk -->
            <div class="w-full max-w-lg lg:max-w-md lg:col-span-5 flex flex-col justify-center space-y-3.5 pb-8 lg:pb-0 mx-auto font-normal">
                
                <div>
                    <a href="index.php" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-normal text-[#1865f2] mb-1">
                        <i class="fa-solid fa-chevron-left text-[11px]"></i>
                        <span>Kembali</span>
                    </a>
                    <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-normal">
                        Masuk ke Dirasah.id hari ini!
                    </h2>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="p-2.5 text-xs bg-red-50 text-red-600 rounded-md border border-red-200 flex items-center gap-2 font-normal">
                        <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                        <span><?= $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="space-y-3.5 font-normal">
                    
                    <!-- Tombol Lanjutkan dengan Google Asli -->
                    <a 
                        href="auth_google.php" 
                        class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white border border-slate-200 rounded-md font-normal text-slate-800 text-xs sm:text-sm shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Lanjutkan dengan Google</span>
                    </a>

                    <!-- Tombol Lanjutkan dengan Facebook Asli -->
                    <a 
                        href="auth_facebook.php" 
                        class="w-full flex items-center justify-center gap-3 py-2.5 px-4 bg-white border border-slate-200 rounded-md font-normal text-slate-800 text-xs sm:text-sm shadow-sm cursor-pointer"
                    >
                        <i class="fa-brands fa-facebook text-[#1877F2] text-lg"></i>
                        <span>Lanjutkan dengan Facebook</span>
                    </a>

                    <!-- Garis Pemisah "Atau masuk menggunakan email" -->
                    <div class="relative flex items-center justify-center py-1 font-normal">
                        <div class="border-t border-slate-200 w-full"></div>
                        <span class="bg-white px-3 text-xs text-slate-500 whitespace-nowrap font-normal">
                            Atau masuk menggunakan email
                        </span>
                    </div>

                    <!-- Form Masuk Manual -->
                    <form method="POST" action="" class="space-y-3.5 font-normal">
                        <!-- Input Email Pengguna -->
                        <div class="space-y-1 font-normal">
                            <div class="flex items-center justify-between text-xs font-normal">
                                <label class="text-slate-800" for="email">Email Pengguna</label>
                                <span class="text-slate-400 text-[11px]">wajib</span>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                value="<?= htmlspecialchars($remembered_email); ?>"
                                placeholder="Alamat email Anda"
                                class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-md border border-slate-200 outline-none focus:border-[#1865f2] text-slate-900 bg-white font-normal"
                            >
                        </div>

                        <!-- Input Kata Sandi dengan Posisi Presisi di Tengah -->
                        <div class="space-y-1 font-normal">
                            <div class="flex items-center justify-between text-xs font-normal">
                                <label class="text-slate-800" for="password">Kata sandi</label>
                                <span class="text-slate-400 text-[11px]">wajib</span>
                            </div>
                            <div class="relative flex items-center">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="Masukkan kata sandi akun Anda"
                                    class="w-full pl-3.5 pr-11 py-2 text-xs sm:text-sm rounded-md border border-slate-200 outline-none focus:border-[#1865f2] text-slate-900 bg-white font-normal"
                                >
                                <button 
                                    type="button" 
                                    id="toggle-eye-btn"
                                    onclick="togglePassword('password', 'eye-btn')" 
                                    class="absolute inset-y-0 right-0 px-3.5 flex items-center justify-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                                    title="Tampilkan / Sembunyikan Sandi"
                                >
                                    <i id="eye-btn" class="fa-regular fa-eye text-sm leading-none"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Masuk -->
                        <button 
                            type="submit" 
                            class="w-full py-2.5 sm:py-3 px-4 rounded-md font-normal text-white text-xs sm:text-sm bg-[#1865f2] shadow-sm cursor-pointer"
                        >
                            Masuk
                        </button>
                    </form>

                </div>

                <!-- Footer Form -->
                <div class="pt-1 text-xs text-slate-600 text-left font-normal">
                    Belum punya akun Dirasah.id? 
                    <a href="daftar_step2.php?role=pelajar" class="text-[#1865f2] font-normal ml-1">
                        Daftar
                    </a>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white border-t border-slate-200 py-3.5 text-center text-xs text-slate-500 shrink-0 mt-auto font-normal">
        <p>&copy; 2026 Dirasah.id &mdash; Platform Solusi Interkoneksi Studi Islam</p>
    </footer>

    <script>
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;

        function showNextSlide() {
            if (slides.length <= 1) return;
            slides[currentSlide].classList.remove('opacity-100');
            slides[currentSlide].classList.add('opacity-0', 'pointer-events-none');

            currentSlide = (currentSlide + 1) % slides.length;

            slides[currentSlide].classList.remove('opacity-0', 'pointer-events-none');
            slides[currentSlide].classList.add('opacity-100');
        }
        if (slides.length > 1) {
            setInterval(showNextSlide, 4500);
        }

        function togglePassword(inputId, iconId) {
            const field = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!field || !icon) return;
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-sm leading-none';
            } else {
                field.type = 'password';
                icon.className = 'fa-regular fa-eye text-sm leading-none';
            }
        }
    </script>
</body>
</html>