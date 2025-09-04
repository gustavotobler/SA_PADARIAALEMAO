<?php
session_start(); // Inicia a sessão
require_once '../conexao.php'; // Arquivo de conexão com o banco

// Primeiro: verifica se o usuário está logado
// Ele precisa ter as variáveis de sessão "funcionario" e "nivel"
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='../inicial1.php';</script>";
    exit;
}

// Segundo: verifica se o usuário é administrador
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../produtos.php';</script>";
    exit;
}

// Terceiro: só aceita requisições feitas via POST (mais seguro para exclusões)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verifica se o ID do produto foi enviado
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        // Prepara a query de exclusão (DELETE) com parâmetro
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE ID_produto = ?");
        
        // Executa passando o ID
        if ($stmt->execute([$id])) {
            // Se der certo, mostra alerta e volta para a página de produtos
            echo "<script>alert('Produto excluído com sucesso!');window.location.href='../produtos.php'</script>";
            exit;
        } else {
            // Se der erro na exclusão
            echo "Erro ao excluir produto.";
        }
    } else {
        // Caso não tenha sido enviado o ID
        echo "ID do produto não informado.";
    }
} else {
    // Se tentarem acessar via GET, bloqueia
    echo "Método inválido.";
}
?>
