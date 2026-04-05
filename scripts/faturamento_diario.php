<?php
// Script de Cron Job para Faturamento Diário
// Executar diariamente (ex: 08:00 AM)

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnvLoader.php';
require_once __DIR__ . '/../classes/BillingEngine.php';

// Carrega variáveis de ambiente
EnvLoader::load(__DIR__ . '/../.env');

echo "Iniciando Faturamento Diário - " . date('Y-m-d H:i:s') . "\n";

try {
    $engine = new BillingEngine($pdo);

    // 1. Buscar pacientes com vencimento próximo
    // Regra: dia_vencimento = hoje + dias_antecedencia
    // Ou regra simplificada: Todos os ativos, e o método calculateMonthlySessions decide se gera ou não?
    // O ideal é filtrar para não processar todos os pacientes todo dia.
    
    // Ajuste SQL para pegar quem tem (dia_vencimento - dias_antecedencia) == hoje
    // Ex: Vence dia 10, antecedencia 5. Gatilho dia 5.
    // Se hoje é dia 5.
    
    $diaHoje = date('d');
    $mesAtual = date('m');
    $anoAtual = date('Y');
    
    // Busca todos os pacientes ativos para verificar
    // (Em produção com muitos dados, faria o filtro no SQL)
    $stmt = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 0;

    foreach ($pacientes as $paciente) {
        $diaVencimento = $paciente['dia_vencimento'];
        $diasAntecedencia = $paciente['dias_antecedencia'];
        
        // Data de vencimento neste mês
        $dataVencimento = "$anoAtual-$mesAtual-" . str_pad($diaVencimento, 2, '0', STR_PAD_LEFT);
        
        // Data de gatilho
        $dataGatilho = date('Y-m-d', strtotime("-$diasAntecedencia days", strtotime($dataVencimento)));
        
        if (date('Y-m-d') == $dataGatilho) {
            echo "Processando: {$paciente['nome']} (Venc: $dataVencimento)\n";
            
            // Calcula sessões
            $resultado = $engine->calculateMonthlySessions($paciente['id'], $mesAtual, $anoAtual);
            
            if ($resultado['valor_total'] > 0) {
                // Dispara alerta
                $mesReferencia = "$anoAtual-$mesAtual";
                $engine->triggerApprovalAlert(
                    $paciente['id'], 
                    $resultado['valor_total'], 
                    $resultado['qtd_sessoes'], 
                    $mesReferencia
                );
                echo " - Alerta enviado.\n";
                $count++;
            } else {
                echo " - Sem sessões/valor zerado.\n";
            }
        }
    }
    
    // 2. Gerar Agenda do Mês (Garante que os eventos existam)
    require_once __DIR__ . '/../classes/CalendarSyncService.php';
    $calendarService = new CalendarSyncService($pdo);
    
    foreach ($pacientes as $paciente) {
        // Gera agenda para o mês atual
        $calendarService->generateAndSync($paciente['id'], 0, $mesAtual, $anoAtual);
        
        // Gera agenda para o próximo mês também (para visualização antecipada)
        $proximoMes = date('m', strtotime('+1 month'));
        $proximoAno = date('Y', strtotime('+1 month'));
        $calendarService->generateAndSync($paciente['id'], 0, $proximoMes, $proximoAno);
    }
    echo "Agenda sincronizada.\n";

    echo "Fim. Processados: $count\n";

} catch (Exception $e) {
    echo "Erro Crítico: " . $e->getMessage() . "\n";
}
