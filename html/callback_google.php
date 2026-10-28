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

$client_id     = '133782997804-cfr5m7irk7t4a5phpno2nsdc8345asff.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri  = 'https://dirasah.my.id/callback_google.php';

if (isset($_GET['code'])) {
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code'          => $_GET['code'],
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $response_raw = curl_exec($ch);
    $response = json_decode($response_raw, true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        $ch_user = curl_init();
        curl_setopt($ch_user, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt($ch_user, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
        curl_setopt($ch_user, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_user, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_user, CURLOPT_SSL_VERIFYHOST, 0);
        $user_info_raw = curl_exec($ch_user);
        $user_info = json_decode($user_info_raw, true);
        curl_close($ch_user);

        if (!$user_info || empty($user_info['sub'])) {
            die("Gagal mengambil profil dari Google: " . htmlspecialchars($user_info_raw));
        }

        $google_id    = $user_info['sub'];
        $email        = $user_info['email'] ?? '';
        $avatar       = $user_info['picture'] ?? null;
        $nama_lengkap = !empty($_SESSION['temp_reg_nama']) ? $_SESSION['temp_reg_nama'] : ($user_info['name'] ?? 'Pengguna');
        $role         = !empty($_SESSION['temp_reg_role']) ? $_SESSION['temp_reg_role'] : 'umum';

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $update = $pdo->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
                $update->execute([$google_id, $avatar, $user['id']]);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama']    = $user['nama_lengkap'];
                $_SESSION['role']    = $user['role'];
            } else {
                // Memasukkan phone_number sebagai NULL agar tidak memicu duplikat string kosong
                $insert = $pdo->prepare("INSERT INTO users (google_id, nama_lengkap, email, role, avatar, password, phone_number) VALUES (?, ?, ?, ?, ?, ?, NULL)");
                $insert->execute([$google_id, $nama_lengkap, $email, $role, $avatar, '']);
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['nama']    = $nama_lengkap;
                $_SESSION['role']    = $role;
            }

            unset($_SESSION['temp_reg_nama'], $_SESSION['temp_reg_role']);

            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            die("Database Error saat login Google: " . $e->getMessage());
        }
    } else {
        die("Pertukaran token Google gagal: " . htmlspecialchars($response_raw));
    }
}

header('Location: login.php?status=error');
exit;