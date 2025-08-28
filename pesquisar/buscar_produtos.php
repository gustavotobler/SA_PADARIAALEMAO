<?php
session_start();
require_once '../conexao.php';

//VERIFICA SE O USUÁRIO TEM PERMISSÃO DE adm OU secretária
if($_SESSION['nivel'] !=1 && $_SESSION['nivel']!=2){
    echo "<script>alert('Acesso negado!');window.location.href='inicial1.php';</script>";
    exit();
}

$produtos = []; //INICIALIZA A VARIÁVEL PARA EVITAR ERROS

//SE O FORMULÁRIO FOR ENVIADO, BUSCA O USUÁRIO POR ID OU NOME
if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST['busca'])){
    $busca = trim($_POST['busca']);

    //VERIFICA SE A BUSCA É UM número OU nome
    if(is_numeric($busca)){
        $sql="SELECT * FROM produtos WHERE ID_produto = :busca ORDER BY Nome_prod ASC";
        $stmt=$pdo->prepare($sql);
        $stmt->bindParam(':busca',$busca, PDO::PARAM_INT);
    }else{
        $sql="SELECT * FROM produtos WHERE Nome_prod LIKE :busca_nome ORDER BY Nome_prod ASC";
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(':busca_nome',"$busca%",PDO::PARAM_STR);
    }
    }else{
        $sql = "SELECT * FROM produtos order by Nome_prod ASC";
       $stmt = $pdo->prepare($sql);

    }
    $stmt->execute();
    $usuarios = $stmt->fetchALL(PDO::FETCH_ASSOC);
?>