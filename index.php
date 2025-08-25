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

    // Debug: ver os valores da senha e resultado do password_verify
    if ($usuario ) {
        $_SESSION['funcionario'] = $usuario['ID_func'];
        $_SESSION['nivel'] = $usuario['nivel_de_acesso'];
        $_SESSION['nome_func'] = $usuario['Nome_func'];
        header("Location: inicial1.php");
        exit;
    } else {
        $erro = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padaria do Alemão - Login</title>
    <link rel="stylesheet" type="text/css" href="css/site.css">
</head>
<body>
    <header>
        <h1>LOGIN</h1>
        <img src="img/Logopadaria.png" title="Logo Padaria" alt="Logo da Padaria">
    </header>

    <main>
        <form id="form" action="" method="POST">
            <label for="Email">E-mail:</label>
            <input type="email" name="Email" id="Email" class="inputs required" required>

            <label for="Senha">Senha:</label>
            <input type="password" id="Senha" name="Senha" required>

            <?php if ($erro): ?>
                <p style="color:red;">E-mail ou senha incorretos.</p>
            <?php endif; ?>

            <button type="submit">Entrar</button>
            <a href="RECUP_SENHA.php" class="esqueceu-senha">Esqueceu a senha?</a>
        </form>
    </main>

    <footer>
        <div class="fonte">
            <strong>Copyright &copy; Alemões</strong>
        </div>
    </footer>
</body>
</html>
