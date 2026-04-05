<?php
// Test Runner Simples
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/EnvLoader.php';
require_once __DIR__ . '/classes/BillingEngine.php';
require_once __DIR__ . '/classes/CalendarSyncService.php';

EnvLoader::load(__DIR__ . '/.env');

echo "<h1>Verificação do Sistema</h1>";

// 1. Verificar Classes
$classes = ['AsaasClient', 'WhatsAppClient', 'BillingEngine', 'GoogleClientWrapper', 'CalendarSyncService'];
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✅ Classe $class carregada.</p>";
    } else {
        echo "<p style='color:red'>❌ Classe $class NÃO encontrada.</p>";
    }
}

// 2. Testar Conexão DB
if (isset($pdo)) {
    echo "<p style='color:green'>✅ Conexão com Banco de Dados OK.</p>";
} else {
    echo "<p style='color:red'>❌ Falha na conexão com Banco de Dados.</p>";
}

// 3. Teste Lógico BillingEngine (Simulação)
try {
    $engine = new BillingEngine($pdo);
    // Mock de dados
    $mes = date('m');
    $ano = date('Y');
    // Tenta calcular para um ID inexistente para ver se trata erro
    try {
        $engine->calculateMonthlySessions(99999, $mes, $ano);
    } catch (Exception $e) {
        echo "<p style='color:green'>✅ Tratamento de erro BillingEngine OK (Paciente não encontrado).</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>❌ Erro fatal no BillingEngine: " . $e->getMessage() . "</p>";
}

// 4. Teste Lógico CalendarSyncService
try {
    $calendar = new CalendarSyncService($pdo);
    echo "<p style='color:green'>✅ CalendarSyncService instanciado.</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>❌ Erro fatal no CalendarSyncService: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p>Verificação concluída. Se todos os itens acima estiverem verdes, a estrutura base está funcional.</p>";
