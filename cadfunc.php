<?php
session_start();
require_once 'conexao.php';
<<<<<<< Updated upstream
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se o usuário tem permissão supondo que o perfil 1 seja o admin
if($_SESSION['nivel'] != 1){
=======

// Verifica se o usuário tem permissão supondo que o perfil 1 seja o admin
if($_SESSION['nivel_de_acesso'] != 1){
>>>>>>> Stashed changes
    echo "Acesso negado!";
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
  $nome          = $_POST['Nome_func'];
  $telefone      = $_POST['Telefone'];
  $sexo          = $_POST['Sexo'];
  $rg            = $_POST['RG'];
  $cpf           = $_POST['CPF'];
  $esta_civil    = $_POST['Esta_civil'];
  $uf            = $_POST['UF'];
  $cidade        = $_POST['Cidade'];
  $bairro        = $_POST['Bairro'];
  $tipo          = $_POST['Tipo'];
  $cep           = $_POST['CEP'];
  $num_casa      = $_POST['Num_casa'];
  $logradouro    = $_POST['Logradouro'];
  $senha         = password_hash($_POST['Senha'], PASSWORD_DEFAULT);
  $email         = $_POST['Email'];
<<<<<<< Updated upstream
  $nivel         = $_POST['Nivel'];
=======
  $nivel         = $_POST['nivel_de_acesso'];
>>>>>>> Stashed changes
  $data_nasc     = $_POST['Data_nascimento'];
  $data_adm      = $_POST['Data_admissao'];
  $cargo         = $_POST['Cargo'];
  
  $sql = "INSERT INTO funcionario 
  (Nome_func,Telefone,Sexo,RG,CPF,Esta_civil,UF,Cidade,Bairro,Tipo,CEP,Num_casa,Logradouro,Senha,Email,Nivel,Data_nascimento,Data_admissao,Cargo)
  VALUES 
  (:Nome_func,:Telefone,:Sexo,:RG,:CPF,:Esta_civil,:UF,:Cidade,:Bairro,:Tipo,:CEP,:Num_casa,:Logradouro,:Senha,:Email,:Nivel,:Data_nascimento,:Data_admissao,:Cargo)";

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
<<<<<<< Updated upstream
  $stmt->bindParam(":Nivel",$nivel);
=======
  $stmt->bindParam(":nivel_de_acesso",$nivel_de_acesso);
>>>>>>> Stashed changes
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Funcionário</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/cadfunc.css" rel="stylesheet"/>
</head>
<body>
  <div class="container">
    <div class="header">
      <a href="funcionarios.php">
        <button class="back-button"><span class="material-icons">arrow_back</span></button>
      </a>
      <h1>CADASTRO DE FUNCIONÁRIO</h1>
    </div>

    <div class="section-title">DADOS PESSOAIS</div>

    <form id="cadastro-funcionario" class="form-box" action="cadfunc.php" method="POST">
      <div class="form-group">
        <input type="text" id="nome" name="Nome_func" placeholder="Nome completo" required>
        <input type="text" id="rg" name="RG" placeholder="RG" oninput="formatRG(this)" maxlength="12" required>
        <select id="sexo" name="Sexo" required>
          <option value="" disabled selected>Sexo</option>
          <option value="Masculino">Masculino</option>
          <option value="Feminino">Feminino</option>
        </select>
      </div>

      <div class="form-group">
        <input type="text" id="nascimento" name="Data_nascimento" placeholder="Data de Nascimento" maxlength="10" oninput="formatarData(this)" required>
        <input type="text" id="admissao" name="Data_admissao" placeholder="Data de Admissão" maxlength="10" oninput="formatarData(this)" required>
        <input type="text" id="cpf" name="CPF" placeholder="CPF" oninput="formatCPF(this)" maxlength="14" required>
        <select id="estado-civil" name="Esta_civil" required>
          <option value="" disabled selected>Estado civil</option>
          <option value="Solteiro">Solteiro(a)</option> 
          <option value="Casado">Casado(a)</option>
          <option value="Viúvo">Viúvo(a)</option>
        </select>
        <input type="password" id="senha" name="Senha" placeholder="Senha" maxlength="12" required>
      </div>

      <select id="cargo" name="Cargo" required>
        <option value="" disabled selected>Selecione o Cargo</option>
        <option value="Padeiro">Padeiro</option>
        <option value="Confeiteiro">Confeiteiro</option>
        <option value="Gerente">Gerente</option>
        <option value="Caixa">Caixa</option>
        <option value="Entregador">Entregador</option>
        <option value="Ajudante Geral">Ajudante Geral</option>
        <option value="Auxiliar de Limpeza">Auxiliar de Limpeza</option>
        <option value="Estoquista">Estoquista</option>
        <option value="Atendente">Atendente</option>
      </select>

      <input type="text" id="uf" name="UF" class="full-width" placeholder="Estado" required>
      <input type="text" id="cidade" name="Cidade" class="full-width" placeholder="Cidade" required>
      <input type="text" id="bairro" name="Bairro" class="full-width" placeholder="Bairro" required>
      <input type="text" id="tipo" name="Tipo" class="full-width" placeholder="Tipo de rua" required>
      <input type="text" id="cep" name="CEP" class="full-width" placeholder="CEP" required>
      <input type="text" id="num_casa" name="Num_casa" class="full-width" placeholder="Número da casa" required>
      <input type="text" id="logradouro" name="Logradouro" class="full-width" placeholder="Logradouro" required>

      <div class="section-subtitle">FORMAS DE CONTATO</div>
      <div class="form-group">
        <input type="email" id="email" name="Email" placeholder="Digite seu e-mail" required>
        <input type="text" id="telefone" name="Telefone" placeholder="Telefone" oninput="formatTelefone(this)" maxlength="15" required>
      </div>

      <div class="section-subtitle">NÍVEL</div>
      <div class="form-group center">
        <select id="nivel" name="Nivel" required>
          <option value="1">Nível 1</option>
          <option value="2">Nível 2</option>
          <option value="3">Nível 3</option>
        </select>
      </div>

      <div class="form-group center">
        <button type="submit" class="submit-button">CADASTRAR</button>
      </div>
    </form>
  </div>

  <!-- Mensagens de erro/sucesso -->
  <div class="mensagem-erro" id="erro-senha">
    <strong>Senha inválida! Ela deve conter:</strong>
    <ul id="lista-erros"></ul>
    <button onclick="fecharErro()">OK</button>
  </div>

  <div class="mensagem-sucesso" id="sucesso-cadastro">
    <img src="img/confirmado.png" alt="Cadastro realizado com sucesso">
    <div style="text-align: center; margin-top: 15px;">
      <button onclick="fecharSucesso()" class="botao-ok">OK</button>
    </div>
  </div>

  <div class="mensagem-erro" id="erro-cpf"><strong>CPF inválido!</strong></div>
  <div class="mensagem-erro" id="erro-rg"><strong>RG inválido!</strong></div>
  <div class="mensagem-erro" id="erro-telefone"><strong>Telefone inválido!</strong></div>
  <div class="mensagem-erro" id="erro-email"><strong>Email inválido!</strong></div>
  <div class="mensagem-erro" id="erro-nome"><strong>Nome inválido!</strong></div>
  <div class="mensagem-erro" id="erro-nascimento"><strong>Data de nascimento inválida!</strong></div>

<script>
// Funções JS (corrigidas para IDs em minúsculo)
// ...
</script>
</body>
</html>
