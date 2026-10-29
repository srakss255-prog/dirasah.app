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
            $nama_user = !empty($curUser['nama']) ? $curUser['nama'] : (!empty($curUser['username']) ?$curUser['username'] : $nama_user);$foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
        }
    } catch (Exception $e) {$foto_user = null;
    }
}

$banner_v = file_exists('assets/pik1.png') ? filemtime('assets/pik1.png') : time();
$fav_v    = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
$logo_v   = file_exists('assets/dirasah.png') ? filemtime('assets/dirasah.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitab &amp; Syarah - Dirasah.id</title>
    <!-- Favicon Tab Browser (putih.png) -->
    <link rel="icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="apple-touch-icon" href="assets/putih.png?v=<?= $fav_v; ?>">

    <!-- Konfigurasi PWA (Progressive Web App) -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1865f2">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Dirasah.id">

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

                    <a href="hadits.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/hadis.png" alt="Hadits" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kutubut Tis'ah &amp; Hadits</span>
                    </a>

                    <!-- Menu Kitab & Syarah (Aktif) -->
                    <a href="kitab.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-[#1865f2] bg-blue-50/60">
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

    <!-- Konten Utama Halaman Kitab -->
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
                        placeholder="Cari kitab, ulama pengarang, atau tema studi (tekan Enter)..." 
                        oninput="handleSearch()" 
                        onkeydown="handleGlobalSearch(event)"
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
            
            <!-- Banner Khazanah Kitab: Menggunakan pik1.png -->
            <div class="relative w-full rounded-md overflow-hidden shadow-sm min-h-[160px] sm:min-h-[190px] flex flex-col justify-center p-6 sm:p-8 text-white bg-slate-950 font-normal">
                <img src="assets/pik1.png?v=<?= $banner_v; ?>" alt="Banner Kitab" class="absolute inset-0 w-full h-full object-cover object-center z-0 opacity-40">
                <div class="absolute inset-0 bg-slate-950/50 z-0 font-normal"></div>

                <div class="relative z-10 max-w-2xl space-y-1.5 font-normal">
                    <h1 class="text-2xl sm:text-3xl font-normal tracking-wide text-white leading-tight">
                        Khazanah Kitab Kuning &amp; Syarah Turots
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 font-normal italic leading-relaxed">
                        "Barangsiapa menempuh suatu jalan untuk mencari ilmu, maka Allah akan memudahkan baginya jalan menuju surga."
                    </p>
                    <p class="text-[11px] text-slate-300 font-normal not-italic">
                        (HR. Muslim No. 2699)
                    </p>
                </div>
            </div>

            <!-- Tab Kategori Disiplin Ilmu -->
            <div id="category-tabs" class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 font-normal">
                <button onclick="filterCategory('all')" id="tab-all" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-[#1865f2] text-white cursor-pointer">Semua Kitab</button>
                <button onclick="filterCategory('Fikih')" id="tab-fikih" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">Fikih</button>
                <button onclick="filterCategory('Ushul Fikih')" id="tab-ushul" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">Ushul Fikih &amp; Qawa'id</button>
                <button onclick="filterCategory('Aqidah')" id="tab-aqidah" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">Aqidah &amp; Tauhid</button>
                <button onclick="filterCategory('Tata Bahasa')" id="tab-nahwu" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">Nahwu &amp; Shorof</button>
                <button onclick="filterCategory('Akhlak & Tasawuf')" id="tab-akhlak" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">Akhlak &amp; Tasawuf</button>
                <button onclick="filterCategory('Hadits & Ulumul Hadits')" id="tab-ulumul" class="cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">Ulumul Hadits</button>
            </div>

            <!-- Tampilan 1: Katalog Kitab -->
            <div id="view-books" class="space-y-4 font-normal">
                <div class="flex items-center justify-between font-normal">
                    <h2 class="text-sm font-normal text-slate-800">Daftar Kitab Turots</h2>
                    <span id="books-count" class="text-xs text-slate-400 font-normal">Memuat katalog kitab...</span>
                </div>

                <div id="books-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 font-normal">
                    <div class="col-span-full py-16 text-center text-slate-400 text-sm bg-white border border-slate-200 rounded-md font-normal">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                        <p>Menghubungkan ke Khazanah Kitab...</p>
                    </div>
                </div>
            </div>

            <!-- Tampilan 2: Pembaca Matan & Syarah Bab Terpilih -->
            <div id="view-reader" class="space-y-4 hidden font-normal">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-slate-200 rounded-md p-4 font-normal">
                    <div class="flex items-center gap-3 font-normal">
                        <button onclick="backToBooks()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-md text-xs font-normal text-slate-700 flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span>Daftar Kitab</span>
                        </button>
                        <div>
                            <h2 id="active-book-title" class="text-sm font-normal text-slate-900">-</h2>
                            <p id="active-book-author" class="text-xs text-slate-500 font-normal">-</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 font-normal">
                        <span class="text-xs text-slate-500 font-normal">Pilih Bab:</span>
                        <select id="chapter-select" onchange="loadChapterContent()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-800 outline-none focus:border-[#1865f2] cursor-pointer font-normal">
                            <!-- Bab otomatis dimuat di sini -->
                        </select>
                    </div>
                </div>

                <!-- Konten Matan & Terjemahan -->
                <div id="reader-content" class="space-y-4 font-normal">
                    <!-- Dimuat oleh JavaScript -->
                </div>
            </div>

        </main>
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
        
        <!-- Header Chat -->
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

        <!-- Area Percakapan: Sambutan & Prompt Saran -->
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

        <!-- Wadah Preview Gambar Sebelum Dikirim -->
        <div id="artif-image-preview-container" class="hidden px-4 py-2 bg-slate-100/80 border-t border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <div class="flex items-center gap-2.5 font-normal">
                <img id="artif-image-preview" src="" class="w-10 h-10 object-cover rounded-md border border-slate-200">
                <span class="text-[11px] text-slate-600 font-normal">Foto naskah siap ditelaah</span>
            </div>
            <button type="button" onclick="removeSelectedImage()" class="text-slate-400 hover:text-red-500 text-lg leading-none p-1 cursor-pointer">&times;</button>
        </div>

        <!-- Input Box Chat -->
        <div class="p-3 bg-white border-t border-slate-200 shrink-0 font-normal">
            <input type="file" id="artif-file-input" accept="image/*" class="hidden" onchange="handleImageSelect(event)">
            
            <form id="artif-chat-form" onsubmit="sendArtifMessage(event)" class="flex items-center gap-2 m-0 font-normal">
                <button 
                    type="button" 
                    onclick="document.getElementById('artif-file-input').click()" 
                    class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0 cursor-pointer" 
                    title="Unggah foto lembaran kitab / hadits"
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
        let allKitabs = [];
        let activeCategory = 'all';
        let currentKitab = null;

        const API_CATALOG_URL = 'https://raw.githubusercontent.com/raghib88/kitab-kuning-api/main/data/catalog.json';
        const API_BASE_CONTENT = 'https://raw.githubusercontent.com/raghib88/kitab-kuning-api/main/data/books/';

        const fallbackCatalog = [
            { id: 'safinah', title: 'Safinatun Najah', arabic: 'سفينة النجاة', category: 'Fikih', author: 'Syaikh Salim bin Sumair Al-Hadhrami', total_chapters: 12, desc: 'Matan fikih dasar mazhab Syafi\'i mengenai rukun ibadah, wudhu, dan sholat.' },
            { id: 'taqrib', title: 'Matan Al-Ghayah wat Taqrib', arabic: 'متن الغاية والتقريب', category: 'Fikih', author: 'Al-Qadhi Abu Syuja\' Al-Isfahani', total_chapters: 18, desc: 'Rujukan matan fikih komprehensif mazhab Syafi\'i.' },
            { id: 'fathul_qarib', title: 'Fathul Qarib Al-Mujib', arabic: 'فتح القريب المجيب', category: 'Fikih', author: 'Syaikh Ibnu Qasim Al-Ghazi', total_chapters: 22, desc: 'Syarah masyhur atas Matan Taqrib karya Abu Syuja\'.' },
            { id: 'fathul_muin', title: 'Fathul Mu\'in bi Syarhi Qurratil \'Ain', arabic: 'فتح المعين بشرح قرة العين', category: 'Fikih', author: 'Syaikh Zainuddin Al-Malibari', total_chapters: 16, desc: 'Kitab fikih tingkat lanjut pegangan utama ulama nusantara.' },
            { id: 'waraqat', title: 'Matan Al-Waraqat', arabic: 'متن الورقات', category: 'Ushul Fikih', author: 'Imam Al-Haramain Al-Juwaini', total_chapters: 10, desc: 'Dasar-dasar ushul fikih, pembagian dalil, ijma\', qiyas, dan istidlal.' },
            { id: 'qawaid_fiqhiyyah', title: 'Al-Qawa\'id Al-Fiqhiyyah', arabic: 'القواعد الفقهية', category: 'Ushul Fikih', author: 'As-Sayyid Ahmad Az-Zarqa', total_chapters: 8, desc: 'Kaidah-kaidah induk hukum fikih Islam (al-umuru bi maqashidiha, dll).' },
            { id: 'ajurumiyyah', title: 'Matan Al-Ajurumiyyah', arabic: 'متن الآجرومية', category: 'Tata Bahasa', author: 'Ibnu Ajurrum Ash-Shanhaji', total_chapters: 14, desc: 'Fondasi gramatika bahasa Arab: kaidah nahwu, i\'rab, dan tarkib.' },
            { id: 'imrithi', title: 'Nazham Al-Imrithi', arabic: 'نظم العمريطي', category: 'Tata Bahasa', author: 'Syaikh Syarafuddin Yahya Al-Imrithi', total_chapters: 16, desc: 'Bait-bait nazham gramatika nahwu pendalaman Ajurumiyyah.' },
            { id: 'alfiyyah', title: 'Alfiyyah Ibnu Malik', arabic: 'ألفية ابن مالك', category: 'Tata Bahasa', author: 'Imam Ibnu Malik', total_chapters: 25, desc: 'Seribu bait kaidah nahwu dan shorof tingkat tinggi.' },
            { id: 'kailani', title: 'Tashrif Al-Izzi (Al-Kailani)', arabic: 'تصريف العزي - الكيلاني', category: 'Tata Bahasa', author: 'Syaikh Abu Al-Hasan Ali Al-Kailani', total_chapters: 12, desc: 'Studi perubahan bentuk kata (shorof/morfologi) bahasa Arab.' },
            { id: 'aqidatul_awam', title: 'Aqidatul Awam', arabic: 'عقيدة العوام', category: 'Aqidah', author: 'Syaikh Ahmad Al-Marzuqi', total_chapters: 9, desc: 'Nazham aqidah Ahlus Sunnah wal Jama\'ah dan sifat 20.' },
            { id: 'jawharatut_tauhid', title: 'Jauharatut Tauhid', arabic: 'جوهرة التوحيد', category: 'Aqidah', author: 'Imam Ibrahim Al-Laqqani', total_chapters: 15, desc: 'Bait mutiara tauhid Asy\'ariyyah tentang teologi dan kenabian.' },
            { id: 'tijanud_darari', title: 'Tijan Ad-Darari', arabic: 'تيجان الدراري', category: 'Aqidah', author: 'Syaikh Nawawi Al-Bantani', total_chapters: 7, desc: 'Syarah akidah tauhid dan keimanan karya ulama besar Banten.' },
            { id: 'talimul_mutaallim', title: 'Ta\'limul Muta\'allim', arabic: 'تعليم المتعلم', category: 'Akhlak & Tasawuf', author: 'Imam Az-Zarnuji', total_chapters: 13, desc: 'Etika, metode belajar, dan adab menuntut ilmu yang berkah.' },
            { id: 'bidayatul_hidayah', title: 'Bidayatul Hidayah', arabic: 'بداية الهداية', category: 'Akhlak & Tasawuf', author: 'Hujjatul Islam Imam Al-Ghazali', total_chapters: 11, desc: 'Panduan ibadah harian, adab pergaulan, dan menjauhi maksiat.' },
            { id: 'al_hikam', title: 'Al-Hikam Al-Atha\'iyyah', arabic: 'الحكم العطائية', category: 'Akhlak & Tasawuf', author: 'Ibnu Athaillah As-Sakandari', total_chapters: 20, desc: 'Untaian kalam hikmah sufistik pembersih hati dan tawakal.' },
            { id: 'nashaihul_ibad', title: 'Nashaihul \'Ibad', arabic: 'نصائح العباد', category: 'Akhlak & Tasawuf', author: 'Syaikh Nawawi Al-Bantani', total_chapters: 10, desc: 'Kumpulan nasihat penyejuk iman dan tata krama hamba.' },
            { id: 'baiquniyyah', title: 'Manzhumah Al-Baiquniyyah', arabic: 'منظومة البيقونية', category: 'Hadits & Ulumul Hadits', author: 'Syaikh Thaha Al-Baiquni', total_chapters: 6, desc: 'Nazham pokok istilah ilmu hadits: shahih, hasan, dhaif, mawdhu\'.' }
        ];

        async function fetchKitabCatalog() {
            try {
                const response = await fetch(API_CATALOG_URL);
                if (response.ok) {
                    const data = await response.json();
                    if (Array.isArray(data) && data.length > 0) {
                        allKitabs = data;
                        renderBooks(allKitabs);
                        return;
                    }
                }
            } catch (err) {
                console.warn("Menggunakan katalog kitab cadangan lokal:", err);
            }

            allKitabs = fallbackCatalog;
            renderBooks(allKitabs);
        }

        function renderBooks(list) {
            const container = document.getElementById('books-grid');
            const countEl = document.getElementById('books-count');
            
            countEl.textContent = `Menampilkan ${list.length} Kitab Turots`;

            if (list.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full py-16 text-center text-slate-400 text-sm bg-white border border-slate-200 rounded-md font-normal">
                        <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
                        <p>Tidak ada kitab yang sesuai dengan pencarian atau filter.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            list.forEach(item => {
                html += `
                    <div onclick="openKitab('${item.id}')" class="p-5 bg-white border border-slate-200 rounded-md cursor-pointer block space-y-3 hover:border-slate-300 font-normal">
                        <div class="flex items-center justify-between font-normal">
                            <span class="text-xs font-normal text-[#1865f2]">${item.category}</span>
                            <span class="text-sm font-normal text-slate-800" dir="rtl">${item.arabic}</span>
                        </div>
                        <div class="font-normal">
                            <h3 class="text-base font-normal text-slate-900">${item.title}</h3>
                            <p class="text-xs text-slate-500 font-normal">${item.author}</p>
                        </div>
                        <p class="text-xs text-slate-600 line-clamp-2 font-normal">${item.desc}</p>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-normal">
                            <span>Baca Matan &amp; Syarah</span>
                            <i class="fa-solid fa-arrow-right text-[11px] text-slate-400"></i>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function filterCategory(cat) {
            activeCategory = cat;
            
            document.querySelectorAll('.cat-btn').forEach(btn => {
                btn.className = "cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer";
            });

            const activeBtnMap = {
                'all': 'tab-all',
                'Fikih': 'tab-fikih',
                'Ushul Fikih': 'tab-ushul',
                'Aqidah': 'tab-aqidah',
                'Tata Bahasa': 'tab-nahwu',
                'Akhlak & Tasawuf': 'tab-akhlak',
                'Hadits & Ulumul Hadits': 'tab-ulumul'
            };

            const targetId = activeBtnMap[cat] || 'tab-all';
            const btn = document.getElementById(targetId);
            if (btn) {
                btn.className = "cat-btn px-3.5 py-1.5 rounded-md text-xs font-normal bg-[#1865f2] text-white cursor-pointer";
            }

            handleSearch();
        }

        function handleSearch() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const filtered = allKitabs.filter(k => {
                const matchCategory = activeCategory === 'all' || k.category.includes(activeCategory);
                const matchQuery = k.title.toLowerCase().includes(query) || 
                                   k.author.toLowerCase().includes(query) || 
                                   k.desc.toLowerCase().includes(query) || 
                                   k.arabic.includes(query);
                return matchCategory && matchQuery;
            });

            renderBooks(filtered);
        }

        function handleGlobalSearch(event) {
            if (event.key === 'Enter') {
                const keyword = document.getElementById('search-input').value.trim().toLowerCase();
                if (!keyword) return;

                if (keyword.includes('surah') || keyword.includes('quran') || keyword.includes('ayat') || keyword.includes('fatihah')) {
                    window.location.href = 'quran.php';
                } else if (keyword.includes('hadits') || keyword.includes('bukhari') || keyword.includes('muslim') || keyword.includes('tirmidzi')) {
                    window.location.href = 'hadits.php';
                } else if (keyword.includes('doa') || keyword.includes('dzikir')) {
                    window.location.href = 'doa.php';
                } else if (keyword.includes('sholat')) {
                    window.location.href = 'sholat.php';
                } else if (keyword.includes('kiblat') || keyword.includes('arah')) {
                    window.location.href = 'kiblat.php';
                } else {
                    handleSearch();
                }
            }
        }

        async function openKitab(kitabId) {
            currentKitab = allKitabs.find(k => k.id === kitabId);
            if (!currentKitab) return;

            document.getElementById('view-books').classList.add('hidden');
            document.getElementById('category-tabs').classList.add('hidden');
            document.getElementById('view-reader').classList.remove('hidden');

            document.getElementById('active-book-title').textContent = `${currentKitab.title} (${currentKitab.arabic})`;
            document.getElementById('active-book-author').textContent = `${currentKitab.author} • ${currentKitab.category}`;

            const chapterSelect = document.getElementById('chapter-select');
            const readerContent = document.getElementById('reader-content');

            chapterSelect.innerHTML = '<option>Memuat daftar bab...</option>';
            readerContent.innerHTML = `
                <div class="py-16 text-center text-slate-400 text-sm bg-white border border-slate-200 rounded-md font-normal">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                    <p>Memuat naskah matan &amp; syarah...</p>
                </div>
            `;

            try {
                const res = await fetch(`${API_BASE_CONTENT}${kitabId}.json`);
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.chapters && data.chapters.length > 0) {
                        currentKitab.loadedChapters = data.chapters;
                        renderChaptersDropdown(data.chapters);
                        loadChapterContent();
                        return;
                    }
                }
            } catch (e) {
                console.warn("Katalog bab eksternal tidak merespon, menyusun bab otomatis...", e);
            }

            currentKitab.loadedChapters = generateDefaultChapters(currentKitab);
            renderChaptersDropdown(currentKitab.loadedChapters);
            loadChapterContent();
        }

        function renderChaptersDropdown(chapters) {
            const chapterSelect = document.getElementById('chapter-select');
            let opts = '';
            chapters.forEach((chap, idx) => {
                opts += `<option value="${idx}">Bab ${idx + 1}: ${chap.title}</option>`;
            });
            chapterSelect.innerHTML = opts;
        }

        function loadChapterContent() {
            if (!currentKitab || !currentKitab.loadedChapters) return;
            const select = document.getElementById('chapter-select');
            const chapterIndex = parseInt(select.value) || 0;
            const chap = currentKitab.loadedChapters[chapterIndex];

            const container = document.getElementById('reader-content');
            container.innerHTML = `
                <div class="p-6 sm:p-8 bg-white border border-slate-200 rounded-md space-y-6 font-normal">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 font-normal">
                        <span class="text-xs font-normal text-[#1865f2]">
                            ${chap.title}
                        </span>
                        <span class="text-xs text-slate-400 font-normal">
                            Naskah Matan &amp; Syarah Turots
                        </span>
                    </div>

                    <div class="pt-2 text-right select-none font-normal">
                        <p class="arabic-text text-slate-900 font-normal" dir="rtl">
                            ${chap.matan_arab}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-left font-normal">
                        <span class="text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-1.5">Terjemahan Makna &amp; Faedah Bab</span>
                        <p class="translation-text text-slate-700 font-normal">
                            ${chap.terjemahan}
                        </p>
                    </div>
                </div>
            `;
            window.scrollTo({ top: 220, behavior: 'auto' });
        }

        function generateDefaultChapters(kitab) {
            return [
                {
                    title: `Muqaddimah & Bab Awal (${kitab.title})`,
                    matan_arab: `بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ. الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ، وَبِهِ نَسْتَعِينُ عَلَى أُمُورِ الدُّنْيَا وَالدِّينِ، وَصَلَّى اللَّهُ وَسَلَّمَ عَلَى سَيِّدِنَا مُحَمَّدٍ خَاتَمِ النَّبِيِّينَ، وَعَلَى آلِهِ وَصَحْبِهِ أَجْمَعِينَ.`,
                    terjemahan: `Dengan menyebut nama Allah Yang Maha Pengasih lagi Maha Penyayang. Segala puji bagi Allah, Rabb semesta alam, dan kepada-Nya kita memohon pertolongan atas segala urusan dunia dan agama. Shalawat dan salam semoga senantiasa tercurah kepada junjungan kita Nabi Muhammad penutup para Nabi, serta kepada keluarga dan sahabat beliau sekalian.`
                },
                {
                    title: `Bab Pembahasan Pokok & Kaidah`,
                    matan_arab: `فَصْلٌ: فِي بَيَانِ الْأَحْكَامِ وَالْقَوَاعِدِ الْمُعْتَمَدَةِ فِي هَذَا الْبَابِ، وَالْأَصْلُ فِي ذَلِكَ الْكِتَابُ وَالسُّنَّةُ وَإِجْمَاعُ الْأُمَّةِ، لِيَعْلَمَ الْمُبْتَدِئُ وَيَسْتَحْضِرَ الْمُنْتَهِي مَا يَجِبُ عَلَيْهِ شَرْعًا.`,
                    terjemahan: `Fasal: Mengenai penjelasan hukum-hukum dan kaidah-kaidah yang menjadi pegangan utama dalam bab ini. Pondasi dalam menetapkan hukum ini bersumber dari Al-Qur'an, As-Sunnah, dan Ijma' para ulama, agar penuntut ilmu tingkat pemula dapat memahaminya dan penuntut ilmu lanjutan dapat senantiasa mengingat kewajiban syar'i.`
                },
                {
                    title: `Fasal Syarat, Rukun & Kaidah Amaliyah`,
                    matan_arab: `وَشُرُوطُ ذَلِكَ وَأَرْكَانُهُ مَبْنِيَّةٌ عَلَى التَّحْقِيقِ وَالْإِخْلَاصِ لِلَّهِ تَعَالَى، مَعَ اتِّبَاعِ سُنَّةِ رَسُولِ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ فِي الْأَقْوَالِ وَالْأَفْعَالِ وَالْمَقَاصِدِ.`,
                    terjemahan: `Syarat-syarat dan rukun amalan tersebut berlandaskan pada ketelitian dan keikhlasan semata karena Allah Ta'ala, serta senantiasa mengikuti sunnah Rasulullah shallallahu 'alaihi wasallam baik dalam ucapan, perbuatan, maupun niat tujuan.`
                }
            ];
        }

        function backToBooks() {
            document.getElementById('view-reader').classList.add('hidden');
            document.getElementById('view-books').classList.remove('hidden');
            document.getElementById('category-tabs').classList.remove('hidden');
            currentKitab = null;
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

        // Logika Drag & Drop Tombol Floating
        const floatingBtn = document.getElementById('artif-floating-btn');
        let isDragging = false;
        let hasMoved = false;
        let startX = 0, startY = 0;
        let initialLeft = 0, initialTop = 0;

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

            if (Math.abs(dx) > 4 || Math.abs(dy) > 4) {
                hasMoved = true;
            }

            let newX = initialLeft + dx;
            let newY = initialTop + dy;

            const padding = 10;
            const maxX = window.innerWidth - floatingBtn.offsetWidth - padding;
            const maxY = window.innerHeight - floatingBtn.offsetHeight - padding;

            newX = Math.max(padding, Math.min(newX, maxX));
            newY = Math.max(padding, Math.min(newY, maxY));

            floatingBtn.style.left = `${newX}px`;
            floatingBtn.style.top = `${newY}px`;
            floatingBtn.style.bottom = 'auto';
            floatingBtn.style.right = 'auto';
        });

        floatingBtn.addEventListener('pointerup', (e) => {
            if (!isDragging) return;
            isDragging = false;
            try {
                floatingBtn.releasePointerCapture(e.pointerId);
            } catch (err) {}

            if (!hasMoved) {
                toggleArtifChat();
            }
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
            if (form) {
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        }

        // Animasi mengetik teks karakter per karakter
        function typeWriterEffect(targetElement, fullText, callback = null, speed = 12) {
            let i = 0;
            targetElement.innerHTML = '';
            const chatBox = document.getElementById('artif-chat-messages');

            const timer = setInterval(() => {
                if (i < fullText.length) {
                    const char = fullText.charAt(i);
                    targetElement.innerHTML += (char === '\n') ? '<br>' : char;
                    i++;
                    if (chatBox) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
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
            if (imagePayload) {
                userContentHtml += `<img src="${imagePayload}" class="w-36 h-36 object-cover rounded-md mb-2 border border-slate-700">`;
            }
            if (question) {
                userContentHtml += `<div class="leading-relaxed font-normal">${escapeHtml(question)}</div>`;
            }

            userBubble.innerHTML = `
                <div class="bg-slate-800 text-white p-3 rounded-md max-w-[85%] text-xs font-normal">
                    ${userContentHtml}
                </div>
            `;
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
                    ${imagePayload ? 'Ustadz Artif sedang membaca naskah & menelaah ibarat...' : 'Ustadz Artif sedang menulis jawaban...'}
                </div>
            `;
            chatBox.appendChild(loadingBubble);
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                const res = await fetch('api_artif.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        message: question,
                        image_base64: imagePayload 
                    })
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
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Pendaftaran PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then((reg) => console.log('Dirasah PWA aktif:', reg.scope))
                    .catch((err) => console.warn('PWA gagal terdaftar:', err));
            });
        }

        fetchKitabCatalog();
    </script>
</body>
</html>