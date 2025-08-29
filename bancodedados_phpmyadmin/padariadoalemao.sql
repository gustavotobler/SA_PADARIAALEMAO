-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Tempo de geração: 28/08/2025 às 19:56
=======
-- Tempo de geração: 28/08/2025 às 21:11
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118
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
<<<<<<< HEAD
-- Erro ao ler a estrutura para a tabela padariadoalemao.categorias: #1932 - Table &#039;padariadoalemao.categorias&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.categorias: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`categorias`&#039; na linha 1
=======

CREATE TABLE `categorias` (
  `id_categorias` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `nome_categoria`) VALUES
(1, 'Sucos'),
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
<<<<<<< HEAD
-- Erro ao ler a estrutura para a tabela padariadoalemao.fornecedores: #1932 - Table &#039;padariadoalemao.fornecedores&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.fornecedores: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`fornecedores`&#039; na linha 1
=======

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
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`ID_forn`, `Nome_forn`, `Telefone`, `CNPJ`, `UF`, `Cidade`, `Bairro`, `CEP`, `Num_empresa`, `Logradouro`, `Email`, `Data_fundacao`) VALUES
(1, '[joao]', '[47996855520]', '[12345678912345]', '[S', '[joinville]', '[espinheiros]', '[1212121', 0, '[rua osvaldo galiza]', '[joao@gmail.com]', '0000-00-00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--
-- Erro ao ler a estrutura para a tabela padariadoalemao.funcionario: #1932 - Table &#039;padariadoalemao.funcionario&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.funcionario: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`funcionario`&#039; na linha 1

<<<<<<< HEAD
-- --------------------------------------------------------
=======
CREATE TABLE `funcionario` (
  `ID_func` int(11) NOT NULL,
  `Nome_func` varchar(40) NOT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Sexo` enum('Masculino','Feminino','Outro') DEFAULT NULL,
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
  `Email` varchar(60) NOT NULL,
  `nivel_de_acesso` int(1) DEFAULT NULL,
  `Data_nascimento` date DEFAULT NULL,
  `Data_admissao` date DEFAULT NULL,
  `Cargo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118

--
-- Estrutura para tabela `itens_vendas`
--
<<<<<<< HEAD
-- Erro ao ler a estrutura para a tabela padariadoalemao.itens_vendas: #1932 - Table &#039;padariadoalemao.itens_vendas&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.itens_vendas: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`itens_vendas`&#039; na linha 1
=======

INSERT INTO `funcionario` (`ID_func`, `Nome_func`, `Telefone`, `Sexo`, `RG`, `CPF`, `Esta_civil`, `UF`, `Cidade`, `Bairro`, `CEP`, `Num_casa`, `Logradouro`, `Senha`, `Email`, `nivel_de_acesso`, `Data_nascimento`, `Data_admissao`, `Cargo`) VALUES
(1, 'Kerry King', NULL, 'Masculino', NULL, NULL, 'Solteiro', 'SP', 'São Paulo', 'Centro', '89230-45', 190, 'Rua 25 de março', 'admin123', 'kerryking@padaria.com', 1, '1974-06-03', '2025-08-19', 'Gerente'),
(2, 'Ian Lucas Borba', '(92) 03123-1321', 'Masculino', '01.203.4013', '193.239.402-32', 'Viúvo', 'Sa', 'Joinville', 'Espinheiros', '8922687', 189, 'rua', '$2y$10$i.FGwrLPX5JIt9jk98EXoOWOdhj2Yc82rVbFfvh7P33.pYz6uDdp2', 'ian@gmail.com', 0, '0000-00-00', '0000-00-00', 'Padeiro'),
(3, 'Lucas Borba', '(51) 98765-4321', '', '123456789', '123.456.789-00', 'Solteiro', 'RS', 'Porto Alegre', 'Centro', '90000-00', 101, 'Rua das Flores', 'SenhaForte@123', 'lucas.borba@email.com', 2, '1990-05-15', '2025-08-01', 'Analista de Sistemas');

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
(1, 0, 1, 1, 2.00);
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118

-- --------------------------------------------------------

--
-- Estrutura para tabela `nivel`
--
-- Erro ao ler a estrutura para a tabela padariadoalemao.nivel: #1932 - Table &#039;padariadoalemao.nivel&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.nivel: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`nivel`&#039; na linha 1

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--
<<<<<<< HEAD
-- Erro ao ler a estrutura para a tabela padariadoalemao.produtos: #1932 - Table &#039;padariadoalemao.produtos&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.produtos: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`produtos`&#039; na linha 1
=======

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
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`ID_produto`, `ID_forn`, `id_categorias`, `Nome_prod`, `Preco_unitario`, `Unid_medida`, `Validade`, `Qntd_produto`) VALUES
(1, 1, 0, 'bolacha', 2.00, 'kg', '10/03/2026', 80);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--
<<<<<<< HEAD
-- Erro ao ler a estrutura para a tabela padariadoalemao.vendas: #1932 - Table &#039;padariadoalemao.vendas&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.vendas: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`vendas`&#039; na linha 1
=======

CREATE TABLE `vendas` (
  `ID_vendas` int(11) NOT NULL,
  `ID_func` int(11) DEFAULT NULL,
  `venda_data` datetime DEFAULT NULL,
  `forma_pagamento` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`ID_vendas`, `ID_func`, `venda_data`, `forma_pagamento`) VALUES
(0, 3, '0000-00-00 00:00:00', '[pix]');

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
  MODIFY `ID_forn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `ID_func` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `itens_vendas`
--
ALTER TABLE `itens_vendas`
  MODIFY `ID_itensvendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `nivel`
--
ALTER TABLE `nivel`
  MODIFY `nivel_de_acesso` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `ID_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
>>>>>>> 0ec6334156b2fb6153ba4786c22a19ceccb6d118
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
