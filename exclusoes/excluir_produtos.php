<?php 
session_start();
require_once '../conexao.php';

//VERIFICA SE O USUARIO TEM PERMISSAO DE ADM
If($_SESSION['nivel']!=1){
    echo "<script>alert('Acesso Negado!');window.location.href='../inicial1.php'</script>";
    exit();
}

//INICIALIZA A VARIAVEL PARA ARMAZENAR USUARIOS
$produtos = [];

//BUSCA TODOS OS USUARIOS CADASTRADOS EM ORDEM ALFABETICA
$sql = "SELECT * FROM produtos ORDER BY  Nome_prod ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

//SE UM ID FOR PASSADO VIA GET EXCLUIR O USUARIO
if(isset($_POST['id'])&& is_numeric($_POST['id'])){
    $id_funcionario = $_POST['id'];

    //EXCLUI O USUARIO DO BANCO DE DADOS
    $sql = "DELETE FROM produtos WHERE ID_produto=:id";//Variável $sql que guarda um DELETE. Este comando serve para deletar informações do banco de dados, aqui no caso, será o funcionário desejado.
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id',$id_produto,PDO::PARAM_INT);

    if($stmt->execute()){
        echo "<script>alert('Produto deletado com sucesso!');window.location.href='../produtos.php'</script>";
    }else{
        echo "<script>alert('Erro ao excluir produto!');</script>";
    }
}
?>