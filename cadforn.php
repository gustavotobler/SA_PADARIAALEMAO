<?php
// Conexão com o banco
$mysqli = new mysqli("localhost", "root", "", "padariadoalemao");
if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recebendo dados do formulário
    $nome_forn    = trim($_POST['empresa']);
    $tipo         = trim($_POST['tipo']);
    $logradouro   = trim($_POST['logradouro']);
    $num_empresa  = intval($_POST['num_empresa']);
    $bairro       = trim($_POST['bairro']);
    $cidade       = trim($_POST['cidade']);
    $uf           = trim($_POST['uf']);
    $cep          = preg_replace("/\D/", "", $_POST['cep']);
    $telefone     = preg_replace("/\D/", "", $_POST['telefone']);
    $email        = trim($_POST['email']);
    $cnpj         = preg_replace("/\D/", "", $_POST['cnpj']);
    $data_fundacao= $_POST['fundacao'];

    // Validações PHP
    if(empty($nome_forn) || strlen($nome_forn)<3) die("Erro: Nome da empresa inválido.");
    if(empty($tipo)) die("Erro: Tipo de fornecedor obrigatório.");
    if(empty($logradouro)) die("Erro: Logradouro obrigatório.");
    if($num_empresa <= 0) die("Erro: Número da empresa inválido.");
    if(empty($bairro)) die("Erro: Bairro obrigatório.");
    if(empty($cidade)) die("Erro: Cidade obrigatória.");
    if(empty($uf)) die("Erro: UF obrigatório.");
    if(strlen($cep) != 8) die("Erro: CEP inválido.");
    if(strlen($telefone)<10) die("Erro: Telefone inválido.");
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) die("Erro: Email inválido.");
    if(empty($cnpj)) die("Erro: CNPJ obrigatório.");

    // Checa duplicidade de CNPJ
    $check = $mysqli->prepare("SELECT ID_forn FROM fornecedores WHERE CNPJ = ?");
    $check->bind_param("s", $cnpj);
    $check->execute();
    $check->store_result();
    if($check->num_rows > 0){
        die("Erro: CNPJ já cadastrado!");
    }
    $check->close();

    // Inserção no banco
    $stmt = $mysqli->prepare("INSERT INTO fornecedores 
        (nome_forn, Tipo, Logradouro, Num_empresa, Bairro, Cidade, UF, CEP, Telefone, Email, CNPJ, Data_fundacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssisssssss",
        $nome_forn, $tipo, $logradouro, $num_empresa, $bairro, $cidade, $uf, $cep, $telefone, $email, $cnpj, $data_fundacao
    );

    if ($stmt->execute()) {
        $msg = "Fornecedor cadastrado com sucesso!";
    } else {
        $msg = "Erro ao cadastrar fornecedor: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro de Fornecedor</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
body { background: linear-gradient(135deg,#fff,#e9d2b4); display:flex; justify-content:center; align-items:center; min-height:100vh; }
.container { background:#fff; padding:40px 35px; border-radius:15px; box-shadow:0 15px 40px rgba(0,0,0,0.1); width:100%; max-width:500px; transition: transform 0.3s; }
.container:hover { transform: translateY(-5px); }
.container>button { background:#2196f3; color:#fff; border:none; padding:10px 18px; border-radius:10px; cursor:pointer; font-weight:500; margin-bottom:25px; transition:0.3s; }
.container>button:hover { background:#1976d2; transform:scale(1.05); }
h1{ text-align:center; margin-bottom:30px; color:#333; font-size:1.8rem; }
h2{ margin-top:25px; margin-bottom:15px; color:#555; font-size:1.2rem; border-bottom:1px solid #e0e0e0; padding-bottom:5px; }
form{ display:flex; flex-direction:column; gap:18px; }
label{ font-weight:500; margin-bottom:5px; color:#333; }
input, select{ padding:12px 15px; border:1px solid #ccc; border-radius:12px; outline:none; transition: all 0.3s; font-size:0.95rem; }
input:focus, select:focus{ border-color:#2196f3; box-shadow:0 0 8px rgba(33,150,243,0.3); }
form button[type="submit"]{ margin-top:10px; padding:12px; background:#2196f3; color:#fff; border:none; border-radius:12px; font-size:1rem; cursor:pointer; font-weight:500; transition:0.3s; }
form button[type="submit"]:hover{ background:#1976d2; transform:translateY(-2px); box-shadow:0 5px 15px rgba(33,150,243,0.3); }
.msg{ margin:10px 0; padding:10px; background:#d4edda; color:#155724; border-radius:8px; text-align:center; }
</style>
</head>
<body>

<div class="container">
<button onclick="window.location.href='fornecedores.php'">Voltar</button>

<h1>Cadastro de Fornecedor</h1>

<?php if($msg): ?>
<div class="msg"><?php echo $msg; ?></div>
<?php endif; ?>

<form method="POST" id="form-fornecedor">
<h2>Dados da Empresa</h2>
<label for="empresa">Nome da Empresa</label>
<input type="text" id="empresa" name="empresa" maxlength="40" placeholder="Nome da empresa" required>

<label for="cnpj">CNPJ</label>
<input type="text" id="cnpj" name="cnpj" placeholder="99.999.999/9999-99" required>

<label for="fundacao">Data de Fundação</label>
<input type="date" id="fundacao" name="fundacao" min="1800-01-01" required>

<label for="tipo">Tipo de Fornecedor</label>
<input type="text" id="tipo" name="tipo" maxlength="30" placeholder="Categoria ou tipo" required>

<h2>Endereço</h2>
<label for="logradouro">Logradouro</label>
<input type="text" id="logradouro" name="logradouro" maxlength="60" placeholder="Rua, Avenida..." required>

<label for="num_empresa">Número</label>
<input type="number" id="num_empresa" name="num_empresa" placeholder="Número" required>

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

<label for="telefone">Telefone</label>
<input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>

<button type="submit">Cadastrar</button>
</form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
<script>
Inputmask({"mask":"99.999.999/9999-99"}).mask(document.getElementById("cnpj"));
Inputmask({"mask":"(99) 99999-9999"}).mask(document.getElementById("telefone"));
Inputmask({"mask":"99999-999"}).mask(document.getElementById("cep"));
</script>

</body>
</html>
