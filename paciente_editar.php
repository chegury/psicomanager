<?php 
require_once __DIR__ . '/config/db.php';
$id = $_GET['id'] ?? null;
if (!$id) { echo "<script>window.location.href='pacientes.php';</script>"; exit; }

// Deletar Laudo
if (isset($_GET['del_laudo'])) {
    $laudoId = intval($_GET['del_laudo']);
    $pdo->prepare("DELETE FROM testes_resultados WHERE id = ? AND paciente_id = ?")->execute([$laudoId, $id]);
    header("Location: paciente_editar.php?id=$id&msg=laudo_del");
    exit;
}

// Deletar Anamnese
if (isset($_GET['del_anamnese'])) {
    $anaId = intval($_GET['del_anamnese']);
    $pdo->prepare("DELETE FROM anamneses_resultados WHERE id = ? AND paciente_id = ?")->execute([$anaId, $id]);
    header("Location: paciente_editar.php?id=$id&msg=ana_del");
    exit;
}

require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php'; 

$msgType = null; $msgContent = null;
if (isset($_GET['msg']) && $_GET['msg'] === 'laudo_del') {
    $msgType = 'success'; $msgContent = "Laudo excluído com sucesso!";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'ana_del') {
    $msgType = 'success'; $msgContent = "Anamnese excluída com sucesso!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $whatsapp = cleanNumber($_POST['whatsapp']);
    $email = $_POST['email'];
    $cpf = cleanNumber($_POST['cpf']);
    $diaSemana = $_POST['dia_semana_fixo'];
    $horario = $_POST['horario_fixo'];
    $valor = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor_sessao']);
    $diaVencimento = $_POST['dia_vencimento'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $freqCombo = $_POST['frequencia_combo'];
    $frequencia = 'semanal'; $semanaInicio = 1;
    if ($freqCombo === 'quinzenal_impar') { $frequencia = 'quinzenal'; $semanaInicio = 1; }
    elseif ($freqCombo === 'quinzenal_par') { $frequencia = 'quinzenal'; $semanaInicio = 0; }

    $fotoSql = "";
    $params = [$nome, $whatsapp, $email, $cpf, $diaSemana, $horario, $valor, $diaVencimento, $ativo, $frequencia, $semanaInicio, $id];
    
    $uploadError = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $novoNome = "paciente_" . $id . "_" . time() . "." . $ext;
            $destino = __DIR__ . '/public/uploads/pacientes/';
            
            if (!is_dir($destino)) { mkdir($destino, 0777, true); }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino . $novoNome)) {
                $fotoSql = ", foto = ?";
                array_splice($params, 11, 0, $novoNome);
            } else { $uploadError = "Falha ao mover o arquivo."; }
        } else { $uploadError = "Erro no upload: Código " . $_FILES['foto']['error']; }
    }

    $sql = "UPDATE pacientes SET nome=?, whatsapp=?, email=?, cpf=?, dia_semana_fixo=?, horario_fixo=?, valor_sessao=?, dia_vencimento=?, ativo=?, frequencia=?, semana_inicio=? $fotoSql WHERE id=?";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute($params);
        $msgType = 'success'; $msgContent = "Dados atualizados com sucesso!";
        if ($uploadError) { $msgType = 'warning'; $msgContent .= " Porém, houve um erro na foto: $uploadError"; }

        if ($ativo == 0) {
            $stmtDel = $pdo->prepare("DELETE FROM agenda WHERE paciente_id = ? AND data_sessao > NOW()");
            $stmtDel->execute([$id]);
            $deletedCount = $stmtDel->rowCount();
            if ($deletedCount > 0) $msgContent .= " $deletedCount agendamentos futuros foram removidos.";
        } else {
            $pdo->prepare("DELETE FROM agenda WHERE paciente_id = ? AND data_sessao > NOW()")->execute([$id]);
            require_once __DIR__ . '/classes/CalendarSyncService.php';
            $calendarService = new CalendarSyncService($pdo);
            $qtd1 = $calendarService->generateAndSync($id, 0, date('m'), date('Y'));
            $qtd2 = $calendarService->generateAndSync($id, 0, date('m', strtotime('+1 month')), date('Y', strtotime('+1 month')));
            $totalGerado = $qtd1 + $qtd2;
            if ($totalGerado > 0) $msgContent .= " Agenda atualizada com $totalGerado sessões.";
        }
    } catch (PDOException $e) { $msgType = 'error'; $msgContent = "Erro ao atualizar: " . $e->getMessage(); }
}

$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch();

if (!$paciente) { echo "<script>alert('Paciente não encontrado.'); window.location.href='pacientes.php';</script>"; exit; }
?>

<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg"><i class="fas fa-user-edit text-lg"></i></span>
            Editar Paciente
        </h2>
        <p class="page-subtitle">Atualize os dados de <?php echo htmlspecialchars($paciente['nome']); ?></p>
    </div>
    <a href="pacientes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Voltar</span></a>
</div>

<?php if ($msgType): ?>
<div class="mb-6 p-4 rounded-xl flex items-center gap-3 animate-fade-in
    <?php echo $msgType === 'success' ? 'bg-green-50 border border-green-200' : ''; ?>
    <?php echo $msgType === 'warning' ? 'bg-yellow-50 border border-yellow-200' : ''; ?>
    <?php echo $msgType === 'error' ? 'bg-red-50 border border-red-200' : ''; ?>">
    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
        <?php echo $msgType === 'success' ? 'bg-green-100' : ''; ?>
        <?php echo $msgType === 'warning' ? 'bg-yellow-100' : ''; ?>
        <?php echo $msgType === 'error' ? 'bg-red-100' : ''; ?>">
        <i class="fas <?php echo $msgType === 'success' ? 'fa-check text-success' : ($msgType === 'warning' ? 'fa-exclamation-triangle text-warning' : 'fa-times text-danger'); ?>"></i>
    </div>
    <p class="font-medium <?php echo $msgType === 'success' ? 'text-green-800' : ($msgType === 'warning' ? 'text-yellow-800' : 'text-red-800'); ?>">
        <?php echo $msgContent; ?>
    </p>
</div>
<?php endif; ?>

<div class="card-shadow overflow-hidden">
    <form method="POST" enctype="multipart/form-data" id="form-editar">
        <div class="p-6 md:p-8 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="relative group">
                    <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-xl bg-gradient-to-br from-primary/10 to-accent/10">
                        <?php if (!empty($paciente['foto']) && file_exists(__DIR__ . '/public/uploads/pacientes/' . $paciente['foto'])): ?>
                            <img class="w-full h-full object-cover" src="public/uploads/pacientes/<?php echo $paciente['foto']; ?>" alt="Foto atual" id="preview-foto">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center" id="preview-placeholder"><i class="fas fa-camera text-4xl text-primary/40"></i></div>
                            <img class="w-full h-full object-cover hidden" src="" alt="Preview" id="preview-foto">
                        <?php endif; ?>
                    </div>
                    <label class="absolute bottom-0 right-0 w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white cursor-pointer shadow-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-camera text-sm"></i>
                        <input type="file" name="foto" accept="image/*" class="hidden" id="input-foto">
                    </label>
                </div>
                <div class="text-center sm:text-left">
                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($paciente['nome']); ?></h3>
                    <p class="text-gray-500 text-sm flex items-center justify-center sm:justify-start gap-2 mt-1"><i class="fab fa-whatsapp text-green-500"></i> <?php echo htmlspecialchars($paciente['whatsapp']); ?></p>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-user text-sm"></i></span> Dados Pessoais
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-text-main mb-2">Nome Completo *</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($paciente['nome']); ?>" required class="form-input">
                </div>
                <div><label class="block text-sm font-bold text-text-main mb-2">CPF</label><input type="text" name="cpf" value="<?php echo htmlspecialchars($paciente['cpf']); ?>" class="form-input cpf-mask"></div>
                <div><label class="block text-sm font-bold text-text-main mb-2">WhatsApp *</label><input type="text" name="whatsapp" value="<?php echo htmlspecialchars($paciente['whatsapp']); ?>" required class="form-input phone-mask"></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-text-main mb-2">Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($paciente['email']); ?>" class="form-input"></div>
            </div>
        </div>

        <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-file-contract text-sm"></i></span> Configuração do Contrato
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Dia da Sessão *</label>
                    <select name="dia_semana_fixo" class="form-input">
                        <option value="1" <?php echo $paciente['dia_semana_fixo']==1?'selected':''; ?>>Segunda-feira</option>
                        <option value="2" <?php echo $paciente['dia_semana_fixo']==2?'selected':''; ?>>Terça-feira</option>
                        <option value="3" <?php echo $paciente['dia_semana_fixo']==3?'selected':''; ?>>Quarta-feira</option>
                        <option value="4" <?php echo $paciente['dia_semana_fixo']==4?'selected':''; ?>>Quinta-feira</option>
                        <option value="5" <?php echo $paciente['dia_semana_fixo']==5?'selected':''; ?>>Sexta-feira</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Frequência</label>
                    <select name="frequencia_combo" class="form-input">
                        <option value="semanal" <?php echo $paciente['frequencia']=='semanal'?'selected':''; ?>>Semanal</option>
                        <option value="quinzenal_impar" <?php echo ($paciente['frequencia']=='quinzenal'&&$paciente['semana_inicio']==1)?'selected':''; ?>>Quinzenal (Ímpar)</option>
                        <option value="quinzenal_par" <?php echo ($paciente['frequencia']=='quinzenal'&&$paciente['semana_inicio']==0)?'selected':''; ?>>Quinzenal (Par)</option>
                    </select>
                </div>
                <div><label class="block text-sm font-bold text-text-main mb-2">Horário *</label><input type="time" name="horario_fixo" value="<?php echo $paciente['horario_fixo']; ?>" required class="form-input"></div>
                <div><label class="block text-sm font-bold text-text-main mb-2">Valor Sessão (R$) *</label><input type="text" name="valor_sessao" value="<?php echo number_format($paciente['valor_sessao'], 2, ',', '.'); ?>" required class="form-input money-mask"></div>
                <div><label class="block text-sm font-bold text-text-main mb-2">Dia Vencimento *</label><input type="number" name="dia_vencimento" min="1" max="31" value="<?php echo $paciente['dia_vencimento']; ?>" required class="form-input"></div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Status</label>
                    <label class="relative inline-flex items-center cursor-pointer mt-2">
                        <input type="checkbox" name="ativo" value="1" class="sr-only peer" <?php echo $paciente['ativo']?'checked':''; ?>>
                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ms-3 text-sm font-semibold <?php echo $paciente['ativo']?'text-success':'text-gray-500'; ?>">
                            <?php echo $paciente['ativo']?'Ativo':'Inativo'; ?>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 border-b border-gray-100">
            <?php 
            $stmtTestes = $pdo->prepare("SELECT * FROM testes_resultados WHERE paciente_id = ? ORDER BY created_at DESC");
            $stmtTestes->execute([$id]);
            $testesPaciente = $stmtTestes->fetchAll();
            
            if (empty($testesPaciente)): 
            ?>
                <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <p class="text-gray-400 text-sm">Nenhum teste realizado para este paciente.</p>
                    <a href="testes.php" class="text-primary font-bold text-sm hover:underline mt-2 inline-block">Aplicar Teste Agora</a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($testesPaciente as $t): 
                        $badgeClass = match($t['classificacao']) {
                            'Mínimo', 'Normal', 'Baixo', 'Improvável' => 'badge-success',
                            'Leve', 'Sugestivo', 'Média' => 'badge-warning',
                            'Moderado' => 'badge-info',
                            'Alto', 'Grave', 'Baixa', 'Altamente Provável' => 'badge-danger',
                            default => 'badge-neutral'
                        };
                    ?>
                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" name="selecionar_teste[]" value="<?php echo $t['id']; ?>" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary/20">
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold">
                                    <?php echo $t['tipo_teste']; ?>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800"><?php echo formatDateTime($t['created_at']); ?></p>
                                    <p class="text-[10px] text-gray-500 mb-1">Solicitante: <?php echo htmlspecialchars($t['solicitante'] ?? 'O Próprio'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $t['pontuacao']; ?> pts • <span class="badge <?php echo $badgeClass; ?> text-[10px] py-0.5"><?php echo $t['classificacao']; ?></span></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="teste_aplicar.php?tipo=<?php echo $t['tipo_teste']; ?>&id_resultado=<?php echo $t['id']; ?>&simple=1" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-primary hover:text-white transition-all" title="Ver Correção Técnica">
                                    <i class="fas fa-file-invoice text-sm"></i>
                                </a>
                                <a href="?id=<?php echo $id; ?>&del_laudo=<?php echo $t['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all" title="Excluir" onclick="return confirm('Excluir permanentemente?')">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <div class="p-6 md:p-8 border-b border-gray-100">
            <h3 class="text-lg font-bold text-secondary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-secondary/10 rounded-lg flex items-center justify-center"><i class="fas fa-file-signature text-sm"></i></span> Histórico de Anamneses
            </h3>
            <?php 
            $stmtAna = $pdo->prepare("SELECT * FROM anamneses_resultados WHERE paciente_id = ? ORDER BY created_at DESC");
            $stmtAna->execute([$id]);
            $anamnesesPaciente = $stmtAna->fetchAll();
            
            if (empty($anamnesesPaciente)): 
            ?>
                <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <p class="text-gray-400 text-sm">Nenhuma anamnese realizada para este paciente.</p>
                    <a href="anamneses.php" class="text-secondary font-bold text-sm hover:underline mt-2 inline-block">Realizar Anamnese Agora</a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($anamnesesPaciente as $a): ?>
                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" name="selecionar_anamnese[]" value="<?php echo $a['id']; ?>" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary/20">
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary">
                                    <i class="fas <?php echo ($a['tipo_anamnese'] === 'adulto' ? 'fa-user-tie' : 'fa-user-graduate'); ?>"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800"><?php echo formatDateTime($a['created_at']); ?></p>
                                    <p class="text-xs text-gray-500 uppercase font-black tracking-tighter">Anamnese <?php echo ucfirst($a['tipo_anamnese']); ?></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="?id=<?php echo $id; ?>&del_anamnese=<?php echo $a['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all" title="Excluir" onclick="return confirm('Excluir?')">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botão Novo: Gerar Laudo Combinado -->
        <div class="p-6 md:p-8 bg-primary/5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-primary">Gerador de Laudo Oficial</h3>
                <p class="text-xs text-gray-500">Selecione os documentos acima para compor o laudo final (CRP).</p>
            </div>
            <button type="button" onclick="gerarLaudoCombinado()" class="btn btn-primary shadow-lg shadow-primary/30">
                <i class="fas fa-file-medical text-lg"></i> Gerar Laudo Selecionado
            </button>
        </div>


        <div class="p-6 md:p-8 bg-gray-50">
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="pacientes.php" class="btn btn-secondary w-full sm:w-auto justify-center"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto justify-center" id="btn-submit"><i class="fas fa-save"></i> Salvar Alterações</button>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    $('.phone-mask').mask('(00) 00000-0000');
    $('.cpf-mask').mask('000.000.000-00', {reverse: true});
    $('.money-mask').mask('#.##0,00', {reverse: true});
});
document.getElementById('input-foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-foto');
            const placeholder = document.getElementById('preview-placeholder');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
});
document.querySelector('input[name="ativo"]').addEventListener('change', function() {
    const label = this.parentElement.querySelector('span:last-child');
    if (this.checked) { label.textContent = 'Ativo'; label.className = 'ms-3 text-sm font-semibold text-success'; }
    else { label.textContent = 'Inativo'; label.className = 'ms-3 text-sm font-semibold text-gray-500'; }
});
document.getElementById('form-editar').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.classList.add('btn-loading');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
});

function gerarLaudoCombinado() {
    const testes = Array.from(document.querySelectorAll('input[name="selecionar_teste[]"]:checked')).map(el => el.value);
    const anamneses = Array.from(document.querySelectorAll('input[name="selecionar_anamnese[]"]:checked')).map(el => el.value);
    
    if (testes.length === 0 && anamneses.length === 0) {
        alert("Por favor, selecione ao menos um teste ou uma anamnese para compor o laudo.");
        return;
    }
    
    const url = `laudo_gerar.php?paciente_id=<?php echo $id; ?>&testes=${testes.join(',')}&anamneses=${anamneses.join(',')}`;
    window.open(url, '_blank');
}
</script>


<?php include __DIR__ . '/includes/footer.php'; ?>
