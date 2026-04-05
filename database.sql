-- Banco de Dados: PsiManager
-- Criação das tabelas para o sistema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Estrutura da tabela `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dia_semana_fixo` int(11) NOT NULL COMMENT '1=Seg, 2=Ter, 3=Qua, 4=Qui, 5=Sex',
  `horario_fixo` time NOT NULL,
  `valor_sessao` decimal(10,2) NOT NULL,
  `dia_vencimento` int(11) NOT NULL COMMENT 'Dia do mês (ex: 5, 10, 15)',
  `dias_antecedencia` int(11) NOT NULL DEFAULT 5,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `data_sessao` datetime NOT NULL,
  `link_meet` varchar(255) DEFAULT NULL,
  `status` enum('agendado','realizado','cancelado') NOT NULL DEFAULT 'agendado',
  `notificacao_enviada` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `faturas`
--

CREATE TABLE `faturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `mes_referencia` varchar(7) NOT NULL COMMENT 'Formato YYYY-MM',
  `valor_total` decimal(10,2) NOT NULL,
  `qtd_sessoes` int(11) NOT NULL,
  `asaas_id` varchar(100) DEFAULT NULL,
  `link_pagamento` varchar(255) DEFAULT NULL,
  `status` enum('pendente','pago','vencido') NOT NULL DEFAULT 'pendente',
  `data_pagamento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  CONSTRAINT `faturas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
