-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08/09/2025 às 19:28
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
-- Banco de dados: `padariadoalemao`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id_categorias` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `nome_categoria`) VALUES
(2, 'Pães'),
(3, 'Bolos'),
(4, 'Salgados'),
(5, 'Café'),
(6, 'Laticínios'),
(7, 'bebidas');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `ID_forn` int(11) NOT NULL,
  `Nome_forn` varchar(40) NOT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `CNPJ` varchar(18) DEFAULT NULL,
  `UF` char(2) DEFAULT NULL,
  `Cidade` varchar(30) DEFAULT NULL,
  `Bairro` varchar(30) DEFAULT NULL,
  `CEP` char(9) DEFAULT NULL,
  `Num_empresa` int(11) DEFAULT NULL,
  `Logradouro` varchar(60) DEFAULT NULL,
  `Email` varchar(60) DEFAULT NULL,
  `Data_fundacao` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`ID_forn`, `Nome_forn`, `Telefone`, `CNPJ`, `UF`, `Cidade`, `Bairro`, `CEP`, `Num_empresa`, `Logradouro`, `Email`, `Data_fundacao`) VALUES
(1, 'João', '(13) 12321-3213', '21.213.148/1447-84', 'AP', '[joinville]', 'espinheiros', '23423-42', 0, 'rua osvaldo galiza', 'joao@gmail.com', NULL),
(2, 'roberta bolos', '47991402801', '1345623', 'SC', 'Joinville', 'Jarivatuba', '89230455', 150, 'rua das flores', 'robertabolos@gmail.com', '0000-00-00'),
(6, 'Borbinha dos pães franceses', '(47) 92394-2343', '12.213.323/3334-56', 'MG', 'Joinville', 'Paraíso', '23543-433', 2992, 'Avenida Costa', 'borbinha@gmail.com', '2023-03-08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `ID_func` int(11) NOT NULL,
  `Nome_func` varchar(40) NOT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Sexo` enum('Masculino','Feminino') DEFAULT NULL,
  `RG` varchar(15) DEFAULT NULL,
  `CPF` varchar(15) DEFAULT NULL,
  `Esta_civil` enum('Solteiro','Casado','Divorciado','Viúvo') DEFAULT NULL,
  `UF` char(2) DEFAULT NULL,
  `Cidade` varchar(30) DEFAULT NULL,
  `Bairro` varchar(30) DEFAULT NULL,
  `CEP` char(9) DEFAULT NULL,
  `Num_casa` int(11) DEFAULT NULL,
  `Logradouro` varchar(60) DEFAULT NULL,
  `Senha` varchar(255) NOT NULL,
  `senha_temporaria` tinyint(1) NOT NULL,
  `Email` varchar(60) NOT NULL,
  `nivel_de_acesso` int(1) DEFAULT NULL,
  `Data_nascimento` date DEFAULT NULL,
  `Data_admissao` date DEFAULT NULL,
  `Cargo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`ID_func`, `Nome_func`, `Telefone`, `Sexo`, `RG`, `CPF`, `Esta_civil`, `UF`, `Cidade`, `Bairro`, `CEP`, `Num_casa`, `Logradouro`, `Senha`, `senha_temporaria`, `Email`, `nivel_de_acesso`, `Data_nascimento`, `Data_admissao`, `Cargo`) VALUES
(1, 'Kerry King', '(47) 99685-5520', 'Masculino', '01.203.4013', '141.554.709-26', 'Casado', 'SP', 'São Paulo', 'Centro', '89230-45', 190, 'Rua 25 de março', '$2y$10$woPtv9merbKi/1pI9rpbIeHDWKouUttM4CK8l0hIZtputrL/KIDhW', 0, 'kerryking@padaria.com', 1, '0000-00-00', '0000-00-00', 'Gerente'),
(2, 'Ian Lucas Borba', '(92) 03123-1321', 'Masculino', '01.203.4013', '193.239.402-32', 'Viúvo', 'Sa', 'Joinville', 'Espinheiros', '8922687', 189, 'rua', '$2y$10$oTvTgzi1VHWdr7dQzxHLYeMmypykkgAJeHK0SlJStyBUJQW9lyJ3e', 0, 'ian@gmail.com', 2, '0000-00-00', '0000-00-00', 'Padeiro'),
(4, 'Gustavo Tobler', '(92) 03123-1321', 'Masculino', '23.342.34-23', '314.452.536-54', 'Solteiro', 'SC', 'Joinville', 'Paraíso', '90323-33', 189, 'rua das flores', '$2y$10$39hK..XKo3C39QCdLBma0uh68238tzRngj2T6JdTn8Cyrx6N7oRLi', 0, 'toblerone@gmail.com', 1, '2000-09-02', '2025-09-03', 'Gerente'),
(8, 'Varaldo Costa', '(47) 83293-2323', 'Masculino', '21.442.35-45', '546.547.585-68', 'Casado', 'SC', 'Joinville', 'Paraíso', '31945-743', 189, 'Avenida', '$2y$10$7lKWcF.1yh9SSC7pTycQqus.N4/v7.3U82iQYaKbEro3VzWPZgxj6', 0, 'varaldo@gmail.com', 2, '2000-03-03', '2025-09-08', 'Gerente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_vendas`
--

CREATE TABLE `itens_vendas` (
  `ID_itensvendas` int(11) NOT NULL,
  `ID_vendas` int(11) NOT NULL,
  `ID_produto` int(11) NOT NULL,
  `Quantidade` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_vendas`
--

INSERT INTO `itens_vendas` (`ID_itensvendas`, `ID_vendas`, `ID_produto`, `Quantidade`, `valor_unitario`, `valor_total`) VALUES
(2, 1, 1, 3, 50.00, 150.00),
(3, 1, 1, 1, 2.00, 2.00),
(4, 0, 4, 1, 8.00, 8.00),
(5, 0, 6, 1, 21.00, 21.00),
(6, 0, 4, 1, 8.00, 8.00),
(7, 1, 4, 1, 8.00, 8.00),
(8, 1, 4, 1, 8.00, 8.00),
(9, 1, 6, 1, 21.00, 21.00),
(10, 19, 4, 12, 8.00, 96.00),
(11, 19, 6, 1, 21.00, 21.00),
(12, 20, 6, 1, 21.00, 21.00),
(13, 20, 11, 1, 31.23, 31.23),
(14, 20, 8, 1, 5.50, 5.50),
(15, 21, 6, 1, 21.00, 21.00),
(16, 21, 11, 1, 31.23, 31.23),
(17, 23, 4, 12, 8.00, 96.00),
(18, 20, 7, 1, 2.00, 2.00),
(19, 20, 4, 1, 8.00, 8.00),
(20, 20, 4, 1, 8.00, 8.00),
(21, 20, 7, 1, 2.00, 2.00),
(22, 30, 7, 1, 2.00, 2.00),
(23, 31, 12, 1, 4.44, 4.44),
(24, 35, 11, 1, 31.23, 31.23),
(25, 46, 4, 1, 8.00, 8.00),
(26, 52, 7, 1, 2.00, 2.00),
(27, 56, 11, 1, 31.23, 31.23),
(28, 56, 8, 1, 5.50, 5.50),
(29, 59, 4, 1, 8.00, 8.00),
(30, 59, 12, 1, 4.44, 4.44),
(31, 64, 11, 1, 31.23, 31.23),
(32, 65, 7, 1, 2.00, 2.00),
(33, 66, 7, 1, 2.00, 2.00),
(34, 67, 7, 1, 2.00, 2.00),
(35, 67, 7, 1, 2.00, 2.00),
(36, 67, 7, 1, 2.00, 2.00),
(37, 68, 7, 1, 2.00, 2.00),
(38, 69, 7, 1, 2.00, 2.00),
(39, 70, 7, 1, 2.00, 2.00),
(40, 71, 7, 1, 2.00, 2.00),
(41, 72, 7, 1, 2.00, 2.00),
(42, 73, 11, 1, 31.23, 31.23),
(43, 74, 11, 1, 31.23, 31.23),
(44, 75, 11, 1, 31.23, 31.23),
(45, 76, 8, 1, 5.50, 5.50),
(46, 83, 4, 2, 8.00, 16.00),
(47, 85, 11, 1, 31.23, 31.23),
(48, 85, 8, 1, 5.50, 5.50),
(49, 90, 14, 12, 1.23, 14.76);

-- --------------------------------------------------------

--
-- Estrutura para tabela `nivel`
--

CREATE TABLE `nivel` (
  `nivel_de_acesso` int(1) NOT NULL,
  `nome_acesso` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `nivel`
--

INSERT INTO `nivel` (`nivel_de_acesso`, `nome_acesso`) VALUES
(1, 'NIVEL_1'),
(2, 'NIVEL_2');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `ID_produto` int(11) NOT NULL,
  `ID_forn` int(11) DEFAULT NULL,
  `id_categorias` int(11) NOT NULL,
  `Nome_prod` varchar(60) DEFAULT NULL,
  `Preco_unitario` decimal(10,2) DEFAULT NULL,
  `Unid_medida` char(2) DEFAULT NULL,
  `Validade` varchar(15) DEFAULT NULL,
  `Qntd_produto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`ID_produto`, `ID_forn`, `id_categorias`, `Nome_prod`, `Preco_unitario`, `Unid_medida`, `Validade`, `Qntd_produto`) VALUES
(4, 2, 3, 'bolo de leite', 8.00, 'un', '07/10/2025', 0),
(7, 1, 3, 'Bolo de arroz', 2.00, 'kg', '2026-03-10', 77),
(8, 1, 6, 'iogurte', 5.50, 'un', '2025-09-15', 98),
(11, 1, 3, 'bolo de arrox', 31.23, 'g', '2000-32-32', 232330),
(12, 1, 3, 'skibidi suco', 4.44, 'mL', '2026-05-07', 7),
(14, 1, 2, 'Pão de frango', 1.23, 'g', '2200-10-10', 110);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `ID_vendas` int(11) NOT NULL,
  `ID_func` int(11) DEFAULT NULL,
  `venda_data` datetime DEFAULT NULL,
  `forma_pagamento` varchar(15) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ABERTA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`ID_vendas`, `ID_func`, `venda_data`, `forma_pagamento`, `status`) VALUES
(20, 4, '2025-09-05 15:39:19', NULL, 'FECHADA'),
(21, 4, '2025-09-05 16:46:29', NULL, 'ABERTA'),
(22, 4, '2025-09-05 16:50:44', NULL, 'ABERTA'),
(23, 4, '2025-09-05 16:50:50', NULL, 'ABERTA'),
(24, 1, '2025-09-07 15:02:35', NULL, 'ABERTA'),
(25, 1, '2025-09-07 15:02:50', NULL, 'FECHADA'),
(26, 1, '2025-09-07 15:07:19', NULL, 'ABERTA'),
(27, 1, '2025-09-07 15:09:02', NULL, 'ABERTA'),
(28, 1, '2025-09-07 15:11:39', NULL, 'ABERTA'),
(29, 1, '2025-09-07 15:16:22', NULL, 'CANCELADA'),
(30, 1, '2025-09-07 15:17:06', NULL, 'FECHADA'),
(31, 1, '2025-09-07 15:18:11', NULL, 'FECHADA'),
(32, 1, '2025-09-07 15:47:03', NULL, 'ABERTA'),
(33, 1, '2025-09-07 15:47:13', NULL, 'ABERTA'),
(34, 1, '2025-09-07 15:59:43', NULL, 'ABERTA'),
(35, 1, '2025-09-07 16:02:25', NULL, 'ABERTA'),
(36, 1, '2025-09-07 16:05:30', 'PIX', 'ABERTA'),
(37, 1, '2025-09-07 16:05:32', 'FIADO', 'ABERTA'),
(38, 1, '2025-09-07 16:10:43', NULL, 'ABERTA'),
(39, 1, '2025-09-07 16:10:44', NULL, 'ABERTA'),
(40, 1, '2025-09-07 16:11:22', NULL, 'ABERTA'),
(41, 1, '2025-09-07 16:12:44', NULL, 'ABERTA'),
(42, 1, '2025-09-07 16:22:14', NULL, 'ABERTA'),
(43, 1, '2025-09-07 16:30:01', NULL, 'ABERTA'),
(44, 1, '2025-09-07 16:35:57', NULL, 'ABERTA'),
(45, 1, '2025-09-07 16:55:48', NULL, 'ABERTA'),
(46, 1, '2025-09-07 16:58:17', NULL, 'FECHADA'),
(47, 1, '2025-09-07 17:07:32', NULL, 'ABERTA'),
(48, 1, '2025-09-07 17:07:34', NULL, 'ABERTA'),
(49, 1, '2025-09-07 17:09:52', NULL, 'ABERTA'),
(50, 1, '2025-09-07 17:12:17', NULL, 'ABERTA'),
(51, 1, '2025-09-07 17:12:27', NULL, 'ABERTA'),
(52, 1, '2025-09-07 17:14:10', NULL, 'ABERTA'),
(53, 1, '2025-09-07 17:17:56', NULL, 'ABERTA'),
(54, 1, '2025-09-07 17:17:57', NULL, 'ABERTA'),
(55, 1, '2025-09-07 17:17:59', NULL, 'ABERTA'),
(56, 1, '2025-09-07 20:32:11', NULL, 'FECHADA'),
(57, 1, '2025-09-07 20:33:16', NULL, 'ABERTA'),
(58, 1, '2025-09-07 20:33:21', NULL, 'ABERTA'),
(59, 1, '2025-09-07 20:36:40', NULL, 'FECHADA'),
(60, 1, '2025-09-07 21:42:02', NULL, 'ABERTA'),
(61, 1, '2025-09-07 22:04:10', NULL, 'ABERTA'),
(62, 1, '2025-09-07 22:25:18', NULL, 'ABERTA'),
(63, 1, '2025-09-07 22:28:10', NULL, 'ABERTA'),
(64, 1, '2025-09-08 03:33:57', NULL, 'ABERTA'),
(65, 1, '2025-09-08 03:34:00', NULL, 'ABERTA'),
(66, 1, '2025-09-08 03:34:00', NULL, 'ABERTA'),
(67, 1, '2025-09-08 03:34:15', NULL, 'CANCELADA'),
(68, 1, '2025-09-08 03:34:25', NULL, 'ABERTA'),
(69, 1, '2025-09-08 03:34:26', NULL, 'ABERTA'),
(70, 1, '2025-09-08 03:34:26', NULL, 'ABERTA'),
(71, 1, '2025-09-08 03:34:26', NULL, 'ABERTA'),
(72, 1, '2025-09-08 03:36:14', NULL, 'ABERTA'),
(73, 1, '2025-09-08 03:36:14', NULL, 'ABERTA'),
(74, 1, '2025-09-08 03:36:15', NULL, 'ABERTA'),
(75, 1, '2025-09-08 03:36:15', NULL, 'ABERTA'),
(76, 1, '2025-09-08 03:36:15', NULL, 'ABERTA'),
(77, 1, '2025-09-07 22:36:18', NULL, 'ABERTA'),
(78, 1, '2025-09-07 22:36:27', NULL, 'ABERTA'),
(79, 1, '2025-09-07 22:46:52', NULL, 'ABERTA'),
(80, 1, '2025-09-07 22:52:53', NULL, 'ABERTA'),
(81, 1, '2025-09-07 22:52:58', NULL, 'ABERTA'),
(82, 1, '2025-09-07 22:53:07', NULL, 'ABERTA'),
(83, 1, '2025-09-07 22:55:58', NULL, 'FECHADA'),
(84, 1, '2025-09-07 22:59:38', NULL, 'ABERTA'),
(85, 1, '2025-09-07 23:01:11', NULL, 'CANCELADA'),
(86, 1, '2025-09-07 23:04:39', NULL, 'ABERTA'),
(87, 1, '2025-09-07 23:06:47', NULL, 'ABERTA'),
(88, 1, '2025-09-07 23:10:14', NULL, 'ABERTA'),
(89, 1, '2025-09-07 23:26:19', NULL, 'ABERTA'),
(90, 1, '2025-09-08 14:26:29', NULL, 'FECHADA');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categorias`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`ID_forn`),
  ADD UNIQUE KEY `CNPJ` (`CNPJ`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`ID_func`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `CPF` (`CPF`),
  ADD KEY `Nivel` (`nivel_de_acesso`);

--
-- Índices de tabela `itens_vendas`
--
ALTER TABLE `itens_vendas`
  ADD PRIMARY KEY (`ID_itensvendas`),
  ADD KEY `ID_venda` (`ID_vendas`,`ID_produto`);

--
-- Índices de tabela `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`nivel_de_acesso`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`ID_produto`),
  ADD KEY `ID_forn` (`ID_forn`),
  ADD KEY `id_categorias` (`id_categorias`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`ID_vendas`),
  ADD KEY `ID_func` (`ID_func`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `ID_forn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `ID_func` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `itens_vendas`
--
ALTER TABLE `itens_vendas`
  MODIFY `ID_itensvendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `nivel`
--
ALTER TABLE `nivel`
  MODIFY `nivel_de_acesso` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `ID_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `ID_vendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`ID_forn`) REFERENCES `fornecedores` (`ID_forn`);

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`ID_func`) REFERENCES `funcionario` (`ID_func`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
