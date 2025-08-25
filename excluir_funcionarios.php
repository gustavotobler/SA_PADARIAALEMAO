<?php 
session_start();
require_once 'conexao.php';
require 'menu_nav.php';

//VERIFICA SE O USUARIO TEM PERMISSAO DE ADM
If($_SESSION['nivel']!=1){
    echo "<script>alert('Acesso Negado!');window.location.href='principal.php'</script>";
    exit();
}

//INICIALIZA A VARIAVEL PARA ARMAZENAR USUARIOS
$funcionarios = [];

//BUSCA TODOS OS USUARIOS CADASTRADOS EM ORDEM ALFABETICA
$sql = "SELECT * FROM funcionario ORDER BY nome_funcionario ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SE UM ID FOR PASSADO VIA GET EXCLUIR O USUARIO
if(isset($_GET['id'])&& is_numeric($_GET['id'])){
    $id_funcionario = $_GET['id'];

    //EXCLUI O USUARIO DO BANCO DE DADOS
    $sql = "DELETE FROM funcionario WHERE id_funcionario=:id";//Variável $sql que guarda um DELETE. Este comando serve para deletar informações do banco de dados, aqui no caso, será o funcionário desejado.
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id',$id_funcionario,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Funcionário deletado com sucesso!');window.location.href='excluir_funcionario.php'</script>";
    }else{
        echo "<script>alert('Erro ao excluir funcionário!');</script>";
    }
}
?>