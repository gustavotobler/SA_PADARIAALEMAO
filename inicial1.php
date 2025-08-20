<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['funcionario'])) {
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
$nome_func = $nivel['nome_acesso'] ?? '';

// DEFINIÇÃO DAS PERMISSÕES POR PERFIL
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define a codificação para UTF-8 -->
    <title>Página Inicial</title> <!-- Título da página que aparece na aba do navegador -->
    <link rel="stylesheet" href="css/site2.css"> <!-- Link para o arquivo CSS que estiliza a página -->
</head>
<body>

<!-- Menu lateral de navegação -->
<nav class="sidebar" aria-label="Menu de navegação">
    <!-- Botão com logo que serve para voltar ao menu principal, acessível via aria-label -->
    <a href="#" class="back-button" aria-label="Voltar ao Menu Principal">
        <img src="img/Logopadaria.png"><!-- Imagem do logo da padaria -->
    </a>
    <!-- Links para as diferentes seções do sistema -->
    <a href="produtos.php" aria-controls="Produtos">🥐 Produtos</a> <!-- Link para página de produtos -->
    <a href="funcionarios.php" aria-controls="Funcionarios">🙎‍♂️ Funcionários</a> <!-- Link para página de funcionários -->
    <a href="fornecedores.php" aria-controls="Fornecedores">💼 Fornecedores</a> <!-- Link para página de fornecedores -->
    <a href="relatorio_vendas_padaria_alemao1.php" aria-controls="Vendas">📈 Vendas</a> <!-- Link para página de vendas -->
    <a href="selecionar_itens.php" aria-controls="Vendas">🛒 Pagamento</a> <!-- Link para página de pagamento -->
</nav>

<!-- Conteúdo principal da página -->
<main class="main-content">
    <section>
        <div class="welcome-card">
            <h2>Bem-vindo ao nosso sistema!</h2> <!-- Mensagem de boas-vindas -->
        </div>
    </section>
</main>

</body>
</html>
