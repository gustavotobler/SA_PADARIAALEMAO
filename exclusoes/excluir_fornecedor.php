<?php 
session_start(); // Inicia a sessão para validar o usuário logado
require_once '../conexao.php'; // Conexão com o banco de dados

// Aqui só o administrador (nível 1) pode acessar.
if($_SESSION['nivel'] != 1){
    echo "<script>alert('Acesso Negado!');window.location.href='../fornecedores.php'</script>";
    exit();
}

// Cria a variável que vai armazenar os fornecedores cadastrados
$fornecedores = [];

// Consulta todos os fornecedores do banco, em ordem alfabética pelo nome
$sql = "SELECT * FROM fornecedores ORDER BY Nome_forn ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Caso um ID seja enviado via POST, significa que o usuário quer excluir um fornecedor
if(isset($_POST['id']) && is_numeric($_POST['id'])){
    $id_fornecedor = $_POST['id'];

    try {
        // Comando para excluir o fornecedor do banco de dados
        $sql = "DELETE FROM fornecedores WHERE ID_forn = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_fornecedor, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Fornecedor deletado com sucesso!');window.location.href='../fornecedores.php'</script>";
    } catch (PDOException $e) {
        if ($e->getCode() == "23000") {
            // Caso tenha vínculo (ex: produtos, notas, etc.)
            echo "<script>alert('Este fornecedor está vinculado a outros registros e não pode ser excluído.');window.location.href='../fornecedores.php'</script>";
        } else {
            echo "<script>alert('Erro ao excluir fornecedor: ".addslashes($e->getMessage())."');window.location.href='../fornecedores.php'</script>";
        }
    }
}
?>
