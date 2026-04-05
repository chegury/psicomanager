<?php include __DIR__ . '/includes/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary to-accent rounded-2xl mb-4 shadow-lg">
            <i class="fas fa-user-plus text-white text-2xl"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-text-main">Novo Paciente</h2>
        <p class="text-text-light mt-2">Configuração do Contrato Digital</p>
    </div>

    <form action="actions/save_patient.php" method="POST" class="card-shadow overflow-hidden" id="form-cadastro">
        <div class="p-6 md:p-8 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-user text-sm"></i></span>
                Identificação
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-text-main mb-2">Nome Completo *</label>
                    <div class="input-icon-wrapper"><i class="fas fa-user icon"></i><input type="text" name="nome" required class="form-input pl-11" placeholder="Ex: João da Silva"></div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">CPF *</label>
                    <div class="input-icon-wrapper"><i class="fas fa-id-card icon"></i><input type="text" name="cpf" required class="form-input pl-11 cpf-mask" placeholder="000.000.000-00"></div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">WhatsApp *</label>
                    <div class="input-icon-wrapper"><i class="fab fa-whatsapp icon text-green-500"></i><input type="text" name="whatsapp" required class="form-input pl-11 phone-mask" placeholder="(00) 00000-0000"></div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-text-main mb-2">E-mail</label>
                    <div class="input-icon-wrapper"><i class="fas fa-envelope icon"></i><input type="email" name="email" class="form-input pl-11" placeholder="email@exemplo.com"></div>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-clock text-sm"></i></span>
                Regra da Sessão
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Dia da Semana *</label>
                    <select name="dia_semana_fixo" required class="form-input">
                        <option value="">Selecione...</option>
                        <option value="1">Segunda-feira</option><option value="2">Terça-feira</option>
                        <option value="3">Quarta-feira</option><option value="4">Quinta-feira</option>
                        <option value="5">Sexta-feira</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Frequência</label>
                    <select name="frequencia_combo" class="form-input">
                        <option value="semanal">Semanal</option>
                        <option value="quinzenal_impar">Quinzenal (Ímpar)</option>
                        <option value="quinzenal_par">Quinzenal (Par)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Horário *</label>
                    <input type="time" name="horario_fixo" required class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Valor Sessão *</label>
                    <div class="input-icon-wrapper">
                        <span class="icon text-gray-500 font-semibold text-sm" style="pointer-events:none">R$</span>
                        <input type="text" name="valor_sessao" required class="form-input pl-11 money-mask" placeholder="0,00">
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 border-b border-gray-100">
            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center"><i class="fas fa-wallet text-sm"></i></span>
                Faturamento
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Dia de Vencimento *</label>
                    <select name="dia_vencimento" required class="form-input">
                        <option value="5">Dia 05</option><option value="10">Dia 10</option>
                        <option value="15">Dia 15</option><option value="20">Dia 20</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-main mb-2">Antecedência do Envio</label>
                    <select name="dias_antecedencia" required class="form-input">
                        <option value="5">5 dias antes</option><option value="2">2 dias antes</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 bg-gray-50">
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="pacientes.php" class="btn btn-secondary w-full sm:w-auto justify-center"><i class="fas fa-arrow-left"></i> Cancelar</a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto justify-center" id="btn-submit"><i class="fas fa-save"></i> Salvar e Gerar Agenda</button>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    $('.cpf-mask').mask('000.000.000-00');
    $('.phone-mask').mask('(00) 00000-0000');
    $('.money-mask').mask('000.000.000,00', {reverse: true});
});
document.getElementById('form-cadastro').addEventListener('submit', function(e) {
    const btn = document.getElementById('btn-submit');
    btn.classList.add('btn-loading');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
