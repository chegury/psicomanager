<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$tipo = $_GET['tipo'] ?? 'adulto';
$pacienteId = intval($_GET['paciente_id'] ?? 0);

if (!$pacienteId) {
    header("Location: anamneses.php");
    exit;
}

// Buscar dados do paciente
$stmtP = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmtP->execute([$pacienteId]);
$paciente = $stmtP->fetch();

if (!$paciente) {
    header("Location: anamneses.php");
    exit;
}

// Configuração dos campos por tipo
$config = [
    'adulto' => [
        'titulo' => 'Anamnese Inicial - Adulto',
        'secoes' => [
            'Identificação' => [
                'fields' => [
                    'nome' => ['type' => 'text', 'label' => 'Nome', 'value' => $paciente['nome']],
                    'idade' => ['type' => 'number', 'label' => 'Idade'],
                    'sexo' => ['type' => 'text', 'label' => 'Sexo'],
                    'orientacao_sexual' => ['type' => 'text', 'label' => 'Orientação Sexual'],
                    'nacionalidade' => ['type' => 'text', 'label' => 'Nacionalidade'],
                    'estado_civil' => ['type' => 'text', 'label' => 'Estado Civil'],
                    'data_nasc' => ['type' => 'date', 'label' => 'Data de Nascimento'],
                    'grau_instrucao' => ['type' => 'text', 'label' => 'Grau de Instrução'],
                    'profissao' => ['type' => 'text', 'label' => 'Profissão'],
                    'residencia' => ['type' => 'text', 'label' => 'Residência (Cidade/Estado)'],
                    'telefones' => ['type' => 'text', 'label' => 'Telefones para contato', 'value' => $paciente['whatsapp']],
                    'email' => ['type' => 'email', 'label' => 'Email', 'value' => $paciente['email']],
                    'cpf' => ['type' => 'text', 'label' => 'CPF', 'value' => $paciente['cpf']]
                ]
            ],
            'Atendimento' => [
                'fields' => [
                    'data_avaliacao' => ['type' => 'date', 'label' => 'Data do atendimento de avaliação', 'value' => date('Y-m-d')],
                    'frequencia' => ['type' => 'text', 'label' => 'Frequência', 'value' => '2 vezes no mês'],
                    'duracao' => ['type' => 'text', 'label' => 'Duração/Sessão', 'value' => '40 minutos']
                ]
            ],
            'Queixas e Sintomas' => [
                'fields' => [
                    'queixa_principal' => ['type' => 'textarea', 'label' => 'Queixa Principal'],
                    'queixa_secundaria' => ['type' => 'textarea', 'label' => 'Queixa Secundária'],
                    'sintomas' => ['type' => 'textarea', 'label' => 'Sintomas']
                ]
            ],
            'Histórico da Doença Atual' => [
                'fields' => [
                    'inicio_patologia' => ['type' => 'textarea', 'label' => 'Início da patologia'],
                    'hda_frequencia' => ['type' => 'textarea', 'label' => 'Frequência dos episódios'],
                    'hda_intensidade' => ['type' => 'textarea', 'label' => 'Intensidade'],
                    'tratamentos_anteriores' => ['type' => 'textarea', 'label' => 'Tratamentos anteriores'],
                    'medicamentos' => ['type' => 'textarea', 'label' => 'Medicamentos']
                ]
            ],
            'Histórico Pessoal' => [
                'fields' => [
                    'infancia' => ['type' => 'textarea', 'label' => 'Como descreve sua infância?'],
                    'gravidez_mae' => ['type' => 'textarea', 'label' => 'Como foi a gravidez da sua mãe? Houve alguma complicação?'],
                    'rotina_atual' => ['type' => 'textarea', 'label' => 'Rotina atual'],
                    'vicios' => ['type' => 'textarea', 'label' => 'Vícios?'],
                    'drogas' => ['type' => 'textarea', 'label' => 'Já usou drogas?'],
                    'ideacao_suicida' => ['type' => 'textarea', 'label' => 'Tem ou já teve ideação suicida?'],
                    'abusos' => ['type' => 'textarea', 'label' => 'Abusos?'],
                    'hobbies' => ['type' => 'textarea', 'label' => 'Hobbies'],
                    'trabalho' => ['type' => 'textarea', 'label' => 'Relacionamento com o Trabalho']
                ]
            ],
            'Histórico Familiar' => [
                'fields' => [
                    'pais' => ['type' => 'textarea', 'label' => 'Relacionamento com Pais'],
                    'irmaos' => ['type' => 'textarea', 'label' => 'Relacionamento com Irmãos'],
                    'conjuge' => ['type' => 'textarea', 'label' => 'Relacionamento com Cônjuge'],
                    'filhos' => ['type' => 'textarea', 'label' => 'Relacionamento com Filhos'],
                    'lar' => ['type' => 'textarea', 'label' => 'Ambiente no Lar'],
                    'animais' => ['type' => 'textarea', 'label' => 'Animais de estimação'],
                    'religiao' => ['type' => 'textarea', 'label' => 'Espiritualidade/Religião']
                ]
            ],
            'Auto Exame Psíquico' => [
                'fields' => [
                    'aparencia' => ['type' => 'textarea', 'label' => 'Como define sua Aparência?'],
                    'comportamento' => ['type' => 'textarea', 'label' => 'Como define seu Comportamento?']
                ]
            ],
            'Metas e Conclusão' => [
                'fields' => [
                    'pergunta_1' => ['type' => 'textarea', 'label' => '1 - O quão importante é pra você resolver este problema?'],
                    'pergunta_2' => ['type' => 'textarea', 'label' => '2 - Quais são as consequências se você não agir agora?'],
                    'pergunta_3' => ['type' => 'textarea', 'label' => '3 - Existe alguma forma de eu tornar isso viável pra você?']
                ]
            ]
        ]
    ],
    'adolescente' => [
        'titulo' => 'Anamnese Inicial - Adolescente',
        'secoes' => [
            'Identificação' => [
                'fields' => [
                    'nome' => ['type' => 'text', 'label' => 'Nome', 'value' => $paciente['nome']],
                    'idade' => ['type' => 'number', 'label' => 'Idade'],
                    'sexo' => ['type' => 'text', 'label' => 'Sexo'],
                    'nacionalidade' => ['type' => 'text', 'label' => 'Nacionalidade'],
                    'responsavel' => ['type' => 'text', 'label' => 'Responsável Legal'],
                    'grau_parentesco' => ['type' => 'text', 'label' => 'Grau de Parentesco'],
                    'data_nasc' => ['type' => 'date', 'label' => 'Data de Nascimento'],
                    'escola' => ['type' => 'text', 'label' => 'Escola / Ano Escolar'],
                    'residencia' => ['type' => 'text', 'label' => 'Residência (Cid/UF)'],
                    'contato' => ['type' => 'text', 'label' => 'Telefones de Contato', 'value' => $paciente['whatsapp']],
                    'cpf' => ['type' => 'text', 'label' => 'CPF', 'value' => $paciente['cpf']]
                ]
            ],
            'Motivo do Atendimento' => [
                'fields' => [
                    'queixa_principal' => ['type' => 'textarea', 'label' => 'Motivo que trouxe o adolescente ao atendimento?'],
                    'queixa_proprio' => ['type' => 'textarea', 'label' => 'E qual o motivo na visão do próprio adolescente?'],
                    'data_avaliacao' => ['type' => 'date', 'label' => 'Data da Avaliação', 'value' => date('Y-m-d')]
                ]
            ],
            'Histórico de Desenvolvimento' => [
                'fields' => [
                    'gravidez' => ['type' => 'textarea', 'label' => 'Houve complicações na gravidez ou parto?'],
                    'desenvolvimento_motor' => ['type' => 'textarea', 'label' => 'Desenvolvimento motor (andou com que idade?)'],
                    'desenvolvimento_fala' => ['type' => 'textarea', 'label' => 'Desenvolvimento da fala'],
                    'controle_esfincter' => ['type' => 'textarea', 'label' => 'Controle de esfíncter'],
                    'saude_geral' => ['type' => 'textarea', 'label' => 'Saúde na infância (doenças, alergias)']
                ]
            ],
            'Vida Escolar e Social' => [
                'fields' => [
                    'relacao_escola' => ['type' => 'textarea', 'label' => 'Como é a relação com a escola e professores?'],
                    'amizades' => ['type' => 'textarea', 'label' => 'Possui amigos? Como é sua interação social?'],
                    'bullying' => ['type' => 'textarea', 'label' => 'Já sofreu ou praticou bullying?'],
                    'hobbies' => ['type' => 'textarea', 'label' => 'O que gosta de fazer no tempo livre?']
                ]
            ],
            'Contexto Familiar' => [
                'fields' => [
                    'pais' => ['type' => 'textarea', 'label' => 'Sobre os pais (convivência, conflitos)'],
                    'irmaos' => ['type' => 'textarea', 'label' => 'Sobre irmãos e outros familiares no lar'],
                    'lar' => ['type' => 'textarea', 'label' => 'Como define o ambiente em casa?'],
                    'religiao' => ['type' => 'textarea', 'label' => 'Religião na família']
                ]
            ],
            'Comportamento e Hábitos' => [
                'fields' => [
                    'rotina' => ['type' => 'textarea', 'label' => 'Como é a rotina atual?'],
                    'sono_alimentacao' => ['type' => 'textarea', 'label' => 'Sono e Alimentação'],
                    'vicios_drogas' => ['type' => 'textarea', 'label' => 'Vícios, Álcool ou Drogas?'],
                    'ideacao_suicida' => ['type' => 'textarea', 'label' => 'Auto-mutilação ou Ideação Suicida?'],
                    'abusos' => ['type' => 'textarea', 'label' => 'Histórico de abusos ou traumas?']
                ]
            ],
            'Expectativas' => [
                'fields' => [
                    'pergunta_1' => ['type' => 'textarea', 'label' => 'O que você espera conseguir com a terapia?'],
                    'pergunta_2' => ['type' => 'textarea', 'label' => 'O que mudaria na sua vida se este problema fosse resolvido?']
                ]
            ]
        ]
    ],
    'autismo' => [
        'titulo' => '2. Anamnese Aprofundada - TEA (ESSENCIAL)',
        'secoes' => [
            'Infância' => [
                'fields' => [
                    'brincadeiras' => ['type' => 'textarea', 'label' => 'Brincadeiras (imaginativas x repetitivas)'],
                    'socializacao_infancia' => ['type' => 'textarea', 'label' => 'Como era a Socialização na infância?'],
                    'sensibilidades_infancia' => ['type' => 'textarea', 'label' => 'Sensibilidades (som, toque, comida)']
                ]
            ],
            'Adolescência' => [
                'fields' => [
                    'dificuldades_sociais' => ['type' => 'textarea', 'label' => 'Dificuldades sociais nesta fase'],
                    'nao_pertencimento' => ['type' => 'textarea', 'label' => 'Sensação de "não pertencimento"'],
                    'possivel_masking' => ['type' => 'textarea', 'label' => 'Sinais de possível masking (camuflagem social)']
                ]
            ],
            'Vida Adulta' => [
                'fields' => [
                    'exaustao_social' => ['type' => 'textarea', 'label' => 'Exaustão social após interações'],
                    'sobrecarga_sensorial' => ['type' => 'textarea', 'label' => 'Sobrecarga sensorial atual'],
                    'rigidez_controle' => ['type' => 'textarea', 'label' => 'Rigidez / Necessidade de controle']
                ]
            ],
            'Relatos Externos' => [
                'fields' => [
                    'relato_familiares' => ['type' => 'textarea', 'label' => 'Relato de familiares (Se possível)']
                ]
            ]
        ]
    ],
    'tdah_adulto' => [
        'titulo' => 'Anamnese Especialista - TDAH Adulto',
        'secoes' => [
            'Histórico Escolar e Infância' => [
                'fields' => [
                    'desempenho_escolar' => ['type' => 'textarea', 'label' => 'Como era o boletim e o comportamento em sala?'],
                    'queixas_professores' => ['type' => 'textarea', 'label' => 'Era chamado de "avoado", "preguiçoso" ou "bagunceiro"?'],
                    'repetencia' => ['type' => 'textarea', 'label' => 'Houve reprovações ou trocas constantes de escola?']
                ]
            ],
            'Atenção e Foco' => [
                'fields' => [
                    'distratibilidade' => ['type' => 'textarea', 'label' => 'Distrai-se com estímulos externos facilmente?'],
                    'erros_por_descuido' => ['type' => 'textarea', 'label' => 'Comete muitos erros por falta de atenção em detalhes?'],
                    'perda_objetos' => ['type' => 'textarea', 'label' => 'Perde chaves, documentos ou objetos importantes com frequência?'],
                    'esquecimento' => ['type' => 'textarea', 'label' => 'Esquece compromissos ou tarefas rotineiras?']
                ]
            ],
            'Hiperatividade e Impulsividade' => [
                'fields' => [
                    'inquietude' => ['type' => 'textarea', 'label' => 'Sensação interna de agitação? Dificuldade de ficar sentado muito tempo?'],
                    'falar_em_excesso' => ['type' => 'textarea', 'label' => 'Costuma falar demais ou interromper os outros?'],
                    'decisoes_impulsivas' => ['type' => 'textarea', 'label' => 'Gastos excessivos, mudanças bruscas ou reações explosivas?'],
                    'baixa_tolerancia_frustracao' => ['type' => 'textarea', 'label' => 'Desiste fácil de tarefas longas ou entediantes?']
                ]
            ],
            'Funções Executivas' => [
                'fields' => [
                    'organizacao_planejamento' => ['type' => 'textarea', 'label' => 'Dificuldade em organizar o dia a dia e definir prioridades?'],
                    'procrastinacao' => ['type' => 'textarea', 'label' => 'Deixa tudo para a última hora?'],
                    'gestao_tempo' => ['type' => 'textarea', 'label' => 'Sempre chega atrasado ou perde a noção do tempo?']
                ]
            ],
            'Impacto na Vida Adulta' => [
                'fields' => [
                    'carreira' => ['type' => 'textarea', 'label' => 'Instabilidade profissional ou sensação de subaproveitamento?'],
                    'relacionamentos' => ['type' => 'textarea', 'label' => 'Conflitos por esquecimento ou falta de escuta ativa?'],
                    'autoestima' => ['type' => 'textarea', 'label' => 'Sentimento de "culpa" ou "incapacidade" acumulado ao longo da vida?']
                ]
            ]
        ]
    ]
];

$formConfig = $config[$tipo];

// Processar salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respostas = $_POST['anamnese'];
    $stmt = $pdo->prepare("INSERT INTO anamneses_resultados (paciente_id, tipo_anamnese, conteudo_json) VALUES (?, ?, ?)");
    $stmt->execute([$pacienteId, $tipo, json_encode($respostas)]);
    $idResultado = $pdo->lastInsertId();
    header("Location: anamnese_aplicar.php?tipo=$tipo&paciente_id=$pacienteId&success=1&id_resultado=$idResultado");
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header mb-6">
    <div class="flex items-center gap-4">
        <a href="anamneses.php" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="page-title"><?php echo $formConfig['titulo']; ?></h2>
            <p class="page-subtitle">Paciente: <span class="font-bold text-gray-700"><?php echo htmlspecialchars($paciente['nome']); ?></span></p>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center justify-between animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-success"><i class="fas fa-check"></i></div>
            <p class="font-bold text-green-800">Anamnese salva com sucesso!</p>
        </div>
        <button onclick="window.location.href='anamneses.php'" class="btn btn-primary btn-sm">Voltar ao Início</button>
    </div>
<?php endif; ?>

<form method="POST" class="space-y-8 pb-20">
    <?php foreach ($formConfig['secoes'] as $secaoNome => $secao): ?>
    <div class="card-shadow overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100">
            <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest"><?php echo $secaoNome; ?></h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($secao['fields'] as $key => $field): ?>
            <div class="<?php echo $field['type'] === 'textarea' ? 'md:col-span-2' : ''; ?>">
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-tight"><?php echo $field['label']; ?></label>
                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea name="anamnese[<?php echo $key; ?>]" class="form-input min-h-[100px]" placeholder="..."><?php echo $field['value'] ?? ''; ?></textarea>
                <?php else: ?>
                    <input type="<?php echo $field['type']; ?>" name="anamnese[<?php echo $key; ?>]" value="<?php echo $field['value'] ?? ''; ?>" class="form-input">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Floating Action Bar -->
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-gray-100 z-50 flex justify-center">
        <div class="max-w-4xl w-full flex justify-between items-center bg-white p-2 rounded-2xl shadow-2xl border border-gray-100">
            <p class="text-xs text-gray-400 font-medium px-4">Preencha todos os campos antes de salvar.</p>
            <div class="flex gap-2">
                <a href="anamneses.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Anamnese</button>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
