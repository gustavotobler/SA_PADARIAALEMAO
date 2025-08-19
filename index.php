<?php 
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['Email'];
    $senha = $_POST['Senha'];

    $sql = "SELECT * FROM funcionario WHERE Email = :Email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':Email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    

    if ($usuario) {
        // login ok
        $_SESSION['funcionario'] = $usuario['Nome_func'];
        $_SESSION['nivel'] = $usuario['Nivel'];
        $_SESSION['ID_func'] = $usuario['ID_func'];
    
        header("Location: inicial1.php");
        exit;
    } else {
        header("Location: index.php?erro=1");
        exit;
    }}    
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres para UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para o site ser responsivo -->
    <title>Padaria do Alemão - Login</title> <!-- Título que aparece na aba do navegador -->
    <link rel="stylesheet" type="text/css" href="css/site.css"> <!-- Link para o arquivo CSS externo -->
</head>
<body>

    <header>
        <h1>LOGIN</h1> <!-- Título da página -->
        <img src="img/Logopadaria.png" title="Logo Padaria" alt="Logo da Padaria"> <!-- Logo da padaria com título e texto alternativo -->
    </header>

    <main>
        <!-- Formulário de login -->
        <form id="form" action="index.php" method="POST">
            <label for="Email">E-mail:</label> <!-- Label para o input de email -->
            <input type="email" name="Email" id="Email" class="inputs required" required> <!-- Campo de email obrigatório -->

            <label for="Senha">Senha:</label> <!-- Label para o input de senha -->
            <input type="password" id="Senha" name="Senha" required> <!-- Campo de senha obrigatório -->

            <button type="submit">Entrar</button> <!-- Botão para enviar o formulário -->

            <a href="RECUP_SENHA.php" class="esqueceu-senha" id="esqueceu-senha">Esqueceu a senha?</a> <!-- Link para recuperação de senha -->
        </form>
    </main>

    <footer>
		<div class ="fonte">
        <strong>Copyright &copy; Alemões</strong> <!-- Copyright da página -->
		</div>
    </footer>

</body>
</html>
