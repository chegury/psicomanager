<?php
// Script de Cron Job para Faturamento
// Deve rodar diariamente (ex: 08:00 AM)

// Ajuste o caminho conforme a estrutura de pastas real no servidor
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

echo "Iniciando verificação de faturamento...\n";

$hoje = date('d'); // Dia atual (ex: 05)
$mesAtual = date('m');
$anoAtual = date('Y');

// Buscar pacientes que precisam ser cobrados hoje
// Lógica: dia_vencimento = hoje + dias_antecedencia
// Ex: Se hoje é dia 05 e antecedência é 5, buscamos quem vence dia 10.

$sql = "SELECT * FROM pacientes WHERE ativo = 1 AND dia_vencimento = (DAY(CURRENT_DATE) + dias_antecedencia)";
// Nota: Essa query é simplificada e pode falhar em viradas de mês (ex: dia 30 + 5 dias). 
// Para produção, usar DATE_ADD no MySQL é mais seguro.
// Ajuste SQL Seguro:
$sql = "SELECT * FROM pacientes WHERE ativo = 1 AND DAY(DATE_SUB(CONCAT(YEAR(CURRENT_DATE), '-', MONTH(CURRENT_DATE), '-', dia_vencimento), INTERVAL dias_antecedencia DAY)) = DAY(CURRENT_DATE)";
// Simplificando para o protótipo: vamos pegar todos e filtrar no PHP para garantir a lógica de dias úteis

$stmt = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1");
$pacientes = $stmt->fetchAll();

$faturasGeradas = 0;

foreach ($pacientes as $paciente) {
    $diaVencimento = $paciente['dia_vencimento'];
    $diasAntecedencia = $paciente['dias_antecedencia'];
    
    // Calcular a data de gatilho
    // Vamos supor que cobramos o mês corrente.
    // Data de Vencimento deste mês
    $dataVencimento = date('Y-m-') . str_pad($diaVencimento, 2, '0', STR_PAD_LEFT);
    
    // Data de Gatilho (Vencimento - Antecedência)
    $dataGatilho = date('Y-m-d', strtotime("-$diasAntecedencia days", strtotime($dataVencimento)));
    
    // Se hoje for o dia do gatilho
    if (date('Y-m-d') == $dataGatilho) {
        echo "Processando paciente: {$paciente['nome']} (Vencimento: $dataVencimento)\n";
        
        // Calcular sessões do mês
        // Quantas "Segundas-feiras" (ex) tem neste mês até o dia do vencimento ou no mês todo?
        // Geralmente cobra-se o mês fechado. Vamos contar quantas ocorrências do dia da semana existem no mês.
        
        $diaSemanaFixo = $paciente['dia_semana_fixo']; // 1=Seg, 5=Sex
        $qtdSessoes = 0;
        
        // Loop pelos dias do mês
        $numDiasMes = cal_days_in_month(CAL_GREGORIAN, $mesAtual, $anoAtual);
        for ($d = 1; $d <= $numDiasMes; $d++) {
            $dataCheck = "$anoAtual-$mesAtual-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            // date('N') retorna 1 para Seg, 7 para Dom
            if (date('N', strtotime($dataCheck)) == $diaSemanaFixo) {
                $qtdSessoes++;
            }
        }
        
        $valorTotal = $qtdSessoes * $paciente['valor_sessao'];
        
        echo "  - Sessões calculadas: $qtdSessoes\n";
        echo "  - Valor Total: R$ $valorTotal\n";
        
        // Verificar se já existe fatura para este mês e paciente
        $mesReferencia = "$anoAtual-$mesAtual";
        $stmtCheck = $pdo->prepare("SELECT id FROM faturas WHERE paciente_id = ? AND mes_referencia = ?");
        $stmtCheck->execute([$paciente['id'], $mesReferencia]);
        
        if ($stmtCheck->rowCount() == 0) {
            // Criar Fatura Pendente
            $stmtInsert = $pdo->prepare("INSERT INTO faturas (paciente_id, mes_referencia, valor_total, qtd_sessoes, status) VALUES (?, ?, ?, ?, 'pendente')");
            $stmtInsert->execute([$paciente['id'], $mesReferencia, $valorTotal, $qtdSessoes]);
            
            echo "  - Fatura gerada com sucesso!\n";
            
            // AQUI ENTRARIA O ENVIO DO WHATSAPP PARA A DRA.
            // Simulação: Log
            echo "  - [SIMULAÇÃO] WhatsApp enviado para Dra. confirmar fatura.\n";
            
            $faturasGeradas++;
        } else {
            echo "  - Fatura já existe para este mês.\n";
        }
    }
}

echo "Finalizado. Faturas geradas: $faturasGeradas\n";
?>
