<?php
// Script de Cron Job para Lembretes de Agenda
// Deve rodar a cada 15 minutos

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

echo "Verificando agenda para lembretes...\n";

// Buscar sessões agendadas para os próximos 30 minutos que ainda não foram notificadas
// status = 'agendado' AND notificacao_enviada = 0
// data_sessao BETWEEN NOW() AND NOW() + INTERVAL 30 MINUTE

$sql = "SELECT a.*, p.nome, p.whatsapp 
        FROM agenda a 
        JOIN pacientes p ON a.paciente_id = p.id 
        WHERE a.status = 'agendado' 
        AND a.notificacao_enviada = 0 
        AND a.data_sessao BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 MINUTE)";

$stmt = $pdo->query($sql);
$sessoes = $stmt->fetchAll();

$notificacoesEnviadas = 0;

foreach ($sessoes as $sessao) {
    echo "Enviando lembrete para: {$sessao['nome']} (Sessão: {$sessao['data_sessao']})\n";
    
    // Simulação de envio de WhatsApp
    // API Call para Gateway de WhatsApp (ex: Twilio, Z-API, etc)
    $mensagem = "Olá {$sessao['nome']}, sua sessão começa em breve! Link: {$sessao['link_meet']}";
    echo "  - Mensagem: $mensagem\n";
    
    // Marcar como notificado
    $stmtUpdate = $pdo->prepare("UPDATE agenda SET notificacao_enviada = 1 WHERE id = ?");
    $stmtUpdate->execute([$sessao['id']]);
    
    $notificacoesEnviadas++;
}

echo "Finalizado. Lembretes enviados: $notificacoesEnviadas\n";
?>
