<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pacienteId = intval($_GET['paciente_id'] ?? 0);
$testesIds = !empty($_GET['testes']) ? explode(',', $_GET['testes']) : [];
$anamnesesIds = !empty($_GET['anamneses']) ? explode(',', $_GET['anamneses']) : [];

if (!$pacienteId) die("Paciente não informado.");

// Buscar Paciente
$stmtP = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmtP->execute([$pacienteId]);
$paciente = $stmtP->fetch();

// Buscar Testes Selecionados
$testes = [];
if (!empty($testesIds)) {
    $placeholders = implode(',', array_fill(0, count($testesIds), '?'));
    $stmtT = $pdo->prepare("SELECT * FROM testes_resultados WHERE id IN ($placeholders)");
    $stmtT->execute($testesIds);
    $testes = $stmtT->fetchAll();
}

// Buscar Anamneses Selecionadas
$anamneses = [];
if (!empty($anamnesesIds)) {
    $placeholders = implode(',', array_fill(0, count($anamnesesIds), '?'));
    $stmtA = $pdo->prepare("SELECT * FROM anamneses_resultados WHERE id IN ($placeholders)");
    $stmtA->execute($anamnesesIds);
    $anamneses = $stmtA->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-6">
    <div class="flex items-center gap-4">
        <a href="paciente_editar.php?id=<?php echo $pacienteId; ?>" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-primary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="page-title">Gerador de Laudo Psicológico</h2>
            <p class="page-subtitle">Documento formal conforme Resolução CRP 06/2019</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Painel de Configuração -->
    <div class="lg:col-span-1 space-y-6">
        <div class="card-shadow p-6">
            <h3 class="text-sm font-black text-gray-400 uppercase mb-4 tracking-widest">Documentos Selecionados</h3>
            <div class="space-y-3">
                <?php foreach ($anamneses as $a): ?>
                    <div class="flex items-center gap-3 p-2 bg-secondary/5 rounded-lg border border-secondary/10">
                        <i class="fas fa-file-signature text-secondary text-xs"></i>
                        <span class="text-xs font-bold text-gray-700">Anamnese <?php echo ucfirst($a['tipo_anamnese']); ?></span>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($testes as $t): ?>
                    <div class="flex items-center gap-3 p-2 bg-primary/5 rounded-lg border border-primary/10">
                        <i class="fas fa-brain text-primary text-xs"></i>
                        <span class="text-xs font-bold text-gray-700">Teste: <?php echo $t['tipo_teste']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-shadow p-6">
            <h3 class="text-sm font-black text-gray-400 uppercase mb-4 tracking-widest">Ações</h3>
            <button onclick="gerarPDF()" class="btn btn-primary w-full justify-center shadow-lg shadow-primary/30 py-3">
                <i class="fas fa-file-pdf text-lg"></i> Gerar Documento Final
            </button>
            <p class="text-[10px] text-gray-400 mt-4 text-center italic">Este documento deve ser assinado e carimbado pelo profissional responsável.</p>
        </div>
    </div>

    <!-- Editor de Conteúdo -->
    <div class="lg:col-span-2 space-y-6">
        <div class="card-shadow p-8 bg-white" id="laudo-editor">
            <div class="text-center mb-10">
                <h1 class="text-2xl font-black text-gray-900">LAUDO PSICOLÓGICO</h1>
                <div class="w-20 h-1 bg-primary mx-auto mt-2"></div>
            </div>

            <!-- 1. Identificação -->
            <section class="mb-8">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">1. Identificação</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <p><strong>Paciente:</strong> <?php echo htmlspecialchars($paciente['nome']); ?></p>
                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($paciente['cpf']); ?></p>
                    <p><strong>Autor:</strong> Psicólogo(a) Responsável</p>
                    <p><strong>Finalidade:</strong> <input type="text" id="finalidade" class="border-b border-gray-200 focus:border-primary outline-none px-1" value="Avaliação Psicológica"></p>
                </div>
            </section>

            <!-- 2. Descrição da Demanda -->
            <section class="mb-8">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">2. Descrição da Demanda</h3>
                <textarea id="demanda" class="w-full p-3 bg-gray-50 rounded-lg text-sm border-none focus:ring-2 focus:ring-primary/20 min-h-[100px]" placeholder="Descreva o motivo da consulta e a necessidade deste laudo..."></textarea>
            </section>

            <!-- 3. Procedimento -->
            <section class="mb-8">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">3. Procedimento</h3>
                <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">
                    <p>A avaliação foi realizada através dos seguintes instrumentos:</p>
                    <ul class="list-disc ml-5 mt-2 space-y-1">
                        <?php foreach ($anamneses as $a): ?>
                            <li>Entrevista de Anamnese (<?php echo ucfirst($a['tipo_anamnese']); ?>) realizada em <?php echo date('d/m/Y', strtotime($a['created_at'])); ?></li>
                        <?php endforeach; ?>
                        <?php foreach ($testes as $t): ?>
                            <li>Aplicação do instrumento técnico <?php echo $t['tipo_teste']; ?> em <?php echo date('d/m/Y', strtotime($t['created_at'])); ?></li>
                        <?php endforeach; ?>
                        <li>Observação Clínica e Escuta Analítica.</li>
                    </ul>
                </div>
            </section>

            <!-- 4. Análise -->
            <section class="mb-8">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">4. Análise</h3>
                <textarea id="analise" class="w-full p-3 bg-gray-50 rounded-lg text-sm border-none focus:ring-2 focus:ring-primary/20 min-h-[150px]" placeholder="Realize a integração dos dados coletados na anamnese e nos testes..."></textarea>
            </section>

            <!-- 5. Conclusão -->
            <section class="mb-8">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">5. Conclusão</h3>
                <textarea id="conclusao" class="w-full p-3 bg-gray-50 rounded-lg text-sm border-none focus:ring-2 focus:ring-primary/20 min-h-[100px]" placeholder="Apresente os resultados finais e encaminhamentos recomendados..."></textarea>
            </section>

            <div class="text-center mt-20">
                <div class="w-64 h-px bg-gray-300 mx-auto mb-2"></div>
                <p class="text-sm font-bold text-gray-800">Psicólogo(a) Responsável</p>
                <p class="text-xs text-gray-500">Registro Profissional (CRP)</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
async function gerarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Configurações básicas
    const margin = 14;
    const pageWidth = 210;
    const contentWidth = pageWidth - (margin * 2);
    let y = 20;

    // Cabeçalho
    doc.setFontSize(18);
    doc.setFont('helvetica', 'bold');
    doc.text('LAUDO PSICOLÓGICO', 105, y, { align: 'center' });
    y += 5;
    doc.setLineWidth(0.5);
    doc.line(margin, y, pageWidth - margin, y);
    y += 15;

    // 1. Identificação
    doc.setFontSize(12); doc.setFont('helvetica', 'bold');
    doc.text('1. IDENTIFICAÇÃO', margin, y); y += 8;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
    doc.text('Paciente: <?php echo addslashes($paciente['nome']); ?>', margin, y);
    doc.text('CPF: <?php echo addslashes($paciente['cpf']); ?>', 120, y); y += 6;
    doc.text('Finalidade: ' + document.getElementById('finalidade').value, margin, y); y += 6;
    doc.text('Autor(a): Psicólogo(a) Responsável', margin, y); y += 12;

    // 2. Demanda
    doc.setFontSize(12); doc.setFont('helvetica', 'bold');
    doc.text('2. DESCRIÇÃO DA DEMANDA', margin, y); y += 8;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
    const splitDemanda = doc.splitTextToSize(document.getElementById('demanda').value || 'Demanda não informada.', contentWidth);
    doc.text(splitDemanda, margin, y);
    y += (splitDemanda.length * 5) + 10;

    // 3. Procedimento
    if (y > 250) { doc.addPage(); y = 20; }
    doc.setFontSize(12); doc.setFont('helvetica', 'bold');
    doc.text('3. PROCEDIMENTO', margin, y); y += 8;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
    doc.text('Os procedimentos realizados foram:', margin, y); y += 6;
    <?php foreach ($anamneses as $a): ?>
    doc.text('• Entrevista de Anamnese (<?php echo ucfirst($a['tipo_anamnese']); ?>) - <?php echo date('d/m/Y', strtotime($a['created_at'])); ?>', margin + 5, y); y += 6;
    <?php endforeach; ?>
    <?php foreach ($testes as $t): ?>
    doc.text('• Aplicação de instrumento técnico: <?php echo $t['tipo_teste']; ?> - <?php echo date('d/m/Y', strtotime($t['created_at'])); ?>', margin + 5, y); y += 6;
    <?php endforeach; ?>
    doc.text('• Observação clínica e escuta analítica.', margin + 5, y); y += 12;

    // 4. Análise
    if (y > 250) { doc.addPage(); y = 20; }
    doc.setFontSize(12); doc.setFont('helvetica', 'bold');
    doc.text('4. ANÁLISE', margin, y); y += 8;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
    const splitAnalise = doc.splitTextToSize(document.getElementById('analise').value || 'Análise técnica em branco.', contentWidth);
    doc.text(splitAnalise, margin, y);
    y += (splitAnalise.length * 5) + 10;

    // 5. Conclusão
    if (y > 250) { doc.addPage(); y = 20; }
    doc.setFontSize(12); doc.setFont('helvetica', 'bold');
    doc.text('5. CONCLUSÃO', margin, y); y += 8;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10);
    const splitConclusao = doc.splitTextToSize(document.getElementById('conclusao').value || 'Conclusão não informada.', contentWidth);
    doc.text(splitConclusao, margin, y);
    y += (splitConclusao.length * 5) + 25;

    // Assinatura
    if (y > 250) { doc.addPage(); y = 50; }
    doc.setLineWidth(0.2);
    doc.line(margin + 50, y, pageWidth - margin - 50, y); y += 5;
    doc.setFontSize(10); doc.setFont('helvetica', 'bold');
    doc.text('Psicólogo(a) Responsável', 105, y, { align: 'center' }); y += 5;
    doc.setFont('helvetica', 'normal');
    doc.text('Registro Profissional (CRP)', 105, y, { align: 'center' });

    // Roda pé em todas
    const pages = doc.internal.getNumberOfPages();
    for(let i=1; i<=pages; i++) {
        doc.setPage(i);
        doc.setFontSize(8); doc.setTextColor(150);
        doc.text('PsiManager v3.0 - Documento Confidencial conforme Resolução CFP nº 06/2019', 105, 285, { align: 'center' });
        doc.text('Página ' + i + ' de ' + pages, 196, 285, { align: 'right' });
    }

    doc.save('Laudo_Psicologico_<?php echo $paciente['nome']; ?>.pdf');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
