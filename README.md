# PsiManager Web

Sistema de gestão para psicólogos com foco em contratos digitais e automação financeira.

## 🚀 Funcionalidades

- **Cadastro de Pacientes ("Contrato Digital")**: Interface amigável para definir regras de sessão e cobrança.
- **Dashboard**: Visão geral de pacientes ativos e faturas pendentes.
- **Automação Financeira**: Script diário que calcula mensalidades baseadas no número de sessões do mês.
- **Design Premium**: Interface limpa e moderna com Tailwind CSS.

## 🛠️ Instalação na Hostinger (ou similar)

1. **Banco de Dados**:
   - Crie um novo banco de dados MySQL na Hostinger.
   - Importe o arquivo `database.sql` via phpMyAdmin.

2. **Arquivos**:
   - Faça upload de todos os arquivos para a pasta `public_html` (ou subpasta).
   - **Importante**: A pasta `public` contém o frontend. Você pode configurar o domínio para apontar para ela ou mover o conteúdo de `public` para a raiz, ajustando os `includes`.
   - *Recomendação*: Mantenha a estrutura e aponte o domínio para a pasta `public`, ou mova o conteúdo de `public` para a raiz e ajuste os `require_once '../config/db.php'` para `require_once 'config/db.php'`.

3. **Configuração**:
   - Edite o arquivo `config/db.php` com as credenciais do seu banco de dados:
     ```php
     $host = 'localhost';
     $dbname = 'u123456789_psimanager'; // Seu banco na Hostinger
     $username = 'u123456789_user';     // Seu usuário
     $password = 'SuaSenhaForte';
     ```

4. **Cron Jobs (Automação)**:
   - No painel da Hostinger, vá em "Cron Jobs".
   - Adicione um novo Cron Job para rodar **Diariamente** (ex: às 08:00).
   - Comando:
     ```bash
     /usr/bin/php /home/u123456789/domains/seudominio.com/public_html/scripts/cron_faturamento.php
     ```
   - *Nota*: Ajuste o caminho conforme a estrutura real do seu servidor.

## 📂 Estrutura de Pastas

- `config/`: Configurações do banco de dados.
- `includes/`: Arquivos compartilhados (cabeçalho, rodapé, funções).
- `public/`: Arquivos acessíveis pelo navegador (telas do sistema).
- `scripts/`: Scripts para execução automática (não devem ser acessíveis publicamente se possível).

## 💡 Como Usar

1. Acesse o sistema e cadastre um novo paciente.
2. Defina o dia fixo da sessão e o dia de vencimento.
3. O sistema calculará automaticamente o valor da fatura dias antes do vencimento e alertará (simulação de log) para envio.
4. Acompanhe os pagamentos na tela "Financeiro".

## 🎨 Personalização

- As cores e fontes estão configuradas no arquivo `includes/header.php` via Tailwind Config e `public/assets/css/style.css`.


## 🤖 Módulos de Automação e Integrações

O sistema agora conta com módulos avançados para faturamento, agendamento e relatórios.

### 1. Configuração Inicial (.env)

Renomeie o arquivo `.env.example` para `.env` na raiz do projeto e configure suas chaves de API:

```ini
# Banco de Dados
DB_HOST=localhost
DB_NAME=psicomanager
DB_USER=root
DB_PASS=

# Asaas (Pagamentos)
ASAAS_API_KEY=sua_chave_api_asaas
ASAAS_URL=https://sandbox.asaas.com/api/v3 # Mude para produção quando pronto

# Google Calendar
GOOGLE_APPLICATION_CREDENTIALS=config/service-account.json
GOOGLE_CALENDAR_ID=primary

# WhatsApp (Notificações)
WHATSAPP_API_URL=https://api.whatsapp.com/send
WHATSAPP_API_KEY=sua_chave_api_whatsapp
```

### 2. Google Calendar

Para que a sincronização funcione:
1. Crie uma Service Account no Google Cloud Console.
2. Baixe o JSON de credenciais e salve como `config/service-account.json`.
3. Compartilhe sua agenda principal com o email da Service Account.

### 3. Cron Jobs (Agendamento de Tarefas)

Configure os seguintes scripts no painel da Hostinger para rodar automaticamente:

| Frequência | Script | Função |
|------------|--------|--------|
| **Diariamente (08:00)** | `scripts/faturamento_diario.php` | Verifica vencimentos e gera faturas rascunho. |
| **A cada 15 min** | `scripts/lembretes_15min.php` | Envia lembretes de sessão via WhatsApp. |
| **Mensal (Dia 01)** | `scripts/relatorio_contador_mensal.php` | Gera relatório CSV de pagamentos para contabilidade. |

### 4. Fluxo de Faturamento

1. O script diário detecta pacientes com vencimento próximo.
2. Um alerta é enviado para o WhatsApp da Dra. com um link de aprovação.
3. Ao clicar no link (`confirm_billing.php`), a cobrança é criada no Asaas.
4. O link de pagamento (Boleto/Pix) é enviado automaticamente para o paciente.

---
Desenvolvido com PHP Puro, MySQL e Tailwind CSS.
