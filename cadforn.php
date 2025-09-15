<?php 
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit;
}

if ($_SESSION['nivel'] != 1) {
  echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='fornecedores.php';</script>";
  exit;
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
body { background:rgb(59, 75, 93); min-height:100vh; display:flex; flex-direction:column; }
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
.erro { color:#e74c3c; font-size:0.85rem; margin-top:-10px; margin-bottom:10px; display:block;}
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='fornecedores.php'">&#8592; Voltar</button>
  <h1>Cadastro de Fornecedor</h1>
</header>

<main>
  <form id="form-fornecedor" method="POST" action="cadastros/cadastro_fornecedor.php">
    <h2>Dados da Empresa</h2>
    <label for="nome_forn">Nome da Empresa</label>
    <input type="text" id="nome_forn" name="Nome_forn" maxlength="40" placeholder="Nome da empresa" />
    <span id="erro-nome_forn" class="erro"></span>

    <label for="cnpj">CNPJ</label>
    <input type="text" id="cnpj" name="CNPJ" maxlength="18" placeholder="99.999.999/9999-99" />
    <span id="erro-cnpj" class="erro"></span>

    <label for="data_fundacao">Data de Fundação</label>
    <input type="date" id="data_fundacao" name="Data_fundacao" min="1800-01-01"/>
    <span id="erro-data_fundacao" class="erro"></span>

    <h2>Endereço</h2>
    <label for="logradouro">Logradouro</label>
    <input type="text" id="logradouro" name="Logradouro" maxlength="60" placeholder="Rua, Avenida..." />
    <span id="erro-logradouro" class="erro"></span>

    <label for="num_empresa">Número</label>
    <input type="number" id="num_empresa" name="Num_empresa" min="1" max="99999" placeholder="Número" />
    <span id="erro-num_empresa" class="erro"></span>

    <label for="bairro">Bairro</label>
    <input type="text" id="bairro" name="Bairro" maxlength="30" placeholder="Bairro" />
    <span id="erro-bairro" class="erro"></span>

    <label for="cidade">Cidade</label>
    <input type="text" id="cidade" name="Cidade" maxlength="30" placeholder="Cidade" />
    <span id="erro-cidade" class="erro"></span>

    <label for="uf">UF</label>
    <select id="uf" name="UF">
      <option value="">Selecione</option>
      <?php
        $ufs = ["AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG","PA","PB",
                "PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO"];
        foreach($ufs as $u){
            echo "<option value='$u'>$u</option>";
        }
      ?>
    </select>
    <span id="erro-uf" class="erro"></span>

    <label for="cep">CEP</label>
    <input type="text" id="cep" name="CEP" maxlength="9" placeholder="00000-000" />
    <span id="erro-cep" class="erro"></span>

    <h2>Formas de Contato</h2>
    <label for="email">Email</label>
    <input type="email" id="email" name="Email" maxlength="60" placeholder="Digite o e-mail" />
    <span id="erro-email" class="erro"></span>

    <label for="telefone">Telefone ou Celular</label>
    <input type="text" id="telefone" name="Telefone" maxlength="15" placeholder="(00) 00000-0000" />
    <span id="erro-telefone" class="erro"></span>

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
const form = document.getElementById("form-fornecedor");

function limparErros() {
    document.querySelectorAll(".erro").forEach(span => span.innerText = "");
    document.querySelectorAll("input, select").forEach(campo => campo.style.borderColor="#ccc");
}

// Máscaras
document.getElementById("cnpj").addEventListener("input", e=>{
    let v=e.target.value.replace(/\D/g,"").slice(0,14);
    v=v.replace(/^(\d{2})(\d)/,"$1.$2").replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");
    v=v.replace(/\.(\d{3})(\d)/,".$1/$2").replace(/(\d{4})(\d)/,"$1-$2");
    e.target.value=v;
});
document.getElementById("cep").addEventListener("input", e=>{
    let v=e.target.value.replace(/\D/g,"").slice(0,8);
    if(v.length>5) v=v.replace(/^(\d{5})(\d{1,3})$/,"$1-$2");
    e.target.value=v;
});
document.getElementById("telefone").addEventListener("input", e=>{
    let v=e.target.value.replace(/\D/g,"").slice(0,11);
    if(v.length>10) v=v.replace(/^(\d{2})(\d{5})(\d{4})$/,"($1) $2-$3");
    else if(v.length>6) v=v.replace(/^(\d{2})(\d{4})(\d{0,4})$/,"($1) $2-$3");
    else if(v.length>2) v=v.replace(/^(\d{2})(\d{0,5})$/,"($1) $2");
    else v=v.replace(/^(\d*)$/,"($1");
    e.target.value=v;
});

// Validação
form.addEventListener("submit", e=>{
    e.preventDefault();
    limparErros();
    let ok=true;

    const campos=[
        {id:"nome_forn", tipo:"texto"},
        {id:"cnpj", tipo:"cnpj"},
        {id:"data_fundacao", tipo:"data"},
        {id:"logradouro", tipo:"texto"},
        {id:"num_empresa", tipo:"numero"},
        {id:"bairro", tipo:"texto"},
        {id:"cidade", tipo:"texto"},
        {id:"uf", tipo:"select"},
        {id:"cep", tipo:"cep"},
        {id:"email", tipo:"email"},
        {id:"telefone", tipo:"telefone"}
    ];

    campos.forEach(c=>{
        const el=document.getElementById(c.id);
        const val=el.value.trim();

        if(!val) {
            document.getElementById("erro-"+c.id).innerText="Campo obrigatório.";
            el.style.borderColor="#e74c3c";
            ok=false;
            return;
        }

        if(c.tipo==="cnpj" && val.replace(/\D/g,"").length!==14){
            document.getElementById("erro-"+c.id).innerText="CNPJ inválido.";
            el.style.borderColor="#e74c3c";
            ok=false;
        }

        if(c.tipo==="cep" && val.replace(/\D/g,"").length!==8){
            document.getElementById("erro-"+c.id).innerText="CEP inválido.";
            el.style.borderColor="#e74c3c";
            ok=false;
        }

        if(c.tipo==="email"){
            const regex=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!regex.test(val)){
                document.getElementById("erro-"+c.id).innerText="Email inválido.";
                el.style.borderColor="#e74c3c";
                ok=false;
            }
        }
    });

    if(!ok){
        alert("Por favor, preencha todos os campos destacados antes de enviar.");
        return;
    }

    form.submit();
});

const form = document.getElementById("form-fornecedor");
const dataInput = document.getElementById("data_fundacao");

form.addEventListener("submit", function(e) {
    const valor = dataInput.value;
    if (!valor) return; // deixa o required cuidar de vazio

    const [ano, mes, dia] = valor.split("-").map(Number);

    // Ano inválido
    if (ano < 1000 || ano > 9999) {
        alert("Ano inválido (deve ter 4 dígitos).");
        dataInput.focus();
        e.preventDefault();
        return;
    }

    // Mês inválido
    if (mes < 1 || mes > 12) {
        alert("Mês inválido (deve ser entre 01 e 12).");
        dataInput.focus();
        e.preventDefault();
        return;
    }

    // Último dia do mês (considera fevereiro bissexto)
    const ultimoDia = new Date(ano, mes, 0).getDate();

    // Dia inválido
    if (dia < 1 || dia > ultimoDia) {
        alert(`Dia inválido (esse mês vai até ${ultimoDia}).`);
        dataInput.focus();
        e.preventDefault();
        return;
    }
});

</script>
</body>
</html>
