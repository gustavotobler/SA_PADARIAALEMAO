<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cadastro de Produto</title>

  <!-- Ícones do Material Design -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <!-- CSS da página -->
  <link href="css/cadprod.css" rel="stylesheet"/>
</head>
<body>

  <!-- Verifica o nível do usuário. Se não for 'admin', redireciona. -->
  <script>
    const nivel = localStorage.getItem("nivel_usuario");
    if (nivel !== "admin") {
      alert("Acesso restrito! Apenas administradores podem acessar esta página.");
      window.location.href = "inicial2.php";
    }
  </script>

  <div class="page">
    <div class="form-box">
      <!-- Botão de voltar -->
      <a href="produtos.php"><button class="back-button"><span class="material-icons">arrow_back</span></button></a>

      <!-- Título e subtítulo -->
      <h2>Cadastro de Produto</h2>
      <div class="section-subtitle">INFORMAÇÕES DO PRODUTO</div>

      <!-- Formulário de cadastro -->
      <form id="formCadastro" method="POST" action="cadastros/cadastro_produto.php">

        <!-- Linha com nome do produto e preço -->
        <div class="row">
          <div class="input-group">
            <label for="nome">Nome do Produto</label>
            <input type="text" id="nome" name="Nome_prod" />
          </div>
          <div class="input-group">
            <label for="preco">Preço</label>
            <input type="number" id="preco" name="Preco_unitario" step="0.01" min="0.10" />
          </div>
        </div>

        <!-- Linha com unidade de medida, quantidade e fornecedor -->
        <div class="row">
          <div class="input-group">
            <label for="escolha">Unidade M.</label>
            <select id="escolha" name="Unid_medida">
              <option value="">Selecione</option>
              <option value="kg">kg</option>
              <option value="g">g</option>
              <option value="mL">mL</option>
              <option value="L">L</option>
            </select>
          </div>
          <div class="input-group">
            <label for="Quantidade">Quantidade</label>
            <input type="number" id="Quantidade" name="Qntd_produto" step="1" min="1" />
          </div>
          <div class="input-group">
            <label for="categoria">Fornecedor</label>
            <select id="categoria" name="ID_forn">
              <option value="">Selecione</option>
              <option value="padaria">CARLINHOS</option>
              <option value="laticinio">BOLOS MAIDEN</option>
              <option value="hortifruti">DOCES MARIA</option>
              <option value="bebidas">DONA BENTA</option>
            </select>
          </div>
        </div>
        <div class="input-group">
          <label for="categoria">Categoria</label>
          <select id="categoria" name="id_categorias">
            <option value="">Selecione</option>
            <option value="padaria">Cafés</option>
            <option value="laticinio">Sucos</option>
            <option value="hortifruti">Pães</option>
            <option value="bebidas">Bolos</option>
            <option value="bebidas">Salgados</option>
          </select>
        </div>

        <!-- Campo de validade com máscara de data -->
        <div class="input-group" style="flex:1 1 100%">
          <label for="validade">Validade</label>
          <input type="text" id="validade" name="Validade" placeholder="DD/MM/AAAA" maxlength="10" oninput="formatarData(this)" required />
        </div>

        <!-- Botão de envio -->
        <div class="btn-container"><button type="submit">Cadastrar</button></div>
      </form>
    </div>
  </div>

  <!-- Mensagem de erro personalizada -->
  <div class="mensagem-erro" id="mensagem-erro">
    <strong id="erro-titulo"></strong>
    <p id="erro-texto"></p>
    <button onclick="fecharErro()">OK</button>
  </div>

  <!-- Modal de confirmação de sucesso -->
  <div id="confirmacao">
    <div class="box">
      <h2>Tudo certo!</h2>
      <p>Cadastro realizado com sucesso</p>
      <button onclick="fecharConfirmacao()">OK</button>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Máscara para o campo de data (formato DD/MM/AAAA)
    function formatarData(input) {
      let v = input.value.replace(/\D/g, '').slice(0,8);
      if (v.length >= 5) v = v.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
      else if (v.length >= 3) v = v.replace(/(\d{2})(\d{1,2})/, '$1/$2');
      input.value = v;
    }

    // Exibe mensagem de erro customizada
    function showError(titulo, texto) {
      document.getElementById('erro-titulo').innerText = titulo;
      document.getElementById('erro-texto').innerText = texto;
      document.getElementById('mensagem-erro').style.display = 'block';
    }

    // Fecha a mensagem de erro
    function fecharErro() {
      document.getElementById('mensagem-erro').style.display = 'none';
    }

        // Coleta os valores dos campos
        const nome      = document.getElementById('nome').value.trim();
        const precoVal  = parseFloat(document.getElementById('preco').value);
        const escolha   = document.getElementById('escolha').value;
        const categoria = document.getElementById('categoria').value;
        const valStr    = document.getElementById('validade').value.trim();

        // Valida nome
        if (!nome) {
          showError('Nome inválido','O nome do produto é obrigatório.');
          return;
        }
        if (nome.length < 3) {
          showError('Nome muito curto','O nome deve ter ao menos 3 caracteres.');
          return;
        }

        // Valida preço
        if (isNaN(precoVal)) {
          showError('Preço inválido','Informe um preço válido.');
          return;
        }
        if (precoVal < 0.10) {
          showError('Preço muito baixo','O preço mínimo é R$ 0,10.');
          return;
        }

        // Valida unidade de medida e fornecedor
        if (!escolha) {
          showError('Unidade não selecionada','Escolha uma unidade de medida.');
          return;
        }
        if (!categoria) {
          showError('Fornecedor não selecionado','Selecione um fornecedor.');
          return;
        }

        // Validação da data (formato e valor futuro)
        const partes = valStr.split('/');
        if (partes.length !== 3) {
          showError('Data inválida','Use o formato DD/MM/AAAA na validade.');
          return;
        }
        const [dd, mm, yyyy] = partes.map(n=>parseInt(n,10));
        if ([dd,mm,yyyy].some(isNaN) || dd<1||dd>31||mm<1||mm>12) {
          showError('Data inválida','Verifique dia e mês da validade.');
          return;
        }

        const dataVal = new Date(yyyy, mm-1, dd);
        const hoje = new Date();
        hoje.setHours(0,0,0,0); // Ignora horário
        if (dataVal < hoje) {
          showError('Data anterior','Validade não pode ser anterior a hoje.');
          return;
        }

        // Se todas as validações passarem, exibe modal de sucesso
        document.getElementById('confirmacao').style.display='flex';
      });
    });

    // Fecha o modal de confirmação e reseta o formulário
    function fecharConfirmacao() {
      document.getElementById('confirmacao').style.display='none';
      document.getElementById('formCadastro').reset();
    }
  </script>
</body>
</html>
