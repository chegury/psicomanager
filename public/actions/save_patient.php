<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Receber e limpar dados
        $nome = $_POST['nome'];
        $cpf = cleanNumber($_POST['cpf']);
        $whatsapp = cleanNumber($_POST['whatsapp']);
        $email = $_POST['email'];
        $dia_semana_fixo = $_POST['dia_semana_fixo'];
        $horario_fixo = $_POST['horario_fixo'];
        
        // Converter valor de R$ 1.200,00 para 1200.00
        $valor_sessao = str_replace('.', '', $_POST['valor_sessao']);
        $valor_sessao = str_replace(',', '.', $valor_sessao);
        
        $dia_vencimento = $_POST['dia_vencimento'];
        $dias_antecedencia = $_POST['dias_antecedencia'];

        // Processar Frequência
        $freqCombo = $_POST['frequencia_combo'] ?? 'semanal';
        $frequencia = 'semanal';
        $semanaInicio = 1;

        if ($freqCombo === 'quinzenal_impar') {
            $frequencia = 'quinzenal';
            $semanaInicio = 1;
        } elseif ($freqCombo === 'quinzenal_par') {
            $frequencia = 'quinzenal';
            $semanaInicio = 0;
        }

        // Validação básica
        if (empty($nome) || empty($cpf) || empty($whatsapp)) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        // Inserir no banco
        $stmt = $pdo->prepare("INSERT INTO pacientes (nome, cpf, whatsapp, email, dia_semana_fixo, horario_fixo, valor_sessao, dia_vencimento, dias_antecedencia, frequencia, semana_inicio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $cpf, $whatsapp, $email, $dia_semana_fixo, $horario_fixo, $valor_sessao, $dia_vencimento, $dias_antecedencia, $frequencia, $semanaInicio]);

        $paciente_id = $pdo->lastInsertId();

        // Gerar agenda para o mês atual e próximo mês
        require_once '../../classes/CalendarSyncService.php';
        $calendarService = new CalendarSyncService($pdo);
        
        $mesAtual = date('m');
        $anoAtual = date('Y');
        $proximoMes = date('m', strtotime('+1 month'));
        $proximoAno = date('Y', strtotime('+1 month'));
        
        // Gera agenda do mês atual
        $calendarService->generateAndSync($paciente_id, 0, $mesAtual, $anoAtual);
        
        // Gera agenda do próximo mês também
        $calendarService->generateAndSync($paciente_id, 0, $proximoMes, $proximoAno);
        
        // Redirecionar para o dashboard com mensagem de sucesso
        header("Location: ../index.php?msg=Paciente cadastrado e agenda gerada com sucesso!");
        exit;

    } catch (Exception $e) {
        // Em caso de erro, voltar para o formulário (ideal seria manter os dados preenchidos)
        echo "<script>alert('Erro: " . $e->getMessage() . "'); window.history.back();</script>";
    }
} else {
    header("Location: ../cadastro.php");
    exit;
}
?>
