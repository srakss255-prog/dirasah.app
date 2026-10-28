<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';
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

$app_id       = '2960984774261282';
$app_secret   = 'b19abd55cdd1fa85aa3ac5e748477f1c';
$redirect_uri = 'https://dirasah.my.id/callback_facebook.php';

// Cek jika user membatalkan login di Facebook
if (isset($_GET['error'])) {
    header('Location: login.php?error=' . urlencode($_GET['error_description'] ?? 'Login Facebook dibatalkan.'));
    exit;
}

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

// 1. Tukar Code dengan Access Token
$token_url = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
    'client_id'     => $app_id,
    'client_secret' => $app_secret,
    'redirect_uri'  => $redirect_uri,
    'code'          => $code
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$token_response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($token_response, true);

if (!isset($token_data['access_token'])) {
    die('Gagal mendapatkan access token Facebook. Response: ' . htmlspecialchars($token_response));
}

$access_token = $token_data['access_token'];

// 2. Ambil data profil, email, dan foto profil dari Graph API
$graph_url = 'https://graph.facebook.com/v19.0/me?' . http_build_query([
    'fields'       => 'id,name,email,picture.type(large)',
    'access_token' => $access_token
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $graph_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$user_response = curl_exec($ch);
curl_close($ch);

$fb_user = json_decode($user_response, true);

if (!isset($fb_user['id'])) {
    die('Gagal mengambil data profil Facebook: ' . htmlspecialchars($user_response));
}

$facebook_id = $fb_user['id'];
$nama        = $fb_user['name'] ?? 'Pengguna Facebook';
$email       = $fb_user['email'] ?? ($facebook_id . '@facebook.dirasah.id');
$avatar      = $fb_user['picture']['data']['url'] ?? null;
$role        = $_SESSION['reg_role'] ?? 'umum';

// 3. Simpan atau perbarui data user di Database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE facebook_id = ? OR email = ?");
    $stmt->execute([$facebook_id, $email]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['id'];
        $nama    = $user['nama_lengkap'];
        $role    = $user['role'];

        $update = $pdo->prepare("UPDATE users SET facebook_id = ?, avatar = COALESCE(avatar, ?) WHERE id = ?");
        $update->execute([$facebook_id, $avatar, $user_id]);
    } else {
        $insert = $pdo->prepare("INSERT INTO users (facebook_id, nama_lengkap, email, role, avatar, password, phone_number) VALUES (?, ?, ?, ?, ?, '', NULL)");
        $insert->execute([$facebook_id, $nama, $email, $role, $avatar]);
        $user_id = $pdo->lastInsertId();
    }
} catch (PDOException $e) {
    die('Gagal memproses data ke database: ' . $e->getMessage());
}

// 4. Set Cookie & Session Login
setcookie('dirasah_remember_email', $email, time() + (86400 * 30), "/");

$_SESSION['user_id'] = $user_id;
$_SESSION['nama']    = $nama;
$_SESSION['role']    = $role;

// Bersihkan session registrasi sementara
unset($_SESSION['reg_birth'], $_SESSION['reg_role'], $_SESSION['fb_oauth_state']);

// 5. Arahkan ke Dashboard
header('Location: dashboard.php');
exit;