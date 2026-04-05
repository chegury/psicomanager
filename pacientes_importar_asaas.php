<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/AsaasClient.php';

$asaas = new AsaasClient();
$msg = null;
$error = null;

// Ação de Importação
if (isset($_POST['import_id'])) {
    try {
        $asaasId = $_POST['import_id'];
        $customer = $asaas->request('GET', '/customers/' . $asaasId);
        
        if ($customer) {
            $nome = $customer['name'];
            $email = $customer['email'] ?? '';
            $whatsapp = $customer['mobilePhone'] ?? $customer['phone'] ?? '';
            $cpf = $customer['cpfCnpj'] ?? '';

            // Verificar se já existe no BD local (pelo asaas_id ou CPF)
            $stmtCheck = $pdo->prepare("SELECT id FROM pacientes WHERE asaas_id = ? OR (cpf = ? AND cpf != '')");
            $stmtCheck->execute([$asaasId, $cpf]);
            $exists = $stmtCheck->fetch();

            if ($exists) {
                // Apenas atualiza o asaas_id se for match por CPF
                $pdo->prepare("UPDATE pacientes SET asaas_id = ? WHERE id = ?")->execute([$asaasId, $exists['id']]);
                $msg = "Paciente '$nome' vinculado com sucesso!";
            } else {
                // Cria novo
                $stmt = $pdo->prepare("INSERT INTO pacientes (nome, email, whatsapp, cpf, asaas_id, ativo) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$nome, $email, $whatsapp, $cpf, $asaasId]);
                $msg = "Paciente '$nome' importado com sucesso!";
            }
        }
    } catch (Exception $e) {
        $error = "Erro ao importar: " . $e->getMessage();
    }
}

// Ação de Importação em Massa
if (isset($_POST['import_all'])) {
    try {
        $response = $asaas->listCustomers();
        $allCustomers = $response['data'] ?? [];
        $importedCount = 0;
        $linkedCount = 0;

        foreach ($allCustomers as $customer) {
            $asaasId = $customer['id'];
            $nome = $customer['name'];
            $email = $customer['email'] ?? '';
            $whatsapp = $customer['mobilePhone'] ?? $customer['phone'] ?? '';
            $cpf = $customer['cpfCnpj'] ?? '';

            // Verificar no local
            $stmtCheck = $pdo->prepare("SELECT id, asaas_id FROM pacientes WHERE asaas_id = ? OR (cpf = ? AND cpf != '')");
            $stmtCheck->execute([$asaasId, $cpf]);
            $exists = $stmtCheck->fetch();

            if ($exists) {
                if (!$exists['asaas_id']) {
                    // Vincula
                    $pdo->prepare("UPDATE pacientes SET asaas_id = ? WHERE id = ?")->execute([$asaasId, $exists['id']]);
                    $linkedCount++;
                }
            } else {
                // Importa
                $stmt = $pdo->prepare("INSERT INTO pacientes (nome, email, whatsapp, cpf, asaas_id, ativo) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$nome, $email, $whatsapp, $cpf, $asaasId]);
                $importedCount++;
            }
        }
        $msg = "Sucesso: $importedCount pacientes importados e $linkedCount pacientes vinculados.";
    } catch (Exception $e) {
        $error = "Erro na importação em massa: " . $e->getMessage();
    }
}

// Buscar clientes do Asaas
$asaasCustomers = [];
try {
    $response = $asaas->listCustomers();
    $asaasCustomers = $response['data'] ?? [];
} catch (Exception $e) {
    if (strpos($e->getMessage(), '401') !== false) {
        $error = "Chave de API do Asaas inválida ou não configurada.";
    } else {
        $error = "Erro ao consultar Asaas: " . $e->getMessage();
    }
}

// Buscar pacientes locais para comparação
$stmtLocal = $pdo->query("SELECT id, nome, email, cpf, asaas_id FROM pacientes");
$pacientesLocais = $stmtLocal->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
// Transformar em array simples indexado por campos únicos
$locaisPorAsaasId = [];
$locaisPorCpf = [];
foreach ($pacientesLocais as $nome => $dadosArr) {
    foreach ($dadosArr as $d) {
        if ($d['asaas_id']) $locaisPorAsaasId[$d['asaas_id']] = true;
        if ($d['cpf']) $locaisPorCpf[$d['cpf']] = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-6">
    <div class="flex items-center gap-4">
        <a href="pacientes.php" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="page-title">Importar do Asaas</h2>
            <p class="page-subtitle">Sincronize sua base de clientes do Asaas com o Psicomanager</p>
        </div>
    </div>
</div>

<?php if ($msg): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in text-success font-bold">
        <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-fade-in text-red-600 font-bold">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card-shadow overflow-hidden">
    <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Clientes encontrados no Asaas (<?php echo count($asaasCustomers); ?>)</h3>
        <div class="flex items-center gap-3">
            <?php if (!empty($asaasCustomers)): ?>
                <form method="POST" onsubmit="return confirm('Isso importará todos os clientes que ainda não existem localmente. Continuar?')">
                    <input type="hidden" name="import_all" value="1">
                    <button type="submit" class="btn btn-secondary text-primary border-primary/20 bg-primary/5 hover:bg-primary/20 btn-sm">
                        <i class="fas fa-layer-group"></i> Importar Todos
                    </button>
                </form>
            <?php endif; ?>
            <span class="text-[10px] bg-primary/10 text-primary px-2 py-1 rounded-full font-bold">API: ONLINE</span>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-gray-100">
                    <th class="p-4 text-xs font-black text-gray-400 uppercase">Nome / E-mail</th>
                    <th class="p-4 text-xs font-black text-gray-400 uppercase">CPF/CNPJ</th>
                    <th class="p-4 text-xs font-black text-gray-400 uppercase">Status Local</th>
                    <th class="p-4 text-xs font-black text-gray-400 uppercase text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($asaasCustomers)): ?>
                    <tr>
                        <td colspan="4" class="p-10 text-center text-gray-400 italic">Nenhum cliente encontrado no Asaas ou erro na conexão.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($asaasCustomers as $c): 
                        $status = 'novo';
                        if (isset($locaisPorAsaasId[$c['id']])) {
                            $status = 'vinculado';
                        } elseif ($c['cpfCnpj'] && isset($locaisPorCpf[$c['cpfCnpj']])) {
                            $status = 'conflito'; // Existe por CPF mas não tem Asaas ID vinculado
                        }
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-gray-800"><?php echo htmlspecialchars($c['name']); ?></div>
                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($c['email'] ?? 'Sem e-mail'); ?></div>
                        </td>
                        <td class="p-4 text-sm font-medium text-gray-600"><?php echo htmlspecialchars($c['cpfCnpj'] ?? '-'); ?></td>
                        <td class="p-4">
                            <?php if ($status === 'vinculado'): ?>
                                <span class="badge badge-success text-[10px]"><i class="fas fa-link mr-1"></i> Sincronizado</span>
                            <?php elseif ($status === 'conflito'): ?>
                                <span class="badge badge-warning text-[10px]"><i class="fas fa-exchange-alt mr-1"></i> CPF Já Existe</span>
                            <?php else: ?>
                                <span class="badge badge-neutral text-[10px]"><i class="fas fa-plus mr-1"></i> Não Importado</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right">
                            <?php if ($status !== 'vinculado'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="import_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas <?php echo $status === 'conflito' ? 'fa-link' : 'fa-download'; ?>"></i>
                                    <?php echo $status === 'conflito' ? 'Vincular' : 'Importar'; ?>
                                </button>
                            </form>
                            <?php else: ?>
                                <button disabled class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">
                                    <i class="fas fa-check"></i> Importado
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 flex justify-center">
    <p class="text-xs text-gray-400 max-w-lg text-center">
        * A importação vincula automaticamente cobranças futuras e facilita o acompanhamento financeiro deste paciente no Psicomanager.
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
