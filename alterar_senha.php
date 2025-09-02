<?php
session_start();
require_once 'conexao.php';

// Se não tiver logado, volta pro login
if (!isset($_SESSION['funcionario'])) {
    header("Location: index.php");
    exit();
}

// Se não estiver com senha temporária, não precisa trocar
if (empty($_SESSION['senha_temp'])) {
    header("Location: inicial1.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if ($nova === $confirmar && strlen($nova) >= 6) {
        $hash = password_hash($nova, PASSWORD_DEFAULT);

        $sql = "UPDATE funcionario SET Senha = :senha, senha_temporaria = 0 WHERE ID_func = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $hash);
        $stmt->bindParam(':id', $_SESSION['funcionario']);
        $stmt->execute();

        $_SESSION['senha_temp'] = 0; // Atualiza sessão

        echo "<script>alert('Senha alterada com sucesso!');window.location.href='inicial1.php';</script>";
        exit();
    } else {
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,rgb(0, 0, 0),rgb(0, 4, 57));
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

        h2 {
            margin-bottom: 15px;
            color:rgb(0, 0, 0);
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
            border-color:rgb(5, 37, 91);
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
    <p>Olá, <strong><?php echo $_SESSION['nome_func'] ?? 'funcionário'; ?></strong>. Digite sua nova senha abaixo:</p>

    <form action="alterar_senha.php" method="POST" onsubmit="return validar()">
        <label for="nova_senha">Nova senha</label>
        <input type="password" id="nova_senha" name="nova_senha" required>

        <label for="confirmar_senha">Confirmar nova senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>

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

    return true;
}

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
