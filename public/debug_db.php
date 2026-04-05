<?php
require_once '../config/db.php';

echo "<pre>";
echo "=== Verificando Colunas da Tabela Pacientes ===\n";
try {
    $stmt = $pdo->query("DESCRIBE pacientes");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($columns);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "\n=== Verificando Conteúdo da Agenda ===\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM agenda");
    echo "Total de agendamentos: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $pdo->query("SELECT * FROM agenda LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "\n=== Verificando Pacientes Ativos ===\n";
$stmt = $pdo->query("SELECT id, nome, dia_semana_fixo, horario_fixo FROM pacientes WHERE ativo = 1");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "</pre>";
?>
