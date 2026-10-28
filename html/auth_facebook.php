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

$app_id       = '2960984774261282';
$redirect_uri = 'https://dirasah.my.id/callback_facebook.php';
$permissions  = ['email', 'public_profile'];

// Buat state acak untuk mencegah serangan CSRF
$_SESSION['fb_oauth_state'] = bin2hex(random_bytes(16));

$fb_login_url = 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
    'client_id'     => $app_id,
    'redirect_uri'  => $redirect_uri,
    'state'         => $_SESSION['fb_oauth_state'],
    'scope'         => implode(',', $permissions),
    'response_type' => 'code'
]);

header('Location: ' . $fb_login_url);
exit;