<?php 
session_start();
require_once 'conexao.php';

// Verifica login e nível
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='login.php';</script>";
    exit;
}

if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}

$fornecedores = [];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Fornecedores - Padaria do Alemão</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
    /* ===== Reset e base ===== */
    * { margin:0; padding:0; box-sizing:border-box; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
    body { background:#f5f7fa; color:#333; line-height:1.5; }

    /* ===== Sidebar ===== */
    .sidebar { width:220px; background:#2c3e50; position:fixed; top:0; left:0; bottom:0; padding-top:1rem; overflow:hidden; }
    .sidebar-logo { display:flex; align-items:center; gap:10px; padding:0 1rem 1rem; color:white; font-weight:10; }
    .menu-item { display:flex; align-items:center; gap:10px; padding:0.8rem 1rem; color:#fff; text-decoration:none; transition:background 0.2s; }
    .menu-item:hover, .menu-item.active { background:rgba(255,255,255,0.1); }

    /* ===== Topo/Header ===== */
    header { background: linear-gradient(90deg, #2c3e50, #34495e); color:#fff; padding:0.8rem 1.5rem; box-shadow:0 2px 6px rgba(0,0,0,0.2); margin-left:220px; }
    .topo { display:flex; align-items:center; justify-content:space-between; flex-wrap: wrap; }
    .topo-left { display:flex; align-items:center; gap:15px; }
    .topo-left h1 { font-size:1.5rem; }
    .topo-center { flex:1; display:flex; justify-content:center; margin-top:10px; }

    /* ===== Botões ===== */
    .icon-btn { background:none; border:none; cursor:pointer; font-size:22px; transition:0.2s; }
    .icon-btn:hover { color:#2980b9; transform:scale(1.2); }
    .add-btn { background:#2ecc71; border-radius:50%; padding:8px; color:#fff; display:inline-block; }
    .add-btn:hover { background:#27ae60; }
    .edit-toggle { color:#fff; cursor:pointer; font-size:26px; transition:0.2s; }
    .edit-toggle:hover { transform:rotate(20deg); }

    /* ===== Search ===== */
    .search-container { background:#fff; display:flex; align-items:center; border-radius:25px; padding:0 10px; box-shadow:0 1px 3px rgba(0,0,0,0.2); max-width:350px; margin:0 auto 20px; }
    .search-container span { color:#888; }
    .search-container input { border:none; outline:none; padding:0.5rem; flex:1; }
    .search-container button { background:#3498db; border:none; padding:6px 14px; border-radius:20px; color:#fff; cursor:pointer; font-weight:500; margin-left:6px; }
    .search-container button:hover { background:#2980b9; }

    /* ===== Main/Tabela ===== */
    main { padding:2rem; margin-left:220px; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 3px 8px rgba(0,0,0,0.15); }
    thead { background:#34495e; color:#fff; }
    thead th { padding:14px 10px; text-align:left; font-size:0.9rem; font-weight:600; }
    tbody td { padding:12px 10px; border-bottom:1px solid #eee; font-size:0.9rem; }
    tbody tr:nth-child(even) { background:#f9fbfd; }
    tbody tr:hover { background:#f0f4f8; }
    .action-cell { text-align:center; }
    .delete-btn { color:#e74c3c; }
    .delete-btn:hover { color:#c0392b; }
    .hidden { display:none; }
    .disabled-row { opacity: 0.5; }

    /* ===== Responsividade ===== */
    @media(max-width:768px){
      header { margin-left:0; }
      main { margin-left:0; padding:1rem; }
      .search-container input { width:100px; }
      table { font-size:0.8rem; }
      thead { display:none; }
      tbody td { display:block; text-align:right; padding:8px; }
      tbody td::before { content: attr(data-label); float:left; font-weight:600; color:#555; }
=======
=======
>>>>>>> Stashed changes
    * { margin:0; padding:0; box-sizing:border-box; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
    body { background:#f5f7fa; color:#333; line-height:1.5; }

    .sidebar {
      width:220px;
      background:#2c3e50;
      position:fixed;
      top:0;
      left:0;
      bottom:0;
      padding-top:1rem;
      overflow:hidden;
    }
    .sidebar-logo {
      display:flex;
      align-items:center;
      gap:10px;
      padding:0 1rem 1rem;
      color:white;
      font-weight:600;
    }
    .menu-item {
      display:flex;
      align-items:center;
      gap:10px;
      padding:0.8rem 1rem;
      color:#fff;
      text-decoration:none;
      transition:background 0.2s;
    }
    .menu-item:hover,
    .menu-item.active {
      background:rgba(255,255,255,0.1);
    }

    header {
      background: linear-gradient(90deg,#2c3e50,#34495e);
      color:#fff;
      padding:0.8rem 1.5rem;
      box-shadow:0 2px 6px rgba(0,0,0,0.2);
      margin-left:220px;
    }
    .topo {
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap: wrap;
    }
    .topo-left {
      display:flex;
      align-items:center;
      gap:15px;
    }
    .topo-left h1 {
      font-size:1.5rem;
    }

    .topo-center {
      flex:1;
      display:flex;
      justify-content:center;
      margin-top:10px;
    }

    .search-container {
      display:flex;
      align-items:center;
      background:#fff;
      border-radius:25px;
      padding:0 10px;
      box-shadow:0 1px 3px rgba(0,0,0,0.2);
      max-width:350px;
      width:100%;
    }

    .search-container span {
      color:#888;
    }

    .search-container input {
      border:none;
      outline:none;
      padding:0.5rem;
      flex:1;
    }

    .search-container button {
      background:#3498db;
      border:none;
      padding:6px 14px;
      border-radius:20px;
      color:#fff;
      cursor:pointer;
      font-weight:500;
      margin-left:6px;
    }

    .search-container button:hover {
      background:#2980b9;
    }

    .topo-right {
      display:flex;
      align-items:center;
      gap:10px;
    }

    .add-btn {
      background:#2ecc71;
      border-radius:50%;
      padding:8px;
      color:#fff;
      transition:0.3s;
      display:inline-block;
    }

    .add-btn:hover {
      background:#27ae60;
    }

    .edit-toggle {
      color:#fff;
      cursor:pointer;
      font-size:26px;
    }

    main {
      padding:2rem;
      margin-left:220px;
    }

    table {
      width:100%;
      border-collapse:collapse;
      background:#fff;
      border-radius:12px;
      overflow:hidden;
      box-shadow:0 3px 8px rgba(0,0,0,0.15);
    }

    thead {
      background:#34495e;
      color:#fff;
    }

    thead th {
      padding:14px 10px;
      text-align:left;
      font-size:0.9rem;
    }

    tbody td {
      padding:12px 10px;
      border-bottom:1px solid #eee;
    }

    tbody tr:nth-child(even) {
      background:#f9fbfd;
    }

    tbody tr:hover {
      background:#f0f4f8;
    }

    .action-cell {
      text-align:center;
    }

    .disabled-row {
      opacity:0.5;
      text-decoration:line-through;
    }

    .icon-btn {
      border: none;
      background: none;
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
    }

    .undo-btn {
      display: none;
      cursor: pointer;
      color: #007700;
    }

    .hidden {
      display: none !important;
    }

    @media(max-width:768px){
      header { margin-left:0; }
      main { margin-left:0; padding:1rem; }
      .topo { flex-direction:column; gap:1rem; }
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
    }
  </style>
</head>
<body>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
<nav class="sidebar">
  <div class="sidebar-logo">
  <img src="img/Logopadaria.png" alt="Padaria do Alemão" style="height:34px; width:auto;">

    <span style="color:white;">Padaria do Alemão</span>
  </div>
  <a href="produtos.php" class="menu-item"><span class="material-icons">bakery_dining</span><span>Produtos</span></a>
  <a href="funcionarios.php" class="menu-item"><span class="material-icons">person</span><span>Funcionários</span></a>
  <a href="fornecedores.php" class="menu-item active"><span class="material-icons">work</span><span>Fornecedores</span></a>
  <a href="vendas.php" class="menu-item"><span class="material-icons">analytics</span><span>Vendas</span></a>
  <a href="pagamento.php" class="menu-item"><span class="material-icons">shopping_cart</span><span>Pagamento</span></a>
  <a href="inicial1.php" class="menu-item">Tela principal</a>
</nav>

=======
=======
>>>>>>> Stashed changes
<!-- Sidebar -->
<nav class="sidebar">
  <div class="sidebar-logo">
    <span class="material-icons">local_cafe</span> Padaria
  </div>
  <a href="inicial1.php" class="menu-item"><span class="material-icons">home</span> Início</a>
  <a href="fornecedores.php" class="menu-item active"><span class="material-icons">store</span> Fornecedores</a>
  <a href="produtos.php" class="menu-item"><span class="material-icons">inventory</span> Produtos</a>
  <a href="relatorios.php" class="menu-item"><span class="material-icons">bar_chart</span> Relatórios</a>
  <a href="logout.php" class="menu-item"><span class="material-icons">logout</span> Sair</a>
</nav>

<!-- Header -->
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
<header>
  <div class="topo">
    <div class="topo-left">
      <a href="inicial1.php" title="Voltar"><span class="material-icons">arrow_back</span></a>
    </div>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
    <div class="topo-center">
      <h1>FORNECEDORES</h1>
      <div class="search-container">
        <span class="material-icons">search</span>
        <form action="pesquisar/buscar_fornecedor.php" method="POST" style="margin:0;">
          <input type="text" id="search-input" placeholder="Pesquisar..." name="search">
          <button id="search-btn" type="button">Pesquisar</button>
        </form>
      </div>
    </div>

    <div class="topo-right">
      <a href="cadforn.php" id="add-button" class="add-btn" title="Adicionar">
        <span class="material-icons">add</span>
      </a>
      <span class="material-icons edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
    </div>
  </div>
</header>

<main>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Fornecedor</th>
        <th>Telefone</th>
        <th>Email</th>
        <th>CNPJ</th>
        
        <th class="action-header hidden">Ações</th>
      </tr>
    </thead>
    <tbody id="supplier-table-body">
  <?php

     $sql = "SELECT ID_forn, Nome_forn, Telefone, Email, CNPJ FROM fornecedores";
     $stmt = $pdo->query($sql);
     $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($fornecedores as $fornecedor): ?>
    <tr>
      <td data-label='ID'><?= htmlspecialchars($fornecedor['ID_forn']) ?></td>
      <td data-label='Fornecedor'><?= htmlspecialchars($fornecedor['Nome_forn']) ?></td>
      <td data-label='Telefone'><?= htmlspecialchars($fornecedor['Telefone']) ?></td>
      <td data-label='Email'><?= htmlspecialchars($fornecedor['Email']) ?></td>
      <td data-label='CNPJ'><?= htmlspecialchars($fornecedor['CNPJ']) ?></td>
      <td class="action-cell hidden">
      <a href="alterar/alterar_fornecedor.php?id=<?= $fornecedor['ID_forn'] ?>" class="icon-btn" title="Editar">
            <span class="material-icons">edit</span>
          </a>
          <form action="exclusoes/excluir_fornecedor.php" method="POST" style="display:inline;">
            <input type="hidden" name="id" value="<?= htmlspecialchars($fornecedor['ID_forn']) ?>">
            <button type="submit" class="icon-btn delete-btn" onclick="return confirm('Deseja realmente excluir este fornecedor?')">
              <span class="material-icons">delete</span>
      </td>
    </tr>
  <?php endforeach; ?>
</tbody>

<script>
// Alternar exibição da coluna de ações
document.getElementById("edit-toggle").addEventListener("click", function() {
  const actionHeaders = document.querySelectorAll(".action-header");
  const actionCells = document.querySelectorAll(".action-cell");
  actionHeaders.forEach(header => header.classList.toggle("hidden"));
  actionCells.forEach(cell => cell.classList.toggle("hidden"));
});

// Filtro de busca local (se preferir usar no front-end)
document.getElementById("search-btn").addEventListener("click", function() {
  const searchInput = document.getElementById("search-input").value.toLowerCase();
  const rows = document.querySelectorAll("#supplier-table-body tr");

  rows.forEach(row => {
    const rowText = row.textContent.toLowerCase();
    row.style.display = rowText.includes(searchInput) ? "" : "none";
  });
});
</script>

=======
    <div class="topo-center" style="flex-direction: column; align-items: center;">
      <h1 style="color: white; margin-bottom: 10px;">FORNECEDORES</h1>
      <div class="search-container">
        <span class="material-icons">search</span>
        <form action="pesquisar/buscar_fornecedor.php"
        <input type="text" id="search-input" placeholder="Pesquisar...">
        <button id="search-btn" type="button">Pesquisar</button>
      </div>
    </div>


    <div class="topo-right">
      <a href="cadforn.php" id="add-button" class="add-btn hidden" title="Adicionar">
        <span class="material-icons">add</span>
      </a>
      <span class="material-icons edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
    </div>
  </div>
</header>

<!-- Main -->
<main>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Fornecedores</th>
        <th>Produto fornecido</th>
        <th>Reabastecimento</th>
        <th class="action-header hidden">Ações</th>
      </tr>
    </thead>
    <tbody id="supplier-table-body">
      <tr>
        <td>FR01</td><td>CARLINHOS</td><td>Trigo, leite...</td><td>12/03/2025</td>
        <td class="action-cell hidden">
          <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
          <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
          <button class="undo-btn"><span class="material-icons">undo</span></button>
        </td>
      </tr>
      <tr>
        <td>FR02</td><td>BOLOS MAIDEN</td><td>Bolos, açúcares...</td><td>08/03/2025</td>
        <td class="action-cell hidden">
          <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
          <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
          <button class="undo-btn"><span class="material-icons">undo</span></button>
        </td>
      </tr>
      <!-- Adicione mais linhas conforme necessário -->
    </tbody>
  </table>
</main>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('edit-toggle');
    const actionHeader = document.querySelector('th.action-header');
    const tableBody = document.getElementById('supplier-table-body');
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const addButton = document.getElementById('add-button');

    const getActionCells = () => document.querySelectorAll('td.action-cell');

=======
    <div class="topo-center" style="flex-direction: column; align-items: center;">
      <h1 style="color: white; margin-bottom: 10px;">FORNECEDORES</h1>
      <div class="search-container">
        <span class="material-icons">search</span>
        <form action="pesquisar/buscar_fornecedor.php"
        <input type="text" id="search-input" placeholder="Pesquisar...">
        <button id="search-btn" type="button">Pesquisar</button>
      </div>
    </div>


    <div class="topo-right">
      <a href="cadforn.php" id="add-button" class="add-btn hidden" title="Adicionar">
        <span class="material-icons">add</span>
      </a>
      <span class="material-icons edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
    </div>
  </div>
</header>

<!-- Main -->
<main>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Fornecedores</th>
        <th>Produto fornecido</th>
        <th>Reabastecimento</th>
        <th class="action-header hidden">Ações</th>
      </tr>
    </thead>
    <tbody id="supplier-table-body">
      <tr>
        <td>FR01</td><td>CARLINHOS</td><td>Trigo, leite...</td><td>12/03/2025</td>
        <td class="action-cell hidden">
          <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
          <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
          <button class="undo-btn"><span class="material-icons">undo</span></button>
        </td>
      </tr>
      <tr>
        <td>FR02</td><td>BOLOS MAIDEN</td><td>Bolos, açúcares...</td><td>08/03/2025</td>
        <td class="action-cell hidden">
          <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
          <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
          <button class="undo-btn"><span class="material-icons">undo</span></button>
        </td>
      </tr>
      <!-- Adicione mais linhas conforme necessário -->
    </tbody>
  </table>
</main>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('edit-toggle');
    const actionHeader = document.querySelector('th.action-header');
    const tableBody = document.getElementById('supplier-table-body');
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const addButton = document.getElementById('add-button');

    const getActionCells = () => document.querySelectorAll('td.action-cell');

>>>>>>> Stashed changes
    toggleBtn.addEventListener('click', () => {
      actionHeader.classList.toggle('hidden');
      getActionCells().forEach(td => td.classList.toggle('hidden'));
      addButton.classList.toggle('hidden');
    });

    function doSearch() {
      const term = searchInput.value.trim().toLowerCase();
      Array.from(tableBody.rows).forEach(row => {
        const match = Array.from(row.cells).slice(0, 4)
          .some(td => td.textContent.toLowerCase().includes(term));
        row.style.display = match ? '' : 'none';
      });
    }

    searchBtn.addEventListener('click', doSearch);
    searchInput.addEventListener('input', doSearch);

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.onclick = () => {
        const row = btn.closest('tr');
        const icon = btn.querySelector('.material-icons');
        const editing = row.classList.toggle('editing');
        icon.textContent = editing ? 'save' : 'edit';
        const numCols = row.cells.length - 1;
        for (let i = 0; i < numCols; i++) {
          const cell = row.cells[i];
          if (editing) {
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.value = cell.textContent;
            cell.textContent = '';
            cell.appendChild(inp);
          } else {
            const inp = cell.querySelector('input');
            if (inp) cell.textContent = inp.value;
          }
        }
      };
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.onclick = () => {
        const row = btn.closest('tr');
        row.classList.add('disabled-row');
        const deleteBtn = row.querySelector('.delete-btn');
        const undoBtn = row.querySelector('.undo-btn');

        deleteBtn.style.display = 'none';
        undoBtn.style.display = 'inline-block';

        undoBtn.onclick = () => {
          row.classList.remove('disabled-row');
          deleteBtn.style.display = 'inline-block';
          undoBtn.style.display = 'none';
        };
      };
    });
  });
</script>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
</body>
</html>

