<?php
session_start(); // Inicia a sessão para poder verificar permissões do usuário
require_once '../conexao.php'; // Conecta ao banco (subindo um nível na pasta)

// Aqui é feito o controle de acesso.
// Só quem tem nível 1 (adm) ou 2 (secretária) pode entrar nessa página.
if($_SESSION['nivel'] != 1 && $_SESSION['nivel'] != 2){
    echo "<script>alert('Acesso negado!');window.location.href='inicial1.php';</script>";
    exit(); // Interrompe o código se não tiver permissão
}

// Variável criada só para garantir que não dê erro se não houver produtos ainda
$produtos = []; 

// Se o formulário foi enviado e o campo de busca não está vazio:
if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])){
    $busca = trim($_POST['busca']); // Limpa espaços extras da busca

    // Caso o usuário tenha digitado um número → busca pelo ID do produto
    if(is_numeric($busca)){
        $sql = "SELECT * FROM produtos WHERE ID_produto = :busca ORDER BY Nome_prod ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    }else{
        // Caso contrário → busca pelo nome do produto (começando com o texto digitado)
        $sql = "SELECT * FROM produtos WHERE Nome_prod LIKE :busca_nome ORDER BY Nome_prod ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
    }
}else{
    // Se não houve busca → traz todos os produtos ordenados por nome
    $sql = "SELECT * FROM produtos ORDER BY Nome_prod ASC";
    $stmt = $pdo->prepare($sql);
}

// Executa a consulta (independente do tipo de busca)
$stmt->execute();

// Guarda todos os resultados em um array associativo
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
