<?php
session_start();
require_once '../conexao.php';
// Verifica permissão
if ($_SESSION['nivel'] != 1) {
    die("Acesso negado!");
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
    $id = $_POST['ID_func'];
    $nome       = $_POST['Nome_func'] ?? '';
    $telefone   = $_POST['Telefone'] ?? '';
    $sexo       = $_POST['Sexo'] ?? '';
    $rg         = $_POST['RG'] ?? '';
    $cpf        = $_POST['CPF'] ?? '';
    $esta_civil = $_POST['Esta_civil'] ?? '';
    $uf         = $_POST['UF'] ?? '';
    $cidade     = $_POST['Cidade'] ?? '';
    $bairro     = $_POST['Bairro'] ?? '';
    $cep        = $_POST['CEP'] ?? '';
    $num_casa   = $_POST['Num_casa'] ?? '';
    $logradouro = $_POST['Logradouro'] ?? '';
    $email      = $_POST['Email'] ?? '';
    $nivel      = $_POST['nivel_de_acesso'] ?? '';
    $cargo      = $_POST['Cargo'] ?? '';
    $data_nasc  = $_POST['Data_nascimento'] ?? null;
    $data_adm   = $_POST['Data_admissao'] ?? null;

    // Mantém a senha antiga se o campo estiver vazio
    $senha = !empty($_POST['Senha']) ? password_hash($_POST['Senha'], PASSWORD_DEFAULT) : $func['Senha'];

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
    $stmt->execute([
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

    echo "<script>alert('Funcionário alterado com sucesso!');window.location.href='../funcionarios.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Alterar Funcionário</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{background:#eef2f7;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px;}
form{background:#fff;padding:35px 40px;border-radius:15px;box-shadow:0 12px 25px rgba(0,0,0,0.12);max-width:550px;width:100%;}
h2{text-align:center;margin-bottom:30px;color:#2c3e50;font-size:1.8rem;}
label{display:block;margin-bottom:6px;font-weight:600;color:#34495e;}
input, select{width:100%;padding:12px 15px;margin-bottom:15px;border:1px solid #ccc;border-radius:10px;font-size:0.95rem;transition:all 0.3s ease;}
input:focus, select:focus{border-color:#3498db;box-shadow:0 0 8px rgba(52,152,219,0.3);outline:none;}
button{width:100%;padding:14px;background:#3498db;border:none;color:white;font-size:1rem;font-weight:600;border-radius:10px;cursor:pointer;transition:0.3s;}
button:hover{background:#2980b9;}
.flex-group{display:flex;gap:10px;margin-bottom:15px;}
.flex-group input,.flex-group select{flex:1;}
.erro{color:#e74c3c;font-size:0.85rem;margin-top:-10px;margin-bottom:10px;display:block;}
@media(max-width:600px){form{padding:25px 20px;}.flex-group{flex-direction:column;}}
</style>
</head>
<body>
<div class="page">
<div class="form-box">
<a href="funcionarios.php"><button class="back-button">Voltar</button></a>

<form method="POST" action="editar_funcionario.php">
    <input type="hidden" name="ID_func" value="<?= htmlspecialchars($func['ID_func']) ?>">

<h2>Alterar Funcionário</h2>

<label>Nome:</label>
<input type="text" name="Nome_func" required value="<?= htmlspecialchars($func['Nome_func'] ?? '') ?>">

<label>Telefone:</label>
<input type="text" name="Telefone" id="telefone" required value="<?= htmlspecialchars($func['Telefone'] ?? '') ?>">

<label>Sexo:</label>
<select name="Sexo" required>
  <option value="">Selecione</option>
  <option value="M" <?= ($func['Sexo'] ?? '')=='M'?'selected':'' ?>>Masculino</option>
  <option value="F" <?= ($func['Sexo'] ?? '')=='F'?'selected':'' ?>>Feminino</option>
</select>

<label>RG:</label>
<input type="text" name="RG" id="rg" required value="<?= htmlspecialchars($func['RG'] ?? '') ?>">

<label>CPF:</label>
<input type="text" name="CPF" id="cpf" required maxlength="15" value="<?= htmlspecialchars($func['CPF'] ?? '') ?>">
<span id="erro-cpf" class="erro"></span>

<select name="Esta_civil" id="Estado_civil" value="<?= htmlspecialchars($func['Esta_civil'] ?? '') ?>">
<option value=""></option>
<option>Solteiro</option>
<option>Casado</option>
<option>Viúvo</option>

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
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const telefone=document.getElementById("telefone");
  const rg=document.getElementById("rg");
  const cpf=document.getElementById("cpf");
  const cep=document.getElementById("cep");
  const nascimento=document.getElementById("nascimento");
  const senha=document.getElementById("senha");

  telefone.addEventListener("input",()=>{telefone.value=telefone.value.replace(/\D/g,"").replace(/^(\d{2})(\d)/,"($1) $2").replace(/(\d{5})(\d{4}).*/,"$1-$2");});

  rg.addEventListener("input", () => {
    let v = rg.value.replace(/\D/g,""); // remove tudo que não é número
    v = v.slice(0, 9); // limita a 9 dígitos
    if(v.length > 2) v = v.slice(0,2) + '.' + v.slice(2);
    if(v.length > 6) v = v.slice(0,6) + '.' + v.slice(6);
    if(v.length > 10) v = v.slice(0,10) + '-' + v.slice(10);
    rg.value = v;
  });

  cpf.addEventListener("input",()=>{cpf.value=cpf.value.replace(/\D/g,"").replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d{1,2})$/,"$1-$2");});
  cep.addEventListener("input",()=>{cep.value=cep.value.replace(/\D/g,"").replace(/(\d{5})(\d{3})$/,"$1-$2");});
  nascimento.addEventListener("input",()=>{nascimento.value=nascimento.value.replace(/\D/g,"").replace(/(\d{2})(\d)/,"$1/$2").replace(/(\d{2})(\d)/,"$1/$2").replace(/(\d{4})(\d)/,"$1");});

  document.querySelector("form").addEventListener("submit",(e)=>{
    let ok=true;
    if(cpf.value.replace(/\D/g,"").length!==11){document.getElementById("erro-cpf").innerText="CPF inválido."; ok=false;} else document.getElementById("erro-cpf").innerText="";
    if(senha.value.length>0 && senha.value.length<8){document.getElementById("erro-senha").innerText="Senha deve ter no mínimo 8 caracteres."; ok=false;} else document.getElementById("erro-senha").innerText="";
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
