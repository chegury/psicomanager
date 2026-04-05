<?php
require_once '../config/db.php';
require_once '../classes/CalendarSyncService.php';

echo "Running V2 Script...\n";

// DEBUG: Check columns
try {
    $cols = $pdo->query("SHOW COLUMNS FROM pacientes")->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in DB:\n";
    foreach ($cols as $c) {
        echo $c['Field'] . "\n";
    }
} catch (Exception $e) {
    echo "Error showing columns: " . $e->getMessage() . "\n";
}

// Limpar dados de teste
$pdo->exec("DELETE FROM agenda WHERE status = 'teste_bi'");
$pdo->exec("DELETE FROM pacientes WHERE nome = 'Teste Quinzenal'");

// Criar Paciente Quinzenal (Semana PAR = 0)
$diaSemana = date('N'); // Hoje

try {
    $stmt = $pdo->prepare("INSERT INTO pacientes (nome, email, whatsapp, valor_sessao, dia_vencimento, ativo, dia_semana_fixo, horario_fixo, frequencia, semana_inicio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Teste Quinzenal', 'teste@bi.com', '11999999999', 200.00, 10, 1, $diaSemana, '14:00:00', 'quinzenal', 0]);
    $id = $pdo->lastInsertId();
    echo "Paciente criado ID: $id (Quinzenal Par)\n";

    // Rodar Sync
    $service = new CalendarSyncService($pdo);
    $mes = date('m');
    $ano = date('Y');
    $qtd = $service->generateAndSync($id, 0, $mes, $ano);

    echo "Sessões geradas: $qtd\n";

    // Verificar datas geradas
    $stmt = $pdo->prepare("SELECT data_sessao FROM agenda WHERE paciente_id = ? ORDER BY data_sessao");
    $stmt->execute([$id]);
    $sessoes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($sessoes as $data) {
        $semana = date('W', strtotime($data));
        $par = ($semana % 2 == 0) ? 'Par' : 'Ímpar';
        echo "Sessão: $data (Semana $semana - $par)\n";
        
        // Marcar como teste para limpeza futura
        $pdo->prepare("UPDATE agenda SET status = 'teste_bi' WHERE paciente_id = ?")->execute([$id]);
    }

} catch (Exception $e) {
    echo "Error inserting/syncing: " . $e->getMessage() . "\n";
}
?>
