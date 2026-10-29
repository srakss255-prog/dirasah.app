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
$lokasi_user = 'BEKASI, WEST JAVA (-6.2736, 106.9348)';

$user_lat = -6.2736;
$user_lng = 106.9348;

try {
    $qUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $qUser->execute([$user_id]);
    $curUser = $qUser->fetch(PDO::FETCH_ASSOC);
    if ($curUser) {
        $nama_user = !empty($curUser['nama']) ? $curUser['nama'] : (!empty($curUser['username']) ? $curUser['username'] : $nama_user);$foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
        if (!empty($curUser['lokasi'])) {
            $lokasi_user =$curUser['lokasi'];
            if (preg_match('/\((-?\d+\.?\d*),\s*(-?\d+\.?\d*)\)/', $lokasi_user,$matches)) {
                $user_lat = floatval($matches[1]);
                $user_lng = floatval($matches[2]);
            }
        }
    }
} catch (Exception $e) {$foto_user = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Arah Kiblat - Dirasah.id</title>
    <!-- Google Fonts -->
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
        .sidebar-transition {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        a, button, .prompt-btn {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }
        .compass-smooth {
            transition: transform 0.15s cubic-bezier(0.1, 0.7, 0.1, 1);
            will-change: transform;
        }
    </style>
    <link rel="icon" type="image/png" href="assets/putih.png">
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

    <!-- Sidebar Bilah Sisi Identik -->
    <aside id="main-sidebar" class="sidebar-transition fixed top-0 bottom-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col w-64 -translate-x-full lg:translate-x-0 select-none font-normal">
        <div class="h-20 px-3 border-b border-slate-200 flex items-center justify-between shrink-0">
            <button type="button" onclick="toggleSidebar()" class="flex items-center gap-3 w-full p-1 focus:outline-none cursor-pointer" title="Buka / Tutup Menu">
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

        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            <div>
                <span class="sidebar-text px-3 text-[11px] font-normal uppercase tracking-wider text-slate-400 block mb-2">Utama</span>
                <nav class="space-y-1">
                    <a href="dashboard.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
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
                    <a href="quran.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/quran.png" alt="Al-Qur'an" class="w-8 h-auto max-h-7 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Al-Qur'an Digital</span>
                    </a>

                    <a href="hadits.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/hadis.png" alt="Hadits" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Kutubut Tis'ah &amp; Hadits</span>
                    </a>

                    <a href="kitab.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kitab.png" alt="Kitab &amp; Syarah" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Kitab &amp; Syarah</span>
                    </a>

                    <a href="doa.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
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
                    <a href="sholat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/sholat.png" alt="Jadwal Sholat" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Jadwal Sholat</span>
                    </a>

                    <a href="kiblat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-[#1865f2] bg-blue-50/60">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kiblat.png" alt="Arah Kiblat" class="w-9 h-auto max-h-8 object-contain mix-blend-multiply">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Arah Kiblat</span>
                    </a>

                    <a href="kalender.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-slate-700 hover:bg-slate-50">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kalender.png" alt="Kalender Islam" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap">Kalender Islam</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div id="content-area" class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-64 font-normal">
        
        <!-- Header -->
        <header class="w-full bg-white border-b border-slate-200 h-16 px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 font-normal">
            <div class="flex items-center gap-3 flex-1 max-w-xl">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-700 hover:text-[#1865f2] p-1.5 focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center text-xs font-normal text-slate-500">
                    <span id="header-location"><?= htmlspecialchars($lokasi_user); ?></span>
                </div>
            </div>

            <div class="flex items-center">
                <a href="profil.php" class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-slate-100 transition group" title="Buka Profil Saya">
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

            <!-- Title & Lokasi -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
                <div>
                    <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Penentu Arah Kiblat</h1>
                    <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Penghitungan derajat arah Ka'bah (Mekkah) berdasarkan koordinat posisi Anda.</p>
                </div>
                <button id="btn-action-trigger" type="button" onclick="handleMainAction()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-normal rounded-md border border-slate-300 shadow-sm transition self-start sm:self-auto cursor-pointer">
                    Gunakan GPS Presisi
                </button>
            </div>

            <!-- Konten Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Panel Visual Kompas Dial DetikHikmah Style -->
                <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 p-6 sm:p-8 shadow-sm flex flex-col items-center justify-center">
                    
                    <div class="text-center mb-6">
                        <span class="text-xs uppercase tracking-wider text-slate-400 font-normal block">Sudut Derajat Kiblat</span>
                        <div class="text-4xl font-normal text-slate-900 tracking-tight mt-1">
                            <span id="qibla-degree-val">295.14</span>° <span class="text-base font-normal text-slate-500">BBU (Barat Laut)</span>
                        </div>
                        <p id="sensor-status" class="text-xs text-slate-500 mt-1.5 max-w-sm font-normal">
                            Sensor kompas smartphone aktif.
                        </p>
                    </div>

                    <div class="text-[11px] font-normal text-slate-400 mb-2 uppercase tracking-wider">
                        Arah Depan HP
                    </div>

                    <!-- Wadah Kompas Dial Ala DetikHikmah -->
                    <div class="relative w-64 h-64 sm:w-72 sm:h-72 flex items-center justify-center my-3 select-none">
                        
                        <!-- Piringan Dial Kompas (Berputar melawan arah hadap HP) -->
                        <div id="compass-dial" class="compass-smooth absolute inset-0 w-full h-full flex items-center justify-center">
                            <svg viewBox="0 0 320 320" class="w-full h-full drop-shadow-sm">
                                <!-- Ring Luar Putih -->
                                <circle cx="160" cy="160" r="156" fill="#ffffff" stroke="#cbd5e1" stroke-width="2.5" />
                                
                                <!-- Cincin Hijau Tua DetikHikmah -->
                                <circle cx="160" cy="160" r="126" fill="none" stroke="#25644d" stroke-width="36" />
                                
                                <!-- Lingkaran Tengah Putih Dalam -->
                                <circle cx="160" cy="160" r="108" fill="#ffffff" stroke="#e2e8f0" stroke-width="1.5" />

                                <!-- Tanda Garis Derajat -->
                                <g stroke="#475569" stroke-width="1.5" stroke-linecap="round">
                                    <line x1="160" y1="5" x2="160" y2="16" />
                                    <line x1="160" y1="304" x2="160" y2="315" />
                                    <line x1="5" y1="160" x2="16" y2="160" />
                                    <line x1="304" y1="160" x2="315" y2="160" />

                                    <line x1="269" y1="51" x2="261" y2="59" />
                                    <line x1="51" y1="269" x2="59" y2="261" />
                                    <line x1="51" y1="51" x2="59" y2="59" />
                                    <line x1="269" y1="269" x2="261" y2="261" />
                                </g>

                                <!-- Angka Derajat Luar -->
                                <text x="160" y="32" text-anchor="middle" font-size="12" fill="#334155">0</text>
                                <text x="295" y="164" text-anchor="middle" font-size="11" fill="#334155">90</text>
                                <text x="160" y="298" text-anchor="middle" font-size="11" fill="#334155">180</text>
                                <text x="25" y="164" text-anchor="middle" font-size="11" fill="#334155">270</text>

                                <!-- Huruf Mata Angin di Cincin Hijau (U, T, S, B) -->
                                <text x="160" y="78" text-anchor="middle" font-size="18" fill="#ffffff">U</text>
                                <text x="246" y="166" text-anchor="middle" font-size="18" fill="#ffffff">T</text>
                                <text x="160" y="254" text-anchor="middle" font-size="18" fill="#ffffff">S</text>
                                <text x="74" y="166" text-anchor="middle" font-size="18" fill="#ffffff">B</text>
                            </svg>
                        </div>

                        <!-- Jarum Penunjuk Kiblat Merah Runcing Ala DetikHikmah -->
                        <div id="qibla-pointer" class="compass-smooth absolute inset-0 w-full h-full flex items-center justify-center pointer-events-none" style="transform: rotate(295.14deg);">
                            <svg viewBox="0 0 320 320" class="w-full h-full drop-shadow-md">
                                <!-- Jarum Runcing Merah Mengarah ke Kiblat -->
                                <path d="M 160 52 L 174 160 L 160 148 L 146 160 Z" fill="#e11d48" stroke="#be123c" stroke-width="1.5" stroke-linejoin="round" />
                                <!-- Ekor Jarum Abu-abu -->
                                <path d="M 160 268 L 168 160 L 160 170 L 152 160 Z" fill="#94a3b8" />
                            </svg>
                        </div>

                        <!-- Poros Tengah: Ka'bah Miniatur 3D DetikHikmah -->
                        <div class="absolute z-20 w-14 h-14 rounded-full bg-white border border-slate-200 shadow-md flex items-center justify-center pointer-events-none">
                            <svg viewBox="0 0 36 36" class="w-8 h-8">
                                <polygon points="18,3 31,10 18,17 5,10" fill="#23272e" />
                                <polygon points="5,10 18,17 18,32 5,25" fill="#181a1f" />
                                <polygon points="31,10 18,17 18,32 31,25" fill="#0b0c0e" />
                                <polygon points="5,14 18,21 18,23 5,16" fill="#facc15" />
                                <polygon points="31,14 18,21 18,23 31,16" fill="#eab308" />
                            </svg>
                        </div>

                    </div>

                    <!-- Indikator Keterangan -->
                    <div class="flex items-center gap-6 mt-6 text-xs text-slate-600 font-normal">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#e11d48] inline-block"></span>
                            <span>Jarum Kiblat (Ka'bah)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#25644d] inline-block"></span>
                            <span>Mata Angin Kompas</span>
                        </div>
                    </div>

                </div>

                <!-- Panel Informasi Koordinat & Jarak -->
                <div class="lg:col-span-5 space-y-4 font-normal">
                    
                    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-normal text-slate-900 pb-3 border-b border-slate-100">Detail Posisi &amp; Jarak</h3>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-slate-50">
                                <span class="text-slate-500">Lokasi Anda</span>
                                <span id="pos-loc-name" class="font-normal text-slate-800 truncate max-w-[200px]"><?= htmlspecialchars($lokasi_user) ?></span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50">
                                <span class="text-slate-500">Koordinat Lintang (Lat)</span>
                                <span id="pos-lat" class="text-slate-800 font-normal"><?= $user_lat ?>°</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50">
                                <span class="text-slate-500">Koordinat Bujur (Lng)</span>
                                <span id="pos-lng" class="text-slate-800 font-normal"><?= $user_lng ?>°</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50">
                                <span class="text-slate-500">Jarak ke Ka'bah (Mekkah)</span>
                                <span id="pos-dist" class="font-normal text-slate-800">Menghitung...</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-500">Azimuth Kiblat</span>
                                <span id="pos-azimuth" class="font-normal text-[#25644d]">295.14°</span>
                            </div>
                        </div>
                    </div>

                    <!-- Panduan Cara Penggunaan -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-3 text-xs text-slate-600">
                        <h4 class="font-normal text-slate-900">Petunjuk Penggunaan:</h4>
                        <ol class="list-decimal list-inside space-y-1.5 text-slate-500 leading-relaxed font-normal">
                            <li>Buka halaman ini di browser smartphone (HP) Anda.</li>
                            <li>Letakkan smartphone di permukaan datar yang jauh dari medan magnet atau benda logam.</li>
                            <li>Putar tubuh Anda bersama perangkat sampai ujung jarum runcing merah tegak lurus mengarah tepat ke atas depan Anda.</li>
                        </ol>
                    </div>

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
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                    <img src="assets/artif.png" onerror="this.onerror=null; this.src='artif.png';" alt="" class="w-full h-full object-cover object-top">
                </div>
                <div>
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

        <!-- Wadah Preview Gambar -->
        <div id="artif-image-preview-container" class="hidden px-4 py-2 bg-slate-100/80 border-t border-slate-200 flex items-center justify-between shrink-0 font-normal">
            <div class="flex items-center gap-2.5">
                <img id="artif-image-preview" src="" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                <span class="text-[11px] text-slate-600 font-normal">Gambar siap dianalisis</span>
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
                    class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0 cursor-pointer" 
                    title="Unggah foto teks hadits / lembaran kitab"
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
        const KAABA_LAT = 21.422487;
        const KAABA_LNG = 39.826206;

        let currentLat = <?= $user_lat ?>;
        let currentLng = <?= $user_lng ?>;
        let qiblaBearing = 295.14;
        let isSensorListening = false;

        function calculateQibla(lat, lng) {
            const phi1 = lat * Math.PI / 180;
            const phi2 = KAABA_LAT * Math.PI / 180;
            const deltaLambda = (KAABA_LNG - lng) * Math.PI / 180;

            const y = Math.sin(deltaLambda);
            const x = Math.cos(phi1) * Math.tan(phi2) - Math.sin(phi1) * Math.cos(deltaLambda);
            let q = Math.atan2(y, x) * 180 / Math.PI;
            return (q + 360) % 360;
        }

        function calculateDistance(lat1, lon1) {
            const R = 6371;
            const dLat = (KAABA_LAT - lat1) * Math.PI / 180;
            const dLon = (KAABA_LNG - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(KAABA_LAT * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return Math.round(R * c);
        }

        function updateQiblaDisplay(lat, lng) {
            currentLat = lat;
            currentLng = lng;
            qiblaBearing = calculateQibla(lat, lng);
            const dist = calculateDistance(lat, lng);

            document.getElementById('qibla-degree-val').textContent = qiblaBearing.toFixed(2);
            document.getElementById('pos-azimuth').textContent = `${qiblaBearing.toFixed(2)}°`;
            document.getElementById('pos-lat').textContent = `${lat.toFixed(4)}°`;
            document.getElementById('pos-lng').textContent = `${lng.toFixed(4)}°`;
            document.getElementById('pos-dist').textContent = `± ${dist.toLocaleString('id-ID')} km`;

            // Atur sudut jarum penunjuk kiblat
            document.getElementById('qibla-pointer').style.transform = `rotate(${qiblaBearing}deg)`;
        }

        function handleOrientation(e) {
            let heading = null;

            if (typeof e.webkitCompassHeading !== 'undefined' && e.webkitCompassHeading !== null) {
                heading = e.webkitCompassHeading;
            } else if (e.alpha !== null && typeof e.alpha !== 'undefined') {
                heading = (360 - e.alpha) % 360;
            }

            if (heading !== null && !isNaN(heading)) {
                const roundedHeading = Math.round(heading);
                document.getElementById('sensor-status').innerHTML = `Sensor HP aktif. Menghadap: <strong class="text-[#25644d] font-normal">${roundedHeading}°</strong>`;
                
                // Dial kompas berputar berlawanan arah putar HP
                document.getElementById('compass-dial').style.transform = `rotate(${-roundedHeading}deg)`;
                // Jarum kiblat tetap stabil mengarah ke kiblat terhadap posisi device
                document.getElementById('qibla-pointer').style.transform = `rotate(${qiblaBearing - roundedHeading}deg)`;
            }
        }

        function attachCompassListeners() {
            if (isSensorListening) return;

            if ('ondeviceorientationabsolute' in window) {
                window.addEventListener('deviceorientationabsolute', handleOrientation, true);
                isSensorListening = true;
            } else if ('ondeviceorientation' in window) {
                window.addEventListener('deviceorientation', handleOrientation, true);
                isSensorListening = true;
            }
        }

        function handleMainAction() {
            detectGPSLocation();

            const btn = document.getElementById('btn-action-trigger');
            if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission()
                    .then(response => {
                        if (response === 'granted') {
                            window.addEventListener('deviceorientation', handleOrientation, true);
                            isSensorListening = true;
                            if (btn) btn.textContent = 'Sensor & GPS Aktif';
                            document.getElementById('sensor-status').textContent = 'Sensor kompas smartphone aktif.';
                        } else {
                            alert('Izin sensor kompas ditolak.');
                        }
                    })
                    .catch(console.error);
            } else {
                attachCompassListeners();
                if (btn) btn.textContent = 'Sensor & GPS Aktif';
            }
        }

        function initDeviceOrientation() {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const btn = document.getElementById('btn-action-trigger');

            if (isIOS && typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                if (btn) btn.textContent = 'Aktifkan Sensor & GPS';
                document.getElementById('sensor-status').textContent = 'Klik tombol di kanan atas untuk mengizinkan sensor kompas.';
            } else {
                attachCompassListeners();
                document.getElementById('sensor-status').textContent = 'Sensor kompas smartphone aktif.';
            }
        }

        function detectGPSLocation() {
            const btn = document.getElementById('btn-action-trigger');
            if (!navigator.geolocation) {
                alert("Browser tidak mendukung geolokasi GPS.");
                return;
            }

            if (btn && btn.textContent !== 'Sensor & GPS Aktif') {
                btn.textContent = "Mendeteksi...";
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    document.getElementById('header-location').textContent = `Koordinat GPS (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                    document.getElementById('pos-loc-name').textContent = `GPS Presisi (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                    updateQiblaDisplay(lat, lng);
                },
                (err) => {
                    alert("Tidak dapat mengakses GPS. Menggunakan lokasi profil.");
                },
                { enableHighAccuracy: true, timeout: 10000 }
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

        // Floating Widget Ustadz Artif
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

        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        updateQiblaDisplay(currentLat, currentLng);
        initDeviceOrientation();
    </script>

<footer style="text-align: center; padding: 25px 0; margin-top: 40px; font-size: 14px; opacity: 0.85;">
  <p>&copy; 2026 Dirasah. Developed and Created by <strong>Shahzada Rakhshaa Aprihansyah</strong>.</p>
  <p><a href="https://github.com/srakss255-prog" target="_blank" rel="noopener noreferrer" style="text-decoration: underline; color: inherit;">Developer GitHub Profile</a> | <a href="/humans.txt" style="text-decoration: underline; color: inherit;">Humans.txt</a></p>
</footer>
</body>
</html>