-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/04/2026 às 05:59
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `psicomanager`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `data_sessao` datetime NOT NULL,
  `link_meet` varchar(255) DEFAULT NULL,
  `status` enum('agendado','realizado','cancelado') NOT NULL DEFAULT 'agendado',
  `notificacao_enviada` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agenda`
--

INSERT INTO `agenda` (`id`, `paciente_id`, `data_sessao`, `link_meet`, `status`, `notificacao_enviada`, `created_at`) VALUES
(1, 1, '2025-11-05 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(2, 1, '2025-11-12 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(3, 1, '2025-11-19 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(4, 1, '2025-11-26 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(5, 2, '2025-11-05 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(6, 2, '2025-11-12 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(7, 2, '2025-11-19 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(8, 2, '2025-11-26 19:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(9, 3, '2025-11-07 16:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(10, 3, '2025-11-14 16:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(11, 3, '2025-11-21 16:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(12, 3, '2025-11-28 16:00:00', 'Pendente', 'agendado', 0, '2025-11-29 02:44:51'),
(28, 5, '2025-11-30 10:00:00', NULL, '', 0, '2025-11-29 03:49:00'),
(30, 7, '2025-11-01 14:00:00', 'Pendente', '', 0, '2025-11-29 04:25:30'),
(37, 9, '2026-01-05 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 06:05:25'),
(38, 9, '2026-01-12 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 06:05:25'),
(39, 9, '2026-01-19 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 06:05:25'),
(40, 9, '2026-01-26 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 06:05:25'),
(44, 10, '2026-01-14 18:00:00', 'Pendente', 'agendado', 0, '2025-12-14 06:59:57'),
(45, 10, '2026-01-28 18:00:00', 'Pendente', 'agendado', 0, '2025-12-14 06:59:57'),
(54, 9, '2025-12-01 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(55, 9, '2025-12-08 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(56, 9, '2025-12-15 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(57, 9, '2025-12-22 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(58, 9, '2025-12-29 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(59, 10, '2025-12-03 18:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(60, 10, '2025-12-17 18:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(61, 10, '2025-12-31 18:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:05:43'),
(63, 12, '2025-12-15 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:14:09'),
(64, 12, '2025-12-29 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:14:09'),
(65, 12, '2026-01-05 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:14:09'),
(66, 12, '2026-01-19 20:00:00', 'Pendente', 'agendado', 0, '2025-12-14 07:14:09'),
(75, 14, '2026-04-06 20:00:00', 'https://meet.jit.si/PsiManager-31ba3cf8169b', 'agendado', 0, '2026-04-01 03:00:29'),
(76, 14, '2026-04-13 20:00:00', 'https://meet.jit.si/PsiManager-00abac3b674b', 'agendado', 0, '2026-04-01 03:00:29'),
(77, 14, '2026-04-20 20:00:00', 'https://meet.jit.si/PsiManager-d173c04d77ef', 'agendado', 0, '2026-04-01 03:00:29'),
(78, 14, '2026-04-27 20:00:00', 'https://meet.jit.si/PsiManager-637c1e4677b1', 'agendado', 0, '2026-04-01 03:00:29'),
(79, 14, '2026-05-04 20:00:00', 'https://meet.jit.si/PsiManager-f7e74381b292', 'agendado', 0, '2026-04-01 03:00:29'),
(80, 14, '2026-05-11 20:00:00', 'https://meet.jit.si/PsiManager-f8aeb5172748', 'agendado', 0, '2026-04-01 03:00:29'),
(81, 14, '2026-05-18 20:00:00', 'https://meet.jit.si/PsiManager-5121072d0ec0', 'agendado', 0, '2026-04-01 03:00:29'),
(82, 14, '2026-05-25 20:00:00', 'https://meet.jit.si/PsiManager-97dd04479a43', 'agendado', 0, '2026-04-01 03:00:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `faturas`
--

CREATE TABLE `faturas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `mes_referencia` varchar(7) NOT NULL COMMENT 'Formato YYYY-MM',
  `valor_total` decimal(10,2) NOT NULL,
  `qtd_sessoes` int(11) NOT NULL,
  `asaas_id` varchar(100) DEFAULT NULL,
  `link_pagamento` varchar(255) DEFAULT NULL,
  `status` enum('pendente','pago','vencido') NOT NULL DEFAULT 'pendente',
  `data_pagamento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `faturas`
--

INSERT INTO `faturas` (`id`, `paciente_id`, `mes_referencia`, `valor_total`, `qtd_sessoes`, `asaas_id`, `link_pagamento`, `status`, `data_pagamento`, `created_at`) VALUES
(1, 9, '2025-12', 200.00, 1, 'pay_d0yynqy6ysazzusb', 'https://sandbox.asaas.com/i/d0yynqy6ysazzusb', 'pago', '2025-12-14', '2025-12-14 06:51:59'),
(2, 9, '2025-12', 10.00, 0, 'pay_gh8k0vy1y24xj6in', 'https://sandbox.asaas.com/i/gh8k0vy1y24xj6in', 'pendente', NULL, '2025-12-14 06:56:51'),
(3, 9, '2025-12', 10.00, 0, 'pay_ohk5v285fkr8rlqs', 'https://sandbox.asaas.com/i/ohk5v285fkr8rlqs', 'pendente', NULL, '2025-12-14 06:56:51'),
(4, 9, '2025-12', 1000.00, 5, 'pay_8ju7u5i1wll9g0qi', 'https://sandbox.asaas.com/i/8ju7u5i1wll9g0qi', 'pago', '2025-12-14', '2025-12-14 07:25:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
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
  `foto` varchar(255) DEFAULT NULL,
  `frequencia` enum('semanal','quinzenal') DEFAULT 'semanal',
  `semana_inicio` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pacientes`
--

INSERT INTO `pacientes` (`id`, `nome`, `cpf`, `whatsapp`, `email`, `dia_semana_fixo`, `horario_fixo`, `valor_sessao`, `dia_vencimento`, `dias_antecedencia`, `ativo`, `created_at`, `foto`, `frequencia`, `semana_inicio`) VALUES
(1, 'vinicius batista de souza chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 3, '19:00:00', 150.00, 10, 5, 0, '2025-11-29 01:49:32', 'paciente_1_1764384488.jpg', 'semanal', 1),
(2, 'vinicius batista de souza chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 3, '19:00:00', 250.00, 5, 5, 0, '2025-11-29 02:19:32', NULL, 'semanal', 1),
(3, 'vinicius batista de souza chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 5, '16:00:00', 100.00, 5, 5, 0, '2025-11-29 02:23:23', NULL, 'semanal', 1),
(4, 'vinicius batista de souza chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 1, '13:00:00', 150.00, 5, 5, 0, '2025-11-29 03:46:03', NULL, 'semanal', 1),
(5, 'Teste Inativacao', '', '11999999999', 'teste1@email.com', 1, '00:00:00', 150.00, 10, 5, 0, '2025-11-29 03:49:00', NULL, 'semanal', 1),
(6, 'Teste UI Agenda', '', '11888888888', 'teste2@email.com', 0, '00:00:00', 200.00, 15, 5, 0, '2025-11-29 03:49:00', NULL, 'semanal', 1),
(7, 'Teste Quinzenal', '', '11999999999', 'teste@bi.com', 1, '14:00:00', 200.00, 10, 5, 0, '2025-11-29 04:25:30', NULL, 'semanal', 1),
(8, 'vinicius batista de souza chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 1, '15:00:00', 200.00, 5, 5, 0, '2025-11-29 04:43:11', NULL, 'quinzenal', 1),
(9, 'vinicius batista de souza chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 1, '20:00:00', 200.00, 5, 5, 0, '2025-12-14 06:03:08', 'paciente_9_1765692299.png', 'semanal', 1),
(10, 'Shirley', '41164693867', '11966811999', '', 1, '18:00:00', 165.00, 10, 5, 0, '2025-12-14 06:12:34', NULL, 'quinzenal', 1),
(11, 'Leo canada', '41164693867', '11966811999', '', 1, '20:00:00', 200.00, 5, 5, 0, '2025-12-14 07:07:01', NULL, 'semanal', 1),
(12, 'Geralda', '41241873860', '11966811999', '', 1, '20:00:00', 100.00, 5, 5, 0, '2025-12-14 07:14:09', NULL, 'quinzenal', 1),
(13, 'Vinicius Batista de Souza Chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 1, '20:00:00', 2.00, 5, 5, 0, '2026-04-01 02:29:29', NULL, 'semanal', 1),
(14, 'Vinicius Chegury', '41241873860', '11966811999', 'chegury@hotmail.com', 1, '20:00:00', 200.00, 5, 5, 1, '2026-04-01 03:00:29', NULL, 'semanal', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `testes_resultados`
--

CREATE TABLE `testes_resultados` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `tipo_teste` varchar(20) NOT NULL,
  `respostas_json` text DEFAULT NULL,
  `pontuacao` int(11) NOT NULL DEFAULT 0,
  `classificacao` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `testes_resultados`
--

INSERT INTO `testes_resultados` (`id`, `paciente_id`, `tipo_teste`, `respostas_json`, `pontuacao`, `classificacao`, `observacoes`, `created_at`) VALUES
(1, 13, 'BAI', '[3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3]', 63, 'Grave', '', '2026-04-01 02:32:39'),
(2, 13, 'BAI', '[0,0,1,2,3,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]', 6, 'Mínimo', '', '2026-04-01 02:49:05'),
(3, 14, 'GAD7', '[3,3,3,3,3,3,3]', 21, 'Grave', 'é um gostoso', '2026-04-01 03:04:49');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Índices de tabela `faturas`
--
ALTER TABLE `faturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Índices de tabela `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `testes_resultados`
--
ALTER TABLE `testes_resultados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de tabela `faturas`
--
ALTER TABLE `faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `testes_resultados`
--
ALTER TABLE `testes_resultados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agenda`
--
ALTER TABLE `agenda`
  ADD CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `faturas`
--
ALTER TABLE `faturas`
  ADD CONSTRAINT `faturas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `testes_resultados`
--
ALTER TABLE `testes_resultados`
  ADD CONSTRAINT `testes_resultados_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
