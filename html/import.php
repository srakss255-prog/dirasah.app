<?php
// Koneksi ke Aiven
$host     = 'mysql-29ace160-srakss255-4581.aivencloud.com';
$port     = 28302;
$dbname   = 'defaultdb';
$username = 'avnadmin';
$password = 'AVNS_AeSn3UrqqGD44oOIrPJ';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);

    // Baca file SQL dari Downloads
    $sqlFile = 'C:\\Users\\S.Raks Aprihansyah\\Downloads\\dirasah_db.sql';
    if (!file_exists($sqlFile)) {
        die("File SQL tidak ditemukan di: " . $sqlFile);
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);

    echo "<h2 style='color:green;'>Alhamdulillah! Database berhasil diimpor ke Aiven.</h2>";
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Gagal: " . $e->getMessage() . "</h2>";
}