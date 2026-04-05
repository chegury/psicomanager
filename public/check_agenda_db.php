<?php
require_once __DIR__ . '/../config/db.php';

echo "<pre>";
echo "=== Conteúdo da Tabela Agenda ===\n";

$stmt = $pdo->query("SELECT * FROM agenda");
$agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total de registros: " . count($agendas) . "\n";

foreach ($agendas as $a) {
    echo "ID: {$a['id']} | Paciente: {$a['paciente_id']} | Data: {$a['data_sessao']} | Status: {$a['status']}\n";
}

echo "</pre>";
?>
