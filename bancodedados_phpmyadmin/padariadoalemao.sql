-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/09/2025 às 21:48
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
  `CEP` char(8) DEFAULT NULL,
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
(3, 'Padaria Pão Quente', '(11) 98765-4321', '12.345.678/0001-90', 'SP', 'São Paulo', 'Centro', '01000-00', 100, 'Rua das Flores', 'contato@paoqueente.com.br', '2005-03-15');

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
  `CEP` char(8) DEFAULT NULL,
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
(1, 'Kerry King', '(47) 99685-5520', 'Masculino', '01.203.4013', '141.554.709-26', 'Casado', 'SP', 'São Paulo', 'Centro', '89230-45', 190, 'Rua 25 de março', '$2y$10$NCqESNfuzXEBVXt2XNkTnOA2bMRZLOX/8Orq8LnwjHg1okdCmWj96', 0, 'kerryking@padaria.com', 1, '0000-00-00', '0000-00-00', 'Gerente'),
(2, 'Ian Lucas Borba', '(92) 03123-1321', 'Masculino', '01.203.4013', '193.239.402-32', 'Viúvo', 'Sa', 'Joinville', 'Espinheiros', '8922687', 189, 'rua', '$2y$10$oTvTgzi1VHWdr7dQzxHLYeMmypykkgAJeHK0SlJStyBUJQW9lyJ3e', 0, 'ian@gmail.com', 2, '0000-00-00', '0000-00-00', 'Padeiro'),
(3, 'Lucas Borba', '(51) 98765-4321', '', '123456789', '123.456.789-00', 'Solteiro', 'RS', 'Porto Alegre', 'Centro', '90000-00', 101, 'Rua das Flores', 'SenhaForte@123', 0, 'lucas.borba@email.com', 2, '1990-05-15', '2025-08-01', 'Analista de Sistemas'),
(4, 'Gustavo Tobler', '(92) 03123-1321', '', '23.342.34-23', '314.452.536-54', 'Solteiro', 'SC', 'Joinville', 'Paraíso', '90323-33', 189, 'rua das flores', '$2y$10$sfS6FgkVVfa78RVMalM7vO1OIVJeiouVfk/KKPJDYY3hVjMn2kPiK', 0, 'toblerone@gmail.com', 1, '2000-09-02', '2025-09-03', 'Gerente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_vendas`
--

CREATE TABLE `itens_vendas` (
  `ID_itensvendas` int(11) NOT NULL,
  `ID_vendas` int(11) NOT NULL,
  `ID_produto` int(11) NOT NULL,
  `Quantidade` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_vendas`
--

INSERT INTO `itens_vendas` (`ID_itensvendas`, `ID_vendas`, `ID_produto`, `Quantidade`, `valor_total`) VALUES
(2, 1, 1, 3, 150.00),
(3, 1, 1, 1, 2.00),
(4, 0, 4, 1, 8.00),
(5, 0, 6, 1, 21.00),
(6, 0, 4, 1, 8.00),
(7, 1, 4, 1, 8.00),
(8, 1, 4, 1, 8.00),
(9, 1, 6, 1, 21.00),
(10, 19, 4, 12, 96.00),
(11, 19, 6, 1, 21.00),
(12, 20, 6, 1, 21.00),
(13, 20, 11, 1, 31.23),
(14, 20, 8, 1, 5.50),
(15, 21, 6, 1, 21.00),
(16, 21, 11, 1, 31.23);

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
(4, 2, 3, 'bolo de leite', 8.00, 'un', '07/10/2025', 7),
(6, 3, 2, 'Pão Frânces', 21.00, 'kg', '07/10/2025', 199),
(7, 1, 3, 'Bolo de arroz', 2.00, 'kg', '2026-03-10', 80),
(8, 1, 6, 'iogurte', 5.50, 'un', '2025-09-15', 100),
(11, 1, 3, 'bolo de arrox', 31.23, 'g', '2000-32-32', 232332);

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
(20, 4, '2025-09-05 15:39:19', NULL, 'ABERTA'),
(21, 4, '2025-09-05 16:46:29', NULL, 'ABERTA');

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
  MODIFY `ID_forn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `ID_func` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `itens_vendas`
--
ALTER TABLE `itens_vendas`
  MODIFY `ID_itensvendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `nivel`
--
ALTER TABLE `nivel`
  MODIFY `nivel_de_acesso` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `ID_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `ID_vendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
