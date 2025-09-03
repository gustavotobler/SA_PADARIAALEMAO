<?php
session_start();
require_once '../conexao.php';
error_reporting(E_ALL);

// Verifica permissão
if ($_SESSION['nivel'] != 1) {
    echo "Acesso negado!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Pegando com segurança
  $nome       = $_POST['Nome_func'] ?? null;
  $telefone   = $_POST['Telefone'] ?? null;
  $sexo       = $_POST['Sexo'] ?? null;
  $rg         = $_POST['RG'] ?? null;
  $cpf        = $_POST['CPF'] ?? null;
  $esta_civil = $_POST['Esta_civil'] ?? null;
  $uf         = $_POST['UF'] ?? null;
  $cidade     = $_POST['Cidade'] ?? null;
  $bairro     = $_POST['Bairro'] ?? null;
  $cep        = $_POST['CEP'] ?? null;
  $num_casa   = $_POST['Num_casa'] ?? null;
  $logradouro = $_POST['Logradouro'] ?? null;
  $senha      = password_hash($_POST['Senha'] ?? '', PASSWORD_DEFAULT);
  $email      = $_POST['Email'] ?? null;
  $nivel      = $_POST['nivel_de_acesso'] ?? null;
  $cargo      = $_POST['Cargo'] ?? null;

  function formatarDataBanco($data){
      if(!$data) return null;
      $partes = explode("/", $data);
      if(count($partes) == 3){
          return $partes[2]."-".$partes[1]."-".$partes[0];
      }
      return null;
  }
  $data_nasc = formatarDataBanco($_POST['Data_nascimento'] ?? null);
  $data_adm  = formatarDataBanco($_POST['Data_admissao'] ?? null);

  $sql = "INSERT INTO funcionario 
  (Nome_func,Telefone,Sexo,RG,CPF,Esta_civil,UF,Cidade,Bairro,CEP,Num_casa,Logradouro,Senha,Email,nivel_de_acesso,Data_nascimento,Data_admissao,Cargo)
  VALUES 
  (:Nome_func,:Telefone,:Sexo,:RG,:CPF,:Esta_civil,:UF,:Cidade,:Bairro,:CEP,:Num_casa,:Logradouro,:Senha,:Email,:nivel_de_acesso,:Data_nascimento,:Data_admissao,:Cargo)";

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
      echo "<script>alert('Funcionário cadastrado com sucesso!');window.location.href='../funcionarios.php'</script>";
  } else{
      echo "<script>alert('Erro ao cadastrar funcionário');</script>";
  }
}
?>
