<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../classes/BillingEngine.php';
include '../includes/header.php'; 

// Buscar pacientes ativos
$stmtPacientes = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1 ORDER BY nome ASC");
$pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

// Definição do Mês de Referência
$mesRef = $_GET['mes'] ?? date('Y-m');
$dataRef = DateTime::createFromFormat('Y-m', $mesRef);

// Navegação de Meses
$mesAnterior = (clone $dataRef)->modify('-1 month')->format('Y-m');
$proximoMes = (clone $dataRef)->modify('+1 month')->format('Y-m');

// Formatação para exibição (Português)
$mesesPT = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];
$mesNome = $mesesPT[$dataRef->format('m')] ?? $dataRef->format('F');
$anoRef = $dataRef->format('Y');

// Buscar faturas do mês selecionado
$stmtFaturas = $pdo->prepare("SELECT * FROM faturas WHERE mes_referencia = ?");
$stmtFaturas->execute([$mesRef]);
$faturasMes = [];
$totalPagoMes = 0;
$totalGeradoMes = 0;

foreach ($stmtFaturas->fetchAll(PDO::FETCH_ASSOC) as $f) {
    $faturasMes[$f['paciente_id']] = $f;
    $totalGeradoMes += $f['valor_total'];
    if ($f['status'] == 'pago') {
        $totalPagoMes += $f['valor_total'];
    }
}

// Buscar sessões do mês selecionado (Agenda)
$stmtAgenda = $pdo->prepare("SELECT paciente_id, COUNT(*) as qtd FROM agenda WHERE DATE_FORMAT(data_sessao, '%Y-%m') = ? GROUP BY paciente_id");
$stmtAgenda->execute([$mesRef]);
$sessaoPorPaciente = $stmtAgenda->fetchAll(PDO::FETCH_KEY_PAIR);

// Cálculos para os Cards
$engine = new BillingEngine($pdo);
list($ano, $mes) = explode('-', $mesRef);

$previsaoFaturamento = 0;
$totalSessoesPrevistas = 0;

foreach ($pacientes as $p) {
    $calculo = $engine->calculateMonthlySessions($p['id'], $mes, $ano);
    $previsaoFaturamento += $calculo['valor_total'];
    $totalSessoesPrevistas += $calculo['qtd_sessoes'];
}

$ticketMedio = $totalSessoesPrevistas > 0 ? $previsaoFaturamento / $totalSessoesPrevistas : 0;
$jaFaturado = $totalGeradoMes; 
$pendente = $previsaoFaturamento - $jaFaturado;
$porcentagem = $previsaoFaturamento > 0 ? ($jaFaturado / $previsaoFaturamento) * 100 : 0;
$porcentagem = min(100, $porcentagem);
?>

<!-- Page Header -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-chart-line text-lg"></i>
            </span>
            Financeiro
        </h2>
        <p class="page-subtitle">Gestão de Faturas e Cobranças</p>
    </div>
    <button onclick="location.reload()" class="btn btn-primary">
        <i class="fas fa-sync-alt"></i>
        <span class="hidden sm:inline">Atualizar Status</span>
    </button>
</div>

<!-- Month Navigator -->
<div class="flex items-center justify-center bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden mb-6 w-full sm:w-auto sm:inline-flex">
    <a href="?mes=<?php echo $mesAnterior; ?>" 
       class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors">
        <i class="fas fa-chevron-left"></i>
    </a>
    <div class="px-6 py-3 min-w-[200px] text-center border-x border-gray-100">
        <h2 class="text-lg font-bold text-text-main capitalize flex items-center justify-center gap-2">
            <i class="far fa-calendar-alt text-primary"></i>
            <?php echo "$mesNome de $anoRef"; ?>
        </h2>
    </div>
    <a href="?mes=<?php echo $proximoMes; ?>" 
       class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors">
        <i class="fas fa-chevron-right"></i>
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <!-- Previsão -->
    <div class="stat-card group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Previsão</p>
                <p class="text-2xl md:text-3xl font-extrabold text-primary"><?php echo formatMoney($previsaoFaturamento); ?></p>
                <p class="text-xs text-gray-400 mt-2">Baseado nos contratos</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-pie text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Gerado / Realizado -->
    <div class="stat-card success group">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Gerado / Realizado</p>
                <p class="text-2xl md:text-3xl font-extrabold text-success"><?php echo formatMoney($jaFaturado); ?></p>
                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $porcentagem; ?>%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1"><?php echo number_format($porcentagem, 0); ?>% do previsto</p>
                </div>
            </div>
            <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center text-success group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    <!-- A Faturar -->
    <div class="stat-card warning group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">A Faturar</p>
                <p class="text-2xl md:text-3xl font-extrabold text-warning"><?php echo formatMoney($pendente); ?></p>
                <p class="text-xs text-gray-400 mt-2">Potencial restante</p>
            </div>
            <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center text-warning group-hover:scale-110 transition-transform">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Ticket Médio -->
    <div class="stat-card group" style="border-left-color: #8b5cf6;">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Ticket Médio</p>
                <p class="text-2xl md:text-3xl font-extrabold text-purple-600"><?php echo formatMoney($ticketMedio); ?></p>
                <p class="text-xs text-gray-400 mt-2">Por sessão</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-receipt text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Financial Table -->
<div class="card-shadow overflow-hidden">
    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="text-lg font-bold text-text-main flex items-center gap-2">
            <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-primary text-sm"></i>
            </span>
            Detalhamento Financeiro - <?php echo "$mesNome de $anoRef"; ?>
        </h3>
        <span class="badge badge-info"><?php echo count($pacientes); ?> pacientes ativos</span>
    </div>
    
    <div class="table-responsive">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th class="text-center hidden sm:table-cell">Sessões</th>
                    <th class="text-right hidden md:table-cell">Valor Sessão</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pacientes)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state py-8">
                                <div class="empty-state-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <p class="empty-state-title">Nenhum paciente ativo</p>
                                <p class="empty-state-text">Cadastre pacientes para ver o financeiro</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pacientes as $paciente): 
                        $fatura = $faturasMes[$paciente['id']] ?? null;
                        $status = $fatura ? $fatura['status'] : 'nao_gerado';
                        $qtdSessoes = $sessaoPorPaciente[$paciente['id']] ?? 0;
                        $valorPago = ($status == 'pago') ? $fatura['valor_total'] : 0;
                        $valorTotal = $fatura ? $fatura['valor_total'] : ($qtdSessoes * $paciente['valor_sessao']);
                    ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-md">
                                    <?php if (!empty($paciente['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $paciente['foto'])): ?>
                                        <img src="uploads/pacientes/<?php echo $paciente['foto']; ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-user text-sm"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($paciente['nome']); ?></p>
                                    <p class="text-xs text-gray-400">Vence dia <?php echo $paciente['dia_vencimento']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center hidden sm:table-cell">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg font-bold text-gray-700">
                                <?php echo $qtdSessoes; ?>
                            </span>
                        </td>
                        <td class="text-right text-gray-500 hidden md:table-cell">
                            <?php echo formatMoney($paciente['valor_sessao']); ?>
                        </td>
                        <td class="text-right">
                            <span class="font-bold <?php echo $status == 'pago' ? 'text-success' : 'text-gray-700'; ?>">
                                <?php echo formatMoney($valorTotal); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($status == 'nao_gerado'): ?>
                                <span class="badge badge-neutral">Não Gerado</span>
                            <?php elseif ($status == 'pendente'): ?>
                                <span class="badge badge-warning">Pendente</span>
                            <?php elseif ($status == 'pago'): ?>
                                <span class="badge badge-success">Pago</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($status == 'nao_gerado'): ?>
                                <a href="../scripts/faturamento_diario.php?force_paciente=<?php echo $paciente['id']; ?>" 
                                   target="_blank" 
                                   class="btn btn-primary text-xs py-1.5 px-3"
                                   onclick="return confirm('Gerar fatura para <?php echo htmlspecialchars($paciente['nome']); ?>?')">
                                    <i class="fas fa-plus-circle"></i>
                                    <span class="hidden sm:inline">Gerar</span>
                                </a>
                            <?php elseif ($status == 'pendente' && !empty($fatura['link_pagamento'])): ?>
                                <a href="<?php echo $fatura['link_pagamento']; ?>" 
                                   target="_blank" 
                                   class="btn btn-secondary text-xs py-1.5 px-3">
                                    <i class="fas fa-link"></i>
                                    <span class="hidden sm:inline">Link</span>
                                </a>
                            <?php elseif ($status == 'pago'): ?>
                                <span class="text-success text-sm">
                                    <i class="fas fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
