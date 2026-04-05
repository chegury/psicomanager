<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/CalendarSyncService.php';
require_once __DIR__ . '/../classes/HolidayCalculator.php';

echo "<pre>";
echo "=== Debug Agenda Logic ===\n";

$mes = date('m');
$ano = date('Y');
echo "Mês/Ano: $mes/$ano\n";

// Teste Feriados
echo "\n--- Teste Feriados ---\n";
$testDates = ["$ano-01-01", "$ano-11-15", "$ano-11-20", "$ano-12-25", "$ano-11-05"];
foreach ($testDates as $date) {
    $isHoliday = HolidayCalculator::isHoliday($date) ? 'SIM' : 'NÃO';
    echo "Data $date é feriado? $isHoliday\n";
}

// Teste Paciente
echo "\n--- Teste Paciente ---\n";
$stmt = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1 LIMIT 1");
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($paciente) {
    echo "Paciente: {$paciente['nome']} (ID: {$paciente['id']})\n";
    echo "Dia Fixo: {$paciente['dia_semana_fixo']} (1=Seg, 7=Dom)\n";
    
    $numDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    echo "Dias no mês: $numDiasMes\n";
    
    for ($d = 1; $d <= $numDiasMes; $d++) {
        $dataCheck = "$ano-$mes-" . str_pad($d, 2, '0', STR_PAD_LEFT);
        $diaSemana = date('N', strtotime($dataCheck));
        
        if ($diaSemana == $paciente['dia_semana_fixo']) {
            echo "Dia $d ($dataCheck) é dia de sessão.\n";
            if (HolidayCalculator::isHoliday($dataCheck)) {
                echo " -> É FERIADO! Pular.\n";
            } else {
                echo " -> Válido. Tentando inserir...\n";
            }
        }
    }
} else {
    echo "Nenhum paciente ativo encontrado.\n";
}

echo "</pre>";
?>
