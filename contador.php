<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/BillingEngine.php';
require_once __DIR__ . '/classes/HolidayCalculator.php';
include __DIR__ . '/includes/header.php'; 

// Filtro de mês
$mesRef = $_GET['mes'] ?? date('Y-m');
$dataRef = DateTime::createFromFormat('Y-m', $mesRef);
$mesAnterior = (clone $dataRef)->modify('-1 month')->format('Y-m');
$proximoMes = (clone $dataRef)->modify('+1 month')->format('Y-m');

$mesesPT = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];
$mesNome = $mesesPT[$dataRef->format('m')] ?? '';
$anoRef = $dataRef->format('Y');

function getSessionDates($diaSemanaFixo, $mes, $ano, $frequencia = 'semanal', $semanaInicio = 1) {
    if (!$diaSemanaFixo) return '-';
    $dates = [];
    $numDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    $semanaDoMes = 0;
    
    for ($d = 1; $d <= $numDiasMes; $d++) {
        $dateStr = "$ano-$mes-" . str_pad($d, 2, '0', STR_PAD_LEFT);
        if (date('N', strtotime($dateStr)) == $diaSemanaFixo) {
            $semanaDoMes++;
            if (HolidayCalculator::isHoliday($dateStr)) continue;
            
            if ($frequencia === 'semanal') {
                $dates[] = str_pad($d, 2, '0', STR_PAD_LEFT);
            } else if ($frequencia === 'quinzenal') {
                $ehSemanaImpar = ($semanaDoMes % 2 == 1);
                if (($semanaInicio == 1 && $ehSemanaImpar) || ($semanaInicio == 0 && !$ehSemanaImpar)) {
                    $dates[] = str_pad($d, 2, '0', STR_PAD_LEFT);
                }
            }
        }
    }
    return !empty($dates) ? implode(', ', $dates) : '-';
}

// Buscar dados de faturas pagas do mês
$sql = "SELECT f.*, p.nome, p.cpf, p.dia_semana_fixo, p.frequencia, p.semana_inicio 
        FROM faturas f 
        JOIN pacientes p ON f.paciente_id = p.id 
        WHERE f.mes_referencia = ? 
        ORDER BY p.nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mesRef]);
$faturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total
$totalGeral = 0;
foreach ($faturas as &$f) {
    $totalGeral += $f['valor_total'];
    $f['dias_sessoes'] = getSessionDates($f['dia_semana_fixo'], $dataRef->format('m'), $dataRef->format('Y'), $f['frequencia'], $f['semana_inicio']);
}

// Se não houver faturas, buscar cálculo baseado nos pacientes ativos
$engine = new BillingEngine($pdo);
list($ano, $mes) = explode('-', $mesRef);

$pacientesCalculo = [];
if (empty($faturas)) {
    $stmtP = $pdo->query("SELECT * FROM pacientes WHERE ativo = 1 ORDER BY nome ASC");
    $pacientesAtivos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pacientesAtivos as $p) {
        $calculo = $engine->calculateMonthlySessions($p['id'], $mes, $ano);
        if ($calculo['valor_total'] > 0) {
            $pacientesCalculo[] = [
                'nome' => $p['nome'],
                'cpf' => $p['cpf'],
                'valor_total' => $calculo['valor_total'],
                'qtd_sessoes' => $calculo['qtd_sessoes'],
                'status' => 'previsto',
                'data_pagamento' => null,
                'mes_referencia' => $mesRef,
                'dias_sessoes' => getSessionDates($p['dia_semana_fixo'], $mes, $ano, $p['frequencia'], $p['semana_inicio'])
            ];
            $totalGeral += $calculo['valor_total'];
        }
    }
}

$dadosTabela = !empty($faturas) ? $faturas : $pacientesCalculo;
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
    
    <div class="flex gap-2">
        <a href="?mes=<?php echo $mesRef; ?>" class="btn btn-secondary">
            <i class="fas fa-sync-alt"></i> Resetar
        </a>
        <button onclick="gerarPDF()" class="btn btn-primary" id="btn-pdf">
            <i class="fas fa-file-pdf"></i>
            <span class="hidden sm:inline">Gerar PDF</span>
            <span class="sm:hidden">PDF</span>
        </button>
    </div>
</div>

<!-- Month Navigator -->
<div class="flex items-center justify-center bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden mb-8 w-full sm:w-auto sm:inline-flex">
    <a href="?mes=<?php echo $mesAnterior; ?>" class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors">
        <i class="fas fa-chevron-left"></i>
    </a>
    <div class="px-6 py-3 min-w-[200px] text-center border-x border-gray-100">
        <h2 class="text-lg font-bold text-text-main capitalize flex items-center justify-center gap-2">
            <i class="far fa-calendar-alt text-primary"></i>
            <?php echo "$mesNome de $anoRef"; ?>
        </h2>
    </div>
    <a href="?mes=<?php echo $proximoMes; ?>" class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors">
        <i class="fas fa-chevron-right"></i>
    </a>
</div>

<!-- Informações Complementares -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
    <div class="card p-5 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center"><i class="fas fa-file-invoice-dollar"></i></span>
            Despesas e Deduções
        </h4>
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">Telefone / Internet</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">Enel</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">Psicóloga Mãe</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">CRP</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">Sabesp</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">Aluguel</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
            <div class="flex items-center justify-between gap-4">
                <label class="text-[10px] font-black text-gray-400 uppercase">Escola Samuel</label>
                <input type="text" class="form-input text-sm text-right w-32 border-none bg-gray-50 focus:bg-white money-mask" placeholder="R$ 0,00" value="">
            </div>
        </div>
    </div>
    
    <div class="card p-5 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-green-50 text-green-500 flex items-center justify-center"><i class="fas fa-users"></i></span>
            Dados dos Dependentes
        </h4>
        <div class="space-y-4">
            <div class="p-4 bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-100 relative group overflow-hidden">
                <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fas fa-child text-4xl text-blue-800"></i>
                </div>
                <p class="text-[10px] font-black text-blue-800 uppercase mb-1 tracking-wider">Dependente Autista</p>
                <p class="text-sm font-bold text-gray-800 mb-1">Samuel loiola Chegury</p>
                <div class="flex flex-col gap-0.5">
                    <p class="text-[11px] text-gray-600 font-mono"><span class="font-bold">CPF:</span> 576.839.088/00</p>
                    <p class="text-[11px] text-gray-600"><span class="font-bold">Nasc:</span> 11/09/2019</p>
                </div>
            </div>
            
            <div class="p-4 bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-100 relative group overflow-hidden">
                <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fas fa-female text-4xl text-purple-800"></i>
                </div>
                <p class="text-[10px] font-black text-purple-800 uppercase mb-1 tracking-wider">Dependente Mãe</p>
                <p class="text-sm font-bold text-gray-800 mb-1">Maria Inacelia de oliveira loiola</p>
                <div class="flex flex-col gap-0.5">
                    <p class="text-[11px] text-gray-600 font-mono"><span class="font-bold">CPF:</span> 063.833.398-93</p>
                    <p class="text-[11px] text-gray-600"><span class="font-bold">Nasc:</span> 11/02/1962</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela Completa -->
<div class="card-shadow overflow-hidden" id="tabela-contador-container">
    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="text-lg font-bold text-text-main flex items-center gap-2">
            <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="fas fa-table text-primary text-sm"></i>
            </span>
            Relatório Detalhado - <?php echo "$mesNome de $anoRef"; ?>
        </h3>
        <span class="badge badge-info" id="count-registros"><?php echo count($dadosTabela); ?> registros</span>
    </div>
    
    <?php if (empty($dadosTabela)): ?>
        <div class="empty-state py-16">
            <div class="empty-state-icon"><i class="fas fa-file-invoice"></i></div>
            <p class="empty-state-title">Nenhum dado encontrado</p>
            <p class="empty-state-text">Não há faturas ou previsões para este mês</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-premium" id="table-data">
                <thead>
                    <tr>
                        <th class="w-8">#</th>
                        <th>NOME COMPLETO PACIENTE</th>
                        <th>CPF PAGADOR</th>
                        <th>CPF BENEFICIÁRIO</th>
                        <th>DATA RECEBIMENTO</th>
                        <th class="text-right">VALOR RECEBIDO</th>
                        <th>DIAS DE SESSÕES</th>
                        <th class="w-10">Ações</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php $i = 1; foreach ($dadosTabela as $item): ?>
                    <tr class="row-financeiro" data-valor="<?php echo $item['valor_total']; ?>">
                        <td class="text-gray-400 font-mono text-xs"><?php echo $i++; ?></td>
                        <td>
                            <p class="font-semibold text-gray-900 uppercase text-xs"><?php echo htmlspecialchars($item['nome']); ?></p>
                        </td>
                        <td class="font-mono text-[10px] text-gray-500">
                            <?php 
                            $cpf = $item['cpf'] ?? '';
                            if (strlen($cpf) == 11) {
                                echo substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2);
                            } else {
                                echo htmlspecialchars($cpf);
                            }
                            ?>
                        </td>
                        <td class="font-mono text-[10px] text-gray-500">
                            <?php 
                            // O usuário pediu CPF Pagador e Beneficiário. Por padrão usamos o mesmo.
                            echo substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2);
                            ?>
                        </td>
                        <td class="text-gray-500 text-[10px]">
                            <?php echo !empty($item['data_pagamento']) ? date('d/m/Y', strtotime($item['data_pagamento'])) : ( ($item['status'] ?? '') == 'pago' ? date('d/m/Y') : '-' ); ?>
                        </td>
                        <td class="text-right">
                            <span class="font-bold text-gray-800 text-xs"><?php echo formatMoney($item['valor_total']); ?></span>
                        </td>
                        <td class="text-[10px] text-gray-500">
                            <?php echo $item['dias_sessoes'] ?? '-'; ?>
                        </td>
                        <td>
                            <button onclick="removerLinha(this)" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gradient-to-r from-primary/5 to-accent/5">
                        <td colspan="5" class="font-extrabold text-gray-800 text-right text-sm">TOTAL GERAL RECEBIDO</td>
                        <td class="text-right">
                            <span class="font-extrabold text-primary text-sm" id="total-geral-display"><?php echo formatMoney($totalGeral); ?></span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function(){
    $('.money-mask').mask('#.##0,00', {reverse: true});
});

function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function removerLinha(btn) {
    if (!confirm('Deseja remover esta linha do relatório atual?')) return;
    
    const row = btn.closest('tr');
    row.classList.add('opacity-0', '-translate-x-4');
    
    setTimeout(() => {
        row.remove();
        atualizarTotais();
        showToast('Linha removida do relatório', 'info');
    }, 300);
}

function atualizarTotais() {
    let total = 0;
    const rows = document.querySelectorAll('.row-financeiro');
    rows.forEach(row => {
        total += parseFloat(row.dataset.valor);
    });
    
    document.getElementById('total-geral-display').textContent = formatarMoeda(total);
    document.getElementById('count-registros').textContent = rows.length + ' registros';
    
    // Atualizar no card do topo também se houver
    const statTotal = document.querySelector('.stat-card .text-primary');
    if (statTotal) statTotal.textContent = formatarMoeda(total);
}

function gerarPDF() {
    const btn = document.getElementById('btn-pdf');
    btn.classList.add('btn-loading');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    setTimeout(() => {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4'); // Paisagem para caber colunas
            
            // Layout de cores
            const primaryColor = [14, 165, 233];
            
            // Header
            doc.setFillColor(249, 250, 251);
            doc.rect(0, 0, 297, 40, 'F');
            
            doc.setTextColor(31, 41, 55);
            doc.setFontSize(22);
            doc.setFont('helvetica', 'bold');
            doc.text('PSIMANAGER - RELATÓRIO CONTÁBIL', 15, 20);
            
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(107, 114, 128);
            doc.text('DOCUMENTO DE REFERÊNCIA FINANCEIRA', 15, 28);
            
            doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
            doc.setFont('helvetica', 'bold');
            doc.text('MÊS: <?php echo mb_strtoupper("$mesNome de $anoRef"); ?>', 282, 20, { align: 'right' });
            
            doc.setTextColor(107, 114, 128);
            doc.setFont('helvetica', 'normal');
            doc.text('DATA DE EMISSÃO: ' + new Date().toLocaleDateString('pt-BR'), 282, 28, { align: 'right' });
            
            // Informações extras
            let currentY = 45;
            
            // Tabela Principal
            const tableData = [];
            document.querySelectorAll('#table-body tr').forEach((row, index) => {
                const cols = row.querySelectorAll('td');
                tableData.push([
                    index + 1,
                    cols[1].innerText.trim(),
                    cols[2].innerText.trim(),
                    cols[3].innerText.trim(),
                    cols[4].innerText.trim(),
                    cols[5].innerText.trim(),
                    cols[6].innerText.trim()
                ]);
            });
            
            doc.autoTable({
                head: [['#', 'NOME COMPLETO PACIENTE', 'CPF PAGADOR', 'CPF BENEFICIÁRIO', 'DATA RECEB.', 'VALOR (R$)', 'DIAS SESSÕES']],
                body: tableData,
                startY: currentY,
                theme: 'striped',
                headStyles: { fillColor: primaryColor, textColor: 255, fontSize: 8, halign: 'center' },
                styles: { fontSize: 7, cellPadding: 2 },
                columnStyles: {
                    0: { cellWidth: 10, halign: 'center' },
                    5: { halign: 'right', fontStyle: 'bold' },
                    6: { halign: 'center' }
                },
                didDrawPage: function(data) { currentY = data.cursor.y; }
            });
            
            currentY += 10;
            
            // Totais e Informações Fiscais
            if (currentY > 160) { doc.addPage(); currentY = 20; }
            
            doc.setDrawColor(229, 231, 235);
            doc.line(15, currentY, 282, currentY);
            currentY += 10;
            
            doc.setFontSize(12);
            doc.setTextColor(31, 41, 55);
            doc.setFont('helvetica', 'bold');
            doc.text('RESUMO E INFORMAÇÕES COMPLEMENTARES', 15, currentY);
            
            currentY += 10;
            
            // Coluna 1: Despesas
            doc.setFontSize(9);
            doc.text('DESPESAS:', 15, currentY);
            let subY = currentY + 7;
            const labelsMap = ['Telefone/Internet', 'Enel', 'Psicóloga Mãe', 'CRP', 'Sabesp', 'Aluguel', 'Escola Samuel'];
            document.querySelectorAll('.card:first-child input').forEach((inp, i) => {
                doc.setFont('helvetica', 'normal');
                doc.text(labelsMap[i] + ':', 15, subY);
                doc.setFont('helvetica', 'bold');
                doc.text(inp.value || 'R$ 0,00', 60, subY);
                subY += 6;
            });
            
            // Coluna 3: Dependentes
            doc.setFont('helvetica', 'bold');
            doc.text('DADOS DEPENDENTES:', 120, currentY);
            subY = currentY + 7;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.text('SAMUEL LOIOLA CHEGURY (AUTISTA)', 120, subY);
            doc.text('CPF: 576.839.088/00 - NASC: 11/09/2019', 120, subY + 4);
            subY += 12;
            doc.text('MARIA INACELIA DE OLIVEIRA LOIOLA (MÃE)', 120, subY);
            doc.text('CPF: 063.833.398-93 - NASC: 11/02/1962', 120, subY + 4);
            
            // Footer Final
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(156, 163, 175);
                doc.text('GERADO VIA PSIMANAGER v3.1 - DOCUMENTO PARA FINS CONTÁBEIS', 15, 200);
                doc.text('PÁGINA ' + i + ' DE ' + pageCount, 282, 200, { align: 'right' });
            }
            
            doc.save('CONTADOR_PSIMANAGER_<?php echo $mesRef; ?>.pdf');
            showToast('Relatório PDF gerado!', 'success');
        } catch (e) {
            console.error(e);
            showToast('Erro ao gerar PDF: ' + e.message, 'error');
        }
        
        btn.classList.remove('btn-loading');
        btn.innerHTML = '<i class="fas fa-file-pdf"></i> <span class="hidden sm:inline">Gerar PDF</span><span class="sm:hidden">PDF</span>';
    }, 500);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

