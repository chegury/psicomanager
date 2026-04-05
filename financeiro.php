<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/BillingEngine.php';
include __DIR__ . '/includes/header.php'; 

$mesRef = $_GET['mes_ref'] ?? date('Y-m');
list($ano, $mes) = explode('-', $mesRef);

$engine = new BillingEngine($pdo);

// Gerar cobranças em lote
$batchResult = null;
if (isset($_POST['gerar_todas'])) {
    $stmtP = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1");
    $pacAtivos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    $geradas = 0; $erros = 0;
    
    foreach ($pacAtivos as $pac) {
        $calculo = $engine->calculateMonthlySessions($pac['id'], $mes, $ano);
        if ($calculo['valor_total'] > 0) {
            // Verificar se já existe fatura para este mês
            $chk = $pdo->prepare("SELECT id FROM faturas WHERE paciente_id = ? AND mes_referencia = ?");
            $chk->execute([$pac['id'], $mesRef]);
            if (!$chk->fetch()) {
                try {
                    $diaVenc = $pac['dia_vencimento'];
                    $dataVenc = "$ano-$mes-" . str_pad($diaVenc, 2, '0', STR_PAD_LEFT);
                    
                    $ins = $pdo->prepare("INSERT INTO faturas (paciente_id, mes_referencia, valor_total, qtd_sessoes, status) VALUES (?, ?, ?, ?, 'pendente')");
                    $ins->execute([$pac['id'], $mesRef, $calculo['valor_total'], $calculo['qtd_sessoes']]);
                    $geradas++;
                } catch (Exception $e) { $erros++; }
            }
        }
    }
    $batchResult = ['geradas' => $geradas, 'erros' => $erros];
}

// Dados dos pacientes
$stmtPacientes = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1 ORDER BY nome ASC");
$pacientesAtivos = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

$listaFinanceira = [];
$totalPrevisao = 0;
$totalFaturado = 0;

foreach ($pacientesAtivos as $paciente) {
    $calculo = $engine->calculateMonthlySessions($paciente['id'], $mes, $ano);
    
    // Verificar se há fatura gerada
    $stmtFat = $pdo->prepare("SELECT * FROM faturas WHERE paciente_id = ? AND mes_referencia = ?");
    $stmtFat->execute([$paciente['id'], $mesRef]);
    $fatura = $stmtFat->fetch(PDO::FETCH_ASSOC);
    
    $statusFatura = $fatura ? $fatura['status'] : 'não gerada';
    if ($fatura && $fatura['status'] === 'pago') $totalFaturado += $fatura['valor_total'];
    
    $totalPrevisao += $calculo['valor_total'];
    
    $listaFinanceira[] = [
        'id' => $paciente['id'],
        'nome' => $paciente['nome'],
        'foto' => $paciente['foto'] ?? null,
        'whatsapp' => $paciente['whatsapp'],
        'qtd_sessoes' => $calculo['qtd_sessoes'],
        'valor_previsto' => $calculo['valor_total'],
        'fatura' => $fatura,
        'status' => $statusFatura
    ];
}

$mesesPT = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
?>

<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-chart-line text-lg"></i></span>
            Financeiro
        </h2>
        <p class="page-subtitle">Controle financeiro mensal</p>
    </div>
    <div class="flex gap-2">
        <form method="POST" class="inline">
            <button type="submit" name="gerar_todas" class="btn btn-primary" onclick="return confirm('Gerar cobranças para todos os pacientes deste mês?')">
                <i class="fas fa-bolt"></i> <span class="hidden sm:inline">Gerar Todas Cobranças</span>
            </button>
        </form>
    </div>
</div>

<?php if ($batchResult): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in">
    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center"><i class="fas fa-check text-success"></i></div>
    <p class="font-medium text-green-800"><?php echo $batchResult['geradas']; ?> cobranças geradas! <?php echo $batchResult['erros'] > 0 ? "({$batchResult['erros']} erros)" : ''; ?></p>
</div>
<?php endif; ?>

<div class="flex items-center justify-center bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden mb-6 w-full sm:w-auto sm:inline-flex">
    <a href="?mes_ref=<?php echo date('Y-m', strtotime($mesRef . '-01 -1 month')); ?>" class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors"><i class="fas fa-chevron-left"></i></a>
    <div class="px-6 py-3 min-w-[200px] text-center border-x border-gray-100">
        <h2 class="text-lg font-bold text-text-main"><?php echo ($mesesPT[$mes] ?? $mes) . ' / ' . $ano; ?></h2>
    </div>
    <a href="?mes_ref=<?php echo date('Y-m', strtotime($mesRef . '-01 +1 month')); ?>" class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors"><i class="fas fa-chevron-right"></i></a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card"><div class="flex items-start justify-between"><div><p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Previsão</p><p class="text-2xl font-extrabold text-primary"><?php echo formatMoney($totalPrevisao); ?></p></div><div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary"><i class="fas fa-chart-line text-xl"></i></div></div></div>
    <div class="stat-card success"><div class="flex items-start justify-between"><div><p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Faturado</p><p class="text-2xl font-extrabold text-success"><?php echo formatMoney($totalFaturado); ?></p></div><div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center text-success"><i class="fas fa-check-circle text-xl"></i></div></div></div>
    <div class="stat-card warning"><div class="flex items-start justify-between"><div><p class="text-xs text-text-light uppercase font-bold tracking-wider mb-1">Pendente</p><p class="text-2xl font-extrabold text-warning"><?php echo formatMoney($totalPrevisao - $totalFaturado); ?></p></div><div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center text-warning"><i class="fas fa-clock text-xl"></i></div></div></div>
</div>

<div class="card-shadow overflow-hidden">
    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white"><h3 class="text-lg font-bold text-text-main flex items-center gap-2"><span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-users text-primary text-sm"></i></span> Pacientes - <?php echo ($mesesPT[$mes] ?? $mes) . '/' . $ano; ?></h3></div>
    <div class="table-responsive">
        <table class="table-premium">
            <thead><tr><th>Paciente</th><th class="text-center">Sessões</th><th class="text-right">Valor</th><th class="text-center">Status</th></tr></thead>
            <tbody>
                <?php foreach ($listaFinanceira as $item): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar avatar-md">
                                <?php if (!empty($item['foto']) && file_exists(__DIR__ . '/public/uploads/pacientes/' . $item['foto'])): ?>
                                    <img src="public/uploads/pacientes/<?php echo $item['foto']; ?>" alt="">
                                <?php else: ?><i class="fas fa-user text-sm"></i><?php endif; ?>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($item['nome']); ?></p>
                        </div>
                    </td>
                    <td class="text-center"><span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-lg font-bold text-gray-700"><?php echo $item['qtd_sessoes']; ?></span></td>
                    <td class="text-right"><span class="font-bold text-gray-800"><?php echo formatMoney($item['valor_previsto']); ?></span></td>
                    <td class="text-center">
                        <?php
                        $badgeClass = match($item['status']) {
                            'pago' => 'badge-success',
                            'pendente' => 'badge-warning',
                            'vencido' => 'badge-danger',
                            default => 'badge-neutral'
                        };
                        $statusLabel = match($item['status']) {
                            'pago' => 'Pago',
                            'pendente' => 'Pendente',
                            'vencido' => 'Vencido',
                            default => 'Não Gerada'
                        };
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
