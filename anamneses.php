<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php'; 

// Buscar pacientes
$stmtPacientes = $pdo->query("SELECT id, nome FROM pacientes WHERE ativo = 1 ORDER BY nome ASC");
$pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

// Definição das Anamneses
$categorias = [
    'Avaliação Clínica' => [
        'icon' => 'fa-stethoscope',
        'color' => 'blue',
        'formularios' => [
            'adulto' => [
                'nome' => 'Anamnese Inicial para Adultos',
                'descricao' => 'Formulário completo de identificação, queixa, histórico pessoal e familiar para adultos.',
                'icon' => 'fa-user-tie',
                'color' => 'from-blue-500 to-indigo-600'
            ],
            'adolescente' => [
                'nome' => 'Anamnese Inicial para Adolescentes',
                'descricao' => 'Adaptação do formulário inicial com foco em escola, desenvolvimento e relações familiares.',
                'icon' => 'fa-user-graduate',
                'color' => 'from-emerald-500 to-teal-600'
            ]
        ]
    ],
    'Avaliação Especialista' => [
        'icon' => 'fa-star',
        'color' => 'purple',
        'formularios' => [
            'autismo' => [
                'nome' => 'Anamnese Aprofundada - Autismo (TEA)',
                'descricao' => 'Roteiro detalhado sobre marcos do desenvolvimento, processamento sensorial e interação social.',
                'icon' => 'fa-puzzle-piece',
                'color' => 'from-purple-500 to-indigo-600'
            ],
            'tdah_adulto' => [
                'nome' => 'Anamnese Especialista - TDAH em Adulto',
                'descricao' => 'Foco em funções executivas, atenção, procrastinação e histórico desde a infância.',
                'icon' => 'fa-brain',
                'color' => 'from-rose-500 to-pink-600'
            ]
        ]
    ]
];
?>

<div class="page-header mb-6">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-file-signature text-lg"></i>
            </span>
            Módulo de Anamneses
        </h2>
        <p class="page-subtitle">Selecione o modelo de formulário para iniciar a avaliação</p>
    </div>
</div>

<?php foreach ($categorias as $nomeCat => $cat): 
    $catId = strtolower(str_replace([' ', '/'], '-', $nomeCat));
?>
<div class="mb-4">
    <div class="flex items-center justify-between group cursor-pointer bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-primary/50 transition-all" onclick="toggleCategory('<?php echo $catId; ?>')">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-<?php echo $cat['color']; ?>-100 flex items-center justify-center text-<?php echo $cat['color']; ?>-600 group-hover:scale-110 transition-transform">
                <i class="fas <?php echo $cat['icon']; ?> text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-gray-800"><?php echo $nomeCat; ?></h3>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black"><?php echo count($cat['formularios']); ?> Modelos</p>
            </div>
        </div>
        <i class="fas fa-chevron-right text-gray-300 group-hover:text-primary transition-all duration-300 transform" id="icon-<?php echo $catId; ?>" style="transform: rotate(0deg);"></i>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 transition-all duration-500 overflow-hidden mt-4" id="grid-<?php echo $catId; ?>" style="max-height: 0px; opacity: 0;">
        <?php foreach ($cat['formularios'] as $key => $form): ?>
        <div class="card-shadow overflow-hidden group hover:border-primary/30 transition-all cursor-pointer" onclick="openAnamneseModal('<?php echo $key; ?>', '<?php echo $form['nome']; ?>')">
            <div class="h-1.5 bg-gradient-to-r <?php echo $form['color']; ?>"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?php echo $form['color']; ?> flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                        <i class="fas <?php echo $form['icon']; ?> text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-primary transition-colors"><?php echo $form['nome']; ?></h3>
                <p class="text-xs text-gray-500 mb-4 leading-relaxed line-clamp-2"><?php echo $form['descricao']; ?></p>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                    <span class="text-xs font-bold text-gray-400 group-hover:text-primary transition-colors">Abrir Formulário</span>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<script>
function toggleCategory(catId) {
    const grid = document.getElementById('grid-' + catId);
    const targetIcon = document.getElementById('icon-' + catId);
    
    if (grid.style.maxHeight === '0px' || !grid.style.maxHeight) {
        grid.style.maxHeight = '2000px';
        grid.style.opacity = '1';
        targetIcon.style.transform = 'rotate(90deg)';
    } else {
        grid.style.maxHeight = '0px';
        grid.style.opacity = '0';
        targetIcon.style.transform = 'rotate(0deg)';
    }
}

function openAnamneseModal(tipo, nome) {
    document.getElementById('modal-tipo').value = tipo;
    document.getElementById('modal-title').textContent = nome;
    const modal = document.getElementById('anamnese-modal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-95');
    }, 10);
}

function closeAnamneseModal() {
    const modal = document.getElementById('anamnese-modal');
    modal.classList.add('opacity-0');
    modal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>

<!-- Modal para Selecionar Paciente -->
<div id="anamnese-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-2xl transform scale-95 transition-transform duration-300 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary to-accent">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-file-signature text-sm opacity-80"></i>
                <span id="modal-title">Nova Anamnese</span>
            </h3>
            <button onclick="closeAnamneseModal()" class="text-white/80 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="anamnese_aplicar.php" method="GET" class="p-6 space-y-4">
            <input type="hidden" name="tipo" id="modal-tipo">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Selecione o Paciente</label>
                <select name="paciente_id" required class="form-input">
                    <option value="">Selecione...</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-full justify-center mt-4">
                <i class="fas fa-check"></i>
                Iniciar Anamnese
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
