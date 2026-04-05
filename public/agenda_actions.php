<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'move') {
    $id = $_POST['id'] ?? null;
    $newDate = $_POST['new_date'] ?? null;
    // Opcional: new_time se implementarmos drop em horários específicos
    
    if ($id && $newDate) {
        try {
            // Mantém o horário original, muda apenas a data
            $stmt = $pdo->prepare("UPDATE agenda SET data_sessao = CONCAT(?, ' ', TIME(data_sessao)) WHERE id = ?");
            $stmt->execute([$newDate, $id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    }

} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM agenda WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Agendamento não encontrado ou já excluído']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Ação inválida']);
}
?>
