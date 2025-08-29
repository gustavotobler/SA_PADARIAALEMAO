<?php 
session_start();
require_once '../conexao.php';

//VERIFICA SE O USUARIO TEM PERMISSAO DE ADM
If($_SESSION['nivel']!=1){
    echo "<script>alert('Acesso Negado!');window.location.href='../inicial1.php'</script>";
    exit();
}

//INICIALIZA A VARIAVEL PARA ARMAZENAR USUARIOS
$funcionarios = [];

//BUSCA TODOS OS USUARIOS CADASTRADOS EM ORDEM ALFABETICA
$sql = "SELECT * FROM fornecedores ORDER BY  Nome_forn ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SE UM ID FOR PASSADO VIA GET EXCLUIR O USUARIO
if(isset($_POST['id'])&& is_numeric($_POST['id'])){
    $id_funcionario = $_POST['id'];

    //EXCLUI O USUARIO DO BANCO DE DADOS
    $sql = "DELETE FROM fornecedores WHERE ID_forn=:id";//Variável $sql que guarda um DELETE. Este comando serve para deletar informações do banco de dados, aqui no caso, será o funcionário desejado.
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id',$id_funcionario,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Fornecedor deletado com sucesso!');window.location.href='../fornecedores.php'</script>";
    }else{
        echo "<script>alert('Erro ao excluir fornecedpr!');</script>";
    }
}
?>