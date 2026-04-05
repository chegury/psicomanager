<?php
require_once __DIR__ . '/../config/db.php';
try {
    $pdo->exec("ALTER TABLE pacientes ADD COLUMN IF NOT EXISTS asaas_id VARCHAR(100) AFTER cpf;");
    $pdo->exec("ALTER TABLE pacientes ADD COLUMN IF NOT EXISTS valor_sessao DECIMAL(10,2) DEFAULT 0.00;");
    $pdo->exec("ALTER TABLE pacientes ADD COLUMN IF NOT EXISTS dia_vencimento INT DEFAULT 5;");
    $pdo->exec("ALTER TABLE pacientes ADD COLUMN IF NOT EXISTS horario_fixo TIME NULL;");
    echo "Banco de dados atualizado com sucesso!";
} catch (Exception $e) {
    echo "Erro ao atualizar banco: " . $e->getMessage();
}
?>
