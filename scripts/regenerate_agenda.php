<?php
/**
 * Regenera a agenda com a lógica corrigida de frequência quinzenal
 * 
 * Acesse: http://localhost/PsicoinspireManager/scripts/regenerate_agenda.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/CalendarSyncService.php';

$mesAtual = date('m');
$anoAtual = date('Y');

echo "<pre style='background:#1e1e1e;color:#fff;padding:20px;font-family:monospace;'>";
echo "<span style='color:#4ec9b0;font-size:18px;'>📅 Regenerando Agenda</span>\n";
echo "Mês: $mesAtual/$anoAtual\n\n";

// 1. Primeiro, mostra a situação atual
$stmtCount = $pdo->prepare("
    SELECT p.id, p.nome, p.frequencia, p.semana_inicio, COUNT(a.id) as qtd_agendamentos
    FROM pacientes p
    LEFT JOIN agenda a ON p.id = a.paciente_id 
        AND DATE_FORMAT(a.data_sessao, '%Y-%m') = ?
    WHERE p.ativo = 1
    GROUP BY p.id
");
$stmtCount->execute(["$anoAtual-$mesAtual"]);
$situacao = $stmtCount->fetchAll(PDO::FETCH_ASSOC);

echo "<span style='color:#569cd6;'>━━━━ SITUAÇÃO ATUAL ━━━━</span>\n";
foreach ($situacao as $s) {
    $freq = $s['frequencia'] ?? 'semanal';
    $tipo = $freq === 'quinzenal' ? "Quinzenal (" . ($s['semana_inicio'] ? 'Ímpar' : 'Par') . ")" : "Semanal";
    echo "📌 {$s['nome']}: {$s['qtd_agendamentos']} agendamentos [$tipo]\n";
}

// 2. Perguntar se deve limpar ou apenas verificar faltantes
$limpar = isset($_GET['limpar']) && $_GET['limpar'] == '1';

if ($limpar) {
    echo "\n<span style='color:#f44747;'>🗑️ LIMPANDO agenda do mês atual...</span>\n";
    $stmtDelete = $pdo->prepare("DELETE FROM agenda WHERE DATE_FORMAT(data_sessao, '%Y-%m') = ?");
    $stmtDelete->execute(["$anoAtual-$mesAtual"]);
    $deleted = $stmtDelete->rowCount();
    echo "Removidos: $deleted agendamentos\n";
}

// 3. Regenerar agenda
echo "\n<span style='color:#4ec9b0;'>🔄 PROCESSANDO PACIENTES...</span>\n\n";

$calendarService = new CalendarSyncService($pdo);

$stmt = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1");
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalGerado = 0;
foreach ($pacientes as $p) {
    $freq = $p['frequencia'] ?? 'semanal';
    $tipo = $freq === 'quinzenal' ? "Quinzenal" : "Semanal";
    
    echo "<span style='color:#dcdcaa;'>→ {$p['nome']}</span> [$tipo]\n";
    
    if (!empty($p['dia_semana_fixo']) && !empty($p['horario_fixo'])) {
        $qtd = $calendarService->generateAndSync($p['id'], 0, $mesAtual, $anoAtual);
        echo "  ✅ <span style='color:#4ec9b0;'>$qtd novas sessões geradas</span>\n";
        $totalGerado += $qtd;
    } else {
        echo "  ⚠️ Dados de contrato incompletos\n";
    }
}

// 4. Mostrar resultado final
echo "\n<span style='color:#569cd6;'>━━━━ RESULTADO FINAL ━━━━</span>\n";
$stmtFinal = $pdo->prepare("
    SELECT p.nome, COUNT(a.id) as qtd
    FROM pacientes p
    LEFT JOIN agenda a ON p.id = a.paciente_id 
        AND DATE_FORMAT(a.data_sessao, '%Y-%m') = ?
    WHERE p.ativo = 1
    GROUP BY p.id
");
$stmtFinal->execute(["$anoAtual-$mesAtual"]);
$final = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

foreach ($final as $f) {
    echo "📅 {$f['nome']}: {$f['qtd']} sessões\n";
}

echo "\n<span style='color:#4ec9b0;'>✅ Total de novas sessões geradas: $totalGerado</span>\n";

if (!$limpar) {
    echo "\n<span style='color:#ce9178;'>💡 Para limpar e regenerar do zero, acesse:</span>\n";
    echo "<a href='?limpar=1' style='color:#4fc3f7;'>Clique aqui para limpar e regenerar</a>\n";
}

echo "\n<a href='/PsicoinspireManager/public/agenda.php' style='color:#4fc3f7;'>← Voltar para Agenda</a>";
echo "</pre>";
?>
