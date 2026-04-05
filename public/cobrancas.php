<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php'; 

$statusFilter = $_GET['status'] ?? '';
$mesFilter = $_GET['mes'] ?? '';

$sql = "SELECT f.*, p.nome, p.foto FROM faturas f JOIN pacientes p ON f.paciente_id = p.id WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND f.status = ?";
    $params[] = $statusFilter;
}
if ($mesFilter) {
    $sql .= " AND f.mes_referencia = ?";
    $params[] = $mesFilter;
}

$sql .= " ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$faturas = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-file-invoice-dollar text-lg"></i>
            </span>
            Cobranças
        </h2>
        <p class="page-subtitle">Histórico completo de faturas</p>
    </div>
    <a href="../scripts/sync_asaas_status.php" target="_blank" class="btn btn-primary">
        <i class="fas fa-sync-alt"></i>
        <span class="hidden sm:inline">Sincronizar com Asaas</span>
        <span class="sm:hidden">Sync</span>
    </a>
</div>

<!-- Filters -->
<div class="card-shadow p-4 md:p-5 mb-6">
    <form class="flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1 sm:flex-initial sm:w-40">
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Status</label>
            <select name="status" class="form-input py-2.5 text-sm">
                <option value="">Todos</option>
                <option value="pendente" <?php echo $statusFilter == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                <option value="pago" <?php echo $statusFilter == 'pago' ? 'selected' : ''; ?>>Pago</option>
            </select>
        </div>
        
        <div class="flex-1 sm:flex-initial sm:w-48">
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Mês Referência</label>
            <input type="month" name="mes" value="<?php echo $mesFilter; ?>" class="form-input py-2.5 text-sm">
        </div>
        
        <div class="flex gap-2 w-full sm:w-auto">
            <button type="submit" class="btn btn-primary flex-1 sm:flex-initial justify-center">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
            <?php if ($statusFilter || $mesFilter): ?>
                <a href="cobrancas.php" class="btn btn-secondary flex-1 sm:flex-initial justify-center">
                    <i class="fas fa-times"></i>
                    Limpar
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card-shadow overflow-hidden">
    <?php if (empty($faturas)): ?>
        <div class="empty-state py-16">
            <div class="empty-state-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <p class="empty-state-title">Nenhuma fatura encontrada</p>
            <p class="empty-state-text">Não há cobranças que correspondam aos filtros selecionados</p>
        </div>
    <?php else: ?>
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">
                <i class="fas fa-list mr-2 text-primary"></i>
                <?php echo count($faturas); ?> fatura(s) encontrada(s)
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th class="hidden sm:table-cell">Referência</th>
                        <th class="text-right">Valor</th>
                        <th class="text-center">Status</th>
                        <th class="text-center hidden md:table-cell">Pagamento</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faturas as $f): ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm">
                                    <?php if (!empty($f['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $f['foto'])): ?>
                                        <img src="uploads/pacientes/<?php echo $f['foto']; ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-user text-xs"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($f['nome']); ?></p>
                                    <p class="text-xs text-gray-400 sm:hidden"><?php echo $f['mes_referencia']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-600 hidden sm:table-cell">
                            <?php 
                                $refParts = explode('-', $f['mes_referencia']);
                                $mesesPT = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
                                echo ($mesesPT[$refParts[1]] ?? '') . '/' . $refParts[0];
                            ?>
                        </td>
                        <td class="text-right">
                            <span class="font-bold text-gray-800"><?php echo formatMoney($f['valor_total']); ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($f['status'] == 'pendente'): ?>
                                <span class="badge badge-warning">Pendente</span>
                            <?php elseif ($f['status'] == 'pago'): ?>
                                <span class="badge badge-success">Pago</span>
                            <?php elseif ($f['status'] == 'vencido'): ?>
                                <span class="badge badge-danger">Vencido</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center text-gray-500 text-sm hidden md:table-cell">
                            <?php echo $f['data_pagamento'] ? date('d/m/Y', strtotime($f['data_pagamento'])) : '-'; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($f['link_pagamento']): ?>
                                <a href="<?php echo $f['link_pagamento']; ?>" target="_blank" 
                                   class="inline-flex items-center gap-1 text-primary hover:text-primary-dark font-semibold text-sm transition-colors">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span class="hidden sm:inline">Link</span>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
