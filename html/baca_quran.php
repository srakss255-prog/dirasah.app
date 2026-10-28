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
        $qUser =$pdo->prepare("SELECT foto FROM users WHERE id = ?");
        $qUser->execute([$user_id]);
        $curUser =$qUser->fetch(PDO::FETCH_ASSOC);
        $foto_user =$curUser['foto'] ?? null;
    } catch (Exception $e) {$foto_user = null;
    }
}

$surah_no = isset($_GET['surah']) ? (int)$_GET['surah'] : 1;
if ($surah_no < 1) {$surah_no = 1; }
if ($surah_no > 114) { $surah_no = 1; }$target_ayah = isset($_GET['ayah']) ? (int)$_GET['ayah'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Al-Qur'an - Dirasah.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Scheherazade+New:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body, input, textarea, button, select, p, span, h1, h2, h3, h4, h5, h6, a, div {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 400 !important;
            font-style: normal !important;
        }
        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }
        .arabic-ayah { 
            font-family: 'Amiri Quran', 'Scheherazade New', serif !important; 
            direction: rtl !important;
            text-align: right !important;
            text-rendering: optimizeLegibility;
            font-feature-settings: "cv01", "cv02", "liga", "calt";
            -webkit-font-smoothing: antialiased;
            word-spacing: 4px;
        }
        @media (max-width: 640px) {
            .arabic-ayah {
                font-size: 1.65rem !important;
                line-height: 2.6 !important;
                word-spacing: 3px;
            }
        }
        @media (min-width: 641px) {
            .arabic-ayah {
                font-size: 2.1rem !important;
                line-height: 2.8 !important;
                word-spacing: 5px;
            }
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
        a, button, .toggle-switch-bg, .toggle-switch-dot, .murojaah-blank-card {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }
        html { scroll-behavior: smooth; }
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
<body class="bg-[#f8fafc] text-slate-900 antialiased selection:bg-blue-100 selection:text-blue-900 min-h-screen flex font-normal pb-32">

    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/40 z-40 hidden lg:hidden backdrop-blur-[2px]"></div>

    <!-- Hitung Mundur Ringkas Muroja'ah -->
    <div id="countdown-overlay" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm hidden flex-col items-center justify-center text-white select-none">
        <div class="text-xs uppercase tracking-widest text-slate-300 mb-2 font-normal">Sedia melafalkan ayat</div>
        <div id="countdown-number" class="text-7xl sm:text-8xl font-normal text-white">1</div>
    </div>

    <!-- Sidebar Bilah Sisi Tanpa Mutaba'ah -->
    <aside id="main-sidebar" class="sidebar-transition fixed top-0 bottom-0 left-0 z-40 bg-white border-r border-slate-200 flex flex-col w-64 -translate-x-full lg:translate-x-0 select-none font-normal">
        <div class="h-20 px-3 border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <button type="button" onclick="toggleSidebar()" class="flex items-center gap-3 w-full p-1 focus:outline-none cursor-pointer">
                <div class="w-12 h-12 flex items-center justify-center shrink-0">
                    <img src="assets/dirasah.png" alt="Dirasah.id" class="w-12 h-12 object-contain">
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
                    <a href="dashboard.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/home.png" alt="Dashboard" class="w-6 h-6 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Dashboard</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">Khazanah Studi</span>
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
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">Ibadah &amp; Waktu</span>
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

    <!-- Konten Pembaca Surah -->
    <div id="content-area" class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-64 font-normal">
        <header class="w-full bg-white border-b border-slate-200 h-16 px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 font-normal">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-700 hover:text-[#1865f2] p-1.5 focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <a href="quran.php" class="text-xs font-normal text-slate-500 hover:text-[#1865f2] transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Daftar Surah</span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <select id="jump-surah" onchange="jumpToSurah(this.value)" class="text-xs bg-slate-50 border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 outline-none focus:border-[#1865f2]">
                    <option value="">Pilih Surah...</option>
                </select>

                <a href="profil.php" class="flex items-center gap-2">
                    <?php if (!empty($foto_user)): ?>
                        <img src="<?= htmlspecialchars($foto_user); ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full bg-[#1865f2] text-white flex items-center justify-center font-normal text-xs">
                            <?= strtoupper(substr($nama_user, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-8 max-w-4xl mx-auto w-full space-y-6 font-normal">
            <!-- Header Informasi Surah -->
            <div id="surah-header-card" class="bg-white border border-slate-200 rounded-md p-6 sm:p-8 text-center space-y-3 font-normal relative">
                
                <!-- Saklar Mode Muroja'ah -->
                <div class="sm:absolute sm:top-5 sm:right-5 flex items-center justify-center gap-2 cursor-pointer select-none" onclick="toggleMurojaahMode()">
                    <span class="text-xs text-slate-600 font-normal">Mode Muroja'ah</span>
                    <div id="murojaah-switch-bg" class="toggle-switch-bg w-9 h-5 bg-slate-300 rounded-full p-0.5 flex items-center">
                        <div id="murojaah-switch-dot" class="toggle-switch-dot w-4 h-4 bg-white rounded-full shadow-sm"></div>
                    </div>
                </div>

                <div class="text-xs font-normal text-slate-500 tracking-wider" id="surah-badge">
                    Memuat Surah...
                </div>
                <h1 class="text-2xl sm:text-3xl font-normal text-slate-900" id="surah-title-latin">
                    -
                </h1>
                <p class="arabic-ayah text-3xl sm:text-4xl text-slate-800" id="surah-title-arabic" dir="rtl">
                    -
                </p>
                <p class="text-xs sm:text-sm text-slate-500 font-normal" id="surah-meta">
                    -
                </p>

                <div id="normal-audio-actions" class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-center gap-3">
                    <button id="btn-play-all" onclick="togglePlaySurah()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#1865f2] hover:bg-blue-600 text-white text-xs font-normal cursor-pointer transition">
                        <i id="icon-play-all" class="fa-solid fa-play text-[11px]"></i>
                        <span id="text-play-all">Putar Murattal Full</span>
                    </button>
                    <button type="button" onclick="tandaiAyat(currentSurahNumber, 1)" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-normal cursor-pointer transition">
                        <i class="fa-regular fa-bookmark text-[11px]"></i>
                        <span>Tandai Surah Ini</span>
                    </button>
                    <audio id="audio-surah-player" class="hidden"></audio>
                </div>
            </div>

            <!-- Teks Basmalah -->
            <div id="bismillah-box" class="py-4 text-center">
                <p class="arabic-ayah text-2xl sm:text-3xl text-slate-800" dir="rtl">
                    بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                </p>
            </div>

            <!-- Kontainer Daftar Ayat -->
            <div id="ayah-list" class="space-y-4 font-normal">
                <div class="py-16 text-center text-slate-400 text-sm font-normal">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-[#1865f2]"></i>
                    <p>Memuat ayat-ayat suci Al-Qur'an...</p>
                </div>
            </div>

            <!-- Navigasi Bawah -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-200 font-normal">
                <button id="btn-prev-surah" onclick="navigateSurah(-1)" class="px-4 py-2 rounded-md border border-slate-200 text-xs font-normal text-slate-700 bg-white disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer transition">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Surah Sebelumnya</span>
                </button>
                <a href="quran.php" class="text-xs font-normal text-slate-600 hover:text-[#1865f2]">
                    Daftar 114 Surah
                </a>
                <button id="btn-next-surah" onclick="navigateSurah(1)" class="px-4 py-2 rounded-md border border-slate-200 text-xs font-normal text-slate-700 bg-white disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer transition">
                    <span>Surah Selanjutnya</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        </main>
    </div>

    <!-- Floating Dock Mikrofon Muroja'ah -->
    <div id="murojaah-dock" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-white border border-slate-200 rounded-full px-5 py-2.5 shadow-md hidden items-center gap-4 transition font-normal max-w-[92vw]">
        <button 
            type="button" 
            id="btn-voice-stream" 
            onclick="startCountdownMurojaah()" 
            class="w-11 h-11 rounded-full bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center text-base transition cursor-pointer shrink-0"
            title="Mulai Rekam Suara"
        >
            <i id="voice-stream-icon" class="fa-solid fa-microphone"></i>
        </button>

        <div class="flex flex-col min-w-0 pr-1">
            <span id="voice-stream-title" class="text-xs text-slate-800 truncate font-normal">Mode Muroja'ah Aktif</span>
            <span id="voice-stream-transcript" class="text-[11px] text-slate-400 truncate max-w-[240px] sm:max-w-xs font-normal">Sebut ayat untuk membuka teks</span>
        </div>
    </div>

    <!-- Toast Notifikasi Sederhana -->
    <div id="toast-notif" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white px-4 py-2 rounded-md text-xs font-normal shadow-md hidden">
        <span id="toast-text">Tersimpan</span>
    </div>

    <script>
        const currentUserId = <?= json_encode($user_id); ?>;
        const currentSurahNumber = <?= (int)$surah_no ?>;
        const targetAyahNumber = <?= (int)$target_ayah ?>;
        const storageKey = `dirasah_reading_history_${currentUserId}`;

        let surahData = null;
        let currentPlayingAudio = null;
        let activeBookmarkedAyah = null;

        // State Muroja'ah
        let isMurojaahActive = false;
        let currentAyahTargetIndex = 0;
        let speechRecognizer = null;
        let isRecognizing = false;
        let isCountdownRunning = false;
        const guessedAyahs = new Set();
        const typingActiveSet = new Set();

        function getSavedBookmark() {
            try {
                const history = JSON.parse(localStorage.getItem(storageKey) || '[]');
                const found = history.find(item => item.tipe === "Al-Qur'an" && String(item.surah_no) === String(currentSurahNumber));
                if (found && found.ayah_no) {
                    activeBookmarkedAyah = parseInt(found.ayah_no);
                }
            } catch (e) {}
        }

        function tandaiAyat(surahNo, ayahNo) {
            if (!surahData) return;
            const surahName = surahData.namaLatin || surahData.nama_latin || `Surah ${surahNo}`;
            const surahTitle = `Surah ${surahName}`;

            let history = [];
            try {
                history = JSON.parse(localStorage.getItem(storageKey) || '[]');
            } catch (e) {}

            history = history.filter(item => item.judul !== surahTitle);

            history.unshift({
                id: 'quran_' + surahNo + '_' + Date.now(),
                surah_no: surahNo,
                ayah_no: ayahNo,
                judul: surahTitle,
                subjudul: `Terakhir dibaca (Ayat ${ayahNo})`,
                tipe: "Al-Qur'an",
                link: `baca_quran.php?surah=${surahNo}&ayah=${ayahNo}`,
                timestamp: Date.now()
            });

            if (history.length > 10) history = history.slice(0, 10);
            localStorage.setItem(storageKey, JSON.stringify(history));

            activeBookmarkedAyah = ayahNo;
            refreshBookmarkUI();
            showToastMessage(`${surahTitle} Ayat ${ayahNo} ditandai`);
        }

        function refreshBookmarkUI() {
            document.querySelectorAll('.btn-mark-ayah').forEach(btn => {
                const aNo = parseInt(btn.getAttribute('data-ayah'));
                const icon = btn.querySelector('i');
                const text = btn.querySelector('.mark-text');
                const card = document.getElementById(`ayah-${aNo}`);

                if (aNo === activeBookmarkedAyah) {
                    btn.className = "btn-mark-ayah h-8 px-2.5 rounded-md border border-slate-300 bg-slate-100 text-slate-800 text-xs font-normal flex items-center gap-1.5 cursor-pointer";
                    icon.className = "fa-solid fa-bookmark text-[11px] text-[#1865f2]";
                    text.textContent = "Ditandai";
                    if (card && !isMurojaahActive) card.classList.add('bg-slate-50/60');
                } else {
                    btn.className = "btn-mark-ayah h-8 px-2.5 rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 text-xs font-normal flex items-center gap-1.5 cursor-pointer";
                    icon.className = "fa-regular fa-bookmark text-[11px]";
                    text.textContent = "Tandai";
                    if (card && !isMurojaahActive) card.classList.remove('bg-slate-50/60');
                }
            });
        }

        function showToastMessage(msg) {
            const toast = document.getElementById('toast-notif');
            document.getElementById('toast-text').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 2500);
        }

        // ================= ULTRA-FAST SPEECH RECOGNITION =================
        function toggleMurojaahMode() {
            isMurojaahActive = !isMurojaahActive;
            const bg = document.getElementById('murojaah-switch-bg');
            const dot = document.getElementById('murojaah-switch-dot');
            const dock = document.getElementById('murojaah-dock');
            const normalActions = document.getElementById('normal-audio-actions');

            guessedAyahs.clear();
            typingActiveSet.clear();
            currentAyahTargetIndex = 0;

            if (isMurojaahActive) {
                bg.classList.remove('bg-slate-300');
                bg.classList.add('bg-slate-900');
                dot.style.transform = 'translateX(16px)';
                dock.classList.remove('hidden');
                dock.classList.add('flex');
                normalActions.classList.add('hidden');
                updateMurojaahStatus();
            } else {
                bg.classList.remove('bg-slate-900');
                bg.classList.add('bg-slate-300');
                dot.style.transform = 'translateX(0px)';
                dock.classList.add('hidden');
                dock.classList.remove('flex');
                normalActions.classList.remove('hidden');
                stopVoiceRecognition();
            }

            if (surahData) {
                renderAyahs(surahData);
            }
        }

        // Efek ketik perkata untuk teks Arab dan terjemahan
        function typeAyatWordByWord(noAyat, arabText, transText) {
            if (typingActiveSet.has(noAyat)) return;
            typingActiveSet.add(noAyat);

            const blankArea = document.getElementById(`murojaah-blank-${noAyat}`);
            const textArea = document.getElementById(`murojaah-text-${noAyat}`);
            const arabEl = document.getElementById(`arab-text-${noAyat}`);
            const transEl = document.getElementById(`trans-text-${noAyat}`);

            if (blankArea) blankArea.classList.add('hidden');
            if (textArea) textArea.classList.remove('hidden');

            const arabWords = (arabText || '').trim().split(/\s+/);
            const transWords = (transText || '').trim().split(/\s+/);

            if (arabEl) arabEl.textContent = '';
            if (transEl) transEl.textContent = '';

            let aIndex = 0;
            const aInterval = setInterval(() => {
                if (aIndex < arabWords.length) {
                    if (arabEl) {
                        arabEl.textContent += (aIndex === 0 ? '' : ' ') + arabWords[aIndex];
                    }
                    aIndex++;
                } else {
                    clearInterval(aInterval);
                    // Setelah teks arab selesai mengetik, lanjutkan mengetik terjemahan perkata
                    let tIndex = 0;
                    const tInterval = setInterval(() => {
                        if (tIndex < transWords.length) {
                            if (transEl) {
                                transEl.textContent += (tIndex === 0 ? '' : ' ') + transWords[tIndex];
                            }
                            tIndex++;
                        } else {
                            clearInterval(tInterval);
                            typingActiveSet.delete(noAyat);
                        }
                    }, 40);
                }
            }, 60);
        }

        function peekAyat(noAyat) {
            if (!isMurojaahActive || guessedAyahs.has(noAyat)) return;
            guessedAyahs.add(noAyat);

            const targetAyahObj = (surahData.ayat || []).find(a => (a.nomorAyat || a.nomor) === noAyat);
            if (targetAyahObj) {
                const arab = targetAyahObj.teksArab || targetAyahObj.ar || '';
                const trans = targetAyahObj.teksIndonesia || targetAyahObj.idn || targetAyahObj.translation || '';
                typeAyatWordByWord(noAyat, arab, trans);
            }

            if (surahData && surahData.ayat && surahData.ayat[currentAyahTargetIndex]) {
                const target = surahData.ayat[currentAyahTargetIndex];
                const curNo = target.nomorAyat || target.nomor;
                if (curNo === noAyat) {
                    currentAyahTargetIndex++;
                    updateMurojaahStatus();
                }
            }
        }

        function startCountdownMurojaah() {
            if (isRecognizing) {
                stopVoiceRecognition();
                return;
            }
            if (isCountdownRunning) return;

            const overlay = document.getElementById('countdown-overlay');
            const numEl = document.getElementById('countdown-number');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            isCountdownRunning = true;

            numEl.textContent = '1';
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                isCountdownRunning = false;
                startVoiceRecognition();
            }, 300);
        }

        function cleanArabic(str) {
            if (!str) return '';
            return str
                .replace(/[\u0610-\u061A\u064B-\u065F\u0670\u06D6-\u06ED\u06DF-\u06E8\u06EA-\u06ED]/g, '')
                .replace(/[\u0671\u0622\u0623\u0625]/g, 'ا')
                .replace(/[\u0649]/g, 'ي')
                .replace(/[\u0629]/g, 'ه')
                .replace(/[\u0624\u0626]/g, 'و')
                .replace(/[\u0640]/g, '')
                .replace(/[^\u0621-\u064A\s]/g, '')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function updateMurojaahStatus() {
            if (!surahData || !surahData.ayat) return;
            const total = surahData.ayat.length;
            const target = surahData.ayat[currentAyahTargetIndex];
            const micStatus = document.getElementById('voice-stream-title');

            if (currentAyahTargetIndex < total) {
                const noAyat = target.nomorAyat || target.nomor;
                micStatus.textContent = `Muroja'ah Ayat ${noAyat}`;
            } else {
                micStatus.textContent = `Selesai ${total} Ayat`;
                stopVoiceRecognition();
            }
        }

        function initSpeechEngine() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert("Browser Anda belum mendukung Web Speech Recognition. Gunakan browser Google Chrome.");
                return null;
            }

            const recognizer = new SpeechRecognition();
            recognizer.continuous = true;
            recognizer.interimResults = true;
            recognizer.maxAlternatives = 1;
            recognizer.lang = 'ar-SA';

            recognizer.onstart = () => {
                isRecognizing = true;
                const micIcon = document.getElementById('voice-stream-icon');
                if (micIcon) micIcon.className = "fa-solid fa-microphone-lines text-rose-500 animate-pulse";
            };

            recognizer.onresult = (event) => {
                let latestStreamText = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    latestStreamText += ' ' + event.results[i][0].transcript;
                }
                const cleanSpoken = latestStreamText.trim();
                if (cleanSpoken) {
                    document.getElementById('voice-stream-transcript').textContent = cleanSpoken;
                    evaluateVoiceFast(cleanSpoken);
                }
            };

            recognizer.onerror = (e) => {
                if (e.error !== 'no-speech') {
                    stopVoiceRecognition();
                }
            };

            recognizer.onend = () => {
                if (isRecognizing && isMurojaahActive && currentAyahTargetIndex < surahData.ayat.length) {
                    try { recognizer.start(); } catch(e){}
                } else {
                    stopVoiceRecognition();
                }
            };

            return recognizer;
        }

        function startVoiceRecognition() {
            if (!speechRecognizer) {
                speechRecognizer = initSpeechEngine();
                if (!speechRecognizer) return;
            }
            try {
                speechRecognizer.start();
            } catch(e) {
                try {
                    speechRecognizer.stop();
                    setTimeout(() => speechRecognizer.start(), 100);
                } catch(err){}
            }
        }

        function stopVoiceRecognition() {
            isRecognizing = false;
            if (speechRecognizer) {
                try { speechRecognizer.stop(); } catch(e){}
            }
            const micIcon = document.getElementById('voice-stream-icon');
            if (micIcon) micIcon.className = "fa-solid fa-microphone";
        }

        function evaluateVoiceFast(spoken) {
            if (!surahData || !surahData.ayat) return;
            const target = surahData.ayat[currentAyahTargetIndex];
            if (!target) return;

            const noAyat = target.nomorAyat || target.nomor;
            const targetText = cleanArabic(target.teksArab || target.ar);
            const userSpoken = cleanArabic(spoken);

            if (!targetText || !userSpoken) return;

            const targetWords = targetText.split(' ').filter(w => w.length > 0);
            const spokenWords = userSpoken.split(' ').filter(w => w.length > 0);

            let isMatched = false;

            if (targetWords.length > 0 && spokenWords.length > 0) {
                const firstWordTarget = targetWords[0];
                const firstWordSpoken = spokenWords[spokenWords.length - 1];

                if (firstWordSpoken.includes(firstWordTarget) || firstWordTarget.includes(firstWordSpoken)) {
                    isMatched = true;
                }
            }

            if (!isMatched && (userSpoken.includes(targetText) || targetText.includes(userSpoken))) {
                isMatched = true;
            }

            if (!isMatched && targetWords.length >= 2) {
                let matches = 0;
                for (let w of targetWords) {
                    if (userSpoken.includes(w)) matches++;
                    if (matches >= 2) {
                        isMatched = true;
                        break;
                    }
                }
            }

            if (isMatched && !guessedAyahs.has(noAyat)) {
                guessedAyahs.add(noAyat);

                const arab = target.teksArab || target.ar || '';
                const trans = target.teksIndonesia || target.idn || target.translation || '';
                
                typeAyatWordByWord(noAyat, arab, trans);

                const targetEl = document.getElementById(`ayah-${noAyat}`);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                currentAyahTargetIndex++;
                updateMurojaahStatus();
                document.getElementById('voice-stream-transcript').textContent = "Ayat benar, lanjut...";
            }
        }

        async function loadSurahOptions() {
            try {
                const res = await fetch('https://equran.id/api/v2/surat');
                const result = await res.json();
                if (result.data) {
                    const select = document.getElementById('jump-surah');
                    result.data.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.nomor;
                        opt.textContent = `${s.nomor}. ${s.namaLatin || s.nama_latin}`;
                        if (s.nomor === currentSurahNumber) opt.selected = true;
                        select.appendChild(opt);
                    });
                }
            } catch (e) {}
        }

        async function fetchSurahDetail() {
            getSavedBookmark();
            const listEl = document.getElementById('ayah-list');
            try {
                const res = await fetch(`https://equran.id/api/v2/surat/${currentSurahNumber}`);
                const json = await res.json();
                if (json.data) {
                    surahData = json.data;
                    renderAyahs(surahData);
                    scrollToTargetAyah();
                    return;
                }
            } catch (e) {}

            try {
                const res2 = await fetch(`https://quran-api-id.vercel.app/surahs/${currentSurahNumber}`);
                const json2 = await res2.json();
                if (json2) {
                    surahData = {
                        nomor: json2.number,
                        namaLatin: json2.name,
                        nama: json2.arabic,
                        arti: json2.translation,
                        jumlahAyat: json2.numberOfAyahs,
                        tempatTurun: json2.revelation,
                        audioFull: json2.audio,
                        ayat: (json2.ayahs || []).map(a => ({
                            nomorAyat: a.number.inSurah,
                            teksArab: a.arab,
                            teksLatin: a.translation,
                            teksIndonesia: a.translation,
                            audio: { "05": a.audio.alafasy }
                        }))
                    };
                    renderAyahs(surahData);
                    scrollToTargetAyah();
                    return;
                }
            } catch (e2) {}

            listEl.innerHTML = `
                <div class="p-8 text-center bg-white border border-slate-200 rounded-md space-y-3 font-normal">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-3xl"></i>
                    <p class="text-xs sm:text-sm text-slate-700 font-normal">Gagal memuat ayat surah. Pastikan koneksi internet aktif.</p>
                    <button onclick="fetchSurahDetail()" class="px-5 py-2 bg-[#1865f2] text-white text-xs rounded-md cursor-pointer font-normal">Coba Lagi</button>
                </div>
            `;
        }

        function scrollToTargetAyah() {
            if (targetAyahNumber > 0 && !isMurojaahActive) {
                setTimeout(() => {
                    const targetEl = document.getElementById(`ayah-${targetAyahNumber}`);
                    if (targetEl) {
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 400);
            }
        }

        function renderAyahs(data) {
            const tempat = (data.tempatTurun || '').toLowerCase();
            const tempatTurun = (tempat === 'mekah' || tempat === 'meccan') ? 'Makkiyyah' : 'Madaniyyah';

            document.getElementById('surah-badge').textContent = `Surah Ke-${data.nomor} • ${tempatTurun}`;
            document.getElementById('surah-title-latin').textContent = data.namaLatin || data.nama_latin;
            document.getElementById('surah-title-arabic').textContent = data.nama;
            document.getElementById('surah-meta').textContent = `${data.arti} • ${data.jumlahAyat || data.jumlah_ayat} Ayat`;

            document.getElementById('btn-prev-surah').disabled = currentSurahNumber <= 1;
            document.getElementById('btn-next-surah').disabled = currentSurahNumber >= 114;

            const bismillahBox = document.getElementById('bismillah-box');
            if (currentSurahNumber === 1 || currentSurahNumber === 9) {
                bismillahBox.classList.add('hidden');
            } else {
                bismillahBox.classList.remove('hidden');
            }

            const fullAudioUrl = data.audioFull ? (data.audioFull['05'] || Object.values(data.audioFull)[0]) : null;
            const audioSurah = document.getElementById('audio-surah-player');
            if (fullAudioUrl) {
                audioSurah.src = fullAudioUrl;
            } else {
                document.getElementById('btn-play-all').classList.add('hidden');
            }

            const listEl = document.getElementById('ayah-list');
            let html = '';

            const ayats = data.ayat || [];
            ayats.forEach(ay => {
                const noAyat = ay.nomorAyat || ay.nomor;
                const audioAyatUrl = ay.audio ? (ay.audio['05'] || Object.values(ay.audio)[0]) : '';
                const isMarked = (noAyat === activeBookmarkedAyah);
                const isGuessed = guessedAyahs.has(noAyat);

                const showContent = !isMurojaahActive || isGuessed;

                html += `
                    <div id="ayah-${noAyat}" class="p-5 sm:p-7 bg-white border border-slate-200 rounded-md space-y-4 font-normal ${isMarked && !isMurojaahActive ? 'bg-slate-50/60' : ''}">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="relative w-10 h-10 flex items-center justify-center shrink-0">
                                <svg viewBox="0 0 44 44" class="w-full h-full text-[#9c7a36]" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="22" cy="22" r="14.5" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M 11.5 15 C 14.8 9.8 18.5 8 22 8 C 25.5 8 29.2 9.8 32.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <circle cx="22" cy="8" r="1.6" fill="white" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M 11.5 29 C 14.8 34.2 18.5 36 22 36 C 25.5 36 29.2 34.2 32.5 29" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <circle cx="22" cy="36" r="1.6" fill="white" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-[11px] font-normal text-slate-800 pt-0.5">${noAyat}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    onclick="tandaiAyat(${currentSurahNumber}, ${noAyat})" 
                                    data-ayah="${noAyat}" 
                                    class="btn-mark-ayah h-8 px-2.5 rounded-md border ${isMarked ? 'border-slate-300 bg-slate-100 text-slate-800' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-500'} text-xs font-normal flex items-center gap-1.5 cursor-pointer transition"
                                    title="Tandai ayat ini"
                                >
                                    <i class="${isMarked ? 'fa-solid text-[#1865f2]' : 'fa-regular'} fa-bookmark text-[11px]"></i>
                                    <span class="mark-text">${isMarked ? 'Ditandai' : 'Tandai'}</span>
                                </button>

                                ${audioAyatUrl ? `
                                    <button onclick="playSingleAyahAudio('${audioAyatUrl}', this)" class="w-8 h-8 rounded-md bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center cursor-pointer transition" title="Putar Ayat">
                                        <i class="fa-solid fa-play text-xs"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Ruang Kosong Bersih Muroja'ah (Tanpa teks & tanpa efek hover) -->
                        <div id="murojaah-blank-${noAyat}" onclick="peekAyat(${noAyat})" class="murojaah-blank-card ${showContent ? 'hidden' : ''} h-28 bg-slate-50/60 border border-slate-200/80 rounded-md cursor-default select-none"></div>

                        <!-- Teks Ayat Terbuka (Mendukung efek ketik perkata) -->
                        <div id="murojaah-text-${noAyat}" class="${showContent ? '' : 'hidden'} space-y-3">
                            <div class="pt-2 pb-1 text-right">
                                <p id="arab-text-${noAyat}" class="arabic-ayah text-slate-900 select-text" dir="rtl">${ay.teksArab || ay.ar}</p>
                            </div>

                            <div class="pt-2 text-left space-y-1">
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
                                    <span class="font-normal text-slate-800">${noAyat}.</span> <span id="trans-text-${noAyat}">${ay.teksIndonesia || ay.idn || ay.translation}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
            listEl.innerHTML = html;
        }

        function playSingleAyahAudio(url, btn) {
            if (currentPlayingAudio) currentPlayingAudio.pause();
            const audio = new Audio(url);
            currentPlayingAudio = audio;
            const icon = btn.querySelector('i');
            icon.className = 'fa-solid fa-pause text-xs text-[#1865f2]';
            audio.play();
            audio.onended = () => { icon.className = 'fa-solid fa-play text-xs'; };
        }

        function togglePlaySurah() {
            const player = document.getElementById('audio-surah-player');
            const icon = document.getElementById('icon-play-all');
            const text = document.getElementById('text-play-all');

            if (player.paused) {
                if (currentPlayingAudio) currentPlayingAudio.pause();
                player.play();
                icon.className = 'fa-solid fa-pause text-[11px]';
                text.textContent = 'Jeda Murattal';
            } else {
                player.pause();
                icon.className = 'fa-solid fa-play text-[11px]';
                text.textContent = 'Putar Murattal Full';
            }
            player.onended = () => {
                icon.className = 'fa-solid fa-play text-[11px]';
                text.textContent = 'Putar Murattal Full';
            };
        }

        function jumpToSurah(num) {
            if (num) window.location.href = `baca_quran.php?surah=${num}`;
        }

        function navigateSurah(delta) {
            const nextSurah = currentSurahNumber + delta;
            if (nextSurah >= 1 && nextSurah <= 114) {
                window.location.href = `baca_quran.php?surah=${nextSurah}`;
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

        loadSurahOptions();
        fetchSurahDetail();
    </script>
</body>
</html>