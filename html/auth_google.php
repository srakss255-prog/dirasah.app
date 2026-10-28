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

if (isset($_GET['action']) && $_GET['action'] === 'register') {
    $_SESSION['temp_reg_nama'] = filter_input(INPUT_GET, 'nama', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['temp_reg_role'] = filter_input(INPUT_GET, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
}

$client_id = '133782997804-cfr5m7irk7t4a5phpno2nsdc8345asff.apps.googleusercontent.com';

// Menggunakan domain aktif hosting secara langsung
$redirect_uri = 'https://dirasah.my.id/callback_google.php';

$scope = 'email profile';

$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => $scope,
    'access_type'   => 'online',
    'prompt'        => 'select_account'
]);

header('Location: ' . $google_auth_url);
exit;