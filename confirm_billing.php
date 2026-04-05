<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/EnvLoader.php';
require_once __DIR__ . '/classes/BillingEngine.php';

EnvLoader::load(__DIR__ . '/.env');

$faturaId = $_GET['fat_id'] ?? null;
if (!$faturaId) { die("ID da fatura não fornecido."); }

try {
    $engine = new BillingEngine($pdo);
    $resultado = $engine->executeAsaasBilling($faturaId);

    if ($resultado['success']) {
        echo "<h1>Fatura Confirmada com Sucesso!</h1>";
        echo "<p>Cobrança gerada no Asaas e enviada para o paciente.</p>";
        echo "<p>Link: <a href='{$resultado['link']}' target='_blank'>{$resultado['link']}</a></p>";
    } else {
        echo "<h1>Erro ao processar</h1><p>{$resultado['message']}</p>";
    }
} catch (Exception $e) {
    echo "<h1>Erro</h1><p>" . $e->getMessage() . "</p>";
}
