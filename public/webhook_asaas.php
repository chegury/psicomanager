<?php
/**
 * Webhook do Asaas
 * 
 * Configure no painel do Asaas:
 * Minha Conta → Integrações → Webhooks
 * URL: https://seu-dominio.com/PsicoinspireManager/public/webhook_asaas.php
 * 
 * Para teste local, use ngrok ou similar
 */

// Log de todas as requisições
$logFile = __DIR__ . '/../logs/asaas_webhook.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Pega o body da requisição
$rawInput = file_get_contents('php://input');
$timestamp = date('Y-m-d H:i:s');

// Log da requisição
file_put_contents($logFile, "[$timestamp] Requisição recebida\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] Body: $rawInput\n\n", FILE_APPEND);

// Responde imediatamente para o Asaas não ficar esperando
http_response_code(200);
header('Content-Type: application/json');

// Se não tiver body, retorna
if (empty($rawInput)) {
    echo json_encode(['status' => 'ok', 'message' => 'No data']);
    exit;
}

// Decodifica o JSON
$data = json_decode($rawInput, true);

if (!$data || !isset($data['event'])) {
    file_put_contents($logFile, "[$timestamp] Erro: JSON inválido ou sem evento\n\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

// Log do evento
file_put_contents($logFile, "[$timestamp] Evento: {$data['event']}\n", FILE_APPEND);

// Processa o evento
try {
    require_once '../config/db.php';
    
    $event = $data['event'];
    $payment = $data['payment'] ?? null;
    
    // Eventos de pagamento
    if ($payment && isset($payment['id'])) {
        $asaasId = $payment['id'];
        $status = $payment['status'] ?? '';
        $paymentDate = $payment['paymentDate'] ?? null;
        
        file_put_contents($logFile, "[$timestamp] Payment ID: $asaasId, Status: $status\n", FILE_APPEND);
        
        // Mapear status do Asaas para o nosso sistema
        $statusMap = [
            'PENDING' => 'pendente',
            'RECEIVED' => 'pago',
            'CONFIRMED' => 'pago',
            'RECEIVED_IN_CASH' => 'pago',
            'OVERDUE' => 'vencido',
            'REFUNDED' => 'pendente',
            'REFUND_REQUESTED' => 'pendente',
        ];
        
        $novoStatus = $statusMap[$status] ?? null;
        
        if ($novoStatus) {
            // Atualiza a fatura no banco
            $stmt = $pdo->prepare("
                UPDATE faturas 
                SET status = ?, 
                    data_pagamento = ?
                WHERE asaas_id = ?
            ");
            
            $dataPagamento = null;
            if ($novoStatus === 'pago' && $paymentDate) {
                $dataPagamento = $paymentDate;
            }
            
            $stmt->execute([$novoStatus, $dataPagamento, $asaasId]);
            $affected = $stmt->rowCount();
            
            file_put_contents($logFile, "[$timestamp] Atualizado: $affected fatura(s) para status '$novoStatus'\n\n", FILE_APPEND);
        }
    }
    
    echo json_encode(['status' => 'ok', 'processed' => true]);
    
} catch (Exception $e) {
    file_put_contents($logFile, "[$timestamp] ERRO: " . $e->getMessage() . "\n\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
