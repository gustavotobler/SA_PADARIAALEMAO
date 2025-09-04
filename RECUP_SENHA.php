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

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('Formato de email inválido');</script>";
    } else {
        $sql="SELECT * FROM funcionario WHERE Email = :Email";
        $stmt= $pdo->prepare($sql);
        $stmt->bindParam(':Email',$email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario){
            $senha_temporaria = gerarSenhaTemporaria();
            $senha_hash = password_hash($senha_temporaria,PASSWORD_DEFAULT);

            $sql="UPDATE funcionario SET Senha = :Senha, senha_temporaria = TRUE WHERE Email = :Email";
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':Senha',$senha_hash);
            $stmt->bindParam(':Email',$email);
            $stmt->execute();

            simularEnvioEmail($email,$senha_temporaria);
            echo "<script>alert('Uma senha temporária foi gerada e enviada (simulação). Verifique o arquivo emails_simulados.txt');window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Email não encontrado');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:  #1b263b;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0px 6px 20px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        .logo {
            width: 100px;
            margin-bottom: 15px;
        }

        h2 {
            margin-bottom: 15px;
            color:rgb(0, 0, 0);
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
            text-align: left;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            transition: all 0.3s;
        }

        input:focus {
            border-color:rgb(5, 37, 91);
            outline: none;
            box-shadow: 0 0 6px rgba(2, 31, 82, 0.5);
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg,rgb(4, 10, 21),rgb(5, 1, 69));
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0px 5px 15px rgba(37,117,252,0.4);
        }

        .btn-voltar {
            display: block;
            margin-top: 12px;
            padding: 12px;
            border-radius: 10px;
            background: #999;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-voltar:hover {
            background: #777;
        }

        footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="img/Logopadaria.png" alt="Logo Padaria" class="logo">
        <h2>Recuperar senha</h2>
        <form action="RECUP_SENHA.php" method="POST" onsubmit="return validarFuncionario()">
            <label for="email">Digite o seu e-mail cadastrado</label>
            <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>
            <button type="submit">Enviar senha temporária</button>
        </form>
        <a href="index.php" class="btn-voltar">Voltar</a>
        <footer>&copy; 2025 Padaria do Alemão</footer>
    </div>
    <script>
      function validarFuncionario() {
        const emailInput = document.getElementById("email");
        const email = emailInput.value.trim();

        if (email === "") {
            alert("Por favor, digite o seu e-mail.");
            emailInput.focus();
            return false;
        }

        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(email)) {
            alert("Digite um e-mail válido (exemplo: usuario@email.com).");
            emailInput.focus();
            return false;
        }

        return true;
      }
    </script>
</body>
</html>
