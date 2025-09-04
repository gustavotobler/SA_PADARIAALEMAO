<?php
session_start(); // Inicia a sessão (para acessar variáveis de sessão do usuário)
require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

// Aqui verificamos se o usuário tem permissão.
// Só pode acessar quem for "adm" (nível 1) ou "secretária" (nível 2).
if($_SESSION['nivel'] != 1 && $_SESSION['nivel'] != 2){
    // Caso contrário, mostra um alerta e redireciona para a página inicial
    echo "<script>alert('Acesso negado!');window.location.href='inicial1.php';</script>";
    exit(); // Encerra o script aqui
}

// Criamos uma variável vazia para armazenar os funcionários.
// Isso evita erros caso a busca não retorne nada.
$funcionarios = []; 

// Se o formulário foi enviado e o campo de busca não está vazio:
if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])){
    $busca = trim($_POST['busca']); // Remove espaços extras do que foi digitado

    // Se o que o usuário digitou for número, busca pelo ID do funcionário
    if(is_numeric($busca)){
        $sql = "SELECT * FROM funcionario WHERE ID_func = :busca ORDER BY Nome_func ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    }else{
        // Caso contrário, busca pelo nome do funcionário (que começa com o texto digitado)
        $sql = "SELECT * FROM funcionario WHERE Nome_func LIKE :busca_nome ORDER BY Nome_func ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
    }
}else{
    // Se não houve busca, lista todos os funcionários em ordem alfabética
    $sql = "SELECT * FROM funcionario ORDER BY Nome_func ASC";
    $stmt = $pdo->prepare($sql);
}

// Executa a consulta (seja busca ou lista geral)
$stmt->execute();

// Armazena todos os resultados da consulta em forma de array associativo
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
