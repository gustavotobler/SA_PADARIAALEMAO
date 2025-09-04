<?php 
session_start();
require_once '../conexao.php';

//VERIFICA SE O USUARIO TEM PERMISSAO DE ADM
if($_SESSION['nivel']!=1){
    echo "<script>alert('Acesso Negado!');window.location.href='../fornecedores.php'</script>";
    exit();
}

//INICIALIZA A VARIAVEL PARA ARMAZENAR USUARIOS
$fornecedores = [];

//BUSCA TODOS OS USUARIOS CADASTRADOS EM ORDEM ALFABETICA
$sql = "SELECT * FROM fornecedores ORDER BY  Nome_forn ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SE UM ID FOR PASSADO VIA GET EXCLUIR O FORNECEDOR
if(isset($_POST['id'])&& is_numeric($_POST['id'])){
    $id_fornecedor = $_POST['id'];

    //EXCLUI O USUARIO DO BANCO DE DADOS
    $sql = "DELETE FROM fornecedores WHERE ID_forn=:id";//Variável $sql que guarda um DELETE. Este comando serve para deletar informações do banco de dados, aqui no caso, será o fornecedor desejado.
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id',$id_fornecedor,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Fornecedor deletado com sucesso!');window.location.href='../fornecedores.php'</script>";
    }else{
        echo "<script>alert('Erro ao excluir fornecedor!');</script>";
    }
}
?>