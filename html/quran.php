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

$user_id = $_SESSION['user_id'];$nama_user = $_SESSION['nama'] ?? 'Pengguna Dirasah';$foto_user = null;

if (isset($pdo)) {
    try {
        $qUser =$pdo->prepare("SELECT * FROM users WHERE id = ?");
        $qUser->execute([$user_id]);
        $curUser =$qUser->fetch(PDO::FETCH_ASSOC);
        if ($curUser) {$nama_user = !empty($curUser['nama']) ?$curUser['nama'] : (!empty($curUser['username']) ?$curUser['username'] : $nama_user);$foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
        }
    } catch (Exception $e) {$foto_user = null;
    }
}

$banner_v = file_exists('assets/pik1.png') ? filemtime('assets/pik1.png') : time();
$fav_v = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
$logo_v = file_exists('assets/dirasah.png') ? filemtime('assets/dirasah.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al-Qur'an Digital - Dirasah.id</title>
    <!-- Favicon Tab Browser (putih.png) -->
    <link rel="icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="apple-touch-icon" href="assets/putih.png?v=<?= $fav_v; ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Amiri+Quran&family=Amiri&family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet">
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
            font-family: 'Amiri Quran', 'Scheherazade New', 'Amiri', serif !important; 
            direction: rtl !important;
            line-height: 2 !important;
        }
        .sidebar-transition {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        a, button, .category-tab, .prompt-btn {
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
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased selection:bg-blue-100 selection:text-blue-900 min-h-screen flex font-normal">

    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/40 z-40 hidden lg:hidden"></div>

    <!-- Sidebar Bilah Sisi Tanpa Mutaba'ah -->
    <aside id="main-sidebar" class="sidebar-transition fixed top-0 bottom-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col w-64 -translate-x-full lg:translate-x-0 select-none font-normal">
        <div class="h-20 px-3 border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <button 
                type="button" 
                onclick="toggleSidebar()" 
                id="brand-toggle-btn"
                class="flex items-center gap-3 w-full p-1 focus:outline-none cursor-pointer"
                title="Buka / Tutup Menu"
            >
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
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">
                    Utama
                </span>
                <nav class="space-y-1">
                    <a href="dashboard.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/home.png" alt="Dashboard" class="w-6 h-6 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Dashboard</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">
                    Khazanah Studi
                </span>
                <nav class="space-y-1">
                    <a href="quran.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-[#1865f2] bg-blue-50/60">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/quran.png" alt="Al-Qur'an" class="w-8 h-auto max-h-7 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Al-Qur'an Digital</span>
                    </a>

                    <a href="hadits.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/hadis.png" alt="Hadits" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Kutubut Tis'ah &amp; Hadits</span>
                    </a>

                    <a href="kitab.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kitab.png" alt="Kitab &amp; Syarah" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Kitab &amp; Syarah</span>
                    </a>

                    <a href="doa.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/doa.png" alt="Doa &amp; Dzikir" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Doa &amp; Dzikir</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">
                    Ibadah &amp; Waktu
                </span>
                <nav class="space-y-1">
                    <a href="sholat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/sholat.png" alt="Jadwal Sholat" class="w-9 h-auto max-h-8 object-contain mix-blend-multiply">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Jadwal Sholat</span>
                    </a>

                    <a href="kiblat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kiblat.png" alt="Arah Kiblat" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Arah Kiblat</span>
                    </a>

                    <a href="kalender.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kalender.png" alt="Kalender Islam" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Kalender Islam</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Konten Utama Al-Qur'an (Daftar Surah) -->
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
                        id="search-surah" 
                        placeholder="Cari surah (misal: Al-Kahf, Yasin, Al-Mulk)..." 
                        oninput="filterSurah()" 
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
            
            <!-- Banner Header (pik1.png) -->
            <div class="relative w-full rounded-md overflow-hidden shadow-sm min-h-[170px] flex flex-col justify-center p-6 sm:p-8 text-white bg-slate-950 font-normal">
                <img 
                    src="assets/pik1.png?v=<?= $banner_v; ?>" 
                    onerror="this.onerror=null; this.src='assets/pik2.png?v=<?= $banner_v; ?>';"
                    alt="Banner Al-Qur'an" 
                    class="absolute inset-0 w-full h-full object-cover object-center z-0 opacity-40"
                >
                <div class="absolute inset-0 bg-slate-950/45 z-0 font-normal"></div>

                <div class="relative z-10 max-w-2xl space-y-2 font-normal">
                    <h1 class="text-xl sm:text-3xl font-normal tracking-normal text-white leading-tight">
                        Al-Qur'an Digital Al-Karim
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 font-normal italic leading-relaxed">
                        "Bacalah Al-Qur'an, sesungguhnya ia akan datang pada hari kiamat memberikan syafa'at bagi para pembacanya."
                    </p>
                    <p class="text-[11px] text-slate-300 font-normal not-italic">
                        (HR. Muslim No. 804)
                    </p>
                </div>
            </div>

            <!-- Panel Penanda Terakhir Dibaca -->
            <div id="last-read-panel" class="hidden bg-white border border-slate-200 rounded-md p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4 font-normal">
                <div class="font-normal">
                    <span class="text-[10px] font-normal text-slate-400 uppercase tracking-wider block mb-0.5">Terakhir Dibaca</span>
                    <h4 id="last-read-title" class="text-xs sm:text-sm font-normal text-slate-800 leading-tight">-</h4>
                    <p id="last-read-sub" class="text-[11px] text-slate-500 font-normal mt-0.5">-</p>
                </div>
                <div class="flex items-center gap-2 font-normal">
                    <a id="last-read-link" href="#" class="px-3.5 py-1.5 bg-slate-900 text-white text-xs font-normal rounded-md hover:bg-slate-800 transition">
                        Lanjut Membaca
                    </a>
                    <button type="button" onclick="removeLastBookmark()" class="px-2 py-1.5 text-slate-400 hover:text-rose-500 text-xs cursor-pointer font-normal" title="Hapus Penanda">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <!-- Tab Filter Kategori -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3 font-normal">
                <div class="flex items-center gap-2 font-normal">
                    <button type="button" onclick="setCategoryFilter('all')" id="tab-all" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-[#1865f2] text-white cursor-pointer transition">
                        Semua Surah (114)
                    </button>
                    <button type="button" onclick="setCategoryFilter('makkiyyah')" id="tab-makkiyyah" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                        Makkiyyah
                    </button>
                    <button type="button" onclick="setCategoryFilter('madaniyyah')" id="tab-madaniyyah" class="category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                        Madaniyyah
                    </button>
                </div>

                <div id="surah-count-info" class="text-xs text-slate-400 font-normal">
                    Memuat surah...
                </div>
            </div>

            <!-- Grid Kontainer Daftar Surah -->
            <div id="surah-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 font-normal">
                <div class="col-span-full py-16 text-center text-slate-400 text-sm font-normal">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                    <p>Memuat daftar 114 surah Al-Qur'an...</p>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= WIDGET FLOATING USTADZ ARTIF ================= -->
    <div 
        id="artif-floating-btn" 
        class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-16 h-16 bg-white rounded-full border border-slate-200 shadow-sm overflow-hidden cursor-grab active:cursor-grabbing select-none touch-none p-0.5"
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
                <button type="button" onclick="document.getElementById('artif-file-input').click()" class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0 cursor-pointer" title="Unggah gambar ayat">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
                <input type="text" id="artif-input-text" placeholder="Ketik pertanyaan ke Ustadz Artif..." autocomplete="off" class="flex-1 px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:border-slate-400 text-slate-800 font-normal">
                <button type="submit" id="artif-btn-send" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-xs font-normal shrink-0 cursor-pointer">
                    Kirim
                </button>
            </form>
        </div>
    </div>

    <script>
        const currentUserId = <?= json_encode($user_id); ?>;
        const storageKey = `dirasah_reading_history_${currentUserId}`;
        let allSurahs = [];
        let currentFilter = 'all';

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

        function getBookmarkForSurah(surahNo) {
            const history = getSavedHistory();
            return history.find(item => item.tipe === "Al-Qur'an" && String(item.surah_no) === String(surahNo));
        }

        function renderLastReadPanel() {
            const panel = document.getElementById('last-read-panel');
            const history = getSavedHistory();
            const lastQuran = history.find(item => item.tipe === "Al-Qur'an");

            if (!lastQuran) {
                panel.classList.add('hidden');
                return;
            }

            document.getElementById('last-read-title').textContent = lastQuran.judul;
            document.getElementById('last-read-sub').textContent = lastQuran.subjudul;
            document.getElementById('last-read-link').href = lastQuran.link;
            panel.classList.remove('hidden');
        }

        function removeLastBookmark() {
            let history = getSavedHistory();
            history = history.filter(item => item.tipe !== "Al-Qur'an");
            saveHistory(history);
            renderLastReadPanel();
            renderSurahCards(allSurahs);
        }

        async function fetchSurahList() {
            const grid = document.getElementById('surah-grid');
            
            try {
                const response = await fetch('https://equran.id/api/v2/surat');
                const result = await response.json();
                
                if (result.data && Array.isArray(result.data)) {
                    allSurahs = result.data.map(item => ({
                        nomor: item.nomor,
                        nama_latin: item.namaLatin || item.nama_latin,
                        nama: item.nama,
                        arti: item.arti,
                        jumlah_ayat: item.jumlahAyat || item.jumlah_ayat,
                        tempat_turun: item.tempatTurun || item.tempat_turun
                    }));
                    renderSurahCards(allSurahs);
                    renderLastReadPanel();
                    return;
                }
            } catch (err) {}

            try {
                const response = await fetch('https://quran-api-id.vercel.app/surahs');
                const data = await response.json();
                
                if (Array.isArray(data)) {
                    allSurahs = data.map(item => ({
                        nomor: item.number,
                        nama_latin: item.name,
                        nama: item.arabic,
                        arti: item.translation,
                        jumlah_ayat: item.numberOfAyahs,
                        tempat_turun: item.revelation
                    }));
                    renderSurahCards(allSurahs);
                    renderLastReadPanel();
                    return;
                }
            } catch (err2) {}

            grid.innerHTML = `
                <div class="col-span-full p-8 text-center bg-white border border-slate-200 rounded-md space-y-3 font-normal">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-3xl"></i>
                    <p class="text-xs sm:text-sm text-slate-700 font-normal">Gagal terhubung ke server Al-Qur'an. Periksa koneksi internet Anda.</p>
                    <button onclick="fetchSurahList()" class="px-5 py-2 bg-[#1865f2] text-white text-xs font-normal rounded-md cursor-pointer transition hover:bg-blue-600">
                        Muat Ulang Data
                    </button>
                </div>
            `;
        }

        function renderSurahCards(surahs) {
            const grid = document.getElementById('surah-grid');
            const countInfo = document.getElementById('surah-count-info');
            
            if (surahs.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full py-16 text-center text-slate-400 text-sm font-normal">
                        <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
                        <p>Tidak ada surah yang sesuai dengan pencarian.</p>
                    </div>
                `;
                countInfo.textContent = `Menampilkan 0 Surah`;
                return;
            }

            countInfo.textContent = `Menampilkan ${surahs.length} Surah`;

            let html = '';
            surahs.forEach(s => {
                const tempat = (s.tempat_turun || '').toLowerCase();
                const tempatTurun = (tempat === 'mekah' || tempat === 'meccan') ? 'Makkiyyah' : 'Madaniyyah';
                
                const bookmark = getBookmarkForSurah(s.nomor);
                const hasBookmark = !!bookmark;
                
                const targetLink = hasBookmark ? bookmark.link : `baca_quran.php?surah=${s.nomor}`;

                html += `
                    <a href="${targetLink}" class="bg-white border border-slate-200 hover:border-slate-300 rounded-md p-4 block transition font-normal cursor-pointer">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="relative w-11 h-11 flex items-center justify-center shrink-0">
                                    <svg viewBox="0 0 44 44" class="w-full h-full text-[#9c7a36]" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="22" cy="22" r="14.5" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M 11.5 15 C 14.8 9.8 18.5 8 22 8 C 25.5 8 29.2 9.8 32.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <circle cx="22" cy="8" r="1.6" fill="white" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M 11.5 29 C 14.8 34.2 18.5 36 22 36 C 25.5 36 29.2 34.2 32.5 29" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <circle cx="22" cy="36" r="1.6" fill="white" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-[12px] font-normal text-slate-800 pt-0.5">
                                        ${s.nomor}
                                    </span>
                                </div>

                                <div class="truncate">
                                    <div class="flex items-center gap-1.5">
                                        <h3 class="text-sm font-normal text-slate-900 truncate">
                                            ${s.nama_latin}
                                        </h3>
                                        ${hasBookmark ? `<span class="text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded font-normal shrink-0">Ayat ${bookmark.ayah_no || 1}</span>` : ''}
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-normal truncate mt-0.5">
                                        ${s.arti} &bull; <span class="text-slate-400 font-normal">${s.jumlah_ayat} Ayat</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="arabic-text text-xl font-normal text-slate-800 block">
                                    ${s.nama}
                                </span>
                                <span class="text-[10px] uppercase font-normal text-slate-400">
                                    ${tempatTurun}
                                </span>
                            </div>
                        </div>
                    </a>
                `;
            });

            grid.innerHTML = html;
        }

        function filterSurah() {
            const query = document.getElementById('search-surah').value.toLowerCase().trim();
            
            const filtered = allSurahs.filter(s => {
                const cocokQuery = s.nama_latin.toLowerCase().includes(query) || 
                                   s.arti.toLowerCase().includes(query) || 
                                   String(s.nomor).includes(query);

                const tempat = (s.tempat_turun || '').toLowerCase();
                const isMekah = tempat === 'mekah' || tempat === 'meccan';
                const isMadinah = tempat === 'madinah' || tempat === 'medinan';

                if (currentFilter === 'all') return cocokQuery;
                if (currentFilter === 'makkiyyah') return cocokQuery && isMekah;
                if (currentFilter === 'madaniyyah') return cocokQuery && isMadinah;
                return cocokQuery;
            });

            renderSurahCards(filtered);
        }

        function setCategoryFilter(category) {
            currentFilter = category;
            
            document.querySelectorAll('.category-tab').forEach(btn => {
                btn.className = "category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 cursor-pointer transition";
            });

            const activeBtn = document.getElementById(`tab-${category}`);
            if (activeBtn) {
                activeBtn.className = "category-tab px-3.5 py-1.5 rounded-md text-xs font-normal bg-[#1865f2] text-white cursor-pointer transition";
            }

            filterSurah();
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
                    ${imagePayload ? 'Ustadz Artif sedang meneliti gambar & teks...' : 'Ustadz Artif sedang menulis jawaban...'}
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

        fetchSurahList();
    </script>
</body>
</html>