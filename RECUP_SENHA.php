<?php 
session_start();
require_once 'conexao.php';

// Função que gera uma senha temporária
function gerarSenhaTemporaria($tamanho = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%';
    return substr(str_shuffle($chars), 0, $tamanho);
}

// Função que simula envio de email (grava em txt)
function simularEnvioEmail($email, $senha) {
    $arquivo = "emails_simulados.txt";
    $mensagem = "Para: $email - Senha temporária: $senha" . PHP_EOL;
    file_put_contents($arquivo, $mensagem, FILE_APPEND);
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $email = $_POST['email'];

    // Validação extra no servidor
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('Formato de email inválido');</script>";
    } else {
        //VERIFICA SE O EMAIL EXISTE NO BANCO DE DADOS
        $sql="SELECT * FROM funcionario WHERE Email = :Email";
        $stmt= $pdo->prepare($sql);
        $stmt->bindParam(':Email',$email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario){
            //GERA UMA SENHA TEMPORÁRIA
            $senha_temporaria = gerarSenhaTemporaria();
            $senha_hash = password_hash($senha_temporaria,PASSWORD_DEFAULT);

            //ATUALIZA NO BANCO
            $sql="UPDATE funcionario SET Senha = :Senha, senha_temporaria = TRUE WHERE Email = :Email";
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':Senha',$senha_hash);
            $stmt->bindParam(':Email',$email);
            $stmt->execute();

            //SIMULA ENVIO
            simularEnvioEmail($email,$senha_temporaria);
            echo "<script>alert('Uma senha temporária foi gerada e enviada (simulação). Verifique o arquivo emails_simulados.txt');window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Email não encontrado');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #74ABE2, #5563DE);
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
      }

      .container {
        background: #fff;
        padding: 25px 30px;
        border-radius: 15px;
        box-shadow: 0px 5px 15px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 400px;
        text-align: center;
      }

      h2 {
        margin-bottom: 20px;
        color: #5563DE;
      }

      label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
      }

      input[type="email"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
        transition: 0.3s;
      }

      input[type="email"]:focus {
        border-color: #5563DE;
        outline: none;
        box-shadow: 0 0 5px rgba(85,99,222,0.5);
      }

      button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: #5563DE;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
      }

      button:hover {
        background: #4050b5;
      }

      a button {
        background: #999;
        margin-top: 10px;
      }

      a button:hover {
        background: #777;
      }

      footer {
        margin-top: 20px;
        font-size: 12px;
        color: #666;
      }
    </style>
</head>
<body>
    <div class="container">
        <h2>Recuperar senha</h2>
        <form action="RECUP_SENHA.php" method="POST" onsubmit="return validarFuncionario()">
            <label for="email">Digite o seu email cadastrado</label>
            <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>
            <button type="submit">Enviar senha temporária</button>
        </form>
        <a href="index.php"><button type="button">Voltar</button></a>
        <footer>© Gustavo Tobler - Técnico de Desenvolvimento de Sistemas</footer>
    </div>
    <script>
      function validarFuncionario() {
        const emailInput = document.getElementById("email");
        const email = emailInput.value.trim();

        if (email === "") {
            alert("Por favor, digite o seu email.");
            emailInput.focus();
            return false;
        }

        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(email)) {
            alert("Digite um email válido (exemplo: usuario@email.com).");
            emailInput.focus();
            return false;
        }

        return true;
      }
    </script>
</body>
</html>
