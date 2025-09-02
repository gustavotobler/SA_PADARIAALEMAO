<?php
session_start();
require_once 'conexao.php';
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
      echo "<script>alert('Funcionário cadastrado com sucesso!');window.location.href='funcionarios.php'</script>";
  } else{
      echo "<script>alert('Erro ao cadastrar funcionário');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro de Funcionário</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
  /* Reset */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  body {
    background: #eef2f7;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* Header */
  header {
    background:rgb(27, 68, 95);
    padding: 15px 20px;
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
  }
  header .back-btn {
    background: transparent;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 24px;
  }
  header h1 {
    flex: 1;
    font-weight: 700;
    font-size: 1.5rem;
    user-select: none;
  }

  /* Container principal */
  main {
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 25px 15px;
  }

  /* Formulário estilo card */
  form {
    background: #fff;
    padding: 30px 35px;
    border-radius: 15px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.12);
    max-width: 600px;
    width: 100%;
  }
  form h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
    font-size: 1.8rem;
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #34495e;
  }
  input, select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
  }
  input:focus, select:focus {
    border-color:rgb(27, 68, 95);db;
    box-shadow: 0 0 8px rgba(52,152,219,0.3);
    outline: none;
  }
  button[type="submit"] {
    width: 100%;
    padding: 14px;
    background:rgb(27, 68, 95);
    border: none;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  button[type="submit"]:hover {
    background:rgb(0, 153, 255)68, 95);
  }

  /* Flex container para inputs lado a lado */
  .flex-group {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
  }
  .flex-group > div {
    flex: 1;
  }

  /* Mensagens de erro */
  .erro {
    color: #e74c3c;
    font-size: 0.85rem;
    margin-top: -10px;
    margin-bottom: 10px;
    display: block;
  }

  @media(max-width: 600px) {
    .flex-group {
      flex-direction: column;
    }
  }
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='funcionarios.php'" title="Voltar">
    <span class="material-icons">arrow_back</span>
  </button>
  <h1>Cadastro de Funcionário</h1>
</header>

<main>
  <form method="POST" novalidate>
    <h2>Cadastro de Funcionário</h2>

    <label for="Nome_func">Nome:</label>
    <input type="text" name="Nome_func" id="Nome_func" required />

    <label for="telefone">Telefone:</label>
    <input type="text" name="Telefone" id="telefone" required />
    <span id="erro-telefone" class="erro"></span>

    <label for="Sexo">Sexo:</label>
    <select name="Sexo" id="Sexo" required>
      <option value="">Selecione</option>
      <option value="Masculino">Masculino</option>
      <option value="Feminino">Feminino</option>
    </select>

    <label for="rg">RG:</label>
    <input type="text" name="RG" id="rg" required />
    <span id="erro-rg" class="erro"></span>

    <label for="cpf">CPF:</label>
    <input type="text" name="CPF" id="cpf" maxlength="15" required />
    <span id="erro-cpf" class="erro"></span>

    <label for="Esta_civil">Estado Civil:</label>
    <select name="Esta_civil" id="Esta_civil" required>
      <option value=""></option>
      <option>Solteiro</option>
      <option>Casado</option>
      <option>Viúvo</option>
    </select>

    <div class="flex-group">
      <div>
        <label for="UF">UF:</label>
        <input type="text" name="UF" id="UF" maxlength="2" />
      </div>
      <div>
        <label for="Num_casa">Número da Casa:</label>
        <input type="number" name="Num_casa" id="Num_casa" />
      </div>
    </div>

    <label for="Cidade">Cidade:</label>
    <input type="text" name="Cidade" id="Cidade" />

    <label for="Bairro">Bairro:</label>
    <input type="text" name="Bairro" id="Bairro" />

    <label for="cep">CEP:</label>
    <input type="text" name="CEP" id="cep" />
    <span id="erro-cep" class="erro"></span>

    <label for="Logradouro">Logradouro:</label>
    <input type="text" name="Logradouro" id="Logradouro" />

    <label for="Email">Email:</label>
    <input type="email" name="Email" id="email" required />
    <span id="erro-email" class="erro"></span>

    <label for="senha">Senha:</label>
    <input type="password" name="Senha" id="senha" required />
    <span id="erro-senha" class="erro"></span>

    <label for="nivel_de_acesso">Nível de Acesso:</label>
    <select name="nivel_de_acesso" id="nivel_de_acesso" required>
      <option value="1">Administrador</option>
      <option value="2">Funcionário</option>
    </select>

    <label for="cargo">Cargo:</label>
    <select name="Cargo" id="cargo" required>
      <option>Gerente</option>
      <option>Padeiro</option>
      <option>Caixa</option>
      <option>Confeiteiro</option>
    </select>

    <div class="flex-group">
      <div>
        <label for="nascimento">Data de Nascimento:</label>
        <input type="text" name="Data_nascimento" id="nascimento" placeholder="dd/mm/aaaa" />
        <span id="erro-nascimento" class="erro"></span>
      </div>
      <div>
        <label for="admissao">Data de Admissão:</label>
        <input type="text" name="Data_admissao" id="admissao" placeholder="dd/mm/aaaa" />
      </div>
    </div>

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const telefone = document.getElementById("telefone");
  const rg = document.getElementById("rg");
  const cpf = document.getElementById("cpf");
  const cep = document.getElementById("cep");
  const nascimento = document.getElementById("nascimento");
  const admissao = document.getElementById("admissao");
  const senha = document.getElementById("senha");

  // Máscara telefone (99) 99999-9999
  telefone.addEventListener("input", () => {
    let v = telefone.value.replace(/\D/g,"");
    if(v.length > 11) v = v.slice(0,11);
    v = v.replace(/^(\d{2})(\d)/,"($1) $2");
    v = v.replace(/(\d{5})(\d)/,"$1-$2");
    telefone.value = v;
  });

  // Máscara RG (99.999.999-9)
  rg.addEventListener("input", () => {
    let v = rg.value.replace(/\D/g,"").slice(0,9);
    if(v.length > 2) v = v.slice(0,2) + '.' + v.slice(2);
    if(v.length > 5) v = v.slice(0,6) + '.' + v.slice(6);
    if(v.length > 8) v = v.slice(0,9) + '-' + v.slice(9);
    rg.value = v;
  });

  // Máscara CPF (999.999.999-99)
  cpf.addEventListener("input", () => {
    let v = cpf.value.replace(/\D/g,"").slice(0,11);
    v = v.replace(/(\d{3})(\d)/,"$1.$2");
    v = v.replace(/(\d{3})(\d)/,"$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
    cpf.value = v;
  });

  // Máscara CEP (99999-999)
  cep.addEventListener("input", () => {
    let v = cep.value.replace(/\D/g,"").slice(0,8);
    v = v.replace(/(\d{5})(\d)/,"$1-$2");
    cep.value = v;
  });

  // Máscara datas dd/mm/aaaa
  function mascaraData(el){
    el.addEventListener("input", () => {
      let v = el.value.replace(/\D/g,"").slice(0,8);
      if(v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
      if(v.length > 5) v = v.slice(0,5) + '/' + v.slice(5);
      el.value = v;
    });
  }
  mascaraData(nascimento);
  mascaraData(admissao);

  // Validação antes de enviar
  document.querySelector("form").addEventListener("submit",(e)=>{
    let ok = true;

    // CPF
    if(cpf.value.replace(/\D/g,"").length !== 11){
      document.getElementById("erro-cpf").innerText = "CPF inválido.";
      ok = false;
    } else document.getElementById("erro-cpf").innerText = "";

    // Senha
    if(senha.value.length > 0 && senha.value.length < 8){
      document.getElementById("erro-senha").innerText = "Senha deve ter no mínimo 8 caracteres.";
      ok = false;
    } else document.getElementById("erro-senha").innerText = "";

    // Data nascimento >= 18 anos
    if(nascimento.value){
      const p = nascimento.value.split("/");
      if(p.length === 3){
        const nasc = new Date(p[2], p[1]-1, p[0]);
        const hoje = new Date();
        let idade = hoje.getFullYear() - nasc.getFullYear();
        if(hoje < new Date(nasc.setFullYear(hoje.getFullYear()))) idade--;
        if(idade < 18){
          document.getElementById("erro-nascimento").innerText = "Funcionário deve ter pelo menos 18 anos.";
          ok = false;
        } else document.getElementById("erro-nascimento").innerText = "";
      }
    }

    if(!ok) e.preventDefault();
  });
});
</script>

</body>
</html>
