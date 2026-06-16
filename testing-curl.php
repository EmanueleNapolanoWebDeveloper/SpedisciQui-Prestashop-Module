<?php
header('Content-Type: text/plain');

// L'URL del tuo Laravel locale
$url = 'http://127.0.0.1:8000/api/auth/verify';

echo "=== INIZIO TEST cURL ===\n";
echo "Chiamata verso: $url\n\n";

$ch = curl_init();

// Configurazione cURL minimale e pulita
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout di 5 secondi

// Header fondamentali per farsi ascoltare da Laravel
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

// Inviamo un body vuoto o finto giusto per la POST
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['token' => 'test_123']));

// Esecuzione
$response = curl_exec($ch);

// Controllo degli errori
if (curl_errno($ch)) {
    echo "❌ ERRORE cURL (" . curl_errno($ch) . "): " . curl_error($ch) . "\n";
} else {
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "Status Code HTTP: " . $http_code . "\n";
    echo "Risposta dal Server:\n";
    echo "----------------------------------------\n";
    echo $response ? $response : "[Risposta Vuota]\n";
    echo "\n----------------------------------------\n";
}

curl_close($ch);
echo "\n=== FINE TEST ===\n";
