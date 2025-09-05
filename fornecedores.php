<?php 
session_start();
require_once 'conexao.php';

// Se não estiver logado
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit;
}

// Se não for administrador
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Fornecedores - Padaria do Alemão</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
:root {
    --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
    --primary-text: #f8f9fa;
    --hover-bg: #1e3a5f;
    --main-bg: rgb(59, 75, 93);
    --card-bg: #ffffff;
    --accent: #1b263b;
    --highlight: #0077b6;
}

/* Reset e corpo */
* { box-sizing: border-box; margin:0; padding:0; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
body { background-color: var(--main-bg); display: flex; }

/* Sidebar */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    transition: width 0.3s;
    box-shadow: 3px 0 10px rgba(0,0,0,0.3);
}
.sidebar.collapsed { width: 60px; }
.sidebar a {
    display: flex;
    align-items: center;
    color: var(--primary-text);
    text-decoration: none;
    padding: 15px 20px;
    white-space: nowrap;
    transition: background 0.2s, padding 0.3s;
}
.sidebar a:hover { background: var(--hover-bg); border-left: 4px solid var(--highlight); padding-left: 16px; }
.sidebar .icon { margin-right: 8px; }
.sidebar.collapsed .text { display: none; }
.sidebar.collapsed .icon { margin-right: 0; justify-content: center; }
.toggle-btn { cursor: pointer; text-align: center; margin-bottom: 20px; font-size: 22px; color: var(--primary-text); }

/* Main Content */
.main-content { margin-left: 240px; padding: 20px 30px; width: 100%; transition: margin-left 0.3s; }
.main-content.collapsed { margin-left: 60px; }

/* Título */
h1 { text-align: center; margin-bottom: 20px; color:#ffffff; }

/* Topo (pesquisa + ações) */
.topo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 10px;
}
.topo > div:first-child { flex:1; display:flex; justify-content:center; }
.search-container {
    background:#fff;
    display:flex;
    align-items:center;
    border-radius:25px;
    padding:0 10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
    max-width:500px;
    flex:1;
}
.search-container input { border:none; outline:none; padding:0.5rem; flex:1; }
.search-container button {
    background:#0077b6;
    border:none;
    padding:6px 14px;
    border-radius:20px;
    color:#fff;
    cursor:pointer;
    font-weight:500;
    margin-left:6px;
}
.search-container button:hover { background:#023e8a; }

.actions-top {
    display:flex;
    align-items:center;
    gap:10px;
}
.add-btn { background:#2ecc71; border-radius:50%; padding:8px; color:#fff; display:inline-block; transition:0.3s; }
.add-btn:hover { background:#27ae60; }
.edit-toggle { color:#0d1b2a; cursor:pointer; font-size:26px; transition:0.2s; }
.edit-toggle:hover { transform:rotate(20deg); }

/* Tabela */
table { width:100%; border-collapse:collapse; background: var(--card-bg); border-radius:12px; overflow:hidden; box-shadow:0 3px 8px rgba(0,0,0,0.15); }
thead { background: var(--accent); color: var(--primary-text); }
thead th { padding:14px 10px; text-align:left; font-size:0.9rem; font-weight:600; letter-spacing:0.5px; }
tbody td { padding:12px 10px; border-bottom:1px solid #eee; font-size:0.9rem; }
tbody tr:nth-child(even) { background:#f9fbfd; }
tbody tr:hover { background: var(--highlight); color:#fff; transition:0.2s; }
.action-cell { text-align:center; }
.hidden { display:none; }

/* Botões de ação */
.icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f1f3f5;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.icon-btn span { font-size: 20px; color: #1b263b; }
.icon-btn:hover { transform: scale(1.1); }

/* Editar */
.icon-btn.edit-btn { background: #0077b6; }
.icon-btn.edit-btn span { color:#fff; }
.icon-btn.edit-btn:hover { background:#023e8a; }

/* Excluir */
.icon-btn.delete-btn { background:#e63946; }
.icon-btn.delete-btn span { color:#fff; }
.icon-btn.delete-btn:hover { background:#c1121f; }

/* Responsividade */
@media(max-width:768px){
  .main-content { margin-left:0; padding:1rem; }
  table { font-size:0.8rem; }
  thead { display:none; }
  tbody td { display:block; text-align:right; padding:8px; border:none; border-bottom:1px solid #eee; }
  tbody td::before { content: attr(data-label); float:left; font-weight:600; color:#555; }
  .topo { flex-direction: column; gap:10px; align-items: stretch; }
  .actions-top { justify-content:flex-end; }
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
    <a href="estoque.php"><span class="material-icons icon">analytics</span><span class="text">Estoque</span></a>
    <a href="relatorio_vendas_padaria_alemao1.php"><span class="material-icons icon">analytics</span><span class="text">Vendas</span></a>
    <a href="comanda.php"><span class="material-icons">receipt_long</span> Comanda</a>

</nav>

<main class="main-content" id="mainContent">
<h1>FORNECEDORES</h1>

<div class="topo">
    <div>
        <div class="search-container">
            <span class="material-icons">search</span>
            <input type="text" id="search-input" placeholder="Pesquisar...">
            <button id="search-btn" type="button">Pesquisar</button>
        </div>
    </div>
    <div class="actions-top">
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
<tbody id="forn-table-body">
<?php
$sql = "SELECT ID_forn, Nome_forn, Telefone, Email, CNPJ FROM fornecedores";
$stmt = $pdo->query($sql);
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($fornecedores as $forn): ?>
<tr>
    <td data-label="ID"><?= htmlspecialchars($forn['ID_forn']) ?></td>
    <td data-label="Fornecedor"><?= htmlspecialchars($forn['Nome_forn']) ?></td>
    <td data-label="Telefone"><?= htmlspecialchars($forn['Telefone']) ?></td>
    <td data-label="Email"><?= htmlspecialchars($forn['Email']) ?></td>
    <td data-label="CNPJ"><?= htmlspecialchars($forn['CNPJ']) ?></td>
    <td class="action-cell hidden">
        <a href="alterar/alterar_fornecedor.php?id=<?= $forn['ID_forn'] ?>" class="icon-btn edit-btn" title="Editar">
            <span class="material-icons">edit</span>
        </a>
        <form action="exclusoes/excluir_fornecedor.php" method="POST" style="display:inline;">
            <input type="hidden" name="id" value="<?= htmlspecialchars($forn['ID_forn']) ?>">
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

document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('edit-toggle');
  const actionHeader = document.querySelector('th.action-header');
  const addButton = document.getElementById('add-button');
  const tableBody = document.getElementById('forn-table-body');

  const getActionCells = () => document.querySelectorAll('td.action-cell');

  toggleBtn.addEventListener('click', () => {
    actionHeader.classList.toggle('hidden');
    getActionCells().forEach(td => td.classList.toggle('hidden'));
    addButton.classList.toggle('hidden');
  });

  const searchInput = document.getElementById("search-input");
  const searchBtn = document.getElementById("search-btn");

  function doSearch() {
    const term = searchInput.value.trim().toLowerCase();
    Array.from(tableBody.rows).forEach(row => {
      const match = Array.from(row.cells).slice(0,5).some(td => td.textContent.toLowerCase().includes(term));
      row.style.display = match ? '' : 'none';
      const cell = row.querySelector('td.action-cell');
      if (cell) cell.classList.toggle('hidden', !match || actionHeader.classList.contains('hidden'));
    });
  }

  searchBtn.addEventListener('click', doSearch);
  searchInput.addEventListener('input', doSearch);
});
</script>

</body>
</html>
