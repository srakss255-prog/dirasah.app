<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/helpers_wa.php";

date_default_timezone_set("Asia/Jakarta");
$today = date("Y-m-d");

$stmt = $pdo->prepare("SELECT * FROM kalender_reminders WHERE tanggal = ? AND (is_sent = 0 OR is_sent IS NULL)");
$stmt->execute([$today]);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($reminders)) {
    echo "[$today 00:00] Tidak ada pengingat kalender untuk hari ini.\n";
    exit;
}

foreach ($reminders as $r) {
    $target = $r["no_wa"];
    $pesan  = $r["keterangan"] ?? $r["judul"];

    sendWhatsAppMessage($target, $pesan);

    $upd = $pdo->prepare("UPDATE kalender_reminders SET is_sent = 1 WHERE id = ?");
    $upd->execute([$r["id"]]);

    echo "[$today 00:00] Pengingat kalender ID {$r[id]} berhasil dikirim ke {$target}\n";
}
