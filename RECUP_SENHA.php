<!DOCTYPE html>
<html lang="pt-br">
<head>  
  <meta charset="UTF-8"> <!-- Define a codificação de caracteres para UTF-8 -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsivo: ajusta a escala da página em dispositivos móveis -->
  <title>Recuperar Senha</title> <!-- Título da aba do navegador -->

  <!-- Importa os ícones do Google Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Importa o arquivo CSS para estilizar a página -->
  <link rel="stylesheet" type="text/css" href="css/site.css">
</head>
<body>

  <header>
    <!-- Botão de voltar para a página inicial (index.html) -->
    <a href="index.html">
      <button class="back-button">
        <!-- Ícone de seta para trás -->
        <span class="material-icons">arrow_back</span>
      </button>
    </a>

    <!-- Título principal da página -->
    <h1>RECUPERAR SENHA</h1>

    <!-- Logo da padaria com texto alternativo e título -->
    <img src="img/Logopadaria.png" alt="Logo da Padaria" title="Logo">
  </header>

  <main>
    <!-- Formulário para recuperação de senha -->
    <form id="form" name="frmRecupera" method="get" action="#">
      <div class="login">
        <!-- Explicação para o usuário -->
        <h2>CASO TENHA ESQUECIDO SUA SENHA, PREENCHA SEU E‑MAIL PARA ENVIARMOS UM CÓDIGO DE RECUPERAÇÃO</h2>

        <!-- Label do campo email -->
        <label for="email"><strong>E‑mail:</strong></label>

        <!-- Campo de entrada do email, obrigatório -->
        <input type="email" name="txtemail" id="email" class="inputs required" required>

        <!-- Botão para enviar o formulário -->
        <button type="submit">Enviar</button>
      </div>
    </form>
  </main>

  <footer>
    <!-- Rodapé simples -->
     <div class="fonte">
    <strong>Copyright &copy; Alemões</strong>
    </div>
  </footer>

</body>
</html>
