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

// Cache-buster otomatis agar browser selalu memuat file terbaru
$v = time();$fav_v = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dirasah.id - Solusi Interkoneksi Studi Islam</title>
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
        /* Matikan efek transisi/animasi saat kursor diarahkan */
        a, button, .group, .prompt-btn {
            transition: none !important;
            transform: none !important;
            animation: none !important;
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
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4 font-normal">
            
            <div class="flex items-center gap-1.5 sm:gap-4 flex-1 min-w-0 font-normal">
                <div id="jelajahi-dropdown" class="relative group shrink-0">
                    <button class="flex items-center gap-1 text-xs sm:text-base font-normal text-[#1865f2] py-2 cursor-pointer">
                        <span>Jelajahi</span>
                        <i class="fa-solid fa-caret-down text-[10px] sm:text-xs"></i>
                    </button>
                    <div class="absolute left-0 top-full mt-1 w-52 bg-white border border-slate-200 rounded-md shadow-lg py-2 hidden group-hover:block z-50 font-normal">
                        <a href="quran.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Al-Qur'an Digital</a>
                        <a href="hadits.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Kutubut Tis'ah &amp; Hadits</a>
                        <a href="kitab.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Kitab &amp; Syarah</a>
                        <a href="doa.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Doa &amp; Dzikir</a>
                        <a href="kiblat.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Kompas Kiblat Web</a>
                    </div>
                </div>

                <button 
                    id="search-btn-trigger" 
                    onclick="openSearchBar()" 
                    class="flex items-center gap-1.5 px-2.5 py-1.5 border border-slate-300 rounded-md text-[#1865f2] bg-white text-xs sm:text-sm font-normal cursor-pointer shrink-0"
                >
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span class="hidden xs:inline">Cari</span>
                </button>

                <div id="search-active-container" class="hidden items-center gap-2 flex-1 max-w-md font-normal">
                    <button 
                        onclick="closeSearchBar()" 
                        class="text-[#1865f2] text-base w-7 h-7 flex items-center justify-center shrink-0 cursor-pointer" 
                        title="Tutup Pencarian"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div class="relative w-full font-normal">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input 
                            id="search-input" 
                            type="text" 
                            placeholder="Cari surat, hadits, atau topik (tekan Enter)..."
                            onkeydown="handleGlobalSearch(event)"
                            class="w-full pl-8 pr-3 py-1.5 text-xs sm:text-sm bg-white border-2 border-[#1865f2] rounded-md outline-none text-slate-900 font-normal"
                        >
                    </div>
                </div>
            </div>

            <div id="brand-logo" class="flex items-center justify-center shrink-0 px-1 font-normal">
                <a href="index.php" class="flex items-center gap-2 sm:gap-2.5 font-normal">
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

            <div class="flex items-center justify-end gap-2 sm:gap-6 flex-1 shrink-0 font-normal">
                <a href="login.php" class="text-xs sm:text-base font-normal text-[#1865f2]">
                    Masuk
                </a>
                <a href="daftar_step2.php?role=pelajar" class="bg-[#1865f2] text-white px-3 sm:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-base font-normal shadow-sm">
                    Daftar
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-8 py-5 sm:py-8 lg:py-0 flex-1 flex items-center justify-center font-normal">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 w-full items-center justify-items-center">
            
            <!-- Kolom Kiri: Slider 3 Gambar Berputar Otomatis -->
            <div class="w-full max-w-lg lg:max-w-none lg:col-span-7 relative h-[360px] sm:h-[480px] lg:h-[600px] rounded-md overflow-hidden shadow-md bg-slate-950 mx-auto font-normal">
                
                <!-- Slide 1: pik1.png -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-100 flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik1.png?v=<?= $v; ?>" alt="Slide 1" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Ubah pencarian literasi Islam menjadi pemahaman nyata.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Dirasah.id membantu siapa saja mendalami studi Islam melalui korpus Al-Qur'an, jalur sanad hadits, dan literatur fikih dalam satu platform terpadu.
                        </p>
                        <div class="pt-2">
                            <button type="button" onclick="openTrialArtifChat()" class="inline-flex items-center gap-2 bg-[#1865f2] text-white pl-2 pr-3 py-1.5 rounded-md text-xs font-normal shadow cursor-pointer">
                                <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="Ustadz Artif" class="w-5 h-5 rounded-full object-cover object-top shrink-0">
                                <span>Tanya Ustadz Artif</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: pik2.png -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik2.png?v=<?= $v; ?>" alt="Slide 2" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Eksplorasi sanad hadits dan tafsir lebih terstruktur.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Telusuri jalur transmisi riwayat periwayatan dengan pohon sanad visual dan perbandingan matan kitab induk.
                        </p>
                        <div class="pt-2">
                            <button type="button" onclick="openTrialArtifChat()" class="inline-flex items-center gap-2 bg-[#1865f2] text-white pl-2 pr-3 py-1.5 rounded-md text-xs font-normal shadow cursor-pointer">
                                <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="Ustadz Artif" class="w-5 h-5 rounded-full object-cover object-top shrink-0">
                                <span>Tanya Ustadz Artif</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: pik3.png -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik3.png?v=<?= $v; ?>" alt="Slide 3" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Ruang belajar interaktif untuk semua kalangan.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Dari pelajar, pengkaji, hingga pendidik, temukan kurikulum kajian Islam yang kredibel dan aplikatif.
                        </p>
                        <div class="pt-2">
                            <button type="button" onclick="openTrialArtifChat()" class="inline-flex items-center gap-2 bg-[#1865f2] text-white pl-2 pr-3 py-1.5 rounded-md text-xs font-normal shadow cursor-pointer">
                                <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="Ustadz Artif" class="w-5 h-5 rounded-full object-cover object-top shrink-0">
                                <span>Tanya Ustadz Artif</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan: Pilihan Peran -->
            <div class="w-full max-w-lg lg:max-w-md lg:col-span-5 flex flex-col justify-center space-y-4 pb-8 lg:pb-0 mx-auto font-normal">
                
                <div>
                    <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-normal">
                        Daftar di Dirasah.id!
                    </h2>
                </div>

                <div class="space-y-3 font-normal">
                    <!-- Opsi 1: Pelajar & Siswa -->
                    <a 
                        href="daftar_step2.php?role=pelajar" 
                        class="flex items-center justify-between p-3.5 sm:p-4 bg-white border border-slate-200 rounded-md shadow-sm font-normal"
                    >
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-normal text-slate-900 leading-snug">
                                Pelajar &amp; Siswa
                            </h3>
                            <p class="text-xs sm:text-[13px] text-slate-500 font-normal leading-normal">
                                Belajar mandiri, tadarus, dan hafalan terstruktur
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-[#1865f2] shrink-0 ml-3"></i>
                    </a>

                    <!-- Opsi 2: Pengkaji & Peneliti -->
                    <a 
                        href="daftar_step2.php?role=peneliti" 
                        class="flex items-center justify-between p-3.5 sm:p-4 bg-white border border-slate-200 rounded-md shadow-sm font-normal"
                    >
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-normal text-slate-900 leading-snug">
                                Pengkaji &amp; Peneliti
                            </h3>
                            <p class="text-xs sm:text-[13px] text-slate-500 font-normal leading-normal">
                                Analisis matan, sanad hadits, dan syarah kitab
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-[#1865f2] shrink-0 ml-3"></i>
                    </a>

                    <!-- Opsi 3: Guru & Pendidik -->
                    <a 
                        href="daftar_step2.php?role=guru" 
                        class="flex items-center justify-between p-3.5 sm:p-4 bg-white border border-slate-200 rounded-md shadow-sm font-normal"
                    >
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-normal text-slate-900 leading-snug">
                                Guru &amp; Pendidik
                            </h3>
                            <p class="text-xs sm:text-[13px] text-slate-500 font-normal leading-normal">
                                Materi halaqah, silabus kajian, dan kelas
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-[#1865f2] shrink-0 ml-3"></i>
                    </a>
                </div>

                <!-- Footer Tautan Masuk -->
                <div class="pt-1 text-xs sm:text-sm text-slate-600 text-left font-normal">
                    Sudah punya akun Dirasah.id? 
                    <a href="login.php" class="text-[#1865f2] font-normal ml-1">
                        Masuk
                    </a>
                </div>

            </div>

        </div>
    </main>

    <!-- Modal AI Assistant (Ustadz Artif) -->
    <div id="trial-artif-modal" class="fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-3 sm:p-4 hidden font-normal">
        <div class="bg-white rounded-md overflow-hidden max-w-lg w-full shadow-xl border border-slate-200 flex flex-col font-normal" style="height: 560px; max-height: 90vh;">
            
            <div class="px-4 py-3 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
                <div class="flex items-center gap-3 font-normal">
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-8 h-8 rounded-full object-cover object-top shrink-0 border border-slate-200">
                    <div>
                        <h4 class="text-sm font-normal text-slate-800 leading-tight">Ustadz Artif</h4>
                        <p class="text-[11px] text-slate-400 font-normal">AI Asisten Islami Dirasah</p>
                    </div>
                </div>
                <button type="button" onclick="closeTrialArtifChat()" class="text-slate-400 hover:text-slate-600 p-1.5 focus:outline-none cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Area Percakapan dengan Teks Sambutan & Prompt Netral Non-Neon -->
            <div id="trial-chat-messages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50 text-xs text-slate-700 font-normal">
                <div class="flex items-start gap-2.5 font-normal">
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-7 h-7 rounded-full object-cover object-top shrink-0 mt-0.5 border border-slate-200">
                    <div class="bg-white border border-slate-200 p-3.5 rounded-md max-w-[90%] shadow-sm leading-relaxed text-slate-800 font-normal text-left space-y-2.5">
                        <p>
                            Assalamu’alaikum! Saya <strong>Asisten Dirasah</strong>. Saya bisa membantu mencari dan memahami materi hadits, fikih, dan tafsir yang tersedia di <a href="index.php" class="text-[#1865f2] hover:underline">Dirasah.id</a>. Jawaban akan dilengkapi rujukan jika ditemukan. Untuk pertanyaan yang belum ada sumbernya, silakan konfirmasi kepada ustadz.
                        </p>
                        
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-[11px] text-slate-400 block mb-1.5 font-normal">Coba tanyakan:</span>
                            <div class="space-y-1.5">
                                <button type="button" onclick="quickAskPrompt('Apa saja rukun wudhu menurut materi fikih?')" class="prompt-btn w-full text-left px-2.5 py-1.5 rounded bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-[11px] cursor-pointer block select-none">
                                    • Apa saja rukun wudu menurut materi fikih?
                                </button>
                                <button type="button" onclick="quickAskPrompt('Jelaskan makna hadits tentang niat.')" class="prompt-btn w-full text-left px-2.5 py-1.5 rounded bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-[11px] cursor-pointer block select-none">
                                    • Jelaskan makna hadis tentang niat.
                                </button>
                                <button type="button" onclick="quickAskPrompt('Apa tafsir singkat ayat tentang menuntut ilmu?')" class="prompt-btn w-full text-left px-2.5 py-1.5 rounded bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-[11px] cursor-pointer block select-none">
                                    • Apa tafsir singkat ayat tentang menuntut ilmu?
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white border-t border-slate-200 shrink-0 font-normal">
                <form id="trial-chat-form" onsubmit="sendTrialArtifMessage(event)" class="flex items-center gap-2 m-0 font-normal">
                    <input 
                        type="text" 
                        id="trial-input-text" 
                        placeholder="Ketik pertanyaan untuk Ustadz Artif..." 
                        autocomplete="off"
                        class="flex-1 px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:border-slate-400 text-slate-800 font-normal"
                    >
                    <button 
                        type="submit" 
                        id="trial-btn-send"
                        class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-xs font-normal shrink-0 cursor-pointer"
                    >
                        Kirim
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="w-full bg-white border-t border-slate-200 py-3.5 text-center text-xs text-slate-500 shrink-0 mt-auto font-normal">
        <p>&copy; 2026 Dirasah.id &mdash; Platform Solusi Interkoneksi Studi Islam</p>
    </footer>

    <script>
        // Logika Pergantian 3 Slide Otomatis
        const heroSlides = document.querySelectorAll('.hero-slide');
        let currentSlideIdx = 0;

        function runHeroSlider() {
            if (heroSlides.length <= 1) return;

            heroSlides[currentSlideIdx].classList.remove('opacity-100');
            heroSlides[currentSlideIdx].classList.add('opacity-0', 'pointer-events-none');

            currentSlideIdx = (currentSlideIdx + 1) % heroSlides.length;

            heroSlides[currentSlideIdx].classList.remove('opacity-0', 'pointer-events-none');
            heroSlides[currentSlideIdx].classList.add('opacity-100');
        }

        setInterval(runHeroSlider, 4500);

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

        // ================= PENCARIAN GLOBAL HEADER (ENTER) =================
        function handleGlobalSearch(event) {
            if (event.key === 'Enter') {
                const keyword = document.getElementById('search-input').value.trim().toLowerCase();
                if (!keyword) return;

                if (keyword.includes('surah') || keyword.includes('quran') || keyword.includes('ayat') || keyword.includes('fatihah')) {
                    window.location.href = 'quran.php';
                } else if (keyword.includes('hadits') || keyword.includes('bukhari') || keyword.includes('muslim') || keyword.includes('tirmidzi')) {
                    window.location.href = 'hadits.php';
                } else if (keyword.includes('kitab') || keyword.includes('fikih') || keyword.includes('safinah')) {
                    window.location.href = 'kitab.php';
                } else if (keyword.includes('doa') || keyword.includes('dzikir')) {
                    window.location.href = 'doa.php';
                } else if (keyword.includes('kiblat') || keyword.includes('arah')) {
                    window.location.href = 'kiblat.php';
                } else {
                    window.location.href = `search_router.php?search=${encodeURIComponent(keyword)}`;
                }
            }
        }

        // ================= TRIAL AI ASSISTANT (USTADZ ARTIF) =================
        const MAX_TRIAL_QUESTIONS = 3;
        const TRIAL_STORAGE_KEY = 'dirasah_guest_artif_count';

        function getTrialUsage() {
            try {
                return parseInt(localStorage.getItem(TRIAL_STORAGE_KEY) || '0');
            } catch(e) {
                return 0;
            }
        }

        function setTrialUsage(val) {
            try {
                localStorage.setItem(TRIAL_STORAGE_KEY, val.toString());
            } catch(e) {}
        }

        function openTrialArtifChat() {
            const modal = document.getElementById('trial-artif-modal');
            if (modal) {
                modal.classList.remove('hidden');
                const input = document.getElementById('trial-input-text');
                if (input) input.focus();
            }
        }

        function closeTrialArtifChat() {
            const modal = document.getElementById('trial-artif-modal');
            if (modal) modal.classList.add('hidden');
        }

        // Fungsi klik cepat butir saran pertanyaan
        function quickAskPrompt(text) {
            const input = document.getElementById('trial-input-text');
            if (!input) return;
            input.value = text;
            
            const form = document.getElementById('trial-chat-form');
            if (form) {
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        }

        async function sendTrialArtifMessage(e) {
            e.preventDefault();
            const input = document.getElementById('trial-input-text');
            const btn = document.getElementById('trial-btn-send');
            const chatBox = document.getElementById('trial-chat-messages');
            const question = input.value.trim();

            if (!question) return;

            // Render User Bubble
            const userBubble = document.createElement('div');
            userBubble.className = 'flex justify-end';
            userBubble.innerHTML = `
                <div class="bg-slate-800 text-white p-3 rounded-md max-w-[85%] text-xs font-normal">
                    ${question.replace(/</g, "&lt;").replace(/>/g, "&gt;")}
                </div>
            `;
            chatBox.appendChild(userBubble);
            input.value = '';
            input.disabled = true;
            btn.disabled = true;
            chatBox.scrollTop = chatBox.scrollHeight;

            let usage = getTrialUsage();

            // Setelah 3x tanya -> Ustadz Artif menjawab dengan ajakan Login / Daftar
            if (usage >= MAX_TRIAL_QUESTIONS) {
                setTimeout(() => {
                    const limitBubble = document.createElement('div');
                    limitBubble.className = 'flex items-start gap-2.5';
                    limitBubble.innerHTML = `
                        <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-7 h-7 rounded-full object-cover object-top shrink-0 mt-0.5 border border-slate-200">
                        <div class="bg-white border border-slate-200 p-3.5 rounded-md max-w-[85%] shadow-sm leading-relaxed text-slate-800 text-xs text-left font-normal space-y-2.5">
                            <p>
                                Afwan, batas sesi tanya jawab gratis Anda telah tercapai. Silakan masuk atau daftar akun Dirasah.id terlebih dahulu untuk melanjutkan diskusi dan tanya jawab tanpa batas.
                            </p>
                            <div class="flex items-center gap-2 pt-1">
                                <a href="login.php" class="px-3 py-1.5 rounded-md border border-slate-300 text-slate-700 text-xs font-normal">
                                    Masuk
                                </a>
                                <a href="daftar_step2.php?role=pelajar" class="px-3 py-1.5 rounded-md bg-[#1865f2] text-white text-xs font-normal">
                                    Daftar Akun
                                </a>
                            </div>
                        </div>
                    `;
                    chatBox.appendChild(limitBubble);
                    chatBox.scrollTop = chatBox.scrollHeight;
                    input.disabled = false;
                    btn.disabled = false;
                    input.focus();
                }, 400);
                return;
            }

            // Loading bubble
            const loadingBubble = document.createElement('div');
            loadingBubble.className = 'flex items-start gap-2.5';
            loadingBubble.id = 'trial-loading';
            loadingBubble.innerHTML = `
                <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-7 h-7 rounded-full object-cover object-top shrink-0 mt-0.5 border border-slate-200">
                <div class="bg-white border border-slate-200 p-3 rounded-md max-w-[85%] shadow-sm text-slate-400 text-xs italic font-normal">
                    Ustadz Artif sedang merumuskan jawaban...
                </div>
            `;
            chatBox.appendChild(loadingBubble);
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                const res = await fetch('api_artif.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: question })
                });
                const result = await res.json();
                
                const loadEl = document.getElementById('trial-loading');
                if (loadEl) loadEl.remove();

                const aiBubble = document.createElement('div');
                aiBubble.className = 'flex items-start gap-2.5';

                const rawReply = result.reply || result.message || 'Afwan, belum dapat merespon saat ini.';
                const cleanReply = rawReply.replace(/\*\*(.*?)\*\*/g, '$1').replace(/\*(.*?)\*/g, '$1').replace(/\n/g, '<br>');

                aiBubble.innerHTML = `
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-7 h-7 rounded-full object-cover object-top shrink-0 mt-0.5 border border-slate-200">
                    <div class="bg-white border border-slate-200 p-3 rounded-md max-w-[85%] shadow-sm leading-relaxed text-slate-800 text-xs text-left font-normal">
                        ${cleanReply}
                    </div>
                `;
                chatBox.appendChild(aiBubble);

                usage++;
                setTrialUsage(usage);

            } catch (err) {
                const loadEl = document.getElementById('trial-loading');
                if (loadEl) loadEl.remove();

                const errBubble = document.createElement('div');
                errBubble.className = 'flex items-start gap-2.5';
                errBubble.innerHTML = `
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-7 h-7 rounded-full object-cover object-top shrink-0 mt-0.5 border border-slate-200">
                    <div class="bg-rose-50 border border-rose-200 p-3 rounded-md max-w-[85%] shadow-sm leading-relaxed text-rose-600 text-xs font-normal">
                        Terjadi gangguan koneksi ke server AI.
                    </div>
                `;
                chatBox.appendChild(errBubble);
            } finally {
                input.disabled = false;
                btn.disabled = false;
                input.focus();
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }
    </script>
</body>
</html>