<?php

require_once __DIR__ . '/GoogleClientWrapper.php';
require_once __DIR__ . '/WhatsAppClient.php';
require_once __DIR__ . '/HolidayCalculator.php';

class CalendarSyncService {
    private $pdo;
    private $google;
    private $whatsapp;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->google = new GoogleClientWrapper();
        $this->whatsapp = new WhatsAppClient();
    }

    /**
     * Cria os eventos no Google e na base local
     * Considera frequência (semanal/quinzenal) igual ao BillingEngine
     */
    public function generateAndSync($pacienteId, $qtdSessoes, $mes, $ano) {
        $stmt = $this->pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
        $stmt->execute([$pacienteId]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paciente || !$paciente['ativo']) {
            return 0;
        }

        $diaSemanaFixo = $paciente['dia_semana_fixo'];
        $horarioFixo = $paciente['horario_fixo']; // Formato HH:MM:SS
        $frequencia = $paciente['frequencia'] ?? 'semanal';
        $semanaInicio = $paciente['semana_inicio'] ?? 1; // 1 = ímpar, 0 = par
        
        $sessoesGeradas = 0;
        $numDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        
        // Contador de semanas com o dia fixo encontrado (mesmo do BillingEngine)
        $semanaDoMes = 0;

        for ($d = 1; $d <= $numDiasMes; $d++) {
            $dataCheck = "$ano-$mes-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            
            // Verifica dia da semana
            if (date('N', strtotime($dataCheck)) == $diaSemanaFixo) {
                $semanaDoMes++;
                
                // Lógica Quinzenal - mesma do BillingEngine
                if ($frequencia === 'quinzenal') {
                    $ehSemanaImpar = ($semanaDoMes % 2 == 1);
                    
                    // semana_inicio = 1 → sessão em semanas ímpares (1ª, 3ª, 5ª)
                    // semana_inicio = 0 → sessão em semanas pares (2ª, 4ª)
                    if (($semanaInicio == 1 && !$ehSemanaImpar) || 
                        ($semanaInicio == 0 && $ehSemanaImpar)) {
                        continue; // Pula se a paridade não coincidir
                    }
                }

                // Verifica feriado
                if (HolidayCalculator::isHoliday($dataCheck)) {
                    continue; // Pula feriados
                }

                // Verifica se já existe agendamento para este dia/paciente
                $stmtCheck = $this->pdo->prepare("SELECT id FROM agenda WHERE paciente_id = ? AND DATE(data_sessao) = ?");
                $stmtCheck->execute([$pacienteId, $dataCheck]);
                
                if ($stmtCheck->rowCount() == 0) {
                    // Cria evento no Google
                    $inicio = $dataCheck . 'T' . $horarioFixo;
                    // Assume 50 min de sessão
                    $fim = date('Y-m-d\TH:i:s', strtotime($inicio . ' +50 minutes'));
                    
                    $evento = $this->google->createEvent(
                        "Sessão: " . $paciente['nome'],
                        "Sessão de Terapia",
                        $inicio,
                        $fim,
                        $paciente['email']
                    );

                    $linkMeet = $evento['link'] ?? 'Pendente';

                    // Salva no Banco
                    $stmtInsert = $this->pdo->prepare("INSERT INTO agenda (paciente_id, data_sessao, link_meet, status) VALUES (?, ?, ?, 'agendado')");
                    $stmtInsert->execute([$pacienteId, "$dataCheck $horarioFixo", $linkMeet]);
                    
                    $sessoesGeradas++;
                }
            }
        }
        
        return $sessoesGeradas;
    }

    /**
     * Executa o envio de lembretes
     */
    public function sendReminderCron($currentTime = 'now') {
        // Consulta agenda onde data_sessao está 15 minutos à frente
        // Intervalo de busca: entre agora e agora + 20 min (margem de segurança)
        // E notificacao_enviada = 0
        
        $sql = "SELECT a.*, p.nome, p.whatsapp 
                FROM agenda a 
                JOIN pacientes p ON a.paciente_id = p.id 
                WHERE a.status = 'agendado' 
                AND a.notificacao_enviada = 0 
                AND a.data_sessao BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 20 MINUTE)";
        
        $stmt = $this->pdo->query($sql);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $enviados = 0;

        foreach ($agendamentos as $agenda) {
            $horaSessao = date('H:i', strtotime($agenda['data_sessao']));
            $link = $agenda['link_meet'];
            
            $msg = "Olá {$agenda['nome']}, sua sessão começa às $horaSessao.\n";
            $msg .= "Link para acesso: $link";
            
            $this->whatsapp->sendMessage($agenda['whatsapp'], $msg);
            
            // Atualiza status
            $stmtUpdate = $this->pdo->prepare("UPDATE agenda SET notificacao_enviada = 1 WHERE id = ?");
            $stmtUpdate->execute([$agenda['id']]);
            
            $enviados++;
        }
        
        return $enviados;
    }
}
