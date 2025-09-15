<?php 
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit;
}

if ($_SESSION['nivel'] != 1) {
  echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='funcionarios.php';</script>";
  exit;
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
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
  body { background:rgb(59, 75, 93); min-height:100vh; display:flex; flex-direction:column; }
  header { background:rgb(27, 68, 95); padding:15px 20px; color:white; display:flex; align-items:center; gap:15px; box-shadow:0 3px 10px rgba(0,0,0,0.15); }
  header .back-btn { background:transparent; border:none; color:white; cursor:pointer; font-size:24px; }
  header h1 { flex:1; font-weight:700; font-size:1.5rem; user-select:none; }
  main { flex:1; display:flex; justify-content:center; padding:25px 15px; }
  form { background:#fff; padding:30px 35px; border-radius:15px; box-shadow:0 12px 25px rgba(0,0,0,0.12); max-width:600px; width:100%; }
  form h2 { text-align:center; margin-bottom:30px; color:#2c3e50; font-size:1.8rem; }
  label { display:block; margin-bottom:6px; font-weight:600; color:#34495e; }
  input, select { width:100%; padding:12px 15px; margin-bottom:15px; border:1px solid #ccc; border-radius:10px; font-size:0.95rem; transition:all 0.3s ease; }
  input:focus, select:focus { border-color:rgb(27, 68, 95); box-shadow:0 0 8px rgba(52,152,219,0.3); outline:none; }
  button[type="submit"] { width:100%; padding:14px; background:rgb(27, 68, 95); border:none; color:white; font-size:1rem; font-weight:600; border-radius:10px; cursor:pointer; transition:background-color 0.3s; }
  button[type="submit"]:hover { background:rgb(0, 153, 255); }
  .flex-group { display:flex; gap:10px; margin-bottom:15px; }
  .flex-group > div { flex:1; }
  .erro { color:#e74c3c; font-size:0.85rem; margin-top:-10px; margin-bottom:10px; display:block; }
  @media(max-width:600px){ .flex-group { flex-direction:column; } }
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
  <form id="form-funcionario" method="POST" action="cadastros/cadastro_funcionario.php" novalidate>
    <h2>Cadastro de Funcionário</h2>

    <label for="Nome_func">Nome:</label>
    <input type="text" id="Nome_func" name="Nome_func" />
    <span id="erro-Nome_func" class="erro"></span>

    <label for="telefone">Telefone:</label>
    <input type="text" id="telefone" name="Telefone" />
    <span id="erro-telefone" class="erro"></span>

    <label for="Sexo">Sexo:</label>
    <select id="Sexo" name="Sexo">
      <option value="">Selecione</option>
      <option value="Masculino">Masculino</option>
      <option value="Feminino">Feminino</option>
    </select>
    <span id="erro-Sexo" class="erro"></span>

    <label for="rg">RG:</label>
    <input type="text" id="rg" name="RG" />
    <span id="erro-rg" class="erro"></span>

    <label for="cpf">CPF:</label>
    <input type="text" id="cpf" name="CPF" maxlength="15" />
    <span id="erro-cpf" class="erro"></span>

    <label for="Esta_civil">Estado Civil:</label>
    <select id="Esta_civil" name="Esta_civil">
      <option value="">Selecione</option>
      <option>Solteiro</option>
      <option>Casado</option>
      <option>Viúvo</option>
    </select>
    <span id="erro-Esta_civil" class="erro"></span>

    <div class="flex-group">
      <div>
        <label for="UF">UF:</label>
        <input type="text" id="UF" name="UF" maxlength="2" />
        <span id="erro-UF" class="erro"></span>
      </div>
      <div>
        <label for="Num_casa">Número da Casa:</label>
        <input type="number" id="Num_casa" name="Num_casa" />
        <span id="erro-Num_casa" class="erro"></span>
      </div>
    </div>

    <label for="Cidade">Cidade:</label>
    <input type="text" id="Cidade" name="Cidade" />
    <span id="erro-Cidade" class="erro"></span>

    <label for="Bairro">Bairro:</label>
    <input type="text" id="Bairro" name="Bairro" />
    <span id="erro-Bairro" class="erro"></span>

    <label for="cep">CEP:</label>
    <input type="text" id="cep" name="CEP" />
    <span id="erro-cep" class="erro"></span>

    <label for="Logradouro">Logradouro:</label>
    <input type="text" id="Logradouro" name="Logradouro" />
    <span id="erro-Logradouro" class="erro"></span>

    <label for="email">Email:</label>
    <input type="email" id="email" name="Email" />
    <span id="erro-email" class="erro"></span>

    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="Senha" />
    <span id="erro-senha" class="erro"></span>

    <label for="nivel_de_acesso">Nível de Acesso:</label>
    <select id="nivel_de_acesso" name="nivel_de_acesso">
      <option value="">Selecione</option>
      <option value="1">Administrador</option>
      <option value="2">Funcionário</option>
    </select>
    <span id="erro-nivel_de_acesso" class="erro"></span>

    <label for="cargo">Cargo:</label>
    <select id="cargo" name="Cargo">
      <option value="">Selecione</option>
      <option>Gerente</option>
      <option>Padeiro</option>
      <option>Caixa</option>
      <option>Confeiteiro</option>
    </select>
    <span id="erro-cargo" class="erro"></span>

    <div class="flex-group">
      <div>
        <label for="nascimento">Data de Nascimento:</label>
        <input type="text" id="nascimento" name="Data_nascimento" placeholder="dd/mm/aaaa" />
        <span id="erro-nascimento" class="erro"></span>
      </div>
      <div>
        <label for="admissao">Data de Admissão:</label>
        <input type="text" id="admissao" name="Data_admissao" placeholder="dd/mm/aaaa" />
        <span id="erro-admissao" class="erro"></span>
      </div>
    </div>

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const form = document.getElementById("form-funcionario");

  const campos = [
    "Nome_func","telefone","Sexo","rg","cpf","Esta_civil","UF","Num_casa",
    "Cidade","Bairro","cep","Logradouro","email","senha","nivel_de_acesso",
    "cargo","nascimento","admissao"
  ];

  function limparErros(){
    campos.forEach(id=>{
      const el=document.getElementById(id);
      if(el) el.style.borderColor="#ccc";
      const span=document.getElementById("erro-"+id);
      if(span) span.innerText="";
    });
  }

  // Máscaras
  const telefone = document.getElementById("telefone");
  const rg = document.getElementById("rg");
  const cpf = document.getElementById("cpf");
  const cep = document.getElementById("cep");
  const nascimento = document.getElementById("nascimento");
  const admissao = document.getElementById("admissao");

  telefone.addEventListener("input", ()=> {
    let v = telefone.value.replace(/\D/g,"").slice(0,11);
    if(v.length>10) v=v.replace(/^(\d{2})(\d{5})(\d{4})$/,"($1) $2-$3");
    else if(v.length>6) v=v.replace(/^(\d{2})(\d{4})(\d{0,4})$/,"($1) $2-$3");
    else if(v.length>2) v=v.replace(/^(\d{2})(\d{0,5})$/,"($1) $2");
    else v=v.replace(/^(\d*)$/,"($1");
    telefone.value=v;
  });

  rg.addEventListener("input", ()=> {
    let v = rg.value.replace(/\D/g,"").slice(0,9);
    v=v.replace(/(\d{2})(\d)/,"$1.$2").replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d{1,2})$/,"$1-$2");
    rg.value=v;
  });

  cpf.addEventListener("input", ()=> {
    let v = cpf.value.replace(/\D/g,"").slice(0,11);
    v=v.replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d)/,"$1.$2").replace(/(\d{3})(\d{1,2})$/,"$1-$2");
    cpf.value=v;
  });

  cep.addEventListener("input", ()=> {
    let v = cep.value.replace(/\D/g,"").slice(0,8);
    v=v.replace(/(\d{5})(\d)/,"$1-$2");
    cep.value=v;
  });

  function mascaraData(el){
    el.addEventListener("input", ()=>{
      let v=el.value.replace(/\D/g,"").slice(0,8);
      if(v.length>2) v=v.slice(0,2)+'/'+v.slice(2);
      if(v.length>5) v=v.slice(0,5)+'/'+v.slice(5);
      el.value=v;
    });
  }
  mascaraData(nascimento);
  mascaraData(admissao);

  // Validação
  form.addEventListener("submit",(e)=>{
    e.preventDefault();
    limparErros();
    let ok=true;

    campos.forEach(id=>{
      const el=document.getElementById(id);
      const val=el ? el.value.trim() : "";
      if(!val){
        const span=document.getElementById("erro-"+id);
        if(span) span.innerText="Campo obrigatório.";
        if(el) el.style.borderColor="#e74c3c";
        ok=false;
      }
    });

    // validações específicas
    if(telefone.value.replace(/\D/g,"").length<10){
      document.getElementById("erro-telefone").innerText="Telefone inválido."; ok=false;
    }
    if(rg.value.replace(/\D/g,"").length<7){
      document.getElementById("erro-rg").innerText="RG inválido."; ok=false;
    }
    if(cpf.value.replace(/\D/g,"").length!==11){
      document.getElementById("erro-cpf").innerText="CPF inválido."; ok=false;
    }
    if(cep.value.replace(/\D/g,"").length!==8){
      document.getElementById("erro-cep").innerText="CEP inválido."; ok=false;
    }
    const emailRegex=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRegex.test(email.value)){
      document.getElementById("erro-email").innerText="Email inválido."; ok=false;
    }
    if(senha.value.length>0 && senha.value.length<8){
      document.getElementById("erro-senha").innerText="Senha deve ter no mínimo 8 caracteres."; ok=false;
    }

    // validação datas
    function validarData(el,id){
      if(!el.value) return false;
      const parts=el.value.split("/");
      if(parts.length!==3) return false;
      const d=new Date(parts[2],parts[1]-1,parts[0]);
      return !isNaN(d.getTime());
    }

    if(!validarData(nascimento)){
      document.getElementById("erro-nascimento").innerText="Data de nascimento inválida.";
      ok=false;
    } else {
      // idade mínima 18
      const parts=nascimento.value.split("/");
      const nasc=new Date(parts[2],parts[1]-1,parts[0]);
      const hoje=new Date();
      let idade=hoje.getFullYear()-nasc.getFullYear();
      const m=hoje.getMonth()-nasc.getMonth();
      if(m<0||(m===0 && hoje.getDate()<nasc.getDate())) idade--;
      if(idade<18){ document.getElementById("erro-nascimento").innerText="Funcionário deve ter pelo menos 18 anos."; ok=false; }
    }

    if(!validarData(admissao)){
      document.getElementById("erro-admissao").innerText="Data de admissão inválida.";
      ok=false;
    }

    if(ok) form.submit();
    else alert("Por favor, preencha todos os campos destacados antes de enviar.");
  });
});
</script>

</body>
</html>
