<?php
session_start(); // Inicia a sessão para poder usar as variáveis de sessão
require_once 'conexao.php'; // Conecta ao banco de dados

// Aqui verificamos se o usuário logado tem permissão.
// Só entra se for "adm" (nível 1) ou "secretária" (nível 2).
if($_SESSION['nivel'] != 1 && $_SESSION['nivel'] != 2){
    // Se não tiver permissão, mostra alerta e volta pra página inicial
    echo "<script>alert('Acesso negado!');window.location.href='inicial1.php';</script>";
    exit(); // Para a execução do código aqui mesmo
}

// Criamos a variável que vai guardar os fornecedores.
// Isso evita erro caso não ache nenhum.
$funcionarios = []; 

// Se o formulário foi enviado e o campo de busca não está vazio
if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST['busca'])){
    $busca = trim($_POST['busca']); // Remove espaços extras do que foi digitado

    // Se o que o usuário digitou for número, vamos buscar pelo ID do fornecedor
    if(is_numeric($busca)){
        $sql = "SELECT * FROM fornecedores WHERE ID_forn = :busca ORDER BY Nome_forn ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca',$busca, PDO::PARAM_INT);
    }else{
        // Caso contrário, buscamos pelo nome (iniciando com o que foi digitado)
        $sql = "SELECT * FROM fornecedores WHERE Nome_forn LIKE :busca_nome ORDER BY Nome_forn ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
    }
}else{
    // Se não houve busca, mostra todos os fornecedores ordenados pelo nome
    $sql = "SELECT * FROM fornecedores ORDER BY Nome_forn ASC";
    $stmt = $pdo->prepare($sql);
}

// Executa a consulta (seja busca ou lista geral)
$stmt->execute();

// Pega todos os resultados encontrados no banco
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
