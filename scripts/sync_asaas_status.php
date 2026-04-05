<?php
/**
 * Sincroniza status das faturas com o Asaas
 * Use este script para atualizar manualmente o status dos pagamentos
 * 
 * Acesse: /PsicoinspireManager/scripts/sync_asaas_status.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnvLoader.php';
require_once __DIR__ . '/../classes/AsaasClient.php';
require_once __DIR__ . '/../includes/functions.php';

EnvLoader::load(__DIR__ . '/../.env');

// Se chamado via web, mostra output formatado
$isWeb = php_sapi_name() !== 'cli';
if ($isWeb) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<pre style='font-family:monospace;background:#1e1e1e;color:#fff;padding:20px;'>";
}

function output($msg) {
    global $isWeb;
    echo $msg . ($isWeb ? "\n" : "\n");
}

output("🔄 Sincronizando status das faturas com Asaas...\n");

try {
    $asaas = new AsaasClient();
    
    // Buscar faturas pendentes que tem asaas_id
    $stmt = $pdo->query("
        SELECT f.*, p.nome as paciente_nome 
        FROM faturas f 
        JOIN pacientes p ON f.paciente_id = p.id
        WHERE f.asaas_id IS NOT NULL 
        AND f.asaas_id != ''
        AND f.status = 'pendente'
    ");
    
    $faturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($faturas);
    $atualizados = 0;
    
    output("📋 Encontradas $total faturas pendentes com ID Asaas\n");
    
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
    
    foreach ($faturas as $fatura) {
        $asaasId = $fatura['asaas_id'];
        output("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        output("📄 Fatura #{$fatura['id']} - {$fatura['paciente_nome']}");
        output("   Asaas ID: $asaasId");
        
        try {
            // Consulta o status no Asaas
            $payment = $asaas->getPayment($asaasId);
            
            if ($payment && isset($payment['status'])) {
                $asaasStatus = $payment['status'];
                $novoStatus = $statusMap[$asaasStatus] ?? null;
                $paymentDate = $payment['paymentDate'] ?? null;
                
                output("   Status Asaas: $asaasStatus → $novoStatus");
                
                if ($novoStatus && $novoStatus !== $fatura['status']) {
                    // Atualiza no banco
                    $stmtUpdate = $pdo->prepare("
                        UPDATE faturas 
                        SET status = ?, data_pagamento = ?
                        WHERE id = ?
                    ");
                    $stmtUpdate->execute([$novoStatus, $paymentDate, $fatura['id']]);
                    
                    output("   ✅ Atualizado para: $novoStatus");
                    $atualizados++;
                } else {
                    output("   ⏸️  Sem alteração");
                }
            } else {
                output("   ⚠️  Resposta inválida do Asaas");
            }
            
        } catch (Exception $e) {
            output("   ❌ Erro: " . $e->getMessage());
        }
        
        // Pequena pausa para não sobrecarregar a API
        usleep(200000); // 200ms
    }
    
    output("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    output("✅ Sincronização concluída!");
    output("   Total consultado: $total");
    output("   Atualizados: $atualizados");
    
} catch (Exception $e) {
    output("❌ Erro geral: " . $e->getMessage());
}

if ($isWeb) {
    echo "</pre>";
    echo "<br><a href='/PsicoinspireManager/public/cobrancas.php' style='color:#4ec9b0;'>← Voltar para Cobranças</a>";
}
