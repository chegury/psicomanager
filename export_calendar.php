<?php
// Exportar calendário no formato ICS para Google Calendar
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$mesRef = $_GET['mes'] ?? date('Y-m');

$stmtAgenda = $pdo->prepare("
    SELECT a.*, p.nome, p.email 
    FROM agenda a 
    JOIN pacientes p ON a.paciente_id = p.id 
    WHERE DATE_FORMAT(a.data_sessao, '%Y-%m') = ? 
    AND p.ativo = 1 
    ORDER BY a.data_sessao ASC
");
$stmtAgenda->execute([$mesRef]);
$agendaMes = $stmtAgenda->fetchAll(PDO::FETCH_ASSOC);

// Gerar ICS
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="agenda_' . $mesRef . '.ics"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//PsiManager//Agenda//PT\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";
echo "X-WR-CALNAME:PsiManager - Agenda\r\n";
echo "X-WR-TIMEZONE:America/Sao_Paulo\r\n";

foreach ($agendaMes as $ev) {
    $dtStart = date('Ymd\THis', strtotime($ev['data_sessao']));
    $dtEnd = date('Ymd\THis', strtotime($ev['data_sessao'] . ' +50 minutes'));
    $uid = 'psimanager-' . $ev['id'] . '@psicoinspire';
    $summary = 'Sessão: ' . $ev['nome'];
    $description = 'Sessão de Psicoterapia com ' . $ev['nome'];
    $meetLink = $ev['link_meet'] ?? '';
    
    if ($meetLink) {
        $description .= "\\nLink Meet: $meetLink";
    }
    
    echo "BEGIN:VEVENT\r\n";
    echo "UID:$uid\r\n";
    echo "DTSTART;TZID=America/Sao_Paulo:$dtStart\r\n";
    echo "DTEND;TZID=America/Sao_Paulo:$dtEnd\r\n";
    echo "SUMMARY:$summary\r\n";
    echo "DESCRIPTION:$description\r\n";
    if ($meetLink) {
        echo "URL:$meetLink\r\n";
    }
    if ($ev['email']) {
        echo "ATTENDEE;RSVP=TRUE:mailto:" . $ev['email'] . "\r\n";
    }
    echo "STATUS:CONFIRMED\r\n";
    echo "BEGIN:VALARM\r\n";
    echo "TRIGGER:-PT15M\r\n";
    echo "ACTION:DISPLAY\r\n";
    echo "DESCRIPTION:Sessão em 15 minutos\r\n";
    echo "END:VALARM\r\n";
    echo "END:VEVENT\r\n";
}

echo "END:VCALENDAR\r\n";
?>
