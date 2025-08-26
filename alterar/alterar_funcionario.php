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
  $tipo       = $_POST['Tipo'] ?? null;
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
  (Nome_func,Telefone,Sexo,RG,CPF,Esta_civil,UF,Cidade,Bairro,Tipo,CEP,Num_casa,Logradouro,Senha,Email,nivel_de_acesso,Data_nascimento,Data_admissao,Cargo)
  VALUES 
  (:Nome_func,:Telefone,:Sexo,:RG,:CPF,:Esta_civil,:UF,:Cidade,:Bairro,:Tipo,:CEP,:Num_casa,:Logradouro,:Senha,:Email,:nivel_de_acesso,:Data_nascimento,:Data_admissao,:Cargo)";

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
      echo "<script>alert('Funcionário cadastrado com sucesso!');</script>";
  } else{
      echo "<script>alert('Erro ao cadastrar funcionário');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Alterar Funcionário</title>
<style>
/* Reset e corpo */
* {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body {background:#eef2f7;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px;}

/* Card do formulário */
form {background:#fff;padding:35px 40px;border-radius:15px;box-shadow:0 12px 25px rgba(0,0,0,0.12);max-width:550px;width:100%;}
h2 {text-align:center;margin-bottom:30px;color:#2c3e50;font-size:1.8rem;}
label {display:block;margin-bottom:6px;font-weight:600;color:#34495e;}
input, select {width:100%;padding:12px 15px;margin-bottom:15px;border:1px solid #ccc;border-radius:10px;font-size:0.95rem;transition:all 0.3s ease;}
input:focus, select:focus {border-color:#3498db;box-shadow:0 0 8px rgba(52,152,219,0.3);outline:none;}
button {width:100%;padding:14px;background:#3498db;border:none;color:white;font-size:1rem;font-weight:600;border-radius:10px;cursor:pointer;transition:0.3s;}
button:hover {background:#2980b9;}
.erro {color:#e74c3c;font-size:0.85rem;margin-top:-10px;margin-bottom:10px;display:block;}
.flex-group {display:flex;gap:10px;margin-bottom:15px;}
.flex-group input,.flex-group select {flex:1;}
@media(max-width:600px){form{padding:25px 20px;}.flex-group{flex-direction:column;}}
</style>
</head>
<body>
<div class="page">
    <div class="form-box">
      <!-- Botão de voltar -->
      <a href="funcionarios.php"><button class="back-button"><span class="material-icons">arrow_back</span></button></a>

<form method="POST">
<h2>Alterar Funcionário</h2>

<label>Nome:</label>
<input type="text" name="Nome_func" required>

<label>Telefone:</label>
<input type="text" name="Telefone" id="telefone" required>
<span id="erro-telefone" class="erro"></span>

<label>Sexo:</label>
<select name="Sexo" required>
  <option value="">Selecione</option>
  <option value="M">Masculino</option>
  <option value="F">Feminino</option>
</select>

<label>RG:</label>
<input type="text" name="RG" id="rg" required>
<span id="erro-rg" class="erro"></span>

<label>CPF:</label>
<input type="text" name="CPF" id="cpf" required>
<span id="erro-cpf" class="erro"></span>

<label>Estado Civil:</label>
<input type="text" name="Esta_civil">

<div class="flex-group">
  <div>
    <label>UF:</label>
    <input type="text" name="UF" maxlength="2">
  </div>
  <div>
    <label>Número da Casa:</label>
    <input type="text" name="Num_casa">
  </div>
</div>

<label>Cidade:</label>
<input type="text" name="Cidade">

<label>Bairro:</label>
<input type="text" name="Bairro">

<label>Tipo:</label>
<input type="text" name="Tipo">

<label>CEP:</label>
<input type="text" name="CEP" id="cep">
<span id="erro-cep" class="erro"></span>

<label>Logradouro:</label>
<input type="text" name="Logradouro">

<label>Email:</label>
<input type="email" name="Email" id="email" required>
<span id="erro-email" class="erro"></span>

<label>Senha:</label>
<input type="password" name="Senha" id="senha" required>
<span id="erro-senha" class="erro"></span>

<label>Nível de Acesso:</label>
<select name="nivel_de_acesso" required>
  <option value="1">Administrador</option>
  <option value="2">Funcionário</option>
</select>

<label>Cargo:</label>
<input type="text" name="Cargo">

<div class="flex-group">
  <div>
    <label>Data de Nascimento:</label>
    <input type="text" name="Data_nascimento" id="nascimento" placeholder="dd/mm/aaaa">
    <span id="erro-nascimento" class="erro"></span>
  </div>
  <div>
    <label>Data de Admissão:</label>
    <input type="text" name="Data_admissao" id="admissao" placeholder="dd/mm/aaaa">
  </div>
</div>

<button type="submit">Cadastrar</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const telefone=document.getElementById("telefone");
  const rg=document.getElementById("rg");
  const cpf=document.getElementById("cpf");
  const cep=document.getElementById("cep");
  const nascimento=document.getElementById("nascimento");
  const senha=document.getElementById("senha");

  telefone.addEventListener("input",()=>{telefone.value=telefone.value.replace(/\D/g,"").replace(/^(\d{2})(\d)/,"($1) $2").replace(/(\d{5})(\d{4}).*/,"$1-$2");});
  rg.addEventListener("input",()=>{rg.value=rg.value.replace(/\D/g,"").slice(0,9);});
  cpf.addEventListener("input",()=>{cpf.value=cpf.value.replace(/\D/g,"").replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d{1,2})$/,"$1-$2");});
  cep.addEventListener("input",()=>{cep.value=cep.value.replace(/\D/g,"").replace(/(\d{5})(\d{3})$/,"$1-$2");});
  nascimento.addEventListener("input",()=>{nascimento.value=nascimento.value.replace(/\D/g,"").replace(/(\d{2})(\d)/,"$1/$2").replace(/(\d{2})(\d)/,"$1/$2").replace(/(\d{4})(\d)/,"$1");});

  document.querySelector("form").addEventListener("submit",(e)=>{
    let ok=true;
    if(cpf.value.replace(/\D/g,"").length!==11){document.getElementById("erro-cpf").innerText="CPF inválido."; ok=false;} else document.getElementById("erro-cpf").innerText="";
    if(senha.value.length<8){document.getElementById("erro-senha").innerText="Senha deve ter no mínimo 8 caracteres."; ok=false;} else document.getElementById("erro-senha").innerText="";
    if(nascimento.value){
      const p=nascimento.value.split("/");
      if(p.length===3){
        const nasc=new Date(p[2],p[1]-1,p[0]);
        const hoje=new Date();
        let idade=hoje.getFullYear()-nasc.getFullYear();
        if(hoje<new Date(nasc.setFullYear(hoje.getFullYear()))) idade--;
        if(idade<18){document.getElementById("erro-nascimento").innerText="Funcionário deve ter pelo menos 18 anos."; ok=false;}
        else document.getElementById("erro-nascimento").innerText="";
      }
    }
    if(!ok) e.preventDefault();
  });
});
</script>

</body>
</html>
