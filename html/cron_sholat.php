<?php
// Wajib set zona waktu ke WIB
date_default_timezone_set("Asia/Jakarta");

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/helpers_wa.php";

$nowTime = date("H:i");
$todayDate = date("Y-m-d");
$year = date("Y");
$month = date("m");
$day = date("d");

// 1. AMBIL SEMUA USER YANG MEMILIKI NOMOR WA & LOKASI
$stmt = $pdo->query("SELECT id, nama_lengkap, no_wa, lokasi FROM users WHERE no_wa IS NOT NULL AND no_wa != '' AND lokasi IS NOT NULL AND lokasi != ''");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "[" . date("Y-m-d H:i:s") . "] Tidak ada user terdaftar dengan nomor WA dan lokasi.\n";
    exit;
}

// Cache ID kota agar tidak request berulang kali ke API Kemenag
$cityIdCache = [];

foreach ($users as $user) {
    $rawLokasi = $user['lokasi'];
    $namaKota = trim(explode(',', explode('(', $rawLokasi)[0])[0]);
    if (empty($namaKota)) continue;

    if (!isset($cityIdCache[$namaKota])) {
        $searchUrl = "https://api.myquran.com/v2/sholat/kota/cari/" . urlencode($namaKota);
        $resSearch = @file_get_contents($searchUrl);
        if ($resSearch) {
            $jsonSearch = json_decode($resSearch, true);
            if (!empty($jsonSearch['data'][0]['id'])) {
                $cityIdCache[$namaKota] = $jsonSearch['data'][0]['id'];
            }
        }
    }

    $idKota = $cityIdCache[$namaKota] ?? null;
    if (!$idKota) continue;

    $jadwalUrl = "https://api.myquran.com/v2/sholat/jadwal/{$idKota}/{$year}/{$month}/{$day}";
    $resJadwal = @file_get_contents($jadwalUrl);
    if (!$resJadwal) continue;

    $jsonJadwal = json_decode($resJadwal, true);
    if (empty($jsonJadwal['data']['jadwal'])) continue;

    $jadwal = $jsonJadwal['data']['jadwal'];

    $waktuList = [
        'Subuh'   => $jadwal['subuh'] ?? '',
        'Dzuhur'  => $jadwal['dzuhur'] ?? '',
        'Ashar'   => $jadwal['ashar'] ?? '',
        'Maghrib' => $jadwal['maghrib'] ?? '',
        'Isya'    => $jadwal['isya'] ?? ''
    ];

    $namaUser = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : 'Sahabat';

    foreach ($waktuList as $sholat => $jam) {
        $jamClean = trim($jam);
        if (empty($jamClean)) continue;

        // Hitung waktu pemicu
        $preTime30 = date("H:i", strtotime("-30 minutes", strtotime($jamClean)));
        $preTime5  = date("H:i", strtotime("-5 minutes", strtotime($jamClean)));

        // 1. PENGINGAT 30 MENIT SEBELUM
        if ($nowTime === $preTime30) {
            $pesan = "🔔 *PENGINGAT WAKTU SHOLAT (30 MENIT LAGI) - DIRASAH.ID*\n\n" .
                     "Assalamu'alaikum *{$namaUser}*,\n" .
                     "Waktu sholat *{$sholat}* untuk wilayah *{$namaKota}* dan sekitarnya akan tiba sekitar *30 menit lagi* (pukul *{$jamClean} WIB*).\n\n" .
                     "Mari selesaikan aktivitas penting Anda secara bertahap dan persiapkan diri menyambut waktu sholat.";

            sendWhatsAppMessage($user['no_wa'], $pesan);
            echo "[" . date("Y-m-d H:i:s") . "] Notifikasi H-30 {$sholat} terkirim ke {$user['no_wa']} ({$namaKota})\n";
        }

        // 2. PENGINGAT 5 MENIT SEBELUM
        if ($nowTime === $preTime5) {
            $pesan = "⏳ *PENGINGAT WAKTU SHOLAT (5 MENIT LAGI) - DIRASAH.ID*\n\n" .
                     "Assalamu'alaikum *{$namaUser}*,\n" .
                     "Waktu sholat *{$sholat}* untuk wilayah *{$namaKota}* dan sekitarnya akan tiba dalam *5 menit lagi* (pukul *{$jamClean} WIB*).\n\n" .
                     "Mari bersiap, berwudhu, dan merapikan aktivitas sejenak untuk menyambut panggilan sholat.";

            sendWhatsAppMessage($user['no_wa'], $pesan);
            echo "[" . date("Y-m-d H:i:s") . "] Notifikasi H-5 {$sholat} terkirim ke {$user['no_wa']} ({$namaKota})\n";
        }

        // 3. NOTIFIKASI TEPAT WAKTU SHOLAT
        if ($nowTime === $jamClean) {
            $pesan = "🕌 *WAKTU SHOLAT TELAH TIBA - DIRASAH.ID*\n\n" .
                     "Assalamu'alaikum *{$namaUser}*,\n" .
                     "Waktu sholat *{$sholat}* untuk wilayah *{$namaKota}* dan sekitarnya telah tiba pada pukul *{$jamClean} WIB*.\n\n" .
                     "Mari segera mengambil air wudhu dan menunaikan ibadah sholat tepat pada waktunya. Semoga Allah SWT menerima amal ibadah kita. Aamiin.";

            sendWhatsAppMessage($user['no_wa'], $pesan);
            echo "[" . date("Y-m-d H:i:s") . "] Notifikasi Tepat Waktu {$sholat} terkirim ke {$user['no_wa']} ({$namaKota})\n";
        }
    }
}

// 2. CEK PENGINGAT AGENDA KALENDER PADA WAKTU SUBUH (04:30 WIB)
if ($nowTime === "04:30") {
    $qKal = $pdo->prepare("SELECT id, no_wa, pesan FROM pengingat_kalender WHERE tanggal_kirim = ? AND status = 'pending'");
    $qKal->execute([$todayDate]);
    $agendas = $qKal->fetchAll(PDO::FETCH_ASSOC);

    foreach ($agendas as $ag) {
        sendWhatsAppMessage($ag['no_wa'], $ag['pesan']);
        $upd = $pdo->prepare("UPDATE pengingat_kalender SET status = 'sent' WHERE id = ?");
        $upd->execute([$ag['id']]);
        echo "[" . date("Y-m-d H:i:s") . "] Pengingat kalender berhasil dikirim ke {$ag['no_wa']}\n";
    }
}

echo "[" . date("Y-m-d H:i:s") . "] Pengecekan selesai untuk jam {$nowTime} WIB.\n";
