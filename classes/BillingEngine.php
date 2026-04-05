<?php

require_once __DIR__ . '/AsaasClient.php';
require_once __DIR__ . '/WhatsAppClient.php';
require_once __DIR__ . '/HolidayCalculator.php';

class BillingEngine {
    private $pdo;
    private $asaas;
    private $whatsapp;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->asaas = new AsaasClient();
        $this->whatsapp = new WhatsAppClient();
    }



    /**
     * Calcula o valor total do pacote no mês
     * Considera frequência (semanal/quinzenal) e semana_inicio
     */
    public function calculateMonthlySessions($pacienteId, $mes, $ano) {
        $stmt = $this->pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
        $stmt->execute([$pacienteId]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paciente || !$paciente['ativo']) {
            return ['qtd_sessoes' => 0, 'valor_total' => 0];
        }

        $diaSemanaFixo = $paciente['dia_semana_fixo'];
        $frequencia = $paciente['frequencia'] ?? 'semanal';
        $semanaInicio = $paciente['semana_inicio'] ?? 1; // 1 = ímpar, 0 = par
        
        $qtdSessoes = 0;
        $numDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        
        // Contador de semanas com o dia fixo encontrado
        $semanaDoMes = 0;

        for ($d = 1; $d <= $numDiasMes; $d++) {
            $dataCheck = "$ano-$mes-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            
            // Verifica dia da semana
            if (date('N', strtotime($dataCheck)) == $diaSemanaFixo) {
                $semanaDoMes++;
                
                // Verifica se é feriado
                if (HolidayCalculator::isHoliday($dataCheck)) {
                    continue;
                }
                
                // Se semanal, conta todas
                if ($frequencia === 'semanal') {
                    $qtdSessoes++;
                } 
                // Se quinzenal, verifica se é semana par ou ímpar
                else if ($frequencia === 'quinzenal') {
                    $ehSemanaImpar = ($semanaDoMes % 2 == 1);
                    
                    // semana_inicio = 1 → sessão em semanas ímpares (1ª, 3ª, 5ª)
                    // semana_inicio = 0 → sessão em semanas pares (2ª, 4ª)
                    if (($semanaInicio == 1 && $ehSemanaImpar) || 
                        ($semanaInicio == 0 && !$ehSemanaImpar)) {
                        $qtdSessoes++;
                    }
                }
            }
        }

        $valorTotal = $qtdSessoes * $paciente['valor_sessao'];

        return [
            'qtd_sessoes' => $qtdSessoes,
            'valor_total' => $valorTotal
        ];
    }

    public function triggerApprovalAlert($pacienteId, $valorTotal, $qtdSessoes, $mesReferencia) {
        // 1. Cria rascunho da fatura (ou atualiza se já existir pendente/draft)
        // Verifica se já existe fatura para este mês
        $stmtCheck = $this->pdo->prepare("SELECT id FROM faturas WHERE paciente_id = ? AND mes_referencia = ?");
        $stmtCheck->execute([$pacienteId, $mesReferencia]);
        $faturaId = $stmtCheck->fetchColumn();

        if (!$faturaId) {
            $stmtInsert = $this->pdo->prepare("INSERT INTO faturas (paciente_id, mes_referencia, valor_total, qtd_sessoes, status) VALUES (?, ?, ?, ?, 'pendente')");
            $stmtInsert->execute([$pacienteId, $mesReferencia, $valorTotal, $qtdSessoes]);
            $faturaId = $this->pdo->lastInsertId();
        } else {
            // Atualiza valores caso tenha mudado (recalculo)
            $stmtUpdate = $this->pdo->prepare("UPDATE faturas SET valor_total = ?, qtd_sessoes = ? WHERE id = ?");
            $stmtUpdate->execute([$valorTotal, $qtdSessoes, $faturaId]);
        }

        // 2. Monta mensagem e link
        // URL base deve ser configurada ou detectada. Assumindo localhost ou domínio configurado.
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/PsicoinspireManager';
        $linkConfirmacao = "$baseUrl/public/confirm_billing.php?fat_id=$faturaId";
        
        $stmtP = $this->pdo->prepare("SELECT nome FROM pacientes WHERE id = ?");
        $stmtP->execute([$pacienteId]);
        $nomePaciente = $stmtP->fetchColumn();

        $mensagem = "🔔 *Aprovação de Faturamento*\n\n";
        $mensagem .= "Paciente: *$nomePaciente*\n";
        $mensagem .= "Ref: $mesReferencia\n";
        $mensagem .= "Sessões: $qtdSessoes\n";
        $mensagem .= "Valor: R$ " . number_format($valorTotal, 2, ',', '.') . "\n\n";
        $mensagem .= "Clique para gerar a cobrança:\n$linkConfirmacao";

        // Envia para o admin (Dra.) - Número deve estar no .env
        $adminPhone = $_ENV['ADMIN_PHONE'] ?? '5511999999999'; 
        $this->whatsapp->sendMessage($adminPhone, $mensagem);

        return $faturaId;
    }

    /**
     * Dispara a cobrança após a confirmação
     */
    public function executeAsaasBilling($faturaId) {
        // 1. Busca dados da fatura e paciente
        $stmt = $this->pdo->prepare("
            SELECT f.*, p.nome, p.cpf, p.email, p.whatsapp, p.dia_vencimento 
            FROM faturas f 
            JOIN pacientes p ON f.paciente_id = p.id 
            WHERE f.id = ?
        ");
        $stmt->execute([$faturaId]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            throw new Exception("Fatura não encontrada.");
        }

        if ($dados['status'] == 'pago') {
            throw new Exception("Fatura já está paga.");
        }

        if (!empty($dados['asaas_id'])) {
            // Já gerada, apenas retorna ou reenvia link
            return [
                'success' => true, 
                'asaas_id' => $dados['asaas_id'], 
                'link' => $dados['link_pagamento'],
                'message' => 'Cobrança já existia.'
            ];
        }

        // 2. Cria cobrança no Asaas
        // Primeiro garante que o cliente existe no Asaas
        $customerId = $this->asaas->createCustomer(
            $dados['nome'], 
            $dados['cpf'], 
            $dados['email'], 
            $dados['whatsapp']
        );

        // Calcula vencimento (dia fixo no mês atual ou próximo, dependendo da regra)
        // Assumindo vencimento no mês da referência ou mês seguinte?
        // O user disse "dia_vencimento" na tabela pacientes.
        // Se estamos gerando hoje, e o vencimento já passou, talvez seja para o próximo mês?
        // Regra simplificada: Vencimento = Ano-MesAtual-DiaVencimento. Se for passado, +1 mês?
        // O user disse "calcula valor do pacote no mês". Geralmente cobra-se antecipado ou postecipado.
        // Vou usar a data de vencimento baseada no mês de referência da fatura, se possível, ou mês atual.
        // Se mes_referencia é "2023-11", e dia_vencimento é 10. Vencimento = 2023-11-10.
        
        $vencimento = $dados['mes_referencia'] . '-' . str_pad($dados['dia_vencimento'], 2, '0', STR_PAD_LEFT);
        
        // Se vencimento já passou (ex: gerando dia 20 para vencimento dia 10), Asaas não aceita data passada para Boleto.
        // Ajuste: Se data passada, joga para hoje + 3 dias ou mantém se for apenas registro.
        if (strtotime($vencimento) < time()) {
            $vencimento = date('Y-m-d', strtotime('+3 days'));
        }

        $billingData = [
            'value' => $dados['valor_total'],
            'dueDate' => $vencimento,
            'description' => "Sessões de Psicologia - Ref: " . $dados['mes_referencia'],
            'externalReference' => $faturaId
        ];

        $cobranca = $this->asaas->createPayment(['id' => $customerId], $billingData);

        // 3. Atualiza fatura
        $asaasId = $cobranca['id'];
        $linkPagamento = $cobranca['invoiceUrl'] ?? $cobranca['bankSlipUrl'] ?? 'Link não gerado';

        $stmtUpdate = $this->pdo->prepare("UPDATE faturas SET asaas_id = ?, link_pagamento = ? WHERE id = ?");
        $stmtUpdate->execute([$asaasId, $linkPagamento, $faturaId]);

        // 4. Envia link para o paciente
        $msgPaciente = "Olá {$dados['nome']}, sua fatura do mês {$dados['mes_referencia']} está disponível.\n";
        $msgPaciente .= "Valor: R$ " . number_format($dados['valor_total'], 2, ',', '.') . "\n";
        $msgPaciente .= "Link para pagamento: $linkPagamento";
        
        $this->whatsapp->sendMessage($dados['whatsapp'], $msgPaciente);

        return [
            'success' => true,
            'asaas_id' => $asaasId,
            'link' => $linkPagamento
        ];
    }
}
