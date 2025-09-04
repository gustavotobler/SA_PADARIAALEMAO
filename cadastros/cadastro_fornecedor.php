<?php
session_start();
require_once("../conexao.php");

// Primeiro, verifica se o usuário está logado
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit;
}

// Verifica se o usuário é administrador
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../fornecedores.php';</script>";
    exit;
}

// Inicializa a variável de mensagem, caso queira usar depois
$msg = '';

// Só processa se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pega e limpa os dados enviados pelo formulário
    $nome_forn     = trim($_POST["Nome_forn"] ?? '');
    $telefone      = trim($_POST["Telefone"] ?? ''); 
    $cnpj          = trim($_POST["CNPJ"] ?? '');     
    $uf            = strtoupper(trim($_POST["UF"] ?? ''));
    $cidade        = trim($_POST["Cidade"] ?? '');
    $bairro        = trim($_POST["Bairro"] ?? '');
    $cep           = trim($_POST["CEP"] ?? '');
    $num_empresa   = isset($_POST["Num_empresa"]) ? (int)$_POST["Num_empresa"] : null;
    $logradouro    = trim($_POST["Logradouro"] ?? '');
    $email         = trim($_POST["Email"] ?? '');
    $data_fundacao = !empty($_POST["Data_fundacao"]) ? $_POST["Data_fundacao"] : null;

    // Verifica se os campos obrigatórios foram preenchidos
    if (!empty($nome_forn) && !empty($telefone) && !empty($cnpj) && !empty($cep)) {
        try {
            // Prepara a query para inserir o fornecedor no banco
            $sql = "INSERT INTO fornecedores 
                    (Nome_forn, Telefone, CNPJ, UF, Cidade, Bairro, CEP, Num_empresa, Logradouro, Email, Data_fundacao) 
                    VALUES 
                    (:Nome_forn, :Telefone, :CNPJ, :UF, :Cidade, :Bairro, :CEP, :Num_empresa, :Logradouro, :Email, :Data_fundacao)";
            
            $stmt = $pdo->prepare($sql);

            // Associa cada variável ao placeholder correspondente
            $stmt->bindParam(':Nome_forn', $nome_forn);
            $stmt->bindParam(':Telefone', $telefone);
            $stmt->bindParam(':CNPJ', $cnpj);
            $stmt->bindParam(':UF', $uf);
            $stmt->bindParam(':Cidade', $cidade);
            $stmt->bindParam(':Bairro', $bairro);
            $stmt->bindParam(':CEP', $cep);
            $stmt->bindParam(':Num_empresa', $num_empresa);
            $stmt->bindParam(':Logradouro', $logradouro);
            $stmt->bindParam(':Email', $email);
            $stmt->bindParam(':Data_fundacao', $data_fundacao);

            // Executa a query e verifica se deu certo
            if ($stmt->execute()) {
                echo "<script>alert('Fornecedor cadastrado com sucesso!');window.location.href='../fornecedores.php'</script>";
                exit;
            } else {
                echo "<script>alert('Erro ao cadastrar fornecedor!');</script>";
            }
        } catch (PDOException $e) {
            // Caso dê erro, exibe a mensagem
            echo "<script>alert('Erro: " . $e->getMessage() . "');</script>";
        }
    } else {
        // Se algum campo obrigatório estiver vazio, alerta o usuário
        echo "<script>alert('Preencha todos os campos obrigatórios!');</script>";
    }
}
?>
