<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php'; 

// Listar relatórios existentes na pasta public/relatorios
$dir = __DIR__ . '/relatorios';
$relatorios = [];
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if (strpos($file, 'relatorio_') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
            $relatorios[] = $file;
        }
    }
    rsort($relatorios); // Mais recentes primeiro
}

// Trigger manual
if (isset($_POST['gerar_relatorio'])) {
    ob_start();
    include '../scripts/relatorio_contador_mensal.php';
    $output = ob_get_clean();
    $msg = "Relatório gerado com sucesso!";
    header("Location: contador.php?msg=" . urlencode($msg));
    exit;
}
?>

<!-- Page Header -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-calculator text-lg"></i>
            </span>
            Área do Contador
        </h2>
        <p class="page-subtitle">Relatórios mensais de faturamento</p>
    </div>
    
    <form method="POST">
        <button type="submit" name="gerar_relatorio" class="btn btn-primary">
            <i class="fas fa-file-export"></i>
            <span class="hidden sm:inline">Gerar Relatório Atual</span>
            <span class="sm:hidden">Gerar</span>
        </button>
    </form>
</div>

<!-- Success Message -->
<?php if (isset($_GET['msg'])): ?>
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in">
        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-success"></i>
        </div>
        <p class="text-green-800 font-medium"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    </div>
<?php endif; ?>

<!-- Reports Grid -->
<?php if (empty($relatorios)): ?>
    <div class="card-shadow">
        <div class="empty-state py-16">
            <div class="empty-state-icon">
                <i class="fas fa-file-csv"></i>
            </div>
            <p class="empty-state-title">Nenhum relatório gerado</p>
            <p class="empty-state-text">Clique no botão acima para gerar o relatório do mês atual</p>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <?php foreach ($relatorios as $file): 
            $data = str_replace(['relatorio_', '.csv'], '', $file);
            $tamanho = filesize($dir . '/' . $file);
            $dataParts = explode('-', $data);
            $mesesPT = ['01'=>'Janeiro','02'=>'Fevereiro','03'=>'Março','04'=>'Abril','05'=>'Maio','06'=>'Junho','07'=>'Julho','08'=>'Agosto','09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro'];
            $mesNome = isset($dataParts[1]) && isset($mesesPT[$dataParts[1]]) ? $mesesPT[$dataParts[1]] : '';
            $anoNome = $dataParts[0] ?? '';
        ?>
        <div class="card-shadow p-5 group hover:border-primary/30 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center text-success group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-csv text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-800 truncate">
                        <?php echo $mesNome && $anoNome ? "$mesNome $anoNome" : $data; ?>
                    </h3>
                    <p class="text-xs text-gray-400 flex items-center gap-2">
                        <span><i class="fas fa-file mr-1"></i> CSV</span>
                        <span>•</span>
                        <span><?php echo round($tamanho / 1024, 2); ?> KB</span>
                    </p>
                </div>
                <a href="relatorios/<?php echo $file; ?>" download 
                   class="w-12 h-12 rounded-xl bg-gray-100 hover:bg-primary text-gray-500 hover:text-white flex items-center justify-center transition-all flex-shrink-0">
                    <i class="fas fa-download text-lg"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Info Card -->
<div class="mt-8 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-info-circle text-blue-500 text-xl"></i>
        </div>
        <div>
            <h4 class="font-bold text-blue-800 mb-1">Sobre os Relatórios</h4>
            <p class="text-sm text-blue-700">
                Os relatórios CSV contêm detalhes de todas as faturas pagas no mês de referência, 
                incluindo nome do paciente, valor, quantidade de sessões e data de pagamento.
                Esses arquivos podem ser importados em softwares de contabilidade ou planilhas.
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
