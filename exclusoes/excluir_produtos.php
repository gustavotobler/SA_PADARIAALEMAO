<?php
session_start(); 
require_once '../conexao.php';

// Verifica login
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='../inicial1.php';</script>";
    exit;
}

// Verifica nível de acesso
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../produtos.php';</script>";
    exit;
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE ID_produto = ?");
            $stmt->execute([$id]);

            echo "<script>alert('Produto excluído com sucesso!');window.location.href='../produtos.php'</script>";
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == "23000") {
                // Erro de chave estrangeira
                echo "<script>alert('Este produto está vinculado a pagamentos e não pode ser excluído.');window.location.href='../produtos.php'</script>";
            } else {
                echo "<script>alert('Erro ao excluir produto: ".addslashes($e->getMessage())."');window.location.href='../produtos.php'</script>";
            }
        }
    } else {
        echo "<script>alert('ID do produto não informado.');window.location.href='../produtos.php'</script>";
    }
} else {
    echo "<script>alert('Método inválido.');window.location.href='../produtos.php'</script>";
}
?>
