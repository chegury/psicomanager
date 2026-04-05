<?php
require_once '../config/db.php';

try {
    // Adicionar coluna frequencia
    $pdo->exec("ALTER TABLE pacientes ADD COLUMN frequencia ENUM('semanal', 'quinzenal') DEFAULT 'semanal'");
    echo "Coluna 'frequencia' adicionada com sucesso.\n";
} catch (Exception $e) {
    echo "Erro ao adicionar 'frequencia' (pode já existir): " . $e->getMessage() . "\n";
}

try {
    // Adicionar coluna semana_inicio (1 = Ímpar, 0 = Par)
    $pdo->exec("ALTER TABLE pacientes ADD COLUMN semana_inicio TINYINT(1) DEFAULT 1");
    echo "Coluna 'semana_inicio' adicionada com sucesso.\n";
} catch (Exception $e) {
    echo "Erro ao adicionar 'semana_inicio' (pode já existir): " . $e->getMessage() . "\n";
}
?>
