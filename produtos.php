<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Produtos</title>

  <!-- Ícones Material -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

  <style>
    /* Sidebar base */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      background-color: #292929;
      color: #f7b975;
      font-family: "Arial Rounded MT Bold", Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: width 0.3s ease;
      overflow: hidden;
      width: 230px; /* Largura expandida */
      z-index: 1100;
    }

    /* Sidebar recolhida */
    .sidebar.collapsed {
      width: 70px;
    }

    /* Logo */
    .sidebar-logo {
      margin: 1rem 0 2rem;
      cursor: pointer;
      text-align: center;
      user-select: none;
      transition: all 0.3s ease;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* Logo imagem */
    .sidebar-logo img {
      width: 100px;
      transition: width 0.3s ease;
    }

    /* Logo menor quando recolhida */
    .sidebar.collapsed .sidebar-logo img {
      width: 40px;
    }

    /* Logo texto visível só expandido */
    .sidebar-logo span {
      margin-top: 0.5rem;
      font-weight: bold;
      font-size: 1.5rem;
      color: #f7b975;
      white-space: nowrap;
      transition: opacity 0.3s ease;
    }

    .sidebar.collapsed .sidebar-logo span {
      opacity: 0;
      height: 0;
      overflow: hidden;
    }

    /* Menu itens */
    .menu-item {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      cursor: pointer;
      color: #f7b975;
      font-size: 1rem;
      text-decoration: none;
      border-radius: 6px;
      user-select: none;
      transition: background-color 0.3s ease;
      white-space: nowrap;
    }

    .menu-item:hover {
      background-color: #f7b975;
      color: #292929;
    }

    /* Ícone dos menus */
    .menu-icon {
      font-size: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 24px;
      text-align: center;
    }

    /* Esconde texto quando recolhido */
    .sidebar.collapsed .menu-item span.text {
      display: none;
    }

    /* Ajusta padding para os ícones quando recolhido */
    .sidebar.collapsed .menu-item {
      justify-content: center;
      padding: 12px 0;
    }

    /* Conteúdo principal ajusta o padding conforme sidebar */
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      padding-left: 230px;
      box-sizing: border-box;
      transition: padding-left 0.3s ease;
      color: #292929;
    }

    /* Ajusta o padding do corpo quando sidebar recolhida */
    body.sidebar-collapsed {
      padding-left: 70px;
    }

    /* ========= Estilos originais da sua tabela e conteúdo que você enviou no começo ========= */

    table {
      width: 100%;
      max-width: 1200px;
      margin: 40px auto;
      border-collapse: collapse;
      font-family: Arial, sans-serif;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      background-color: #fefefe;
      border-radius: 8px;
      overflow: hidden;
    }

    thead tr {
      background-color: #f7b975;
      color: #292929;
      text-align: center;
      font-weight: bold;
      font-family: "Arial Rounded MT Bold", Arial, sans-serif;
    }

    thead th {
      padding: 12px 15px;
      border: 1px solid #ddd;
      font-size: 1rem;
    }

    tbody tr {
      border-bottom: 1px solid #ddd;
      transition: background-color 0.3s ease;
      cursor: default;
    }

    tbody tr:hover {
      background-color: #f7b975;
      color: #292929;
      font-weight: bold;
    }

    tbody td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      font-size: 0.95rem;
      text-align: center;
    }

    /* Esconde colunas com classe .hidden */
    .hidden {
      display: none;
    }

  </style>
</head>
<body>
  <!-- Menu lateral fixo -->
  <nav class="sidebar" id="sidebar">
    <div class="sidebar-logo" id="sidebar-logo">
      <img src="img/Logopadaria.png" alt="Padaria do Alemão" />
      <span>Padaria do Alemão</span>
    </div>

    <a href="produtos.php" class="menu-item">
      <span class="material-icons menu-icon">bakery_dining</span>
      <span class="text">Produtos</span>
    </a>
    <a href="funcionarios.php" class="menu-item">
      <span class="material-icons menu-icon">person</span>
      <span class="text">Funcionários</span>
    </a>
    <a href="fornecedores.php" class="menu-item">
      <span class="material-icons menu-icon">work</span>
      <span class="text">Fornecedores</span>
    </a>
    <a href="vendas.php" class="menu-item">
      <span class="material-icons menu-icon">analytics</span>
      <span class="text">Vendas</span>
    </a>
    <a href="pagamento.php" class="menu-item">
      <span class="material-icons menu-icon">shopping_cart</span>
      <span class="text">Pagamento</span>
    </a>
  </nav>

  <main>
    <table>
      <thead>
        <tr>
          <th>ID FORNECEDOR</th>
          <th>ID</th>
          <th>Nome do produto</th>
          <th>Preço</th>
          <th>Quantidade</th>
          <th class="action-header hidden">Ações</th>
        </tr>
      </thead>
      <tbody id="supplier-table-body">
        <!-- Conteúdo da tabela exatamente igual ao seu código original -->
        <tr>
          <td>1</td>
          <td>101</td>
          <td>Pão Francês</td>
          <td>R$ 1,50</td>
          <td>200</td>
          <td class="hidden"></td>
        </tr>
        <tr>
          <td>2</td>
          <td>102</td>
          <td>Rosca Doce</td>
          <td>R$ 3,00</td>
          <td>50</td>
          <td class="hidden"></td>
        </tr>
        <tr>
          <td>3</td>
          <td>103</td>
          <td>Bolo de Chocolate</td>
          <td>R$ 15,00</td>
          <td>30</td>
          <td class="hidden"></td>
        </tr>
      </tbody>
    </table>
  </main>

  <script>
    // Toggle da sidebar no clique da logo
    const sidebar = document.getElementById('sidebar');
    const logo = document.getElementById('sidebar-logo');
    const body = document.body;

    logo.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      body.classList.toggle('sidebar-collapsed');
    });
  </script>
</body>
</html>
