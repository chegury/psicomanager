<?php
require_once __DIR__ . '/config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("UPDATE pacientes SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
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
