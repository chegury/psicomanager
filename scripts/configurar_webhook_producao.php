<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/AsaasClient.php';

// URL do NGrok fornecida
$baseUrl = "https://eurythmical-exactingly-dustin.ngrok-free.dev";
$webhookUrl = rtrim($baseUrl, '/') . "/public/webhook_asaas.php";

$apiKey = $_ENV['ASAAS_API_KEY'] ?? getenv('ASAAS_API_KEY');

echo "Tentando configurar Webhook no Asaas (Ambiente de Produção)...\n";
echo "URL do Webhook: $webhookUrl\n\n";

if (empty($apiKey)) {
    die("❌ ERRO: ASAAS_API_KEY não encontrada no .env\n");
}

// Endpoint do Asaas para Webhook de Faturas/Cobranças
$apiUrl = "https://www.asaas.com/api/v3/webhook";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'url' => $webhookUrl,
        'email' => 'suporte@psimanager.com',
        'enabled' => true,
        'interrupted' => false,
        'sendType' => 'SEQUENTIALLY',
        'apiVersion' => 3
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'access_token: ' . $apiKey,
        'User-Agent: PsiManager/1.0'
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ SUCESSO: O Webhook foi configurado no Asaas em modo Produção.\n";
    echo "ID: " . ($decoded['id'] ?? 'N/A') . "\n";
} else {
    echo "❌ ERRO ($httpCode): " . ($decoded['errors'][0]['description'] ?? $response) . "\n";
    echo "DICA: Verifique se sua chave de produção tem permissão para configurar webhooks.\n";
}
