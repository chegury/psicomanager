<?php
require_once '../config/db.php';
require_once '../classes/CalendarSyncService.php';

echo "<pre>";
echo "=== Iniciando Correção do Sistema ===\n\n";

// 1. Adicionar coluna 'foto' se não existir
echo "1. Verificando tabela 'pacientes'...\n";
try {
    $stmt = $pdo->query("DESCRIBE pacientes");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('foto', $columns)) {
        echo " - Coluna 'foto' não encontrada. Adicionando...\n";
        $pdo->exec("ALTER TABLE pacientes ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
        echo " - Coluna 'foto' adicionada com sucesso.\n";
    } else {
        echo " - Coluna 'foto' já existe.\n";
    }
} catch (Exception $e) {
    echo "Erro ao verificar/alterar tabela: " . $e->getMessage() . "\n";
}

// 2. Verificar Pacientes Ativos e Dados de Contrato
echo "\n2. Verificando Pacientes Ativos...\n";
$stmt = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1");
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($pacientes) == 0) {
    echo " - Nenhum paciente ativo encontrado. Ative um paciente para gerar agenda.\n";
} else {
    foreach ($pacientes as $p) {
        echo " - Paciente: {$p['nome']} (ID: {$p['id']})\n";
        echo "   Dia Fixo: {$p['dia_semana_fixo']} | Horário: {$p['horario_fixo']}\n";
        
        if (empty($p['dia_semana_fixo']) || empty($p['horario_fixo'])) {
            echo "   [ALERTA] Dados de contrato incompletos. Agenda não será gerada.\n";
        }
    }
}

// 3. Gerar Agenda (Debug Verbose)
echo "\n3. Gerando Agenda para o Mês Atual (" . date('m/Y') . ")...\n";
$calendarService = new CalendarSyncService($pdo);
$mesAtual = date('m');
$anoAtual = date('Y');
$countTotal = 0;

foreach ($pacientes as $p) {
    if (!empty($p['dia_semana_fixo']) && !empty($p['horario_fixo'])) {
        echo " -> Processando {$p['nome']} (Dia: {$p['dia_semana_fixo']})...\n";
        
        // Debug da lógica interna
        $numDiasMes = cal_days_in_month(CAL_GREGORIAN, $mesAtual, $anoAtual);
        $diasEncontrados = 0;
        
        for ($d = 1; $d <= $numDiasMes; $d++) {
            $dataCheck = "$anoAtual-$mesAtual-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            $diaSemanaData = date('N', strtotime($dataCheck));
            
            if ($diaSemanaData == $p['dia_semana_fixo']) {
                echo "    - Dia $d ($dataCheck) é compatível.\n";
                $diasEncontrados++;
                
                // Tenta gerar via serviço
                // O serviço verifica se já existe antes de inserir
            }
        }
        
        if ($diasEncontrados == 0) {
            echo "    [AVISO] Nenhum dia compatível encontrado neste mês.\n";
        }
        
        // Executa geração real
        try {
            $qtd = $calendarService->generateAndSync($p['id'], 0, $mesAtual, $anoAtual);
            echo "    > Resultado do Serviço: $qtd sessões inseridas.\n";
            $countTotal += $qtd;
        } catch (Exception $e) {
            echo "    [ERRO] Falha no serviço: " . $e->getMessage() . "\n";
        }
    } else {
        echo " -> Pular {$p['nome']} (Dados incompletos)\n";
    }
}

echo "\n=== Concluído. Total de sessões na agenda: $countTotal ===\n";
echo "</pre>";
?>
