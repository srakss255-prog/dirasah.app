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
$no_wa_user = '';

// Handle Simpan Nomor WA Langsung via POST Ajax
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_wa') {
    header('Content-Type: application/json');
    $raw_phone = trim($_POST['phone'] ?? '');
    $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);
    if (str_starts_with($clean_phone, '0')) {
        $clean_phone = '62' . substr($clean_phone, 1);
    } elseif (!str_starts_with($clean_phone, '62') && !empty($clean_phone)) {
        $clean_phone = '62' . $clean_phone;
    }

    try {
        try {
            $pdo->query("SELECT no_wa FROM users LIMIT 1");
        } catch (Exception $colErr) {
            $pdo->exec("ALTER TABLE users ADD COLUMN no_wa VARCHAR(25) NULL AFTER lokasi");
        }

        $upd = $pdo->prepare("UPDATE users SET no_wa = ? WHERE id = ?");
        $upd->execute([$clean_phone, $user_id]);
        echo json_encode(['status' => true, 'message' => 'Nomor WhatsApp berhasil disimpan untuk akun Anda.']);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

try {
    $qUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $qUser->execute([$user_id]);
    $curUser = $qUser->fetch(PDO::FETCH_ASSOC);
    if ($curUser) {
        $nama_user = !empty($curUser['nama']) ? $curUser['nama'] : (!empty($curUser['username']) ? $curUser['username'] : $nama_user);
        $foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
        if (!empty($curUser['lokasi'])) {
            $lokasi_user = $curUser['lokasi'];
        }
        if (!empty($curUser['no_wa'])) {
            $no_wa_user = $curUser['no_wa'];
        }
    }
} catch (Exception $e) {
    $foto_user = null;
}

$banner_v = file_exists('assets/pik2.png') ? filemtime('assets/pik2.png') : time();
$fav_v    = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();
$logo_v   = file_exists('assets/dirasah.png') ? filemtime('assets/dirasah.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Sholat &amp; Sistem Pengingat Sholat Otomatis - Dirasah.id</title>
    <!-- Favicon Tab Browser (putih.png) -->
    <link rel="icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="apple-touch-icon" href="assets/putih.png?v=<?= $fav_v; ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body, input, textarea, button, select, p, span, h1, h2, h3, h4, h5, h6, a, div, table, th, td {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 400 !important;
        }
        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }
        .sidebar-transition {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .alarm-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            position: relative;
            cursor: pointer;
        }
        .alarm-btn-active {
            color: #0d9488;
        }
        .alarm-btn-inactive {
            color: #94a3b8;
        }

        a, button, .prompt-btn {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }

        /* Pengaturan Cetak PDF: Wajib Portrait & Pas 1 Halaman A4 */
        @page {
            size: A4 portrait;
            margin: 12mm 14mm 10mm 14mm;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                height: 100%;
                overflow: hidden !important;
            }
            body * {
                visibility: hidden;
            }
            #print-area, #print-area * {
                visibility: visible;
            }
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }
            #print-area h3 {
                font-size: 16px !important;
                margin-bottom: 2px !important;
            }
            #print-area p#table-month-label {
                font-size: 11px !important;
                margin-bottom: 8px !important;
            }
            #print-area table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            #print-area th {
                padding: 7px 8px !important;
                font-size: 10.5px !important;
                background-color: #f1f5f9 !important;
                border-bottom: 1.5px solid #cbd5e1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            #print-area td {
                padding: 6px 8px !important;
                font-size: 10.5px !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }
            .no-print {
                display: none !important;
            }
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
                    <!-- Menu Jadwal Sholat (Aktif) -->
                    <a href="sholat.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-[#1865f2] bg-blue-50/60">
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

    <!-- Konten Utama Halaman Jadwal Sholat -->
    <div id="content-area" class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-64 font-normal">
        
        <header class="w-full bg-white border-b border-slate-200 h-16 px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 font-normal">
            <div class="flex items-center gap-3 flex-1 max-w-xl font-normal">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-700 hover:text-[#1865f2] p-1.5 focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center text-xs font-normal text-slate-500">
                    <span id="header-location"><?= htmlspecialchars($lokasi_user); ?></span>
                </div>
            </div>

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
            
            <!-- Banner Waktu Sholat: Menggunakan assets/pik2.png -->
            <div class="relative w-full rounded-md overflow-hidden shadow-sm bg-slate-950 p-6 sm:p-8 text-white min-h-[200px] flex flex-col justify-between font-normal">
                <img 
                    src="assets/pik2.png?v=<?= $banner_v; ?>" 
                    alt="Banner Sholat" 
                    class="absolute inset-0 w-full h-full object-cover object-center z-0 opacity-40"
                >
                <div class="absolute inset-0 bg-slate-950/40 z-0"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6 font-normal">
                    <div class="space-y-1 font-normal">
                        <span class="text-[11px] font-normal tracking-wider text-slate-300 uppercase block">
                            WAKTU REALTIME INDONESIA
                        </span>
                        <div id="live-clock" class="text-3xl sm:text-4xl font-normal tracking-tight">
                            --:--:--
                        </div>
                        <p id="live-date" class="text-xs sm:text-sm text-slate-300 font-normal">
                            Memuat tanggal...
                        </p>
                    </div>

                    <div class="max-w-md text-left md:text-right space-y-1.5 font-normal">
                        <span class="text-[11px] font-normal text-amber-400 uppercase tracking-wider block">Menuju Waktu Sholat Berikutnya</span>
                        <h2 id="next-prayer-name" class="text-xl sm:text-2xl font-normal text-white">
                            Memuat...
                        </h2>
                        <div id="countdown-timer" class="text-2xl sm:text-3xl font-mono font-normal text-amber-300">
                            --:--:--
                        </div>
                    </div>
                </div>

                <!-- Baris Waktu Sholat Lengkap Hari Ini -->
                <div class="relative z-10 pt-6 mt-4 border-t border-white/10 grid grid-cols-4 sm:grid-cols-8 gap-2.5 text-center font-normal">
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-slate-400 uppercase font-normal block">Imsak</span>
                        <span id="time-imsak" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-amber-500 uppercase font-normal block">Shubuh</span>
                        <span id="time-subuh" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-slate-400 uppercase font-normal block">Terbit</span>
                        <span id="time-terbit" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-slate-400 uppercase font-normal block">Dhuha</span>
                        <span id="time-dhuha" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-amber-500 uppercase font-normal block">Dzuhur</span>
                        <span id="time-dzuhur" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-amber-500 uppercase font-normal block">Ashar</span>
                        <span id="time-ashar" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-amber-500 uppercase font-normal block">Maghrib</span>
                        <span id="time-maghrib" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                    <div class="space-y-1 font-normal">
                        <span class="text-[10px] text-amber-500 uppercase font-normal block">Isya'</span>
                        <span id="time-isya" class="text-sm sm:text-base font-normal text-white">--:--</span>
                    </div>
                </div>
            </div>

            <!-- Panel Pengingat Otomatis Dari Web ke WhatsApp -->
            <div class="p-6 bg-white border border-slate-200 rounded-md space-y-5 font-normal">
                <div class="pb-3 border-b border-slate-100 font-normal">
                    <h3 class="text-sm font-semibold text-slate-800">Sistem Pengingat Sholat Otomatis</h3>
                    <p class="text-xs text-slate-500 font-normal mt-0.5">Sistem web akan langsung mengirim pesan WhatsApp ke nomor Anda saat waktu sholat tiba.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-1 items-start font-normal">
                    <!-- Kolom Kiri: Input Nomor & Tombol Aksi -->
                    <div class="space-y-3.5 font-normal">
                        <div class="font-normal">
                            <label class="text-xs font-medium text-slate-700 block mb-1.5">Nomor WhatsApp Penerima:</label>
                            <input 
                                type="text" 
                                id="input-wa" 
                                placeholder="Masukkan nomor WhatsApp Anda" 
                                value="" 
                                class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-md text-slate-800 outline-none focus:border-slate-400 font-normal"
                            >
                            <p class="text-[11px] text-slate-400 mt-1 font-normal">Pesan akan masuk otomatis ke nomor ini tanpa Anda perlu mengetik apa pun.</p>
                        </div>

                        <!-- Form Kustom Teks Pesan Pengingat WhatsApp -->
                        

                        <!-- Tombol Aksi: Tes Kirim & Simpan Nomor -->
                        <div class="pt-2 flex flex-wrap items-center gap-2 font-normal">
                            <button type="button" id="btn-test-send" onclick="sendTestNotification()" class="px-4 py-2 rounded-md bg-[#1865f2] hover:bg-blue-600 text-white text-xs font-normal transition cursor-pointer flex items-center gap-2 shadow-sm">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>Tes Kirim Pesan ke WA</span>
                            </button>
                            <button type="button" id="btn-save-wa" onclick="saveUserPhoneNumber()" class="px-3.5 py-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-normal transition border border-slate-300 cursor-pointer">
                                <span>Simpan Nomor</span>
                            </button>
                        </div>

                        <p id="send-log-msg" class="text-xs text-slate-500 font-normal pt-1">
                            Saat waktu sholat tiba, website akan mengirim pesan WA secara otomatis ke HP Anda.
                        </p>
                    </div>

                    <!-- Kolom Kanan: Daftar Alarm Waktu Sholat -->
                    <div class="space-y-2 font-normal">
                        <label class="text-xs font-medium text-slate-700 block">Pilih Waktu Sholat yang Diingatkan ke WA:</label>
                        
                        <div class="bg-white rounded-md border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-sm font-normal">
                            
                            <!-- Imsak -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-slate-700 text-xs font-normal">Imsak</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-imsak" class="text-slate-700 text-xs font-normal">04:05</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-imsak" class="hidden" onchange="toggleAlarmIcon(this, 'icon-imsak')">
                                        <div id="icon-imsak" class="alarm-toggle alarm-btn-inactive">
                                            <i class="fa-regular fa-bell text-base text-teal-600/70"></i>
                                            <i class="fa-solid fa-xmark text-[9px] absolute text-rose-400"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Subuh -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-slate-700 text-xs font-normal">Subuh</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-subuh" class="text-slate-700 text-xs font-normal">04:15</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-subuh" checked class="hidden" onchange="toggleAlarmIcon(this, 'icon-subuh')">
                                        <div id="icon-subuh" class="alarm-toggle alarm-btn-active">
                                            <i class="fa-regular fa-bell text-base text-teal-600"></i>
                                            <i class="fa-solid fa-check text-[9px] absolute text-teal-600"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Terbit -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-slate-700 text-xs font-normal">Terbit</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-terbit" class="text-slate-700 text-xs font-normal">05:28</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-terbit" class="hidden" onchange="toggleAlarmIcon(this, 'icon-terbit')">
                                        <div id="icon-terbit" class="alarm-toggle alarm-btn-inactive">
                                            <i class="fa-regular fa-bell text-base text-teal-600/70"></i>
                                            <i class="fa-solid fa-xmark text-[9px] absolute text-rose-400"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Dzuhur -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-[#b4916c] text-xs font-normal">Dzuhur</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-dzuhur" class="text-[#b4916c] text-xs font-normal">11:41</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-dzuhur" checked class="hidden" onchange="toggleAlarmIcon(this, 'icon-dzuhur')">
                                        <div id="icon-dzuhur" class="alarm-toggle alarm-btn-active">
                                            <i class="fa-regular fa-bell text-base text-teal-600"></i>
                                            <i class="fa-solid fa-check text-[9px] absolute text-teal-600"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Ashar -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-slate-700 text-xs font-normal">Ashar</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-ashar" class="text-slate-700 text-xs font-normal">14:44</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-ashar" checked class="hidden" onchange="toggleAlarmIcon(this, 'icon-ashar')">
                                        <div id="icon-ashar" class="alarm-toggle alarm-btn-active">
                                            <i class="fa-regular fa-bell text-base text-teal-600"></i>
                                            <i class="fa-solid fa-check text-[9px] absolute text-teal-600"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Maghrib -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-slate-700 text-xs font-normal">Maghrib</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-maghrib" class="text-slate-700 text-xs font-normal">17:48</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-maghrib" checked class="hidden" onchange="toggleAlarmIcon(this, 'icon-maghrib')">
                                        <div id="icon-maghrib" class="alarm-toggle alarm-btn-active">
                                            <i class="fa-regular fa-bell text-base text-teal-600"></i>
                                            <i class="fa-solid fa-check text-[9px] absolute text-teal-600"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Isya -->
                            <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 font-normal">
                                <span class="text-slate-700 text-xs font-normal">Isya'</span>
                                <div class="flex items-center gap-8 font-normal">
                                    <span id="alarm-time-isya" class="text-slate-700 text-xs font-normal">18:57</span>
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" id="check-isya" checked class="hidden" onchange="toggleAlarmIcon(this, 'icon-isya')">
                                        <div id="icon-isya" class="alarm-toggle alarm-btn-active">
                                            <i class="fa-regular fa-bell text-base text-teal-600"></i>
                                            <i class="fa-solid fa-check text-[9px] absolute text-teal-600"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Jadwal Sholat Bulanan Resmi Kemenag RI & Fitur Cetak PDF -->
            <div id="print-area" class="p-6 bg-white border border-slate-200 rounded-md space-y-4 font-normal">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 font-normal">
                    <div class="font-normal">
                        <h3 class="text-sm font-normal text-slate-900">Jadwal Sholat Bulanan Resmi Kemenag RI</h3>
                        <p id="table-month-label" class="text-xs text-slate-500 font-normal">Memuat bulan...</p>
                    </div>
                    <!-- Tombol Cetak / Simpan PDF -->
                    <button type="button" onclick="printJadwalPDF()" class="no-print inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-normal transition cursor-pointer">
                        <i class="fa-solid fa-print text-slate-500 text-xs"></i>
                        <span>Cetak PDF</span>
                    </button>
                </div>

                <div class="overflow-x-auto font-normal">
                    <table class="w-full text-left text-xs font-normal">
                        <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-normal">
                            <tr>
                                <th class="py-2.5 px-3 font-normal">Tanggal</th>
                                <th class="py-2.5 px-3 font-normal">Imsak</th>
                                <th class="py-2.5 px-3 font-normal">Shubuh</th>
                                <th class="py-2.5 px-3 font-normal">Terbit</th>
                                <th class="py-2.5 px-3 font-normal">Dhuha</th>
                                <th class="py-2.5 px-3 font-normal">Dzuhur</th>
                                <th class="py-2.5 px-3 font-normal">Ashar</th>
                                <th class="py-2.5 px-3 font-normal">Maghrib</th>
                                <th class="py-2.5 px-3 font-normal">Isya'</th>
                            </tr>
                        </thead>
                        <tbody id="monthly-table-body" class="divide-y divide-slate-100 text-slate-700 font-normal">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 font-normal">
                                    <i class="fa-solid fa-spinner fa-spin text-lg mb-1 text-[#1865f2]"></i>
                                    <p>Memuat jadwal sholat bulanan dari Kemenag...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                    class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0 cursor-pointer" 
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
        const userSavedLocation = <?= json_encode($lokasi_user); ?>;
        const currentUserName = <?= json_encode($nama_user); ?>;
        const currentUserId = <?= json_encode($user_id); ?>;
        const customWaMsgStorageKey = `dirasah_custom_wa_msg_${currentUserId}`;

        const defaultWaTemplate = "*PENGINGAT WAKTU SHOLAT - DIRASAH.ID*\n\nAssalamu'alaikum *{nama}*,\nWaktu sholat *{sholat}* untuk wilayah *{lokasi}* telah tiba pada pukul *{waktu}* WIB.\n\nMari segera mengambil air wudhu dan menunaikan ibadah sholat tepat pada waktunya. Semoga Allah SWT memberkahi setiap langkah kebaikan Anda.";

        let todayTimings = null;
        let notifiedPrayers = {};

        function getStoredCustomMessage() {
    var el = document.getElementById("wa_message_template") || document.getElementById("input-template");
    return el ? el.value : defaultWaTemplate;
}
function saveCustomMessage(val) {}

        function initCustomMessageUI() {
            const textarea = document.getElementById('input-custom-msg');
            if (textarea) {
                textarea.value = getStoredCustomMessage();
            }
        }

        function resetToDefaultMessage() {
            const textarea = document.getElementById('input-custom-msg');
            if (textarea) {
                textarea.value = defaultWaTemplate;
                saveCustomMessage(defaultWaTemplate);
                const logEl = document.getElementById('send-log-msg');
                logEl.innerHTML = `<span class="text-slate-600 font-normal">Teks berhasil dikembalikan ke format default.</span>`;
            }
        }

        function buildFormattedPrayerMessage(prayerName, prayerTime) {
            const template = getStoredCustomMessage();
            const cleanLoc = userSavedLocation.split(',')[0].replace(/[^a-zA-Z\s]/g, '').trim() || 'Bekasi';
            
            return template
                .replace(/{nama}/g, currentUserName)
                .replace(/{sholat}/g, prayerName)
                .replace(/{waktu}/g, prayerTime || '--:--')
                .replace(/{lokasi}/g, cleanLoc);
        }

        function printJadwalPDF() {
            window.print();
        }

        function toggleAlarmIcon(checkbox, iconId) {
            const iconEl = document.getElementById(iconId);
            if (!iconEl) return;
            
            if (checkbox.checked) {
                iconEl.className = "alarm-toggle alarm-btn-active";
                iconEl.innerHTML = `
                    <i class="fa-regular fa-bell text-base text-teal-600"></i>
                    <i class="fa-solid fa-check text-[9px] absolute text-teal-600"></i>
                `;
            } else {
                iconEl.className = "alarm-toggle alarm-btn-inactive";
                iconEl.innerHTML = `
                    <i class="fa-regular fa-bell text-base text-teal-600/70"></i>
                    <i class="fa-solid fa-xmark text-[9px] absolute text-rose-400"></i>
                `;
            }
        }

        async function saveUserPhoneNumber() {
            const rawWa = document.getElementById('input-wa').value.trim();
            const logEl = document.getElementById('send-log-msg');
            const btnSave = document.getElementById('btn-save-wa');

            if (!rawWa) {
                alert('Silakan masukkan nomor WhatsApp terlebih dahulu!');
                return;
            }

            btnSave.disabled = true;
            btnSave.textContent = 'Menyimpan...';

            try {
                const formData = new FormData();
                formData.append('action', 'save_wa');
                formData.append('phone', rawWa);

                const res = await fetch('sholat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status) {
                    logEl.innerHTML = `<span class="text-blue-600 font-normal">${data.message}</span>`;
                } else {
                    logEl.innerHTML = `<span class="text-rose-500 font-normal">${data.message}</span>`;
                }
            } catch (err) {
                logEl.innerHTML = `<span class="text-rose-500 font-normal">Gagal menyimpan nomor: periksa koneksi.</span>`;
            } finally {
                btnSave.disabled = false;
                btnSave.textContent = 'Simpan Nomor';
            }
        }

        function updateLiveClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').textContent = `${h}:${m}:${s}`;

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('live-date').textContent = now.toLocaleDateString('id-ID', options);

            checkAutoPrayerAlert(now);
            updateCountdown(now);
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        async function fetchFullPrayerTimes() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');

            document.getElementById('table-month-label').textContent = now.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

            let cityName = 'bekasi';
            if (userSavedLocation) {
                const clean = userSavedLocation.split(',')[0].replace(/[^a-zA-Z\s]/g, '').trim();
                if (clean) cityName = clean.toLowerCase();
            }

            try {
                const searchRes = await fetch(`https://api.myquran.com/v2/sholat/kota/cari/${encodeURIComponent(cityName)}`);
                const searchData = await searchRes.json();

                if (searchData.status && searchData.data && searchData.data.length > 0) {
                    const kotaId = searchData.data[0].id;
                    
                    const monthlyRes = await fetch(`https://api.myquran.com/v2/sholat/jadwal/${kotaId}/${year}/${month}`);
                    const monthlyData = await monthlyRes.json();

                    if (monthlyData.status && monthlyData.data && monthlyData.data.jadwal) {
                        const jadwalList = monthlyData.data.jadwal;
                        renderMonthlyTable(jadwalList, day);

                        const todayJadwal = jadwalList.find(j => parseInt(j.tanggal.split(',')[0]) === parseInt(day)) || jadwalList[parseInt(day) - 1];
                        if (todayJadwal) {
                            applyTodayTimings(todayJadwal);
                            return;
                        }
                    }
                }
            } catch (err) {
                console.warn("Kalkulasi Kemenag lokal digunakan:", err);
            }

            applyTodayTimings({
                imsak: '04:05',
                subuh: '04:15',
                terbit: '05:28',
                dhuha: '05:55',
                dzuhur: '11:41',
                ashar: '14:44',
                maghrib: '17:48',
                isya: '18:57'
            });
        }

        function applyTodayTimings(j) {
            todayTimings = {
                'Subuh': j.subuh || '04:15',
                'Dzuhur': j.dzuhur || '11:41',
                'Ashar': j.ashar || '14:44',
                'Maghrib': j.maghrib || '17:48',
                'Isya': j.isya || '18:57'
            };

            document.getElementById('time-imsak').textContent = j.imsak || '04:05';
            document.getElementById('time-subuh').textContent = j.subuh || '04:15';
            document.getElementById('time-terbit').textContent = j.terbit || '05:28';
            document.getElementById('time-dhuha').textContent = j.dhuha || '05:55';
            document.getElementById('time-dzuhur').textContent = j.dzuhur || '11:41';
            document.getElementById('time-ashar').textContent = j.ashar || '14:44';
            document.getElementById('time-maghrib').textContent = j.maghrib || '17:48';
            document.getElementById('time-isya').textContent = j.isya || '18:57';

            if (document.getElementById('alarm-time-imsak')) document.getElementById('alarm-time-imsak').textContent = j.imsak || '04:05';
            if (document.getElementById('alarm-time-subuh')) document.getElementById('alarm-time-subuh').textContent = j.subuh || '04:15';
            if (document.getElementById('alarm-time-terbit')) document.getElementById('alarm-time-terbit').textContent = j.terbit || '05:28';
            if (document.getElementById('alarm-time-dzuhur')) document.getElementById('alarm-time-dzuhur').textContent = j.dzuhur || '11:41';
            if (document.getElementById('alarm-time-ashar')) document.getElementById('alarm-time-ashar').textContent = j.ashar || '14:44';
            if (document.getElementById('alarm-time-maghrib')) document.getElementById('alarm-time-maghrib').textContent = j.maghrib || '17:48';
            if (document.getElementById('alarm-time-isya')) document.getElementById('alarm-time-isya').textContent = j.isya || '18:57';
        }

        function renderMonthlyTable(list, currentDay) {
            const tbody = document.getElementById('monthly-table-body');
            let html = '';

            list.forEach(j => {
                const dayNum = parseInt(j.tanggal);
                const isToday = dayNum === parseInt(currentDay);
                const rowClass = isToday ? 'bg-blue-50/70 text-[#1865f2] font-normal' : 'hover:bg-slate-50 font-normal';

                html += `
                    <tr class="${rowClass}">
                        <td class="py-2 px-3">${j.tanggal} ${isToday ? '<span class="text-[9px] bg-[#1865f2] text-white px-1.5 py-0.5 rounded ml-1 font-normal">Hari Ini</span>' : ''}</td>
                        <td class="py-2 px-3">${j.imsak}</td>
                        <td class="py-2 px-3">${j.subuh}</td>
                        <td class="py-2 px-3">${j.terbit}</td>
                        <td class="py-2 px-3">${j.dhuha}</td>
                        <td class="py-2 px-3">${j.dzuhur}</td>
                        <td class="py-2 px-3">${j.ashar}</td>
                        <td class="py-2 px-3">${j.maghrib}</td>
                        <td class="py-2 px-3">${j.isya}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function updateCountdown(now) {
            if (!todayTimings) return;

            const prayers = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
            let nextPrayer = null;
            let targetDate = null;

            for (const p of prayers) {
                const [pHour, pMinute] = todayTimings[p].split(':').map(Number);
                const pDate = new Date(now);
                pDate.setHours(pHour, pMinute, 0, 0);

                if (pDate > now) {
                    nextPrayer = p;
                    targetDate = pDate;
                    break;
                }
            }

            if (!nextPrayer) {
                nextPrayer = 'Subuh (Besok)';
                const [sHour, sMinute] = todayTimings['Subuh'].split(':').map(Number);
                targetDate = new Date(now);
                targetDate.setDate(targetDate.getDate() + 1);
                targetDate.setHours(sHour, sMinute, 0, 0);
            }

            document.getElementById('next-prayer-name').textContent = `${nextPrayer} (${targetDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;

            const diff = targetDate - now;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('countdown-timer').textContent = 
                `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function checkAutoPrayerAlert(now) {
            if (!todayTimings) return;

            const currentHM = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

            for (const [pName, pTime] of Object.entries(todayTimings)) {
                if (currentHM === pTime && !notifiedPrayers[pName]) {
                    notifiedPrayers[pName] = true;
                    
                    const isChecked = document.getElementById(`check-${pName.toLowerCase()}`)?.checked;
                    if (isChecked) {
                        triggerServerSendWA(pName, pTime);
                    }
                }
            }
        }

        async function triggerServerSendWA(prayerName, prayerTime) {
            const rawWa = document.getElementById('input-wa').value.trim();
            if (!rawWa) return;

            const finalMessage = buildFormattedPrayerMessage(prayerName, prayerTime);

            try {
                const response = await fetch('api_kirim_wa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: rawWa,
                        prayer: prayerName,
                        message: finalMessage
                    })
                });
                const res = await response.json();
                console.log('Bot pengingat WA terkirim:', res);
            } catch (err) {
                console.error('Gagal memicu pengiriman server:', err);
            }
        }

        async function sendTestNotification() {
            const rawWa = document.getElementById('input-wa').value.trim();
            const logEl = document.getElementById('send-log-msg');
            const btn = document.getElementById('btn-test-send');

            if (!rawWa) {
                alert('Silakan masukkan nomor WhatsApp Anda terlebih dahulu!');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            try {
                const now = new Date();
                const nowHM = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
                const testMsg = buildFormattedPrayerMessage('Tes Pengingat', nowHM);
                
                const response = await fetch('api_kirim_wa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: rawWa,
                        message: testMsg
                    })
                });

                const res = await response.json();
                if (res.status) {
                    logEl.innerHTML = `<span class="text-blue-600 font-normal">Pesan dengan format kustom berhasil dikirim ke WhatsApp Anda!</span>`;
                } else {
                    logEl.innerHTML = `<span class="text-rose-500 font-normal">${res.message}</span>`;
                }
            } catch (err) {
                logEl.innerHTML = `<span class="text-rose-500 font-normal">Gagal mengirim pesan: periksa koneksi.</span>`;
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-brands fa-whatsapp text-sm"></i> <span>Tes Kirim Pesan ke WA</span>`;
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

        // ================= LOGIKA WIDGET FLOATING USTADZ ARTIF =================
        const floatingBtn = document.getElementById('artif-floating-btn');
        let isDragging = false, hasMoved = false;
        let startX = 0, startY = 0, initialLeft = 0, initialTop = 0;

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
            try { floatingBtn.releasePointerCapture(e.pointerId); } catch (err) {}
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

        initCustomMessageUI();
        fetchFullPrayerTimes();
    </script>
</body>
</html>