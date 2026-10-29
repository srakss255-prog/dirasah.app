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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pilih Peran Akun - Dirasah.id</title>
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
<body class="bg-white text-slate-900 min-h-screen lg:h-screen overflow-x-hidden overflow-y-auto lg:overflow-hidden flex flex-col antialiased selection:bg-blue-100 selection:text-blue-900">

    <!-- Header / Navbar -->
    <header class="w-full bg-white border-b border-slate-200 shrink-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            
            <div class="flex items-center gap-1.5 sm:gap-4 flex-1 min-w-0">
                <div id="jelajahi-dropdown" class="relative group shrink-0">
                    <button class="flex items-center gap-1 text-xs sm:text-base font-bold text-[#1865f2] hover:text-[#0b4fc7] transition py-2">
                        <span>Jelajahi</span>
                        <i class="fa-solid fa-caret-down text-[10px] sm:text-xs"></i>
                    </button>
                    <div class="absolute left-0 top-full mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-2 hidden group-hover:block z-50">
                        <a href="quran.php" class="block px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#1865f2]">Al-Qur'an Digital</a>
                        <a href="hadits.php" class="block px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#1865f2]">Kutubut Tis'ah & Kitab</a>
                        <a href="kiblat.php" class="block px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#1865f2]">Kompas Kiblat Web</a>
                    </div>
                </div>

                <button 
                    id="search-btn-trigger" 
                    onclick="openSearchBar()" 
                    class="flex items-center gap-1.5 px-2.5 py-1.5 border border-slate-300 rounded-md text-[#1865f2] hover:border-slate-400 bg-white text-xs sm:text-sm font-semibold cursor-pointer shrink-0"
                >
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span class="hidden xs:inline">Cari</span>
                </button>

                <div id="search-active-container" class="hidden items-center gap-2 flex-1 max-w-md">
                    <button 
                        onclick="closeSearchBar()" 
                        class="text-[#1865f2] hover:text-[#0b4fc7] text-base w-7 h-7 flex items-center justify-center shrink-0 cursor-pointer" 
                        title="Tutup Pencarian"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input 
                            id="search-input" 
                            type="text" 
                            class="w-full pl-8 pr-3 py-1.5 text-xs sm:text-sm bg-white border-2 border-[#1865f2] rounded-md outline-none text-slate-900 font-normal"
                        >
                    </div>
                </div>
            </div>

            <div id="brand-logo" class="flex items-center justify-center shrink-0 px-1">
                <a href="index.php" class="flex items-center gap-1.5 sm:gap-2">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center text-slate-950">
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <path d="M52 8C48 9.5 45 13.5 45 18C45 23.5 49.5 28 55 28C57.5 28 59.8 27.1 61.5 25.5C59.5 29.5 55 32 50 32C42.8 32 37 26.2 37 19C37 13.2 40.8 8.3 46 6.5C47.8 5.8 50 6.5 52 8Z" fill="#020617"/>
                            <polygon points="62,13 63.5,16 66.5,16.5 64.3,18.5 64.9,21.5 62,20 59.1,21.5 59.7,18.5 57.5,16.5 60.5,16" fill="#020617"/>
                            <path d="M14 41L11 68L22 75" stroke="#020617" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M86 41L89 68L78 75" stroke="#020617" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 37L15 65L25 72" stroke="#020617" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M83 37L85 65L75 72" stroke="#020617" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M50 42C36 30 22 30 19 33V63C22 60 36 60 50 72C64 60 78 60 81 63V33C78 30 64 30 50 42Z" stroke="#020617" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M50 42V72" stroke="#020617" stroke-width="5" stroke-linecap="round"/>
                            <path d="M43 73.5C43 78 57 78 57 73.5" stroke="#020617" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M10 74C25 68 38 68 47 74" stroke="#020617" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M90 74C75 68 62 68 53 74" stroke="#020617" stroke-width="4.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-base sm:text-2xl font-extrabold tracking-tight text-slate-950">
                        Dirasah.id
                    </span>
                </a>
            </div>

            <div class="flex items-center justify-end gap-2 sm:gap-6 flex-1 shrink-0">
                <a href="login.php" class="text-xs sm:text-base font-bold text-[#1865f2] hover:underline transition">
                    Masuk
                </a>
                <a href="daftar.php" class="bg-[#1865f2] hover:bg-[#0b4fc7] text-white px-3 sm:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-base font-bold transition shadow-sm">
                    Daftar
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-8 py-5 sm:py-8 lg:py-0 flex-1 flex items-center justify-center">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 w-full items-center justify-items-center">
            
            <!-- Kolom Kiri: Slider Visual & Narasi Universal -->
            <div class="w-full max-w-lg lg:max-w-none lg:col-span-7 relative h-[360px] sm:h-[480px] lg:h-[600px] rounded-2xl sm:rounded-3xl overflow-hidden shadow-md bg-slate-950 mx-auto">
                <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-100 flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white">
                    <img src="assets/pik1.png" alt="Slide 1" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl">
                        <h1 class="hero-title text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white">
                            Ubah pencarian literasi Islam menjadi pemahaman nyata.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-light">
                            Dirasah.id membantu siapa saja mendalami studi Islam melalui korpus Al-Qur'an, jalur sanad hadits, dan literatur fikih dalam satu platform terpadu.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pilihan Peran -->
            <div class="w-full max-w-lg lg:max-w-md lg:col-span-5 flex flex-col justify-center space-y-4 pb-8 lg:pb-0 mx-auto">
                
                <div>
                    <h2 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">
                        Daftar di Dirasah.id!
                    </h2>
                </div>

                <div class="space-y-3">
                    <!-- Opsi 1: Pelajar & Siswa -->
                    <a 
                        href="daftar_step2.php?role=pelajar" 
                        class="group flex items-center justify-between p-3.5 sm:p-4 bg-white border border-slate-300 hover:border-[#1865f2] rounded-md transition shadow-sm hover:shadow"
                    >
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-medium text-slate-900">
                                Pelajar
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 font-normal">
                                Siswa belajar sendiri
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-[#1865f2] transition-transform duration-200 group-hover:translate-x-0.5"></i>
                    </a>

                    <!-- Opsi 2: Peneliti & Pengkaji -->
                    <a 
                        href="daftar_step2.php?role=peneliti" 
                        class="group flex items-center justify-between p-3.5 sm:p-4 bg-white border border-slate-300 hover:border-[#1865f2] rounded-md transition shadow-sm hover:shadow"
                    >
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-medium text-slate-900">
                                Peneliti
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 font-normal">
                                Analisis matan, sanad riwayat, dan riset
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-[#1865f2] transition-transform duration-200 group-hover:translate-x-0.5"></i>
                    </a>

                    <!-- Opsi 3: Guru & Pendidik -->
                    <a 
                        href="daftar_step2.php?role=guru" 
                        class="group flex items-center justify-between p-3.5 sm:p-4 bg-white border border-slate-300 hover:border-[#1865f2] rounded-md transition shadow-sm hover:shadow"
                    >
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-medium text-slate-900">
                                Guru
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 font-normal">
                                Pendidik dengan kelas
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-[#1865f2] transition-transform duration-200 group-hover:translate-x-0.5"></i>
                    </a>
                </div>

                <!-- Footer Tautan Masuk -->
                <div class="pt-2 text-xs text-slate-700 text-left">
                    Sudah punya akun Dirasah.id? 
                    <a href="login.php" class="text-[#1865f2] hover:underline font-bold ml-1">
                        Masuk
                    </a>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white border-t border-slate-200 py-3.5 text-center text-xs text-slate-500 shrink-0 mt-auto">
        <p>&copy; 2026 Dirasah.id &mdash; Platform Solusi Interkoneksi Studi Islam</p>
    </footer>

    <script>
        const jelajahiDropdown = document.getElementById('jelajahi-dropdown');
        const searchBtnTrigger = document.getElementById('search-btn-trigger');
        const searchActiveContainer = document.getElementById('search-active-container');
        const searchInput = document.getElementById('search-input');

        function openSearchBar() {
            if (jelajahiDropdown) jelajahiDropdown.classList.add('hidden');
            if (searchBtnTrigger) searchBtnTrigger.classList.add('hidden');
            if (searchActiveContainer) {
                searchActiveContainer.classList.remove('hidden');
                searchActiveContainer.classList.add('flex');
            }
            if (searchInput) searchInput.focus();
        }

        function closeSearchBar() {
            if (searchActiveContainer) {
                searchActiveContainer.classList.remove('flex');
                searchActiveContainer.classList.add('hidden');
            }
            if (jelajahiDropdown) jelajahiDropdown.classList.remove('hidden');
            if (searchBtnTrigger) searchBtnTrigger.classList.remove('hidden');
            if (searchInput) searchInput.value = '';
        }
    </script>

<footer style="text-align: center; padding: 25px 0; margin-top: 40px; font-size: 14px; opacity: 0.85;">
  <p>&copy; 2026 Dirasah. Developed and Created by <strong>Shahzada Rakhshaa Aprihansyah</strong>.</p>
  <p><a href="https://github.com/srakss255-prog" target="_blank" rel="noopener noreferrer" style="text-decoration: underline; color: inherit;">Developer GitHub Profile</a> | <a href="/humans.txt" style="text-decoration: underline; color: inherit;">Humans.txt</a></p>
</footer>
</body>
</html>