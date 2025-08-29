<?php
session_start();
require_once 'conexao.php';

// Se não estiver logado, redireciona
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    header('Location: index.php');
    exit();
}

// OBTENDO O NOME DO PERFIL DO USUÁRIO LOGADO
$id_Nivel = $_SESSION['nivel'];
$sqlNivel = 'SELECT nome_acesso FROM nivel WHERE nivel_de_acesso = :nivel_de_acesso';
$stmtNivel = $pdo->prepare($sqlNivel);
$stmtNivel->bindParam(':nivel_de_acesso', $id_Nivel, PDO::PARAM_INT);
$stmtNivel->execute();
$nivel = $stmtNivel->fetch(PDO::FETCH_ASSOC);
$nomeNivel = $nivel['nome_acesso'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Página Inicial</title>
    <link rel="stylesheet" href="css/site2.css">
</head>
<body>

<!-- Mensagem personalizada -->

<!-- Menu lateral de navegação -->
<nav class="sidebar" aria-label="Menu de navegação">
    <a href="#" class="back-button" aria-label="Voltar ao Menu Principal">
        <img src="img/Logopadaria.png" alt="Logo da Padaria Alemão">
    </a>
    <a href="produtos.php" aria-label="Ir para Produtos">🥐 Produtos</a>
    <a href="funcionarios.php" aria-label="Ir para Funcionários">🙎‍♂️ Funcionários</a>
    <a href="fornecedores.php" aria-label="Ir para Fornecedores">💼 Fornecedores</a>
    <a href="estoque.php" arial-label="Ir para Estoque">📦 Estoque</a>
    <a href="relatorio_vendas_padaria_alemao1.php" aria-label="Ir para Relatório de Vendas">📈 Vendas</a>
    <a href="selecionar_itens.php" aria-label="Ir para Pagamento">🛒 Pagamento</a>

    <a href="index.php">Logout</a>
</nav>

<!-- Conteúdo principal -->
<main class="main-content">
    <section>
        <div class="welcome-card">
            <h2>Seja bem-vindo ao nosso sistema de gestão! <br>Você está utilizando um perfil de <?=$nomeNivel?>.</h2>
        </div>
    </section>
</main>

</body>
</html>
