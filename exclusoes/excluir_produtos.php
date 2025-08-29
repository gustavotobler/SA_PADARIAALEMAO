<?php
session_start();
require_once '../conexao.php'; // Ajuste o caminho se necessário

if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='../inicial1.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        // Prepare e execute o DELETE
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE ID_produto = ?");
        if ($stmt->execute([$id])) {
            header('Location: ../produtos.php?msg=Produto excluído com sucesso');
            exit;
        } else {
            echo "Erro ao excluir produto.";
        }
    } else {
        echo "ID do produto não informado.";
    }
} else {
    echo "Método inválido.";
}
?>
