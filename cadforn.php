<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Fornecedor</title>
  <link rel="stylesheet" href="css/cadforn.css">
</head>
<body>

  <div class="container">
    <button onclick="window.location.href='fornecedores.php'">
      <span class="material-icons">arrow_back</span> Voltar
    </button>

    <h1>Cadastro de Fornecedor</h1>

    <form id="form-fornecedor" novalidate>
      <h2>Dados da Empresa</h2>

      <label for="empresa">Nome da Empresa</label>
      <input type="text" id="empresa" name="empresa" placeholder="Nome da empresa" required>

      <label for="cnpj">CNPJ</label>
      <input type="text" id="cnpj" name="cnpj" placeholder="99.999.999/9999-99" required>

      <label for="fundacao">Data de Fundação</label>
      <input type="date" id="fundacao" name="fundacao" min="1800-01-01" max="<?=date('Y-m-d')?>" required>

      <label for="endereco">Endereço</label>
      <input type="text" id="endereco" name="endereco" placeholder="Rua ..., Nº, Bairro, Cidade" required>

      <h2>Formas de Contato</h2>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Digite o e-mail" required>

      <label for="telefone">Telefone</label>
      <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>

      <button type="submit">Cadastrar</button>
    </form>
  </div>

  <div id="erro-modal" style="display:none;">
    <strong>Erro no cadastro</strong>
    <ul id="erro-lista"></ul>
    <button onclick="document.getElementById('erro-modal').style.display='none'">Fechar</button>
  </div>

  <script>
    // Bloqueio de acesso
    if(localStorage.getItem("nivel_usuario") !== "admin") {
      alert("Acesso restrito! Apenas administradores podem acessar esta página.");
      window.location.href = "inicial1.php";
    }

    // Máscaras
    Inputmask({"mask":"99.999.999/9999-99"}).mask(document.getElementById("cnpj"));
    Inputmask({"mask":"(99) 99999-9999"}).mask(document.getElementById("telefone"));

    // Validação de CNPJ
    function validarCNPJ(cnpj) {
      cnpj = cnpj.replace(/[^\d]+/g,'');
      if(cnpj.length!==14 || /^(\d)\1+$/.test(cnpj)) return false;
      let tamanho=12,numeros=cnpj.substring(0,tamanho),digitos=cnpj.substring(tamanho),soma=0,pos=tamanho-7;
      for(let i=tamanho;i>=1;i--){soma+=parseInt(numeros.charAt(tamanho-i))*pos--;if(pos<2) pos=9;}
      let resultado=soma%11<2?0:11-(soma%11); if(resultado!==parseInt(digitos.charAt(0))) return false;
      tamanho+=1;numeros=cnpj.substring(0,tamanho);soma=0;pos=tamanho-7;
      for(let i=tamanho;i>=1;i--){soma+=parseInt(numeros.charAt(tamanho-i))*pos--;if(pos<2) pos=9;}
      resultado=soma%11<2?0:11-(soma%11); return resultado===parseInt(digitos.charAt(1));
    }

    // Validação geral do formulário
    document.getElementById("form-fornecedor").addEventListener("submit", function(e){
      let erros=[];

      const empresa=document.getElementById("empresa").value.trim();
      if(empresa.length<3 || empresa.length>100) erros.push("O nome da empresa deve ter entre 3 e 100 caracteres.");

      const endereco=document.getElementById("endereco").value.trim();
      if(endereco.length<10 || endereco.length>200) erros.push("O endereço deve ter entre 10 e 200 caracteres.");

      const email=document.getElementById("email").value.trim();
      const emailRegex=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if(!emailRegex.test(email)) erros.push("E-mail inválido.");

      const telefone=document.getElementById("telefone").value.replace(/\D/g,'');
      if(telefone.length<10 || telefone.length>11) erros.push("Telefone inválido.");

      const cnpj=document.getElementById("cnpj").value;
      if(!validarCNPJ(cnpj)) erros.push("CNPJ inválido.");

      const fundacao=document.getElementById("fundacao").value;
      if(!fundacao || fundacao<"1800-01-01" || fundacao>new Date().toISOString().split("T")[0])
        erros.push("Data de fundação inválida.");

      if(erros.length>0){
        e.preventDefault();
        const lista=document.getElementById("erro-lista");
        lista.innerHTML=erros.map(msg=>`<li>${msg}</li>`).join('');
        document.getElementById("erro-modal").style.display="block";
      }
    });
  </script>

</body>
</html>
