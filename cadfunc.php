<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Funcionário</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/cadfunc.css" rel="stylesheet"/>
  
</head>
<body>
  <div class="container">
    <div class="header">
      <a href="funcionarios.html">
        <button class="back-button"><span class="material-icons">arrow_back</span></button>
      </a>
      <h1>CADASTRO DE FUNCIONÁRIO</h1>
    </div>

    <div class="section-title">DADOS PESSOAIS</div>

    <form id="cadastro-funcionario" class="form-box">
      <div class="form-group">
        <input type="text" id="nome" name="nome" placeholder="Nome completo" required>
        <input type="text" id="rg" name="rg" placeholder="RG" oninput="formatRG(this)" maxlength="12" required>
        <select id="sexo" name="sexo" required>
          <option value="" disabled selected>Sexo</option>
          <option value="MASCULINO">Masculino</option>
          <option value="FEMININO">Feminino</option>
        </select>
      </div>

      <div class="form-group">
<input type="text" id="nascimento" name="nascimento" placeholder="Data de Nascimento" maxlength="10" oninput="formatarData(this)" required>
        <input type="text" id="cpf" name="cpf" placeholder="CPF" oninput="formatCPF(this)" maxlength="14" required>
        <select id="estado-civil" name="estado-civil" required>
          <option value="" disabled selected>Estado civil</option>
          <option value="Solteiro">Solteiro(a)</option> 
          <option value="Casado">Casado(a)</option>
          <option value="Viúvo">Viúvo(a)</option>
        </select>
        <input type="password" id="senha" name="senha" placeholder="Senha" maxlength="12" required>
      </div>

      <input type="text" id="endereco" name="endereco" class="full-width" placeholder="RUA... Nº... BAIRRO, CIDADE" required>

      <div class="section-subtitle">FORMAS DE CONTATO</div>

      <div class="form-group">
        <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
        <input type="text" id="telefone" name="telefone" placeholder="Telefone" oninput="formatTelefone(this)" maxlength="15" required>
      </div>
      
      <div class="section-subtitle">NÍVEL</div>
      
      <div class="form-group center">
        <select id="nivel-usuario" name="nivel-usuario" required>
          <option value="" disabled selected>Nível de usuário</option>
          <option value="NIVEL 1">Nível 1</option>
          <option value="NIVEL 2">Nível 2</option>
        </select>
      </div>

      <div class="form-group center">
        <button type="submit" class="submit-button">CADASTRAR</button>
      </div>
    </form>
  </div>
    
  <div class="mensagem-erro" id="erro-senha">
    <strong>Senha inválida! Ela deve conter:</strong>
    <ul id="lista-erros"></ul>
    <button onclick="fecharErro()">OK</button>
  </div>


  <div class="mensagem-sucesso" id="sucesso-cadastro">
    <img src="img/confirmado.png" alt="Cadastro realizado com sucesso">
    <div style="text-align: center; margin-top: 15px;">
      <button onclick="fecharSucesso()" class="botao-ok">OK</button>
    </div>
  </div>
  
  <div class="mensagem-erro" id="erro-cpf">
    <strong>CPF inválido!</strong>
    <ul><li>Verifique o número digitado antes de cadastrar.</li></ul>
    <button onclick="fecharErroCPF()">OK</button>
  </div>
  
  <div class="mensagem-erro" id="erro-rg">
    <strong>RG inválido!</strong>
    <ul><li>O RG deve ter entre 7 e 10 dígitos numéricos.</li></ul>
    <button onclick="fecharErroRG()">OK</button>
  </div>

  <div class="mensagem-erro" id="erro-telefone">
    <strong>Telefone inválido!</strong>
    <ul><li>O telefone deve ter entre 10 e 11 dígitos numéricos (com DDD).</li></ul>
    <button onclick="fecharErroTelefone()">OK</button>
  </div>
  
  <div class="mensagem-erro" id="erro-email">
  <strong id="erro-email-titulo"></strong>
  <p id="erro-email-texto"></p>
  <button onclick="fecharErroEmail()">OK</button>
</div>

<div class="mensagem-erro" id="erro-nome">
  <strong>Nome inválido!</strong>
  <ul><li>O nome deve conter no mínimo 3 caracteres.</li></ul>
  <button onclick="fecharErroNome()">OK</button>
</div>

  <div class="mensagem-erro" id="erro-nascimento">
  <strong>Data de nascimento inválida!</strong>
  <ul><li>A idade deve estar entre 18 e 64 anos.</li></ul>
  <button onclick="fecharErroNascimento()">OK</button>
</div>

  
  
  <script>
    function fecharErroNascimento() {
  document.getElementById('erro-nascimento').style.display = 'none';
}

    function fecharErroNome() {
  document.getElementById('erro-nome').style.display = 'none';
}

  
    function validarEmail(email) {
  const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regexEmail.test(email);
}

function fecharErroEmail() {
  document.getElementById('erro-email').style.display = 'none';
}

    function validarTelefone(telefone) {
  telefone = telefone.replace(/\D/g, '');
  return telefone.length >= 10 && telefone.length <= 11;
}

function fecharErroTelefone() {
  document.getElementById('erro-telefone').style.display = 'none';
}

    function validarRG(rg) {
  rg = rg.replace(/\D/g, '');
  return rg.length >= 7 && rg.length <= 10;
}

function fecharErroRG() {
  document.getElementById('erro-rg').style.display = 'none';
}

    function fecharErroCPF() {
  document.getElementById('erro-cpf').style.display = 'none';
}


    function formatarData(input) {
  let valor = input.value.replace(/\D/g, '').slice(0, 8); // só números, máx 8 dígitos

  if (valor.length >= 5) {
    valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
  } else if (valor.length >= 3) {
    valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
  }

  input.value = valor;
}
function fecharSucesso() {
  document.getElementById('sucesso-cadastro').style.display = 'none';
  document.getElementById('cadastro-funcionario').reset(); // Limpa os campos
}

    function validarSenha(senha) {
      const erros = [];
      if (senha.length < 6) erros.push("• Mínimo 6 caracteres");
      if (!/[A-Z]/.test(senha)) erros.push("• Pelo menos 1 letra maiúscula");
      if (!/[a-z]/.test(senha)) erros.push("• Pelo menos 1 letra minúscula");
      if (!/[0-9]/.test(senha)) erros.push("• Pelo menos 1 número");
      if (!/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/.test(senha)) erros.push("• Pelo menos 1 caractere especial");
      return erros;
    }
  
    function fecharErro() {
      document.getElementById('erro-senha').style.display = 'none';
    }
  
    function validarCPF(cpf) {
      cpf = cpf.replace(/\D/g, '');
      if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
  
      let soma = 0;
      for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
      }
      let resto = (soma * 10) % 11;
      if (resto === 10) resto = 0;
      if (resto !== parseInt(cpf.charAt(9))) return false;
  
      soma = 0;
      for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
      }
      resto = (soma * 10) % 11;
      if (resto === 10) resto = 0;
      return resto === parseInt(cpf.charAt(10));
    }
    function validarIdade(dataNascStr) {
  const partes = dataNascStr.split('/');
  if (partes.length !== 3) return false;

  const [dia, mes, ano] = partes.map(Number);
  const nascimento = new Date(ano, mes - 1, dia);

  if (isNaN(nascimento.getTime())) return false;

  const hoje = new Date();
  let idade = hoje.getFullYear() - nascimento.getFullYear();
  const mesAtual = hoje.getMonth();
  const diaAtual = hoje.getDate();

  if (mesAtual < nascimento.getMonth() || 
     (mesAtual === nascimento.getMonth() && diaAtual < nascimento.getDate())) {
    idade--;
  }

  return idade >= 18 && idade <= 64;
}

  
    document.getElementById('cadastro-funcionario').addEventListener('submit', function(e) {
  const inputCpf = document.getElementById('cpf');
  const inputSenha = document.getElementById('senha');
  const listaErros = document.getElementById('lista-erros');
  const inputRG = document.getElementById('rg');
  const inputTelefone = document.getElementById('telefone');
  const inputEmail = document.getElementById('email');
  const inputNome = document.getElementById('nome');
  const inputNascimento = document.getElementById('nascimento');

// Limpa erro anterior
inputNascimento.classList.remove('cpf-erro');
document.getElementById('erro-nascimento').style.display = 'none';

// Validação da idade
if (!validarIdade(inputNascimento.value)) {
  inputNascimento.classList.add('cpf-erro');
  document.getElementById('erro-nascimento').style.display = 'block';
  e.preventDefault();
  return;
}


// Limpa erro anterior
inputNome.classList.remove('cpf-erro');
document.getElementById('erro-nome').style.display = 'none';

// Nome
if (inputNome.value.trim().length < 3) {
  inputNome.classList.add('cpf-erro');
  document.getElementById('erro-nome').style.display = 'block';
  e.preventDefault();
  return;
}

if (inputNome.value.trim().length < 3) {
  inputNome.classList.add('cpf-erro');
  alert("O nome deve conter no mínimo 3 caracteres.");
  e.preventDefault();
  return;
}


  // Limpa erros anteriores
  inputTelefone.classList.remove('cpf-erro');
  document.getElementById('erro-telefone').style.display = 'none';

  inputRG.classList.remove('cpf-erro');
  document.getElementById('erro-rg').style.display = 'none';

  inputCpf.classList.remove('cpf-erro');
  inputSenha.classList.remove('senha-erro');
  listaErros.innerHTML = '';
  document.getElementById('erro-senha').style.display = 'none';

  inputEmail.classList.remove('cpf-erro');
  document.getElementById('erro-email').style.display = 'none';

  // CPF
  if (!validarCPF(inputCpf.value)) {
    inputCpf.classList.add('cpf-erro');
    document.getElementById('erro-cpf').style.display = 'block';
    e.preventDefault();
    return;
  }

  // RG
  if (!validarRG(inputRG.value)) {
    inputRG.classList.add('cpf-erro');
    document.getElementById('erro-rg').style.display = 'block';
    e.preventDefault();
    return;
  }

  // Telefone
  if (!validarTelefone(inputTelefone.value)) {
    inputTelefone.classList.add('cpf-erro');
    document.getElementById('erro-telefone').style.display = 'block';
    e.preventDefault();
    return;
  }

  // Email
  if (!validarEmail(inputEmail.value)) {
    inputEmail.classList.add('cpf-erro');
    document.getElementById('erro-email').style.display = 'block';
    e.preventDefault();
    return;
  }

  // Senha
  const errosSenha = validarSenha(inputSenha.value);
  if (errosSenha.length > 0) {
    inputSenha.classList.add('senha-erro');
    listaErros.innerHTML = errosSenha.map(e => `<li>${e}</li>`).join('');
    document.getElementById('erro-senha').style.display = 'block';
    e.preventDefault();
    return;
  }

  // Sucesso
    e.preventDefault();
  document.getElementById('sucesso-cadastro').style.display = 'block';
  setTimeout(() => {
    document.getElementById('sucesso-cadastro').style.display = 'none';
  }, 3000);
});

  
    function formatCPF(input) {
      let v = input.value.replace(/\D/g, '').slice(0, 11);
      if (v.length > 9) {
        v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
      } else if (v.length > 6) {
        v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
      } else if (v.length > 3) {
        v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
      }
      input.value = v;
    }
  
    function formatRG(input) {
      let v = input.value.replace(/\D/g, '').slice(0, 10);
      if (v.length > 9) {
        v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
      } else if (v.length > 6) {
        v = v.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
      } else if (v.length > 2) {
        v = v.replace(/(\d{2})(\d{1,3})/, '$1.$2');
      }
      input.value = v;
    }
  
    function formatTelefone(input) {
      let v = input.value.replace(/\D/g, '').slice(0, 11);
      if (v.length <= 10) {
        if (v.length > 6) {
          v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (v.length > 2) {
          v = v.replace(/(\d{2})(\d{0,4})/, '($1) $2');
        }
      } else {
        v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
      }
      input.value = v;
    } 
  </script>
  
</body>
</html>