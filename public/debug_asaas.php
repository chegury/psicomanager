<?php
/**
 * Script de Debug v2 - Mostra resposta RAW
 */

require_once '../classes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../.env');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$apiKey = $_ENV['ASAAS_API_KEY'] ?? getenv('ASAAS_API_KEY') ?? '';
$apiUrl = 'https://sandbox.asaas.com/api/v3';

echo "<pre style='background:#1e1e1e;color:#fff;padding:20px;font-size:14px;'>";
echo "<span style='color:#4ec9b0;font-size:18px;'>🔍 DEBUG ASAAS API v2</span>\n\n";

// Info da API Key
echo "<span style='color:#dcdcaa;'>API Key:</span> " . substr($apiKey, 0, 30) . "...\n";
echo "<span style='color:#dcdcaa;'>Tamanho:</span> " . strlen($apiKey) . " chars\n";
echo "<span style='color:#dcdcaa;'>Primeiro char:</span> '" . substr($apiKey, 0, 1) . "' (ASCII: " . ord(substr($apiKey, 0, 1)) . ")\n\n";

// Verifica se começa com $
if (substr($apiKey, 0, 1) === '$') {
    echo "<span style='color:#f44747;'>⚠️ A API Key começa com $ - isso é normal para Asaas</span>\n\n";
}

// Teste simples: GET
echo "<span style='color:#569cd6;'>━━━━ TESTE: GET /customers ━━━━</span>\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl . '/customers?limit=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'access_token: ' . $apiKey,
        'User-Agent: PsiManager/1.0'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true,
    CURLOPT_HEADER => true // Inclui headers na resposta
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

// Separa headers do body
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "<span style='color:#dcdcaa;'>HTTP Code:</span> <span style='color:" . ($httpCode < 400 ? '#4ec9b0' : '#f44747') . ";'>$httpCode</span>\n";
echo "<span style='color:#dcdcaa;'>Content-Type recebido:</span> " . ($curlInfo['content_type'] ?? 'N/A') . "\n";
echo "<span style='color:#dcdcaa;'>cURL Error:</span> " . ($curlError ?: 'Nenhum') . "\n\n";

echo "<span style='color:#569cd6;'>Response Headers:</span>\n";
echo "<span style='color:#808080;'>" . htmlspecialchars($headers) . "</span>\n\n";

echo "<span style='color:#569cd6;'>Response Body (RAW):</span>\n";
echo "<span style='color:#ce9178;'>" . htmlspecialchars($body) . "</span>\n\n";

echo "<span style='color:#569cd6;'>Response Body (JSON Decoded):</span>\n";
$decoded = json_decode($body, true);
if ($decoded) {
    echo "<span style='color:#b5cea8;'>";
    print_r($decoded);
    echo "</span>";
} else {
    echo "<span style='color:#f44747;'>Não é JSON válido ou está vazio</span>\n";
    echo "<span style='color:#dcdcaa;'>json_last_error:</span> " . json_last_error_msg() . "\n";
}

// Verifica se a resposta contém HTML (página de erro)
if (stripos($body, '<html') !== false || stripos($body, '<!DOCTYPE') !== false) {
    echo "\n<span style='color:#f44747;'>⚠️ A API retornou HTML ao invés de JSON - possível erro de servidor ou URL incorreta</span>\n";
}

echo "\n<span style='color:#569cd6;'>━━━━ INFO CURL ━━━━</span>\n";
echo "<span style='color:#dcdcaa;'>URL Final:</span> " . $curlInfo['url'] . "\n";
echo "<span style='color:#dcdcaa;'>IP:</span> " . ($curlInfo['primary_ip'] ?? 'N/A') . "\n";
echo "<span style='color:#dcdcaa;'>Time Total:</span> " . round($curlInfo['total_time'], 2) . "s\n";

echo "\n</pre>";
?>
