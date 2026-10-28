<?php
$q = trim($_GET["search"] ?? $_GET["q"] ?? "");

if (empty($q)) {
    header("Location: dashboard.php");
    exit;
}

$surahList = [
    "fatihah", "baqarah", "imran", "nisa", "maidah", "anam", "araf", "anfal", "taubah", 
    "yunus", "hud", "yusuf", "rad", "ibrahim", "hijr", "nahl", "isra", "kahf", "maryam", 
    "taha", "anbiya", "hajj", "muminun", "nur", "furqan", "syuara", "naml", "qasas", 
    "ankabut", "rum", "luqman", "sajdah", "ahzab", "saba", "fatir", "yasin", "saffat", 
    "sad", "zumar", "gafir", "fussilat", "syura", "zukhruf", "dukhan", "jasiyah", "ahqaf", 
    "muhammad", "fath", "hujurat", "qaf", "zariyat", "tur", "najm", "qamar", "rahman", 
    "waqiah", "hadid", "mujadilah", "hasyr", "mumtahanah", "saff", "jumuah", "munafiqun", 
    "tagabun", "talaq", "tahrim", "mulk", "qalam", "haqqah", "maarij", "nuh", "jinn", 
    "muzzammil", "muddassir", "qiyamah", "insan", "mursalat", "naba", "naziat", "abasa", 
    "takwir", "infitar", "mutaffifin", "insyiqaq", "buruj", "tariq", "ala", "gasyiyah", 
    "fajr", "balad", "syams", "lail", "duha", "syarh", "tin", "alaq", "qadr", "bayyinah", 
    "zalzalah", "adiyat", "qariah", "takasur", "asr", "humazah", "fil", "quraisy", "maun", 
    "kausar", "kafirun", "nasr", "lahab", "ikhlas", "falaq", "nas"
];

$clean = strtolower(preg_replace("/[^a-z]/", "", $q));

$isQuran = false;
foreach ($surahList as $s) {
    if (strpos($clean, $s) !== false) {
        $isQuran = true;
        break;
    }
}

if ($isQuran || strpos($clean, "quran") !== false || strpos($clean, "surat") !== false || strpos($clean, "juz") !== false) {
    header("Location: quran.php?search=" . urlencode($q));
} else {
    header("Location: hadits.php?search=" . urlencode($q));
}
exit;
