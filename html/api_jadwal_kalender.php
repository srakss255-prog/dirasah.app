<?php
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
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/config/db.php";

$userId = $_SESSION["user_id"] ?? 15;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        $stmt = $pdo->prepare("SELECT tanggal FROM kalender_reminders WHERE user_id = ?");
        $stmt->execute([$userId]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(["status" => true, "data" => $dates]);
    } catch (PDOException $e) {
        echo json_encode(["status" => false, "data" => []]);
    }
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$phone = trim($input["phone"] ?? "");
$tanggal = trim($input["tanggal"] ?? "");
$pesan = trim($input["pesan"] ?? "");

if (!$phone || !$tanggal) {
    echo json_encode(["status" => false, "message" => "Nomor WA dan tanggal wajib diisi."]);
    exit;
}

try {
    $sql = "INSERT INTO kalender_reminders (user_id, tanggal, judul, keterangan, no_wa)
            VALUES (:uid, :tgn, :jdl, :ket, :now)
            ON DUPLICATE KEY UPDATE no_wa = :now2, keterangan = :ket2";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":uid"  => $userId,
        ":tgn"  => $tanggal,
        ":jdl"  => "Pengingat Ibadah",
        ":ket"  => $pesan,
        ":now"  => $phone,
        ":now2" => $phone,
        ":ket2" => $pesan
    ]);

    echo json_encode(["status" => true, "message" => "Berhasil disimpan."]);
} catch (PDOException $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
