<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php'; 

// Filtro de status
$filtroStatus = $_GET['status'] ?? 'todos';
$sql = "SELECT f.*, p.nome, p.whatsapp FROM faturas f JOIN pacientes p ON f.paciente_id = p.id";
if ($filtroStatus !== 'todos') { $sql .= " WHERE f.status = ?"; }
$sql .= " ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($filtroStatus !== 'todos') { $stmt->execute([$filtroStatus]); }
else { $stmt->execute(); }
$faturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sincronizar status de faturas Asaas
$syncError = null;
$syncedCount = 0;
if (isset($_GET['sync'])) {
    try {
        require_once __DIR__ . '/classes/EnvLoader.php';
        EnvLoader::load(__DIR__ . '/.env');
        
        $apiKey = $_ENV['ASAAS_API_KEY'] ?? getenv('ASAAS_API_KEY') ?? '';
        $apiUrl = $_ENV['ASAAS_URL'] ?? getenv('ASAAS_URL') ?? 'https://sandbox.asaas.com/api/v3';
        
        if (empty($apiKey) || $apiKey === 'sua_api_key_sandbox_aqui') {
            $syncError = 'Chave API Asaas não configurada. Configure em .env → ASAAS_API_KEY';
        } else {
            foreach ($faturas as $f) {
                if (!empty($f['asaas_id']) && $f['status'] !== 'pago') {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => rtrim($apiUrl, '/') . '/payments/' . $f['asaas_id'],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'access_token: ' . $apiKey, 'User-Agent: PsiManager/1.0'],
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_TIMEOUT => 15
                    ]);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlErr = curl_error($ch);
                    curl_close($ch);
                    
                    if ($curlErr) {
                        $syncError = "Erro de conexão com Asaas: $curlErr";
                        break;
                    }
                    
                    if ($httpCode >= 200 && $httpCode < 300) {
                        $data = json_decode($response, true);
                        if (isset($data['status'])) {
                            $newStatus = match($data['status']) {
                                'RECEIVED', 'CONFIRMED' => 'pago',
                                'OVERDUE' => 'vencido',
                                'PENDING' => 'pendente',
                                default => $f['status']
                            };
                            if ($newStatus !== $f['status']) {
                                $pdo->prepare("UPDATE faturas SET status = ?, data_pagamento = ? WHERE id = ?")
                                    ->execute([$newStatus, ($newStatus === 'pago' ? date('Y-m-d') : null), $f['id']]);
                                $syncedCount++;
                            }
                        }
                    } elseif ($httpCode == 401) {
                        $syncError = 'API Key inválida ou expirada. Verifique o .env';
                        break;
                    }
                }
            }
            
            if (!$syncError) {
                header("Location: cobrancas.php?status=$filtroStatus&synced=1&count=$syncedCount");
                exit;
            }
        }
    } catch (Exception $e) {
        $syncError = 'Erro: ' . $e->getMessage();
    }
}
?>

<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-file-invoice-dollar text-lg"></i></span>
            Cobranças
        </h2>
        <p class="page-subtitle">Histórico e status das cobranças</p>
    </div>
    <a href="cobrancas.php?sync=1&status=<?php echo $filtroStatus; ?>" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> <span class="hidden sm:inline">Sincronizar Asaas</span></a>
</div>

<?php if ($syncError): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-fade-in">
    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-danger"></i></div>
    <div>
        <p class="font-bold text-red-800">Erro na Sincronização</p>
        <p class="text-sm text-red-700"><?php echo htmlspecialchars($syncError); ?></p>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['synced'])): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in">
    <i class="fas fa-check-circle text-success"></i>
    <p class="font-medium text-green-800">Sincronização concluída! <?php echo ($_GET['count'] ?? 0) > 0 ? ($_GET['count'] . ' registros atualizados.') : 'Nenhuma alteração necessária.'; ?></p>
</div>
<?php endif; ?>

<div class="flex flex-wrap gap-2 mb-6">
    <?php 
    $filtros = ['todos' => 'Todos', 'pendente' => 'Pendentes', 'pago' => 'Pagos', 'vencido' => 'Vencidos'];
    foreach ($filtros as $val => $label): ?>
        <a href="?status=<?php echo $val; ?>" class="px-4 py-2 rounded-xl font-semibold text-sm transition-all <?php echo $filtroStatus === $val ? 'bg-primary text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'; ?>">
            <?php echo $label; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card-shadow overflow-hidden">
    <?php if (empty($faturas)): ?>
        <div class="empty-state py-12"><div class="empty-state-icon"><i class="fas fa-file-invoice"></i></div><p class="empty-state-title">Nenhuma cobrança</p><p class="empty-state-text">As cobranças aparecerão aqui após serem geradas</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-premium">
                <thead><tr><th>Paciente</th><th class="hidden sm:table-cell">Referência</th><th class="text-right">Valor</th><th class="text-center">Status</th><th class="text-center hidden sm:table-cell">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($faturas as $f): ?>
                    <tr>
                        <td class="font-semibold text-gray-900"><?php echo htmlspecialchars($f['nome']); ?></td>
                        <td class="text-gray-500 text-sm hidden sm:table-cell"><?php echo $f['mes_referencia']; ?></td>
                        <td class="text-right"><span class="font-bold text-gray-800"><?php echo formatMoney($f['valor_total']); ?></span></td>
                        <td class="text-center">
                            <?php
                            $bc = match($f['status']) { 'pago' => 'badge-success', 'pendente' => 'badge-warning', 'vencido' => 'badge-danger', default => 'badge-neutral' };
                            $sl = match($f['status']) { 'pago' => 'Pago', 'pendente' => 'Pendente', 'vencido' => 'Vencido', default => ucfirst($f['status']) };
                            ?>
                            <span class="badge <?php echo $bc; ?>"><?php echo $sl; ?></span>
                        </td>
                        <td class="text-center hidden sm:table-cell">
                            <?php if (!empty($f['link_pagamento'])): ?>
                                <a href="<?php echo $f['link_pagamento']; ?>" target="_blank" class="text-primary hover:text-primary-dark" title="Ver Fatura"><i class="fas fa-external-link-alt"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
