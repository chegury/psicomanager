<?php 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php'; 

// Definição do Mês de Referência
$mesRef = $_GET['mes'] ?? date('Y-m');
$dataRef = DateTime::createFromFormat('Y-m', $mesRef);

// Navegação de Meses
$mesAnterior = (clone $dataRef)->modify('-1 month')->format('Y-m');
$proximoMes = (clone $dataRef)->modify('+1 month')->format('Y-m');

// Formatação para exibição (Português)
$mesesPT = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];
$mesNome = $mesesPT[$dataRef->format('m')] ?? $dataRef->format('F');
$anoRef = $dataRef->format('Y');

// Buscar sessões do mês selecionado
$stmtAgenda = $pdo->prepare("
    SELECT a.*, p.nome, p.foto, p.whatsapp 
    FROM agenda a 
    JOIN pacientes p ON a.paciente_id = p.id 
    WHERE DATE_FORMAT(a.data_sessao, '%Y-%m') = ? 
    AND p.ativo = 1 
    ORDER BY a.data_sessao ASC
");
$stmtAgenda->execute([$mesRef]);
$agendaMes = $stmtAgenda->fetchAll(PDO::FETCH_ASSOC);

// Agrupar agenda por dia
$agendaPorDia = [];
foreach ($agendaMes as $evento) {
    $dia = date('d', strtotime($evento['data_sessao']));
    $agendaPorDia[$dia][] = $evento;
}

// Contar total de sessões
$totalSessoes = count($agendaMes);
?>

<!-- Page Header -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-calendar-alt text-lg"></i>
            </span>
            Agenda Completa
        </h2>
        <p class="page-subtitle">Visualização mensal de sessões</p>
    </div>
</div>

<!-- Controls Bar -->
<div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-6">
    <!-- Month Navigator -->
    <div class="flex items-center bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <a href="?mes=<?php echo $mesAnterior; ?>" 
           class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors">
            <i class="fas fa-chevron-left"></i>
        </a>
        <div class="px-6 py-3 min-w-[200px] text-center border-x border-gray-100">
            <h2 class="text-lg font-bold text-text-main capitalize">
                <?php echo "$mesNome de $anoRef"; ?>
            </h2>
            <p class="text-xs text-gray-400"><?php echo $totalSessoes; ?> sessões agendadas</p>
        </div>
        <a href="?mes=<?php echo $proximoMes; ?>" 
           class="px-4 py-3 text-primary hover:bg-primary hover:text-white transition-colors">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>

    <!-- View Toggle -->
    <div class="bg-white p-1 rounded-xl shadow-md border border-gray-100 flex">
        <button onclick="setView('list')" id="btn-list" 
                class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all bg-primary text-white">
            <i class="fas fa-list mr-2"></i>Lista
        </button>
        <button onclick="setView('kanban')" id="btn-kanban" 
                class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all text-gray-500 hover:bg-gray-100">
            <i class="fas fa-columns mr-2"></i>Colunas
        </button>
    </div>
</div>

<!-- Agenda Container -->
<div id="agenda-container" class="transition-all duration-300">
    <?php if (empty($agendaPorDia)): ?>
        <div class="card-shadow">
            <div class="empty-state py-16">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <p class="empty-state-title">Sem agendamentos</p>
                <p class="empty-state-text">Nenhum agendamento encontrado para este mês</p>
            </div>
        </div>
    <?php else: ?>
        <!-- View: LISTA -->
        <div id="view-list" class="space-y-4">
            <?php foreach ($agendaPorDia as $dia => $eventos): ?>
                <div class="card-shadow p-4 sm:p-6">
                    <div class="flex gap-4 sm:gap-6">
                        <!-- Day Indicator -->
                        <div class="flex-shrink-0 w-16 text-center">
                            <span class="block text-3xl sm:text-4xl font-extrabold text-primary"><?php echo $dia; ?></span>
                            <span class="block text-xs text-gray-400 uppercase font-bold tracking-wide">
                                <?php echo getDayName(date('N', strtotime($eventos[0]['data_sessao']))); ?>
                            </span>
                        </div>
                        
                        <!-- Events List -->
                        <div class="flex-1 border-l-2 border-primary/20 pl-4 sm:pl-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                <?php foreach ($eventos as $ev): 
                                    $whatsappClean = preg_replace('/\D/', '', $ev['whatsapp']);
                                    $meetLink = $ev['link_meet'] ?? '';
                                ?>
                                    <div id="appt-<?php echo $ev['id']; ?>" 
                                         draggable="true" 
                                         ondragstart="drag(event)" 
                                         ondragend="dragEnd(event)"
                                         class="group relative bg-gradient-to-r from-gray-50 to-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-lg hover:border-primary/30 transition-all cursor-move">
                                        
                                        <!-- Delete Button -->
                                        <button onclick="deleteAppointment(<?php echo $ev['id']; ?>)" 
                                                class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-red-50 text-red-400 hover:bg-danger hover:text-white opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center"
                                                title="Excluir agendamento">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>

                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="avatar avatar-md ring-2 ring-white shadow">
                                                <?php if (!empty($ev['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $ev['foto'])): ?>
                                                    <img src="uploads/pacientes/<?php echo $ev['foto']; ?>" alt="<?php echo htmlspecialchars($ev['nome']); ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-user text-xs"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($ev['nome']); ?></p>
                                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                                    <i class="far fa-clock text-primary"></i>
                                                    <?php echo date('H:i', strtotime($ev['data_sessao'])); ?>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                            <div class="flex gap-2">
                                                <?php if ($meetLink): ?>
                                                    <a href="<?php echo $meetLink; ?>" target="_blank" 
                                                       class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all flex items-center justify-center"
                                                       title="Entrar no Meet">
                                                        <i class="fas fa-video text-sm"></i>
                                                    </a>
                                                    <a href="https://wa.me/<?php echo $whatsappClean; ?>?text=<?php echo urlencode("Olá {$ev['nome']}, segue o link para nossa sessão: $meetLink"); ?>" 
                                                       target="_blank" 
                                                       class="w-8 h-8 rounded-lg bg-green-50 text-green-500 hover:bg-green-500 hover:text-white transition-all flex items-center justify-center"
                                                       title="Enviar link via WhatsApp">
                                                        <i class="fab fa-whatsapp text-sm"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <a href="https://wa.me/<?php echo $whatsappClean; ?>" target="_blank" 
                                               class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 hover:bg-green-500 hover:text-white transition-all flex items-center justify-center"
                                               title="Conversar no WhatsApp">
                                                <i class="fab fa-whatsapp text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- View: KANBAN (Colunas) -->
        <div id="view-kanban" class="hidden overflow-x-auto pb-4 -mx-4 px-4">
            <div class="flex gap-4 min-w-max">
                <?php foreach ($agendaPorDia as $dia => $eventos): 
                    $dataCompleta = date('Y-m-d', strtotime($eventos[0]['data_sessao']));
                ?>
                    <div class="w-72 sm:w-80 bg-gray-50/80 rounded-2xl border border-gray-200 flex-shrink-0 flex flex-col max-h-[75vh]" 
                         ondragover="allowDrop(event)" 
                         ondrop="drop(event, '<?php echo $dataCompleta; ?>')">
                        
                        <!-- Column Header -->
                        <div class="p-4 bg-white rounded-t-2xl border-b border-gray-200 sticky top-0 z-10">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-2xl font-extrabold text-primary"><?php echo $dia; ?></span>
                                    <span class="text-xs font-bold text-gray-400 uppercase ml-2">
                                        <?php echo getDayName(date('N', strtotime($eventos[0]['data_sessao']))); ?>
                                    </span>
                                </div>
                                <span class="badge badge-neutral"><?php echo count($eventos); ?></span>
                            </div>
                        </div>

                        <!-- Cards Container -->
                        <div class="p-3 space-y-3 overflow-y-auto flex-1">
                            <?php foreach ($eventos as $ev): 
                                $whatsappClean = preg_replace('/\D/', '', $ev['whatsapp']);
                                $meetLink = $ev['link_meet'] ?? '';
                            ?>
                                <div id="appt-<?php echo $ev['id']; ?>" 
                                     draggable="true" 
                                     ondragstart="drag(event)" 
                                     ondragend="dragEnd(event)"
                                     class="group relative bg-white border border-gray-200 rounded-xl p-3 shadow-sm hover:shadow-md hover:border-primary/30 transition-all cursor-move">
                                    
                                    <!-- Delete Button -->
                                    <button onclick="deleteAppointment(<?php echo $ev['id']; ?>)" 
                                            class="absolute top-2 right-2 w-6 h-6 rounded-lg bg-red-50 text-red-400 hover:bg-danger hover:text-white opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>

                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm">
                                            <?php if (!empty($ev['foto']) && file_exists(__DIR__ . '/uploads/pacientes/' . $ev['foto'])): ?>
                                                <img src="uploads/pacientes/<?php echo $ev['foto']; ?>" alt="">
                                            <?php else: ?>
                                                <i class="fas fa-user text-xs"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($ev['nome']); ?></p>
                                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                                <i class="far fa-clock"></i>
                                                <?php echo date('H:i', strtotime($ev['data_sessao'])); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="flex gap-2 mt-3 pt-2 border-t border-gray-100">
                                        <?php if ($meetLink): ?>
                                            <a href="<?php echo $meetLink; ?>" target="_blank" 
                                               class="flex-1 text-center py-1.5 rounded-lg bg-primary/10 text-primary text-xs font-semibold hover:bg-primary hover:text-white transition-all">
                                                <i class="fas fa-video mr-1"></i> Meet
                                            </a>
                                        <?php endif; ?>
                                        <a href="https://wa.me/<?php echo $whatsappClean; ?>" target="_blank" 
                                           class="flex-1 text-center py-1.5 rounded-lg bg-green-50 text-green-600 text-xs font-semibold hover:bg-green-500 hover:text-white transition-all">
                                            <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // ========== Toggle View Logic ==========
    function setView(view) {
        const list = document.getElementById('view-list');
        const kanban = document.getElementById('view-kanban');
        const btnList = document.getElementById('btn-list');
        const btnKanban = document.getElementById('btn-kanban');

        if (!list || !kanban) return;

        if (view === 'list') {
            list.classList.remove('hidden');
            kanban.classList.add('hidden');
            
            btnList.classList.add('bg-primary', 'text-white');
            btnList.classList.remove('text-gray-500', 'hover:bg-gray-100');
            
            btnKanban.classList.remove('bg-primary', 'text-white');
            btnKanban.classList.add('text-gray-500', 'hover:bg-gray-100');
        } else {
            list.classList.add('hidden');
            kanban.classList.remove('hidden');
            
            btnKanban.classList.add('bg-primary', 'text-white');
            btnKanban.classList.remove('text-gray-500', 'hover:bg-gray-100');
            
            btnList.classList.remove('bg-primary', 'text-white');
            btnList.classList.add('text-gray-500', 'hover:bg-gray-100');
        }
        
        // Save preference
        localStorage.setItem('agenda-view', view);
    }

    // Restore view preference
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('agenda-view');
        if (savedView) {
            setView(savedView);
        }
    });

    // ========== Drag and Drop Logic ==========
    function drag(ev) {
        ev.dataTransfer.setData("text/plain", ev.target.id);
        ev.target.classList.add('opacity-50', 'scale-105');
    }

    function dragEnd(ev) {
        ev.target.classList.remove('opacity-50', 'scale-105');
    }

    function allowDrop(ev) {
        ev.preventDefault();
        ev.currentTarget.classList.add('bg-primary/5', 'border-primary');
    }

    function drop(ev, newDate) {
        ev.preventDefault();
        ev.currentTarget.classList.remove('bg-primary/5', 'border-primary');
        
        var data = ev.dataTransfer.getData("text/plain");
        var draggedElement = document.getElementById(data);
        var dropZone = ev.currentTarget.querySelector('.space-y-3');
        
        if (draggedElement && dropZone) {
            dropZone.appendChild(draggedElement);
            
            const appointmentId = data.replace('appt-', '');
            updateAppointmentDate(appointmentId, newDate);
        }
    }

    function updateAppointmentDate(id, newDate) {
        const formData = new FormData();
        formData.append('action', 'move');
        formData.append('id', id);
        formData.append('new_date', newDate);

        fetch('agenda_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Agendamento movido com sucesso!', 'success');
            } else {
                showToast('Erro ao mover: ' + data.error, 'error');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('Erro de conexão', 'error');
        });
    }

    // ========== Delete Action (BUG FIX) ==========
    function deleteAppointment(id) {
        if (confirm('Tem certeza que deseja excluir este agendamento?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('agenda_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('appt-' + id);
                    if (el) {
                        el.style.transform = 'scale(0.8)';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 200);
                    }
                    showToast('Agendamento excluído com sucesso!', 'success');
                } else {
                    showToast('Erro ao excluir: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro de conexão', 'error');
            });
        }
    }

    // Prevent drop zone highlight from sticking
    document.querySelectorAll('[ondragover]').forEach(el => {
        el.addEventListener('dragleave', function() {
            this.classList.remove('bg-primary/5', 'border-primary');
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
