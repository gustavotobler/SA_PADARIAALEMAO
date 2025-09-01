<?php
session_start();
require_once("../conexao.php");

$msg = "";

// Pega o ID tanto da URL quanto do formulário
$id = isset($_GET["id"]) ? (int) $_GET["id"] : (isset($_POST["id"]) ? (int) $_POST["id"] : 0);

if ($id <= 0) {
    die("ID do fornecedor não informado!");
}

// Busca os dados atuais do fornecedor
try {
    $sql = "SELECT * FROM fornecedores WHERE ID_forn = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {
        die("Fornecedor não encontrado!");
    }
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

// Atualizar ao enviar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    if ($nome_forn) {
        $sql = "UPDATE fornecedores 
                SET Nome_forn=:Nome_forn, Telefone=:Telefone, CNPJ=:CNPJ, UF=:UF, Cidade=:Cidade,
                    Bairro=:Bairro, CEP=:CEP, Num_empresa=:Num_empresa, Logradouro=:Logradouro, 
                    Email=:Email, Data_fundacao=:Data_fundacao
                WHERE ID_forn=:id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":Nome_forn", $nome_forn);
        $stmt->bindParam(":Telefone", $telefone);
        $stmt->bindParam(":CNPJ", $cnpj);
        $stmt->bindParam(":UF", $uf);
        $stmt->bindParam(":Cidade", $cidade);
        $stmt->bindParam(":Bairro", $bairro);
        $stmt->bindParam(":CEP", $cep);
        $stmt->bindParam(":Num_empresa", $num_empresa, PDO::PARAM_INT);
        $stmt->bindParam(":Logradouro", $logradouro);
        $stmt->bindParam(":Email", $email);
        $stmt->bindParam(":Data_fundacao", $data_fundacao);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $msg = "<script>alert('Fornecedor atualizado com sucesso!');window.location.href='../fornecedores.php'</script>";
        } else {
            $msg = "<script>alert('Erro ao atualizar fornecedor!');</script>";
        }
    } else {
        $msg = "<script>alert('O campo Nome do Fornecedor é obrigatório!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Fornecedor</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
body{background:#eef2f7; min-height:100vh; display:flex; flex-direction:column;}
header{background:rgb(27,68,95); padding:15px 20px; color:white; display:flex; align-items:center; gap:15px; box-shadow:0 3px 10px rgba(0,0,0,0.15);}
header .back-btn{background:transparent; border:none; color:white; cursor:pointer; font-size:24px; font-weight:700; user-select:none;}
header h1{flex:1; font-weight:700; font-size:1.5rem; user-select:none; text-align:center;}
main{flex:1; display:flex; justify-content:center; padding:25px 15px;}
form{background:#fff; padding:30px 35px; border-radius:15px; box-shadow:0 12px 25px rgba(0,0,0,0.12); max-width:600px; width:100%;}
form h2{text-align:center; margin-bottom:30px; color:#2c3e50; font-size:1.8rem;}
label{display:block; margin-bottom:6px; font-weight:600; color:#34495e;}
input,select{width:100%; padding:12px 15px; margin-bottom:15px; border:1px solid #ccc; border-radius:10px; font-size:0.95rem; transition:all 0.3s ease;}
input:focus,select:focus{border-color:rgb(27,68,95); box-shadow:0 0 8px rgba(27,68,95,0.3); outline:none;}
button[type="submit"]{width:100%; padding:14px; background:rgb(27,68,95); border:none; color:white; font-size:1rem; font-weight:600; border-radius:10px; cursor:pointer; transition:background-color 0.3s;}
button[type="submit"]:hover{background:rgb(0,153,255);}
.flex-group{display:flex; gap:10px; margin-bottom:15px;}
.flex-group > div{flex:1;}
.erro{color:#e74c3c; font-size:0.85rem; margin-top:-10px; margin-bottom:10px; display:block;}
@media(max-width:600px){.flex-group{flex-direction:column;}}
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='../fornecedores.php'">&#8592; Voltar</button>
  <h1>Editar Fornecedor</h1>
</header>

<main>
  <form method="POST" id="form-fornecedor">
    <input type="hidden" name="id" value="<?= $id ?>">
    <?php echo $msg; ?>

    <h2>Dados da Empresa</h2>
    <label for="nome_forn">Nome da Empresa</label>
    <input type="text" id="nome_forn" name="Nome_forn" value="<?= htmlspecialchars($fornecedor['Nome_forn']) ?>" required/>

    <label for="cnpj">CNPJ</label>
    <input type="text" id="cnpj" name="CNPJ" value="<?= htmlspecialchars($fornecedor['CNPJ']) ?>" required maxlength="18"/>

    <label for="data_fundacao">Data de Fundação</label>
    <input type="date" id="data_fundacao" name="Data_fundacao" value="<?= $fornecedor['Data_fundacao'] ?>" />

    <h2>Endereço</h2>
    <label for="logradouro">Logradouro</label>
    <input type="text" id="logradouro" name="Logradouro" value="<?= htmlspecialchars($fornecedor['Logradouro']) ?>" required />
    <label for="num_empresa">Número</label>
    <input type="number" id="num_empresa" name="Num_empresa" value="<?= htmlspecialchars($fornecedor['Num_empresa']) ?>" required />
    <label for="bairro">Bairro</label>
    <input type="text" id="bairro" name="Bairro" value="<?= htmlspecialchars($fornecedor['Bairro']) ?>" required />
    <label for="cidade">Cidade</label>
    <input type="text" id="cidade" name="Cidade" value="<?= htmlspecialchars($fornecedor['Cidade']) ?>" required />
    <label for="uf">UF</label>
    <select id="uf" name="UF" required>
      <option value="">Selecione</option>
      <?php 
        $ufs = ["AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG","PA","PB",
                "PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO"];
        foreach ($ufs as $u) {
            $sel = ($u == $fornecedor['UF']) ? "selected" : "";
            echo "<option value='$u' $sel>$u</option>";
        }
      ?>
    </select>
    <label for="cep">CEP</label>
    <input type="text" id="cep" name="CEP" value="<?= htmlspecialchars($fornecedor['CEP']) ?>" required maxlength="9"/>

    <h2>Formas de Contato</h2>
    <label for="email">Email</label>
    <input type="email" id="email" name="Email" value="<?= htmlspecialchars($fornecedor['Email']) ?>" required />
    <label for="telefone">Telefone ou Celular</label>
    <input type="text" id="telefone" name="Telefone" value="<?= htmlspecialchars($fornecedor['Telefone']) ?>" maxlength="15"/>

    <button type="submit">Salvar Alterações</button>
  </form>
</main>

<script>
// Máscaras
function mascaraCNPJ(cnpj){cnpj=cnpj.replace(/\D/g,"");cnpj=cnpj.replace(/^(\d{2})(\d)/,"$1.$2");cnpj=cnpj.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");cnpj=cnpj.replace(/\.(\d{3})(\d)/,".$1/$2");cnpj=cnpj.replace(/(\d{4})(\d)/,"$1-$2");return cnpj;}
function mascaraCEP(cep){cep=cep.replace(/\D/g,"");cep=cep.replace(/^(\d{5})(\d)/,"$1-$2");return cep;}
function mascaraTelefone(tel){tel=tel.replace(/\D/g,"");if(tel.length<=10){tel=tel.replace(/(\d{2})(\d{4})(\d{0,4})/,"($1) $2-$3");}else{tel=tel.replace(/(\d{2})(\d{5})(\d{0,4})/,"($1) $2-$3");}return tel;}

document.getElementById("cnpj").addEventListener("input",function(){this.value=mascaraCNPJ(this.value);});
document.getElementById("cep").addEventListener("input",function(){this.value=mascaraCEP(this.value);});
document.getElementById("telefone").addEventListener("input",function(){this.value=mascaraTelefone(this.value);});

// Validação
document.getElementById("form-fornecedor").addEventListener("submit",function(e){
    let erros=[];
    const nome=document.getElementById("nome_forn").value.trim();
    const cnpj=document.getElementById("cnpj").value.trim();
    const cep=document.getElementById("cep").value.trim();
    const telefone=document.getElementById("telefone").value.trim();
    const email=document.getElementById("email").value.trim();
    const uf=document.getElementById("uf").value;

    if(nome.length<3) erros.push("O nome deve ter pelo menos 3 caracteres.");
    if(cnpj.replace(/\D/g,'').length!==14) erros.push("CNPJ deve ter 14 dígitos.");
    if(cep.replace(/\D/g,'').length!==8) erros.push("CEP deve ter 8 dígitos.");
    if(telefone && (telefone.replace(/\D/g,'').length<10 || telefone.replace(/\D/g,'').length>11)) erros.push("Telefone deve ter 10 ou 11 dígitos.");
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) erros.push("E-mail inválido.");
    if(!uf) erros.push("Selecione uma UF válida.");

    if(erros.length>0){ e.preventDefault(); alert(erros.join("\n")); }
});
</script>

</body>
</html>
