<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro de Fornecedor</title>
<style>
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
</style>
</head>
<body>

<div class="container">
<button onclick="window.location.href='fornecedores.php'">Voltar</button>

<h1>Cadastro de Fornecedor</h1>

<form id="form-fornecedor">
<h2>Dados da Empresa</h2>
<label for="empresa">Nome da Empresa</label>
<input type="text" id="empresa" name="empresa" placeholder="Nome da empresa">

<label for="cnpj">CNPJ</label>
<input type="text" id="cnpj" name="cnpj" placeholder="99.999.999/9999-99">

<label for="fundacao">Data de Fundação</label>
<input type="date" id="fundacao" name="fundacao" min="1800-01-01">

<label for="endereco">Endereço</label>
<input type="text" id="endereco" name="endereco" placeholder="Rua ..., Nº, Bairro, Cidade">

<h2>Formas de Contato</h2>
<label for="email">Email</label>
<input type="email" id="email" name="email" placeholder="Digite o e-mail">

<label for="telefone">Telefone</label>
<input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000">

<button type="submit">Cadastrar</button>
</form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>
<script>
// Máscaras
Inputmask({"mask":"99.999.999/9999-99"}).mask(document.getElementById("cnpj"));
Inputmask({"mask":"(99) 99999-9999"}).mask(document.getElementById("telefone"));

// Validação CNPJ
function validarCNPJ(cnpj){
  cnpj=cnpj.replace(/\D/g,'');
  if(cnpj.length!=14 || /^(\d)\1+$/.test(cnpj)) return false;
  let numeros=cnpj.substring(0,12);
  let digitos=cnpj.substring(12);
  let soma=0,pos=5;
  for(let i=0;i<12;i++){soma+=parseInt(numeros[i])*pos; pos--; if(pos<2) pos=9;}
  let resultado=soma%11<2?0:11-(soma%11);
  if(resultado!=parseInt(digitos[0])) return false;
  numeros=cnpj.substring(0,13);
  soma=0; pos=6;
  for(let i=0;i<13;i++){soma+=parseInt(numeros[i])*pos; pos--; if(pos<2) pos=9;}
  resultado=soma%11<2?0:11-(soma%11);
  return resultado==parseInt(digitos[1]);
}

// Validação via JS
document.getElementById("form-fornecedor").addEventListener("submit",function(e){
  e.preventDefault(); // Sempre previne para checar JS

  const empresa=document.getElementById("empresa");
  if(!empresa.value.trim() || empresa.value.trim().length<3 || empresa.value.trim().length>100){
    alert("O nome da empresa deve ter entre 3 e 100 caracteres.");
    empresa.focus(); return;
  }

  const endereco=document.getElementById("endereco");
  if(!endereco.value.trim() || endereco.value.trim().length<10 || endereco.value.trim().length>200){
    alert("O endereço deve ter entre 10 e 200 caracteres.");
    endereco.focus(); return;
  }

  const email=document.getElementById("email");
  const emailRegex=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if(!email.value.trim() || !emailRegex.test(email.value.trim())){
    alert("Email inválido.");
    email.focus(); return;
  }

  const telefone=document.getElementById("telefone");
  const telNum=telefone.value.replace(/\D/g,'');
  if(!telNum || telNum.length<10 || telNum.length>11){
    alert("Telefone inválido.");
    telefone.focus(); return;
  }

  const cnpj=document.getElementById("cnpj");
  if(!cnpj.value.trim() || !validarCNPJ(cnpj.value)){
    alert("CNPJ inválido.");
    cnpj.focus(); return;
  }

  const fundacao=document.getElementById("fundacao");
  const hoje=new Date().toISOString().split("T")[0];
  if(!fundacao.value || fundacao.value<"1800-01-01" || fundacao.value>hoje){
    alert("Data de fundação inválida.");
    fundacao.focus(); return;
  }

  // Todos válidos
  alert("Formulário válido! Pode enviar via AJAX ou POST.");
  // Para enviar realmente: this.submit();
});
</script>

</body>
</html>
