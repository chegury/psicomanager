<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/EnvLoader.php';
require_once __DIR__ . '/classes/GoogleClientWrapper.php';

EnvLoader::load(__DIR__ . '/.env');
include __DIR__ . '/includes/header.php'; 

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

// Dados para o calendário visual
$primeiroDia = date('N', strtotime("$mesRef-01")); // 1=Seg, 7=Dom
$diasNoMes = cal_days_in_month(CAL_GREGORIAN, intval($dataRef->format('m')), intval($anoRef));
$hoje = date('Y-m-d');
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
    <div class="flex gap-2">
        <a href="google_auth.php" class="btn <?php 
            $google = new GoogleClientWrapper();
            echo $google->isAuthenticated() ? 'btn-success' : 'btn-primary';
        ?>" title="Sincronizar com Google Agenda">
            <i class="fab fa-google"></i>
            <span class="hidden sm:inline">
                <?php echo $google->isAuthenticated() ? 'Conectado' : 'Conectar Google'; ?>
            </span>
        </a>
        <a href="export_calendar.php?mes=<?php echo $mesRef; ?>" class="btn btn-secondary" title="Exportar Calendário (ICS)">
            <i class="fas fa-file-export"></i>
            <span class="hidden sm:inline">Exportar ICS</span>
        </a>
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
        <button onclick="setView('calendar')" id="btn-calendar" 
                class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all bg-primary text-white">
            <i class="fas fa-calendar mr-2"></i>Calendário
        </button>
        <button onclick="setView('list')" id="btn-list" 
                class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all text-gray-500 hover:bg-gray-100">
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

    <!-- View: CALENDÁRIO VISUAL -->
    <div id="view-calendar" class="card-shadow overflow-hidden">
        <!-- Calendar Header -->
        <div class="grid grid-cols-7 bg-gradient-to-r from-primary/5 to-accent/5">
            <?php 
            $diasSemana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
            foreach ($diasSemana as $ds): ?>
                <div class="p-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                    <?php echo $ds; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Calendar Body -->
        <div class="grid grid-cols-7">
            <?php
            // Dias em branco antes do primeiro dia
            for ($i = 1; $i < $primeiroDia; $i++): ?>
                <div class="min-h-[100px] sm:min-h-[120px] p-2 border-b border-r border-gray-50 bg-gray-50/50"></div>
            <?php endfor;
            
            // Dias do mês
            for ($d = 1; $d <= $diasNoMes; $d++):
                $dataAtual = sprintf('%s-%02d', $mesRef, $d);
                $ehHoje = ($dataAtual === $hoje);
                $diaStr = str_pad($d, 2, '0', STR_PAD_LEFT);
                $eventosDia = $agendaPorDia[$diaStr] ?? [];
                $diaSemana = date('N', strtotime($dataAtual));
                $ehFds = ($diaSemana >= 6);
            ?>
                <div class="min-h-[100px] sm:min-h-[120px] p-1.5 sm:p-2 border-b border-r border-gray-50 transition-colors hover:bg-primary/5 <?php echo $ehHoje ? 'bg-primary/10 ring-2 ring-primary/30 ring-inset' : ''; ?> <?php echo $ehFds ? 'bg-gray-50/30' : ''; ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-bold <?php echo $ehHoje ? 'text-primary bg-primary/20 w-7 h-7 rounded-full flex items-center justify-center' : 'text-gray-600'; ?>">
                            <?php echo $d; ?>
                        </span>
                        <?php if (count($eventosDia) > 0): ?>
                            <span class="text-[10px] bg-primary/10 text-primary font-bold px-1.5 py-0.5 rounded-full"><?php echo count($eventosDia); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-1 overflow-y-auto max-h-[70px] sm:max-h-[85px]">
                        <?php foreach ($eventosDia as $ev): 
                            $whatsappClean = preg_replace('/\D/', '', $ev['whatsapp']);
                            $meetLink = $ev['link_meet'] ?? '';
                        ?>
                            <div class="group relative bg-gradient-to-r from-primary/10 to-primary/5 rounded-lg px-1.5 py-1 cursor-pointer hover:from-primary/20 hover:to-primary/10 transition-all" 
                                 title="<?php echo htmlspecialchars($ev['nome']); ?> - <?php echo date('H:i', strtotime($ev['data_sessao'])); ?>">
                                <p class="text-[10px] sm:text-xs font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($ev['nome']); ?></p>
                                <p class="text-[9px] sm:text-[10px] text-gray-500"><?php echo date('H:i', strtotime($ev['data_sessao'])); ?></p>
                                <!-- Quick actions on hover -->
                                <div class="absolute right-1 top-1 hidden group-hover:flex gap-1 z-10">
                                    <?php if ($meetLink): ?>
                                        <a href="<?php echo $meetLink; ?>" target="_blank" class="w-5 h-5 bg-primary rounded flex items-center justify-center text-white text-[8px]">
                                            <i class="fas fa-video"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="https://wa.me/<?php echo $whatsappClean; ?>?text=<?php echo urlencode("Olá {$ev['nome']}, segue o link para nossa sessão: $meetLink"); ?>" target="_blank" class="w-5 h-5 bg-green-500 rounded flex items-center justify-center text-white text-[8px]">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
            endfor;
            
            // Preencher dias restantes após o último dia
            $ultimoDiaSemana = date('N', strtotime("$mesRef-$diasNoMes"));
            for ($i = $ultimoDiaSemana + 1; $i <= 7; $i++): ?>
                <div class="min-h-[100px] sm:min-h-[120px] p-2 border-b border-r border-gray-50 bg-gray-50/50"></div>
            <?php endfor; ?>
        </div>
    </div>

    <?php if (empty($agendaPorDia)): ?>
        <div id="view-list" class="hidden card-shadow">
            <div class="empty-state py-16">
                <div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div>
                <p class="empty-state-title">Sem agendamentos</p>
                <p class="empty-state-text">Nenhum agendamento encontrado para este mês</p>
            </div>
        </div>
    <?php else: ?>
        <!-- View: LISTA -->
        <div id="view-list" class="space-y-4 hidden">
            <?php foreach ($agendaPorDia as $dia => $eventos): ?>
                <div class="card-shadow p-4 sm:p-6">
                    <div class="flex gap-4 sm:gap-6">
                        <div class="flex-shrink-0 w-16 text-center">
                            <span class="block text-3xl sm:text-4xl font-extrabold text-primary"><?php echo $dia; ?></span>
                            <span class="block text-xs text-gray-400 uppercase font-bold tracking-wide">
                                <?php echo getDayName(date('N', strtotime($eventos[0]['data_sessao']))); ?>
                            </span>
                        </div>
                        <div class="flex-1 border-l-2 border-primary/20 pl-4 sm:pl-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                <?php foreach ($eventos as $ev): 
                                    $whatsappClean = preg_replace('/\D/', '', $ev['whatsapp']);
                                    $meetLink = $ev['link_meet'] ?? '';
                                ?>
                                    <div id="appt-<?php echo $ev['id']; ?>" draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)"
                                         class="group relative bg-gradient-to-r from-gray-50 to-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-lg hover:border-primary/30 transition-all cursor-move">
                                        <button onclick="deleteAppointment(<?php echo $ev['id']; ?>)" 
                                                class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-red-50 text-red-400 hover:bg-danger hover:text-white opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center"
                                                title="Excluir agendamento">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="avatar avatar-md ring-2 ring-white shadow">
                                                <?php if (!empty($ev['foto']) && file_exists(__DIR__ . '/public/uploads/pacientes/' . $ev['foto'])): ?>
                                                    <img src="public/uploads/pacientes/<?php echo $ev['foto']; ?>" alt="<?php echo htmlspecialchars($ev['nome']); ?>">
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
                                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                            <div class="flex gap-2">
                                                <?php if ($meetLink && strpos($meetLink, 'meet.google.com') !== false): ?>
                                                    <!-- Sincronizado com Google Meet -->
                                                    <a href="<?php echo $meetLink; ?>" target="_blank" 
                                                       class="w-8 h-8 rounded-lg bg-green-100 text-green-600 hover:bg-green-600 hover:text-white transition-all flex items-center justify-center"
                                                       title="Entrar no Google Meet">
                                                        <i class="fas fa-video text-sm"></i>
                                                    </a>
                                                    <a href="https://wa.me/<?php echo $whatsappClean; ?>?text=<?php echo urlencode("Olá {$ev['nome']}, segue o link para nossa sessão no Google Meet:\n\n🔗 $meetLink\n\nAcesse no horário agendado!"); ?>" 
                                                       target="_blank" 
                                                       class="w-8 h-8 rounded-lg bg-green-50 text-green-500 hover:bg-green-500 hover:text-white transition-all flex items-center justify-center"
                                                       title="Enviar link via WhatsApp">
                                                        <i class="fab fa-whatsapp text-sm"></i>
                                                    </a>
                                                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-400 flex items-center justify-center" title="Sincronizado com Google Agenda">
                                                        <i class="fas fa-check-circle text-xs"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <!-- Ainda não sincronizado -->
                                                    <button onclick="generateMeetLink(<?php echo $ev['id']; ?>, '<?php echo $whatsappClean; ?>', '<?php echo addslashes($ev['nome']); ?>')" 
                                                            class="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all flex items-center gap-1 shadow-sm"
                                                            title="Gerar Meet e Sincronizar com Google">
                                                        <i class="fab fa-google"></i> Gerar Meet
                                                    </button>
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

        <!-- View: KANBAN -->
        <div id="view-kanban" class="hidden overflow-x-auto pb-4 -mx-4 px-4">
            <div class="flex gap-4 min-w-max">
                <?php foreach ($agendaPorDia as $dia => $eventos): 
                    $dataCompleta = date('Y-m-d', strtotime($eventos[0]['data_sessao']));
                ?>
                    <div class="w-72 sm:w-80 bg-gray-50/80 rounded-2xl border border-gray-200 flex-shrink-0 flex flex-col max-h-[75vh]" 
                         ondragover="allowDrop(event)" 
                         ondrop="drop(event, '<?php echo $dataCompleta; ?>')">
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
                        <div class="p-3 space-y-3 overflow-y-auto flex-1">
                            <?php foreach ($eventos as $ev): 
                                $whatsappClean = preg_replace('/\D/', '', $ev['whatsapp']);
                                $meetLink = $ev['link_meet'] ?? '';
                            ?>
                                <div id="appt-<?php echo $ev['id']; ?>" draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)"
                                     class="group relative bg-white border border-gray-200 rounded-xl p-3 shadow-sm hover:shadow-md hover:border-primary/30 transition-all cursor-move">
                                    <button onclick="deleteAppointment(<?php echo $ev['id']; ?>)" 
                                            class="absolute top-2 right-2 w-6 h-6 rounded-lg bg-red-50 text-red-400 hover:bg-danger hover:text-white opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm">
                                            <?php if (!empty($ev['foto']) && file_exists(__DIR__ . '/public/uploads/pacientes/' . $ev['foto'])): ?>
                                                <img src="public/uploads/pacientes/<?php echo $ev['foto']; ?>" alt="">
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
        const calendar = document.getElementById('view-calendar');
        const list = document.getElementById('view-list');
        const kanban = document.getElementById('view-kanban');
        const btnCalendar = document.getElementById('btn-calendar');
        const btnList = document.getElementById('btn-list');
        const btnKanban = document.getElementById('btn-kanban');

        const allViews = [calendar, list, kanban].filter(v => v);
        const allBtns = [btnCalendar, btnList, btnKanban].filter(b => b);

        allViews.forEach(v => v.classList.add('hidden'));
        allBtns.forEach(b => {
            b.classList.remove('bg-primary', 'text-white');
            b.classList.add('text-gray-500', 'hover:bg-gray-100');
        });

        const targetView = document.getElementById('view-' + view);
        const targetBtn = document.getElementById('btn-' + view);

        if (targetView) targetView.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.add('bg-primary', 'text-white');
            targetBtn.classList.remove('text-gray-500', 'hover:bg-gray-100');
        }
        
        localStorage.setItem('agenda-view', view);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('agenda-view');
        if (savedView) setView(savedView);
    });

    // ========== Generate Meet Link ==========
    function generateMeetLink(appointmentId, whatsapp, nome) {
        const btn = event.target.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
        }
        
        const formData = new FormData();
        formData.append('action', 'generate_meet');
        formData.append('id', appointmentId);

        fetch('agenda_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const meetLink = data.meet_link;
                const whatsappNum = data.whatsapp || whatsapp;
                const patientName = data.nome || nome;
                const statusMsg = data.message || 'Link gerado com sucesso!';
                
                showToast(statusMsg, 'success');
                
                // Abre WhatsApp com o link
                const msg = encodeURIComponent(`Olá ${patientName}, segue o link para nossa sessão de hoje:\n\n🔗 ${meetLink}\n\nAcesse no horário agendado. Até logo!`);
                window.open(`https://wa.me/${whatsappNum}?text=${msg}`, '_blank');
                
                // Se tem link do Google Calendar, abre também
                if (data.calendar_link) {
                    setTimeout(() => {
                        window.open(data.calendar_link, '_blank');
                    }, 500);
                }
                
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('Erro: ' + data.error, 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-video"></i> Gerar Meet';
                }
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Erro de conexão', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-video"></i> Gerar Meet';
            }
        });
    }

    // ========== Add to Google Calendar ==========
    function addToGoogleCalendar(appointmentId) {
        const formData = new FormData();
        formData.append('action', 'add_to_gcal');
        formData.append('id', appointmentId);

        fetch('agenda_actions.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.calendar_url) {
                window.open(data.calendar_url, '_blank');
                showToast('Abrindo Google Calendar...', 'info');
            } else {
                showToast('Erro: ' + (data.error || 'Falha ao gerar URL'), 'error');
            }
        })
        .catch(() => showToast('Erro de conexão', 'error'));
    }

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

        fetch('agenda_actions.php', {method: 'POST', body: formData})
        .then(r => r.json())
        .then(data => {
            if (data.success) showToast('Agendamento movido com sucesso!', 'success');
            else { showToast('Erro ao mover: ' + data.error, 'error'); location.reload(); }
        })
        .catch(() => showToast('Erro de conexão', 'error'));
    }

    function deleteAppointment(id) {
        if (confirm('Tem certeza que deseja excluir este agendamento?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('agenda_actions.php', {method: 'POST', body: formData})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('appt-' + id);
                    if (el) { el.style.transform = 'scale(0.8)'; el.style.opacity = '0'; setTimeout(() => el.remove(), 200); }
                    showToast('Agendamento excluído!', 'success');
                } else showToast('Erro: ' + data.error, 'error');
            })
            .catch(() => showToast('Erro de conexão', 'error'));
        }
    }

    document.querySelectorAll('[ondragover]').forEach(el => {
        el.addEventListener('dragleave', function() {
            this.classList.remove('bg-primary/5', 'border-primary');
        });
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
