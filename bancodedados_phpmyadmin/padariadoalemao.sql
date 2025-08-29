-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/08/2025 às 19:56
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
-- Erro ao ler a estrutura para a tabela padariadoalemao.categorias: #1932 - Table &#039;padariadoalemao.categorias&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.categorias: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`categorias`&#039; na linha 1

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
-- Erro ao ler a estrutura para a tabela padariadoalemao.fornecedores: #1932 - Table &#039;padariadoalemao.fornecedores&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.fornecedores: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`fornecedores`&#039; na linha 1

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_vendas`
--
-- Erro ao ler a estrutura para a tabela padariadoalemao.itens_vendas: #1932 - Table &#039;padariadoalemao.itens_vendas&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.itens_vendas: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`itens_vendas`&#039; na linha 1

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
-- Erro ao ler a estrutura para a tabela padariadoalemao.produtos: #1932 - Table &#039;padariadoalemao.produtos&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.produtos: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`produtos`&#039; na linha 1

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`ID_produto`, `ID_forn`, `id_categorias`, `Nome_prod`, `Preco_unitario`, `Unid_medida`, `Validade`, `Qntd_produto`) VALUES
(1, 1, 0, 'bolacha', 2.00, 'kg', '10/03/2026', 80);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--
-- Erro ao ler a estrutura para a tabela padariadoalemao.vendas: #1932 - Table &#039;padariadoalemao.vendas&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela padariadoalemao.vendas: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `padariadoalemao`.`vendas`&#039; na linha 1
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
