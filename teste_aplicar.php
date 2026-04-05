<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// ============================================================
// CONFIGURAÇÃO DOS INSTRUMENTOS
// ============================================================
$testesConfig = [
    'BAI' => [
        'nome' => 'BAI - Inventário de Ansiedade de Beck',
        'instrucao' => 'Indique o quanto cada sintoma o incomodou durante a ÚLTIMA SEMANA.',
        'opcoes' => ['0' => 'Absolutamente não', '1' => 'Levemente', '2' => 'Moderadamente', '3' => 'Gravemente'],
        'itens' => ['Dormência ou formigamento','Sensação de calor','Tremores nas pernas','Incapacidade de relaxar','Medo que aconteça o pior','Atordoado ou tonto','Palpitação ou aceleração do coração','Sem equilíbrio','Aterrorizado','Nervoso','Sensação de sufocação','Tremores nas mãos','Trêmulo','Medo de perder o controle','Dificuldade de respirar','Medo de morrer','Assustado','Indigestão ou desconforto abdominal','Sensação de desmaio','Rosto afogueado','Suor (não devido ao calor)'],
        'cortes' => [[0,7,'Mínimo'],[8,15,'Leve'],[16,25,'Moderado'],[26,63,'Grave']]
    ],
    'GAD-7' => [
        'nome' => 'GAD-7 - Ansiedade Generalizada',
        'instrucao' => 'Nas últimas 2 semanas, com que frequência foi incomodado por estes problemas?',
        'opcoes' => ['0' => 'Nenhuma vez', '1' => 'Vários dias', '2' => 'Mais da metade dos dias', '3' => 'Quase todos os dias'],
        'itens' => ['Sentir-se nervoso(a), ansioso(a) ou muito tenso(a)','Não ser capaz de impedir ou controlar as preocupações','Preocupar-se muito com diversas coisas','Dificuldade para relaxar','Ficar tão agitado(a) que é difícil ficar sentado(a) quieto(a)','Ficar facilmente aborrecido(a) ou irritado(a)','Sentir medo como se algo horrível fosse acontecer'],
        'cortes' => [[0,4,'Mínimo'],[5,9,'Leve'],[10,14,'Moderado'],[15,21,'Grave']]
    ],
    'PHQ-9' => [
        'nome' => 'PHQ-9 - Questionário de Saúde do Paciente',
        'instrucao' => 'Nas últimas 2 semanas, com que frequência foi incomodado(a) pelos seguintes problemas?',
        'opcoes' => ['0' => 'Nenhuma vez', '1' => 'Vários dias', '2' => 'Mais da metade dos dias', '3' => 'Quase todos os dias'],
        'itens' => ['Pouco interesse ou prazer em fazer as coisas','Sentir-se para baixo, deprimido(a) ou sem perspectiva','Dificuldade para pegar no sono ou dormir demais','Sentir-se cansado(a) ou com pouca energia','Falta de apetite ou comendo demais','Sentir-se mal consigo mesmo(a) — ou que é um fracasso','Dificuldade para se concentrar nas coisas','Mover-se ou falar tão lentamente que as outras pessoas notaram','Pensamentos de que seria melhor estar morto(a) ou de se ferir'],
        'cortes' => [[0,4,'Mínimo'],[5,9,'Leve'],[10,14,'Moderado'],[15,19,'Moderadamente Grave'],[20,27,'Grave']]
    ],
    'RSES' => [
        'nome' => 'RSES - Escala de Autoestima de Rosenberg',
        'instrucao' => 'Indique o quanto você concorda ou discorda de cada afirmação.',
        'opcoes' => ['3' => 'Concordo Plenamente', '2' => 'Concordo', '1' => 'Discordo', '0' => 'Discordo Plenamente'],
        'itens' => ['Sinto que sou uma pessoa de valor','Sinto que tenho várias boas qualidades','No geral, tendo a pensar que sou um(a) fracassado(a)','Sou capaz de fazer as coisas tão bem quanto a maioria','Sinto que não tenho muito do que me orgulhar','Tenho uma atitude positiva em relação a mim','No geral, estou satisfeito(a) comigo','Gostaria de ter mais respeito por mim','Sinto-me inútil às vezes','Às vezes acho que não presto para nada'],
        'cortes' => [[0,14,'Baixa'],[15,25,'Média'],[26,30,'Alta']]
    ],
    'ASRS-18' => [
        'nome' => 'ASRS-18 - Rastreio de TDAH Adulto',
        'instrucao' => 'Com que frequência você sentiu cada sintoma nos últimos 6 meses?',
        'opcoes' => ['0' => 'Nunca', '1' => 'Raramente', '2' => 'Às vezes', '3' => 'Frequentemente', '4' => 'Muito Frequentemente'],
        'itens' => ['Dificuldade em finalizar os detalhes finais de um projeto','Dificuldade em colocar as coisas em ordem','Dificuldade em lembrar-se de compromissos','Quando tem tarefa que exige muito pensamento, evita ou adia','Fica remexendo mãos ou pés quando sentado','Sente-se ativo demais, como se estivesse com um motor','Comete erros por descuido em tarefas difíceis','Dificuldade em manter atenção em tarefas monótonas','Dificuldade em concentrar-se no que dizem a você','Perde ou tem dificuldade de encontrar coisas','Distrai-se com facilidade por estímulos externos','Levanta-se em situações onde deveria ficar sentado','Sente-se inquieto(a) ou agitado(a)','Dificuldade em sossegar quando tem tempo livre','Fala demais em situações sociais','Termina frases das pessoas antes delas terminarem','Dificuldade em esperar sua vez','Interrompe os outros quando estão ocupados'],
        'cortes' => [[0,23,'Não Sugestivo'],[24,45,'Sugestivo de TDAH'],[46,72,'Altamente Sugestivo']]
    ],
    'BDI' => [
        'nome' => 'BDI-II - Inventário de Depressão de Beck',
        'instrucao' => 'Escolha a opção que melhor descreve como você se sentiu na última semana.',
        'opcoes' => ['0' => '0', '1' => '1', '2' => '2', '3' => '3'],
        'itens' => ['Tristeza','Pessimismo','Fracasso Passado','Perda de Prazer','Sentimento de Culpa','Sentimento de Punição','Autoestima','Autocrítica','Pensamentos Suicidas','Choro','Agitação','Perda de Interesse','Indecisão','Desvalorização','Falta de Energia','Alterações no Sono','Irritabilidade','Alterações no Apetite','Dificuldade de Concentração','Cansaço ou Fadiga','Interesse Sexual'],
        'cortes' => [[0,13,'Mínima'],[14,19,'Leve'],[20,28,'Moderada'],[29,63,'Grave']]
    ],
    'STAI' => [
        'nome' => 'STAI - Ansiedade Traço/Estado',
        'instrucao' => 'Indique como se sente agora (Estado) ou geralmente (Traço).',
        'opcoes' => ['1' => 'Absolutamente não', '2' => 'Um pouco', '3' => 'Bastante', '4' => 'Muitíssimo'],
        'itens' => ['Sinto-me calmo(a)','Sinto-me seguro(a)','Estou tenso(a)','Estou arrependido(a)','Sinto-me à vontade','Sinto-me perturbado(a)','Estou preocupado(a) com possíveis infortúnios','Sinto-me descansado(a)','Sinto-me ansioso(a)','Sinto-me em casa','Sinto-me confiante','Sinto-me nervoso(a)','Estou agitado(a)','Sinto-me uma pilha de nervos','Estou descontraído(a)','Sinto-me satisfeito(a)','Estou preocupado(a)','Sinto-me confuso(a)','Sinto-me alegre','Sinto-me bem'],
        'cortes' => [[20,39,'Baixo'],[40,59,'Moderado'],[60,80,'Alto']]
    ],
    'SAS' => [
        'nome' => 'SAS - Escala de Autoavaliação de Ansiedade de Zung',
        'instrucao' => 'Frequência com que sentiu cada sintoma nos últimos dias.',
        'opcoes' => ['1' => 'Raramente/Nunca', '2' => 'Às vezes', '3' => 'Muitas vezes', '4' => 'Na maioria/Todo o tempo'],
        'itens' => ['Sinto-me mais nervoso(a) e ansioso(a) do que o normal','Sinto medo sem motivo aparente','Fico facilmente perturbado(a) ou em pânico','Sinto como se estivesse desmoronando','Sinto que tudo está bem','Meus braços e pernas tremem','Sinto-me incomodado(a) com dores de cabeça','Sinto-me fraco(a) e canso facilmente','Sinto-me calmo(a) e posso ficar parado(a)','Posso sentir meu coração batendo rápido','Tenho crises de tontura','Tenho crises de desmaio','Posso inspirar e expirar facilmente','Sinto dormência e formigamento','Sinto-me incomodado(a) por dores de estômago','Preciso urinar com frequência','Minhas mãos são secas e quentes','Meu rosto fica quente e corado','Adormeço facilmente','Tenho pesadelos'],
        'cortes' => [[20,44,'Normal'],[45,59,'Leve'],[60,74,'Moderado'],[75,80,'Grave']]
    ],
    'BIG5' => [
        'nome' => 'BIG FIVE - Personalidade (Reduzido)',
        'instrucao' => 'Identifique o quanto cada frase descreve sua personalidade.',
        'opcoes' => ['1' => 'Discordo', '2' => 'Neutro', '3' => 'Concordo'],
        'itens' => ['Sou extrovertido(a) e entusiasta','Sou crítico(a) e briguento(a)','Sou confiável e disciplinado(a)','Sou ansioso(a) e me irrito fácil','Sou aberto(a) a novas experiências','Sou reservado(a) e quieto(a)','Sou compreensivo(a) e caloroso(a)','Sou desorganizado(a) e descuidado(a)','Sou calmo(a) e emocionalmente estável','Sou convencional e pouco criativo(a)'],
        'cortes' => [[10,15,'Equilibrado'],[16,25,'Traços Fortes'],[26,30,'Intensidade Alta']]
    ],
    // ── DIVA-5: Entrevista 100% fiel ──
    'DIVA' => ['nome' => 'DIVA-5 — Entrevista Diagnóstica para TDAH em Adultos', 'is_diva' => true],
    // ── Testes de Lançamento Manual (SATEPSI) ──
    'WAIS-IV' => ['nome' => 'WAIS-IV - Escala Wechsler de Inteligência', 'manual' => true],
    'BFP'     => ['nome' => 'BFP - Bateria Fatorial de Personalidade', 'manual' => true],
    'd2-R'    => ['nome' => 'Teste d2-R - Atenção Concentrada', 'manual' => true],
    'Tavis'   => ['nome' => 'TAVIS-4 - Atenção Visual', 'manual' => true],
    'FDT'     => ['nome' => 'FDT - Teste dos Cinco Dígitos', 'manual' => true],
    'Stroop'  => ['nome' => 'Stroop Test - Cores e Palavras', 'manual' => true],
];

// ============================================================
// DIVA-5 — ESTRUTURA FIEL AO INSTRUMENTO ORIGINAL
// Kooij & Francken. Baseado nos critérios do DSM-5.
// ============================================================
$divaCriterios = [
    'A1' => [
        'titulo' => 'Parte 1 — Critérios de Desatenção (A1)',
        'subtitulo' => 'O paciente frequentemente...',
        'itens' => [
            [
                'criterio' => 'A1.1',
                'texto' => 'Não presta atenção a detalhes ou comete erros por descuido em trabalhos escolares/profissionais ou em outras atividades.',
                'exemplos_adulto' => 'Comete erros por descuido no trabalho; trabalho é impreciso; não lê instruções com cuidado; perde detalhes importantes.',
                'exemplos_infancia' => 'Cometia erros descuidados nos deveres escolares; não conferia o trabalho; perdia a linha ao ler.',
            ],
            [
                'criterio' => 'A1.2',
                'texto' => 'Tem dificuldade em manter a atenção em tarefas ou atividades de lazer.',
                'exemplos_adulto' => 'Dificuldade em se concentrar durante reuniões, leituras longas ou conversas prolongadas.',
                'exemplos_infancia' => 'Distraía-se facilmente em sala de aula; não conseguia prestar atenção durante toda a aula; perdia-se nas brincadeiras.',
            ],
            [
                'criterio' => 'A1.3',
                'texto' => 'Parece não escutar quando alguém lhe dirige a palavra diretamente.',
                'exemplos_adulto' => 'A mente parece estar em outro lugar mesmo sem distração óbvia; as pessoas se queixam de não ser ouvidas.',
                'exemplos_infancia' => 'Os pais/professores diziam que "vivia no mundo da lua"; precisava ser chamado(a) várias vezes.',
            ],
            [
                'criterio' => 'A1.4',
                'texto' => 'Não segue instruções e não termina tarefas no trabalho ou deveres (não por oposição ou por não entender).',
                'exemplos_adulto' => 'Começa tarefas mas perde a concentração e se desvia facilmente; não conclui obrigações do trabalho.',
                'exemplos_infancia' => 'Não terminava os deveres de casa; deixava trabalhos escolares pela metade; precisava de muito auxílio.',
            ],
            [
                'criterio' => 'A1.5',
                'texto' => 'Tem dificuldade em organizar tarefas e atividades.',
                'exemplos_adulto' => 'Dificuldade em gerenciar tarefas sequenciais; material e pertences desorganizados; mau gerenciamento de tempo; não cumpre prazos.',
                'exemplos_infancia' => 'Quarto/mochila sempre bagunçados; cadernos desorganizados; perdia materiais escolares frequentemente.',
            ],
            [
                'criterio' => 'A1.6',
                'texto' => 'Evita, demonstra relutância ou tem dificuldade em se envolver em tarefas que exijam esforço mental prolongado.',
                'exemplos_adulto' => 'Evita ou adia relatórios, formulários, revisão de textos longos; procrastinação de tarefas burocráticas.',
                'exemplos_infancia' => 'Evitava deveres de casa e leituras longas; reclamava de tarefas que exigiam atenção sustentada.',
            ],
            [
                'criterio' => 'A1.7',
                'texto' => 'Perde coisas necessárias para tarefas ou atividades.',
                'exemplos_adulto' => 'Perde celular, carteira, chaves, documentos, óculos, agenda com frequência.',
                'exemplos_infancia' => 'Perdia material escolar, brinquedos, casaco, lancheira com frequência; esquecia objetos em diferentes lugares.',
            ],
            [
                'criterio' => 'A1.8',
                'texto' => 'É facilmente distraído por estímulos externos (ou pensamentos não relacionados, em adolescentes e adultos).',
                'exemplos_adulto' => 'Dificuldade em filtrar ruídos; pensamentos paralelos durante conversas; desvia o foco com qualquer estímulo.',
                'exemplos_infancia' => 'Qualquer barulho ou movimento tirava a atenção na aula; olhava pela janela constantemente.',
            ],
            [
                'criterio' => 'A1.9',
                'texto' => 'É esquecido em atividades do dia a dia.',
                'exemplos_adulto' => 'Esquece compromissos, de dar recados, de pagar contas, de retornar ligações; precisa de muitos lembretes.',
                'exemplos_infancia' => 'Esquecia recados dos pais; esquecia de fazer tarefas rotineiras; esquecia datas e compromissos.',
            ],
        ]
    ],
    'A2' => [
        'titulo' => 'Parte 2 — Critérios de Hiperatividade-Impulsividade (A2)',
        'subtitulo' => 'O paciente frequentemente...',
        'itens' => [
            [
                'criterio' => 'A2.1',
                'texto' => 'Remexe ou batuca as mãos/pés, ou se contorce na cadeira.',
                'exemplos_adulto' => 'Inquietação nas mãos e pés; mexe-se constantemente na cadeira em reuniões; batuca na mesa.',
                'exemplos_infancia' => 'Não ficava parado(a) na cadeira na escola; mexia-se constantemente; a professora chamava atenção.',
            ],
            [
                'criterio' => 'A2.2',
                'texto' => 'Levanta da cadeira em situações em que se espera que permaneça sentado.',
                'exemplos_adulto' => 'Levanta-se em reuniões, no escritório, no cinema, em jantares; precisa se mover.',
                'exemplos_infancia' => 'Levantava da carteira em sala de aula sem permissão; não conseguia ficar sentado(a) durante as refeições.',
            ],
            [
                'criterio' => 'A2.3',
                'texto' => 'Corre ou sobe nas coisas em situações inapropriadas. Em adultos: sensação de inquietação.',
                'exemplos_adulto' => 'Sente-se inquieto(a) internamente; dificuldade em ficar parado(a); desconforto em situações sedentárias.',
                'exemplos_infancia' => 'Corria e subia em móveis, árvores; não conseguia brincar calmamente; era considerado(a) "impossível de conter".',
            ],
            [
                'criterio' => 'A2.4',
                'texto' => 'É incapaz de brincar ou se envolver em atividades de lazer calmamente.',
                'exemplos_adulto' => 'Sempre faz tudo com pressa; não controla o volume da voz; tem dificuldade em se envolver em hobbies tranquilos.',
                'exemplos_infancia' => 'Não conseguia brincar silenciosamente; os pais diziam que era "barulhento(a) demais"; fazia muito barulho nas brincadeiras.',
            ],
            [
                'criterio' => 'A2.5',
                'texto' => 'Está "a todo vapor" ou age como se "estivesse com um motor".',
                'exemplos_adulto' => 'Sente-se sempre "ligado"; incapaz de ficar parado por longos períodos; desconfortável em ficar sem fazer nada.',
                'exemplos_infancia' => 'Os pais/professores diziam que era "elétrico(a)"; parecia não parar nunca; corria de um lado a outro.',
            ],
            [
                'criterio' => 'A2.6',
                'texto' => 'Fala em excesso.',
                'exemplos_adulto' => 'Fala demais em contextos sociais; domina conversas; as pessoas reclamam que fala muito.',
                'exemplos_infancia' => 'Falava demais em sala de aula; tomava bronca por falar fora de hora; os adultos pediam para parar de falar.',
            ],
            [
                'criterio' => 'A2.7',
                'texto' => 'Deixa escapar uma resposta antes que a pergunta tenha sido concluída.',
                'exemplos_adulto' => 'Completa frases dos outros; responde antes de a pergunta ser terminada; não espera o outro acabar de falar.',
                'exemplos_infancia' => 'Respondia na sala de aula sem levantar a mão; interrompia a pergunta do professor para responder.',
            ],
            [
                'criterio' => 'A2.8',
                'texto' => 'Tem dificuldade em esperar a sua vez.',
                'exemplos_adulto' => 'Impaciência em filas; dificuldade em aguardar em reuniões; faz ultrapassagens no trânsito por impaciência.',
                'exemplos_infancia' => 'Não conseguia esperar a vez em jogos ou brincadeiras; furava filas; ficava agitado(a) quando tinha que esperar.',
            ],
            [
                'criterio' => 'A2.9',
                'texto' => 'Interrompe ou se intromete em conversas ou atividades alheias.',
                'exemplos_adulto' => 'Intromete-se em conversas; toma a direção de atividades dos outros; usa coisas dos outros sem pedir.',
                'exemplos_infancia' => 'Entrava nas brincadeiras dos outros sem ser convidado(a); interrompia conversas de adultos; era intrusivo(a).',
            ],
        ]
    ]
];

$divaPrejuizos = [
    'trabalho'       => 'Trabalho / Educação',
    'relacionamento' => 'Relacionamento familiar / conjugal',
    'social'         => 'Contatos sociais',
    'lazer'          => 'Tempo livre / Hobbies',
    'autoconfianca'  => 'Autoconfiança / Autoimagem',
];

// ============================================================
// PROCESSAMENTO
// ============================================================
$tipo = $_GET['tipo'] ?? 'BAI';
$config = $testesConfig[$tipo] ?? null;
if (!$config) { echo "<script>alert('Teste não encontrado');window.location='testes.php';</script>"; exit; }

$pacientes = $pdo->query("SELECT id, nome FROM pacientes WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Garantir tabela
try { $pdo->exec("CREATE TABLE IF NOT EXISTS testes_resultados (id INT AUTO_INCREMENT PRIMARY KEY, paciente_id INT NOT NULL, tipo_teste VARCHAR(20) NOT NULL, respostas_json TEXT, pontuacao INT NOT NULL DEFAULT 0, classificacao VARCHAR(100), solicitante VARCHAR(100), finalidade VARCHAR(100), observacoes TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (Exception $e) {}

$resultado = null;

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacienteId = intval($_POST['paciente_id'] ?? 0);
    $solicitante = $_POST['solicitante'] ?? 'O Próprio';
    $finalidade  = $_POST['finalidade']  ?? 'Avaliação Psicológica';
    $observacoes = $_POST['observacoes'] ?? '';
    $pontuacao   = 0;
    $classificacao = '';
    $respostas = [];

    if (isset($config['is_diva'])) {
        // ── DIVA-5 ──
        $scores = ['A1_adulto'=>0,'A1_infancia'=>0,'A2_adulto'=>0,'A2_infancia'=>0];
        $detalhes = [];
        foreach (['A1','A2'] as $parte) {
            for ($i = 1; $i <= 9; $i++) {
                $key = "{$parte}_{$i}";
                $adulto   = intval($_POST[$key.'_adulto']   ?? 0);
                $infancia = intval($_POST[$key.'_infancia'] ?? 0);
                $scores[$parte.'_adulto']   += $adulto;
                $scores[$parte.'_infancia'] += $infancia;
                $detalhes[$key] = ['adulto' => $adulto, 'infancia' => $infancia];
            }
        }
        // Idade de início
        $detalhes['idade_inicio']  = $_POST['idade_inicio'] ?? '';
        // Prejuízos
        $detalhes['prejuizo_adulto']   = $_POST['prejuizo_adulto'] ?? [];
        $detalhes['prejuizo_infancia'] = $_POST['prejuizo_infancia'] ?? [];

        $respostas = ['scores' => $scores, 'detalhes' => $detalhes];
        $pontuacao = $scores['A1_adulto'] + $scores['A2_adulto'];

        // DSM-5: ≥5 sintomas em adultos, ≥6 em crianças
        $desA = ($scores['A1_adulto'] >= 5 && $scores['A1_infancia'] >= 6);
        $hipA = ($scores['A2_adulto'] >= 5 && $scores['A2_infancia'] >= 6);
        if ($desA && $hipA)      $classificacao = 'Sugestivo de TDAH — Apresentação Combinada';
        elseif ($desA)           $classificacao = 'Sugestivo de TDAH — Predom. Desatento';
        elseif ($hipA)           $classificacao = 'Sugestivo de TDAH — Predom. Hiperativo-Impulsivo';
        else                     $classificacao = 'Critérios diagnósticos não preenchidos';

        $observacoes .= " | A1 Inf:{$scores['A1_infancia']}/9 Adu:{$scores['A1_adulto']}/9 | A2 Inf:{$scores['A2_infancia']}/9 Adu:{$scores['A2_adulto']}/9";

    } elseif (isset($config['manual'])) {
        $pontuacao      = intval($_POST['ponto_bruto'] ?? 0);
        $classificacao  = $_POST['ponto_padronizado'] ?? 'Avaliado';
        $respostas      = ['manual' => true, 'bruto' => $pontuacao, 'padrao' => $classificacao];
    } else {
        for ($i = 0; $i < count($config['itens'] ?? []); $i++) {
            $val = intval($_POST["item_$i"] ?? 0);
            $respostas[] = $val;
            $pontuacao += $val;
        }
        $classificacao = 'Indeterminado';
        foreach ($config['cortes'] ?? [] as $c) {
            if ($pontuacao >= $c[0] && $pontuacao <= $c[1]) { $classificacao = $c[2]; break; }
        }
    }

    if ($pacienteId > 0) {
        $stmt = $pdo->prepare("INSERT INTO testes_resultados (paciente_id, tipo_teste, respostas_json, pontuacao, classificacao, solicitante, finalidade, observacoes) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$pacienteId, $tipo, json_encode($respostas), $pontuacao, $classificacao, $solicitante, $finalidade, $observacoes]);
        $id_resultado = $pdo->lastInsertId();
        header("Location: teste_aplicar.php?tipo=$tipo&id_resultado=$id_resultado&success=1");
        exit;
    }
}

// Carregar resultado salvo
$id_resultado = $_GET['id_resultado'] ?? null;
if ($id_resultado) {
    $stmt = $pdo->prepare("SELECT * FROM testes_resultados WHERE id = ?");
    $stmt->execute([$id_resultado]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $resultado = [
            'pontuacao' => $res['pontuacao'],
            'classificacao' => $res['classificacao'],
            'respostas' => json_decode($res['respostas_json'], true),
            'paciente_id' => $res['paciente_id'],
            'observacoes' => $res['observacoes']
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- ============================================================ -->
<!-- CABEÇALHO DA PÁGINA                                          -->
<!-- ============================================================ -->
<div class="page-header mb-6">
    <div>
        <h2 class="page-title"><?php echo $config['nome']; ?></h2>
        <p class="page-subtitle"><?php
            if (isset($config['is_diva'])) echo 'Entrevista Estruturada · Kooij & Francken · Critérios DSM-5';
            elseif (isset($config['manual'])) echo 'Lançamento de Resultados Manuais';
            else echo 'Aplicação Digital · ' . count($config['itens']) . ' itens';
        ?></p>
    </div>
    <a href="testes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in">
    <i class="fas fa-check-circle text-green-500"></i>
    <span class="text-sm font-bold text-green-800">Resultado salvo com sucesso no prontuário do paciente.</span>
</div>
<?php endif; ?>

<?php if ($resultado): ?>
<!-- ============================================================ -->
<!-- TELA DE RESULTADO                                            -->
<!-- ============================================================ -->
<div class="card-shadow p-8 text-center animate-fade-in">
    <?php if (isset($config['is_diva']) && isset($resultado['respostas']['scores'])): 
        $sc = $resultado['respostas']['scores'];
    ?>
        <h3 class="text-lg font-black text-gray-800 mb-6"><?php echo $resultado['classificacao']; ?></h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-xl mx-auto mb-6">
            <div class="bg-orange-50 rounded-xl p-4">
                <p class="text-[10px] font-black text-orange-400 uppercase">A1 Infância</p>
                <p class="text-2xl font-black text-orange-600"><?php echo $sc['A1_infancia']; ?>/9</p>
            </div>
            <div class="bg-primary/5 rounded-xl p-4">
                <p class="text-[10px] font-black text-primary/60 uppercase">A1 Adulto</p>
                <p class="text-2xl font-black text-primary"><?php echo $sc['A1_adulto']; ?>/9</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-4">
                <p class="text-[10px] font-black text-orange-400 uppercase">A2 Infância</p>
                <p class="text-2xl font-black text-orange-600"><?php echo $sc['A2_infancia']; ?>/9</p>
            </div>
            <div class="bg-primary/5 rounded-xl p-4">
                <p class="text-[10px] font-black text-primary/60 uppercase">A2 Adulto</p>
                <p class="text-2xl font-black text-primary"><?php echo $sc['A2_adulto']; ?>/9</p>
            </div>
        </div>
        <p class="text-xs text-gray-400">DSM-5: ≥6 sintomas na infância + ≥5 na vida adulta em pelo menos um domínio.</p>
    <?php else: ?>
        <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl font-black text-primary"><?php echo $resultado['pontuacao']; ?></span>
        </div>
        <h3 class="text-xl font-bold text-gray-800"><?php echo $resultado['classificacao']; ?></h3>
    <?php endif; ?>
    <div class="mt-8 flex justify-center gap-2">
        <a href="paciente_editar.php?id=<?php echo $resultado['paciente_id']; ?>" class="btn btn-primary">Ver Prontuário</a>
        <a href="testes.php" class="btn btn-secondary">Todos os Testes</a>
    </div>
</div>

<?php elseif (isset($config['is_diva'])): ?>
<!-- ============================================================ -->
<!-- DIVA-5 — FORMULÁRIO COMPLETO E FIEL                         -->
<!-- ============================================================ -->
<form method="POST" id="form-diva">
    <!-- Identificação -->
    <div class="card-shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Paciente *</label>
                <select name="paciente_id" required class="form-input">
                    <option value="">Selecione...</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Solicitante</label>
                <input type="text" name="solicitante" value="O Próprio" class="form-input">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Finalidade</label>
                <input type="text" name="finalidade" value="Avaliação Diagnóstica TDAH" class="form-input">
            </div>
        </div>
    </div>

    <!-- Instrução Geral -->
    <div class="mb-6 p-4 bg-orange-50/60 rounded-xl border border-orange-100 text-sm text-orange-800">
        <p class="font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Instruções de Aplicação (DIVA-5):</p>
        <p>Para cada critério, investigue se o sintoma está presente na <strong>Vida Adulta</strong> (últimos 6 meses) e se esteve presente na <strong>Infância</strong> (5 a 12 anos). Use os exemplos como guia. Marque <b>SIM</b> apenas se o paciente confirmar a presença do sintoma de forma frequente e clinicamente relevante.</p>
    </div>

    <?php foreach ($divaCriterios as $parteKey => $parte): ?>
    <!-- Bloco da Parte -->
    <div class="card-shadow overflow-hidden mb-8">
        <div class="p-5 bg-gradient-to-r <?php echo $parteKey === 'A1' ? 'from-blue-50 to-cyan-50' : 'from-orange-50 to-amber-50'; ?> border-b border-gray-100">
            <h3 class="text-sm font-black uppercase tracking-widest <?php echo $parteKey === 'A1' ? 'text-blue-600' : 'text-orange-600'; ?>">
                <?php echo $parte['titulo']; ?>
            </h3>
            <p class="text-xs text-gray-500 mt-1"><?php echo $parte['subtitulo']; ?></p>
        </div>

        <div class="divide-y divide-gray-50">
            <?php foreach ($parte['itens'] as $idx => $item): 
                $num = $idx + 1;
                $fieldKey = "{$parteKey}_{$num}";
            ?>
            <div class="p-5">
                <!-- Critério -->
                <div class="flex items-start gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg <?php echo $parteKey === 'A1' ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600'; ?> flex items-center justify-center text-xs font-black flex-shrink-0"><?php echo $item['criterio']; ?></span>
                    <p class="text-sm font-bold text-gray-800 leading-snug"><?php echo $item['texto']; ?></p>
                </div>

                <!-- Duas colunas: Vida Adulta | Infância -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-11">
                    <!-- Coluna VIDA ADULTA -->
                    <div class="bg-primary/3 rounded-xl p-4 border border-primary/10">
                        <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-2">Vida Adulta (últimos 6 meses)</p>
                        <p class="text-xs text-gray-500 italic mb-3"><?php echo $item['exemplos_adulto']; ?></p>
                        <div class="flex gap-2">
                            <label class="cursor-pointer flex-1">
                                <input type="radio" name="<?php echo $fieldKey; ?>_adulto" value="1" class="sr-only peer">
                                <span class="block w-full text-center py-2 rounded-lg border-2 border-gray-100 text-xs font-black text-gray-300 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-all">SIM</span>
                            </label>
                            <label class="cursor-pointer flex-1">
                                <input type="radio" name="<?php echo $fieldKey; ?>_adulto" value="0" class="sr-only peer" checked>
                                <span class="block w-full text-center py-2 rounded-lg border-2 border-gray-100 text-xs font-black text-gray-300 peer-checked:bg-gray-500 peer-checked:text-white peer-checked:border-gray-500 transition-all">NÃO</span>
                            </label>
                        </div>
                    </div>

                    <!-- Coluna INFÂNCIA -->
                    <div class="bg-orange-50/50 rounded-xl p-4 border border-orange-100/50">
                        <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-2">Infância (5 a 12 anos)</p>
                        <p class="text-xs text-gray-500 italic mb-3"><?php echo $item['exemplos_infancia']; ?></p>
                        <div class="flex gap-2">
                            <label class="cursor-pointer flex-1">
                                <input type="radio" name="<?php echo $fieldKey; ?>_infancia" value="1" class="sr-only peer">
                                <span class="block w-full text-center py-2 rounded-lg border-2 border-gray-100 text-xs font-black text-gray-300 peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500 transition-all">SIM</span>
                            </label>
                            <label class="cursor-pointer flex-1">
                                <input type="radio" name="<?php echo $fieldKey; ?>_infancia" value="0" class="sr-only peer" checked>
                                <span class="block w-full text-center py-2 rounded-lg border-2 border-gray-100 text-xs font-black text-gray-300 peer-checked:bg-gray-500 peer-checked:text-white peer-checked:border-gray-500 transition-all">NÃO</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Parte 3: Idade de Início -->
    <div class="card-shadow p-6 mb-8">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Parte 3 — Idade de Início dos Sintomas</h3>
        <p class="text-xs text-gray-500 mb-3">Critério B do DSM-5: Vários sintomas de desatenção ou hiperatividade-impulsividade estavam presentes <b>antes dos 12 anos de idade</b>.</p>
        <div class="max-w-sm">
            <label class="block text-xs font-bold text-gray-500 mb-2">Com que idade surgiram os primeiros sintomas?</label>
            <input type="text" name="idade_inicio" class="form-input" placeholder="Ex: 6 anos / Não lembra com certeza">
        </div>
    </div>

    <!-- Parte 4: Avaliação de Prejuízos -->
    <div class="card-shadow p-6 mb-8">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Parte 4 — Prejuízos Causados pelos Sintomas</h3>
        <p class="text-xs text-gray-500 mb-4">Critérios C e D do DSM-5: Os sintomas devem causar prejuízo clinicamente significativo em pelo menos <b>dois contextos</b>.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Prejuízo Adulto -->
            <div>
                <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-3">Áreas com prejuízo na Vida Adulta</p>
                <?php foreach ($divaPrejuizos as $key => $label): ?>
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="prejuizo_adulto[]" value="<?php echo $key; ?>" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="text-sm text-gray-700"><?php echo $label; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <!-- Prejuízo Infância -->
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-3">Áreas com prejuízo na Infância</p>
                <?php foreach ($divaPrejuizos as $key => $label): ?>
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="prejuizo_infancia[]" value="<?php echo $key; ?>" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                    <span class="text-sm text-gray-700"><?php echo $label; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Observações -->
    <div class="card-shadow p-6 mb-8">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Observações Clínicas</label>
        <textarea name="observacoes" class="form-input min-h-[100px]" placeholder="Foram entrevistados informantes? Comorbidades observadas? Comportamentos durante a entrevista?"></textarea>
    </div>

    <!-- Submit -->
    <div class="flex justify-end pb-8">
        <button type="submit" class="btn btn-primary px-10 py-4 text-base shadow-xl shadow-primary/20">
            <i class="fas fa-calculator mr-2"></i> Calcular Resultado DIVA-5
        </button>
    </div>
</form>

<?php else: ?>
<!-- ============================================================ -->
<!-- FORMULÁRIO PADRÃO (Escalas Digitais / Lançamento Manual)     -->
<!-- ============================================================ -->
<form method="POST" class="space-y-4">
    <div class="card-shadow p-6 mb-2">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Paciente *</label>
                <select name="paciente_id" required class="form-input">
                    <option value="">Selecione...</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Solicitante</label>
                <input type="text" name="solicitante" value="O Próprio" class="form-input">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Finalidade</label>
                <input type="text" name="finalidade" value="Avaliação Clínica" class="form-input">
            </div>
        </div>
    </div>

    <?php if (isset($config['manual'])): ?>
        <div class="card-shadow p-8">
            <div class="max-w-md mx-auto space-y-6 text-center">
                <i class="fas fa-file-signature text-4xl text-blue-200"></i>
                <h4 class="font-bold text-gray-700">Instrumento Restrito — Lançamento Manual</h4>
                <p class="text-xs text-gray-400">Aplique o teste com o material físico e registre os resultados abaixo.</p>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Pontuação Bruta</label>
                    <input type="number" name="ponto_bruto" required class="form-input text-center text-xl font-bold py-4">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Classificação / Percentil</label>
                    <input type="text" name="ponto_padronizado" required class="form-input text-center py-4" placeholder="Ex: Percentil 75 / Médio Superior">
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100 text-sm text-blue-700 italic mb-2">
            <i class="fas fa-info-circle mr-1"></i> <?php echo $config['instrucao']; ?>
        </div>
        <?php foreach ($config['itens'] as $i => $item): ?>
        <div class="card-shadow p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-lg bg-primary/5 flex items-center justify-center text-xs font-bold text-primary"><?php echo ($i+1); ?></span>
                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($item); ?></p>
            </div>
            <div class="flex gap-1 flex-shrink-0">
                <?php foreach ($config['opcoes'] as $v => $l): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="item_<?php echo $i; ?>" value="<?php echo $v; ?>" required class="sr-only peer">
                    <span class="w-10 h-10 rounded-lg border-2 border-gray-100 flex items-center justify-center text-xs font-bold text-gray-400 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-all hover:border-primary/30" title="<?php echo $l; ?>"><?php echo $v; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="pt-6 flex justify-end">
        <button type="submit" class="btn btn-primary px-10 py-4"><i class="fas fa-check mr-2"></i> Finalizar Avaliação</button>
    </div>
</form>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
