<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// ============================================================
// DIVA 2.0 / DIVA-5 — ESTRUTURA FIEL AO INSTRUMENTO ORIGINAL
// ============================================================
$diva = [
  'A1' => [
    'titulo' => 'Critérios de Déficit de Atenção',
    'itens' => [
      ['cod'=>'A1a','pergunta'=>'Você com frequência não presta atenção suficiente aos detalhes ou comete erros por distração, no trabalho ou em outras atividades?',
       'ex_adulto'=>['Comete erros por distração','Tem que trabalhar devagar para evitar erros','Não lê as instruções com atenção','Não é bom em trabalhos detalhados','Precisa de muito tempo para os detalhes','Perde-se nos detalhes','Trabalha muito rápido e, por isso, comete erros'],
       'ex_infancia'=>['Cometia erros por distração nos trabalhos escolares','Cometia erros devido a uma leitura errada das perguntas','Deixava perguntas sem responder, por não tê-las lido corretamente','Deixava sem responder as perguntas do verso da página nas provas','Os outros comentavam sobre o seu trabalho desleixado','Não revia as respostas dos trabalhos feitos em casa','Precisava de muito tempo para os trabalhos detalhados']],
      ['cod'=>'A1b','pergunta'=>'Você com frequência tem dificuldade em manter-se concentrado durante a realização de tarefas ou atividades?',
       'ex_adulto'=>['Distrai-se facilmente durante o trabalho','Dificuldade em manter a atenção durante leitura','Perde o fio da meada em conversas','Dificuldade em manter a concentração em filmes ou palestras','Não consegue concluir tarefas por perda de foco'],
       'ex_infancia'=>['Sonhava acordado(a) na aula','Não conseguia manter a atenção nas tarefas escolares','Distraía-se facilmente durante as brincadeiras','Dificuldade em manter-se concentrado(a) ao ler','Começava coisas mas não terminava']],
      ['cod'=>'A1c','pergunta'=>'Você com frequência parece não estar ouvindo, quando alguém lhe dirige a palavra?',
       'ex_adulto'=>['As pessoas se queixam de que você não escuta','Parece estar com a cabeça em outro lugar','Precisa que as coisas sejam repetidas','Dificuldade em acompanhar conversas'],
       'ex_infancia'=>['Os pais/professores diziam que vivia no mundo da lua','Precisava ser chamado(a) várias vezes','Não ouvia quando os pais falavam','Parecia estar sempre sonhando acordado(a)']],
      ['cod'=>'A1d','pergunta'=>'Você com frequência não segue as instruções ou não termina as tarefas ou obrigações no trabalho?',
       'ex_adulto'=>['Não conclui tarefas no trabalho','Dificuldade em seguir instruções complexas','Começa várias coisas ao mesmo tempo sem terminar','Precisa de ajuda para concluir tarefas'],
       'ex_infancia'=>['Não terminava os deveres de casa','Deixava trabalhos escolares pela metade','Precisava de muito auxílio para completar tarefas','Não seguia as instruções dos professores']],
      ['cod'=>'A1e','pergunta'=>'Você com frequência tem dificuldade em organizar tarefas e atividades?',
       'ex_adulto'=>['Má gestão do tempo','Não cumpre prazos','Não consegue manter a ordem no trabalho/casa','Dificuldade em planejar atividades','Mesa/escritório sempre desorganizados'],
       'ex_infancia'=>['Quarto/mochila sempre bagunçados','Cadernos desorganizados','Perdia materiais escolares','Não conseguia planejar os estudos','Deixava tudo para a última hora']],
      ['cod'=>'A1f','pergunta'=>'Você com frequência evita, tem aversão ou sente relutância em envolver-se em tarefas que requerem esforço mental contínuo?',
       'ex_adulto'=>['Evita ou adia tarefas burocráticas','Relutância em ler documentos longos','Adia relatórios e formulários','Procrastina tarefas que exigem concentração'],
       'ex_infancia'=>['Evitava deveres de casa','Não gostava de leitura','Reclamava de tarefas que exigiam atenção','Resistia a atividades que exigiam esforço mental']],
      ['cod'=>'A1g','pergunta'=>'Você com frequência perde objetos necessários para as tarefas ou atividades?',
       'ex_adulto'=>['Perde celular, carteira, chaves com frequência','Perde documentos importantes','Esquece onde deixou as coisas','Precisa de muitos lembretes'],
       'ex_infancia'=>['Perdia material escolar frequentemente','Perdia brinquedos e roupas','Esquecia lancheira/casaco na escola','Perdia dinheiro dado pelos pais']],
      ['cod'=>'A1h','pergunta'=>'Você com frequência distrai-se facilmente com estímulos externos?',
       'ex_adulto'=>['Dificuldade em filtrar ruídos','Qualquer estímulo desvia o foco','Pensamentos paralelos durante conversas','Distrai-se com o celular/internet constantemente'],
       'ex_infancia'=>['Qualquer barulho tirava a atenção na aula','Olhava pela janela constantemente','Distraía-se com colegas facilmente','Não conseguia estudar com qualquer ruído']],
      ['cod'=>'A1i','pergunta'=>'Você se esquece com frequência das atividades do dia a dia?',
       'ex_adulto'=>['Esquece compromissos e reuniões','Esquece de pagar contas','Esquece de retornar ligações/mensagens','Esquece de tomar medicação','Precisa de muitos lembretes e alarmes'],
       'ex_infancia'=>['Esquecia recados dos pais','Esquecia de fazer tarefas rotineiras','Esquecia material em casa','Esquecia datas de provas e trabalhos']],
    ]
  ],
  'A2' => [
    'titulo' => 'Critérios de Hiperatividade/Impulsividade',
    'itens' => [
      ['cod'=>'A2a','pergunta'=>'H/I 1. Com frequência mexe de forma irrequieta as mãos e os pés ou remexe-se na cadeira quando está sentado.',
       'ex_adulto'=>['Mexe mãos/pés constantemente','Batuca na mesa','Não consegue ficar parado em reuniões','Muda de posição na cadeira frequentemente'],
       'ex_infancia'=>['Não ficava parado na cadeira na escola','Mexia-se constantemente','A professora chamava atenção por não ficar quieto','Balançava as pernas/pés']],
      ['cod'=>'A2b','pergunta'=>'H/I 2. Com frequência levanta-se do lugar em situações em que é esperado que permaneça sentado.',
       'ex_adulto'=>['Levanta-se em reuniões','Não consegue ficar sentado em restaurantes/cinema','Precisa se mover durante situações formais','Caminha pelo escritório sem necessidade'],
       'ex_infancia'=>['Levantava da carteira sem permissão','Não ficava sentado durante refeições','Andava pela sala de aula','Levantava-se na igreja/missa']],
      ['cod'=>'A2c','pergunta'=>'H/I 3. Com frequência sente-se irrequieto.',
       'ex_adulto'=>['Sensação interna de inquietação','Desconforto em situações sedentárias','Precisa fazer exercício para se acalmar','Sente-se agitado internamente'],
       'ex_infancia'=>['Corria e subia em tudo','Era impossível de conter','Não conseguia brincar calmamente','Sempre agitado(a)']],
      ['cod'=>'A2d','pergunta'=>'H/I 4. Com frequência tem dificuldade em dedicar-se tranquilamente a atividades de lazer.',
       'ex_adulto'=>['Faz tudo com pressa','Não consegue relaxar','Dificuldade em hobbies que exigem paciência','As pessoas reclamam que é agitado demais'],
       'ex_infancia'=>['Não brincava silenciosamente','Fazia muito barulho','As brincadeiras eram sempre agitadas','Os pais diziam que era barulhento demais']],
      ['cod'=>'A2e','pergunta'=>'H/I 5. Com frequência "anda a mil" ou age como se estivesse "ligado a um motor".',
       'ex_adulto'=>['Sente-se sempre ligado','Incapaz de ficar parado por longos períodos','Desconfortável em ficar sem fazer nada','Sempre fazendo algo'],
       'ex_infancia'=>['Era chamado de elétrico(a)','Não parava nunca','Corria de um lado a outro','Os adultos ficavam exaustos']],
      ['cod'=>'A2f','pergunta'=>'H/I 6. Com frequência fala excessivamente.',
       'ex_adulto'=>['Fala demais em contextos sociais','Domina conversas','As pessoas reclamam que fala muito','Dificuldade em ficar calado'],
       'ex_infancia'=>['Falava demais em sala de aula','Tomava bronca por falar fora de hora','Os adultos pediam para parar de falar','Falava sem parar']],
      ['cod'=>'A2g','pergunta'=>'H/I 7. Com frequência responde precipitadamente antes que as perguntas tenham acabado.',
       'ex_adulto'=>['Completa frases dos outros','Responde antes de a pergunta terminar','Não espera o outro acabar de falar','Antecipa o que vão dizer'],
       'ex_infancia'=>['Respondia sem levantar a mão','Interrompia a pergunta do professor','Dava respostas antes da hora','Não esperava sua vez de falar']],
      ['cod'=>'A2h','pergunta'=>'H/I 8. Com frequência tem dificuldade em esperar pela sua vez.',
       'ex_adulto'=>['Impaciência em filas','Dificuldade em aguardar em reuniões','Impaciente no trânsito','Interrompe processos por impaciência'],
       'ex_infancia'=>['Não esperava a vez em jogos','Furava filas','Ficava agitado quando tinha que esperar','Não respeitava a vez dos colegas']],
      ['cod'=>'A2i','pergunta'=>'H/I 9. Com frequência interrompe ou interfere nas atividades dos outros.',
       'ex_adulto'=>['Intromete-se em conversas','Toma decisões pelos outros','Usa coisas dos outros sem pedir','Interrompe reuniões e atividades'],
       'ex_infancia'=>['Entrava nas brincadeiras sem ser convidado','Interrompia conversas de adultos','Era intrusivo(a)','Pegava coisas dos outros sem pedir']],
    ]
  ]
];

// ── Processamento POST ──
$pacientes = $pdo->query("SELECT id,nome FROM pacientes WHERE ativo=1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
try { $pdo->exec("CREATE TABLE IF NOT EXISTS testes_resultados (id INT AUTO_INCREMENT PRIMARY KEY, paciente_id INT NOT NULL, tipo_teste VARCHAR(20) NOT NULL, respostas_json TEXT, pontuacao INT NOT NULL DEFAULT 0, classificacao VARCHAR(100), solicitante VARCHAR(100), finalidade VARCHAR(100), observacoes TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch(Exception $e){}

$resultado = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid = intval($_POST['paciente_id']??0);
    $sol = $_POST['solicitante']??'O Próprio';
    $fin = $_POST['finalidade']??'Avaliação Diagnóstica';
    $obs = $_POST['observacoes']??'';
    $scores = ['A1_adulto'=>0,'A1_infancia'=>0,'A2_adulto'=>0,'A2_infancia'=>0];
    $det = [];
    foreach(['A1','A2'] as $p){
        foreach($diva[$p]['itens'] as $i=>$item){
            $c = $item['cod'];
            $sa = intval($_POST["sint_{$c}_adulto"]??0);
            $si = intval($_POST["sint_{$c}_infancia"]??0);
            $scores[$p.'_adulto'] += $sa;
            $scores[$p.'_infancia'] += $si;
            $det[$c] = ['adulto'=>$sa,'infancia'=>$si,
                'ex_adulto'=>$_POST["ex_{$c}_adulto"]??[],
                'ex_infancia'=>$_POST["ex_{$c}_infancia"]??[],
                'outros_adulto'=>$_POST["outros_{$c}_adulto"]??'',
                'outros_infancia'=>$_POST["outros_{$c}_infancia"]??''];
        }
    }
    $det['idade_inicio'] = $_POST['idade_inicio']??'';
    $det['prejuizo_adulto'] = $_POST['prejuizo_adulto']??[];
    $det['prejuizo_infancia'] = $_POST['prejuizo_infancia']??[];
    $respostas = ['scores'=>$scores,'detalhes'=>$det];
    $pont = $scores['A1_adulto']+$scores['A2_adulto'];
    $desA = ($scores['A1_adulto']>=5 && $scores['A1_infancia']>=6);
    $hipA = ($scores['A2_adulto']>=5 && $scores['A2_infancia']>=6);
    if($desA&&$hipA) $cl='Sugestivo de TDAH — Apresentação Combinada';
    elseif($desA) $cl='Sugestivo de TDAH — Predom. Desatento';
    elseif($hipA) $cl='Sugestivo de TDAH — Predom. Hiperativo-Impulsivo';
    else $cl='Critérios diagnósticos não preenchidos';
    $obs.=" | A1 Inf:{$scores['A1_infancia']}/9 Adu:{$scores['A1_adulto']}/9 | A2 Inf:{$scores['A2_infancia']}/9 Adu:{$scores['A2_adulto']}/9";
    if($pid>0){
        $stmt=$pdo->prepare("INSERT INTO testes_resultados (paciente_id,tipo_teste,respostas_json,pontuacao,classificacao,solicitante,finalidade,observacoes) VALUES(?,?,?,?,?,?,?,?)");
        $stmt->execute([$pid,'DIVA',json_encode($respostas),$pont,$cl,$sol,$fin,$obs]);
        header("Location: diva_aplicar.php?id_resultado=".$pdo->lastInsertId()."&success=1"); exit;
    }
}

// Carregar resultado
$id_resultado = $_GET['id_resultado']??null;
if($id_resultado){
    $stmt=$pdo->prepare("SELECT * FROM testes_resultados WHERE id=?");
    $stmt->execute([$id_resultado]);
    $res=$stmt->fetch(PDO::FETCH_ASSOC);
    if($res) $resultado = ['pontuacao'=>$res['pontuacao'],'classificacao'=>$res['classificacao'],'respostas'=>json_decode($res['respostas_json'],true),'paciente_id'=>$res['paciente_id']];
}

include __DIR__.'/includes/header.php';
?>

<div class="page-header mb-6">
    <div>
        <h2 class="page-title">DIVA 5 — Entrevista Diagnóstica para TDAH em Adultos</h2>
        <p class="page-subtitle">Kooij & Francken · Critérios DSM-5 · Entrevista Estruturada</p>
    </div>
    <a href="testes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in">
    <i class="fas fa-check-circle text-green-500"></i>
    <span class="text-sm font-bold text-green-800">DIVA-5 salvo com sucesso no prontuário.</span>
</div>
<?php endif; ?>

<?php if($resultado):
    $sc = $resultado['respostas']['scores'];
?>
<!-- ══════ RESULTADO: TABELA DE RESUMO (PRINT 2) ══════ -->
<div class="card-shadow overflow-hidden mb-8">
    <div class="p-5 bg-red-600 text-white"><h3 class="text-sm font-black uppercase tracking-widest">Resumo de Sintomas A e H/I</h3></div>
    <p class="p-4 text-sm font-bold text-blue-800">Indique quais os critérios que foram marcados na parte 1 e 2 e some-os</p>
    <div class="overflow-x-auto">
    <table class="w-full text-left text-sm border-collapse">
        <thead><tr class="bg-red-600 text-white text-xs">
            <th class="p-3">Critério DSM</th><th class="p-3">Sintoma</th><th class="p-3 text-center">Presente na idade adulta</th><th class="p-3 text-center">Presente na infância</th>
        </tr></thead>
        <tbody>
        <?php foreach($diva as $pk=>$parte): $bg = $pk==='A1'?'bg-blue-50':'bg-orange-50'; ?>
            <?php foreach($parte['itens'] as $item):
                $d = $resultado['respostas']['detalhes'][$item['cod']]??['adulto'=>0,'infancia'=>0];
            ?>
            <tr class="<?php echo $bg; ?> border-b border-gray-100">
                <td class="p-3 font-black text-red-600"><?php echo $item['cod']; ?></td>
                <td class="p-3 text-gray-700"><?php echo $item['pergunta']; ?></td>
                <td class="p-3 text-center"><?php echo $d['adulto']?'<i class="fas fa-check text-green-500"></i>':'<i class="fas fa-times text-gray-300"></i>'; ?></td>
                <td class="p-3 text-center"><?php echo $d['infancia']?'<i class="fas fa-check text-green-500"></i>':'<i class="fas fa-times text-gray-300"></i>'; ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-red-600 text-white font-black">
                <td class="p-3" colspan="2">Total de critérios de <?php echo $parte['titulo']; ?></td>
                <td class="p-3 text-center text-lg"><?php echo $sc[$pk.'_adulto']; ?>/9</td>
                <td class="p-3 text-center text-lg"><?php echo $sc[$pk.'_infancia']; ?>/9</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card-shadow p-8 text-center mb-8">
    <h3 class="text-xl font-black text-gray-800 mb-2"><?php echo $resultado['classificacao']; ?></h3>
    <p class="text-xs text-gray-400">DSM-5: ≥6 sintomas na infância + ≥5 na vida adulta em pelo menos um domínio</p>
    <div class="mt-6 flex justify-center gap-2">
        <a href="paciente_editar.php?id=<?php echo $resultado['paciente_id']; ?>" class="btn btn-primary">Ver Prontuário</a>
        <a href="testes.php" class="btn btn-secondary">Todos os Testes</a>
    </div>
</div>

<?php else: ?>
<!-- ══════ FORMULÁRIO DE APLICAÇÃO (PRINT 1) ══════ -->
<form method="POST">
    <div class="card-shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Paciente *</label>
                <select name="paciente_id" required class="form-input"><option value="">Selecione...</option>
                <?php foreach($pacientes as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Solicitante</label><input type="text" name="solicitante" value="O Próprio" class="form-input"></div>
            <div><label class="block text-xs font-bold text-gray-400 uppercase mb-2">Finalidade</label><input type="text" name="finalidade" value="Avaliação Diagnóstica TDAH" class="form-input"></div>
        </div>
    </div>

    <?php foreach($diva as $pk=>$parte): ?>
    <h3 class="text-lg font-black text-gray-800 mb-4 mt-10 flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl <?php echo $pk==='A1'?'bg-blue-600':'bg-orange-600'; ?> text-white flex items-center justify-center text-sm font-black"><?php echo $pk; ?></span>
        <?php echo $parte['titulo']; ?>
    </h3>

    <?php foreach($parte['itens'] as $idx=>$item): $c=$item['cod']; ?>
    <div class="card-shadow overflow-hidden mb-6">
        <!-- Cabeçalho do Critério -->
        <div class="p-4 <?php echo $pk==='A1'?'bg-blue-600':'bg-orange-600'; ?> text-white flex items-start gap-3">
            <span class="text-xl font-black"><?php echo $c; ?></span>
            <p class="text-sm font-medium leading-snug"><?php echo $item['pergunta']; ?> <em>Como era durante sua infância?</em></p>
        </div>

        <!-- Duas colunas: Adulto | Infância -->
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">
            <!-- Coluna ADULTO -->
            <div class="p-5">
                <p class="text-xs font-black text-blue-700 uppercase tracking-widest mb-3">Exemplos na idade adulta</p>
                <div class="space-y-2 mb-4">
                    <?php foreach($item['ex_adulto'] as $ei=>$ex): ?>
                    <label class="flex items-start gap-2 cursor-pointer text-sm text-gray-700 hover:text-gray-900">
                        <input type="checkbox" name="ex_<?php echo $c; ?>_adulto[]" value="<?php echo $ei; ?>" class="mt-0.5 rounded border-gray-300 text-blue-600">
                        <?php echo $ex; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-400">Outros:</label>
                    <textarea name="outros_<?php echo $c; ?>_adulto" class="form-input mt-1 min-h-[60px] text-sm" placeholder="..."></textarea>
                </div>
                <div class="pt-3 border-t border-gray-100 flex items-center gap-4">
                    <span class="text-sm font-bold text-gray-600">Sintoma presente?</span>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="sint_<?php echo $c; ?>_adulto" value="1" class="text-blue-600"><span class="text-sm font-bold">Sim</span></label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="sint_<?php echo $c; ?>_adulto" value="0" checked class="text-gray-400"><span class="text-sm font-bold">Não</span></label>
                </div>
            </div>
            <!-- Coluna INFÂNCIA -->
            <div class="p-5">
                <p class="text-xs font-black text-orange-600 uppercase tracking-widest mb-3">Exemplos na infância</p>
                <div class="space-y-2 mb-4">
                    <?php foreach($item['ex_infancia'] as $ei=>$ex): ?>
                    <label class="flex items-start gap-2 cursor-pointer text-sm text-gray-700 hover:text-gray-900">
                        <input type="checkbox" name="ex_<?php echo $c; ?>_infancia[]" value="<?php echo $ei; ?>" class="mt-0.5 rounded border-gray-300 text-orange-500">
                        <?php echo $ex; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-400">Outros:</label>
                    <textarea name="outros_<?php echo $c; ?>_infancia" class="form-input mt-1 min-h-[60px] text-sm" placeholder="..."></textarea>
                </div>
                <div class="pt-3 border-t border-gray-100 flex items-center gap-4">
                    <span class="text-sm font-bold text-gray-600">Sintoma presente?</span>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="sint_<?php echo $c; ?>_infancia" value="1" class="text-orange-500"><span class="text-sm font-bold">Sim</span></label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="sint_<?php echo $c; ?>_infancia" value="0" checked class="text-gray-400"><span class="text-sm font-bold">Não</span></label>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Observações e Submit -->
    <div class="card-shadow p-6 mb-6 mt-8">
        <label class="block text-xs font-black text-gray-400 uppercase mb-3">Idade de início dos sintomas (Critério B: antes dos 12 anos)</label>
        <input type="text" name="idade_inicio" class="form-input max-w-sm" placeholder="Ex: 6 ou 7 anos">
    </div>
    <div class="card-shadow p-6 mb-6">
        <label class="block text-xs font-black text-gray-400 uppercase mb-3">Observações Clínicas</label>
        <textarea name="observacoes" class="form-input min-h-[100px]" placeholder="Informantes, comorbidades, comportamento durante a entrevista..."></textarea>
    </div>
    <div class="flex justify-end pb-8">
        <button type="submit" class="btn btn-primary px-10 py-4 text-base shadow-xl shadow-primary/20">
            <i class="fas fa-calculator mr-2"></i> Calcular Resultado DIVA-5
        </button>
    </div>
</form>
<?php endif; ?>

<?php include __DIR__.'/includes/footer.php'; ?>
