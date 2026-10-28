<?php
function sendWhatsAppMessage($targetNumber, $messageText) {
    $cleanNumber = preg_replace('/[^0-9]/', '', $targetNumber);
    if (strpos($cleanNumber, '0') === 0) {
        $cleanNumber = '62' . substr($cleanNumber, 1);
    }

    $gatewayUrl = 'http://172.17.0.1:8000/send-message'; 
    $apiKey     = 'dirasah123';

    $payload = json_encode([
        'number'  => $cleanNumber,
        'message' => $messageText
    ]);

    $ch = curl_init($gatewayUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200);
}
