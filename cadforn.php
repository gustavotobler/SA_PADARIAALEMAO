<?php
session_start();
require_once("conexao.php");

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
            $msg = "<script>alert('Fornecedor cadastrado com sucesso!');window.location.href='fornecedores.php'</script>";
        } else {
            $msg = "<script>alert('Erro ao cadastrar fornecedor!');</script>";
        }
    } else {
        $msg = "<script>alert('Preencha todos os campos obrigatórios!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro de Fornecedor</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
body { background:#eef2f7; min-height:100vh; display:flex; flex-direction:column; }
header { background:rgb(27,68,95); padding:15px 20px; color:white; display:flex; align-items:center; gap:15px; box-shadow:0 3px 10px rgba(0,0,0,0.15);}
header .back-btn { background:transparent; border:none; color:white; cursor:pointer; font-size:24px; font-weight:700;}
header h1 { flex:1; text-align:center; font-weight:700; font-size:1.5rem;}
main { flex:1; display:flex; justify-content:center; padding:25px 15px;}
form { background:#fff; padding:30px 35px; border-radius:15px; box-shadow:0 12px 25px rgba(0,0,0,0.12); max-width:600px; width:100%; }
form h2 { text-align:center; margin-bottom:30px; color:#2c3e50; font-size:1.8rem;}
label { display:block; margin-bottom:6px; font-weight:600; color:#34495e; }
input, select { width:100%; padding:12px 15px; margin-bottom:15px; border:1px solid #ccc; border-radius:10px; font-size:0.95rem; transition:all 0.3s;}
input:focus, select:focus { border-color:rgb(27,68,95); box-shadow:0 0 8px rgba(27,68,95,0.3); outline:none;}
button[type="submit"] { width:100%; padding:14px; background:rgb(27,68,95); border:none; color:white; font-size:1rem; font-weight:600; border-radius:10px; cursor:pointer; transition:background-color 0.3s;}
button[type="submit"]:hover { background:rgb(0,153,255);}
.flex-group { display:flex; gap:10px; margin-bottom:15px;}
.flex-group>div { flex:1;}
.erro { color:#e74c3c; font-size:0.85rem; margin-top:-10px; margin-bottom:10px; display:block;}
@media(max-width:600px){ .flex-group { flex-direction:column; } }
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='fornecedores.php'">&#8592; Voltar</button>
  <h1>Cadastro de Fornecedor</h1>
</header>

<main>
  <form method="POST" id="form-fornecedor" action="">
    <?php echo $msg; ?>

    <h2>Dados da Empresa</h2>
    <label for="nome_forn">Nome da Empresa</label>
    <input type="text" id="nome_forn" name="Nome_forn" maxlength="40" placeholder="Nome da empresa" required />

    <label for="cnpj">CNPJ</label>
    <input type="text" id="cnpj" name="CNPJ" maxlength="18" placeholder="99.999.999/9999-99" required />

    <label for="data_fundacao">Data de Fundação</label>
    <input type="date" id="data_fundacao" name="Data_fundacao" min="1800-01-01" required />

    <h2>Endereço</h2>
    <label for="logradouro">Logradouro</label>
    <input type="text" id="logradouro" name="Logradouro" maxlength="60" placeholder="Rua, Avenida..." required />

    <label for="num_empresa">Número</label>
    <input type="number" id="num_empresa" name="Num_empresa" min="1" max="99999" placeholder="Número" required />

    <label for="bairro">Bairro</label>
    <input type="text" id="bairro" name="Bairro" maxlength="30" placeholder="Bairro" required />

    <label for="cidade">Cidade</label>
    <input type="text" id="cidade" name="Cidade" maxlength="30" placeholder="Cidade" required />

    <label for="uf">UF</label>
    <select id="uf" name="UF" required>
      <option value="">Selecione</option>
      <?php
        $ufs = ["AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG","PA","PB",
                "PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO"];
        foreach($ufs as $u){
            echo "<option value='$u'>$u</option>";
        }
      ?>
    </select>

    <label for="cep">CEP</label>
    <input type="text" id="cep" name="CEP" maxlength="9" placeholder="00000-000" required />

    <h2>Formas de Contato</h2>
    <label for="email">Email</label>
    <input type="email" id="email" name="Email" maxlength="60" placeholder="Digite o e-mail" required />

    <label for="telefone">Telefone ou Celular</label>
    <input type="text" id="telefone" name="Telefone" maxlength="15" placeholder="(00) 00000-0000" />

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
// CNPJ
document.getElementById("cnpj").addEventListener("input", function(e){
    let v = e.target.value.replace(/\D/g,"").slice(0,14);
    v = v.replace(/^(\d{2})(\d)/,"$1.$2");
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");
    v = v.replace(/\.(\d{3})(\d)/,".$1/$2");
    v = v.replace(/(\d{4})(\d)/,"$1-$2");
    e.target.value = v;
});

// CEP - corrige o problema do último dígito
document.getElementById("cep").addEventListener("input", function(e){
    let v = e.target.value.replace(/\D/g,"").slice(0,8); // só 8 dígitos
    if(v.length > 5){
        v = v.replace(/^(\d{5})(\d{1,3})$/,"$1-$2"); // adiciona hífen
    }
    e.target.value = v;
});

// Telefone
document.getElementById("telefone").addEventListener("input", function(e){
    let v = e.target.value.replace(/\D/g,"").slice(0,11);
    if(v.length > 10){
        v = v.replace(/^(\d{2})(\d{5})(\d{4})$/,"($1) $2-$3");
    } else if(v.length > 6){
        v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/,"($1) $2-$3");
    } else if(v.length > 2){
        v = v.replace(/^(\d{2})(\d{0,5})$/,"($1) $2");
    } else {
        v = v.replace(/^(\d*)$/,"($1");
    }
    e.target.value = v;
});
</script>

</body>
</html>
