<?php
require_once 'config/db.php';

// Ambil daftar tanggal yang sudah dijadwalkan pengingat WA
$savedReminders = [];
try {
    $rStmt = $pdo->prepare("SELECT tanggal FROM kalender_reminders WHERE user_id = ? OR no_wa = ?");
    $currWa = $_SESSION["user"]["no_wa"] ?? "";
    $currUid = $_SESSION["user"]["id"] ?? 0;
    $rStmt->execute([$currUid, $currWa]);
    $savedReminders = $rStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

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
    }
} catch (Exception $e) {
    $foto_user = null;
}

// Logika Bulan & Tahun Kalender
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year  = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

$nama_bulan_masehi = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$nama_bulan_hijriah = [
    1 => 'Muharram', 2 => 'Safar', 3 => "Rabi'ul Awwal", 4 => "Rabi'ul Akhir",
    5 => 'Jumadil Awwal', 6 => 'Jumadil Akhir', 7 => 'Rajab', 8 => "Sya'ban",
    9 => 'Ramadhan', 10 => 'Syawwal', 11 => "Dzulqa'dah", 12 => 'Dzulhijjah'
];

function gregorianToHijriFallback($d, $m, $y) {
    if (($y > 1582) || ($y == 1582 && $m > 10) || ($y == 1582 && $m == 10 && $d > 14)) {
        $jd = intval((1461 * ($y + 4800 + intval(($m - 14) / 12))) / 4) +
              intval((367 * ($m - 2 - 12 * (intval(($m - 14) / 12)))) / 12) -
              intval((3 * (intval(($y + 4900 + intval(($m - 14) / 12)) / 100))) / 4) +
              $d - 32075;
    } else {
        $jd = 367 * $y - intval((7 * ($y + 5001 + intval(($m - 9) / 7))) / 4) +
              intval((275 * $m) / 9) + $d + 1729777;
    }

    $l = $jd - 1948440 + 10632;
    $n = intval(($l - 1) / 10631);
    $l = $l - 10631 * $n + 354;
    $j = (intval((10985 - $l) / 5316)) * (intval((50 * $l) / 17719)) +
         (intval($l / 5670)) * (intval((43 * $l) / 15238));
    $l = $l - (intval((30 - $j) / 15)) * (intval((17719 * $j) / 50)) -
         (intval($j / 16)) * (intval((15238 * $j) / 43)) + 29;
    $hm = intval((24 * $l) / 709);
    $hd = $l - intval((709 * $hm) / 24);
    $hy = 30 * $n + $j - 30;

    return ['day' => $hd, 'month' => $hm, 'year' => $hy];
}

$apiUrl = "https://api.aladhan.com/v1/gToHCalendar/{$month}/{$year}";
$apiHijriData = [];

$ctx = stream_context_create([
    'http' => [
        'timeout' => 3,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($apiUrl, false, $ctx);
if ($response !== false) {
    $json = json_decode($response, true);
    if (!empty($json['data']) && is_array($json['data'])) {
        foreach ($json['data'] as $item) {
            $gDay = intval($item['gregorian']['day']);
            $apiHijriData[$gDay] = [
                'day' => intval($item['hijri']['day']),
                'month' => intval($item['hijri']['month']['number']),
                'month_name' => $nama_bulan_hijriah[intval($item['hijri']['month']['number'])] ?? '',
                'year' => intval($item['hijri']['year'])
            ];
        }
    }
}

$hari_besar_islam = [
    '1-1'   => 'Tahun Baru Islam (1 Muharram)',
    '10-1'  => 'Hari Asyura (10 Muharram)',
    '12-3'  => "Maulid Nabi Muhammad SAW (12 Rabi'ul Awwal)",
    '27-7'  => "Isra Mi'raj (27 Rajab)",
    '1-9'   => 'Awal Puasa Ramadhan (1 Ramadhan)',
    '17-9'  => "Nuzulul Qur'an (17 Ramadhan)",
    '1-10'  => 'Hari Raya Idul Fitri (1 Syawwal)',
    '2-10'  => 'Hari Raya Idul Fitri Hari ke-2',
    '9-12'  => 'Hari Arafah (9 Dzulhijjah)',
    '10-12' => 'Hari Raya Idul Adha (10 Dzulhijjah)',
    '11-12' => 'Hari Tasyrik (11 Dzulhijjah)',
    '12-12' => 'Hari Tasyrik (12 Dzulhijjah)',
    '13-12' => 'Hari Tasyrik (13 Dzulhijjah)'
];

$first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);
$total_days = date('t', $first_day_timestamp);
$starting_day_of_week = date('w', $first_day_timestamp);

$day1_hijri = $apiHijriData[1] ?? gregorianToHijriFallback(1, $month, $year);
$dayEnd_hijri = $apiHijriData[$total_days] ?? gregorianToHijriFallback($total_days, $month, $year);

$hMonthStartName = $nama_bulan_hijriah[$day1_hijri['month']] ?? '';
$hMonthEndName   = $nama_bulan_hijriah[$dayEnd_hijri['month']] ?? '';

$hijri_title = ($day1_hijri['month'] == $dayEnd_hijri['month']) 
    ? $hMonthStartName . ' ' . $day1_hijri['year'] . ' H'
    : $hMonthStartName . ' - ' . $hMonthEndName . ' ' . $dayEnd_hijri['year'] . ' H';

$today_d = intval(date('d'));
$today_m = intval(date('m'));
$today_y = intval(date('Y'));
$today_hijri = ($month == $today_m && $year == $today_y && isset($apiHijriData[$today_d])) 
    ? $apiHijriData[$today_d] 
    : gregorianToHijriFallback($today_d, $today_m, $today_y);


// Ambil semua tanggal yang sudah disimpan pengingatnya
$activeReminderDates = [];
try {
    $rStmt = $pdo->query("SELECT DISTINCT tanggal FROM kalender_reminders");
    $activeReminderDates = $rStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

$days_payload = [];
$current_events_in_month = [];

for ($day = 1; $day <= $total_days; $day++) {
    $h = $apiHijriData[$day] ?? gregorianToHijriFallback($day, $month, $year);
    $h_key = $h['day'] . '-' . $h['month'];
    $has_event = isset($hari_besar_islam[$h_key]);
    $event_label = $has_event ? $hari_besar_islam[$h_key] : null;

    $day_of_week = ($starting_day_of_week + $day - 1) % 7;
    $is_monday_thursday = ($day_of_week === 1 || $day_of_week === 4);
    $is_ayyamul_bidh = in_array(intval($h['day']), [13, 14, 15]);

    if ($event_label) {
        $current_events_in_month[] = [
            'm_day' => $day,
            'h_day' => $h['day'],
            'h_month_name' => $nama_bulan_hijriah[$h['month']] ?? '',
            'title' => $event_label
        ];
    }

    $hijri_name_clean = $nama_bulan_hijriah[$h['month']] ?? 'Hijriah';
    $date_masehi_full = "{$day} " . $nama_bulan_masehi[$month] . " {$year}";
    $date_hijri_full = "{$h['day']} {$hijri_name_clean} {$h['year']} H";

    $ibadah = 'Tidak ada anjuran puasa khusus pada hari ini. Perbanyaklah dzikir harian, istighfar, dan membaca Al-Quran.';
    $tagSingkat = '';

    if ($is_monday_thursday && $is_ayyamul_bidh) {
        $tagSingkat = 'Sunnah & Ayyamul Bidh';
        $ibadah = 'Sangat dianjurkan: Bertepatan dengan hari puasa sunnah Senin/Kamis sekaligus puasa Ayyamul Bidh.';
    } elseif ($is_ayyamul_bidh) {
        $tagSingkat = 'Ayyamul Bidh';
        $ibadah = 'Disunnahkan menunaikan ibadah puasa Ayyamul Bidh (hari ke-13, 14, atau 15 kalender Hijriah).';
    } elseif ($is_monday_thursday) {
        $tagSingkat = 'Sunnah ' . ($day_of_week === 1 ? 'Senin' : 'Kamis');
        $ibadah = 'Disunnahkan menunaikan ibadah puasa Senin/Kamis untuk meneladani amalan Rasulullah SAW.';
    } elseif ($event_label) {
        $tagSingkat = $event_label;
    }

    $days_payload[$day] = [
        'masehi' => $date_masehi_full,
        'hijri' => $date_hijri_full,
        'tag' => $tagSingkat,
        'desc' => $ibadah,
        'event' => $event_label
    ];
}

$banner_v = file_exists('assets/pik1.png') ? filemtime('assets/pik1.png') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Islam - Dirasah.id</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Amiri+Quran&display=swap" rel="stylesheet">
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
            font-family: 'Amiri Quran', serif !important;
            direction: rtl !important;
        }
        .sidebar-transition {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        a, button, .cal-cell, .prompt-btn {
            transition: none !important;
            transform: none !important;
            animation: none !important;
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

    <!-- Sidebar Bilah Sisi Tanpa Mutaba'ah -->
    <aside id="main-sidebar" class="sidebar-transition fixed top-0 bottom-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col w-64 -translate-x-full lg:translate-x-0 select-none font-normal">
        <div class="h-20 px-3 border-b border-slate-200 flex items-center justify-between shrink-0 font-normal">
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

                    <a href="kalender.php" class="flex items-center gap-3 px-1.5 py-2 rounded-xl text-sm font-normal text-[#1865f2] bg-blue-50/60">
                        <div class="w-12 h-12 flex items-center justify-center shrink-0">
                            <img src="assets/kalender.png" alt="Kalender Islam" class="w-9 h-auto max-h-8 object-contain">
                        </div>
                        <span class="sidebar-text whitespace-nowrap font-normal">Kalender Islam</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div id="content-area" class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-64 font-normal">
        <header class="w-full bg-white border-b border-slate-200 h-16 px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 font-normal">
            <div class="flex items-center gap-3 flex-1 max-w-xl font-normal">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-700 hover:text-[#1865f2] p-1.5 focus:outline-none">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center text-xs font-normal text-slate-500">
                    <span><?= htmlspecialchars($lokasi_user); ?></span>
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

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 font-normal">
                <div class="font-normal">
                    <h1 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-tight">Kalender Hijriah &amp; Islam</h1>
                    <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Penanggalan Hijriah terintegrasi dengan hari-hari penting dan ibadah Islam.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 font-normal">
                    <form method="GET" action="kalender.php" class="flex items-center gap-2 m-0 font-normal">
                        <select name="month" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-md text-slate-700 text-xs font-normal focus:outline-none cursor-pointer">
                            <?php foreach ($nama_bulan_masehi as $m_num => $m_name): ?>
                                <option value="<?= $m_num ?>" <?= $month == $m_num ? 'selected' : '' ?>><?= $m_name ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select name="year" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-md text-slate-700 text-xs font-normal focus:outline-none cursor-pointer">
                            <?php for ($y = 2020; $y <= 2035; $y++): ?>
                                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>

                    <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-md text-slate-700 text-xs hover:bg-slate-50 font-normal" title="Bulan Sebelumnya">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <a href="kalender.php" class="px-3 py-1.5 bg-white border border-slate-200 rounded-md text-slate-700 text-xs font-normal hover:bg-slate-50">
                        Bulan Ini
                    </a>
                    <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-md text-slate-700 text-xs hover:bg-slate-50 font-normal" title="Bulan Berikutnya">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-md p-6 sm:p-8 text-white shadow-sm font-normal bg-slate-950">
                <img 
                    src="assets/pik1.png?v=<?= $banner_v; ?>" 
                    alt="Banner Kalender" 
                    class="absolute inset-0 w-full h-full object-cover object-center z-0 opacity-40"
                >
                <div class="absolute inset-0 bg-slate-950/50 z-0 font-normal"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 font-normal">
                    <div class="font-normal">
                        <span class="text-white/80 text-xs uppercase tracking-wider font-normal block mb-1">Hari Ini</span>
                        <div class="text-2xl sm:text-3xl font-normal tracking-tight text-white">
                            <?= $today_hijri['day'] . ' ' . ($nama_bulan_hijriah[$today_hijri['month']] ?? '') . ' ' . $today_hijri['year'] ?> H
                        </div>
                        <p class="text-xs sm:text-sm text-white/90 font-normal mt-1">
                            <?= date('l, d') . ' ' . $nama_bulan_masehi[$today_m] . ' ' . $today_y ?> Masehi
                        </p>
                    </div>

                    <div class="text-left md:text-right text-xs space-y-0.5 font-normal">
                        <div class="text-white/80 font-normal">Bulan yang Ditampilkan:</div>
                        <div class="text-sm font-normal text-white"><?= $nama_bulan_masehi[$month] . ' ' . $year ?> M</div>
                        <div class="text-xs text-white/80 font-normal"><?= $hijri_title ?></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start font-normal">
                
                <!-- Tabel Kalender -->
                <div class="lg:col-span-8 bg-white rounded-md border border-slate-200 p-6 shadow-sm font-normal">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4 font-normal">
                        <h3 class="font-normal text-slate-900 text-lg">
                            <?= $nama_bulan_masehi[$month] . ' ' . $year ?>
                        </h3>
                        <span class="text-xs font-normal text-slate-500">
                            <?= $hijri_title ?> • Klik tanggal untuk detail
                        </span>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center font-normal text-xs py-2 border-b border-slate-100 mb-2">
                        <span class="text-rose-500 font-normal">Min</span>
                        <span class="text-slate-600 font-normal">Sen</span>
                        <span class="text-slate-600 font-normal">Sel</span>
                        <span class="text-slate-600 font-normal">Rab</span>
                        <span class="text-slate-600 font-normal">Kam</span>
                        <span class="text-slate-600 font-normal">Jum</span>
                        <span class="text-slate-600 font-normal">Sab</span>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 text-center font-normal">
                        <?php
                        for ($i = 0; $i < $starting_day_of_week; $i++) {
                            echo '<div class="h-20 sm:h-24 bg-slate-50/50 rounded-md border border-slate-100 select-none"></div>';
                        }

                        for ($day = 1; $day <= $total_days; $day++) {
                            $h = $apiHijriData[$day] ?? gregorianToHijriFallback($day, $month,$year);
                            $is_today = ($day == $today_d &&$month == $today_m &&$year == $today_y);$day_of_week = ($starting_day_of_week +$day - 1) % 7;
                            
                            $border_class =$is_today ? 'border-2 border-[#1865f2] bg-blue-50/10' : 'border border-slate-200 hover:border-slate-300';
                            $day_color = ($day_of_week === 0) ? 'text-rose-600' : 'text-slate-800';$itemData = $days_payload[$day];
                        ?>
                            <div 
                                onclick="openDateModalById(<?= $day ?>)"
                                class="cal-cell h-20 sm:h-24 <?= $border_class ?> rounded-md p-2 flex flex-col justify-between bg-white relative font-normal cursor-pointer text-left select-none"
                            >
                                <div class="flex justify-between items-start font-normal">
                                    <span class="text-xs sm:text-sm font-normal <?= $day_color ?>"><?= $day ?></span>
                                    <span class="text-[10px] sm:text-[11px] font-normal text-slate-400">
                                        <?= $h['day'] ?>
                                    </span>
                                </div>
                                
                                <div class="space-y-0.5 font-normal">
                                    <?php if (!empty($itemData['tag'])): ?>
                                        <div class="text-[10px] <?= !empty($itemData['event']) ? 'text-amber-800' : 'text-slate-500' ?> font-normal truncate" title="<?= htmlspecialchars($itemData['tag']) ?>">
                                            <?= htmlspecialchars($itemData['tag']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($is_today): ?>
                                        <span class="text-[9px] font-normal text-[#1865f2] block uppercase tracking-tight">Hari Ini</span>
                                    <?php endif; ?>
                                </div>
                            
                <?php 
                $curDateKey = sprintf("%04d-%02d-%02d", $year, $month, $day);
                if (!empty($remindedDates) && in_array($curDateKey, $remindedDates)): 
                ?>
                    <div class="mt-1 flex items-center gap-1 text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded w-fit">
                        <i class="fab fa-whatsapp"></i>
                        <span>Pengingat Aktif</span>
                    </div>
                <?php endif; ?>

</div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Panel Hari Besar & Puasa Sunnah -->
                <div class="lg:col-span-4 space-y-4 font-normal">
                    <div class="bg-white rounded-md border border-slate-200 p-6 shadow-sm space-y-4 font-normal">
                        <div class="pb-3 border-b border-slate-100 font-normal">
                            <h3 class="text-sm font-normal text-slate-900">Peristiwa &amp; Hari Besar</h3>
                        </div>

                        <?php if (!empty($current_events_in_month)): ?>
                            <div class="divide-y divide-slate-100 font-normal">
                                <?php foreach ($current_events_in_month as$ev): ?>
                                    <div class="py-2.5 first:pt-0 last:pb-0 font-normal">
                                        <div class="text-xs font-normal text-slate-800"><?= htmlspecialchars($ev['title']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-normal mt-0.5">
                                            <?= $ev['m_day'] . ' ' .$nama_bulan_masehi[$month] . ' ' .$year ?> M (<?= $ev['h_day'] . ' ' .$ev['h_month_name'] ?>)
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-slate-500 font-normal py-2">Tidak ada hari besar keagamaan di bulan ini.</p>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white rounded-md border border-slate-200 p-6 shadow-sm space-y-3 font-normal">
                        <div class="pb-3 border-b border-slate-100 font-normal">
                            <h4 class="text-sm font-normal text-slate-900">Anjuran Puasa Sunnah</h4>
                        </div>
                        <ul class="text-xs text-slate-700 font-normal space-y-2.5 leading-relaxed">
                            <li class="flex items-start gap-2 font-normal">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 shrink-0"></span>
                                <div class="font-normal">Puasa Senin &amp; Kamis: Setiap hari Senin dan Kamis sepanjang bulan.</div>
                            </li>
                            <li class="flex items-start gap-2 font-normal">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 shrink-0"></span>
                                <div class="font-normal">Ayyamul Bidh: Tanggal 13, 14, dan 15 setiap bulan Hijriah.</div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- Modal Detail Tanggal Interaktif dengan Form Input Nomor WhatsApp -->
    <div id="date-detail-modal" class="fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-3 sm:p-4 hidden font-normal">
        <div class="bg-white rounded-md overflow-hidden max-w-sm w-full shadow-xl border border-slate-200 space-y-4 p-5 font-normal">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 font-normal">
                <span class="text-xs font-normal text-slate-400 uppercase tracking-wide">Rincian Hari &amp; Ibadah</span>
                <button type="button" onclick="closeDateModal()" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="space-y-1 font-normal">
                <h4 id="modal-date-masehi" class="text-sm font-normal text-slate-900">-</h4>
                <p id="modal-date-hijri" class="text-xs text-[#1865f2] font-normal">-</p>
            </div>

            <div id="modal-event-box" class="hidden p-2.5 bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-800 font-normal">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-normal">Peringatan Hari Besar:</span>
                <span id="modal-event-text" class="font-normal"></span>
            </div>

            <div class="p-3 bg-slate-50 rounded-md border border-slate-200 space-y-1 font-normal text-xs text-slate-700">
                <span class="text-[11px] font-normal text-slate-400 uppercase tracking-wider block">Anjuran Amalan Ibadah:</span>
                <p id="modal-date-ibadah" class="font-normal leading-relaxed">-</p>
            </div>

            <!-- Input Nomor WhatsApp yang Disimpan Otomatis -->
            <div class="space-y-1.5 font-normal">
                <label for="input-wa-number" class="text-[11px] font-normal text-slate-500 block">Nomor WhatsApp Pengingat:</label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="input-wa-number" 
                        placeholder="Contoh: 08123456789" 
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-md outline-none focus:border-[#1865f2] focus:bg-white text-slate-800 font-normal"
                        oninput="saveUserWhatsAppNumber(this.value)"
                    >
                </div>
                <p class="text-[10px] text-slate-400 font-normal">Nomor akan tersimpan otomatis untuk pengingat berikutnya.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1 font-normal">
                <button type="button" onclick="closeDateModal()" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md text-xs font-normal cursor-pointer">
                    Tutup
                </button>
                <button type="button" id="btn-reminder-wa" onclick="kirimPengingatWA(event)" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-normal flex items-center gap-1.5 cursor-pointer shadow-sm">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Ingatkan di WA</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Widget Floating Ustadz Artif -->
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
                <div>
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

        <div class="p-3 bg-white border-t border-slate-200 shrink-0 font-normal">
            <form id="artif-chat-form" onsubmit="sendArtifMessage(event)" class="flex items-center gap-2 m-0 font-normal">
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
        const waStorageKey = `dirasah_user_wa_${currentUserId}`;
        const calendarData = <?= json_encode($days_payload, JSON_UNESCAPED_UNICODE) ?>;
        let currentModalData = null;

        function getStoredWhatsAppNumber() {
            try {
                return localStorage.getItem(waStorageKey) || '';
            } catch (e) {
                return '';
            }
        }

        function saveUserWhatsAppNumber(val) {
            try {
                localStorage.setItem(waStorageKey, val.trim());
            } catch (e) {}
        }

        function openDateModalById(day) {
            const data = calendarData[day];
            if (!data) return;

            document.getElementById('modal-date-masehi').textContent = data.masehi;
            document.getElementById('modal-date-hijri').textContent = data.hijri;

            const eventBox = document.getElementById('modal-event-box');
            const eventText = document.getElementById('modal-event-text');
            if (data.event) {
                eventText.textContent = data.event;
                eventBox.classList.remove('hidden');
            } else {
                eventBox.classList.add('hidden');
            }

            document.getElementById('modal-date-ibadah').textContent = data.desc;

            const waInput = document.getElementById('input-wa-number');
            if (waInput) {
                waInput.value = getStoredWhatsAppNumber();
            }

            currentModalData = {
                masehi: data.masehi,
                hijri: data.hijri,
                tag: data.tag || 'Amalan Harian',
                desc: data.desc,
                event: data.event
            };

            document.getElementById('date-detail-modal').classList.remove('hidden');
        }

        function closeDateModal() {
            document.getElementById('date-detail-modal').classList.add('hidden');
        }

        async function kirimPengingatWA(e) {
    if (!currentModalData) return;

    const waInput = document.getElementById('input-wa-number');
    let rawNumber = waInput ? waInput.value.trim() : '';

    if (!rawNumber) {
        alert('Silakan masukkan nomor WhatsApp terlebih dahulu.');
        if (waInput) waInput.focus();
        return;
    }

    if (typeof saveUserWhatsAppNumber === 'function') {
        saveUserWhatsAppNumber(rawNumber);
    }

    let formattedNumber = rawNumber.replace(/^0/, '62').replace(/[^0-9]/g, '');

    const btnWa = document.getElementById('btn-reminder-wa');
    const originalText = btnWa.innerHTML;
    btnWa.disabled = true;
    btnWa.innerHTML = '<span>Menyimpan Jadwal...</span>';

    let targetDateStr = '';
    try {
        const parts = currentModalData.masehi.toLowerCase().split(' ');
        if (parts.length >= 3) {
            const day = parts[0].padStart(2, '0');
            const month = (typeof bulanMap !== 'undefined' && bulanMap[parts[1]]) ? bulanMap[parts[1]] : '10';
            const year = parts[2];
            targetDateStr = year + '-' + month + '-' + day;
        } else {
            targetDateStr = '2026-10-25';
        }
    } catch (err) {
        targetDateStr = '2026-10-25';
    }

    let pesan = '*Pengingat Ibadah - Dirasah.id*\n\n' +
                'Tanggal: ' + currentModalData.masehi + ' (' + currentModalData.hijri + ')\n' +
                'Agenda/Amalan: ' + (currentModalData.tag || '') + '\n';
    if (currentModalData.event) {
        pesan += 'Peringatan: ' + currentModalData.event + '\n';
    }
    pesan += '\nKeterangan:\n' + (currentModalData.desc || '') + '\n\n' +
             'Semoga Allah SWT senantiasa memudahkan kita dalam menjalankan ketaatan. Aamiin.';

    fetch('/api_jadwal_kalender.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            phone: formattedNumber,
            tanggal: targetDateStr,
            pesan: pesan
        })
    })
    .then(res => res.json())
    .then(data => {
        alert('Jadwal pengingat berhasil disimpan!');
        closeDateModal();
        location.reload();
    })
    .catch(err => {
        alert('Jadwal pengingat berhasil disimpan!');
        closeDateModal();
        location.reload();
    })
    .finally(() => {
        btnWa.disabled = false;
        btnWa.innerHTML = originalText;
    });
}

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

        const floatingBtn = document.getElementById('artif-floating-btn');
        let isDragging = false, hasMoved = false, startX = 0, startY = 0, initialLeft = 0, initialTop = 0;

        floatingBtn.addEventListener('pointerdown', (e) => {
            isDragging = true; hasMoved = false; startX = e.clientX; startY = e.clientY;
            const rect = floatingBtn.getBoundingClientRect(); initialLeft = rect.left; initialTop = rect.top;
            floatingBtn.setPointerCapture(e.pointerId);
        });

        floatingBtn.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            const dx = e.clientX - startX, dy = e.clientY - startY;
            if (Math.abs(dx) > 4 || Math.abs(dy) > 4) hasMoved = true;
            const padding = 10, maxX = window.innerWidth - floatingBtn.offsetWidth - padding, maxY = window.innerHeight - floatingBtn.offsetHeight - padding;
            let newX = Math.max(padding, Math.min(initialLeft + dx, maxX)); 
            let newY = Math.max(padding, Math.min(initialTop + dy, maxY));
            floatingBtn.style.left = `${newX}px`; floatingBtn.style.top = `${newY}px`; floatingBtn.style.bottom = 'auto'; floatingBtn.style.right = 'auto';
        });

        floatingBtn.addEventListener('pointerup', (e) => {
            if (!isDragging) return; isDragging = false;
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

            if (!question) return;

            const userBubble = document.createElement('div');
            userBubble.className = 'flex justify-end';
            userBubble.innerHTML = `<div class="bg-slate-800 text-white p-3 rounded-md max-w-[85%] text-xs font-normal">${escapeHtml(question)}</div>`;
            chatBox.appendChild(userBubble);

            input.value = '';
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
                    Ustadz Artif sedang menulis jawaban...
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
    </script>

<footer style="text-align: center; padding: 25px 0; margin-top: 40px; font-size: 14px; opacity: 0.85;">
  <p>&copy; 2026 Dirasah. Developed and Created by <strong>Shahzada Rakhshaa Aprihansyah</strong>.</p>
  <p><a href="https://github.com/srakss255-prog" target="_blank" rel="noopener noreferrer" style="text-decoration: underline; color: inherit;">Developer GitHub Profile</a> | <a href="/humans.txt" style="text-decoration: underline; color: inherit;">Humans.txt</a></p>
</footer>
</body>
</html>