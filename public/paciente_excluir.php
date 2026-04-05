<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Exclusão Lógica (Soft Delete) para manter histórico financeiro
        $stmt = $pdo->prepare("UPDATE pacientes SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        // Cancelar agendamentos futuros
        $pdo->prepare("DELETE FROM agenda WHERE paciente_id = ? AND data_sessao > NOW()")->execute([$id]);
        
        header("Location: pacientes.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        die("Erro ao excluir paciente: " . $e->getMessage());
    }
} else {
    header("Location: pacientes.php");
    exit;
}
?>
