<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php'; 

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script>window.location.href='pacientes.php';</script>";
    exit;
}

// Processar Formulário
$msgType = null;
$msgContent = null;

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

    // Processar Frequência
    $freqCombo = $_POST['frequencia_combo'];
    $frequencia = 'semanal';
    $semanaInicio = 1;

    if ($freqCombo === 'quinzenal_impar') {
        $frequencia = 'quinzenal';
        $semanaInicio = 1;
    } elseif ($freqCombo === 'quinzenal_par') {
        $frequencia = 'quinzenal';
        $semanaInicio = 0;
    }

    // Upload de Foto
    $fotoSql = "";
    $params = [$nome, $whatsapp, $email, $cpf, $diaSemana, $horario, $valor, $diaVencimento, $ativo, $frequencia, $semanaInicio, $id];
    
    $uploadError = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $novoNome = "paciente_" . $id . "_" . time() . "." . $ext;
            $destino = __DIR__ . '/uploads/pacientes/';
            
            if (!is_dir($destino)) {
                if (!mkdir($destino, 0777, true)) {
                    $uploadError = "Falha ao criar diretório de upload.";
                }
            }
            
            if (!$uploadError && move_uploaded_file($_FILES['foto']['tmp_name'], $destino . $novoNome)) {
                $fotoSql = ", foto = ?";
                array_splice($params, 11, 0, $novoNome);
            } else {
                $uploadError = "Falha ao mover o arquivo.";
            }
        } else {
            $uploadError = "Erro no upload: Código " . $_FILES['foto']['error'];
        }
    }

    $sql = "UPDATE pacientes SET nome=?, whatsapp=?, email=?, cpf=?, dia_semana_fixo=?, horario_fixo=?, valor_sessao=?, dia_vencimento=?, ativo=?, frequencia=?, semana_inicio=? $fotoSql WHERE id=?";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute($params);
        $msgType = 'success';
        $msgContent = "Dados atualizados com sucesso!";
        if ($uploadError) {
            $msgType = 'warning';
            $msgContent .= " Porém, houve um erro na foto: $uploadError";
        }

        // Se inativou o paciente, excluir agendamentos futuros
        if ($ativo == 0) {
            $stmtDel = $pdo->prepare("DELETE FROM agenda WHERE paciente_id = ? AND data_sessao > NOW()");
            $stmtDel->execute([$id]);
            $deletedCount = $stmtDel->rowCount();
            if ($deletedCount > 0) {
                $msgContent .= " $deletedCount agendamentos futuros foram removidos.";
            }
        } else {
            // Se paciente ativo, regenerar agenda com os novos dados
            // Primeiro remove agendamentos futuros para evitar duplicatas
            $stmtDelFuturos = $pdo->prepare("DELETE FROM agenda WHERE paciente_id = ? AND data_sessao > NOW()");
            $stmtDelFuturos->execute([$id]);
            
            // Depois gera novamente
            require_once '../classes/CalendarSyncService.php';
            $calendarService = new CalendarSyncService($pdo);
            
            $mesAtual = date('m');
            $anoAtual = date('Y');
            $proximoMes = date('m', strtotime('+1 month'));
            $proximoAno = date('Y', strtotime('+1 month'));
            
            $qtd1 = $calendarService->generateAndSync($id, 0, $mesAtual, $anoAtual);
            $qtd2 = $calendarService->generateAndSync($id, 0, $proximoMes, $proximoAno);
            
            $totalGerado = $qtd1 + $qtd2;
            if ($totalGerado > 0) {
                $msgContent .= " Agenda atualizada com $totalGerado sessões.";
            }
        }
    } catch (PDOException $e) {
        $msgType = 'error';
        $msgContent = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Buscar dados atuais
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch();

if (!$paciente) {
    echo "<script>alert('Paciente não encontrado.'); window.location.href='pacientes.php';</script>";
    exit;
}
?>

<!-- Page Header -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-user-edit text-lg"></i>
            </span>
            Editar Paciente
        </h2>
        <p class="page-subtitle">Atualize os dados de <?php echo htmlspecialchars($paciente['nome']); ?></p>
    </div>
    <a href="pacientes.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        <span class="hidden sm:inline">Voltar</span>
    </a>
</div>

<!-- Alerts -->
<?php if ($msgType): ?>
    <div class="mb-6 p-4 rounded-xl flex items-center gap-3 animate-fade-in
        <?php echo $msgType === 'success' ? 'bg-green-50 border border-green-200' : ''; ?>
        <?php echo $msgType === 'warning' ? 'bg-yellow-50 border border-yellow-200' : ''; ?>
        <?php echo $msgType === 'error' ? 'bg-red-50 border border-red-200' : ''; ?>">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
            <?php echo $msgType === 'success' ? 'bg-green-100' : ''; ?>
            <?php echo $msgType === 'warning' ? 'bg-yellow-100' : ''; ?>
            <?php echo $msgType === 'error' ? 'bg-red-100' : ''; ?>">
            <i class="fas 
                <?php echo $msgType === 'success' ? 'fa-check text-success' : ''; ?>
                <?php echo $msgType === 'warning' ? 'fa-exclamation-triangle text-warning' : ''; ?>
                <?php echo $msgType === 'error' ? 'fa-times text-danger' : ''; ?>"></i>
        </div>
        <p class="font-medium
            <?php echo $msgType === 'success' ? 'text-green-800' : ''; ?>
            <?php echo $msgType === 'warning' ? 'text-yellow-800' : ''; ?>
            <?php echo $msgType === 'error' ? 'text-red-800' : ''; ?>">
            <?php echo $msgContent; ?>
        </p>
    </div>
<?php endif; ?>

<div class="card-shadow overflow-hidden">
    <form method="POST" enctype="multipart/form-data" id="form-editar">
        
        <!-- Photo Upload Section -->
        <div class="p-6 md:p-8 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="relative group">
                    <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-xl bg-gradient-to-br from-primary/10 to-accent/10">
                        <?php if (!empty($paciente['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $paciente['foto'])): ?>
                            <img class="w-full h-full object-cover" src="uploads/pacientes/<?php echo $paciente['foto']; ?>" alt="Foto atual" id="preview-foto">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center" id="preview-placeholder">
                                <i class="fas fa-camera text-4xl text-primary/40"></i>
                            </div>
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
                    <p class="text-gray-500 text-sm flex items-center justify-center sm:justify-start gap-2 mt-1">
                        <i class="fab fa-whatsapp text-green-500"></i>
                        <?php echo htmlspecialchars($paciente['whatsapp']); ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">Clique no ícone da câmera para alterar a foto</p>
                </div>
            </div>
        </div>

        <!-- Personal Data Section -->
        <div class="p-6 md:p-8 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user text-sm"></i>
                </span>
                Dados Pessoais
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-text-main mb-2">Nome Completo *</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($paciente['nome']); ?>" required class="form-input">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">CPF</label>
                    <input type="text" name="cpf" value="<?php echo htmlspecialchars($paciente['cpf']); ?>" class="form-input cpf-mask">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">WhatsApp *</label>
                    <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($paciente['whatsapp']); ?>" required class="form-input phone-mask">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-text-main mb-2">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($paciente['email']); ?>" class="form-input">
                </div>
            </div>
        </div>

        <!-- Contract Settings Section -->
        <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-contract text-sm"></i>
                </span>
                Configuração do Contrato
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Dia da Sessão *</label>
                    <select name="dia_semana_fixo" class="form-input">
                        <option value="1" <?php echo $paciente['dia_semana_fixo'] == 1 ? 'selected' : ''; ?>>Segunda-feira</option>
                        <option value="2" <?php echo $paciente['dia_semana_fixo'] == 2 ? 'selected' : ''; ?>>Terça-feira</option>
                        <option value="3" <?php echo $paciente['dia_semana_fixo'] == 3 ? 'selected' : ''; ?>>Quarta-feira</option>
                        <option value="4" <?php echo $paciente['dia_semana_fixo'] == 4 ? 'selected' : ''; ?>>Quinta-feira</option>
                        <option value="5" <?php echo $paciente['dia_semana_fixo'] == 5 ? 'selected' : ''; ?>>Sexta-feira</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Frequência</label>
                    <select name="frequencia_combo" class="form-input">
                        <option value="semanal" <?php echo $paciente['frequencia'] == 'semanal' ? 'selected' : ''; ?>>Semanal</option>
                        <option value="quinzenal_impar" <?php echo ($paciente['frequencia'] == 'quinzenal' && $paciente['semana_inicio'] == 1) ? 'selected' : ''; ?>>Quinzenal (Ímpar)</option>
                        <option value="quinzenal_par" <?php echo ($paciente['frequencia'] == 'quinzenal' && $paciente['semana_inicio'] == 0) ? 'selected' : ''; ?>>Quinzenal (Par)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Horário *</label>
                    <input type="time" name="horario_fixo" value="<?php echo $paciente['horario_fixo']; ?>" required class="form-input">
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Valor Sessão (R$) *</label>
                    <input type="text" name="valor_sessao" value="<?php echo number_format($paciente['valor_sessao'], 2, ',', '.'); ?>" required class="form-input money-mask">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Dia Vencimento *</label>
                    <input type="number" name="dia_vencimento" min="1" max="31" value="<?php echo $paciente['dia_vencimento']; ?>" required class="form-input">
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Status do Paciente</label>
                    <label class="relative inline-flex items-center cursor-pointer mt-2">
                        <input type="checkbox" name="ativo" value="1" class="sr-only peer" <?php echo $paciente['ativo'] ? 'checked' : ''; ?>>
                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ms-3 text-sm font-semibold <?php echo $paciente['ativo'] ? 'text-success' : 'text-gray-500'; ?>">
                            <?php echo $paciente['ativo'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </label>
                </div>
            </div>
            
            <?php if ($paciente['ativo']): ?>
            <div class="mt-5 p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border border-yellow-200 flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
                <p class="text-sm text-yellow-800">
                    <strong>Atenção:</strong> Ao inativar o paciente, todos os agendamentos futuros serão automaticamente removidos.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Submit Button -->
        <div class="p-6 md:p-8 bg-gray-50">
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="pacientes.php" class="btn btn-secondary w-full sm:w-auto justify-center">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto justify-center" id="btn-submit">
                    <i class="fas fa-save"></i>
                    Salvar Alterações
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Máscaras
    $(document).ready(function(){
        $('.phone-mask').mask('(00) 00000-0000');
        $('.cpf-mask').mask('000.000.000-00', {reverse: true});
        $('.money-mask').mask('#.##0,00', {reverse: true});
    });

    // Preview de foto
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

    // Toggle status label
    document.querySelector('input[name="ativo"]').addEventListener('change', function() {
        const label = this.parentElement.querySelector('span:last-child');
        if (this.checked) {
            label.textContent = 'Ativo';
            label.classList.remove('text-gray-500');
            label.classList.add('text-success');
        } else {
            label.textContent = 'Inativo';
            label.classList.remove('text-success');
            label.classList.add('text-gray-500');
        }
    });

    // Form submission with loading state
    document.getElementById('form-editar').addEventListener('submit', function(e) {
        const btn = document.getElementById('btn-submit');
        btn.classList.add('btn-loading');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    });
</script>

<?php include '../includes/footer.php'; ?>
