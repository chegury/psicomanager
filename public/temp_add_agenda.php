<?php
require_once '../config/db.php';

// Pega o primeiro paciente ativo
$stmt = $pdo->query("SELECT id FROM pacientes WHERE ativo = 1 LIMIT 1");
$paciente = $stmt->fetch();

if ($paciente) {
    $hoje = date('Y-m-d 10:00:00');
    $pdo->prepare("INSERT INTO agenda (paciente_id, data_sessao, status) VALUES (?, ?, 'agendado')")->execute([$paciente['id'], $hoje]);
    echo "Agendamento criado para hoje: $hoje";
} else {
    echo "Nenhum paciente ativo encontrado.";
}
?>
