-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12/09/2025 às 21:32
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
(1, 'Fornecedor Central', '(47) 3333-4444', '12.345.678/0001-10', 'SC', 'Joinville', 'Centro', '89200-00', 10, 'Rua A', 'central@forn.com', '2000-01-10'),
(2, 'Confeitaria Doce Vida', '(47) 9999-1111', '22.333.444/0001-20', 'SC', 'Joinville', 'Jarivatuba', '89230-20', 5, 'Rua B', 'docevida@forn.com', '2010-05-05'),
(3, 'Laticinios Sul Ltda', '(11) 8888-2222', '33.444.555/0001-30', 'SP', 'São Paulo', 'Bela Vista', '01010-01', 20, 'Av. C', 'laticinios@forn.com', '1995-07-15'),
(4, 'Bebidas Brasil', '(21) 7777-3333', '44.555.666/0001-40', 'RJ', 'Rio de Janeiro', 'Copacabana', '22000-00', 8, 'Av. D', 'bebidas@forn.com', '2008-03-20'),
(5, 'Padaria Pão Quente', '(11) 98765-4321', '55.666.777/0001-50', 'SP', 'São Paulo', 'Centro', '01000-00', 12, 'Rua das Flores', 'paoquente@forn.com', '2005-03-15'),
(6, 'Distribuidora Oeste', '(19) 4444-5555', '66.777.888/0001-60', 'SP', 'Campinas', 'Centro', '13000-00', 6, 'Rua E', 'oeste@forn.com', '2012-11-11'),
(7, 'Doces & Cias', '(47) 3333-2222', '77.888.999/0001-70', 'SC', 'Joinville', 'Espinheiros', '89220-00', 4, 'Rua F', 'doces@forn.com', '2018-02-02'),
(8, 'Produtos Naturais', '(47) 3210-1234', '88.999.000/0001-80', 'SC', 'Joinville', 'Atiradores', '89210-00', 7, 'Rua G', 'naturais@forn.com', '2016-06-06');

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
(1, 'Kerry King', '(47) 99685-5520', 'Masculino', '01.203.4013', '141.554.709-26', 'Casado', 'SC', 'Joinville', 'Centro', '89230-45', 190, 'Rua 25 de Março', '$2y$10$hash1', 0, 'kerry@padaria.com', 1, '1980-05-10', '2020-01-01', 'Gerente'),
(2, 'Ian Lucas', '(92) 03123-1321', 'Masculino', '02.203.4013', '193.239.402-32', 'Solteiro', 'SC', 'Joinville', 'Espinheiros', '89226-87', 189, 'Rua A', '$2y$10$hash2', 0, 'ian@padaria.com', 2, '1992-08-15', '2023-06-01', 'Padeiro'),
(3, 'Lucas Borba', '(51) 98765-4321', 'Masculino', '03.203.4013', '123.456.789-00', 'Solteiro', 'RS', 'Porto Alegre', 'Centro', '90000-00', 101, 'Rua das Flores', '$2y$10$hash3', 0, 'lucas@padaria.com', 2, '1990-05-15', '2025-08-01', 'Analista'),
(4, 'Gustavo Tobler', '(92) 03123-1321', 'Masculino', '04.203.4013', '314.452.536-54', 'Solteiro', 'SC', 'Joinville', 'Paraíso', '90323-33', 189, 'Rua B', '$2y$10$hash4', 0, 'gustavo@padaria.com', 1, '2000-09-02', '2025-09-03', 'Gerente'),
(5, 'Mariana Silva', '(47) 91234-5678', 'Feminino', '05.203.4013', '321.654.987-11', '', 'SC', 'Joinville', 'Atiradores', '89210-10', 50, 'Rua C', '$2y$10$hash5', 0, 'mariana@padaria.com', 2, '1995-12-12', '2024-02-15', 'Atendente'),
(6, 'Roberta Bolos', '(47) 99140-2801', 'Feminino', '06.203.4013', '222.333.444-55', '', 'SC', 'Joinville', 'Jarivatuba', '89230-45', 150, 'Rua das Flores', '$2y$10$hash6', 0, 'roberta@padaria.com', 2, '1988-03-03', '2015-09-01', 'Confeiteira');

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
(1, 1, 1, 4, 0.50, 2.00),
(2, 1, 27, 1, 5.00, 5.00),
(3, 2, 12, 1, 35.00, 35.00),
(4, 2, 11, 2, 18.00, 36.00),
(5, 3, 19, 3, 6.00, 18.00),
(6, 3, 21, 2, 5.50, 11.00),
(7, 4, 33, 1, 5.50, 5.50),
(8, 4, 35, 1, 38.00, 38.00),
(9, 5, 3, 2, 35.00, 70.00),
(10, 5, 6, 6, 6.50, 39.00),
(11, 6, 24, 1, 7.50, 7.50),
(12, 6, 26, 2, 6.80, 13.60),
(13, 7, 9, 3, 4.50, 13.50),
(14, 7, 10, 1, 12.90, 12.90),
(15, 8, 41, 2, 7.00, 14.00),
(16, 8, 43, 4, 3.00, 12.00),
(17, 9, 13, 1, 28.90, 28.90),
(18, 9, 14, 1, 65.00, 65.00),
(19, 10, 7, 5, 9.90, 49.50),
(20, 10, 8, 2, 10.50, 21.00),
(21, 11, 15, 1, 22.00, 22.00),
(22, 11, 16, 1, 55.00, 55.00),
(23, 12, 25, 6, 5.90, 35.40),
(24, 12, 26, 3, 6.80, 20.40),
(25, 13, 27, 1, 5.00, 5.00),
(26, 13, 29, 1, 9.00, 9.00),
(27, 14, 30, 2, 12.00, 24.00),
(28, 14, 31, 1, 10.00, 10.00),
(29, 15, 32, 2, 11.00, 22.00),
(30, 15, 33, 3, 5.50, 16.50),
(31, 16, 34, 2, 5.50, 11.00),
(32, 16, 35, 1, 38.00, 38.00),
(33, 17, 36, 1, 36.00, 36.00),
(34, 17, 37, 4, 4.50, 18.00),
(35, 18, 38, 2, 5.00, 10.00),
(36, 18, 39, 1, 8.50, 8.50),
(37, 19, 40, 1, 12.00, 12.00),
(38, 19, 41, 1, 7.00, 7.00),
(39, 20, 42, 2, 6.50, 13.00),
(40, 20, 43, 6, 3.00, 18.00),
(41, 21, 44, 2, 3.50, 7.00),
(42, 21, 45, 1, 45.00, 45.00),
(43, 22, 46, 3, 9.50, 28.50),
(44, 22, 47, 1, 70.00, 70.00),
(45, 23, 48, 1, 58.00, 58.00),
(46, 23, 49, 4, 4.00, 16.00),
(47, 24, 50, 1, 45.00, 45.00),
(48, 24, 1, 10, 0.50, 5.00),
(49, 25, 2, 5, 7.50, 37.50),
(50, 25, 3, 1, 35.00, 35.00),
(51, 26, 4, 2, 5.90, 11.80),
(52, 26, 5, 3, 8.90, 26.70),
(53, 27, 6, 8, 6.50, 52.00),
(54, 27, 7, 2, 9.90, 19.80),
(55, 28, 8, 6, 10.50, 63.00),
(56, 28, 9, 3, 4.50, 13.50),
(57, 29, 10, 2, 12.90, 25.80),
(58, 29, 11, 1, 18.00, 18.00),
(59, 30, 12, 1, 35.00, 35.00),
(60, 30, 13, 1, 28.90, 28.90),
(61, 31, 14, 1, 65.00, 65.00),
(62, 31, 15, 2, 22.00, 44.00),
(63, 32, 16, 1, 55.00, 55.00),
(64, 32, 17, 1, 33.00, 33.00),
(65, 33, 18, 2, 31.00, 62.00),
(66, 33, 19, 4, 6.00, 24.00),
(67, 34, 20, 2, 7.00, 14.00),
(68, 34, 21, 3, 5.50, 16.50),
(69, 35, 22, 2, 8.00, 16.00),
(70, 35, 23, 4, 6.50, 26.00),
(71, 36, 24, 3, 7.50, 22.50),
(72, 36, 25, 5, 5.90, 29.50),
(73, 37, 26, 2, 6.80, 13.60),
(74, 37, 27, 6, 5.00, 30.00),
(75, 38, 28, 4, 7.00, 28.00),
(76, 38, 29, 2, 9.00, 18.00),
(77, 39, 30, 1, 12.00, 12.00),
(78, 39, 31, 2, 10.00, 20.00),
(79, 40, 32, 3, 11.00, 33.00),
(80, 40, 33, 5, 5.50, 27.50);

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
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `ID_pagamento` int(11) NOT NULL,
  `ID_vendas` int(11) NOT NULL,
  `metodo` varchar(50) DEFAULT NULL,
  `valor_pago` decimal(10,2) NOT NULL,
  `troco` decimal(10,2) DEFAULT 0.00,
  `data` datetime DEFAULT current_timestamp(),
  `ID_func_registro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamentos`
--

INSERT INTO `pagamentos` (`ID_pagamento`, `ID_vendas`, `metodo`, `valor_pago`, `troco`, `data`, `ID_func_registro`) VALUES
(1, 1, 'DINHEIRO', 7.00, 0.00, '2025-09-01 08:05:00', 1),
(2, 2, 'PIX', 71.00, 0.00, '2025-09-01 09:20:00', 2),
(3, 3, 'CARTAO', 54.00, 0.00, '2025-09-01 09:35:00', 2),
(4, 4, 'DINHEIRO', 43.50, 0.00, '2025-09-01 10:05:00', 3),
(5, 5, 'CARTAO', 109.00, 0.00, '2025-09-01 10:35:00', 1),
(6, 7, 'PIX', 27.40, 0.00, '2025-09-01 11:35:00', 5),
(7, 8, 'CARTAO', 26.00, 0.00, '2025-09-01 12:05:00', 6),
(8, 9, 'DINHEIRO', 93.90, 0.00, '2025-09-01 12:50:00', 1),
(9, 10, 'PIX', 70.50, 0.00, '2025-09-01 13:15:00', 2),
(10, 11, 'DINHEIRO', 77.00, 0.00, '2025-09-01 14:25:00', 3),
(11, 12, 'CARTAO', 31.60, 0.00, '2025-09-01 15:05:00', 4),
(12, 13, 'DINHEIRO', 14.00, 0.00, '2025-09-02 08:15:00', 5),
(13, 14, 'PIX', 24.00, 0.00, '2025-09-02 09:05:00', 6),
(14, 15, 'CARTAO', 22.00, 0.00, '2025-09-02 09:35:00', 1),
(15, 17, 'PIX', 55.00, 0.00, '2025-09-02 11:05:00', 3),
(16, 18, 'CARTAO', 20.00, 0.00, '2025-09-02 11:35:00', 4),
(17, 19, 'DINHEIRO', 19.00, 0.00, '2025-09-02 12:05:00', 5),
(18, 20, 'PIX', 31.00, 0.00, '2025-09-02 12:50:00', 6),
(19, 21, 'CARTAO', 9.00, 0.00, '2025-09-02 13:35:00', 1),
(20, 22, 'DINHEIRO', 84.50, 0.00, '2025-09-02 14:20:00', 2),
(21, 23, 'PIX', 74.00, 0.00, '2025-09-02 15:05:00', 3),
(22, 24, 'CARTAO', 50.00, 0.00, '2025-09-03 08:05:00', 4),
(23, 25, 'DINHEIRO', 72.50, 0.00, '2025-09-03 09:05:00', 5),
(24, 26, 'PIX', 39.80, 0.00, '2025-09-03 10:05:00', 6),
(25, 27, 'CARTAO', 52.00, 0.00, '2025-09-03 11:05:00', 1),
(26, 28, 'DINHEIRO', 40.50, 0.00, '2025-09-03 12:05:00', 2),
(27, 29, 'PIX', 35.80, 0.00, '2025-09-03 13:05:00', 3),
(28, 30, 'CARTAO', 40.00, 0.00, '2025-09-03 14:05:00', 4),
(29, 31, 'DINHEIRO', 63.00, 0.00, '2025-09-03 15:05:00', 5),
(30, 32, 'PIX', 55.00, 0.00, '2025-09-04 08:05:00', 6);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_itens`
--

CREATE TABLE `pagamentos_itens` (
  `ID_pagamento_item` int(11) NOT NULL,
  `ID_vendas` int(11) NOT NULL,
  `ID_produto` int(11) NOT NULL,
  `Quantidade` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamentos_itens`
--

INSERT INTO `pagamentos_itens` (`ID_pagamento_item`, `ID_vendas`, `ID_produto`, `Quantidade`, `valor_total`) VALUES
(1, 1, 1, 4, 2.00),
(2, 2, 12, 1, 35.00),
(3, 2, 11, 2, 36.00),
(4, 3, 19, 3, 18.00),
(5, 4, 33, 1, 5.50),
(6, 5, 3, 2, 70.00),
(7, 6, 24, 1, 7.50),
(8, 7, 9, 3, 13.50),
(9, 8, 41, 2, 14.00),
(10, 9, 13, 1, 28.90),
(11, 10, 7, 5, 49.50),
(12, 11, 15, 1, 22.00),
(13, 12, 25, 6, 35.40),
(14, 13, 27, 1, 5.00),
(15, 14, 30, 2, 24.00),
(16, 15, 32, 2, 22.00),
(17, 16, 34, 2, 11.00),
(18, 17, 36, 1, 36.00),
(19, 18, 38, 2, 10.00),
(20, 19, 40, 1, 12.00),
(21, 20, 42, 2, 13.00),
(22, 21, 44, 2, 7.00),
(23, 22, 46, 3, 28.50),
(24, 23, 48, 1, 58.00),
(25, 24, 50, 1, 45.00),
(26, 25, 2, 5, 37.50),
(27, 26, 4, 2, 11.80),
(28, 27, 6, 8, 52.00),
(29, 28, 8, 6, 63.00),
(30, 29, 10, 2, 25.80),
(31, 30, 12, 1, 35.00),
(32, 31, 14, 1, 65.00),
(33, 32, 16, 1, 55.00),
(34, 33, 18, 2, 62.00),
(35, 34, 20, 2, 14.00),
(36, 35, 22, 2, 16.00),
(37, 36, 24, 3, 22.50),
(38, 37, 26, 2, 13.60),
(39, 38, 27, 6, 30.00),
(40, 39, 29, 2, 18.00),
(41, 40, 31, 2, 20.00),
(42, 1, 27, 1, 5.00),
(43, 2, 11, 1, 18.00),
(44, 3, 21, 1, 5.50),
(45, 4, 35, 1, 38.00),
(46, 5, 6, 2, 13.00),
(47, 6, 26, 1, 6.80),
(48, 7, 9, 2, 9.00),
(49, 8, 43, 4, 12.00),
(50, 9, 13, 1, 28.90),
(51, 10, 7, 3, 29.70),
(52, 11, 15, 1, 22.00),
(53, 12, 25, 1, 5.90),
(54, 13, 27, 1, 5.00),
(55, 14, 30, 1, 12.00),
(56, 15, 32, 1, 11.00),
(57, 16, 33, 2, 11.00),
(58, 17, 34, 1, 5.50),
(59, 18, 35, 1, 38.00),
(60, 19, 36, 1, 36.00);

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
(1, 1, 2, 'Pão Francês', 0.50, 'UN', '2025-12', 1000),
(2, 1, 2, 'Pão de Forma Integral', 7.50, 'UN', '2026-01', 120),
(3, 2, 2, 'Pão de Queijo Congelado', 35.00, 'KG', '2026-06', 80),
(4, 2, 2, 'Baguete Italiana', 5.90, 'UN', '2025-12', 60),
(5, 3, 2, 'Pão Australiano', 8.90, 'UN', '2026-02', 45),
(6, 3, 2, 'Croissant de Manteiga', 6.50, 'UN', '2025-12', 90),
(7, 4, 2, 'Pão de Centeio', 9.90, 'UN', '2026-03', 40),
(8, 4, 2, 'Pão Multigrãos', 10.50, 'UN', '2026-04', 70),
(9, 5, 2, 'Pão Doce', 4.50, 'UN', '2025-12', 85),
(10, 5, 2, 'Pão Italiano', 12.90, 'UN', '2026-05', 50),
(11, 1, 3, 'Bolo de Chocolate Pequeno', 18.00, 'UN', '2025-12', 40),
(12, 1, 3, 'Bolo de Chocolate Grande', 35.00, 'UN', '2026-01', 25),
(13, 2, 3, 'Bolo de Cenoura', 28.90, 'UN', '2026-01', 30),
(14, 2, 3, 'Bolo Red Velvet', 65.00, 'UN', '2026-02', 15),
(15, 3, 3, 'Bolo de Fubá', 22.00, 'UN', '2025-12', 25),
(16, 3, 3, 'Bolo de Morango', 55.00, 'UN', '2026-03', 18),
(17, 4, 3, 'Bolo de Coco', 33.00, 'UN', '2026-04', 28),
(18, 4, 3, 'Bolo de Limão', 31.00, 'UN', '2026-05', 24),
(19, 5, 4, 'Coxinha de Frango', 6.00, 'UN', '2025-12', 150),
(20, 5, 4, 'Empada de Palmito', 7.00, 'UN', '2025-12', 120),
(21, 1, 4, 'Quibe Frito', 5.50, 'UN', '2025-12', 180),
(22, 1, 4, 'Pastel de Carne', 8.00, 'UN', '2025-12', 140),
(23, 2, 4, 'Esfiha de Queijo', 6.50, 'UN', '2025-12', 160),
(24, 2, 4, 'Pão de Batata Recheado', 7.50, 'UN', '2025-12', 130),
(25, 3, 4, 'Croquete de Carne', 5.90, 'UN', '2025-12', 170),
(26, 3, 4, 'Risole Presunto e Queijo', 6.80, 'UN', '2025-12', 145),
(27, 4, 5, 'Café Expresso Pequeno', 5.00, 'UN', '2025-12', 200),
(28, 4, 5, 'Café Expresso Grande', 7.00, 'UN', '2025-12', 180),
(29, 5, 5, 'Cappuccino Tradicional', 9.00, 'UN', '2025-12', 160),
(30, 5, 5, 'Mocha com Chantilly', 12.00, 'UN', '2025-12', 140),
(31, 6, 5, 'Café Latte', 10.00, 'UN', '2025-12', 150),
(32, 6, 5, 'Café Gelado', 11.00, 'UN', '2025-12', 130),
(33, 7, 6, 'Leite Integral 1L', 5.50, 'LT', '2025-12', 200),
(34, 7, 6, 'Leite Desnatado 1L', 5.50, 'LT', '2025-12', 180),
(35, 8, 6, 'Queijo Mussarela 1kg', 38.00, 'KG', '2026-06', 90),
(36, 8, 6, 'Queijo Prato 1kg', 36.00, 'KG', '2026-06', 70),
(37, 3, 6, 'Iogurte Natural 170g', 4.50, 'UN', '2025-12', 150),
(38, 3, 6, 'Iogurte Morango 170g', 5.00, 'UN', '2025-12', 140),
(39, 2, 7, 'Suco de Laranja 1L', 8.50, 'LT', '2025-12', 60),
(40, 2, 7, 'Suco de Uva 1L', 12.00, 'LT', '2025-12', 40),
(41, 4, 7, 'Refrigerante Cola 2L', 7.00, 'LT', '2026-01', 100),
(42, 4, 7, 'Refrigerante Guaraná 2L', 6.50, 'LT', '2026-01', 95),
(43, 5, 7, 'Água Mineral 500ml', 3.00, 'LT', '2026-12', 150),
(44, 5, 7, 'Água com Gás 500ml', 3.50, 'LT', '2026-12', 140),
(45, 6, 4, 'Empadão de Frango', 45.00, 'UN', '2025-12', 20),
(46, 6, 4, 'Bolinho de Bacalhau', 9.50, 'UN', '2025-12', 60),
(47, 7, 3, 'Bolo Trufado', 70.00, 'UN', '2026-05', 12),
(48, 7, 3, 'Bolo Prestígio', 58.00, 'UN', '2026-04', 22),
(49, 8, 2, 'Pão de Alho', 4.00, 'UN', '2025-12', 90),
(50, 8, 5, 'Café Especial Grãos 1kg', 45.00, 'KG', '2026-12', 50);

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
(1, 1, '2025-09-01 08:00:00', 'DINHEIRO', 'FECHADA'),
(2, 2, '2025-09-01 09:15:00', 'PIX', 'FECHADA'),
(3, 2, '2025-09-01 09:30:00', 'CARTAO', 'FECHADA'),
(4, 3, '2025-09-01 10:00:00', 'DINHEIRO', 'FECHADA'),
(5, 1, '2025-09-01 10:30:00', 'CARTAO', 'FECHADA'),
(6, 4, '2025-09-01 11:00:00', 'DINHEIRO', 'ABERTA'),
(7, 5, '2025-09-01 11:30:00', 'PIX', 'FECHADA'),
(8, 6, '2025-09-01 12:00:00', 'CARTAO', 'FECHADA'),
(9, 1, '2025-09-01 12:45:00', 'DINHEIRO', 'FECHADA'),
(10, 2, '2025-09-01 13:10:00', 'PIX', 'FECHADA'),
(11, 3, '2025-09-01 14:20:00', 'DINHEIRO', 'FECHADA'),
(12, 4, '2025-09-01 15:00:00', 'CARTAO', 'FECHADA'),
(13, 5, '2025-09-02 08:10:00', 'DINHEIRO', 'FECHADA'),
(14, 6, '2025-09-02 09:00:00', 'PIX', 'FECHADA'),
(15, 1, '2025-09-02 09:30:00', 'CARTAO', 'FECHADA'),
(16, 2, '2025-09-02 10:00:00', 'DINHEIRO', 'ABERTA'),
(17, 3, '2025-09-02 11:00:00', 'PIX', 'FECHADA'),
(18, 4, '2025-09-02 11:30:00', 'CARTAO', 'FECHADA'),
(19, 5, '2025-09-02 12:00:00', 'DINHEIRO', 'FECHADA'),
(20, 6, '2025-09-02 12:45:00', 'PIX', 'FECHADA'),
(21, 1, '2025-09-02 13:30:00', 'CARTAO', 'FECHADA'),
(22, 2, '2025-09-02 14:15:00', 'DINHEIRO', 'FECHADA'),
(23, 3, '2025-09-02 15:00:00', 'PIX', 'FECHADA'),
(24, 4, '2025-09-03 08:00:00', 'CARTAO', 'FECHADA'),
(25, 5, '2025-09-03 09:00:00', 'DINHEIRO', 'FECHADA'),
(26, 6, '2025-09-03 10:00:00', 'PIX', 'FECHADA'),
(27, 1, '2025-09-03 11:00:00', 'CARTAO', 'FECHADA'),
(28, 2, '2025-09-03 12:00:00', 'DINHEIRO', 'FECHADA'),
(29, 3, '2025-09-03 13:00:00', 'PIX', 'FECHADA'),
(30, 4, '2025-09-03 14:00:00', 'CARTAO', 'FECHADA'),
(31, 5, '2025-09-03 15:00:00', 'DINHEIRO', 'FECHADA'),
(32, 6, '2025-09-04 08:00:00', 'PIX', 'FECHADA'),
(33, 1, '2025-09-04 09:00:00', 'CARTAO', 'FECHADA'),
(34, 2, '2025-09-04 10:00:00', 'DINHEIRO', 'FECHADA'),
(35, 3, '2025-09-04 11:00:00', 'PIX', 'FECHADA'),
(36, 4, '2025-09-04 12:00:00', 'CARTAO', 'FECHADA'),
(37, 5, '2025-09-04 13:00:00', 'DINHEIRO', 'FECHADA'),
(38, 6, '2025-09-04 14:00:00', 'PIX', 'FECHADA'),
(39, 1, '2025-09-04 15:00:00', 'CARTAO', 'FECHADA'),
(40, 2, '2025-09-04 16:00:00', 'DINHEIRO', 'FECHADA');

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
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`ID_pagamento`),
  ADD KEY `idx_pagamentos_venda` (`ID_vendas`),
  ADD KEY `idx_pagamentos_func` (`ID_func_registro`);

--
-- Índices de tabela `pagamentos_itens`
--
ALTER TABLE `pagamentos_itens`
  ADD PRIMARY KEY (`ID_pagamento_item`),
  ADD KEY `idx_pg_itens_venda` (`ID_vendas`),
  ADD KEY `idx_pg_itens_prod` (`ID_produto`);

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
  MODIFY `ID_forn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `ID_func` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `itens_vendas`
--
ALTER TABLE `itens_vendas`
  MODIFY `ID_itensvendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de tabela `nivel`
--
ALTER TABLE `nivel`
  MODIFY `nivel_de_acesso` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `ID_pagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `pagamentos_itens`
--
ALTER TABLE `pagamentos_itens`
  MODIFY `ID_pagamento_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `ID_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `ID_vendas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `fk_pagamentos_func` FOREIGN KEY (`ID_func_registro`) REFERENCES `funcionario` (`ID_func`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pagamentos_vendas` FOREIGN KEY (`ID_vendas`) REFERENCES `vendas` (`ID_vendas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pagamentos_itens`
--
ALTER TABLE `pagamentos_itens`
  ADD CONSTRAINT `fk_pg_itens_prod` FOREIGN KEY (`ID_produto`) REFERENCES `produtos` (`ID_produto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pg_itens_vendas` FOREIGN KEY (`ID_vendas`) REFERENCES `vendas` (`ID_vendas`) ON DELETE CASCADE ON UPDATE CASCADE;

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
