<?php 
session_start(); // Inicia a sessão para verificar permissões
require_once '../conexao.php'; // Faz a conexão com o banco de dados

// Verifica se o usuário logado é administrador (nível 1).
// Se não for, bloqueia o acesso e redireciona para a página de funcionários.
if($_SESSION['nivel'] != 1){
    echo "<script>alert('Acesso Negado!');window.location.href='../funcionarios.php'</script>";
    exit(); // Encerra o código aqui mesmo
}

// Cria uma variável para armazenar os funcionários cadastrados
$funcionarios = [];

// Busca todos os funcionários cadastrados no banco, em ordem alfabética pelo nome
$sql = "SELECT * FROM funcionario ORDER BY Nome_func ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC); // Transforma o resultado em array associativo

// Se um ID for enviado via POST, significa que o usuário pediu para excluir um funcionário
if(isset($_POST['id']) && is_numeric($_POST['id'])){
    $id_funcionario = $_POST['id']; // Armazena o ID recebido

    // Cria o comando SQL para excluir o funcionário com esse ID
    $sql = "DELETE FROM funcionario WHERE ID_func = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_funcionario, PDO::PARAM_INT);

    // Executa o comando de exclusão e mostra uma mensagem de acordo com o resultado
    if($stmt->execute()){
        echo "<script>alert('Funcionário deletado com sucesso!');window.location.href='../funcionarios.php'</script>";
    }else{
        echo "<script>alert('Erro ao excluir funcionário!');</script>";
    }
}
?>
