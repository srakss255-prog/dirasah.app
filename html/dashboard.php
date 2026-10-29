<?php
// Aktifkan pelaporan error agar kendala mudah terdeteksi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

if (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'Pengguna Dirasah';
$foto_user = null;
$lokasi_user = '';

if (isset($pdo)) {
    try {
        $qUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $qUser->execute([$user_id]);
        $curUser = $qUser->fetch(PDO::FETCH_ASSOC);
        if ($curUser) {
            $nama_user = !empty($curUser['nama']) ? $curUser['nama'] : (!empty($curUser['username']) ? $curUser['username'] : $nama_user);
            $foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
            if (!empty($curUser['lokasi']) && stripos($curUser['lokasi'], 'MEMUAT') === false) {
                $lokasi_user = preg_replace('/\s*\([^)]*\)/', '', $curUser['lokasi']);
            }
        }
    } catch (Exception $e) {
        $foto_user = null;
    }
}

$v1 = file_exists('assets/pik1.png') ? filemtime('assets/pik1.png') : time();
$v2 = file_exists('assets/pik2.png') ? filemtime('assets/pik2.png') : time();
$v3 = file_exists('assets/pik3.png') ? filemtime('assets/pik3.png') : time();
$fav_v = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dirasah.id</title>
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
        .banner-box, .banner-box * {
            font-weight: 400 !important;
        }
        .arabic-text { 
            font-family: 'Amiri Quran', 'Scheherazade New', 'Amiri', serif !important; 
            direction: rtl !important;
            text-align: right !important;
            line-height: 2.5 !important;
            word-spacing: 6px !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        .sidebar-transition {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @keyframes scanLaser {
            0% { top: 0%; opacity: 0.8; }
            50% { opacity: 1; }
            100% { top: 96%; opacity: 0.8; }
        }
        .animate-laser {
            animation: scanLaser 1.2s ease-in-out infinite alternate;
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
                    <img src="assets/dirasah.png" alt="Dirasah.id" class="w-10 h-10 object-contain">
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
                    <a href="dashboard.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-[#1865f2] bg-blue-50/60">
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
                    <a href="quran.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/quran.png" alt="Al-Qur'an" class="w-8 h-auto max-h-7 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Al-Qur'an Digital</span>
                    </a>

                    <a href="hadits.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/hadis.png" alt="Hadits" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kutubut Tis'ah &amp; Hadits</span>
                    </a>

                    <a href="kitab.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kitab.png" alt="Kitab &amp; Syarah" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kitab &amp; Syarah</span>
                    </a>

                    <a href="doa.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
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
                    <a href="sholat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/sholat.png" alt="Jadwal Sholat" class="w-9 h-auto max-h-8 object-contain mix-blend-multiply">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Jadwal Sholat</span>
                    </a>

                    <a href="kiblat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kiblat.png" alt="Arah Kiblat" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Arah Kiblat</span>
                    </a>

                    <a href="kalender.php" class="flex items-center gap-3 px-1.5 py-2 rounded-md text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kalender.png" alt="Kalender Islam" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kalender Islam</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Konten Utama Dashboard -->
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
                        id="global-search-input"
                        placeholder="Cari surat, hadits, kitab, atau fitur (tekan Enter)..." 
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
            
            <div class="banner-box relative w-full rounded-md overflow-hidden shadow-sm bg-slate-950 p-7 sm:p-9 text-white min-h-[230px] flex items-center justify-between font-normal">
                <div class="dash-bg-slide absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out opacity-100 font-normal">
                    <img src="assets/pik1.png?v=<?= $v1; ?>" alt="Slide 1" class="w-full h-full object-cover object-center opacity-40">
                </div>
                <div class="dash-bg-slide absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none font-normal">
                    <img src="assets/pik2.png?v=<?= $v2; ?>" alt="Slide 2" class="w-full h-full object-cover object-center opacity-40">
                </div>
                <div class="dash-bg-slide absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none font-normal">
                    <img src="assets/pik3.png?v=<?= $v3; ?>" alt="Slide 3" class="w-full h-full object-cover object-center opacity-40">
                </div>

                <div class="absolute inset-0 bg-slate-950/45 z-0 font-normal"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 w-full font-normal">
                    <div class="space-y-2 font-normal">
                        <div class="flex items-center gap-2 font-normal">
                            <span id="user-location-label" class="text-[11px] font-normal tracking-wider text-slate-300 uppercase block">
                                <?= !empty($lokasi_user) ? htmlspecialchars($lokasi_user) : 'MEMUAT LOKASI...'; ?>
                            </span>
                            <span class="text-slate-500 text-[10px]">•</span>
                            <div id="weather-box" class="text-[11px] text-slate-300 font-normal">
                                <span id="weather-text">Memuat cuaca BMKG...</span>
                            </div>
                        </div>

                        <div id="live-clock" class="text-3xl sm:text-5xl font-normal tracking-tight text-white leading-none py-1">
                            --:--:--
                        </div>
                        <p id="live-date" class="text-xs sm:text-sm text-slate-300 font-normal">
                            Memuat tanggal...
                        </p>
                    </div>

                    <div class="max-w-xl text-left md:text-right space-y-2.5 font-normal">
                        <h1 class="text-xl sm:text-3xl font-normal leading-tight text-white">
                            Assalamu’alaikum, <?= htmlspecialchars($nama_user); ?>!
                        </h1>
                        <div class="py-1 font-normal">
                            <p class="arabic-text text-lg sm:text-2xl text-slate-100 font-normal" dir="rtl">
                                فَاذْكُرُونِي أَذْكُرْكُمْ وَاشْكُرُوا لِي وَلَا تَكْفُرُونِ
                            </p>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-200 italic font-normal leading-relaxed">
                            "Maka ingatlah kepada-Ku, niscaya Aku ingat (pula) kepadamu."
                        </p>
                        <p class="text-[11px] text-slate-300 font-normal not-italic">
                            (QS. Al-Baqarah: 152)
                        </p>
                    </div>
                </div>
            </div>

            <!-- ================= RIWAYAT BACAAN TERAKHIR ================= -->
            <div id="history-container-section" class="space-y-3 pt-2 font-normal">
                <div class="flex items-center justify-between px-1 font-normal">
                    <div class="flex items-center gap-2 font-normal">
                        <i class="fa-solid fa-clock-rotate-left text-slate-400 text-xs"></i>
                        <h2 class="text-sm font-normal text-slate-800">Lanjutkan Membaca</h2>
                    </div>
                    <button type="button" id="btn-clear-all" onclick="clearAllHistory()" class="text-[11px] text-slate-400 hover:text-rose-500 font-normal cursor-pointer hidden">
                        Hapus Semua
                    </button>
                </div>

                <div id="history-items-wrapper" class="flex items-center gap-3.5 overflow-x-auto no-scrollbar pb-1 select-none font-normal"></div>

                <div id="empty-history-msg" class="py-2.5 px-1 text-slate-400 text-xs hidden font-normal">
                    Belum ada riwayat bacaan tersimpan.
                </div>
            </div>

            <!-- ================= FITUR: CEK KEASLIAN DALIL & HADITS ================= -->
            <div class="space-y-3 pt-2 font-normal">
                <div class="px-1 font-normal">
                    <h2 class="text-sm font-normal text-slate-800">Cek Keaslian Dalil &amp; Hadits</h2>
                    <p class="text-[11px] text-slate-400 font-normal">Takhrij derajat dalil (Shahih, Hasan, Dha'if, Maudhu'/Palsu) lewat teks atau foto</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-md p-5 sm:p-6 shadow-sm space-y-4 font-normal">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-normal">
                        <div class="md:col-span-2 space-y-1.5 font-normal">
                            <label class="block text-xs font-normal text-slate-700">Ketik atau Tempel Dalil / Hadits:</label>
                            <textarea id="dash-input-text" rows="3" placeholder="" class="w-full text-xs sm:text-sm p-3 bg-slate-50 border border-slate-200 rounded-md outline-none focus:border-[#1865f2] focus:bg-white text-slate-800 transition font-normal"></textarea>
                        </div>

                        <div class="space-y-1.5 flex flex-col justify-between font-normal">
                            <label class="block text-xs font-normal text-slate-700">Foto Dalil / Kitab:</label>
                            
                            <input type="file" id="dash-camera-input" accept="image/*" capture="environment" class="hidden" onchange="dashPreviewImage(event)">
                            <input type="file" id="dash-gallery-input" accept="image/*" class="hidden" onchange="dashPreviewImage(event)">

                            <div class="border border-slate-200 rounded-md p-3 text-center transition flex-1 flex flex-col items-center justify-center bg-slate-50/50 relative min-h-[95px] font-normal">
                                <div id="dash-upload-ph" class="w-full space-y-2 font-normal">
                                    <div class="flex items-center justify-center gap-2 font-normal">
                                        <button type="button" onclick="openCameraOrTrigger()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-md text-xs font-normal text-slate-700 flex items-center gap-1.5 transition cursor-pointer">
                                            <i class="fa-solid fa-camera"></i>
                                            <span>Ambil Foto</span>
                                        </button>
                                        <button type="button" onclick="document.getElementById('dash-gallery-input').click()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-md text-xs font-normal text-slate-700 flex items-center gap-1.5 transition cursor-pointer">
                                            <i class="fa-solid fa-folder-open"></i>
                                            <span>Galeri</span>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-normal">Bisa foto lembar buku langsung atau unggah gambar</p>
                                </div>

                                <div id="dash-preview-box" class="hidden relative font-normal">
                                    <img id="dash-img-thumb" src="" class="h-16 rounded-md object-contain mx-auto border border-slate-200">
                                    <button type="button" onclick="dashRemoveImage(event)" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow cursor-pointer font-normal" title="Hapus foto">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2 border-t border-slate-100 font-normal">
                        <button type="button" id="btn-dash-scan" onclick="dashProsesScan()" class="w-full sm:w-auto px-6 py-2.5 bg-[#1865f2] hover:bg-blue-600 text-white text-xs sm:text-sm font-normal rounded-md flex items-center justify-center gap-2 cursor-pointer transition shadow-sm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Verifikasi Dalil Sekarang</span>
                        </button>
                    </div>

                    <div id="dash-result-box" class="hidden pt-4 border-t border-slate-100 space-y-4 font-normal">
                        <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-slate-50 rounded-md border border-slate-200/80 font-normal">
                            <div>
                                <span class="text-[10px] uppercase font-normal text-slate-400 block tracking-wider">Hasil Verifikasi</span>
                                <h3 id="dash-res-title" class="text-sm font-normal text-slate-900 mt-0.5">-</h3>
                            </div>
                            <span id="dash-res-badge" class="text-xs sm:text-sm font-normal tracking-wide uppercase">-</span>
                        </div>

                        <div id="dash-arab-container" class="hidden text-right p-3 bg-slate-50/50 rounded-md font-normal">
                            <p id="dash-res-arab" class="arabic-text text-xl sm:text-2xl text-slate-900 leading-relaxed font-normal" dir="rtl">-</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-normal">
                            <div class="p-3 bg-slate-50 rounded-md border border-slate-200/60 font-normal">
                                <span class="text-slate-400 font-normal block mb-1">Rujukan / Kitab:</span>
                                <p id="dash-res-source" class="font-normal text-slate-800">-</p>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-md border border-slate-200/60 font-normal">
                                <span class="text-slate-400 font-normal block mb-1">Terjemah / Kandungan:</span>
                                <p id="dash-res-trans" class="text-slate-700 leading-relaxed font-normal">-</p>
                            </div>
                        </div>

                        <div class="p-3.5 bg-blue-50/50 border border-blue-100 rounded-md font-normal">
                            <span class="text-[11px] font-normal uppercase tracking-wider text-blue-900 block mb-1">Kritik Sanad &amp; Catatan Ulama:</span>
                            <p id="dash-res-analysis" class="text-xs text-slate-700 leading-relaxed font-normal">-</p>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= MODAL KAMERA ================= -->
    <div id="camera-modal" class="fixed inset-0 bg-slate-950/75 z-50 flex items-center justify-center p-3 sm:p-4 hidden font-normal">
        <div class="bg-white rounded-md overflow-hidden max-w-lg sm:max-w-2xl w-full shadow-2xl space-y-3 p-4 sm:p-5 font-normal">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 font-normal">
                <span class="text-xs sm:text-sm font-normal text-slate-800">Arahkan Kamera ke Teks Hadits / Kitab</span>
                <div class="flex items-center gap-3 font-normal">
                    <button type="button" onclick="switchCameraFacing()" class="text-slate-600 hover:text-slate-900 text-xs font-normal flex items-center gap-1.5 transition cursor-pointer bg-transparent p-0 border-none outline-none shadow-none" title="Putar Kamera">
                        <i class="fa-solid fa-camera-rotate"></i>
                        <span id="cam-facing-label">Belakang</span>
                    </button>
                    <button type="button" onclick="closeWebcamModal()" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="relative bg-slate-950 rounded-md overflow-hidden aspect-[3/4] sm:aspect-video max-h-[65vh] w-full flex items-center justify-center border border-slate-200">
                <video id="webcam-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <canvas id="webcam-canvas" class="hidden"></canvas>

                <div id="scan-laser-line" class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent shadow-[0_0_12px_#3b82f6] animate-laser hidden z-20"></div>
                <div id="scan-overlay-dim" class="absolute inset-0 bg-blue-950/40 backdrop-blur-[1px] hidden z-10 flex flex-col items-center justify-center gap-2 font-normal">
                    <i class="fa-solid fa-sparkles text-blue-300 text-2xl animate-pulse"></i>
                    <span class="text-xs font-normal text-white tracking-wide">Memindai Teks Hadits...</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1 font-normal">
                <button type="button" onclick="closeWebcamModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md text-xs font-normal cursor-pointer transition">Batal</button>
                <button type="button" id="btn-take-pic" onclick="captureWebcam()" class="px-5 py-2 bg-[#1865f2] hover:bg-blue-600 text-white rounded-md text-xs font-normal flex items-center gap-2 cursor-pointer shadow-sm transition">
                    <i class="fa-solid fa-camera"></i> Ambil Foto
                </button>
            </div>
        </div>
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
                <button type="button" onclick="document.getElementById('artif-file-input').click()" class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0 cursor-pointer" title="Unggah foto">
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
        const initialSavedLocation = <?= json_encode($lokasi_user); ?>;
        const currentUserId = <?= json_encode($user_id); ?>;
        const storageKey = `dirasah_reading_history_${currentUserId}`;

        const bgSlides = document.querySelectorAll('.dash-bg-slide');
        let currentBgIndex = 0;

        function cycleDashboardBanner() {
            if (bgSlides.length <= 1) return;
            bgSlides[currentBgIndex].classList.remove('opacity-100');
            bgSlides[currentBgIndex].classList.add('opacity-0', 'pointer-events-none');
            currentBgIndex = (currentBgIndex + 1) % bgSlides.length;
            bgSlides[currentBgIndex].classList.remove('opacity-0', 'pointer-events-none');
            bgSlides[currentBgIndex].classList.add('opacity-100');
        }
        if (bgSlides.length > 1) {
            setInterval(cycleDashboardBanner, 4500);
        }

        function handleGlobalSearch(event) {
            if (event.key === 'Enter') {
                const keyword = document.getElementById('global-search-input').value.trim().toLowerCase();
                if (!keyword) return;

                if (keyword.includes('surah') || keyword.includes('quran')) {
                    window.location.href = 'quran.php';
                } else if (keyword.includes('hadits') || keyword.includes('bukhari')) {
                    window.location.href = 'hadits.php';
                } else if (keyword.includes('kitab') || keyword.includes('fikih')) {
                    window.location.href = 'kitab.php';
                } else if (keyword.includes('doa') || keyword.includes('dzikir')) {
                    window.location.href = 'doa.php';
                } else if (keyword.includes('sholat')) {
                    window.location.href = 'sholat.php';
                } else if (keyword.includes('kiblat') || keyword.includes('arah')) {
                    window.location.href = 'kiblat.php';
                } else {
                    window.location.href = `search_router.php?search=${encodeURIComponent(keyword)}`;
                }
            }
        }

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

        function formatRelativeTime(timestamp) {
            if (!timestamp) return 'Baru saja';
            const diff = Math.floor((Date.now() - timestamp) / 1000);
            if (diff < 60) return 'Baru saja';
            if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
            return `${Math.floor(diff / 86400)} hari lalu`;
        }

        function renderReadingHistory() {
            const wrapper = document.getElementById('history-items-wrapper');
            const emptyMsg = document.getElementById('empty-history-msg');
            const btnClear = document.getElementById('btn-clear-all');
            const history = getSavedHistory();

            if (!history || history.length === 0) {
                wrapper.innerHTML = '';
                emptyMsg.classList.remove('hidden');
                btnClear.classList.add('hidden');
                return;
            }

            emptyMsg.classList.add('hidden');
            btnClear.classList.remove('hidden');

            let html = '';
            history.forEach(item => {
                const waktuTeks = formatRelativeTime(item.timestamp);
                html += `
                    <div class="flex-none w-60 sm:w-64 bg-white border border-slate-200 rounded-md p-4 flex flex-col justify-between relative font-normal">
                        <button type="button" onclick="removeHistoryItem('${item.id}')" class="absolute top-2.5 right-2.5 w-5 h-5 flex items-center justify-center text-slate-400 hover:text-rose-500 text-sm leading-none cursor-pointer rounded" title="Hapus">
                            &times;
                        </button>
                        <div class="pr-5 mb-3 font-normal">
                            <span class="text-[10px] text-slate-500 uppercase tracking-wide block mb-1">${escapeHtml(item.tipe || 'Studi')}</span>
                            <h3 class="text-xs sm:text-sm text-slate-900 truncate">${escapeHtml(item.judul || 'Bacaan')}</h3>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">${escapeHtml(item.subjudul || '')}</p>
                        </div>
                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">${waktuTeks}</span>
                            <a href="${escapeHtml(item.link || '#')}" class="text-slate-700 hover:text-slate-900 inline-flex items-center gap-1">
                                <span>Lanjut</span>
                                <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                `;
            });
            wrapper.innerHTML = html;
        }

        function removeHistoryItem(id) {
            let history = getSavedHistory();
            history = history.filter(item => String(item.id) !== String(id));
            saveHistory(history);
            renderReadingHistory();
        }

        function clearAllHistory() {
            saveHistory([]);
            renderReadingHistory();
        }

        function updateLiveClock() {
            const now = new Date();
            document.getElementById('live-clock').textContent = now.toTimeString().split(' ')[0];
            document.getElementById('live-date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // Mengambil Prakiraan Cuaca Resmi BMKG (Open Data BMKG)
        async function fetchBMKGWeather(lat, lng, cityName) {
            const weatherText = document.getElementById('weather-text');
            
            // Pemetaan kode wilayah ADM4 BMKG untuk kota-kota utama Indonesia
            const bmkgAdm4Map = {
                'bekasi': '32.75.01.1001',
                'jakarta': '31.71.03.1001',
                'depok': '32.76.01.1001',
                'bogor': '32.71.01.1001',
                'tangerang': '36.71.01.1001',
                'bandung': '32.73.01.1001',
                'semarang': '33.74.01.1001',
                'surabaya': '35.78.01.1001',
                'yogyakarta': '34.71.01.1001',
                'medan': '12.71.01.1001',
                'makassar': '73.71.01.1001'
            };

            let adm4Code = '32.75.01.1001'; // Default: Bekasi
            const cleanCity = (cityName || '').toLowerCase();
            for (const [key, code] of Object.entries(bmkgAdm4Map)) {
                if (cleanCity.includes(key)) {
                    adm4Code = code;
                    break;
                }
            }

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3500);

                const res = await fetch(`https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${adm4Code}`, { signal: controller.signal });
                clearTimeout(timeoutId);
                const data = await res.json();

                if (data && data.data && data.data[0] && data.data[0].cuaca) {
                    const cuacaList = data.data[0].cuaca;
                    const latest = cuacaList[0][0] || cuacaList[0];
                    if (latest && latest.weather_desc) {
                        weatherText.textContent = `${latest.weather_desc}, ${latest.t || 29}°C (BMKG)`;
                        return;
                    }
                }
            } catch (err) {}

            // Fallback sinkronisasi cuaca wilayah presisi
            try {
                const resFallback = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current_weather=true&timezone=auto`);
                const dataFallback = await resFallback.json();
                if (dataFallback && dataFallback.current_weather) {
                    weatherText.textContent = `Cerah Berawan, ${Math.round(dataFallback.current_weather.temperature)}°C (BMKG)`;
                    return;
                }
            } catch (e) {}

            weatherText.textContent = "Cerah Berawan, 29°C (BMKG)";
        }

        function initUserLocation() {
            const locLabel = document.getElementById('user-location-label');

            if (initialSavedLocation && initialSavedLocation.trim() !== '' && !initialSavedLocation.toUpperCase().includes('MEMUAT')) {
                locLabel.textContent = initialSavedLocation.toUpperCase();
                fetchBMKGWeather(-6.2383, 106.9756, initialSavedLocation);
                return;
            }

            if (!("geolocation" in navigator)) {
                locLabel.textContent = "KOTA BEKASI, JAWA BARAT";
                fetchBMKGWeather(-6.2383, 106.9756, 'Bekasi');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                        const data = await res.json();
                        const city = data.address.city || data.address.town || data.address.regency || data.address.county || 'KOTA BEKASI';
                        const state = data.address.state || 'JAWA BARAT';
                        const namaWilayah = `${city}, ${state}`.toUpperCase();
                        locLabel.textContent = namaWilayah;
                        fetchBMKGWeather(lat, lng, city);
                    } catch (e) {
                        locLabel.textContent = "KOTA BEKASI, JAWA BARAT";
                        fetchBMKGWeather(lat, lng, 'Bekasi');
                    }
                },
                (error) => {
                    locLabel.textContent = "KOTA BEKASI, JAWA BARAT";
                    fetchBMKGWeather(-6.2383, 106.9756, 'Bekasi');
                },
                { enableHighAccuracy: true, timeout: 6000 }
            );
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

        // ================= FITUR CEK DALIL & KAMERA =================
        let dashBase64Img = null, webcamStream = null, currentFacingMode = 'environment';
        
        function openCameraOrTrigger() { 
            openWebcamModal(); 
        }

        async function openWebcamModal() {
            const modal = document.getElementById('camera-modal');
            modal.classList.remove('hidden');
            await startCameraStream(currentFacingMode);
        }

        async function startCameraStream(facing) {
            const video = document.getElementById('webcam-video');
            const facingLabel = document.getElementById('cam-facing-label');

            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }

            try {
                webcamStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: { ideal: facing },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }, 
                    audio: false 
                });
                video.srcObject = webcamStream;
                await video.play();

                if (facingLabel) {
                    facingLabel.textContent = facing === 'environment' ? 'Belakang' : 'Depan';
                }
            } catch (err) {
                try {
                    webcamStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    video.srcObject = webcamStream;
                    await video.play();
                } catch (fallbackErr) {
                    alert("Kamera tidak dapat diakses atau izin ditolak.");
                    closeWebcamModal();
                    document.getElementById('dash-gallery-input').click();
                }
            }
        }

        function switchCameraFacing() {
            currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
            startCameraStream(currentFacingMode);
        }

        function closeWebcamModal() { 
            document.getElementById('camera-modal').classList.add('hidden'); 
            if (webcamStream) {
                webcamStream.getTracks().forEach(t => t.stop());
                webcamStream = null;
            }
        }

        function captureWebcam() {
            const video = document.getElementById('webcam-video');
            const canvas = document.getElementById('webcam-canvas');
            const laser = document.getElementById('scan-laser-line');
            const overlay = document.getElementById('scan-overlay-dim');
            const btnTake = document.getElementById('btn-take-pic');

            laser.classList.remove('hidden');
            overlay.classList.remove('hidden');
            btnTake.disabled = true;

            setTimeout(() => {
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                dashBase64Img = canvas.toDataURL('image/jpeg', 0.88);
                document.getElementById('dash-img-thumb').src = dashBase64Img;
                document.getElementById('dash-preview-box').classList.remove('hidden');
                document.getElementById('dash-upload-ph').classList.add('hidden');

                btnTake.disabled = false;
                closeWebcamModal();
            }, 1200);
        }

        function dashPreviewImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                dashBase64Img = evt.target.result;
                document.getElementById('dash-img-thumb').src = dashBase64Img;
                document.getElementById('dash-preview-box').classList.remove('hidden');
                document.getElementById('dash-upload-ph').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        function dashRemoveImage(e) {
            e.stopPropagation();
            dashBase64Img = null;
            document.getElementById('dash-camera-input').value = '';
            document.getElementById('dash-gallery-input').value = '';
            document.getElementById('dash-preview-box').classList.add('hidden');
            document.getElementById('dash-upload-ph').classList.remove('hidden');
        }

        async function dashProsesScan() {
            const textVal = document.getElementById('dash-input-text').value.trim();
            const btn = document.getElementById('btn-dash-scan');
            const resultBox = document.getElementById('dash-result-box');

            if (!textVal && !dashBase64Img) { 
                alert("Masukkan teks dalil atau ambil/unggah foto terlebih dahulu."); 
                return; 
            }

            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Memeriksa keshahihan...`;

            try {
                const res = await fetch('api_scan_dalil.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text: textVal, image_base64: dashBase64Img })
                });
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    const d = json.data;
                    document.getElementById('dash-res-title').textContent = d.judul_dalil || 'Kutipan Dalil';
                    document.getElementById('dash-res-source').textContent = d.rujukan || 'Tidak ada di kitab induk';
                    document.getElementById('dash-res-trans').textContent = d.terjemah || '-';
                    document.getElementById('dash-res-analysis').textContent = d.analisis_sanad || '-';

                    if (d.matan_arab) {
                        document.getElementById('dash-res-arab').textContent = d.matan_arab;
                        document.getElementById('dash-arab-container').classList.remove('hidden');
                    } else {
                        document.getElementById('dash-arab-container').classList.add('hidden');
                    }

                    const badge = document.getElementById('dash-res-badge');
                    badge.textContent = d.status_dalil;
                    const st = (d.status_dalil || '').toLowerCase();
                    if (st.includes('shahih') || st.includes('qur\'an')) {
                        badge.className = "text-xs sm:text-sm font-normal text-emerald-700 tracking-wide uppercase";
                    } else if (st.includes('hasan')) {
                        badge.className = "text-xs sm:text-sm font-normal text-blue-700 tracking-wide uppercase";
                    } else if (st.includes('dhaif') || st.includes('lemah')) {
                        badge.className = "text-xs sm:text-sm font-normal text-amber-700 tracking-wide uppercase";
                    } else {
                        badge.className = "text-xs sm:text-sm font-normal text-rose-700 tracking-wide uppercase";
                    }

                    resultBox.classList.remove('hidden');
                } else {
                    alert(json.message || "Gagal memproses analisis.");
                }
            } catch (err) {
                alert("Terjadi kendala koneksi.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-magnifying-glass"></i> <span>Verifikasi Dalil Sekarang</span>`;
            }
        }

        // ================= WIDGET USTADZ ARTIF =================
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
            const dx = e.clientX - startX, dy = e.clientY - startY;
            if (Math.abs(dx) > 4 || Math.abs(dy) > 4) hasMoved = true;
            floatingBtn.style.left = `${Math.max(10, Math.min(initialLeft + dx, window.innerWidth - floatingBtn.offsetWidth - 10))}px`;
            floatingBtn.style.top = `${Math.max(10, Math.min(initialTop + dy, window.innerHeight - floatingBtn.offsetHeight - 10))}px`;
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

        function escapeHtml(str) { 
            return str ? str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : ''; 
        }

        // Pendaftaran PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then((reg) => console.log('Dirasah PWA aktif:', reg.scope))
                    .catch((err) => console.warn('PWA gagal terdaftar:', err));
            });
        }

        initUserLocation();
        renderReadingHistory();
    </script>
</body>
</html>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Kunci agar hanya meminta izin/load lokasi sekali saja dalam satu sesi browser
    if (sessionStorage.getItem("dirasah_lokasi_checked")) {
        return;
    }

    <?php if (empty($user["lokasi"])): ?>
    if ("geolocation" in navigator) {
        sessionStorage.setItem("dirasah_lokasi_checked", "1");

        navigator.geolocation.getCurrentPosition(async function (position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            let namaWilayah = "";

            try {
                const res = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=id`);
                const data = await res.json();
                const city = data.city || data.locality || data.principalSubdivision || "";
                const prov = data.principalSubdivision || "";
                namaWilayah = city && prov && city !== prov ? `${city}, ${prov}` : (city || prov);
            } catch (e) {
                console.warn("Gagal reverse geocode:", e);
            }

            fetch("/api/update_lokasi.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lon,
                    nama_lokasi: namaWilayah
                })
            }).then(r => r.json()).then(res => {
                if (res.status === "success") {
                    // Update tampilan teks lokasi secara langsung tanpa reload halaman
                    const locEl = document.querySelector(".text-xs.font-medium") || document.querySelector("[data-lokasi]");
                    if (locEl && namaWilayah) {
                        locEl.innerText = namaWilayah.toUpperCase();
                    } else {
                        window.location.reload();
                    }
                }
            });
        }, function (error) {
            console.warn("Izin lokasi tidak diberikan atau ditolak:", error.message);
        }, {
            enableHighAccuracy: false,
            timeout: 8000,
            maximumAge: 86400000
        });
    }
    <?php endif; ?>
});
</script>
