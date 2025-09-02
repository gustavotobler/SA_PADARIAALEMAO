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
:root {
    --sidebar-bg: #2e2e2e;
    --primary-text: #fff;
    --hover-bg: #444;
    --main-bg: #fcf6eb;
    --card-bg: #fff;
    --accent: #3f3f3f;
    --highlight: #e0f7ff;
}

* { box-sizing: border-box; margin:0; padding:0; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }

body { background: var(--main-bg); display: flex; }

/* ===== Sidebar ===== */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    transition: width 0.3s;
}
.sidebar.collapsed { width: 60px; }
.sidebar a {
    display: flex;
    align-items: center;
    color: var(--primary-text);
    text-decoration: none;
    padding: 15px 20px;
    white-space: nowrap;
    transition: background 0.2s;
}
.sidebar a:hover { background: var(--hover-bg); }
.sidebar .icon { margin-right: 8px; }
.sidebar.collapsed .text { display: none; }
.sidebar.collapsed .icon { margin-right: 0; justify-content: center; }
.toggle-btn { cursor: pointer; text-align: center; margin-bottom: 20px; font-size: 20px; color: var(--primary-text); }

/* ===== Main Content ===== */
.main-content { margin-left: 240px; padding: 20px 30px; width: 100%; transition: margin-left 0.3s; }
.main-content.collapsed { margin-left: 60px; }

/* ===== Topo ===== */
.topo {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.topo-center {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.topo-center h1 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}
.topo-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ===== Botões ===== */
.icon-btn { background:none; border:none; cursor:pointer; font-size:22px; transition:0.2s; }
.icon-btn:hover { color:#2980b9; transform:scale(1.2); }
.add-btn { background:#2ecc71; border-radius:50%; padding:8px; color:#fff; display:inline-block; }
.add-btn:hover { background:#27ae60; }
.edit-toggle { color:#fff; cursor:pointer; font-size:26px; transition:0.2s; }
.edit-toggle:hover { transform:rotate(20deg); }

/* ===== Search ===== */
.search-container { background:#fff; display:flex; align-items:center; border-radius:25px; padding:0 10px; box-shadow:0 1px 3px rgba(0,0,0,0.2); max-width:400px; width:100%; }
.search-container span { color:#888; }
.search-container input { border:none; outline:none; padding:0.5rem; flex:1; }
.search-container button { background:#3498db; border:none; padding:6px 14px; border-radius:20px; color:#fff; cursor:pointer; font-weight:500; margin-left:6px; transition:background 0.2s; }
.search-container button:hover { background:#2980b9; }

/* ===== Tabela ===== */
table { width:100%; border-collapse:collapse; background: var(--card-bg); border-radius:12px; overflow:hidden; box-shadow:0 3px 8px rgba(0,0,0,0.15); }
thead { background: var(--accent); color: var(--primary-text); }
thead th { padding:14px 10px; text-align:left; font-size:0.9rem; font-weight:600; }
tbody td { padding:12px 10px; border-bottom:1px solid #eee; font-size:0.9rem; }
tbody tr:nth-child(even) { background:#f9fbfd; }
tbody tr:hover { background: var(--highlight); }
.action-cell { text-align:center; }
.delete-btn { color:#e74c3c; }
.delete-btn:hover { color:#c0392b; }
.hidden { display:none; }

/* ===== Responsividade ===== */
@media(max-width:768px){
  .main-content { margin-left:0; padding:1rem; }
  .search-container { max-width:100%; }
  table { font-size:0.8rem; }
  thead { display:none; }
  tbody td { display:block; text-align:right; padding:8px; }
  tbody td::before { content: attr(data-label); float:left; font-weight:600; color:#555; }
}
</style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
    <a href="produtos.php"><span class="material-icons icon">bakery_dining</span><span class="text">Produtos</span></a>
    <a href="funcionarios.php"><span class="material-icons icon">person</span><span class="text">Funcionários</span></a>
    <a href="fornecedores.php"><span class="material-icons icon">work</span><span class="text">Fornecedores</span></a>
    <a href="vendas.php"><span class="material-icons icon">analytics</span><span class="text">Vendas</span></a>
    <a href="pagamento.php"><span class="material-icons icon">shopping_cart</span><span class="text">Pagamento</span></a>
</nav>

<main class="main-content" id="mainContent">
  <div class="topo">
    <div class="topo-center">
      <h1>FORNECEDORES</h1>
      <div class="search-container">
        <span class="material-icons">search</span>
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
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

function toggleSidebar(){
  sidebar.classList.toggle('collapsed');
  mainContent.classList.toggle('collapsed');
}

// Toggle ações e botão adicionar
document.getElementById("edit-toggle").addEventListener("click", function() {
    const actionHeaders = document.querySelectorAll(".action-header");
    const actionCells = document.querySelectorAll(".action-cell");
    const addButton = document.getElementById('add-button');
    actionHeaders.forEach(header => header.classList.toggle("hidden"));
    actionCells.forEach(cell => cell.classList.toggle("hidden"));
    addButton.classList.toggle("hidden");
});

// Pesquisa local
const searchInput = document.getElementById("search-input");
const searchBtn = document.getElementById("search-btn");
const tableBody = document.getElementById("supplier-table-body");

function doSearch() {
    const term = searchInput.value.trim().toLowerCase();
    Array.from(tableBody.rows).forEach(row => {
        const match = Array.from(row.cells).slice(0,5)
            .some(td => td.textContent.toLowerCase().includes(term));
        row.style.display = match ? '' : 'none';
    });
}

searchBtn.addEventListener('click', doSearch);
searchInput.addEventListener('input', doSearch);
</script>
</body>
</html>
