<?php
session_start();
require_once("../conexao.php");

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mantém as máscaras
    $nome_forn     = trim($_POST["Nome_forn"]);
    $telefone      = trim($_POST["Telefone"]); 
    $cnpj          = trim($_POST["CNPJ"]);     
    $uf            = strtoupper(trim($_POST["UF"]));
    $cidade        = trim($_POST["Cidade"]);
    $bairro        = trim($_POST["Bairro"]);
    $cep           = trim($_POST["CEP"]);
    $num_empresa   = (int)$_POST["Num_empresa"];
    $logradouro    = trim($_POST["Logradouro"]);
    $email         = trim($_POST["Email"]);
    $data_fundacao = !empty($_POST["Data_fundacao"]) ? $_POST["Data_fundacao"] : null;

    // Verifica se os obrigatórios foram preenchidos
    if ($nome_forn && $telefone && $cnpj && $cep) {
        $sql = "INSERT INTO fornecedores 
                (Nome_forn, Telefone, CNPJ, UF, Cidade, Bairro, CEP, Num_empresa, Logradouro, Email, Data_fundacao) 
                VALUES 
                (:Nome_forn, :Telefone, :CNPJ, :UF, :Cidade, :Bairro, :CEP, :Num_empresa, :Logradouro, :Email, :Data_fundacao)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':Nome_forn', $nome_forn, PDO::PARAM_STR);
        $stmt->bindParam(':Telefone', $telefone, PDO::PARAM_STR);
        $stmt->bindParam(':CNPJ', $cnpj, PDO::PARAM_STR);
        $stmt->bindParam(':UF', $uf, PDO::PARAM_STR);
        $stmt->bindParam(':Cidade', $cidade, PDO::PARAM_STR);
        $stmt->bindParam(':Bairro', $bairro, PDO::PARAM_STR);
        $stmt->bindParam(':CEP', $cep, PDO::PARAM_STR);
        $stmt->bindParam(':Num_empresa', $num_empresa, PDO::PARAM_INT);
        $stmt->bindParam(':Logradouro', $logradouro, PDO::PARAM_STR);
        $stmt->bindParam(':Email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':Data_fundacao', $data_fundacao, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $msg = "<script>alert('Fornecedor cadastrado com sucesso!');window.location.href='../fornecedores.php'</script>";
        } else {
            $msg = "<script>alert('Erro ao cadastrar fornecedor!');</script>";
        }
    } else {
        $msg = "<script>alert('Preencha todos os campos obrigatórios!');</script>";
    }
}
?>
