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

// Buat session login otomatis sebagai user kamu
$_SESSION['user_id'] = 1; // Sesuaikan ID user kamu di database jika bukan 1
$_SESSION['nama'] = 'Shahzada Rakhshaa Aprihansyah';

// Langsung lempar ke halaman kiblat
header('Location: kiblat.php');
exit;