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

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesi login tidak valid.']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$rawText = trim($input['text'] ?? '');
$imageBase64 =$input['image_base64'] ?? null;

if (empty($rawText) && empty($imageBase64)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Silakan masukkan teks dalil atau unggah gambar.']);
    exit;
}

// API Key KIE AI
$apiKey = "3729e9984477fa5834f9937df4979432";
$url = "https://api.kie.ai/gpt-5-2/v1/chat/completions";

$promptInstruksi = "Anda adalah pakar ilmu Hadits (Muhaddits) dan Takhrij Dalil Islam. Tugas Anda adalah memeriksa potongan teks atau membaca gambar potongan teks dalil/hadits, lalu meneliti status keasliannya apakah Shahih, Hasan, Dha'if, atau Maudhu' (Palsu / Tidak ada asalnya).
Balas HANYA dalam format JSON murni TANPA tag html/escape TANPA blok markdown ```json dengan format persis:
{
  \"status_dalil\": \"Shahih / Hasan / Dha'if / Maudhu' (Palsu) / Ayat Al-Qur'an / Tidak Ditemukan\",
  \"judul_dalil\": \"Nama hadits atau tema\",
  \"rujukan\": \"Kitab & nomor hadits atau QS Nama Surah: Ayat\",
  \"matan_arab\": \"Lafadz arab jika ada\",
  \"terjemah\": \"Arti dalam bahasa Indonesia\",
  \"analisis_sanad\": \"Penjelasan mengenai status perawi, sanad, atau kritik kepalsuan.\"
}";

$userContent = [];
$userContent[] = ["type" => "text", "text" => $promptInstruksi];

if (!empty($rawText)) {
    $userContent[] = ["type" => "text", "text" => "Periksa dalil/hadits berikut:\n" . $rawText];
}

if (!empty($imageBase64)) {
    $userContent[] = [
        "type" => "image_url",
        "image_url" => [
            "url" => $imageBase64
        ]
    ];
}

$payload = [
    "model" => "gpt-5-2",
    "messages" => [
        [
            "role" => "user",
            "content" => $userContent
        ]
    ],
    "temperature" => 0.1
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . trim($apiKey)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghubungi server AI: ' . $err]);
    exit;
}

$resData = json_decode($response, true);
$reply = $resData['choices'][0]['message']['content'] ?? null;

if (!$reply) {
    $errMsg = $resData['error']['message'] ?? (is_array($resData) ? json_encode($resData) : $response);
    echo json_encode(['status' => 'error', 'message' => 'Respon KIE: ' . $errMsg]);
    exit;
}

// Ekstrak isi JSON dari dalam tag <escape>, markdown block, atau teks bebas
$cleanReply = trim($reply);

// 1. Bersihkan wrapper <escape> ... </escape>
$cleanReply = preg_replace('/^<\s*escape\s*>/i', '', $cleanReply);
$cleanReply = preg_replace('/<\s*\/\s*escape\s*>$/i', '', $cleanReply);

// 2. Bersihkan wrapper markdown ```json ... ```
$cleanReply = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($cleanReply));

// 3. Fallback: cari blok kurung kurawal terluar { ... }
if (preg_match('/\{[\s\S]*\}/', $cleanReply, $matches)) {
    $cleanReply = $matches[0];
}

$parsedJson = json_decode($cleanReply, true);

if (!$parsedJson) {
    echo json_encode(['status' => 'error', 'message' => 'Format balasan tidak terbaca: ' . $cleanReply]);
    exit;
}

echo json_encode(['status' => 'success', 'data' => $parsedJson]);