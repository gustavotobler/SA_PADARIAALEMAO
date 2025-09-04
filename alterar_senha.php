<?php
session_start(); // Inicia a sessão para manter dados do usuário
require_once 'conexao.php'; // Inclui a conexão com o banco de dados

// Verifica se o usuário está logado, se não, redireciona para login
if (!isset($_SESSION['funcionario'])) {
    header("Location: index.php");
    exit();
}

// Verifica se a senha temporária está ativa, se não, redireciona
if (empty($_SESSION['senha_temp'])) {
    header("Location: inicial1.php");
    exit();
}

// Processa o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    // Verifica se as senhas coincidem e têm pelo menos 6 caracteres
    if ($nova === $confirmar && strlen($nova) >= 6) {
        $hash = password_hash($nova, PASSWORD_DEFAULT); // Cria hash seguro da senha

        // Atualiza a senha do usuário no banco e desativa a senha temporária
        $sql = "UPDATE funcionario SET Senha = :senha, senha_temporaria = 0 WHERE ID_func = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $hash);
        $stmt->bindParam(':id', $_SESSION['funcionario']);
        $stmt->execute();

        $_SESSION['senha_temp'] = 0; // Atualiza a sessão para indicar senha permanente

        echo "<script>alert('Senha alterada com sucesso!');window.location.href='inicial1.php';</script>";
        exit();
    } else {
        // Caso haja erro na validação
        echo "<script>alert('Senhas não coincidem ou são muito curtas');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha</title>
    <style>
        /* Estilo base do corpo */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:  #1b263b;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Container do formulário */
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

        h2 {
            margin-bottom: 15px;
            color: rgb(0, 0, 0);
        }

        p {
            margin-bottom: 20px;
            font-size: 14px;
            color: #555;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
            text-align: left;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            transition: all 0.3s;
        }

        input[type="password"]:focus {
            border-color: rgb(5, 37, 91);
            outline: none;
            box-shadow: 0 0 6px rgba(2, 31, 82, 0.5);
        }

        .checkbox {
            margin: 10px 0 15px;
            font-size: 13px;
            text-align: left;
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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0px 5px 15px rgba(37,117,252,0.4);
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
    <h2>Alterar Senha</h2>
    <!-- Saudação ao usuário -->
    <p>Olá, <strong><?php echo $_SESSION['nome_func'] ?? 'funcionário'; ?></strong>. Digite sua nova senha abaixo:</p>

    <!-- Formulário de alteração de senha -->
    <form action="alterar_senha.php" method="POST" onsubmit="return validar()">
        <label for="nova_senha">Nova senha</label>
        <input type="password" id="nova_senha" name="nova_senha" required>

        <label for="confirmar_senha">Confirmar nova senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>

        <!-- Checkbox para mostrar senha -->
        <div class="checkbox">
            <input type="checkbox" onclick="mostrarSenha()"> Mostrar senha
        </div>

        <button type="submit">Salvar nova senha</button>
    </form>

    <footer>
        &copy; 2025 Padaria do Alemão
    </footer>
</div>

<script>
   // Validação do formulário antes do envio
   function validar() {
        const novaSenha = document.getElementById('nova_senha').value.trim();
        const confirmarSenha = document.getElementById('confirmar_senha').value.trim();

        if (novaSenha.length < 8) {
            alert('A senha deve ter pelo menos 8 caracteres!');
            return false;
        }

        if (novaSenha !== confirmarSenha) {
            alert('As senhas não coincidem!');
            return false;
        }

        if (novaSenha === 'tem123') {
            alert('Escolha uma senha diferente da senha temporária!');
            return false;
        }

        return true; // Tudo ok, permite envio
    }

    // Alterna visibilidade das senhas
    function mostrarSenha() {
        let senha1 = document.getElementById("nova_senha");
        let senha2 = document.getElementById("confirmar_senha");
        let tipo = senha1.type === "password" ? "text" : "password";
        senha1.type = tipo;
        senha2.type = tipo;
    }
</script>
</body>
</html>
