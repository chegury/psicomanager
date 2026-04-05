<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php'; 

$showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] == '1';
$sql = "SELECT * FROM pacientes";
if (!$showInactive) { $sql .= " WHERE ativo = 1"; }
$sql .= " ORDER BY nome ASC";
$stmt = $pdo->query($sql);
$pacientes = $stmt->fetchAll();
?>

<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-users text-lg"></i>
            </span>
            Pacientes
        </h2>
        <p class="page-subtitle">Gerencie seus pacientes e contratos</p>
    </div>
    <div class="flex flex-wrap gap-2 sm:gap-3 mt-4 sm:mt-0">
        <?php if ($showInactive): ?>
            <a href="pacientes.php" class="btn btn-secondary"><i class="fas fa-eye-slash"></i> <span class="hidden sm:inline">Ocultar Inativos</span></a>
        <?php else: ?>
            <a href="pacientes.php?show_inactive=1" class="btn btn-secondary"><i class="fas fa-eye"></i> <span class="hidden sm:inline">Mostrar Inativos</span></a>
        <?php endif; ?>
        <a href="pacientes_importar_asaas.php" class="btn btn-secondary border-primary/20 text-primary bg-primary/5 hover:bg-primary/10">
            <i class="fas fa-file-import"></i> <span>Importar do Asaas</span>
        </a>
        <a href="cadastro.php" class="btn btn-primary"><i class="fas fa-plus"></i> <span>Novo Paciente</span></a>
    </div>
</div>

<div class="mb-6">
    <div class="search-box w-full sm:max-w-md">
        <input type="text" id="search-pacientes" placeholder="Buscar paciente pelo nome..." class="w-full py-3">
        <i class="fas fa-search search-icon"></i>
    </div>
</div>

<div id="pacientes-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
    <?php if (empty($pacientes)): ?>
        <div class="col-span-full">
            <div class="card-shadow">
                <div class="empty-state py-12">
                    <div class="empty-state-icon"><i class="fas fa-user-plus"></i></div>
                    <p class="empty-state-title">Nenhum paciente encontrado</p>
                    <p class="empty-state-text">Comece cadastrando seu primeiro paciente</p>
                    <a href="cadastro.php" class="btn btn-primary mt-4"><i class="fas fa-plus"></i> Cadastrar Paciente</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($pacientes as $paciente): ?>
        <div class="card-shadow overflow-hidden group" data-searchable="<?php echo htmlspecialchars(strtolower($paciente['nome'] . ' ' . $paciente['whatsapp'] . ' ' . $paciente['email'])); ?>">
            <div class="relative h-40 sm:h-48 bg-gradient-to-br from-primary/5 to-accent/5 overflow-hidden">
                <?php if (!empty($paciente['foto']) && file_exists(__DIR__ . '/public/uploads/pacientes/' . $paciente['foto'])): ?>
                    <img src="public/uploads/pacientes/<?php echo $paciente['foto']; ?>" alt="<?php echo htmlspecialchars($paciente['nome']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-primary/40"></i>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="absolute top-3 right-3">
                    <?php if ($paciente['ativo']): ?>
                        <span class="badge badge-success"><i class="fas fa-check text-xs"></i> Ativo</span>
                    <?php else: ?>
                        <span class="badge badge-danger"><i class="fas fa-times text-xs"></i> Inativo</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <h3 class="text-lg font-bold text-text-main mb-1 truncate"><?php echo htmlspecialchars($paciente['nome']); ?></h3>
                <p class="text-sm text-text-light mb-4 flex items-center gap-2">
                    <i class="fab fa-whatsapp text-green-500"></i> <?php echo htmlspecialchars($paciente['whatsapp']); ?>
                </p>
                <div class="space-y-2 mb-5">
                    <div class="flex justify-between items-center text-sm bg-gray-50 rounded-lg px-3 py-2">
                        <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-calendar-day text-primary/60"></i> Dia Fixo</span>
                        <span class="font-semibold text-gray-700"><?php echo getDayName($paciente['dia_semana_fixo']); ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm bg-gray-50 rounded-lg px-3 py-2">
                        <span class="text-gray-500 flex items-center gap-2"><i class="far fa-clock text-primary/60"></i> Horário</span>
                        <span class="font-semibold text-gray-700"><?php echo substr($paciente['horario_fixo'], 0, 5); ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm bg-gray-50 rounded-lg px-3 py-2">
                        <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-dollar-sign text-primary/60"></i> Sessão</span>
                        <span class="font-bold text-primary"><?php echo formatMoney($paciente['valor_sessao']); ?></span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="paciente_editar.php?id=<?php echo $paciente['id']; ?>" class="flex-1 bg-gray-100 hover:bg-primary hover:text-white text-text-main text-center py-2.5 rounded-lg font-semibold transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-edit"></i> <span>Editar</span>
                    </a>
                    <a href="paciente_excluir.php?id=<?php echo $paciente['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este paciente?');" class="w-11 h-11 bg-red-50 hover:bg-danger hover:text-white text-danger rounded-lg font-semibold transition-all flex items-center justify-center">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="no-results" class="hidden">
    <div class="card-shadow">
        <div class="empty-state py-12">
            <div class="empty-state-icon"><i class="fas fa-search"></i></div>
            <p class="empty-state-title">Nenhum resultado</p>
            <p class="empty-state-text">Nenhum paciente encontrado com esse termo</p>
        </div>
    </div>
</div>

<script>
document.getElementById('search-pacientes').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const cards = document.querySelectorAll('#pacientes-grid [data-searchable]');
    const grid = document.getElementById('pacientes-grid');
    const noResults = document.getElementById('no-results');
    let visibleCount = 0;
    cards.forEach(card => {
        if (card.dataset.searchable.includes(searchTerm)) { card.style.display = ''; visibleCount++; }
        else { card.style.display = 'none'; }
    });
    if (visibleCount === 0 && searchTerm !== '') { noResults.classList.remove('hidden'); grid.classList.add('hidden'); }
    else { noResults.classList.add('hidden'); grid.classList.remove('hidden'); }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
