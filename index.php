<?php
session_start();
require_once 'conexao.php';

$erro = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['Email'] ?? '';
    $senha = $_POST['Senha'] ?? '';

    $sql = "SELECT * FROM funcionario WHERE Email = :Email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':Email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['Senha'])) {
        $_SESSION['funcionario'] = $usuario['ID_func'];
        $_SESSION['nivel'] = $usuario['nivel_de_acesso'];
        $_SESSION['nome_func'] = $usuario['Nome_func'];
        $_SESSION['senha_temp'] = $usuario['senha_temporaria'];
        

        if ($usuario['senha_temporaria']) {
            header("Location: alterar_senha.php");
            exit();
        } else {
            header("Location: inicial1.php");
            exit();
        }
    } else {
        $erro = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padaria do Alemão - Login</title>
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

        input[type="email"],
        input[type="password"] {
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

        .erro {
            color: red;
            font-size: 13px;
            margin: 10px 0;
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

        .esqueceu-senha {
            display: block;
            margin-top: 12px;
            font-size: 13px;
            color:rgb(5, 37, 91);
            text-decoration: none;
        }

        .esqueceu-senha:hover {
            text-decoration: underline;
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
    <h2>Login</h2>

    <form method="POST">
        <label for="Email">E-mail:</label>
        <input type="email" name="Email" id="Email" required>

        <label for="Senha">Senha:</label>
        <input type="password" id="Senha" name="Senha" required>

        <?php if ($erro): ?>
            <p class="erro">E-mail ou senha incorretos.</p>
        <?php endif; ?>

        <button type="submit">Entrar</button>
        <a href="RECUP_SENHA.php" class="esqueceu-senha">Esqueceu a senha?</a>
    </form>

    <footer>
        &copy; 2025 Padaria do Alemão
    </footer>
</div>

</body>
</html>
