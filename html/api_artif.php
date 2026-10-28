<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
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

$groq_api_key = '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';

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
$userMessage = trim($data['message'] ?? '');
$imageBase64 = trim($data['image_base64'] ?? '');

if (empty($userMessage) && empty($imageBase64)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Pertanyaan tidak boleh kosong.']);
    exit;
}

if (!empty($imageBase64) && empty($userMessage)) {
    echo json_encode([
        'status' => 'success',
        'reply'  => 'Afwan, fitur analisis gambar langsung sedang dalam pemeliharaan. Silakan ketikkan teks pertanyaan atau potongan dalil secara langsung.'
    ]);
    exit;
}

$systemPrompt = "Nama Anda adalah Ustadz Artif, asisten AI studi Islam ramah dan cerdas di platform Dirasah.id. Jawablah setiap pertanyaan pengguna seputar agama Islam, Al-Qur'an, hadits, fikih, dan adab dengan santun, mendalam, dan jelas. Bila pengguna menyapa (seperti 'hai', 'halo', 'assalamu'alaikum'), balaslah sapaan tersebut dengan hangat dan tanyakan apa yang bisa dibantu. Dilarang menggunakan tanda bintang tebal (** atau *). Gunakan teks bersih dan rapi.";

// 1. Ambil daftar model yang benar-benar aktif di akun Groq saat ini
$candidateModels = [];

$chList = curl_init('https://api.groq.com/openai/v1/models');
curl_setopt_array($chList, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $groq_api_key,
        'User-Agent: DirasahApp/1.0'
    ],
    CURLOPT_TIMEOUT => 8,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
]);
$resList = curl_exec($chList);
curl_close($chList);

$listData = json_decode($resList, true);

if (!empty($listData['data']) && is_array($listData['data'])) {
    foreach ($listData['data'] as $m) {
        $id = $m['id'];
        $idLower = strtolower($id);

        // Lewati model non-teks dan model preview yang sudah ditarik
        if (
            strpos($idLower, 'whisper') !== false ||
            strpos($idLower, 'guard') !== false ||
            strpos($idLower, 'vision') !== false ||
            strpos($idLower, 'preview') !== false ||
            strpos($idLower, 'orpheus') !== false ||
            strpos($idLower, 'embed') !== false ||
            (isset($m['active']) && $m['active'] === false)
        ) {
            continue;
        }

        $candidateModels[] = $id;
    }
}

// Cadangan urutan model resmi jika endpoint list gagal
if (empty($candidateModels)) {
    $candidateModels = [
        'llama-3.3-70b-versatile',
        'llama-3.1-8b-instant',
        'deepseek-r1-distill-llama-70b',
        'qwen-2.5-32b',
        'mixtral-8x7b-32768',
        'gemma2-9b-it'
    ];
}

// 2. Coba kirim pesan ke model kandidat sampai dapat respon 200 OK
$aiReply = null;
$lastError = 'Gagal memproses jawaban dari AI.';

foreach ($candidateModels as $activeModel) {
    $payload = [
        'model' => $activeModel,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'temperature' => 0.6,
        'max_tokens' => 1000
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $groq_api_key,
            'User-Agent: DirasahApp/1.0'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        $lastError = 'Koneksi cURL: ' . $curlErr;
        continue;
    }

    $resData = json_decode($response, true);

    if ($httpCode === 200 && !empty($resData['choices'][0]['message']['content'])) {
        $aiReply = trim($resData['choices'][0]['message']['content']);
        $aiReply = preg_replace('/<think>[\s\S]*?<\/think>/', '', $aiReply);
        $aiReply = str_replace(['**', '*'], '', $aiReply);
        $aiReply = preg_replace('/^[ \t]+/m', '', $aiReply);
        $aiReply = preg_replace('/\n{3,}/', "\n\n", $aiReply);
        $aiReply = trim($aiReply);
        break;
    } else {
        $lastError = $resData['error']['message'] ?? ('HTTP ' . $httpCode);
    }
}

if ($aiReply !== null) {
    echo json_encode([
        'status' => 'success',
        'reply'  => $aiReply
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $lastError
    ]);
}