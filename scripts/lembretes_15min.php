<?php
// Script de Cron Job para Lembretes
// Executar a cada 15 minutos

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnvLoader.php';
require_once __DIR__ . '/../classes/CalendarSyncService.php';

// Carrega variáveis de ambiente
EnvLoader::load(__DIR__ . '/../.env');

echo "Iniciando Lembretes - " . date('Y-m-d H:i:s') . "\n";

try {
    $service = new CalendarSyncService($pdo);
    
    // Envia lembretes
    $enviados = $service->sendReminderCron();
    
    echo "Lembretes enviados: $enviados\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
