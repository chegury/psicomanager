<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php'; 

// Processar Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $valor = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor']);
    $data_pagamento = $_POST['data_pagamento'];
    $metodo = $_POST['metodo'];
    $status = 'pago';

    try {
        $stmt = $pdo->prepare("INSERT INTO financeiro (paciente_id, valor, data_pagamento, metodo_pagamento, status, tipo_transacao, descricao) VALUES (?, ?, ?, ?, ?, 'receita', 'Pagamento Simulado')");
        $stmt->execute([$paciente_id, $valor, $data_pagamento, $metodo, $status]);
        $msg = "Pagamento simulado registrado com sucesso!";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Erro ao registrar: " . $e->getMessage();
        $msgType = "error";
    }
}

// Buscar pacientes
$pacientes = $pdo->query("SELECT id, nome FROM pacientes WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-primary">Simular Pagamento</h2>
        <p class="text-text-light">Ferramenta para validar lógica financeira</p>
    </div>

    <?php if (isset($msg)): ?>
        <div class="<?php echo $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> p-4 rounded mb-6">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-xl card-shadow p-8 space-y-6">
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Paciente</label>
            <select name="paciente_id" required class="w-full border-gray-300 p-3 rounded focus:ring-primary focus:border-primary">
                <option value="">Selecione...</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Valor (R$)</label>
            <input type="text" name="valor" required class="w-full border-gray-300 p-3 rounded focus:ring-primary focus:border-primary money-mask" placeholder="0,00">
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Data do Pagamento</label>
            <input type="date" name="data_pagamento" value="<?php echo date('Y-m-d'); ?>" required class="w-full border-gray-300 p-3 rounded focus:ring-primary focus:border-primary">
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Método</label>
            <select name="metodo" required class="w-full border-gray-300 p-3 rounded focus:ring-primary focus:border-primary">
                <option value="pix">PIX</option>
                <option value="cartao">Cartão</option>
                <option value="dinheiro">Dinheiro</option>
            </select>
        </div>

        <button type="submit" class="w-full bg-primary hover:bg-primary-hover text-white font-bold py-3 rounded-lg shadow-lg transition">
            Registrar Pagamento
        </button>
    </form>
</div>

<script>
    $(document).ready(function(){
        $('.money-mask').mask('#.##0,00', {reverse: true});
    });
</script>

<?php include '../includes/footer.php'; ?>
