<?php
// Partial: Card de Agendamento
// Espera variável $ev (evento)
$whatsappClean = preg_replace('/\D/', '', $ev['whatsapp']);
$meetLink = $ev['link_meet'] ?? '';
$msgMeet = "Olá {$ev['nome']}, segue o link para nossa sessão: $meetLink";
$linkWaMeet = "https://wa.me/$whatsappClean?text=" . urlencode($msgMeet);
?>
<div id="appt-<?php echo $ev['id']; ?>" draggable="true" ondragstart="drag(event)" ondragend="dragEnd(event)" 
     class="group relative bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition cursor-move select-none">
    
    <!-- Botão Excluir (Hover) -->
    <button onclick="deleteAppointment(<?php echo $ev['id']; ?>)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition">
        <i class="fas fa-trash-alt"></i>
    </button>

    <div class="flex items-center mb-2">
        <div class="h-10 w-10 rounded-full overflow-hidden mr-3 border border-gray-200 shrink-0">
            <?php if (!empty($ev['foto']) && file_exists(__DIR__ . '/../uploads/pacientes/' . $ev['foto'])): ?>
                <img src="uploads/pacientes/<?php echo $ev['foto']; ?>" alt="<?php echo htmlspecialchars($ev['nome']); ?>" class="h-full w-full object-cover">
            <?php else: ?>
                <div class="h-full w-full bg-blue-50 flex items-center justify-center text-primary text-xs">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($ev['nome']); ?></p>
            <p class="text-xs text-gray-500 flex items-center">
                <i class="far fa-clock mr-1"></i> <?php echo date('H:i', strtotime($ev['data_sessao'])); ?>
            </p>
        </div>
    </div>

    <!-- Ações -->
    <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-50">
        <div class="flex space-x-3">
            <?php if ($meetLink): ?>
                <a href="<?php echo $meetLink; ?>" target="_blank" class="text-gray-400 hover:text-primary transition" title="Entrar no Meet">
                    <i class="fas fa-video"></i>
                </a>
                <a href="<?php echo $linkWaMeet; ?>" target="_blank" class="text-gray-400 hover:text-green-500 transition" title="Enviar Link no WhatsApp">
                    <i class="fab fa-whatsapp"></i> <i class="fas fa-link text-[10px]"></i>
                </a>
            <?php endif; ?>
        </div>
        <a href="https://wa.me/<?php echo $whatsappClean; ?>" target="_blank" class="text-gray-400 hover:text-green-500 transition" title="Conversar no WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
</div>
