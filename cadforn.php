<?php
session_start();
require_once("conexao.php"); 

function validarCNPJ($cnpj){
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if(strlen($cnpj) != 14 || preg_match('/^(\d)\1+$/', $cnpj)) return false;

    $numeros = substr($cnpj, 0, 12);
    $digitos = substr($cnpj, 12);

    $soma = 0; $pos = 5;
    for($i=0;$i<12;$i++){
        $soma += $numeros[$i]*$pos;
        $pos--; if($pos<2) $pos=9;
    }
    $resultado = ($soma%11<2)?0:11-($soma%11);
    if($resultado != $digitos[0]) return false;

    $numeros = substr($cnpj,0,13);
    $soma = 0; $pos=6;
    for($i=0;$i<13;$i++){
        $soma += $numeros[$i]*$pos;
        $pos--; if($pos<2) $pos=9;
    }
    $resultado = ($soma%11<2)?0:11-($soma%11);
    return $resultado == $digitos[1];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_forn    = trim($_POST["nome_forn"]);
    $telefone     = preg_replace("/\D/", "", $_POST["telefone"]);
    $cnpj         = preg_replace("/\D/", "", $_POST["cnpj"]);
    $uf           = strtoupper(trim($_POST["uf"]));
    $cidade       = trim($_POST["cidade"]);
    $bairro       = trim($_POST["bairro"]);
    $tipo         = trim($_POST["tipo"]);
    $cep          = preg_replace("/\D/", "", $_POST["cep"]);
    $num_empresa  = (int)$_POST["num_empresa"];
    $logradouro   = trim($_POST["logradouro"]);
    $email        = trim($_POST["email"]);
    $data_fundacao= $_POST["data_fundacao"];

    if(
        !empty($nome_forn) && !empty($telefone) && !empty($cnpj) &&
        !empty($uf) && !empty($cidade) && !empty($bairro) && !empty($tipo) &&
        !empty($cep) && $num_empresa > 0 && !empty($logradouro) &&
        !empty($email) && !empty($data_fundacao)
    ){
        if(!validarCNPJ($cnpj)){
            echo "<script>alert('CNPJ inválido!');history.back();</script>";
            exit;
        }

        try{
            // Checa CNPJ
            $check = $pdo->prepare("SELECT ID_forn FROM fornecedores WHERE CNPJ = :cnpj");
            $check->bindParam(':cnpj', $cnpj, PDO::PARAM_STR);
            $check->execute();
            if($check->rowCount()>0){
                echo "<script>alert('Erro: CNPJ já cadastrado!');history.back();</script>";
                exit;
            }

            // Checa Email
            $checkEmail = $pdo->prepare("SELECT ID_forn FROM fornecedores WHERE Email = :email");
            $checkEmail->bindParam(':email', $email, PDO::PARAM_STR);
            $checkEmail->execute();
            if($checkEmail->rowCount()>0){
                echo "<script>alert('Erro: Email já cadastrado!');history.back();</script>";
                exit;
            }

            // Inserção
            $sql = "INSERT INTO fornecedores 
                    (Nome_forn, Telefone, CNPJ, UF, Cidade, Bairro, Tipo, CEP, Num_empresa, Logradouro, Email, Data_fundacao) 
                    VALUES 
                    (:nome_forn, :telefone, :cnpj, :uf, :cidade, :bairro, :tipo, :cep, :num_empresa, :logradouro, :email, :data_fundacao)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome_forn',$nome_forn);
            $stmt->bindParam(':telefone',$telefone);
            $stmt->bindParam(':cnpj',$cnpj);
            $stmt->bindParam(':uf',$uf);
            $stmt->bindParam(':cidade',$cidade);
            $stmt->bindParam(':bairro',$bairro);
            $stmt->bindParam(':tipo',$tipo);
            $stmt->bindParam(':cep',$cep);
            $stmt->bindParam(':num_empresa',$num_empresa, PDO::PARAM_INT);
            $stmt->bindParam(':logradouro',$logradouro);
            $stmt->bindParam(':email',$email);
            $stmt->bindParam(':data_fundacao',$data_fundacao);

            if($stmt->execute()){
                echo "<script>alert('Fornecedor cadastrado com sucesso!');window.location.href='buscar_fornecedor.php'</script>";
            }else{
                echo "<script>alert('Erro ao cadastrar fornecedor!');</script>";
            }
        }catch(PDOException $e){
            echo "<script>alert('Erro no banco: ".$e->getMessage()."');</script>";
        }

    }else{
        echo "<script>alert('Preencha todos os campos corretamente!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro de Fornecedor</title>
<style>
<<<<<<< Updated upstream
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{background:linear-gradient(135deg,#fff,#e9d2b4);display:flex;justify-content:center;align-items:center;min-height:100vh;}
.container{background:#fff;padding:40px 35px;border-radius:15px;box-shadow:0 15px 40px rgba(0,0,0,0.1);width:100%;max-width:500px;}
.container>button{background:#2196f3;color:#fff;border:none;padding:10px 18px;border-radius:10px;cursor:pointer;font-weight:500;margin-bottom:25px;transition:0.3s;}
.container>button:hover{background:#1976d2;transform:scale(1.05);}
h1{text-align:center;margin-bottom:30px;color:#333;font-size:1.8rem;}
h2{margin-top:25px;margin-bottom:15px;color:#555;font-size:1.2rem;border-bottom:1px solid #e0e0e0;padding-bottom:5px;}
form{display:flex;flex-direction:column;gap:18px;}
label{font-weight:500;margin-bottom:5px;color:#333;}
input,select{padding:12px 15px;border:1px solid #ccc;border-radius:12px;font-size:0.95rem;outline:none;transition:all 0.3s;}
input:focus,select:focus{border-color:#2196f3;box-shadow:0 0 8px rgba(33,150,243,0.3);}
form button[type="submit"]{margin-top:10px;padding:12px;background:#2196f3;color:#fff;border:none;border-radius:12px;font-size:1rem;cursor:pointer;font-weight:500;transition:0.3s;}
form button[type="submit"]:hover{background:#1976d2;transform:translateY(-2px);box-shadow:0 5px 15px rgba(33,150,243,0.3);}
=======

* { 
  margin:0; 
  padding:0; 
  box-sizing:border-box; 
  font-family:'Poppins', sans-serif; 
}
body {
  background: linear-gradient(135deg,#fff,#e9d2b4); 
  display:flex; 
  justify-content:center; 
  align-items:center; 
  min-height:100vh; 
}
.container { 
  background:#fff; 
  padding:40px 35px; 
  border-radius:15px; 
  box-shadow:0 15px 40px rgba(0,0,0,0.1);
  width:100%;
  max-width:500px; 
  transition: transform 0.3s;
 }
.container:hover { 
  transform: translateY(-5px); 
}
.container>button {
   background:#2196f3; 
   color:#fff; 
   border:none; 
   padding:10px 18px; 
   border-radius:10px; 
   cursor:pointer; 
   font-weight:500; 
   margin-bottom:25px; 
   transition:0.3s; 
  }
.container>button:hover { 
  background:#1976d2; 
  transform:scale(1.05); 
}
h1{ 
  text-align:center; 
  margin-bottom:30px; 
  color:#333; 
  font-size:1.8rem; 
}
h2{ 
  margin-top:25px; 
  margin-bottom:15px; 
  color:#555; 
  font-size:1.2rem; 
  border-bottom:1px solid #e0e0e0; 
  padding-bottom:5px; 
}
form{ 
  display:flex; 
  flex-direction:column; 
  gap:18px; 
}
label{ 
  font-weight:500; 
  margin-bottom:5px; 
  color:#333; 
}
input, select{ 
  padding:12px 15px; 
  border:1px solid #ccc; 
  border-radius:12px; 
  outline:none; 
  transition: all 0.3s; 
  font-size:0.95rem; 
}
input:focus, select:focus{ 
  border-color:#2196f3;
  box-shadow:0 0 8px rgba(33,150,243,0.3); 
}
form button[type="submit"]{ 
  margin-top:10px; 
  padding:12px; 
  background:#2196f3; 
  color:#fff; 
  border:none; 
  border-radius:12px; 
  font-size:1rem; 
  cursor:pointer; 
  font-weight:500; 
  transition:0.3s; 
}
form button[type="submit"]:hover{ 
  background:#1976d2; 
  transform:translateY(-2px); 
  box-shadow:0 5px 15px rgba(33,150,243,0.3); 
}
.msg{ 
  margin:10px 0; 
  padding:10px; 
  background:#d4edda; 
  color:#155724; 
  border-radius:8px; 
  text-align:center; 
}
.msg {
  margin:10px 0;
  padding:10px;
  border-radius:8px;
  text-align:center;
  font-weight:500;
}
.msg.sucesso {
  background:#d4edda;
  color:#155724;
}
.msg.erro {
  background:#f8d7da;
  color:#721c24;
}

=======
* {
   margin:0; 
   padding:0; 
   box-sizing:border-box; 
   font-family:'Poppins',
   sans-serif;
  }
body {
  background: linear-gradient(135deg,#fff,#e9d2b4);
  display:flex; 
  justify-content:center; 
  align-items:center; 
  min-height:100vh;
}
.container {
  background:#fff; 
  padding:40px 35px; 
  border-radius:15px; 
  box-shadow:0 15px 40px rgba(0,0,0,0.1); 
  width:100%; 
  max-width:500px; 
  transition: transform 0.3s;
}
.container:hover {
  transform: translateY(-5px);
}
.container>button {
  background:#2196f3;
  color:#fff;
  border:none;
  padding:10px 18px;
  border-radius:10px;
  cursor:pointer;
  font-weight:500;
  margin-bottom:25px; 
  transition: 0.3s;
}
.container>button:hover
{background:#1976d2; 
  transform:scale(1.05);
}
h1{
  text-align:center;
  margin-bottom:30px;
  color:#333;
  font-size:1.8rem;
}
h2{
  margin-top:25px;
  margin-bottom:15px;
  color:#555;
  font-size:1.2rem;
  border-bottom:1px solid #e0e0e0;
  padding-bottom:5px;
}
form{
  display:flex;
  flex-direction:column
  ;gap:18px;
}
label{
  font-weight:500;
  margin-bottom:5px;
  color:#333;
}
input{
  padding:12px 15px;
  border:1px solid #ccc;
  border-radius:12px;
  outline:none
  ;transition: all 0.3s;
  font-size:0.95rem;
}
input:focus{
  border-color:#2196f3;
  box-shadow:0 0 8px rgba(33,150,243,0.3);
}
form button[type="submit"]{
  margin-top:10px;
  padding:12px;
  background:#2196f3;
  color:#fff;
  border:none;
  border-radius:12px;
  font-size:1rem;
  cursor:pointer;
  font-weight:500;
  transition:0.3s;
}
form button[type="submit"]:hover{
  background:#1976d2;
  transform:translateY(-2px);
  box-shadow:0 5px 15px rgba(33,150,243,0.3);
  }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
</style>
</head>
<body>

<div class="container">
<button onclick="window.location.href='fornecedores.php'">Voltar</button>
<h1>Cadastro de Fornecedor</h1>

<<<<<<< Updated upstream
<form method="POST" id="form-fornecedor">
=======
<?php if (!empty($msg)): ?>
  <div class="msg"><?php echo $msg; ?></div>
<?php endif; ?>


<form method="POST" id="form-fornecedor" action="cadforn.php">
  <h2>Dados da Empresa</h2>
  <label for="nome_forn">Nome da Empresa</label>
  <input type="text" id="nome_forn" name="nome_forn" maxlength="40" placeholder="Nome da empresa" required>

  <label for="cnpj">CNPJ</label>
  <input type="text" id="cnpj" name="cnpj" maxlength="18" placeholder="99.999.999/9999-99" required>

  <label for="data_fundacao">Data de Fundação</label>
  <input type="date" id="data_fundacao" name="data_fundacao" min="1800-01-01" required>

  <label for="tipo">Tipo de Fornecedor</label>
  <input type="text" id="tipo" name="tipo" maxlength="30" placeholder="Categoria ou tipo" required>

  <h2>Endereço</h2>
  <label for="logradouro">Logradouro</label>
  <input type="text" id="logradouro" name="logradouro" maxlength="60" placeholder="Rua, Avenida..." required>

  <label for="num_empresa">Número</label>
  <input type="text" id="num_empresa" name="num_empresa" maxlength="5" placeholder="Número" required>

  <label for="bairro">Bairro</label>
  <input type="text" id="bairro" name="bairro" maxlength="30" placeholder="Bairro" required>

  <label for="cidade">Cidade</label>
  <input type="text" id="cidade" name="cidade" maxlength="30" placeholder="Cidade" required>

  <label for="uf">UF</label>
  <select id="uf" name="uf" required>
    <option value="">Selecione</option>
    <option>AC</option><option>AL</option><option>AP</option><option>AM</option><option>BA</option>
    <option>CE</option><option>DF</option><option>ES</option><option>GO</option><option>MA</option>
    <option>MT</option><option>MS</option><option>MG</option><option>PA</option><option>PB</option>
    <option>PR</option><option>PE</option><option>PI</option><option>RJ</option><option>RN</option>
    <option>RS</option><option>RO</option><option>RR</option><option>SC</option><option>SP</option>
    <option>SE</option><option>TO</option>
  </select>

  <label for="cep">CEP</label>
  <input type="text" id="cep" name="cep" maxlength="9" oninput="mascaraCEP(this)" placeholder="00000-000" required>

  <h2>Formas de Contato</h2>
  <label for="email">Email</label>
  <input type="email" id="email" name="email" maxlength="60" placeholder="Digite o e-mail" required>

<form id="form-fornecedor">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
<h2>Dados da Empresa</h2>
<label for="nome_forn">Nome da Empresa</label>
<input type="text" id="nome_forn" name="nome_forn" maxlength="40" placeholder="Nome da empresa" required>

<label for="cnpj">CNPJ</label>
<input type="text" id="cnpj" name="cnpj" maxlength="18" placeholder="99.999.999/9999-99" required>

<label for="data_fundacao">Data de Fundação</label>
<input type="date" id="data_fundacao" name="data_fundacao" min="1800-01-01" required>

<label for="tipo">Tipo de Fornecedor</label>
<input type="text" id="tipo" name="tipo" maxlength="30" placeholder="Categoria ou tipo" required>

<h2>Endereço</h2>
<label for="logradouro">Logradouro</label>
<input type="text" id="logradouro" name="logradouro" maxlength="60" placeholder="Rua, Avenida..." required>

<label for="num_empresa">Número</label>
<input type="number" id="num_empresa" name="num_empresa" min="1" max="99999" placeholder="Número" required>

<label for="bairro">Bairro</label>
<input type="text" id="bairro" name="bairro" maxlength="30" placeholder="Bairro" required>

<label for="cidade">Cidade</label>
<input type="text" id="cidade" name="cidade" maxlength="30" placeholder="Cidade" required>

<label for="uf">UF</label>
<select id="uf" name="uf" required>
<option value="">Selecione</option>
<option>AC</option><option>AL</option><option>AP</option><option>AM</option><option>BA</option>
<option>CE</option><option>DF</option><option>ES</option><option>GO</option><option>MA</option>
<option>MT</option><option>MS</option><option>MG</option><option>PA</option><option>PB</option>
<option>PR</option><option>PE</option><option>PI</option><option>RJ</option><option>RN</option>
<option>RS</option><option>RO</option><option>RR</option><option>SC</option><option>SP</option>
<option>SE</option><option>TO</option>
</select>

<label for="cep">CEP</label>
<input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" required>

<h2>Formas de Contato</h2>
<label for="email">Email</label>
<input type="email" id="email" name="email" maxlength="60" placeholder="Digite o e-mail" required>

<label for="telefone">Telefone ou Celular</label>
<input type="text" id="telefone" name="telefone" placeholder="(11) 90000-9999" required>

<<<<<<< Updated upstream
<button type="submit">Cadastrar</button>
=======
<div class="uk-margin">
    <label> <strong>Telefone ou Celular</strong>
      <input class="uk-input sp_celphones" type="text" name="telefone" placeholder="Ex: (11) 90000-9999">
    </label>
</div>

  <button type="submit">Cadastrar</button>
>>>>>>> Stashed changes
</form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
<script>
// Máscaras
Inputmask({"mask":"99.999.999/9999-99"}).mask(document.getElementById("cnpj"));
Inputmask({"mask":"(99) 99999-9999"}).mask(document.getElementById("telefone"));
Inputmask({"mask":"99999-999"}).mask(document.getElementById("cep"));

// Validação de CNPJ antes de enviar
document.getElementById("form-fornecedor").addEventListener("submit", function(e){
    const cnpj = document.getElementById("cnpj").value.replace(/\D/g,'');
    if(cnpj.length != 14){
        e.preventDefault();
        alert("CNPJ inválido!");
        document.getElementById("cnpj").focus();
    }
});
<<<<<<< Updated upstream
=======
  
$(document).ready(function(){
    var SPMaskBehavior = function (val) {
      return val.replace(/\D/g, '').length === 13 ? '+55 (00) 00000-0000' : '+55 (00) 0000-00009';
    },
    spOptions = {
      onKeyPress: function(val, e, field, options) {
          field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };

    $('.sp_celphones').mask(SPMaskBehavior, spOptions);
});

document.getElementById('cnpj').addEventListener('input', function(e) {
	var value = e.target.value;
	var rawValue = value.replace(/\D/g, ''); // Remove tudo que não é número

	// Verifica se o CNPJ tem 15 dígitos e se o primeiro dígito é '0'
	if (rawValue.length === 15 && rawValue.startsWith('0')) {
		// Verifica se, ao remover o '0', o restante é um CNPJ válido
		var potentialCNPJ = rawValue.substring(1);
		// Atualiza rawValue para o CNPJ sem o '0' inicial
		if (validaCNPJ(potentialCNPJ)) rawValue = potentialCNPJ;
	}

	var cnpjPattern = rawValue
					.replace(/^(\d{2})(\d)/, '$1.$2') // Adiciona ponto após o segundo dígito
					.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3') // Adiciona ponto após o quinto dígito
					.replace(/\.(\d{3})(\d)/, '.$1/$2') // Adiciona barra após o oitavo dígito
					.replace(/(\d{4})(\d)/, '$1-$2') // Adiciona traço após o décimo segundo dígito
					.replace(/(-\d{2})\d+?$/, '$1'); // Impede a entrada de mais de 14 dígitos
	e.target.value = cnpjPattern;
});

function mascaraCEP(input) {
    const valor = input.value.replace(/\D/g, '');  // Remove caracteres não numéricos
    input.value = valor.replace(/^(\d{5})(\d)/, '$1-$2');  // Adiciona o hífen após o quinto dígito
}
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
</script>

</body>
</html>
