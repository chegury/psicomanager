<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../classes/BillingEngine.php';
include '../includes/header.php'; 

// 0. Filtro de Mês
$mesRef = $_GET['mes_ref'] ?? date('Y-m');
list($ano, $mes) = explode('-', $mesRef);

// Instancia Engine para cálculos
$engine = new BillingEngine($pdo);

// 1. Previsão de Faturamento (Forecast)
$stmtPacientes = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1 ORDER BY nome ASC");
$pacientesAtivos = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

$previsaoFaturamento = 0;
$listaFinanceira = [];

foreach ($pacientesAtivos as $paciente) {
    $calculo = $engine->calculateMonthlySessions($paciente['id'], $mes, $ano);
    
    $previsaoFaturamento += $calculo['valor_total'];
    
    $listaFinanceira[] = [
        'nome' => $paciente['nome'],
        'foto' => $paciente['foto'] ?? null,
        'qtd_sessoes' => $calculo['qtd_sessoes'],
        'valor_previsto' => $calculo['valor_total'],
        'dia_vencimento' => $paciente['dia_vencimento'],
        'dia_semana' => $paciente['dia_semana_fixo'],
        'horario' => $paciente['horario_fixo']
    ];
}

// 2. Já Faturado (Realizado) - APENAS faturas PAGAS
$stmtFaturado = $pdo->prepare("SELECT SUM(valor_total) FROM faturas WHERE mes_referencia = ? AND status = 'pago'");
$stmtFaturado->execute([$mesRef]);
$jaFaturado = $stmtFaturado->fetchColumn() ?: 0;

// 3. Calendário do Mês (Agenda) - Apenas pacientes ativos
$stmtAgenda = $pdo->prepare("
    SELECT a.*, p.nome, p.foto 
    FROM agenda a 
    JOIN pacientes p ON a.paciente_id = p.id 
    WHERE DATE_FORMAT(a.data_sessao, '%Y-%m') = ? 
    AND p.ativo = 1
    ORDER BY a.data_sessao ASC
");
$stmtAgenda->execute([$mesRef]);
$agendaMes = $stmtAgenda->fetchAll(PDO::FETCH_ASSOC);

// Agrupar agenda por dia
$agendaPorDia = [];
foreach ($agendaMes as $evento) {
    $dia = date('d', strtotime($evento['data_sessao']));
    $agendaPorDia[$dia][] = $evento;
}

// 4. Ticket Médio
$totalSessoesPrevistas = array_sum(array_column($listaFinanceira, 'qtd_sessoes'));
$ticketMedio = $totalSessoesPrevistas > 0 ? $previsaoFaturamento / $totalSessoesPrevistas : 0;

// Helper de Tradução de Mês
$mesesPT = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];
$mesNome = $mesesPT[date('m', strtotime($mesRef))];
$anoRef = date('Y', strtotime($mesRef));

// Calcular porcentagem
$porcentagem = $previsaoFaturamento > 0 ? ($jaFaturado / $previsaoFaturamento) * 100 : 0;
$porcentagem = min(100, $porcentagem);
?>

<!-- Page Header -->
<div class="page-header mb-8">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-chart-pie text-lg"></i>
            </span>
            Dashboard Financeiro
        </h2>
        <p class="page-subtitle">Visão completa de <?php echo "$mesNome de $anoRef"; ?></p>
    </div>
    
    <form method="GET" class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-md border border-gray-100">
        <label for="mes_ref" class="text-sm font-bold text-gray-600 whitespace-nowrap hidden sm:block">
            <i class="fas fa-calendar-alt mr-1 text-primary"></i> Mês:
        </label>
        <input type="month" name="mes_ref" id="mes_ref" value="<?php echo $mesRef; ?>" 
               class="border-0 bg-gray-50 rounded-lg text-sm font-medium focus:ring-2 focus:ring-primary/30 px-3 py-2"
               onchange="this.form.submit()">
    </form>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <!-- Previsão -->
    <div class="stat-card group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Previsão</p>
                <p class="text-2xl md:text-3xl font-extrabold text-primary"><?php echo formatMoney($previsaoFaturamento); ?></p>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                    <i class="fas fa-file-contract"></i> Baseado nos contratos
                </p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Já Faturado -->
    <div class="stat-card success group">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Realizado</p>
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

    <!-- Pendente / A Faturar -->
    <div class="stat-card warning group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">A Faturar</p>
                <p class="text-2xl md:text-3xl font-extrabold text-warning"><?php echo formatMoney($previsaoFaturamento - $jaFaturado); ?></p>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                    <i class="fas fa-hourglass-half"></i> Potencial restante
                </p>
            </div>
            <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center text-warning group-hover:scale-110 transition-transform">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Ticket Médio -->
    <div class="stat-card secondary group" style="border-left-color: #8b5cf6;">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Ticket Médio</p>
                <p class="text-2xl md:text-3xl font-extrabold text-purple-600"><?php echo formatMoney($ticketMedio); ?></p>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                    <i class="fas fa-tag"></i> Por sessão
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-receipt text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 md:gap-8">
    <!-- Lista Financeira por Paciente -->
    <div class="card-shadow overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <h3 class="text-lg font-bold text-text-main flex items-center gap-2">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-primary text-sm"></i>
                </span>
                Detalhamento por Paciente
            </h3>
            <span class="badge badge-info"><?php echo count($listaFinanceira); ?> pacientes</span>
        </div>
        <div class="table-responsive max-h-96 overflow-y-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th class="hidden sm:table-cell">Horário</th>
                        <th class="text-center">Sessões</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaFinanceira)): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state py-8">
                                <div class="empty-state-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <p class="empty-state-title">Nenhum paciente</p>
                                <p class="empty-state-text">Cadastre seu primeiro paciente para ver o faturamento</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($listaFinanceira as $item): ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-md">
                                    <?php if (!empty($item['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $item['foto'])): ?>
                                        <img src="uploads/pacientes/<?php echo $item['foto']; ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-user text-sm"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($item['nome']); ?></p>
                                    <p class="text-xs text-gray-400">Vence dia <?php echo $item['dia_vencimento']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell">
                            <span class="font-semibold text-primary text-sm"><?php echo getDayName($item['dia_semana']); ?></span>
                            <span class="text-xs text-gray-500 block"><?php echo substr($item['horario'], 0, 5); ?></span>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg font-bold text-gray-700">
                                <?php echo $item['qtd_sessoes']; ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="font-bold text-gray-800"><?php echo formatMoney($item['valor_previsto']); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Calendário Visual -->
    <div class="card-shadow overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <h3 class="text-lg font-bold text-text-main flex items-center gap-2">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-primary text-sm"></i>
                </span>
                Agenda: <?php echo "$mesNome de $anoRef"; ?>
            </h3>
            <a href="agenda.php" class="text-primary hover:text-primary-dark text-sm font-semibold flex items-center gap-1 transition-colors">
                Ver todos <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        <div class="p-4 max-h-96 overflow-y-auto">
            <?php if (empty($agendaPorDia)): ?>
                <div class="empty-state py-8">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <p class="empty-state-title">Sem agendamentos</p>
                    <p class="empty-state-text">Nenhum agendamento para este mês</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($agendaPorDia as $dia => $eventos): ?>
                        <div class="flex gap-4">
                            <!-- Indicador do Dia -->
                            <div class="flex-shrink-0 w-14 text-center">
                                <span class="block text-2xl font-extrabold text-primary"><?php echo $dia; ?></span>
                                <span class="block text-xs text-gray-400 uppercase font-semibold"><?php echo getDayName(date('N', strtotime($eventos[0]['data_sessao']))); ?></span>
                            </div>
                            
                            <!-- Lista de Eventos -->
                            <div class="flex-1 space-y-2 border-l-2 border-primary/20 pl-4">
                                <?php foreach ($eventos as $ev): ?>
                                    <div class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-white p-3 rounded-xl border border-gray-100 hover:border-primary/30 hover:shadow-sm transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div class="avatar avatar-sm ring-2 ring-white shadow">
                                                <?php if (!empty($ev['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $ev['foto'])): ?>
                                                    <img src="uploads/pacientes/<?php echo $ev['foto']; ?>" alt="<?php echo htmlspecialchars($ev['nome']); ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-user text-xs"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($ev['nome']); ?></p>
                                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                                    <i class="far fa-clock"></i>
                                                    <?php echo date('H:i', strtotime($ev['data_sessao'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php if ($ev['link_meet']): ?>
                                            <a href="<?php echo $ev['link_meet']; ?>" target="_blank" 
                                               class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-all opacity-0 group-hover:opacity-100">
                                                <i class="fas fa-video text-sm"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
