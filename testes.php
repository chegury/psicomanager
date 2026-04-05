<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php'; 

$categorias = [
    'Ansiedade' => [
        'icon' => 'fa-wind',
        'color' => 'blue',
        'testes' => [
            'BAI' => ['nome' => 'BAI - Inventário de Ansiedade de Beck', 'desc' => 'Avaliação dos níveis de ansiedade clínica.', 'satepsi' => false],
            'GAD-7' => ['nome' => 'GAD-7 - Ansiedade Generalizada', 'desc' => 'Rastreio rápido para TAG.', 'satepsi' => false],
            'PHQ-9' => ['nome' => 'PHQ-9 - Depressão e Humor', 'desc' => 'Avaliação de sintomas depressivos.', 'satepsi' => false],
            'STAI' => ['nome' => 'STAI - Ansiedade Traço/Estado', 'desc' => 'Diferencia a ansiedade momentânea da habitual.', 'satepsi' => false],
            'Stroop' => ['nome' => 'Stroop Test - Cores e Palavras', 'desc' => 'Avalia resistência à interferência atencional.', 'satepsi' => true],
        ]
    ],
    'Auto estima' => [
        'icon' => 'fa-heart',
        'color' => 'rose',
        'testes' => [
            'RSES' => ['nome' => 'RSES - Escala de Autoestima de Rosenberg', 'desc' => 'Avalia a percepção de valor próprio e autoaceitação.', 'satepsi' => false],
            'BIG5' => ['nome' => 'BIG FIVE - Traços de Personalidade', 'desc' => 'Escala simplificada dos 5 grandes fatores.', 'satepsi' => false],
            'BFP' => ['nome' => 'BFP - Bateria Fatorial de Personalidade', 'desc' => 'Avaliação profunda de personalidade (SATEPSI).', 'satepsi' => true],
        ]
    ],
    'Tdah' => [
        'icon' => 'fa-brain',
        'color' => 'orange',
        'testes' => [
            'ASRS-18' => ['nome' => 'ASRS-18 - Rastreio de TDAH em Adultos', 'desc' => 'Identificação de sintomas de desatenção e impulsividade.', 'satepsi' => false],
            'DIVA' => ['nome' => 'DIVA - Entrevista para Diagnóstico de TDAH em Adultos', 'desc' => 'Entrevista estruturada baseada nos critérios do DSM-IV/5.', 'satepsi' => false],
            'd2-R' => ['nome' => 'Teste d2-R - Atenção Concentrada', 'desc' => 'Avalia seletividade e qualidade do trabalho (SATEPSI).', 'satepsi' => true],
            'Tavis' => ['nome' => 'TAVIS-4 - Atenção Visual', 'desc' => 'Avalia sustentação e alternância atencional (SATEPSI).', 'satepsi' => true],
            'FDT' => ['nome' => 'FDT - Cinco Dígitos', 'desc' => 'Avalia funções executivas e controle inibitório (SATEPSI).', 'satepsi' => true],
        ]
    ],
    'Desenvolvimento' => [
        'icon' => 'fa-seedling',
        'color' => 'emerald',
        'testes' => [
            'SAS' => ['nome' => 'SAS - Escala de Adaptação Social', 'desc' => 'Avalia o ajuste social em diversas áreas da vida.', 'satepsi' => false],
            'WAIS-IV' => ['nome' => 'WAIS-IV - Cognição e QI', 'desc' => 'Avaliação intelectual completa (Adulto) (SATEPSI).', 'satepsi' => true],
        ]
    ]
];
?>

<div class="page-header mb-8">
    <div>
        <h2 class="page-title flex items-center gap-3">
            <span class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary shadow-sm"><i class="fas fa-clipboard-check text-lg"></i></span>
            Catálogo de Testes
        </h2>
        <p class="page-subtitle">Selecione uma categoria para expandir os instrumentos.</p>
    </div>
</div>

<div class="space-y-4">
    <?php foreach ($categorias as $nomeCat => $cat): 
        $catId = strtolower(str_replace(' ', '-', $nomeCat));
    ?>
    <div class="category-block">
        <div onclick="toggleCat('<?php echo $catId; ?>')" class="flex items-center justify-between p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:border-primary/50 cursor-pointer transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-<?php echo $cat['color']; ?>-50 flex items-center justify-center text-<?php echo $cat['color']; ?>-600 group-hover:scale-110 transition-transform">
                    <i class="fas <?php echo $cat['icon']; ?> text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800"><?php echo $nomeCat; ?></h3>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest"><?php echo count($cat['testes']); ?> Modelos</p>
                </div>
            </div>
            <i class="fas fa-chevron-right text-gray-300 transition-all duration-300" id="icon-<?php echo $catId; ?>"></i>
        </div>

        <div id="grid-<?php echo $catId; ?>" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4 px-2">
            <?php foreach ($cat['testes'] as $key => $t): 
                $url = ($key === 'DIVA') ? 'diva_aplicar.php' : "teste_aplicar.php?tipo=$key";
            ?>
            <div class="card-shadow p-5 flex flex-col justify-between group hover:border-primary/30 transition-all cursor-pointer relative overflow-hidden" onclick="window.location.href='<?php echo $url; ?>'">
                <!-- Selo SATEPSI -->
                <div class="absolute -right-12 top-6 <?php echo $t['satepsi'] ? 'bg-green-500' : 'bg-slate-400'; ?> text-white text-[8px] font-black py-1 w-40 text-center transform rotate-45 shadow-sm uppercase tracking-tighter">
                    <?php echo $t['satepsi'] ? 'SATEPSI Favorável' : 'Escala Clínica'; ?>
                </div>

                <div>
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center mb-4 group-hover:bg-primary/5">
                        <i class="fas fa-file-alt text-gray-300 group-hover:text-primary transition-colors"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2 leading-snug group-hover:text-primary pr-8"><?php echo $t['nome']; ?></h4>
                    <p class="text-xs text-gray-500 leading-relaxed"><?php echo $t['desc']; ?></p>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-4">
                    <span class="text-[10px] font-black text-primary uppercase tracking-widest">Iniciar Avaliação</span>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleCat(id) {
    const grid = document.getElementById('grid-' + id);
    const icon = document.getElementById('icon-' + id);
    
    if (grid.classList.contains('hidden')) {
        grid.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
        icon.classList.add('text-primary');
    } else {
        grid.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
        icon.classList.remove('text-primary');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
