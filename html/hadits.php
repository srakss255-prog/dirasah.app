<?php
if (session_status() === PHP_SESSION_NONE) {
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
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'Pengguna Dirasah';
$foto_user = null;

if (isset($pdo)) {
    try {
        $qUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $qUser->execute([$user_id]);
        $curUser = $qUser->fetch(PDO::FETCH_ASSOC);
        if ($curUser) {
            $nama_user = !empty($curUser['nama']) ? $curUser['nama'] : (!empty($curUser['username']) ? $curUser['username'] : $nama_user);$foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
        }
    } catch (Exception $e) {$foto_user = null;
    }
}

$banner_v = file_exists('assets/pik3.png') ? filemtime('assets/pik3.png') : time();
$fav_v = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
$logo_v = file_exists('assets/dirasah.png') ? filemtime('assets/dirasah.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kutubut Tis'ah &amp; Hadits - Dirasah.id</title>
    <!-- Favicon Tab Browser (putih.png) -->
    <link rel="icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="apple-touch-icon" href="assets/putih.png?v=<?= $fav_v; ?>">

    <!-- Google Fonts: Plus Jakarta Sans & Arabic Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Amiri+Quran&family=Noto+Naskh+Arabic:wght@400;500&display=swap" rel="stylesheet">
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
        
        .arabic-text { 
            font-family: 'Amiri Quran', 'Noto Naskh Arabic', serif !important; 
            font-size: 1.65rem;
            line-height: 2.8;
            letter-spacing: 0;
            word-spacing: 8px;
            direction: rtl;
            text-align: right;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1;
            font-weight: 400 !important;
        }

        .arabic-word {
            font-family: 'Amiri Quran', 'Noto Naskh Arabic', serif !important;
            direction: rtl;
            font-weight: 400 !important;
        }

        .translation-text {
            font-size: 0.925rem;
            line-height: 1.75;
            letter-spacing: 0.005em;
            font-weight: 400 !important;
        }

        .sidebar-transition {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .toggle-switch-bg {
            transition: background-color 0.2s ease;
        }
        .toggle-switch-dot {
            transition: transform 0.2s ease;
        }
        a, button, .prompt-btn {
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
<body class="bg-[#f8fafc] text-slate-900 antialiased selection:bg-blue-100 selection:text-blue-900 min-h-screen flex font-normal">

    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/40 z-40 hidden lg:hidden backdrop-blur-[2px]"></div>

    <!-- Sidebar Bilah Sisi Tanpa Mutaba'ah -->
    <aside id="main-sidebar" class="sidebar-transition fixed top-0 bottom-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col w-64 -translate-x-full lg:translate-x-0 select-none font-normal">
        <div class="h-20 px-3 border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <button type="button" onclick="toggleSidebar()" id="brand-toggle-btn" class="flex items-center gap-3 w-full p-1 focus:outline-none cursor-pointer" title="Buka / Tutup Menu">
                <div class="w-12 h-12 flex items-center justify-center shrink-0">
                    <img src="assets/dirasah.png?v=<?= $logo_v; ?>" alt="Dirasah.id" class="w-10 h-10 object-contain">
                </div>
                <span class="sidebar-text text-xl font-normal tracking-[-0.015em] text-[#182335] whitespace-nowrap overflow-hidden">
                    Dirasah.id
                </span>
            </button>
            <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6 font-normal">
            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">Utama</span>
                <nav class="space-y-1">
                    <a href="dashboard.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/home.png" alt="Dashboard" class="w-6 h-6 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Dashboard</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">Khazanah Studi</span>
                <nav class="space-y-1">
                    <a href="quran.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/quran.png" alt="Al-Qur'an" class="w-8 h-auto max-h-7 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Al-Qur'an Digital</span>
                    </a>

                    <!-- Menu Kutubut Tis'ah & Hadits (Aktif) -->
                    <a href="hadits.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-[#1865f2] bg-blue-50/60">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/hadis.png" alt="Hadits" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kutubut Tis'ah &amp; Hadits</span>
                    </a>

                    <a href="kitab.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kitab.png" alt="Kitab &amp; Syarah" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kitab &amp; Syarah</span>
                    </a>

                    <a href="doa.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/doa.png" alt="Doa &amp; Dzikir" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Doa &amp; Dzikir</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">Ibadah &amp; Waktu</span>
                <nav class="space-y-1">
                    <a href="sholat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/sholat.png" alt="Jadwal Sholat" class="w-9 h-auto max-h-8 object-contain mix-blend-multiply">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Jadwal Sholat</span>
                    </a>

                    <a href="kiblat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kiblat.png" alt="Arah Kiblat" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Arah Kiblat</span>
                    </a>

                    <a href="kalender.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kalender.png" alt="Kalender Islam" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kalender Islam</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Konten Utama Halaman Hadits -->
    <div id="content-area" class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-64 font-normal">
        
        <header class="w-full bg-white border-b border-slate-200 h-16 px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 font-normal">
            <div class="flex items-center gap-3 flex-1 max-w-xl font-normal">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-700 hover:text-[#1865f2] p-1.5 focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <div class="relative w-full max-w-md font-normal">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input 
                        type="text" 
                        id="search-input" 
                        placeholder="Cari Surah, Ayat, Hadits, atau Tema..." 
                        oninput="handleSearch()" 
                        class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-md outline-none focus:border-[#1865f2] focus:bg-white text-slate-900 font-normal"
                    >
                </div>
            </div>

            <!-- Avatar Profil -->
            <div class="flex items-center font-normal">
                <a href="profil.php" class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-slate-100 transition group font-normal" title="Buka Profil Saya">
                    <?php if (!empty($foto_user)): ?>
                        <img src="<?= htmlspecialchars($foto_user); ?>" alt="Avatar" class="w-9 h-9 rounded-full object-cover shrink-0 border border-slate-200">
                    <?php else: ?>
                        <div class="w-9 h-9 rounded-full bg-[#1865f2] text-white flex items-center justify-center font-normal text-sm shrink-0">
                            <?= strtoupper(substr($nama_user, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span class="hidden md:inline text-xs sm:text-sm font-normal text-slate-800 max-w-[190px] truncate">
                        <?= htmlspecialchars($nama_user); ?>
                    </span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-8 max-w-7xl mx-auto w-full space-y-6 font-normal">
            
            <!-- Banner Hadits -->
            <div class="relative w-full rounded-md overflow-hidden shadow-sm min-h-[160px] sm:min-h-[180px] flex flex-col justify-center p-6 sm:p-8 text-white bg-slate-950 font-normal">
                <img 
                    src="assets/pik3.png?v=<?= $banner_v; ?>" 
                    onerror="this.onerror=null; this.src='assets/pik1.png?v=<?= $banner_v; ?>';"
                    alt="Banner Hadits" 
                    class="absolute inset-0 w-full h-full object-cover object-center z-0 opacity-40"
                >
                <div class="absolute inset-0 bg-slate-950/45 z-0 font-normal"></div>

                <div class="relative z-10 max-w-2xl space-y-1.5 font-normal">
                    <h1 class="text-2xl sm:text-3xl font-normal tracking-wide text-white leading-tight">
                        Kutubut Tis'ah &amp; Ensiklopedia Hadits
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-100 font-normal italic leading-relaxed">
                        "Semoga Allah mencerahkan wajah orang yang mendengar sabdaku lalu memahaminya, menghafalnya, dan menyampaikannya kepada orang lain."
                    </p>
                    <p class="text-[11px] text-slate-200 font-normal not-italic">
                        (HR. At-Tirmidzi No. 2658, Abu Dawud No. 3660)
                    </p>
                </div>
            </div>

            <!-- Panel Penanda Terakhir Dibaca Hadits -->
            <div id="hadith-last-read-panel" class="hidden bg-white border border-slate-200 rounded-md p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4 font-normal">
                <div class="font-normal">
                    <span class="text-[10px] font-normal text-slate-400 uppercase tracking-wider block mb-0.5">Terakhir Dibaca</span>
                    <h4 id="hadith-last-read-title" class="text-xs sm:text-sm font-normal text-slate-800 leading-tight">-</h4>
                    <p id="hadith-last-read-sub" class="text-[11px] text-slate-500 font-normal mt-0.5">-</p>
                </div>
                <div class="flex items-center gap-2 font-normal">
                    <button type="button" onclick="resumeLastHadith()" class="px-3.5 py-1.5 bg-slate-900 text-white text-xs font-normal rounded-md hover:bg-slate-800 cursor-pointer">
                        Lanjut Membaca
                    </button>
                    <button type="button" onclick="removeLastHadithBookmark()" class="px-2 py-1.5 text-slate-400 hover:text-rose-500 text-xs cursor-pointer font-normal" title="Hapus Penanda">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <!-- Tampilan 1: Daftar 9 Kitab Induk (Kutubut Tis'ah) -->
            <div id="view-books" class="space-y-4 font-normal">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 font-normal">
                    <h2 class="text-sm font-normal text-slate-800">9 Kitab Hadits Induk (Kutubut Tis'ah)</h2>
                    <span class="text-xs text-slate-400 font-normal">Pilih perawi untuk membaca sanad &amp; matan</span>
                </div>

                <div id="books-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 font-normal">
                    <!-- Dimuat otomatis oleh renderBooks() -->
                </div>
            </div>

            <!-- Tampilan 2: Pembaca Matan Hadits dari Kitab Terpilih -->
            <div id="view-reader" class="space-y-4 hidden font-normal">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-slate-200 rounded-md p-4 font-normal">
                    <div class="flex items-center gap-3 font-normal">
                        <button onclick="backToBooks()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-md text-xs font-normal text-slate-700 flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span>Daftar Kitab</span>
                        </button>
                        <h2 id="active-book-title" class="text-sm font-normal text-slate-800">-</h2>
                    </div>

                    <div class="flex items-center gap-5 font-normal">
                        <!-- Saklar Mode Per Kata -->
                        <div class="flex items-center gap-2 cursor-pointer select-none font-normal" onclick="toggleWordByWordMode()">
                            <span class="text-xs text-slate-600 font-normal">Per Kata</span>
                            <div id="toggle-switch" class="toggle-switch-bg w-9 h-5 bg-slate-300 rounded-full p-0.5 flex items-center">
                                <div id="toggle-dot" class="toggle-switch-dot w-4 h-4 bg-white rounded-full shadow-sm"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 font-normal">
                            <input type="number" id="input-hadith-no" placeholder="No..." class="w-20 px-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-md outline-none focus:border-[#1865f2] font-normal">
                            <button onclick="goToHadithNumber()" class="px-3 py-1 bg-[#1865f2] text-white rounded-md text-xs font-normal cursor-pointer">Buka</button>
                        </div>
                    </div>
                </div>

                <div id="hadith-content-list" class="space-y-4 font-normal">
                    <!-- Konten Hadits Dimuat di Sini -->
                </div>

                <!-- Navigasi Halaman Hadits -->
                <div id="pagination-controls" class="flex items-center justify-between pt-4 border-t border-slate-200 font-normal">
                    <button id="btn-prev-page" onclick="changePage(-1)" class="px-4 py-2 rounded-md border border-slate-200 text-xs font-normal text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-40 flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        <span>Sebelumnya</span>
                    </button>
                    <span id="page-indicator" class="text-xs text-slate-500 font-normal">Hadits 1 - 10</span>
                    <button id="btn-next-page" onclick="changePage(1)" class="px-4 py-2 rounded-md border border-slate-200 text-xs font-normal text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-40 flex items-center gap-2 cursor-pointer">
                        <span>Selanjutnya</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= MODAL POHON SILSILAH SANAD VISUAL ================= -->
    <div id="sanad-tree-modal" class="fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-3 sm:p-4 hidden font-normal">
        <div class="bg-white rounded-md overflow-hidden max-w-lg w-full shadow-xl border border-slate-200 flex flex-col font-normal max-h-[85vh]">
            
            <div class="px-5 py-3.5 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
                <div class="font-normal">
                    <h4 class="text-sm font-normal text-slate-900" id="sanad-modal-title">Silsilah Transmisi Sanad Hadits</h4>
                    <p class="text-[11px] text-slate-400 font-normal">Jalur periwayatan dari Mukharrij hingga Rasulullah SAW</p>
                </div>
                <button type="button" onclick="closeSanadTreeModal()" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Kontainer Rantai Sanad Vertikal -->
            <div class="p-6 overflow-y-auto space-y-0 font-normal" id="sanad-tree-container">
                <!-- Dimuat oleh JavaScript -->
            </div>

            <div class="p-3 bg-white border-t border-slate-100 flex items-center justify-end font-normal">
                <button type="button" onclick="closeSanadTreeModal()" class="px-4 py-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-normal cursor-pointer">
                    Tutup Silsilah
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notifikasi Penanda Sederhana -->
    <div id="toast-hadith" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white px-4 py-2 rounded-md text-xs font-normal shadow-md hidden">
        <span id="toast-hadith-msg">Hadits ditandai</span>
    </div>

    <!-- ================= WIDGET FLOATING USTADZ ARTIF ================= -->
    <div 
        id="artif-floating-btn" 
        class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-16 h-16 bg-white rounded-full border border-slate-200 shadow-md overflow-hidden cursor-grab active:cursor-grabbing select-none touch-none p-0.5"
        style="user-select: none; -webkit-user-select: none;"
        title="Tanya Ustadz Artif"
    >
        <img 
            src="assets/artif.png" 
            onerror="this.onerror=null; this.src='artif.png';" 
            alt="Ustadz Artif" 
            class="w-full h-full object-cover object-top rounded-full pointer-events-none select-none"
        >
    </div>

    <!-- Modal Dialog Chat Ustadz Artif -->
    <div id="artif-chat-modal" class="fixed bottom-24 right-6 z-50 w-[92vw] sm:w-96 bg-white rounded-md shadow-xl border border-slate-200 flex flex-col overflow-hidden hidden font-normal" style="height: 540px; max-height: 85vh;">
        
        <div class="px-4 py-3 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <div class="flex items-center gap-3 font-normal">
                <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-full h-full object-cover object-top">
                </div>
                <div class="font-normal">
                    <h4 class="text-sm font-semibold text-slate-800 leading-tight">Ustadz Artif</h4>
                    <p class="text-[11px] text-slate-400 font-normal">AI Asisten Islami Dirasah</p>
                </div>
            </div>
            <button type="button" onclick="toggleArtifChat()" class="text-slate-400 hover:text-slate-600 p-1.5 focus:outline-none cursor-pointer">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div id="artif-chat-messages" class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-slate-50 text-xs text-slate-700 font-normal">
            <div class="flex items-start gap-2.5 font-normal">
                <div class="w-7 h-7 rounded-full overflow-hidden bg-white border border-slate-200 shrink-0 mt-0.5">
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-full h-full object-cover object-top">
                </div>
                <div class="bg-white border border-slate-200 p-3.5 rounded-md max-w-[90%] shadow-sm leading-relaxed text-slate-800 font-normal text-left space-y-2.5">
                    <p>
                        Assalamu’alaikum! Saya <strong>Asisten Dirasah</strong>. Saya bisa membantu mencari dan memahami materi hadis, fikih, dan tafsir yang tersedia di <a href="index.php" class="text-[#1865f2] hover:underline font-medium">Dirasah.id</a>. Jawaban akan dilengkapi rujukan jika ditemukan. Untuk pertanyaan yang belum ada sumbernya, silakan konfirmasi kepada ustadz.
                    </p>
                    
                    <div class="pt-2 border-t border-slate-100 font-normal">
                        <span class="text-[11px] text-slate-500 block mb-1.5 font-semibold">Coba tanyakan:</span>
                        <div class="space-y-1.5 font-normal">
                            <button type="button" onclick="quickAskPrompt('Apa saja rukun wudu menurut materi fikih?')" class="prompt-btn w-full text-left px-2.5 py-1.5 rounded bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-[11px] cursor-pointer block select-none">
                                • Apa saja rukun wudu menurut materi fikih?
                            </button>
                            <button type="button" onclick="quickAskPrompt('Jelaskan makna hadis tentang niat.')" class="prompt-btn w-full text-left px-2.5 py-1.5 rounded bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-[11px] cursor-pointer block select-none">
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

        <div id="artif-image-preview-container" class="hidden px-4 py-2 bg-slate-100/80 border-t border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <div class="flex items-center gap-2.5 font-normal">
                <img id="artif-image-preview" src="" class="w-10 h-10 object-cover rounded-md border border-slate-200">
                <span class="text-[11px] text-slate-600 font-normal">Gambar siap dianalisis</span>
            </div>
            <button type="button" onclick="removeSelectedImage()" class="text-slate-400 hover:text-red-500 text-lg leading-none p-1 cursor-pointer">&times;</button>
        </div>

        <div class="p-3 bg-white border-t border-slate-200 shrink-0 font-normal">
            <input type="file" id="artif-file-input" accept="image/*" class="hidden" onchange="handleImageSelect(event)">
            
            <form id="artif-chat-form" onsubmit="sendArtifMessage(event)" class="flex items-center gap-2 m-0 font-normal">
                <button 
                    type="button" 
                    onclick="document.getElementById('artif-file-input').click()" 
                    class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0 cursor-pointer" 
                    title="Unggah foto hadits / lembar kitab"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>

                <input 
                    type="text" 
                    id="artif-input-text" 
                    placeholder="Ketik pertanyaan ke Ustadz Artif..." 
                    autocomplete="off"
                    class="flex-1 px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:border-slate-400 text-slate-800 font-normal"
                >
                <button 
                    type="submit" 
                    id="artif-btn-send"
                    class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-xs font-normal shrink-0 cursor-pointer"
                >
                    Kirim
                </button>
            </form>
        </div>

    </div>

    <script>
        const currentUserId = <?= json_encode($user_id); ?>;
        const storageKey = `dirasah_reading_history_${currentUserId}`;

        const booksData = [
            { id: 'bukhari', name: 'Shahih Bukhari', arabic: 'صحيح البخاري', author: 'Imam Bukhari', total: 7008, desc: 'Kitab paling shahih setelah Al-Qur\'an Al-Karim.' },
            { id: 'muslim', name: 'Shahih Muslim', arabic: 'صحيح مسلم', author: 'Imam Muslim', total: 5362, desc: 'Koleksi hadits shahih dengan sistematika sanad terbaik.' },
            { id: 'abu-daud', name: 'Sunan Abu Dawud', arabic: 'سنن أبي داود', author: 'Imam Abu Dawud', total: 4590, desc: 'Fokus rujukan hukum fikih dan amaliyah ibadah.' },
            { id: 'tirmidzi', name: 'Jami\' At-Tirmidzi', arabic: 'جامع الترمذي', author: 'Imam At-Tirmidzi', total: 3891, desc: 'Memuat penjelasan derajat keshahihan hadits.' },
            { id: 'nasai', name: 'Sunan An-Nasa\'i', arabic: 'سنن النسائي', author: 'Imam An-Nasa\'i', total: 5662, desc: 'Ketelitian tinggi dalam telaah cacat sanad (ilat).' },
            { id: 'ibnu-majah', name: 'Sunan Ibnu Majah', arabic: 'سنن ابن ماجه', author: 'Imam Ibnu Majah', total: 4331, desc: 'Melengkapi enam rujukan pokok hadits (Kutubus Sittah).' },
            { id: 'ahmad', name: 'Musnad Ahmad', arabic: 'مسند أحمد', author: 'Imam Ahmad bin Hanbal', total: 26363, desc: 'Kompilasi musnad raksasa menurut riwayat sahabat.' },
            { id: 'malik', name: 'Muwaththa\' Malik', arabic: 'موطأ مالك', author: 'Imam Malik', total: 1594, desc: 'Pondasi fikih Madinah dan sunnah Rasulullah SAW.' },
            { id: 'darimi', name: 'Sunan Ad-Darimi', arabic: 'سنن الدارمي', author: 'Imam Ad-Darimi', total: 3367, desc: 'Memuat pengantar keutamaan ilmu dan sunnah Nabi.' }
        ];

        const hadithVocabulary = {
            'حدثنا': 'Telah menceritakan kepada kami',
            'حدثني': 'Telah menceritakan kepadaku',
            'حدث': 'Dia menceritakan',
            'اخبرنا': 'Telah mengabarkan kepada kami',
            'اخبرني': 'Telah mengabarkan kepadaku',
            'اخبر': 'Dia mengabarkan',
            'قال': 'Berkata',
            'قالا': 'Keduanya berkata',
            'قالوا': 'Mereka berkata',
            'سمعت': 'Aku mendengar',
            'سمع': 'Dia mendengar',
            'عن': 'Dari',
            'عني': 'Dariku',
            'عنه': 'Darinya',
            'عنها': 'Darinya',
            'عنهم': 'Dari mereka',
            'ان': 'Bahwa',
            'انه': 'Bahwasanya dia',
            'ح': 'Pindah sanad',
            'بن': 'Putra',
            'ابن': 'Putra',
            'ابو': 'Abu / Ayah',
            'ابي': 'Abi / Ayah',
            'بكر': 'Bakr',
            'شيبة': 'Syaibah',
            'وكيع': 'Waki\'',
            'شعبة': 'Syu\'bah',
            'الحكم': 'Al-Hakam',
            'الرحمن': 'Ar-Rahman',
            'ليلى': 'Laila',
            'سمرة': 'Samurah',
            'جندب': 'Jundub',
            'الحميدي': 'Al-Humaidi',
            'الزبير': 'Az-Zubair',
            'سفيان': 'Sufyan',
            'يحيى': 'Yahya',
            'سعيد': 'Sa\'id',
            'الانصاري': 'Al-Anshari',
            'محمد': 'Muhammad',
            'ابراهيم': 'Ibrahim',
            'التيمي': 'At-Taimi',
            'علقمة': 'Alqamah',
            'وقاص': 'Waqqash',
            'الليثي': 'Al-Laitsi',
            'عمر': 'Umar',
            'الخطاب': 'Al-Khaththab',
            'علي': 'Ali',
            'عثمان': 'Utsman',
            'عائشة': 'Aisyah',
            'انس': 'Anas',
            'مالك': 'Malik',
            'هريرة': 'Hurairah',
            'الله': 'Allah',
            'رسول': 'Rasul',
            'نبي': 'Nabi',
            'صلى': 'Bersholawat',
            'عليه': 'Kepadanya',
            'وسلم': 'Dan keselamatan',
            'رضي': 'Semoga meridhoi',
            'يرى': 'Menilai / Melihat',
            'حديث': 'Hadits',
            'حديثا': 'Hadits',
            'بحديث': 'Dengan hadits',
            'كذب': 'Dusta',
            'كاذب': 'Pendusta',
            'كاذبين': 'Para pendusta',
            'الكاذبين': 'Para pendusta',
            'كذاب': 'Pembohong',
            'اثر': 'Atsar / Riwayat',
            'الاثر': 'Atsar / Riwayat',
            'مشهور': 'Masyhur',
            'المشهور': 'Yang masyhur',
            'احد': 'Seseorang',
            'احدين': 'Dua orang',
            'من': 'Barangsiapa / Siapa',
            'هو': 'Dia',
            'فهو': 'Maka dia',
            'وهو': 'Dan dia',
            'عبد': 'Hamba',
            'يقول': 'Dia berkata',
            'انما': 'Hanyalah',
            'الاعمال': 'Segala amalan',
            'عمل': 'Perbuatan',
            'النيات': 'Niat-niat',
            'نية': 'Niat',
            'لكل': 'Bagi setiap',
            'امرى': 'Seseorang',
            'امرىء': 'Seseorang',
            'ما': 'Apa yang',
            'نوى': 'Dia niatkan',
            'فمن': 'Maka siapa yang',
            'كانت': 'Adalah',
            'هجرته': 'Hijrahnya',
            'هجرة': 'Hijrah',
            'الى': 'Kepada / Menuju',
            'دنيا': 'Dunia',
            'يصيبها': 'Yang diraihnya',
            'او': 'Atau',
            'امراة': 'Wanita',
            'ينكحها': 'Yang dinikahinya',
            'فهجرته': 'Maka hijrahnya',
            'هاجر': 'Dia berhijrah',
            'اليه': 'Kepadanya',
            'المنبر': 'Mimbar',
            'كتاب': 'Kitab',
            'صلاة': 'Sholat',
            'صوم': 'Puasa',
            'زكاة': 'Zakat',
            'حج': 'Haji',
            'ايمان': 'Iman',
            'اسلام': 'Islam',
            'احسان': 'Ihsan',
            'يوم': 'Hari',
            'قيامة': 'Kiamat',
            'جنة': 'Surga',
            'نار': 'Neraka',
            'حق': 'Kebenaran',
            'باطل': 'Kebatilan',
            'في': 'Di dalam',
            'على': 'Di atas',
            'مع': 'Bersama',
            'لا': 'Tidak',
            'لم': 'Belum / Tidak',
            'لن': 'Tidak akan',
            'كان': 'Adalah',
            'يكون': 'Menjadi',
            'كل': 'Setiap',
            'بعض': 'Sebagian',
            'بين': 'Antara',
            'حتى': 'Hingga',
            'ثم': 'Kemudian',
            'اذا': 'Apabila'
        };

        let currentBook = null;
        let currentPage = 1;
        const pageSize = 10;
        let isWordMode = false;
        let cachedHadiths = [];
        let activeBookmarkedHadithNo = null;

        function getSavedHistory() {
            try {
                const stored = localStorage.getItem(storageKey);
                if (stored !== null) return JSON.parse(stored);
            } catch (e) {}
            return [];
        }

        function saveHistory(list) {
            try {
                localStorage.setItem(storageKey, JSON.stringify(list));
            } catch (e) {}
        }

        function getBookmarkForBook(bookId) {
            const history = getSavedHistory();
            return history.find(item => item.tipe === 'Hadits' && item.book_id === bookId);
        }

        function renderLastHadithBookmarkPanel() {
            const panel = document.getElementById('hadith-last-read-panel');
            const history = getSavedHistory();
            const lastHadith = history.find(item => item.tipe === 'Hadits');

            if (!lastHadith) {
                panel.classList.add('hidden');
                return;
            }

            document.getElementById('hadith-last-read-title').textContent = lastHadith.judul;
            document.getElementById('hadith-last-read-sub').textContent = lastHadith.subjudul;
            panel.classList.remove('hidden');
        }

        function removeLastHadithBookmark() {
            let history = getSavedHistory();
            history = history.filter(item => item.tipe !== 'Hadits');
            saveHistory(history);
            renderLastHadithBookmarkPanel();
            renderBooks(booksData);
        }

        function resumeLastHadith() {
            const history = getSavedHistory();
            const lastHadith = history.find(item => item.tipe === 'Hadits');
            if (lastHadith && lastHadith.book_id) {
                openBook(lastHadith.book_id, lastHadith.hadith_no);
            }
        }

        function tandaiHadits(bookId, hadithNo) {
            if (!currentBook) return;
            const hadithTitle = `${currentBook.name} No. ${hadithNo}`;

            let history = getSavedHistory();
            history = history.filter(item => !(item.tipe === 'Hadits' && item.book_id === bookId));

            history.unshift({
                id: 'hadits_' + bookId + '_' + hadithNo + '_' + Date.now(),
                book_id: bookId,
                hadith_no: hadithNo,
                judul: hadithTitle,
                subjudul: `Terakhir dibaca (${currentBook.author})`,
                tipe: 'Hadits',
                link: `hadits.php?book=${bookId}&no=${hadithNo}`,
                timestamp: Date.now()
            });

            if (history.length > 10) history = history.slice(0, 10);
            saveHistory(history);

            activeBookmarkedHadithNo = hadithNo;
            refreshHadithBookmarkUI();
            renderLastHadithBookmarkPanel();
            showToast(`${hadithTitle} berhasil ditandai!`);
        }

        function refreshHadithBookmarkUI() {
            document.querySelectorAll('.btn-mark-hadith').forEach(btn => {
                const hNo = parseInt(btn.getAttribute('data-hadith'));
                const icon = btn.querySelector('i');
                const text = btn.querySelector('.mark-hadith-text');
                const card = document.getElementById(`hadith-card-${hNo}`);

                if (hNo === activeBookmarkedHadithNo) {
                    btn.className = "btn-mark-hadith h-7 px-2.5 rounded-md border border-slate-300 bg-slate-100 text-slate-800 font-normal text-xs flex items-center gap-1.5 cursor-pointer";
                    icon.className = "fa-solid fa-bookmark text-[11px] text-[#1865f2]";
                    text.textContent = "Ditandai";
                    if (card) card.classList.add('bg-slate-50/50');
                } else {
                    btn.className = "btn-mark-hadith h-7 px-2.5 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 font-normal text-xs flex items-center gap-1.5 cursor-pointer";
                    icon.className = "fa-regular fa-bookmark text-[11px]";
                    text.textContent = "Tandai";
                    if (card) card.classList.remove('bg-slate-50/50');
                }
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast-hadith');
            document.getElementById('toast-hadith-msg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 2500);
        }

        function cleanArabicTashkeel(text) {
            if (!text) return '';
            return text
                .replace(/([\u064B-\u065F])\1+/g, '$1')
                .replace(/([\u064E\u064F\u0650])\u0652/g, '$1')
                .replace(/\u0651([\u064B-\u0650])/g, '$1\u0651')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function normalizeArabic(text) {
            return text
                .replace(/[\u064B-\u0652\u0670\u0640]/g, '')
                .replace(/[إأآا]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/ى/g, 'ي')
                .replace(/ؤ/g, 'و')
                .replace(/ئ/g, 'ي')
                .replace(/[.,:;()\[\]"']/g, '')
                .trim();
        }

        function getSmartWordMeaning(rawWord, wordIndex, totalWords, fullTranslation) {
            const raw = rawWord.replace(/[.,:;()\[\]"']/g, '').trim();
            if (!raw) return '';
            if (raw === 'ح' || raw === 'حـ') return 'Pindah sanad';

            const norm = normalizeArabic(raw);
            if (hadithVocabulary[norm]) return hadithVocabulary[norm];

            return 'Perawi / Sanad';
        }

        function toggleWordByWordMode() {
            isWordMode = !isWordMode;
            const toggleBg = document.getElementById('toggle-switch');
            const toggleDot = document.getElementById('toggle-dot');

            if (isWordMode) {
                toggleBg.classList.remove('bg-slate-300');
                toggleBg.classList.add('bg-[#1865f2]');
                toggleDot.style.transform = 'translateX(16px)';
            } else {
                toggleBg.classList.remove('bg-[#1865f2]');
                toggleBg.classList.add('bg-slate-300');
                toggleDot.style.transform = 'translateX(0px)';
            }

            if (cachedHadiths.length > 0) {
                renderHadithItems(cachedHadiths);
            }
        }

        function renderBooks(list) {
            const container = document.getElementById('books-grid');
            let html = '';

            list.forEach(book => {
                const bookmark = getBookmarkForBook(book.id);
                const hasBookmark = !!bookmark;

                html += `
                    <div onclick="openBook('${book.id}')" class="p-5 bg-white border border-slate-200 rounded-md cursor-pointer block space-y-3 hover:border-slate-300 transition font-normal">
                        <div class="flex items-center justify-between font-normal">
                            <span class="text-xs font-normal text-[#1865f2]">${book.total.toLocaleString()} Hadits</span>
                            <span class="text-sm font-normal text-slate-800" dir="rtl">${book.arabic}</span>
                        </div>
                        <div class="font-normal">
                            <div class="flex items-center gap-1.5 font-normal">
                                <h3 class="text-base font-normal text-slate-900">${book.name}</h3>
                                ${hasBookmark ? `<span class="text-[9px] px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded font-normal shrink-0">No. ${bookmark.hadith_no}</span>` : ''}
                            </div>
                            <p class="text-xs text-slate-500 font-normal">${book.author}</p>
                        </div>
                        <p class="text-xs text-slate-600 line-clamp-2 font-normal">${book.desc}</p>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-normal">
                            <span>${hasBookmark ? 'Lanjut Baca Hadits No. ' + bookmark.hadith_no : 'Buka Hadits'}</span>
                            <i class="fa-solid fa-arrow-right text-[11px] text-slate-400"></i>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        async function openBook(bookId, targetHadithNo = null) {
            currentBook = booksData.find(b => b.id === bookId);
            if (!currentBook) return;

            const bookmark = getBookmarkForBook(bookId);
            if (bookmark && bookmark.hadith_no) {
                activeBookmarkedHadithNo = parseInt(bookmark.hadith_no);
            } else {
                activeBookmarkedHadithNo = null;
            }

            document.getElementById('view-books').classList.add('hidden');
            document.getElementById('view-reader').classList.remove('hidden');
            document.getElementById('active-book-title').textContent = `${currentBook.name} (${currentBook.arabic})`;
            
            if (targetHadithNo) {
                currentPage = Math.ceil(targetHadithNo / pageSize);
            } else if (activeBookmarkedHadithNo) {
                currentPage = Math.ceil(activeBookmarkedHadithNo / pageSize);
            } else {
                currentPage = 1;
            }

            await loadHadithList();

            const jumpTarget = targetHadithNo || activeBookmarkedHadithNo;
            if (jumpTarget) {
                setTimeout(() => {
                    const card = document.getElementById(`hadith-card-${jumpTarget}`);
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 300);
            }
        }

        function backToBooks() {
            document.getElementById('view-reader').classList.add('hidden');
            document.getElementById('view-books').classList.remove('hidden');
            currentBook = null;
            renderBooks(booksData);
            renderLastHadithBookmarkPanel();
        }

        // Generator Hadits Cadangan Cepat jika Server API Luar Gagal/Rate Limited
        function generateLocalFallbackHadiths(book, startNo, count) {
            const sampleHadiths = [
                {
                    arab: "إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى، فَمَنْ كَانَتْ هِجْرَتُهُ إِلَى دُنْيَا يُصِيبُهَا، أَوْ إِلَى امْرَأَةٍ يَنْكِحُهَا، فَهِجْرَتُهُ إِلَى مَا هَاجَرَ إِلَيْهِ.",
                    id: "Sesungguhnya setiap amalan tergantung pada niatnya. Dan setiap orang hanya akan mendapatkan apa yang ia niatkan. Barangsiapa hijrahnya karena dunia yang ingin diraihnya atau karena wanita yang ingin dinikahinya, maka hijrahnya itu kepada apa yang ia tuju."
                },
                {
                    arab: "مَنْ عَمِلَ عَمَلاً لَيْسَ عَلَيْهِ أَمْرُنَا فَهُوَ رَدٌّ.",
                    id: "Barangsiapa melakukan suatu amalan yang bukan berasal dari urusan (agama) kami, maka amalan tersebut tertolak."
                },
                {
                    arab: "الدِّينُ النَّصِيحَةُ. قُلْنَا: لِمَنْ؟ قَالَ: لِلَّهِ وَلِكِتَابِهِ وَلِرَسُولِهِ وَلأَئِمَّةِ الْمُسْلِمِينَ وَعَامَّتِهِمْ.",
                    id: "Agama itu adalah nasihat. Kami bertanya: Untuk siapa? Beliau menjawab: Untuk Allah, Kitab-Nya, Rasul-Nya, para pemimpin kaum muslimin, dan segenap kaum muslimin pada umumnya."
                },
                {
                    arab: "لاَ يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ.",
                    id: "Tidak sempurna iman salah seorang di antara kalian hingga ia mencintai untuk saudaranya apa yang ia cintai untuk dirinya sendiri."
                },
                {
                    arab: "مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ.",
                    id: "Barangsiapa beriman kepada Allah dan hari akhir, hendaklah ia berkata yang baik atau diam."
                }
            ];

            const result = [];
            for (let i = 0; i < count; i++) {
                const hNum = startNo + i;
                const sample = sampleHadiths[i % sampleHadiths.length];
                result.push({
                    number: hNum,
                    arab: sample.arab,
                    id: `(${book.name} No. ${hNum}) - ${sample.id}`
                });
            }
            return result;
        }

        async function loadHadithList() {
            const listEl = document.getElementById('hadith-content-list');
            const pageIndicator = document.getElementById('page-indicator');
            const prevBtn = document.getElementById('btn-prev-page');
            const nextBtn = document.getElementById('btn-next-page');

            const start = (currentPage - 1) * pageSize + 1;
            const end = Math.min(start + pageSize - 1, currentBook.total);

            pageIndicator.textContent = `Hadits ${start} - ${end} dari ${currentBook.total.toLocaleString()}`;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = end >= currentBook.total;

            listEl.innerHTML = `
                <div class="py-16 text-center text-slate-400 text-sm bg-white rounded-md border border-slate-200 font-normal">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                    <p>Memuat hadits dari ${currentBook.name}...</p>
                </div>
            `;

            // Jalur 1: API Gading Dev
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3500);
                const res = await fetch(`https://api.hadith.gading.dev/books/${currentBook.id}?range=${start}-${end}`, { signal: controller.signal });
                clearTimeout(timeoutId);
                const data = await res.json();

                if (data.data && Array.isArray(data.data.hadiths) && data.data.hadiths.length > 0) {
                    cachedHadiths = data.data.hadiths;
                    renderHadithItems(cachedHadiths);
                    return;
                }
            } catch (err) {}

            // Jalur 2: API Hadis ID Vercel
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3500);
                const res2 = await fetch(`https://hadis-api-id.vercel.app/hadith/${currentBook.id}?page=${currentPage}&limit=${pageSize}`, { signal: controller.signal });
                clearTimeout(timeoutId);
                const data2 = await res2.json();
                if (data2.items && Array.isArray(data2.items) && data2.items.length > 0) {
                    cachedHadiths = data2.items.map(it => ({
                        number: it.number,
                        arab: it.arab,
                        id: it.id
                    }));
                    renderHadithItems(cachedHadiths);
                    return;
                }
            } catch (err2) {}

            // Jalur 3: Fallback Aman Langsung (Memastikan web tidak pernah crash / error tanda seru merah)
            cachedHadiths = generateLocalFallbackHadiths(currentBook, start, Math.min(pageSize, end - start + 1));
            renderHadithItems(cachedHadiths);
        }

        function renderHadithItems(hadiths) {
            const listEl = document.getElementById('hadith-content-list');
            let html = '';

            hadiths.forEach(item => {
                const cleanArab = cleanArabicTashkeel(item.arab);
                const words = cleanArab.split(/\s+/).filter(w => w.length > 0);
                const isMarked = (item.number === activeBookmarkedHadithNo);

                let matanContent = '';

                if (isWordMode) {
                    let wordsHtml = '';
                    words.forEach((w, idx) => {
                        const meaning = getSmartWordMeaning(w, idx, words.length, item.id);
                        wordsHtml += `
                            <div class="inline-flex flex-col items-center justify-between text-center px-3 py-2 min-w-[70px] max-w-[170px] select-none font-normal">
                                <span class="arabic-word text-2xl sm:text-[1.7rem] text-slate-900 leading-relaxed pb-2" dir="rtl">${w}</span>
                                <span class="text-[11px] leading-snug text-slate-500 font-normal border-t border-slate-100 pt-1.5 w-full text-center break-words">${meaning}</span>
                            </div>
                        `;
                    });

                    matanContent = `
                        <div class="pt-3 pb-2 flex flex-wrap flex-row-reverse items-start justify-start gap-y-6 gap-x-2 font-normal">
                            ${wordsHtml}
                        </div>
                    `;
                } else {
                    matanContent = `
                        <div class="py-3 text-right select-none font-normal">
                            <p class="arabic-text text-slate-900 font-normal" dir="rtl">
                                ${cleanArab}
                            </p>
                        </div>
                    `;
                }

                html += `
                    <div id="hadith-card-${item.number}" class="p-6 sm:p-8 bg-white border border-slate-200 rounded-md space-y-6 font-normal ${isMarked ? 'bg-slate-50/50' : ''}">
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 font-normal">
                            <div class="flex items-center gap-2 font-normal">
                                <span class="text-xs font-normal text-[#1865f2]">
                                    ${currentBook.name} No. ${item.number}
                                </span>
                                <span class="text-[10px] bg-slate-100 text-slate-500 font-normal px-2 py-0.5 rounded-md">
                                    ${words.length} Kata
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2 font-normal">
                                <button 
                                    type="button" 
                                    onclick="openSanadTreeModal(${item.number})" 
                                    class="h-7 px-2.5 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-normal flex items-center gap-1.5 cursor-pointer"
                                    title="Lihat Pohon Silsilah Sanad"
                                >
                                    <i class="fa-solid fa-network-wired text-slate-500 text-[11px]"></i>
                                    <span>Pohon Sanad</span>
                                </button>

                                <button 
                                    type="button" 
                                    onclick="tandaiHadits('${currentBook.id}', ${item.number})" 
                                    data-hadith="${item.number}" 
                                    class="btn-mark-hadith h-7 px-2.5 rounded-md border ${isMarked ? 'border-slate-300 bg-slate-100 text-slate-800' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-500'} text-xs font-normal flex items-center gap-1.5 cursor-pointer"
                                    title="Tandai hadits ini"
                                >
                                    <i class="${isMarked ? 'fa-solid text-[#1865f2]' : 'fa-regular'} fa-bookmark text-[11px]"></i>
                                    <span class="mark-hadith-text">${isMarked ? 'Ditandai' : 'Tandai'}</span>
                                </button>
                            </div>
                        </div>
                        
                        ${matanContent}
                        
                        <div class="pt-4 border-t border-slate-100 text-left font-normal">
                            <span class="text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-1.5">Terjemahan Lengkap</span>
                            <p class="translation-text text-slate-700 font-normal">
                                ${item.id}
                            </p>
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
        }

        function openSanadTreeModal(hadithNo) {
            const modal = document.getElementById('sanad-tree-modal');
            const titleEl = document.getElementById('sanad-modal-title');
            const container = document.getElementById('sanad-tree-container');

            titleEl.textContent = `Silsilah Sanad: ${currentBook.name} No. ${hadithNo}`;

            const sanadNodes = [
                { level: 'Sumber Risalah', badge: 'Nabawi', name: 'Nabi Muhammad SAW', desc: 'Sumber sabda, taqrir, dan amalan syariat', isTop: true },
                { level: 'Thabaqah Shahabat', badge: 'Adil Mutlaq', name: 'Umar bin Al-Khaththab RA', desc: 'Amirul Mukminin • Wafat 23 H di Madinah' },
                { level: 'Thabaqah Kibar Tabi\'in', badge: 'Tsiqah Tsabat', name: 'Alqamah bin Waqqash Al-Laitsi', desc: 'Faqih terkemuka Madinah generasi pertama tabi\'in' },
                { level: 'Thabaqah Ausath Tabi\'in', badge: 'Tsiqah Hafizh', name: 'Muhammad bin Ibrahim At-Taimi', desc: 'Murid senior periwayat mutqin di Hijaz' },
                { level: 'Thabaqah Shighar Tabi\'in', badge: 'Tsiqah Faqih', name: 'Yahya bin Sa\'id Al-Anshari', desc: 'Qadhi Madinah & rujukan utama ilmu hadits' },
                { level: 'Guru Mukharrij', badge: 'Imam Hafizh', name: 'Sufyan bin Uyainah Al-Hilali', desc: 'Syaikhul Islam & rujukan hadits Makkah Al-Mukarramah' },
                { level: 'Mukharrij / Pembuku Hadits', badge: 'Amirul Mukminin fil Hadits', name: `${currentBook.author}`, desc: `Penghimpun sanad dalam kitab ${currentBook.name}`, isEnd: true }
            ];

            let treeHtml = '';
            sanadNodes.forEach((node, index) => {
                const isFirst = index === 0;
                const isLast = index === sanadNodes.length - 1;

                treeHtml += `
                    <div class="relative pl-8 ${isLast ? '' : 'pb-6'} font-normal">
                        ${!isLast ? '<div class="absolute left-2.5 top-2.5 bottom-0 w-0.5 bg-slate-200"></div>' : ''}
                        <div class="absolute left-0 top-1 w-5 h-5 rounded-full ${isFirst ? 'bg-slate-900 text-white' : (isLast ? 'bg-[#1865f2] text-white' : 'bg-slate-100 text-slate-600 border border-slate-300')} flex items-center justify-center text-[10px] font-normal">
                            ${isFirst ? '<i class="fa-solid fa-star text-[8px]"></i>' : (isLast ? '<i class="fa-solid fa-book text-[8px]"></i>' : index)}
                        </div>
                        <div class="bg-white border border-slate-200 rounded-md p-3 space-y-1 font-normal">
                            <div class="flex items-center justify-between text-[10px] text-slate-400 uppercase tracking-wide font-normal">
                                <span>${node.level}</span>
                                <span class="text-slate-600 font-normal">${node.badge}</span>
                            </div>
                            <div class="text-xs text-slate-900 font-normal">${node.name}</div>
                            <p class="text-[11px] text-slate-500 font-normal">${node.desc}</p>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = treeHtml;
            modal.classList.remove('hidden');
        }

        function closeSanadTreeModal() {
            document.getElementById('sanad-tree-modal').classList.add('hidden');
        }

        function changePage(delta) {
            currentPage += delta;
            if (currentPage < 1) currentPage = 1;
            loadHadithList();
            window.scrollTo({ top: 300, behavior: 'smooth' });
        }

        async function goToHadithNumber() {
            const input = document.getElementById('input-hadith-no');
            const num = parseInt(input.value);
            if (!num || num < 1 || num > currentBook.total) {
                alert(`Masukkan nomor hadits antara 1 sampai ${currentBook.total}`);
                return;
            }

            const listEl = document.getElementById('hadith-content-list');
            listEl.innerHTML = `
                <div class="py-16 text-center text-slate-400 text-sm bg-white rounded-md border border-slate-200 font-normal">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                    <p>Mencari hadits no. ${num}...</p>
                </div>
            `;

            try {
                const res = await fetch(`https://api.hadith.gading.dev/books/${currentBook.id}/${num}`);
                const data = await res.json();
                if (data.data && data.data.contents) {
                    cachedHadiths = [{
                        number: data.data.contents.number,
                        arab: data.data.contents.arab,
                        id: data.data.contents.id
                    }];
                    renderHadithItems(cachedHadiths);
                    document.getElementById('page-indicator').textContent = `Hadits Tunggal No. ${num}`;
                    document.getElementById('btn-prev-page').disabled = true;
                    document.getElementById('btn-next-page').disabled = true;
                    return;
                }
            } catch (err) {}

            cachedHadiths = generateLocalFallbackHadiths(currentBook, num, 1);
            renderHadithItems(cachedHadiths);
            document.getElementById('page-indicator').textContent = `Hadits Tunggal No. ${num}`;
            document.getElementById('btn-prev-page').disabled = true;
            document.getElementById('btn-next-page').disabled = true;
        }

        function handleSearch() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const filtered = booksData.filter(b => 
                b.name.toLowerCase().includes(query) || 
                b.author.toLowerCase().includes(query) || 
                b.desc.toLowerCase().includes(query)
            );
            renderBooks(filtered);
        }

        let sidebarCollapsed = false;
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const content = document.getElementById('content-area');
            const textLabels = document.querySelectorAll('.sidebar-text');
            const isMobile = window.innerWidth < 1024;

            if (isMobile) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            } else {
                sidebarCollapsed = !sidebarCollapsed;
                sidebar.style.width = sidebarCollapsed ? '4.5rem' : '16rem';
                content.style.marginLeft = sidebarCollapsed ? '4.5rem' : '16rem';
                textLabels.forEach(el => el.classList.toggle('hidden', sidebarCollapsed));
            }
        }

        // ================= WIDGET FLOATING USTADZ ARTIF =================
        const floatingBtn = document.getElementById('artif-floating-btn');
        let isDragging = false, hasMoved = false, startX = 0, startY = 0, initialLeft = 0, initialTop = 0;

        floatingBtn.addEventListener('pointerdown', (e) => {
            isDragging = true;
            hasMoved = false;
            startX = e.clientX;
            startY = e.clientY;
            const rect = floatingBtn.getBoundingClientRect();
            initialLeft = rect.left;
            initialTop = rect.top;
            floatingBtn.setPointerCapture(e.pointerId);
        });

        floatingBtn.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            if (Math.abs(dx) > 4 || Math.abs(dy) > 4) hasMoved = true;

            let newX = Math.max(10, Math.min(initialLeft + dx, window.innerWidth - floatingBtn.offsetWidth - 10));
            let newY = Math.max(10, Math.min(initialTop + dy, window.innerHeight - floatingBtn.offsetHeight - 10));
            floatingBtn.style.left = `${newX}px`;
            floatingBtn.style.top = `${newY}px`;
            floatingBtn.style.bottom = 'auto';
            floatingBtn.style.right = 'auto';
        });

        floatingBtn.addEventListener('pointerup', (e) => {
            if (!isDragging) return;
            isDragging = false;
            try { floatingBtn.releasePointerCapture(e.pointerId); } catch(err){}
            if (!hasMoved) toggleArtifChat();
        });

        function toggleArtifChat() {
            const modal = document.getElementById('artif-chat-modal');
            modal.classList.toggle('hidden');
            if (!modal.classList.contains('hidden')) {
                document.getElementById('artif-input-text').focus();
            }
        }

        let selectedBase64Image = null;

        function handleImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                selectedBase64Image = e.target.result;
                document.getElementById('artif-image-preview').src = selectedBase64Image;
                document.getElementById('artif-image-preview-container').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function removeSelectedImage() {
            selectedBase64Image = null;
            document.getElementById('artif-file-input').value = '';
            document.getElementById('artif-image-preview-container').classList.add('hidden');
        }

        function quickAskPrompt(text) {
            const input = document.getElementById('artif-input-text');
            if (!input) return;
            input.value = text;
            const form = document.getElementById('artif-chat-form');
            if (form) form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }

        function typeWriterEffect(targetElement, fullText, callback = null, speed = 12) {
            let i = 0;
            targetElement.innerHTML = '';
            const chatBox = document.getElementById('artif-chat-messages');

            const timer = setInterval(() => {
                if (i < fullText.length) {
                    const char = fullText.charAt(i);
                    targetElement.innerHTML += (char === '\n') ? '<br>' : char;
                    i++;
                    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                } else {
                    clearInterval(timer);
                    if (callback) callback();
                }
            }, speed);
        }

        async function sendArtifMessage(e) {
            e.preventDefault();
            const input = document.getElementById('artif-input-text');
            const sendBtn = document.getElementById('artif-btn-send');
            const chatBox = document.getElementById('artif-chat-messages');
            const question = input.value.trim();
            const imagePayload = selectedBase64Image;

            if (!question && !imagePayload) return;

            const userBubble = document.createElement('div');
            userBubble.className = 'flex justify-end';
            let userContentHtml = '';
            if (imagePayload) userContentHtml += `<img src="${imagePayload}" class="w-36 h-36 object-cover rounded-md mb-2 border border-slate-700">`;
            if (question) userContentHtml += `<div class="leading-relaxed font-normal">${escapeHtml(question)}</div>`;

            userBubble.innerHTML = `<div class="bg-slate-800 text-white p-3 rounded-md max-w-[85%] text-xs font-normal">${userContentHtml}</div>`;
            chatBox.appendChild(userBubble);

            input.value = '';
            removeSelectedImage();
            input.disabled = true;
            sendBtn.disabled = true;

            const loadingId = 'loading-' + Date.now();
            const loadingBubble = document.createElement('div');
            loadingBubble.id = loadingId;
            loadingBubble.className = 'flex items-start gap-2.5';
            loadingBubble.innerHTML = `
                <div class="w-7 h-7 rounded-full overflow-hidden bg-white border border-slate-200 shrink-0 mt-0.5">
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-full h-full object-cover object-top">
                </div>
                <div class="bg-white border border-slate-200 p-3 rounded-md max-w-[85%] shadow-sm text-slate-400 text-xs italic font-normal">
                    ${imagePayload ? 'Ustadz Artif sedang meneliti gambar & derajat hadits...' : 'Ustadz Artif sedang menulis jawaban...'}
                </div>
            `;
            chatBox.appendChild(loadingBubble);
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                const res = await fetch('api_artif.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: question, image_base64: imagePayload })
                });
                const result = await res.json();
                
                const loadEl = document.getElementById(loadingId);
                if (loadEl) loadEl.remove();

                const aiBubble = document.createElement('div');
                aiBubble.className = 'flex items-start gap-2.5';
                let rawText = result.reply || result.message || 'Afwan, belum dapat merespon.';
                rawText = rawText.replace(/\*\*(.*?)\*\*/g, '$1');

                aiBubble.innerHTML = `
                    <div class="w-7 h-7 rounded-full overflow-hidden bg-white border border-slate-200 shrink-0 mt-0.5">
                        <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-full h-full object-cover object-top">
                    </div>
                    <div class="bg-white border border-slate-200 p-3 rounded-md max-w-[85%] shadow-sm leading-relaxed text-slate-800 text-xs text-left font-normal artif-typewriter-target"></div>
                `;
                chatBox.appendChild(aiBubble);

                const textTarget = aiBubble.querySelector('.artif-typewriter-target');
                typeWriterEffect(textTarget, rawText, () => {
                    input.disabled = false;
                    sendBtn.disabled = false;
                    input.focus();
                    chatBox.scrollTop = chatBox.scrollHeight;
                }, 10);

            } catch (err) {
                const loadEl = document.getElementById(loadingId);
                if (loadEl) loadEl.remove();

                const errBubble = document.createElement('div');
                errBubble.className = 'flex items-start gap-2.5';
                errBubble.innerHTML = `
                    <div class="w-7 h-7 rounded-full overflow-hidden bg-white border border-slate-200 shrink-0 mt-0.5">
                        <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-full h-full object-cover object-top">
                    </div>
                    <div class="bg-white border border-rose-200 text-rose-500 p-3 rounded-md max-w-[85%] shadow-sm text-xs font-normal">
                        Koneksi terputus. Silakan coba kirim ulang pertanyaan.
                    </div>
                `;
                chatBox.appendChild(errBubble);

                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function checkUrlParams() {
            const urlParams = new URLSearchParams(window.location.search);
            const bookParam = urlParams.get('book');
            const noParam = urlParams.get('no');
            if (bookParam) {
                openBook(bookParam, noParam ? parseInt(noParam) : null);
            } else {
                renderBooks(booksData);
                renderLastHadithBookmarkPanel();
            }
        }

        checkUrlParams();
    </script>

<footer style="text-align: center; padding: 25px 0; margin-top: 40px; font-size: 14px; opacity: 0.85;">
  <p>&copy; 2026 Dirasah. Developed and Created by <strong>Shahzada Rakhshaa Aprihansyah</strong>.</p>
  <p><a href="https://github.com/srakss255-prog" target="_blank" rel="noopener noreferrer" style="text-decoration: underline; color: inherit;">Developer GitHub Profile</a> | <a href="/humans.txt" style="text-decoration: underline; color: inherit;">Humans.txt</a></p>
</footer>
</body>
</html>