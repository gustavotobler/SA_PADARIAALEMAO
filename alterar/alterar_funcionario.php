<?php
session_start();
require_once '../conexao.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica permissão

if ($_SESSION['nivel'] != 1) {
  echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../funcionarios.php';</script>";
  exit;
}
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
  echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
  exit;
}


// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Funcionário não encontrado!");
}

$id = intval($_GET['id']);

// Pega os dados do funcionário
$stmt = $pdo->prepare("SELECT * FROM funcionario WHERE ID_func = :id");
$stmt->execute(['id' => $id]);
$func = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$func) {
    die("Funcionário não encontrado!");
}

// Função para formatar datas para o banco
function formatarDataBanco($data){
    if(!$data) return null;
    $partes = explode("/", $data);
    if(count($partes) == 3){
        return $partes[2]."-".$partes[1]."-".$partes[0];
    }
    return null;
}

// Processa o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome        = $_POST['Nome_func'] ?? '';
    $telefone    = $_POST['Telefone'] ?? '';
    $sexo        = $_POST['Sexo'] ?? '';
    $rg          = $_POST['RG'] ?? '';
    $cpf         = $_POST['CPF'] ?? '';
    $esta_civil  = $_POST['Esta_civil'] ?? '';
    $uf          = $_POST['UF'] ?? '';
    $cidade      = $_POST['Cidade'] ?? '';
    $bairro      = $_POST['Bairro'] ?? '';
    $cep         = $_POST['CEP'] ?? '';
    $num_casa    = $_POST['Num_casa'] ?? '';
    $logradouro  = $_POST['Logradouro'] ?? '';
    $email       = $_POST['Email'] ?? '';
    $nivel       = $_POST['nivel_de_acesso'] ?? '';
    $cargo       = $_POST['Cargo'] ?? '';
    $data_nasc   = formatarDataBanco($_POST['Data_nascimento'] ?? null);
    $data_adm    = formatarDataBanco($_POST['Data_admissao'] ?? null);

    // Mantém a senha antiga se o campo estiver vazio
    $senha = !empty($_POST['Senha']) ? password_hash($_POST['Senha'], PASSWORD_DEFAULT) : $func['Senha'];

    // Atualiza os dados
    $sql = "UPDATE funcionario SET 
                Nome_func=:Nome_func,
                Telefone=:Telefone,
                Sexo=:Sexo,
                RG=:RG,
                CPF=:CPF,
                Esta_civil=:Esta_civil,
                UF=:UF,
                Cidade=:Cidade,
                Bairro=:Bairro,
                CEP=:CEP,
                Num_casa=:Num_casa,
                Logradouro=:Logradouro,
                Senha=:Senha,
                Email=:Email,
                nivel_de_acesso=:nivel_de_acesso,
                Data_nascimento=:Data_nascimento,
                Data_admissao=:Data_admissao,
                Cargo=:Cargo
            WHERE ID_func=:id";

    $stmt = $pdo->prepare($sql);
    $executou = $stmt->execute([
        'Nome_func'=>$nome,
        'Telefone'=>$telefone,
        'Sexo'=>$sexo,
        'RG'=>$rg,
        'CPF'=>$cpf,
        'Esta_civil'=>$esta_civil,
        'UF'=>$uf,
        'Cidade'=>$cidade,
        'Bairro'=>$bairro,
        'CEP'=>$cep,
        'Num_casa'=>$num_casa,
        'Logradouro'=>$logradouro,
        'Senha'=>$senha,
        'Email'=>$email,
        'nivel_de_acesso'=>$nivel,
        'Data_nascimento'=>$data_nasc,
        'Data_admissao'=>$data_adm,
        'Cargo'=>$cargo,
        'id'=>$id
    ]);

    if($executou){
        echo "<script>alert('Funcionário alterado com sucesso!');window.location.href='../funcionarios.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erro ao atualizar o funcionário.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Alterar Funcionário</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{background:rgb(59, 75, 93);min-height:100vh;display:flex;flex-direction:column;}
header {background:rgb(27, 68, 95);padding: 15px 20px;color: white;display: flex;align-items: center;gap: 15px;box-shadow: 0 3px 10px rgba(0,0,0,0.15);}
header .back-btn {background: transparent;border: none;color: white;cursor: pointer;font-size: 24px;}
header h1 {flex: 1;font-weight: 700;font-size: 1.5rem;}
main {flex: 1;display: flex;justify-content: center;padding: 25px 15px;}
form {background:#fff;padding:30px 35px;border-radius:15px;box-shadow:0 12px 25px rgba(0,0,0,0.12);max-width:600px;width:100%;}
form h2 {text-align:center;margin-bottom:30px;color:#2c3e50;font-size:1.8rem;}
label{display:block;margin-bottom:6px;font-weight:600;color:#34495e;}
input, select {width:100%;padding:12px 15px;margin-bottom:15px;border:1px solid #ccc;border-radius:10px;font-size:0.95rem;transition:all 0.3s;}
input:focus,select:focus {border-color:rgb(27,68,95);box-shadow:0 0 8px rgba(52,152,219,0.3);outline:none;}
button[type="submit"] {width:100%;padding:14px;background:rgb(27,68,95);border:none;color:white;font-size:1rem;font-weight:600;border-radius:10px;cursor:pointer;transition:background-color 0.3s;}
button[type="submit"]:hover {background:rgb(0,153,255);}
.flex-group{display:flex;gap:10px;margin-bottom:15px;}
.flex-group>div{flex:1;}
.erro{color:#e74c3c;font-size:0.85rem;margin-top:-10px;margin-bottom:10px;display:block;}
@media(max-width:600px){.flex-group{flex-direction:column;}}
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='../funcionarios.php'" title="Voltar">
    <span class="material-icons">arrow_back</span>
  </button>
  <h1>Alterar Funcionário</h1>
</header>

<main>
<form method="POST">
  <input type="hidden" name="ID_func" value="<?= htmlspecialchars($func['ID_func']) ?>">

  <h2>Alterar Funcionário</h2>

  <label>Nome:</label>
  <input type="text" name="Nome_func" required value="<?= htmlspecialchars($func['Nome_func'] ?? '') ?>">

  <label>Telefone:</label>
  <input type="text" name="Telefone" id="telefone" required value="<?= htmlspecialchars($func['Telefone'] ?? '') ?>">

  <label>Sexo:</label>
  <select name="Sexo" required>
    <option value="">Selecione</option>
    <option value="Masculino" <?= ($func['Sexo'] ?? '')=='Masculino'?'selected':'' ?>>Masculino</option>
    <option value="Feminino" <?= ($func['Sexo'] ?? '')=='Feminino'?'selected':'' ?>>Feminino</option>
  </select>

  <label>RG:</label>
  <input type="text" name="RG" id="rg" required value="<?= htmlspecialchars($func['RG'] ?? '') ?>">

  <label>CPF:</label>
  <input type="text" name="CPF" id="cpf" maxlength="14" required value="<?= htmlspecialchars($func['CPF'] ?? '') ?>">
  <span id="erro-cpf" class="erro"></span>

  <label>Estado civil</label>
  <select name="Esta_civil">
    <option value="">Selecione</option>
    <option <?= ($func['Esta_civil'] ?? '')=='Solteiro'?'selected':'' ?>>Solteiro</option>
    <option <?= ($func['Esta_civil'] ?? '')=='Casado'?'selected':'' ?>>Casado</option>
    <option <?= ($func['Esta_civil'] ?? '')=='Viúvo'?'selected':'' ?>>Viúvo</option>
  </select>

  <div class="flex-group">
    <div>
      <label>UF:</label>
      <input type="text" name="UF" maxlength="2" value="<?= htmlspecialchars($func['UF'] ?? '') ?>">
    </div>
    <div>
      <label>Número da Casa:</label>
      <input type="text" name="Num_casa" value="<?= htmlspecialchars($func['Num_casa'] ?? '') ?>">
    </div>
  </div>

  <label>Cidade:</label>
  <input type="text" name="Cidade" value="<?= htmlspecialchars($func['Cidade'] ?? '') ?>">

  <label>Bairro:</label>
  <input type="text" name="Bairro" value="<?= htmlspecialchars($func['Bairro'] ?? '') ?>">

  <label>CEP:</label>
  <input type="text" name="CEP" id="cep" value="<?= htmlspecialchars($func['CEP'] ?? '') ?>">

  <label>Logradouro:</label>
  <input type="text" name="Logradouro" value="<?= htmlspecialchars($func['Logradouro'] ?? '') ?>">

  <label>Email:</label>
  <input type="email" name="Email" id="email" required value="<?= htmlspecialchars($func['Email'] ?? '') ?>">

  <label>Senha (deixe em branco para não alterar):</label>
  <input type="password" name="Senha" id="senha">
  <span id="erro-senha" class="erro"></span>

  <label>Nível de Acesso:</label>
  <select name="nivel_de_acesso" required>
    <option value="1" <?= ($func['nivel_de_acesso'] ?? '')==1?'selected':'' ?>>Administrador</option>
    <option value="2" <?= ($func['nivel_de_acesso'] ?? '')==2?'selected':'' ?>>Funcionário</option>
  </select>

  <label>Cargo:</label>
  <input type="text" name="Cargo" value="<?= htmlspecialchars($func['Cargo'] ?? '') ?>">

  <div class="flex-group">
    <div>
      <label>Data de Nascimento:</label>
      <input type="text" name="Data_nascimento" id="nascimento" placeholder="dd/mm/aaaa" value="<?= !empty($func['Data_nascimento']) ? date('d/m/Y', strtotime($func['Data_nascimento'])) : '' ?>">
      <span id="erro-nascimento" class="erro"></span>
    </div>
    <div>
      <label>Data de Admissão:</label>
      <input type="text" name="Data_admissao" id="admissao" placeholder="dd/mm/aaaa" value="<?= !empty($func['Data_admissao']) ? date('d/m/Y', strtotime($func['Data_admissao'])) : '' ?>">
    </div>
  </div>

  <button type="submit">Salvar Alterações</button>
</form>
</main>

<script>
// Máscaras e validações
document.addEventListener("DOMContentLoaded", function(){
  const telefone=document.getElementById("telefone");
  const rg=document.getElementById("rg");
  const cpf=document.getElementById("cpf");
  const cep=document.getElementById("cep");
  const nascimento=document.getElementById("nascimento");
  const admissao=document.getElementById("admissao");
  const senha=document.getElementById("senha");

  telefone.addEventListener("input", () => {
    let v=telefone.value.replace(/\D/g,"").slice(0,11);
    if(v.length>10) v=v.replace(/^(\d{2})(\d{5})(\d{4})$/,"($1) $2-$3");
    else if(v.length>6) v=v.replace(/^(\d{2})(\d{4})(\d{0,4})$/,"($1) $2-$3");
    else if(v.length>2) v=v.replace(/^(\d{2})(\d{0,5})$/,"($1) $2");
    else v=v.replace(/^(\d*)$/,"($1");
    telefone.value=v;
  });

  rg.addEventListener("input", () => {
    let v=rg.value.replace(/\D/g,"").slice(0,9);
    if(v.length>2) v=v.slice(0,2)+"."+v.slice(2);
    if(v.length>6) v=v.slice(0,6)+"."+v.slice(6);
    rg.value=v;
  });

  cpf.addEventListener("input", () => {
    let v=cpf.value.replace(/\D/g,"").slice(0,11);
    v=v.replace(/(\d{3})(\d)/,"$1.$2");
    v=v.replace(/(\d{3})(\d)/,"$1.$2");
    v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
    cpf.value=v;
  });

  cep.addEventListener("input", () => {
    let v=cep.value.replace(/\D/g,"").slice(0,8);
    v=v.replace(/(\d{5})(\d)/,"$1-$2");
    cep.value=v;
  });

  function mascaraData(el){
    el.addEventListener("input", () => {
      let v=el.value.replace(/\D/g,"").slice(0,8);
      if(v.length>2) v=v.slice(0,2)+"/"+v.slice(2);
      if(v.length>5) v=v.slice(0,5)+"/"+v.slice(5);
      el.value=v;
    });
  }
  mascaraData(nascimento);
  mascaraData(admissao);

  document.querySelector("form").addEventListener("submit",(e)=>{
    let ok=true;
    if(cpf.value.replace(/\D/g,"").length!==11){
      document.getElementById("erro-cpf").innerText="CPF inválido."; ok=false;
    } else document.getElementById("erro-cpf").innerText="";
    if(senha.value.length>0 && senha.value.length<8){
      document.getElementById("erro-senha").innerText="Senha deve ter no mínimo 8 caracteres."; ok=false;
    } else document.getElementById("erro-senha").innerText="";
    if(!ok) e.preventDefault();
  });
});
</script>

</body>
</html>
