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
  <meta charset="UTF-8" />
  <title>Fornecedores - Padaria do Alemão</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    /* ===== Reset e base ===== */
    * { margin:0; padding:0; box-sizing:border-box; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
    body { background:#f5f7fa; color:#333; line-height:1.5; }

    /* ===== Sidebar ===== */
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
    .menu-item:hover, .menu-item.active {
      background:rgba(255,255,255,0.1);
    }

    /* ===== Topo/Header ===== */
    header {
      background: linear-gradient(90deg, #2c3e50, #34495e);
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
      font-size:2.0rem;
    }
    .topo-center {
      flex:1;
      display:flex;
      justify-content:center;
      margin-top:10px;
    }
    .topo-right {
      display:flex;
      align-items:center;
      gap:10px;
    }

    /* ===== Botões ===== */
    .icon-btn {
      background:none;
      border:none;
      cursor:pointer;
      font-size:22px;
      transition:0.2s;
      padding:4px;
      border-radius:4px;
      color:#fff;
    }
    .icon-btn:hover {
      color:#2980b9;
      transform:scale(1.2);
    }
    .add-btn {
      background:#2ecc71;
      border-radius:50%;
      padding:8px;
      color:#fff;
      display:inline-block;
      transition:0.3s;
      text-decoration:none;
    }
    .add-btn:hover {
      background:#27ae60;
    }
    .edit-toggle {
      cursor:pointer;
      font-size:26px;
      color:#fff;
      transition:0.2s;
    }
    .edit-toggle:hover {
      transform:rotate(20deg);
    }

    /* ===== Search ===== */
    .search-container {
      background:#fff;
      display:flex;
      align-items:center;
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
      font-size:1rem;
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
      font-size:1rem;
      transition: background 0.3s;
    }
    .search-container button:hover {
      background:#2980b9;
    }

    /* ===== Main/Tabela ===== */
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
      font-weight:600;
    }
    tbody td {
      padding:12px 10px;
      border-bottom:1px solid #eee;
      font-size:0.9rem;
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
    .delete-btn {
      color:#e74c3c;
      font-size:22px;
    }
    .delete-btn:hover {
      color:#c0392b;
    }
    .undo-btn {
      cursor: pointer;
      color: #007700;
      font-size:22px;
      background:none;
      border:none;
      padding:4px;
      border-radius:4px;
      margin-left:5px;
      display:none;
      transition: color 0.3s;
    }
    .undo-btn:hover {
      color: #004400;
    }
    .hidden {
      display:none !important;
    }
    .disabled-row {
      opacity: 0.5;
      text-decoration: line-through;
    }

    /* ===== Responsividade ===== */
    @media(max-width:768px){
      header {
        margin-left:0;
      }
      main {
        margin-left:0;
        padding:1rem;
      }
      .topo {
        flex-direction: column;
        gap: 1rem;
      }
      table {
        font-size: 0.8rem;
      }
      thead {
        display:none;
      }
      tbody td {
        display:block;
        text-align:right;
        padding:8px;
        border:none;
        border-bottom:1px solid #eee;
        position: relative;
      }
      tbody td::before {
        content: attr(data-label);
        float:left;
        font-weight:600;
        color:#555;
      }
      .action-cell {
        text-align:right;
      }
    }
  </style>
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">
    <img src="img/Logopadaria.png" alt="Padaria do Alemão" style="height:34px; width:auto;">
    <span>Padaria do Alemão</span>
  </div>
  <a href="produtos.php" class="menu-item"><span class="material-icons">bakery_dining</span><span>Produtos</span></a>
  <a href="funcionarios.php" class="menu-item"><span class="material-icons">person</span><span>Funcionários</span></a>
  <a href="fornecedores.php" class="menu-item active"><span class="material-icons">work</span><span>Fornecedores</span></a>
  <a href="vendas.php" class="menu-item"><span class="material-icons">analytics</span><span>Vendas</span></a>
  <a href="pagamento.php" class="menu-item"><span class="material-icons">shopping_cart</span><span>Pagamento</span></a>
  <a href="inicial1.php" class="menu-item">Tela principal</a>
</nav>

<header>
  <div class="topo" style="align-items: center;">
    <div class="topo-left">
      <a href="inicial1.php" title="Voltar" style="color:#fff; text-decoration:none;">
        <span class="material-icons">arrow_back</span>
      </a>
    </div>

    <div class="topo-center" style="flex-direction: column; align-items: center; flex: 1; margin-top:-5px;" >
      <h1 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 0.4rem;">FORNECEDORES</h1>
      <div class="search-container">
        <span class="material-icons">search</span>
        <form action="pesquisar/buscar_fornecedor.php" method="POST" style="margin:0; display:flex; flex:1; width: 100%;">
          <input type="text" id="search-input" placeholder="Pesquisar..." name="search" autocomplete="off" />
          <button id="search-btn" type="button">Pesquisar</button>
        </form>
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
            <td data-label="ID"><?= htmlspecialchars($fornecedor['ID_forn']) ?></td>
            <td data-label="Fornecedor"><?= htmlspecialchars($fornecedor['Nome_forn']) ?></td>
            <td data-label="Telefone"><?= htmlspecialchars($fornecedor['Telefone']) ?></td>
            <td data-label="Email"><?= htmlspecialchars($fornecedor['Email']) ?></td>
            <td data-label="CNPJ"><?= htmlspecialchars($fornecedor['CNPJ']) ?></td>
            <td class="action-cell hidden">
              <a href="alterar/alterar_fornecedor.php?id=<?= $fornecedor['ID_forn'] ?>" class="icon-btn" title="Editar">
                <span class="material-icons">edit</span>
              </a>
              <form action="exclusoes/excluir_fornecedor.php" method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($fornecedor['ID_forn']) ?>">
                <button type="submit" class="icon-btn delete-btn" onclick="return confirm('Deseja realmente excluir este fornecedor?')">
                  <span class="material-icons">delete</span>
                </button>
              </form>
            </td>
          </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<script>
  // Alternar exibição da coluna de ações e botão adicionar
  document.getElementById("edit-toggle").addEventListener("click", function() {
    const actionHeaders = document.querySelectorAll(".action-header");
    const actionCells = document.querySelectorAll(".action-cell");
    const addButton = document.getElementById('add-button');
    actionHeaders.forEach(header => header.classList.toggle("hidden"));
    actionCells.forEach(cell => cell.classList.toggle("hidden"));
    addButton.classList.toggle("hidden");
  });

  // Filtro de busca local
  const searchInput = document.getElementById("search-input");
  const searchBtn = document.getElementById("search-btn");
  const tableBody = document.getElementById("supplier-table-body");

  function doSearch() {
    const term = searchInput.value.trim().toLowerCase();
    Array.from(tableBody.rows).forEach(row => {
      const cells = Array.from(row.cells);
      const match = cells.slice(0, 5) // ID, Nome, Telefone, Email, CNPJ
        .some(td => td.textContent.toLowerCase().includes(term));
      row.style.display = match ? '' : 'none';
    });
  }

  searchBtn.addEventListener('click', doSearch);
  searchInput.addEventListener('input', doSearch);

  // Função para criar botão Undo para exclusão temporária
  function createUndoButton() {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'undo-btn icon-btn';
    btn.title = 'Desfazer exclusão';
    btn.innerHTML = '<span class="material-icons">undo</span>';
    btn.style.display = 'none';
    return btn;
  }

  // Inicializa botões editar e delete
  function initActionButtons() {
    document.querySelectorAll('td.action-cell').forEach(cell => {
      const deleteBtn = cell.querySelector('.delete-btn');
      if (!deleteBtn) return;

      // Evitar adicionar várias vezes
      if (cell.querySelector('.undo-btn')) return;

      const undoBtn = createUndoButton();
      cell.appendChild(undoBtn);

      deleteBtn.addEventListener('click', function(e) {
        // Confirmação já no HTML com onclick confirm, então aqui só estiliza
        // Deixa botão deletar escondido e mostra Undo
        e.preventDefault();
        const tr = cell.parentElement;
        tr.classList.add('disabled-row');
        deleteBtn.style.display = 'none';
        undoBtn.style.display = 'inline-block';

        // Remove formulário da submissão real para simular exclusão temporária
        tr.dataset.deleted = 'true';
      });

      undoBtn.addEventListener('click', function() {
        const tr = cell.parentElement;
        tr.classList.remove('disabled-row');
        deleteBtn.style.display = 'inline-block';
        undoBtn.style.display = 'none';
        delete tr.dataset.deleted;
      });
    });
  }

  // Ativa/desativa ações ao carregar e ao clicar no toggle
  document.getElementById("edit-toggle").addEventListener("click", initActionButtons);
  window.onload = () => {
    // Inicializa sem mostrar ações, então não precisa chamar initActionButtons aqui
  };

</script>

</body>
</html>
