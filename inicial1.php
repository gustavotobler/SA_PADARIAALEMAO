<?php
session_start();
require_once 'conexao.php';

// Se não estiver logado, redireciona para login
if (!isset($_SESSION['ID_func']) || !isset($_SESSION['nivel'])) {
  header('Location: index.php');
  exit();
}

// OBTENDO O NOME DO PERFIL DO USUÁRIO LOGADO
$id_Nome = $_SESSION['ID_func'];
$sqlNome = 'SELECT Nome_func FROM funcionario WHERE ID_func = :ID_func';
$stmtNome = $pdo->prepare($sqlNome);
$stmtNome->bindParam(':ID_func', $id_Nome, PDO::PARAM_INT);
$stmtNome->execute();
$nome = $stmtNome->fetch(PDO::FETCH_ASSOC);
$nomeFunc = $nome['Nome_func'] ?? ($_SESSION['nome_func'] ?? '');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Página Inicial</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background-color: rgb(59, 75, 93);
      height: 100vh;
      display: flex;
      color: #f8f9fa;
    }

    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      opacity: 0.15;
      z-index: -1;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: linear-gradient(180deg, #0d1b2a, #1b263b);
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.3);
    }

    .sidebar img {
      max-width: 120px;
      margin: 20px auto;
      border-radius: 50%;
    }

    .sidebar a {
      color: #f8f9fa;
      padding: 15px 20px;
      text-decoration: none;
      transition: 0.3s;
      display: flex;
      align-items: center;
      font-size: 15px;
      font-weight: 500;
    }

    .sidebar a .material-icons {
      margin-right: 10px;
      font-size: 20px;
    }

    .sidebar a:hover {
      background: #1e3a5f;
      border-left: 4px solid #00b4d8;
      padding-left: 16px;
    }

    .logout {
      margin-top: auto;
      padding: 20px;
    }

    .logout button {
      width: 100%;
      border-radius: 8px;
      font-weight: 600;
      background: transparent;
      color: #f8f9fa;
      border: 1px solid #dc3545;
      padding: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    .logout button:hover {
      background: #dc3545;
      color: #fff;
    }

    /* Conteúdo */
    .main-content {
      margin-left: 240px;
      padding: 40px;
      width: 100%;
    }

    .welcome-card {
      background: #1b263b;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
      max-width: 800px;
      margin: 80px auto;
      text-align: center;
      transition: transform 0.3s;
      margin-top: -10px;
    }

    .welcome-card:hover {
      transform: translateY(-5px);
    }

    .welcome-card h2 {
      font-size: 26px;
      line-height: 1.5;
      color: #f1f1f1;
    }
  </style>
</head>

<body>

  <!-- Menu lateral -->
  <nav class="sidebar">
    <img src="img/Logopadaria.png" alt="Logo da Padaria Alemão">
    <a href="funcionarios.php"><span class="material-icons">group</span> Funcionários</a>
    <a href="fornecedores.php"><span class="material-icons">work</span> Fornecedores</a>
    <a href="estoque.php"><span class="material-icons">inventory_2</span> Estoque</a>
    <a href="comandas_visualizar.php"><span class="material-icons">receipt_long</span> Pagamento</a>

    <div class="logout">
      <a href="index.php"><button type="button">Logout</button></a>
    </div>
  </nav>

  <!-- Conteúdo principal -->
  <main class="main-content">
    <section>
      <div class="welcome-card">
        <h2>Seja bem-vindo <b><?= $nomeFunc ?></b>, ao nosso sistema de gestão!</h2>
      </div>
    </section>
  </main>

</body>

</html>