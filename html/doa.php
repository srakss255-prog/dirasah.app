<?php
require_once 'config/db.php';
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

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'Pengguna Dirasah';
$foto_user = null;

try {
    $qUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $qUser->execute([$user_id]);
    $curUser = $qUser->fetch(PDO::FETCH_ASSOC);
    if ($curUser) {
        $nama_user = !empty($curUser['nama']) ? $curUser['nama'] : (!empty($curUser['username']) ? $curUser['username'] : $nama_user);
        $foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
    }
} catch (Exception $e) {
    $foto_user = null;
}

$banner_v = file_exists('assets/pik3.png') ? filemtime('assets/pik3.png') : time();
$fav_v    = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
$logo_v   = file_exists('assets/dirasah.png') ? filemtime('assets/dirasah.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doa &amp; Dzikir - Dirasah.id</title>
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
            font-size: 1.6rem;
            line-height: 2.3;
            letter-spacing: 0;
            word-spacing: normal;
            direction: rtl;
            text-align: right;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1;
            font-weight: 400 !important;
        }

        .latin-text {
            font-size: 0.85rem;
            line-height: 1.6;
            color: #64748b;
            font-style: italic;
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

        a, button, .prompt-btn, .category-tab {
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

                    <a href="hadits.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
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

                    <!-- Menu Doa & Dzikir (Aktif) -->
                    <a href="doa.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-[#1865f2] bg-blue-50/60">
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

    <!-- Konten Utama Doa & Dzikir -->
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
                        placeholder="Cari doa (contoh: Istighfar, Ruku', Ayat Kursi, Sapu Jagad)..." 
                        oninput="handleSearch()" 
                        onkeydown="handleGlobalSearch(event)"
                        class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-md outline-none focus:border-[#1865f2] focus:bg-white text-slate-900 font-normal"
                    >
                </div>
            </div>

            <!-- Avatar Profil -->
            <div class="flex items-center font-normal">
                <a href="profil.php" class="flex items-center gap-3 px-2 py-1.5 rounded-md hover:bg-slate-100 transition group font-normal" title="Buka Profil Saya">
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
            
            <!-- Banner Doa & Dzikir: Menggunakan assets/pik3.png -->
            <div class="relative w-full rounded-md overflow-hidden shadow-sm min-h-[160px] sm:min-h-[190px] flex flex-col justify-center p-6 sm:p-8 text-white bg-slate-950 font-normal">
                <img src="assets/pik3.png?v=<?= $banner_v; ?>" alt="Banner Doa" class="absolute inset-0 w-full h-full object-cover object-center z-0 opacity-40">
                <div class="absolute inset-0 bg-slate-950/50 z-0 font-normal"></div>

                <div class="relative z-10 max-w-2xl space-y-1.5 font-normal">
                    <h1 class="text-2xl sm:text-3xl font-normal tracking-wide text-white leading-tight">
                        Kumpulan Doa &amp; Dzikir Ma'tsur
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 font-normal italic leading-relaxed">
                        "Ingatlah, hanya dengan mengingat Allah hati menjadi tenteram."
                    </p>
                    <p class="text-[11px] text-slate-300 font-normal not-italic">
                        (QS. Ar-Ra'd: 28)
                    </p>
                </div>
            </div>

            <!-- Tab Filter Kategori Doa -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3 font-normal">
                <div class="flex flex-wrap items-center gap-2 font-normal">
                    <button onclick="setCategoryFilter('all')" id="tab-all" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-[#1865f2] text-white cursor-pointer">
                        Semua Doa
                    </button>
                    <button onclick="setCategoryFilter('harian')" id="tab-harian" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        Doa Harian
                    </button>
                    <button onclick="setCategoryFilter('dzikir')" id="tab-dzikir" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        Dzikir Pagi &amp; Petang
                    </button>
                    <button onclick="setCategoryFilter('sholat')" id="tab-sholat" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        Doa &amp; Dzikir Sholat
                    </button>
                    <button onclick="setCategoryFilter('quran')" id="tab-quran" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        Doa Al-Qur'an
                    </button>
                    <button onclick="setCategoryFilter('haji')" id="tab-haji" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        Haji &amp; Umroh
                    </button>
                </div>
                <span id="doa-count-info" class="text-xs text-slate-500 font-normal">Memuat doa...</span>
            </div>

            <!-- Daftar Kontainer Doa -->
            <div id="doa-list-container" class="space-y-4 font-normal">
                <div class="py-16 text-center text-slate-400 text-sm bg-white border border-slate-200 rounded-md font-normal">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                    <p>Memuat kumpulan doa...</p>
                </div>
            </div>

            <!-- Kontrol Paginasi -->
            <div id="pagination-wrapper" class="flex items-center justify-between pt-4 border-t border-slate-200 hidden font-normal">
                <button id="btn-prev" onclick="changePage(-1)" class="px-4 py-2 rounded-md border border-slate-200 text-xs font-normal text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-40 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Sebelumnya</span>
                </button>
                <span id="page-indicator" class="text-xs text-slate-500 font-normal">Halaman 1</span>
                <button id="btn-next" onclick="changePage(1)" class="px-4 py-2 rounded-md border border-slate-200 text-xs font-normal text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-40 flex items-center gap-2 cursor-pointer">
                    <span>Berikutnya</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
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

        <!-- Area Percakapan: Sambutan & Prompt Sesuai Saran Ayah -->
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
                <span class="text-[11px] text-slate-600 font-normal">Gambar doa siap diteliti</span>
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
                    title="Unggah foto teks doa / hadits"
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
        let allMasterDoa = [];
        let filteredDoa = [];
        let currentFilter = 'all';
        let currentPage = 1;
        const pageSize = 10;

        const masterDoaBase = [
            { judul: 'Doa Sebelum Tidur', arab: 'بِاسْمِكَ اللّهُمَّ أَحْيَا وَأَمُوتُ', latin: 'Bismika Allahumma ahyaa wa amuutu', terjemah: 'Dengan nama-Mu ya Allah, aku hidup dan aku mati.', riwayat: 'HR. Bukhari no. 6324', tag: 'harian' },
            { judul: 'Doa Bangun Tidur', arab: 'الْحَمْدُ لِلَّهِ الَّذِي أَحْيَانَا بَعْدَ مَا أَمَاتَنَا وَإِلَيْهِ النُّشُورُ', latin: 'Alhamdu lillaahil ladzii ahyaanaa ba\'da maa amaatanaa wa ilaihin nusyuur', terjemah: 'Segala puji bagi Allah yang menghidupkan kami setelah mematikan kami dan kepada-Nya kami dibangkitkan.', riwayat: 'HR. Bukhari no. 6312', tag: 'harian' },
            { judul: 'Doa Sebelum Makan', arab: 'اللَّهُمَّ بَارِكْ لَنَا فِيمَا رَزَقْتَنَا وَقِنَا عَذَابَ النَّارِ، بِسْمِ اللَّهِ', latin: 'Allahumma baarik lanaa fiimaa razaqtanaa wa qinaa \'adzaaban naar, Bismillah', terjemah: 'Ya Allah, berkahilah kami dalam rezeki yang telah Engkau berikan kepada kami dan peliharalah kami dari siksa api neraka. Dengan nama Allah.', riwayat: 'HR. Ibnu Sunni', tag: 'harian' },
            { judul: 'Doa Setelah Makan', arab: 'الْحَمْدُ لِلَّهِ الَّذِي أَطْعَمَنَا وَسَقَانَا وَجَعَلَنَا مُسْلِمِينَ', latin: 'Alhamdu lillaahil ladzii ath\'amanaa wa saqaanaa wa ja\'alanaa muslimiin', terjemah: 'Segala puji bagi Allah yang telah memberi kami makan dan minum serta menjadikan kami orang-orang muslim.', riwayat: 'HR. Abu Dawud no. 3850', tag: 'harian' },
            { judul: 'Doa Masuk Kamar Mandi', arab: 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْخُبُثِ وَالْخَبَائِثِ', latin: 'Allahumma inni a\'uudzu bika minal khubutsi wal khabaa-its', terjemah: 'Ya Allah, sesungguhnya aku berlindung kepada-Mu dari godaan setan laki-laki dan setan perempuan.', riwayat: 'HR. Bukhari & Muslim', tag: 'harian' },
            { judul: 'Doa Keluar Kamar Mandi', arab: 'غُفْرَانَكَ، الْحَمْدُ لِلَّهِ الَّذِي أَذْهَبَ عَنِّي الْأَذَى وَعَافَانِي', latin: 'Ghufraanaka, alhamdu lillaahil ladzii adzhaba \'annil adzaa wa \'aafaanii', terjemah: 'Aku memohon ampunan-Mu. Segala puji bagi Allah yang telah menghilangkan kotoran dari tubuhku dan menyehatkanku.', riwayat: 'HR. Abu Dawud & Tirmidzi', tag: 'harian' },
            { judul: 'Doa Memakai Pakaian', arab: 'الْحَمْدُ لِلَّهِ الَّذِي كَسَانِي هَذَا الثَّوْبَ وَرَزَقَنِيهِ مِنْ غَيْرِ حَوْلٍ مِنِّي وَلَا قُوَّةٍ', latin: 'Alhamdu lillaahil ladzii kasaanii haadzats tsauba wa razaqaniihi min ghairi hawlin minnii wa laa quwwah', terjemah: 'Segala puji bagi Allah yang memakaikan pakaian ini kepadaku dan memberikannya sebagai rezeki tanpa daya dan kekuatan dariku.', riwayat: 'HR. Abu Dawud no. 4023', tag: 'harian' },
            { judul: 'Doa Melepas Pakaian', arab: 'بِسْمِ اللَّهِ الَّذِي لَا إِلَهَ إِلَّا هُوَ', latin: 'Bismillaahil ladzii laa ilaaha illaa huw', terjemah: 'Dengan nama Allah yang tiada tuhan selain Dia.', riwayat: 'HR. Ibnu Sunni', tag: 'harian' },
            { judul: 'Doa Bercermin', arab: 'اللَّهُمَّ كَمَا حَسَّنْتَ خَلْقِي فَحَسِّنْ خُلُقِي', latin: 'Allahumma kamaa hassanta khalqii fahassin khuluqii', terjemah: 'Ya Allah, sebagaimana Engkau telah memperindah rupa fisikku, maka perindahlah pula akhlak budi pekertiku.', riwayat: 'HR. Ahmad', tag: 'harian' },
            { judul: 'Doa Keluar Rumah', arab: 'بِسْمِ اللَّهِ تَوَكَّلْتُ عَلَى اللَّهِ، لاَ حَوْلَ وَلاَ قُوَّةَ إِلاَّ بِاللَّهِ', latin: 'Bismillaahi tawakkaltu \'alallaah, laa hawla wa laa quwwata illaa billaah', terjemah: 'Dengan nama Allah, aku bertawakal kepada Allah. Tiada daya dan kekuatan kecuali dengan pertolongan Allah.', riwayat: 'HR. Abu Dawud no. 5095', tag: 'harian' },
            { judul: 'Doa Masuk Rumah', arab: 'بِسْمِ اللَّهِ وَلَجْنَا، وَبِسْمِ اللَّهِ خَرَجْنَا، وَعَلَى رَبِّنَا تَوَكَّلْنَا', latin: 'Bismillaahi walajnaa, wa bismillaahi kharajnaa, wa \'alaa rabbinaa tawakkalnaa', terjemah: 'Dengan nama Allah kami masuk, dan dengan nama Allah kami keluar, dan kepada Tuhan kami, kami bertawakal.', riwayat: 'HR. Abu Dawud no. 5096', tag: 'harian' },
            { judul: 'Doa Bepergian / Naik Kendaraan', arab: 'سُبْحَانَ الَّذِي سَخَّرَ لَنَا هَذَا وَمَا كُنَّا لَهُ مُقْرِنِينَ وَإِنَّا إِلَى رَبِّنَا لَمُنْقَلِبُونَ', latin: 'Subhaanal ladzii sakhkhara lanaa haadzaa wa maa kunnaa lahuu muqriniin, wa innaa ilaa rabbinaa lamunqalibuun', terjemah: 'Maha Suci Allah yang telah menundukkan semua ini bagi kami padahal kami sebelumnya tidak mampu menguasainya...', riwayat: 'QS. Az-Zukhruf: 13-14', tag: 'harian' },
            { judul: 'Ayat Kursi (Pelindung Pagi & Petang)', arab: 'اللَّهُ لَا إِلَهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ لَهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ مَنْ ذَا الَّذِي يَشْفَعُ عِنْدَهُ إِلَّا بِإِذْنِهِ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ وَلَا يُحِيطُونَ بِشَيْءٍ مِنْ عِلْمِهِ إِلَّا بِمَا شَاءَ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ وَلَا يَئُودُهُ حِفْظُهُمَا وَهُوَ الْعَلِيُّ الْعَظِيمُ', latin: 'Allahu laa ilaaha illaa huwal hayyul qayyuum...', terjemah: 'Allah, tidak ada tuhan selain Dia, Yang Maha Hidup, yang terus-menerus mengurus makhluk-Nya...', riwayat: 'HR. An-Nasa\'i (Shahih)', tag: 'dzikir' },
            { judul: 'Sayyidul Istighfar', arab: 'اللَّهُمَّ أَنْتَ رَبِّي لاَ إِلَهَ إِلاَّ أَنْتَ خَلَقْتَنِي وَأَنَا عَبْدُكَ وَأَنَا عَلَى عهدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ وَأَبُوءُ بِذَنْبِي فَاغْفِرْ لِي فَإِنَّهُ لاَ يَغْفِرُ الذُّنُوبَ إِلاَّ أَنْتَ', latin: 'Allahumma anta rabbii laa ilaaha illaa anta khalaqtanii wa anaa \'abduka...', terjemah: 'Ya Allah, Engkaulah Tuhanku, tiada tuhan selain Engkau. Engkau yang menciptakan aku dan aku adalah hamba-Mu...', riwayat: 'HR. Bukhari no. 6306', tag: 'dzikir' },
            { judul: 'Doa Perlindungan Bahaya (3x)', arab: 'بِسْمِ اللَّهِ الَّذِي لاَ يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الأَرْضِ وَلاَ فِي السَّمَاءِ وَهُوَ السَّمِيعُ العَلِيمُ', latin: 'Bismillaahil ladzii laa yadhurru ma\'asmihii syai-un fil ardhi wa laa fis samaa-i wa huwas samii\'ul \'aliim', terjemah: 'Dengan nama Allah yang bersama nama-Nya tidak ada sesuatu pun di bumi dan di langit yang dapat mencelakai...', riwayat: 'HR. Abu Dawud no. 5088', tag: 'dzikir' },
            { judul: 'Keridhoan dalam Iman (3x)', arab: 'رَضِيتُ بِاللَّهِ رَبًّا، وَبِالإِسْلاَمِ دِينًا، وَبِمُحَمَّدٍ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ نَبِيًّا', latin: 'Radhiitu billaahi rabbaa, wa bil-islaami diinaa, wa bi-muhammadin shallallaahu \'alaihi wa sallama nabiyyaa', terjemah: 'Aku ridho Allah sebagai Tuhanku, Islam sebagai agamaku, dan Muhammad shallallahu \'alaihi wasallam sebagai Nabiku.', riwayat: 'HR. Abu Dawud no. 5072', tag: 'dzikir' },
            { judul: 'Tasbih & Pujian (100x)', arab: 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ', latin: 'Subhaanallaahi wa bihamdih', terjemah: 'Maha Suci Allah dan segala puji bagi-Nya.', riwayat: 'HR. Muslim no. 2692', tag: 'dzikir' },
            { judul: 'Tahlil Tauhid (100x)', arab: 'لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ', latin: 'Laa ilaaha illallaahu wahdahuu laa syariika lah, lahul mulku wa lahul hamdu...', terjemah: 'Tiada tuhan selain Allah semata, tiada sekutu bagi-Nya. Bagi-Nya kerajaan dan segala puji...', riwayat: 'HR. Bukhari no. 3293', tag: 'dzikir' },
            { judul: 'Doa Sebelum Berwudhu', arab: 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ', latin: 'Bismillaahir rahmaanir rahiim', terjemah: 'Dengan menyebut nama Allah Yang Maha Pengasih lagi Maha Penyayang.', riwayat: 'HR. Abu Dawud no. 101', tag: 'sholat' },
            { judul: 'Doa Setelah Berwudhu', arab: 'أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، وَأَشْهَدُ أَنَّ مُحَمَّدًا عَبْدُهُ وَرَسُولُهُ، اللَّهُمَّ اجْعَلْنِي مِنَ التَّوَّابِينَ وَاجْعَلْنِي مِنَ الْمُتَطَهِّرِينَ', latin: 'Asyhadu allaa ilaaha illallaahu wahdahuu laa syariika lah, wa asyhadu anna muhammadan \'abduhuu wa rasuuluh...', terjemah: 'Aku bersaksi tiada tuhan selain Allah Yang Maha Esa... Ya Allah jadikanlah aku termasuk orang yang bertaubat dan menyucikan diri.', riwayat: 'HR. Muslim & Tirmidzi', tag: 'sholat' },
            { judul: 'Doa Menuju Masjid', arab: 'اللَّهُمَّ اجْعَلْ فِي قَلْبِي نُورًا، وَفِي لِسَانِي نُورًا، وَفِي سَمْعِي نُورًا، وَفِي بَصَرِي نُورًا', latin: 'Allahummaj\'al fii qalbii nuuraa, wa fii lisaanii nuuraa, wa fii sam\'ii nuuraa, wa fii basharii nuuraa', terjemah: 'Ya Allah, jadikanlah cahaya di dalam hatiku, cahaya pada lisanku, cahaya pada pendengaranku, dan cahaya pada penglihatanku.', riwayat: 'HR. Muslim no. 763', tag: 'sholat' },
            { judul: 'Doa Masuk Masjid', arab: 'اللَّهُمَّ افْتَحْ لِي أَبْوَابَ رَحْمَتِكَ', latin: 'Allahummaf tah lii abwaaba rahmatik', terjemah: 'Ya Allah, bukakanlah untukku pintu-pintu rahmat-Mu.', riwayat: 'HR. Muslim no. 713', tag: 'sholat' },
            { judul: 'Doa Keluar Masjid', arab: 'اللَّهُمَّ إِنِّي أَسْأَلُكَ مِنْ فَضْلِكَ', latin: 'Allahumma inni as-aluka min fadhlik', terjemah: 'Ya Allah, sesungguhnya aku memohon keutamaan dari-Mu.', riwayat: 'HR. Muslim no. 713', tag: 'sholat' },
            { judul: 'Doa Iftitah Pembuka Sholat', arab: 'اللَّهُمَّ بَاعِدْ بَيْنِي وَبَيْنَ خَطَايَايَ كَمَا بَاعَدْتَ بَيْنَ الْمَشْرِقِ وَالْمَغْرِبِ، اللَّهُمَّ نَقِّنِي مِنَ الْخَطَايَا كَمَا يُنَقَّى الثَّوْبُ الأَبْيَضُ مِنَ الدَّنَسِ، اللَّهُمَّ اغْسِلْ خَطَايَايَ بِالْمَاءِ وَالثَّلْجِ وَالْبَرَدِ', latin: 'Allahumma baa\'id bainii wa baina khathaayaaya kamaa baa\'adta bainal masyriqi wal maghrib...', terjemah: 'Ya Allah, jauhkanlah antara aku dan kesalahan-kesalahanku sebagaimana Engkau menjauhkan timur dan barat...', riwayat: 'HR. Bukhari no. 744, Muslim no. 598', tag: 'sholat' },
            { judul: 'Doa Ruku\'', arab: 'سُبْحَانَ رَبِّيَ الْعَظِيمِ وَبِحَمْدِهِ', latin: 'Subhaana rabbiyal \'azhiimi wa bihamdih', terjemah: 'Maha Suci Tuhanku Yang Maha Agung dan dengan memuji-Nya.', riwayat: 'HR. Abu Dawud no. 869', tag: 'sholat' },
            { judul: 'Doa Bangkit dari Ruku\' (I\'tidal)', arab: 'رَبَّنَا وَلَكَ الْحَمْدُ حَمْدًا كَثِيرًا طَيِّبًا مُبَارَكًا فِيهِ', latin: 'Rabbanaa wa lakal hamdu hamdan katsiiran thayyiban mubaarakan fiih', terjemah: 'Wahai Tuhan kami, bagi-Mu segala puji, pujian yang banyak, tulus lagi penuh berkah.', riwayat: 'HR. Bukhari no. 799', tag: 'sholat' },
            { judul: 'Doa Sujud', arab: 'سُبْحَانَ رَبِّيَ الأَعْلَى وَبِحَمْدِهِ', latin: 'Subhaana rabbiyal a\'laa wa bihamdih', terjemah: 'Maha Suci Tuhanku Yang Maha Tinggi dan dengan memuji-Nya.', riwayat: 'HR. Abu Dawud no. 870', tag: 'sholat' },
            { judul: 'Doa Duduk di Antara Dua Sujud', arab: 'رَبِّ اغْفِرْ لِي، وَارْحَمْنِي، وَاجْبُرْنِي، وَارْفَعْنِي، وَارْزُقْنِي، وَاهْدِنِي، وَعَافِنِي', latin: 'Rabbighfir lii, warhamnii, wajburnii, warfa\'nii, warzuqnii, wahdinii, wa \'aafinii', terjemah: 'Wahai Tuhanku ampunilah aku, sayangilah aku, cukupkanlah kekuranganku, angkatlah derajatku, berilah rezeki kepadaku, dan berilah petunjuk kepadaku.', riwayat: 'HR. Abu Dawud no. 850', tag: 'sholat' },
            { judul: 'Dzikir Istighfar Setelah Salam', arab: 'أَسْتَغْفِرُ اللَّهَ (٣x)، اللَّهُمَّ أَنْتَ السَّلاَمُ وَمِنْكَ السَّلاَمُ تَبَارَكْتَ يَا ذَا الْجَلاَلِ وَالإِكْرَامِ', latin: 'Astaghfirullaah (3x), Allahumma antas salaam wa minkas salaam tabaarakta yaa dzal jalaali wal ikraam', terjemah: 'Aku memohon ampun kepada Allah (3x). Ya Allah, Engkaulah Yang Maha Memberi Keselamatan...', riwayat: 'HR. Muslim no. 591', tag: 'sholat' },
            { judul: 'Doa Sapu Jagad (Kebaikan Dunia & Akhirat)', arab: 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ', latin: 'Rabbanaa aatinaa fid dunyaa hasanah wa fil aakhirati hasanah wa qinaa \'adzaaban naar', terjemah: 'Ya Tuhan kami, berilah kami kebaikan di dunia dan kebaikan di akhirat dan lindungilah kami dari azab neraka.', riwayat: 'QS. Al-Baqarah: 201', tag: 'quran' },
            { judul: 'Doa Memohon Tambahan Ilmu', arab: 'رَبِّ زِدْنِي عِلْمًا', latin: 'Rabbi zidnii \'ilmaa', terjemah: 'Ya Tuhanku, tambahkanlah kepadaku ilmu pengetahuan.', riwayat: 'QS. Thaha: 114', tag: 'quran' },
            { judul: 'Doa Ketetapan Iman & Hidayah', arab: 'رَبَّنَا لَا تُزِغْ قُلُوبَنَا بَعْدَ إِذْ هَدَيْتَنَا وَهَبْ لَنَا مِنْ لَدُنْكَ رَحْمَةً إِنَّكَ أَنْتَ الْوَهَّابُ', latin: 'Rabbanaa laa tuzigh quluubanaa ba\'da idz hadaitanaa wa hab lanaa mil ladunka rahmah, innaka antal wahhaab', terjemah: 'Ya Tuhan kami, janganlah Engkau condongkan hati kami kepada kesesatan setelah Engkau beri petunjuk kepada kami...', riwayat: 'QS. Ali \'Imran: 8', tag: 'quran' },
            { judul: 'Doa Pengampunan untuk Diri & Orang Tua', arab: 'رَبَّنَا اغْفِرْ لِي وَلِوَالِدَيَّ وَلِلْمُؤْمِنِينَ يَوْمَ يَقُومُ الْحِسَابُ', latin: 'Rabbanaghfir lii wa liwaalidayya wa lil-mu\'miniina yauma yaquumul hisaab', terjemah: 'Ya Tuhan kami, ampunilah dosaku dan kedua orang tuaku serta orang-orang mukmin pada hari terjadinya hisab.', riwayat: 'QS. Ibrahim: 41', tag: 'quran' },
            { judul: 'Doa Kasih Sayang untuk Kedua Orang Tua', arab: 'رَبِّ ارْحَمْهُمَا كَمَا رَبَّيَانِي صَغِيرًا', latin: 'Rabbirhamhumaa kamaa rabbayaanii shaghiiraa', terjemah: 'Wahai Tuhanku, sayangilah kedua orang tuaku sebagaimana mereka berdua telah mendidik dan mengasuhku di waktu kecil.', riwayat: 'QS. Al-Isra\': 24', tag: 'quran' },
            { judul: 'Doa Keturunan Shalih & Keluarga Penyejuk Hati', arab: 'رَبَّنَا هَبْ لَنَا مِنْ أَزْوَاجِنَا وَذُرِّيَّاتِنَا قُرَّةَ أَعْيُنٍ وَاجْعَلْنَا لِلْمُتَّقِينَ إِمَامًا', latin: 'Rabbanaa hab lanaa min azwaajinaa wa dzurriyyaatinaa qurrata a\'yunin waj\'alnaa lil-muttaqiina imaamaa', terjemah: 'Ya Tuhan kami, anugerahkanlah kepada kami pasangan kami dan keturunan kami sebagai penyenang hati...', riwayat: 'QS. Al-Furqan: 74', tag: 'quran' },
            { judul: 'Doa Kelapangan Dada & Kemudahan Urusan', arab: 'رَبِّ اشْرَحْ لِي صَدْرِي وَيَسِّرْ لِي أَمْرِي وَاحْلُلْ عُقْدَةً مِنْ لِسَانِي يَفْقَهُوا قَوْلِي', latin: 'Rabbisyrah lii shadrii wa yassir lii amrii wahlul \'uqdatam mil lisaanii yafqahuu qawlii', terjemah: 'Ya Tuhanku, lapangkanlah dadaku, mudahkanlah untukku urusanku, dan lepaskanlah kekakuan dari lidahku...', riwayat: 'QS. Thaha: 25-28', tag: 'quran' },
            { judul: 'Doa Taubat & Penyesalan Nabi Adam AS', arab: 'رَبَّنَا ظَلَمْنَا أَنْفُسَنَا وَإِنْ لَمْ تَغْفِرْ لَنَا وَتَرْحَمْنَا لَنَكُونَنَّ مِنَ الْخَاسِرِينَ', latin: 'Rabbanaa zhalamnaa anfusanaa wa illam taghfir lanaa wa tarhamnaa lanakuunanna minal khaasiriin', terjemah: 'Ya Tuhan kami, kami telah menzalimi diri kami sendiri. Jika Engkau tidak mengampuni kami dan memberi rahmat kepada kami, tentulah kami termasuk orang yang rugi.', riwayat: 'QS. Al-A\'raf: 23', tag: 'quran' },
            { judul: 'Doa Memohon Ketabahan & Kemenangan', arab: 'رَبَّنَا أَفْرِغْ عَلَيْنَا صَبْرًا وَثَبِّتْ أَقْدَامَنَا وَانْصُرْنَا عَلَى الْقَوْمِ الْكَافِرِينَ', latin: 'Rabbanaa afrigh \'alainaa shabraw wa tsabbit aqdaamanaa wanshurnaa \'alal qawmil kaafiriin', terjemah: 'Ya Tuhan kami, limpahkanlah kesabaran kepada kami, kukuhkanlah langkah kami dan tolonglah kami menghadapi orang-orang kafir.', riwayat: 'QS. Al-Baqarah: 250', tag: 'quran' },
            { judul: 'Talbiyah Haji & Umroh', arab: 'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ، لَبَّيْكَ لاَ شَرِيكَ لَكَ لَبَّيْكَ، إِنَّ الْحَمْدَ وَالنِّعْمَةَ لَكَ وَالْمُلْكَ لاَ شَرِيكَ لَكَ', latin: 'Labbaikallaahumma labbaika, labbaika laa syariika laka labbaik...', terjemah: 'Aku penuhi panggilan-Mu ya Allah, aku penuhi panggilan-Mu. Tiada sekutu bagi-Mu...', riwayat: 'HR. Bukhari & Muslim', tag: 'haji' },
            { judul: 'Doa Saat Menatap Ka\'bah', arab: 'اللَّهُمَّ زِدْ هَذَا الْبَيْتَ تَشْرِيفًا وَتَعْظِيمًا وَتَكْرِيمًا وَمَهَابَةً', latin: 'Allahumma zid haadzal baita tasyriifan wa ta\'zhiiman wa takriiman wa mahaabah', terjemah: 'Ya Allah, tambahkanlah kemuliaan, keagungan, kehormatan, dan wibawa pada Baitullah ini.', riwayat: 'HR. Syafi\'i & Al-Baihaqi', tag: 'haji' },
            { judul: 'Doa Thawaf di Rukun Yamani - Hajar Aswad', arab: 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ', latin: 'Rabbanaa aatinaa fid dunyaa hasanah wa fil aakhirati hasanah wa qinaa \'adzaaban naar', terjemah: 'Wahai Tuhan kami, berikanlah kami kebaikan di dunia dan kebaikan di akhirat serta peliharalah kami dari siksa neraka.', riwayat: 'HR. Abu Dawud no. 1892', tag: 'haji' },
            { judul: 'Doa Hari Arafah', arab: 'لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ', latin: 'Laa ilaaha illallaahu wahdahuu laa syariika lah, lahul mulku wa lahul hamdu wa huwa \'alaa kulli syai-in qadiir', terjemah: 'Tiada tuhan selain Allah semata, tiada sekutu bagi-Nya. Bagi-Nya kerajaan dan segala puji...', riwayat: 'HR. At-Tirmidzi no. 3585', tag: 'haji' }
        ];

        async function fetchAllDoa() {
            allMasterDoa = [...masterDoaBase];

            try {
                const resEquran = await fetch('https://equran.id/api/doa');
                if (resEquran.ok) {
                    const dataEquran = await resEquran.json();
                    const list = Array.isArray(dataEquran) ? dataEquran : (dataEquran.data || []);
                    if (Array.isArray(list) && list.length > 0) {
                        list.forEach(d => {
                            let tag = 'harian';
                            const tLow = (d.nama || d.judul || '').toLowerCase();
                            if (tLow.includes('sholat') || tLow.includes('masjid') || tLow.includes('wudhu')) tag = 'sholat';
                            else if (tLow.includes('pagi') || tLow.includes('petang') || tLow.includes('dzikir')) tag = 'dzikir';
                            else if (tLow.includes('rabbana') || tLow.includes('quran')) tag = 'quran';
                            else if (tLow.includes('haji') || tLow.includes('umroh')) tag = 'haji';

                            const exists = allMasterDoa.some(item => item.judul.toLowerCase() === (d.nama || d.judul || '').toLowerCase());
                            if (!exists && (d.ar || d.arab)) {
                                allMasterDoa.push({
                                    judul: d.nama || d.judul || 'Doa Ma\'tsur',
                                    arab: d.ar || d.arab || '',
                                    latin: d.tr || d.latin || '',
                                    terjemah: d.idn || d.terjemah || d.artinya || '',
                                    riwayat: d.tentang || d.riwayat || 'EQur\'an / Ma\'tsur',
                                    tag: tag
                                });
                            }
                        });
                    }
                }
            } catch (e) {
                console.warn("Jalur utama EQur'an dialihkan...", e);
            }

            try {
                const resOpen = await fetch('https://open-api.my.id/api/doa');
                if (resOpen.ok) {
                    const dataOpen = await resOpen.json();
                    if (Array.isArray(dataOpen)) {
                        dataOpen.forEach(d => {
                            const exists = allMasterDoa.some(item => item.judul.toLowerCase() === (d.judul || '').toLowerCase());
                            if (!exists && d.arab) {
                                allMasterDoa.push({
                                    judul: d.judul || 'Doa Harian',
                                    arab: d.arab || '',
                                    latin: d.latin || '',
                                    terjemah: d.terjemah || '',
                                    riwayat: d.riwayat || 'Riwayat Ma\'tsur',
                                    tag: 'harian'
                                });
                            }
                        });
                    }
                }
            } catch (e) {}

            applyFilter();
        }

        function applyFilter() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            filteredDoa = allMasterDoa.filter(item => {
                const matchCategory = currentFilter === 'all' || item.tag === currentFilter;
                const matchQuery = item.judul.toLowerCase().includes(query) ||
                                   item.terjemah.toLowerCase().includes(query) ||
                                   (item.latin && item.latin.toLowerCase().includes(query));
                return matchCategory && matchQuery;
            });

            currentPage = 1;
            renderCurrentPage();
        }

        function renderCurrentPage() {
            const container = document.getElementById('doa-list-container');
            const countInfo = document.getElementById('doa-count-info');
            const paginationWrapper = document.getElementById('pagination-wrapper');
            const pageIndicator = document.getElementById('page-indicator');
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');

            countInfo.textContent = `Menampilkan ${filteredDoa.length} Doa`;

            if (filteredDoa.length === 0) {
                container.innerHTML = `
                    <div class="py-16 text-center text-slate-400 text-sm bg-white border border-slate-200 rounded-md font-normal">
                        <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
                        <p>Tidak ada doa yang cocok dengan pencarian atau filter kategori ini.</p>
                    </div>
                `;
                paginationWrapper.classList.add('hidden');
                return;
            }

            paginationWrapper.classList.remove('hidden');
            const totalPages = Math.max(1, Math.ceil(filteredDoa.length / pageSize));
            
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * pageSize;
            const end = Math.min(start + pageSize, filteredDoa.length);
            const pageItems = filteredDoa.slice(start, end);

            pageIndicator.textContent = `Halaman ${currentPage} dari ${totalPages} (${start + 1} - ${end} doa)`;
            
            btnPrev.disabled = (currentPage <= 1);
            btnNext.disabled = (currentPage >= totalPages);

            let html = '';
            pageItems.forEach((item, index) => {
                const globalIndex = start + index + 1;
                html += `
                    <div class="p-6 sm:p-7 bg-white border border-slate-200 rounded-md space-y-4 font-normal">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 font-normal">
                            <div class="flex items-center gap-3">
                                <div class="relative w-8 h-8 flex items-center justify-center shrink-0">
                                    <svg viewBox="0 0 44 44" class="w-full h-full text-[#9c7a36]" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="22" cy="22" r="14.5" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M 11.5 15 C 14.8 9.8 18.5 8 22 8 C 25.5 8 29.2 9.8 32.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <circle cx="22" cy="8" r="1.6" fill="white" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M 11.5 29 C 14.8 34.2 18.5 36 22 36 C 25.5 36 29.2 34.2 32.5 29" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <circle cx="22" cy="36" r="1.6" fill="white" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-[11px] font-normal text-slate-800 pt-0.5">
                                        ${globalIndex}
                                    </span>
                                </div>
                                <h3 class="text-sm font-normal text-slate-900">
                                    ${item.judul}
                                </h3>
                            </div>
                            <span class="text-xs text-slate-400 font-normal truncate max-w-[180px] sm:max-w-none text-right">
                                ${item.riwayat}
                            </span>
                        </div>

                        <!-- Matan Teks Arab -->
                        <div class="pt-1 text-right select-none font-normal">
                            <p class="arabic-text text-slate-900 font-normal" dir="rtl">
                                ${item.arab}
                            </p>
                        </div>

                        <!-- Transliterasi Latin -->
                        ${item.latin ? `
                            <div class="text-right pt-0.5 font-normal">
                                <p class="latin-text">${item.latin}</p>
                            </div>
                        ` : ''}

                        <!-- Terjemahan Bahasa Indonesia -->
                        <div class="pt-3 border-t border-slate-100 text-left font-normal">
                            <span class="text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-1">Arti:</span>
                            <p class="translation-text text-slate-700 font-normal">
                                ${item.terjemah}
                            </p>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function changePage(delta) {
            currentPage += delta;
            renderCurrentPage();
            window.scrollTo({ top: 350, behavior: 'auto' });
        }

        function setCategoryFilter(category) {
            currentFilter = category;

            document.querySelectorAll('.category-tab').forEach(btn => {
                btn.className = "category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 cursor-pointer";
            });

            const activeBtn = document.getElementById(`tab-${category}`);
            if (activeBtn) {
                activeBtn.className = "category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-[#1865f2] text-white cursor-pointer";
            }

            applyFilter();
        }

        function handleSearch() {
            applyFilter();
        }

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
                } else if (keyword.includes('sholat')) {
                    window.location.href = 'sholat.php';
                } else if (keyword.includes('kiblat') || keyword.includes('arah')) {
                    window.location.href = 'kiblat.php';
                } else {
                    applyFilter();
                }
            }
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
                    ${imagePayload ? 'Ustadz Artif sedang meneliti teks doa...' : 'Ustadz Artif sedang menulis jawaban...'}
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

        fetchAllDoa();
    </script>
</body>
</html>