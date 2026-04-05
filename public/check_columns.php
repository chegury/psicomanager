<?php
require_once '../config/db.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pacientes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
