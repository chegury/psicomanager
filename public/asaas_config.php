<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../classes/EnvLoader.php';
require_once '../classes/AsaasClient.php';

// Carrega variáveis de ambiente
EnvLoader::load(__DIR__ . '/../.env');

include '../includes/header.php';

$testResult = null;
$testError = null;
$createdPayment = null;

// Verifica se a API Key está configurada
$apiKey = $_ENV['ASAAS_API_KEY'] ?? getenv('ASAAS_API_KEY') ?? '';
$apiUrl = $_ENV['ASAAS_URL'] ?? getenv('ASAAS_URL') ?? 'https://sandbox.asaas.com/api/v3';
$isSandbox = strpos($apiUrl, 'sandbox') !== false;
$isConfigured = !empty($apiKey) && $apiKey !== 'sua_api_key_sandbox_aqui';

// Testar conexão
if (isset($_POST['test_connection']) && $isConfigured) {
    try {
        // Testa listando clientes (simples)
        $ch = curl_init();
        $testUrl = rtrim($apiUrl, '/') . '/customers?limit=1';
        
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token: ' . $apiKey,
            'User-Agent: PsiManager/1.0'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para ambientes locais
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $testError = 'Erro cURL: ' . $curlError;
        } elseif ($httpCode === 200) {
            $decoded = json_decode($response, true);
            $totalClientes = $decoded['totalCount'] ?? 0;
            $testResult = "Conexão com Asaas estabelecida com sucesso! ($totalClientes clientes cadastrados)";
        } else {
            $decoded = json_decode($response, true);
            $errorMsg = $decoded['errors'][0]['description'] ?? ($decoded['message'] ?? "HTTP $httpCode");
            $testError = "Erro na API: $errorMsg";
        }
    } catch (Exception $e) {
        $testError = 'Erro: ' . $e->getMessage();
    }
}

// Criar cobrança de teste
if (isset($_POST['create_test_payment']) && $isConfigured) {
    try {
        $asaas = new AsaasClient();
        
        // CPF válido para teste (gerado com dígito verificador correto)
        // 191.183.176-17 → 19118317617
        $cpfTeste = '19118317617';
        
        // Primeiro, criar cliente de teste
        $customerId = $asaas->createCustomer(
            'Cliente Teste PsiManager',
            $cpfTeste,
            'teste' . time() . '@psimanager.com',
            '11999999999'
        );
        
        if ($customerId) {
            // Criar cobrança
            $dueDate = date('Y-m-d', strtotime('+7 days'));
            $result = $asaas->createPayment(
                ['id' => $customerId],
                [
                    'value' => 100.00,
                    'dueDate' => $dueDate,
                    'description' => 'Teste PsiManager - Sessão de Psicologia',
                    'externalReference' => 'TESTE-' . time()
                ]
            );
            
            $createdPayment = $result;
            $testResult = 'Cobrança de teste criada com sucesso!';
        } else {
            $testError = 'Não foi possível criar o cliente de teste';
        }
    } catch (Exception $e) {
        $testError = 'Erro: ' . $e->getMessage();
    }
}

// Buscar pacientes para criar cobrança
$stmtPacientes = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1 ORDER BY nome ASC");
$pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

// Criar cobrança real para um paciente
if (isset($_POST['create_real_payment']) && $isConfigured) {
    $pacienteId = $_POST['paciente_id'] ?? null;
    $valor = floatval(str_replace(['.', ','], ['', '.'], $_POST['valor'] ?? '0'));
    
    if ($pacienteId && $valor > 0) {
        $stmtP = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
        $stmtP->execute([$pacienteId]);
        $paciente = $stmtP->fetch(PDO::FETCH_ASSOC);
        
        if ($paciente) {
            try {
                $asaas = new AsaasClient();
                
                // Criar/buscar cliente
                $cpfLimpo = preg_replace('/\D/', '', $paciente['cpf']);
                $customerId = $asaas->createCustomer(
                    $paciente['nome'],
                    $cpfLimpo,
                    $paciente['email'] ?? null,
                    preg_replace('/\D/', '', $paciente['whatsapp'])
                );
                
                if ($customerId) {
                    $dueDate = date('Y-m-d', strtotime('+' . ($paciente['dias_antecedencia'] ?? 5) . ' days'));
                    $mesRef = date('Y-m');
                    
                    $result = $asaas->createPayment(
                        ['id' => $customerId],
                        [
                            'value' => $valor,
                            'dueDate' => $dueDate,
                            'description' => "Sessões de Psicologia - {$paciente['nome']} - Ref: $mesRef",
                            'externalReference' => "PSI-{$paciente['id']}-$mesRef"
                        ]
                    );
                    
                    if ($result && isset($result['id'])) {
                        // Salvar na tabela de faturas
                        $stmtInsert = $pdo->prepare("
                            INSERT INTO faturas (paciente_id, mes_referencia, valor_total, qtd_sessoes, asaas_id, link_pagamento, status)
                            VALUES (?, ?, ?, ?, ?, ?, 'pendente')
                        ");
                        $stmtInsert->execute([
                            $paciente['id'],
                            $mesRef,
                            $valor,
                            intval($valor / $paciente['valor_sessao']),
                            $result['id'],
                            $result['invoiceUrl'] ?? $result['bankSlipUrl'] ?? ''
                        ]);
                        
                        $createdPayment = $result;
                        $testResult = "Cobrança criada para {$paciente['nome']}!";
                    }
                }
            } catch (Exception $e) {
                $testError = 'Erro: ' . $e->getMessage();
            }
        }
    }
}
?>

<!-- Page Header -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-credit-card text-lg"></i>
            </span>
            Integração Asaas
        </h2>
        <p class="page-subtitle">Configure e teste a integração com o gateway de pagamentos</p>
    </div>
</div>

<!-- Status Messages -->
<?php if ($testResult): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3 animate-fade-in">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-success"></i>
        </div>
        <div>
            <p class="font-bold text-green-800"><?php echo $testResult; ?></p>
            <?php if ($createdPayment): ?>
                <div class="mt-2 text-sm text-green-700">
                    <p><strong>ID Asaas:</strong> <?php echo $createdPayment['id'] ?? 'N/A'; ?></p>
                    <?php if (!empty($createdPayment['invoiceUrl'])): ?>
                        <p class="mt-1">
                            <a href="<?php echo $createdPayment['invoiceUrl']; ?>" target="_blank" class="text-primary underline font-semibold">
                                <i class="fas fa-external-link-alt mr-1"></i> Ver Fatura/Boleto
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($createdPayment['bankSlipUrl'])): ?>
                        <p class="mt-1">
                            <a href="<?php echo $createdPayment['bankSlipUrl']; ?>" target="_blank" class="text-primary underline font-semibold">
                                <i class="fas fa-barcode mr-1"></i> Ver Boleto
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($testError): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-fade-in">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-times text-danger"></i>
        </div>
        <p class="font-medium text-red-800"><?php echo htmlspecialchars($testError); ?></p>
    </div>
<?php endif; ?>

<!-- Configuration Status -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Status Card -->
    <div class="card-shadow p-6">
        <h3 class="text-lg font-bold text-text-main mb-4 flex items-center gap-2">
            <i class="fas fa-cog text-primary"></i>
            Status da Configuração
        </h3>
        
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-700">Ambiente</span>
                <?php if ($isSandbox): ?>
                    <span class="badge badge-warning">
                        <i class="fas fa-flask mr-1"></i> Sandbox (Teste)
                    </span>
                <?php else: ?>
                    <span class="badge badge-success">
                        <i class="fas fa-check mr-1"></i> Produção
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-700">API Key</span>
                <?php if ($isConfigured): ?>
                    <span class="badge badge-success">
                        <i class="fas fa-key mr-1"></i> Configurada
                    </span>
                <?php else: ?>
                    <span class="badge badge-danger">
                        <i class="fas fa-times mr-1"></i> Não Configurada
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-700">URL da API</span>
                <span class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded">
                    <?php echo htmlspecialchars(substr($apiUrl, 0, 35)); ?>...
                </span>
            </div>
        </div>
        
        <?php if ($isConfigured): ?>
            <form method="POST" class="mt-6">
                <button type="submit" name="test_connection" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-plug"></i>
                    Testar Conexão
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Instructions Card -->
    <div class="card-shadow p-6">
        <h3 class="text-lg font-bold text-text-main mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-primary"></i>
            Como Configurar
        </h3>
        
        <ol class="space-y-3 text-sm">
            <li class="flex gap-3">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                <span>Acesse <a href="https://sandbox.asaas.com/" target="_blank" class="text-primary underline font-semibold">sandbox.asaas.com</a> (grátis para testes)</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                <span>Crie uma conta de teste (não precisa de dados reais)</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                <span>Vá em <strong>Minha Conta → Integrações → API</strong></span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
                <span>Copie sua <strong>API Key</strong></span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">5</span>
                <span>Edite o arquivo <code class="bg-gray-100 px-2 py-0.5 rounded">.env</code> na raiz do projeto</span>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">6</span>
                <span>Cole a chave em <code class="bg-gray-100 px-2 py-0.5 rounded">ASAAS_API_KEY=</code></span>
            </li>
        </ol>
        
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm">
            <p class="text-yellow-800">
                <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                <strong>Dica:</strong> No sandbox você pode testar cobranças sem pagar nada. Os boletos e PIX são simulados!
            </p>
        </div>
    </div>
</div>

<?php if ($isConfigured): ?>
<!-- Test Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Create Test Payment -->
    <div class="card-shadow p-6">
        <h3 class="text-lg font-bold text-text-main mb-4 flex items-center gap-2">
            <i class="fas fa-vial text-warning"></i>
            Cobrança de Teste
        </h3>
        
        <p class="text-sm text-gray-600 mb-4">
            Cria uma cobrança fictícia (R$ 100,00) com um cliente de teste para validar a integração.
        </p>
        
        <form method="POST">
            <button type="submit" name="create_test_payment" class="btn btn-secondary w-full justify-center">
                <i class="fas fa-flask"></i>
                Criar Cobrança de Teste
            </button>
        </form>
    </div>
    
    <!-- Create Real Payment -->
    <div class="card-shadow p-6">
        <h3 class="text-lg font-bold text-text-main mb-4 flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-success"></i>
            Criar Cobrança Real
        </h3>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Paciente</label>
                <select name="paciente_id" required class="form-input">
                    <option value="">Selecione...</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?php echo $p['id']; ?>">
                            <?php echo htmlspecialchars($p['nome']); ?> (<?php echo formatMoney($p['valor_sessao']); ?>/sessão)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Valor Total (R$)</label>
                <input type="text" name="valor" required class="form-input money-mask" placeholder="0,00">
            </div>
            
            <button type="submit" name="create_real_payment" class="btn btn-primary w-full justify-center">
                <i class="fas fa-paper-plane"></i>
                Gerar Cobrança
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    $(document).ready(function(){
        $('.money-mask').mask('#.##0,00', {reverse: true});
    });
</script>

<?php include '../includes/footer.php'; ?>
