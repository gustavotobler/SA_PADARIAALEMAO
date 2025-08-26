<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome       = $_POST['Nome_func'] ?? null;
    $telefone   = $_POST['Telefone'] ?? null;
    $sexo       = $_POST['Sexo'] ?? null;
    $rg         = $_POST['RG'] ?? null;
    $cpf        = $_POST['CPF'] ?? null;
    $esta_civil = $_POST['Esta_civil'] ?? null;
    $uf         = $_POST['UF'] ?? null;
    $cidade     = $_POST['Cidade'] ?? null;
    $bairro     = $_POST['Bairro'] ?? null;
    $tipo       = $_POST['Tipo'] ?? null;
    $cep        = $_POST['CEP'] ?? null;
    $num_casa   = $_POST['Num_casa'] ?? null;
    $logradouro = $_POST['Logradouro'] ?? null;
    $senha      = password_hash($_POST['Senha'] ?? '', PASSWORD_DEFAULT);
    $email      = $_POST['Email'] ?? null;
    $nivel      = $_POST['nivel_de_acesso'] ?? null;
    $cargo      = $_POST['Cargo'] ?? null;

    if ($id) {
        $sql = "UPDATE funcionario 
                SET :Nome_func,:Telefone,:Sexo,:RG,:CPF,:Esta_civil,:UF,:Cidade,:Bairro,:Tipo,:CEP,:Num_casa,:Logradouro,:Senha,:Email,:nivel_de_acesso,:Data_nascimento,:Data_admissao,:Cargo
                WHERE ID_func=:id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(":Nome_func",$nome);
      $stmt->bindParam(":Telefone",$telefone);
      $stmt->bindParam(":Sexo",$sexo);
      $stmt->bindParam(":RG",$rg);
      $stmt->bindParam(":CPF",$cpf);
      $stmt->bindParam(":Esta_civil",$esta_civil);
      $stmt->bindParam(":UF",$uf);
      $stmt->bindParam(":Cidade",$cidade);
      $stmt->bindParam(":Bairro",$bairro);
      $stmt->bindParam(":Tipo",$tipo);
      $stmt->bindParam(":CEP",$cep);
      $stmt->bindParam(":Num_casa",$num_casa);
      $stmt->bindParam(":Logradouro",$logradouro);
      $stmt->bindParam(":Senha",$senha);
      $stmt->bindParam(":Email",$email);
      $stmt->bindParam(":nivel_de_acesso",$nivel);
      $stmt->bindParam(":Data_nascimento",$data_nasc);
      $stmt->bindParam(":Data_admissao",$data_adm);
      $stmt->bindParam(":Cargo",$cargo);
        if($stmt->execute()){
            header('Location: funcionarios.php'); // redireciona de volta
            exit;
        } else {
            echo "Erro ao atualizar";
        }
    }
}
?>
