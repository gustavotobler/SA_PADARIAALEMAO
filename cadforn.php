<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Fornecedor</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background-color: #e0e0e0;
      color: #212529;
      padding: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .page {
      max-width: 800px;
      width: 100%;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .form-box h2 {
      text-align: center;
      margin-bottom: 24px;
      color: #343a40;
      padding: 10px;
      background-color: #dee2e6;
      border-radius: 6px;

      font-size: 24px;
    }

    .section-title {
      background-color: #dee2e6;
      color: #343a40;
      font-weight: bold;
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      margin-top: 20px;
    }

    .form-box form {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 10px;
    }

    .row {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .input-group {
      flex: 1;
      min-width: 250px;
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      font-weight: 600;
      margin-bottom: 6px;
      color: #495057;
    }

    .input-group input,
    .input-group select {
      padding: 10px 12px;
      border: 1px solid #ced4da;
      border-radius: 6px;
      font-size: 15px;
      color: #495057;
      background-color: #ffffff;
      outline: none;
    }

    .input-group input:focus,
    .input-group select:focus {
      outline: 2px solid #6666ff;
      background-color: #f0f8ff;
    }

    .btn-container {
      display: flex;
      justify-content: center;
    }

    .btn-container button {
      padding: 12px 32px;
      font-size: 16px;
      background-color: #6c757d;
      color: #ffffff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.2s;
      margin-top:12px;
    }

    .btn-container button:hover {
      background-color: #5a6268;
    }

    .back-button {
      position: absolute;
      left: 20px;
      top: 20px;
      background: none;
      border: none;
      font-size: 28px;
      cursor: pointer;
      color: #333;
    }
  </style>
</head>
<body>
  <script>

function formattelefone(input) {
      let v = input.value.replace(/\D/g, '').slice(0,11);
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

    // Bloqueia acesso se não for admin
    const nivel = localStorage.getItem("nivel_usuario");
    if (nivel !== "admin") {
      alert("Acesso restrito! Apenas administradores podem acessar esta página.");
      window.location.href = "inicial1.html"; // ou página de acesso negado
    }

 src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"
    
      // Aplica a máscara ao campo CNPJ
  Inputmask("99.999.999/9999-99").mask(document.getElementById("cnpj"));

function validarcnpj(cnpj) {
  cnpj = cnpj.replace(/[^\d]+/g, '');

  if (cnpj === '') return false;
  if (cnpj.length !== 14) return false;
  if (/^(\d)\1+$/.test(cnpj)) return false;

  let tamanho = cnpj.length - 2;
  let numeros = cnpj.substring(0, tamanho);
  let digitos = cnpj.substring(tamanho);
  let soma = 0;
  let pos = tamanho - 7;

  for (let i = tamanho; i >= 1; i--) {
    soma += numeros.charAt(tamanho - i) * pos--;
    if (pos < 2) pos = 9;
  }

  let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
  if (resultado !== parseInt(digitos.charAt(0))) return false;

  tamanho += 1;
  numeros = cnpj.substring(0, tamanho);
  soma = 0;
  pos = tamanho - 7;

  for (let i = tamanho; i >= 1; i--) {
    soma += numeros.charAt(tamanho - i) * pos--;
    if (pos < 2) pos = 9;
  }

  resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
  return resultado === parseInt(digitos.charAt(1));
}

// Validação ao enviar o formulário
document.querySelector("form").addEventListener("submit", function (e) {
  const cnpjInput = document.getElementById("cnpj");
  const cnpj = cnpjInput.value;

  if (!validarcnpj(cnpj)) {
    e.preventDefault();
    alert("CNPJ inválido. Por favor, verifique o número informado.");
    cnpjInput.focus();
  }
});
  </script>
  
  <div class="page">
    <a href="fornecedores.html">
    <button class="back-button"><span class="material-icons">arrow_back</span></button>
    </a>
    <div class="form-box">
      <h2>CADASTRO DE FORNECEDOR</h2>

      <div class="section-title">DADOS DA EMPRESA</div>

      <form>
        <div class="row">
          <div class="input-group">
            <label for="empresa">NOME DA EMPRESA</label>
            <input type="text" id="empresa" name="empresa" placeholder="Nome da empresa "
>
          </div>
          <div class="input-group">
            <label for="cnpj">CNPJ</label>
            <input type="text"
                   id=cnpj
                   name=cnpj
                   placeholder="99.999.999/9999-99"
                   oninput="formatcnpj(this)"
                   maxlength="18"
                   required>
          </div>
        </div>

        <div class="row">
          <div class="input-group">
            <label for="fundacao">DATA DE FUNDAÇÃO</label>
<input type="text" id="nascimento" name="nascimento" placeholder="DD/MM/AAAA" maxlength="10" oninput="formatarData(this)" required>
          </div>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="input-group" style="flex: 1 1 100%">
            <label for="endereco">ENDEREÇO</label>
            <input type="text" id="endereco" name="endereco" placeholder="Rua ..., Nº, Bairro, Cidade">
          </div>
        </div>

        <div class="section-title">FORMAS DE CONTATO</div>

<div class="row">
  <div class="input-group">
    <label for="email">EMAIL</label>
    <input type="email" id="email" name="email" placeholder="Digite o e-mail" required>
  </div>
  <div class="input-group">
    <label for="telefone">TELEFONE</label>
    <input type="text"
           id="telefone"
           name="telefone"
           placeholder="(00) 00000-0000"
           oninput="formattelefone(this)"
           maxlength="15"
           required>
  </div>
</div>


<!-- Importa a biblioteca Inputmask -->
<script>


function mostrarErro(titulo, mensagens) {
  const modal = document.getElementById('erro-geral');
  const tituloEl = document.getElementById('erro-titulo');
  const lista = document.getElementById('erro-lista');
  
  tituloEl.textContent = titulo;
  lista.innerHTML = mensagens.map(msg => `<li>${msg}</li>`).join('');
  modal.style.display = 'block';
}

function fecharErroGeral() {
  document.getElementById('erro-geral').style.display = 'none';
}

document.querySelector("form").addEventListener("submit", function (e) {
  const empresa = document.getElementById("empresa");
  const endereco = document.getElementById("endereco");
  const email = document.getElementById("email");
  const telefone = document.getElementById("telefone");
  const fundacao = document.getElementById("nascimento");

  // Nome da empresa
  if (empresa.value.trim().length < 3 || empresa.value.length > 100) {
    e.preventDefault();
    alert("O nome da empresa deve ter entre 3 e 100 caracteres.");
    empresa.focus();
    return;
  }

  // Endereço
  if (endereco.value.trim().length < 10 || endereco.value.length > 200) {
    e.preventDefault();
    alert("O endereço deve ter entre 10 e 200 caracteres.");
    endereco.focus();
    return;
  }

  // Email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email.value)) {
    e.preventDefault();
    alert("E-mail inválido.");
    email.focus();
    return;
  }

  // Telefone
  const telefoneLimpo = telefone.value.replace(/\D/g, '');
  if (telefoneLimpo.length < 10 || telefoneLimpo.length > 11) {
    e.preventDefault();
    alert("Telefone inválido. Deve conter DDD e número com 8 ou 9 dígitos.");
    telefone.focus();
    return;
  }

  // Data de fundação
  const dataRegex = /^\d{2}\/\d{2}\/\d{4}$/;
  if (!dataRegex.test(fundacao.value)) {
    e.preventDefault();
    alert("Data de fundação inválida. Use o formato DD/MM/AAAA.");
    fundacao.focus();
    return;
  }

  const partes = fundacao.value.split("/");
  const dataFormatada = `${partes[2]}-${partes[1]}-${partes[0]}`; // YYYY-MM-DD
  const hoje = new Date().toISOString().split("T")[0];
  if (dataFormatada < "1800-01-01" || dataFormatada > hoje) {
    e.preventDefault();
    alert("Data de fundação deve estar entre 01/01/1800 e hoje.");
    fundacao.focus();
    return;
  }

  // CNPJ já validado acima (mantenha essa parte do seu código)
});

  // Limita a data de fundação entre 1800-01-01 e hoje
const fundacaoInput = document.getElementById("fundacao");
const hoje = new Date().toISOString().split("T")[0];
fundacaoInput.setAttribute("min", "1800-01-01");
fundacaoInput.setAttribute("max", hoje);

// Validação da data ao enviar o formulário
document.querySelector("form").addEventListener("submit", function (e) {
  const fundacao = fundacaoInput.value;
  if (fundacao && (fundacao < "1800-01-01" || fundacao > hoje)) {
    e.preventDefault();
    alert("Data de fundação inválida. Deve estar entre 01/01/1800 e hoje.");
    fundacaoInput.focus();
  }
});

  function formatarData(input) {
  let valor = input.value.replace(/\D/g, '').slice(0, 8); // só números, máx 8 dígitos

  if (valor.length >= 5) {
    valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
  } else if (valor.length >= 3) {
    valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
  }

  input.value = valor;
}


  const dia = document.getElementById('dia').value.padStart(2, '0');
const mes = document.getElementById('mes').value.padStart(2, '0');
const ano = document.getElementById('ano').value;
const dataNascimento = `${ano}-${mes}-${dia}`; // formato YYYY-MM-DD
console.log("Data de nascimento formatada:", dataNascimento);


  function formatDia(input) {
  input.value = input.value.replace(/\D/g, '').slice(0, 2);
}

function formatMes(input) {
  input.value = input.value.replace(/\D/g, '').slice(0, 2);
}

function formatAno(input) {
  input.value = input.value.replace(/\D/g, '').slice(0, 4);
}

  document.addEventListener("DOMContentLoaded", function () {
    const cnpjInput = document.getElementById("cnpj");

    // ✅ Aplica a máscara enquanto o usuário digita
    cnpjInput.addEventListener("input", function () {
      formatarCNPJ(this);
    });

    // ✅ Valida o CNPJ ao enviar o formulário
    document.querySelector("form").addEventListener("submit", function (e) {
      const cnpj = cnpjInput.value;

      if (!validarCNPJ(cnpj)) {
        e.preventDefault();
        alert("CNPJ inválido. Por favor, verifique o número informado.");
        cnpjInput.focus();
      }
    });
  });

  // 🧠 Máscara de CNPJ (formatação)
  function formatarCNPJ(input) {
    let cnpj = input.value.replace(/\D/g, '').slice(0, 14); // apenas números, máximo 14 dígitos

    if (cnpj.length >= 3) {
      cnpj = cnpj.replace(/^(\d{2})(\d)/, "$1.$2");
    }
    if (cnpj.length >= 6) {
      cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
    }
    if (cnpj.length >= 9) {
      cnpj = cnpj.replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, "$1.$2.$3/$4");
    }
    if (cnpj.length >= 13) {
      cnpj = cnpj.replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, "$1.$2.$3/$4-$5");
    }

    input.value = cnpj;
  }

  // 🧠 Validação do CNPJ (dígitos verificadores)
  function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, '');

    if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) return false;

    let tamanho = 12;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
      soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
      if (pos < 2) pos = 9;
    }

    let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado !== parseInt(digitos.charAt(0))) return false;

    tamanho += 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
      soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
      if (pos < 2) pos = 9;
    }

    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    return resultado === parseInt(digitos.charAt(1));
  }



  // Função de formatação de telefone (mantida)
  function formattelefone(input) {
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


        <div class="btn-container">
          <button type="submit">CADASTRAR</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>