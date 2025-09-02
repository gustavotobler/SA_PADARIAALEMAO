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
  <title>Produtos - Padaria do Alemão</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* ===== Reset e base ===== */
    * { margin:0; padding:0; box-sizing:border-box; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
    body { background:#f5f7fa; color:#333; line-height:1.5; }

    /* ===== Sidebar ===== */
    .sidebar { width:220px; background:#2c3e50; position:fixed; top:0; left:0; bottom:0; padding-top:1rem; overflow:hidden; }
    .sidebar-logo { display:flex; align-items:center; gap:10px; padding:0 1rem 1rem; cursor:pointer; }
    .sidebar-logo img { width:40px; }
    .menu-item { display:flex; align-items:center; gap:10px; padding:0.8rem 1rem; color:#fff; text-decoration:none; transition:background 0.2s; }
    .menu-item:hover { background:rgba(255,255,255,0.1); }

    /* ===== Topo/Header ===== */
    header { background: linear-gradient(90deg, #2c3e50, #34495e); color:#fff; padding:0.8rem 1.5rem; box-shadow:0 2px 6px rgba(0,0,0,0.2); margin-left:220px; }
    .topo { display:flex; align-items:center; justify-content:space-between; }
    .topo-center { text-align:center; flex:1; }
    .topo-center h1 { font-size:1.5rem; font-weight:600; margin-bottom:0.4rem; letter-spacing:1px; }

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
    .search-container button { background:#3498db; border:none; padding:6px 14px; border-radius:20px; color:#fff; cursor:pointer; font-weight:500; margin-left:6px; transition:background 0.2s; }
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

    /* ===== Responsividade ===== */
    @media(max-width:768px){
      header { margin-left:0; }
      main { margin-left:0; padding:1rem; }
      .search-container input { width:100px; }
      table { font-size:0.8rem; }
      thead { display:none; }
      tbody td { display:block; text-align:right; padding:8px; }
      tbody td::before { content: attr(data-label); float:left; font-weight:600; color:#555; }
    }
  </style>
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">
    <a href="inicial1.php">
    <img src="img/Logopadaria.png" alt="Padaria do Alemão">
    </a>
    <span style="color:white;">Padaria do Alemão</span>
  
  </div>
  <a href="produtos.php" class="menu-item"><span class="material-icons">bakery_dining</span><span>Produtos</span></a>
  <a href="funcionarios.php" class="menu-item"><span class="material-icons">person</span><span>Funcionários</span></a>
  <a href="fornecedores.php" class="menu-item"><span class="material-icons">work</span><span>Fornecedores</span></a>
  <a href="vendas.php" class="menu-item"><span class="material-icons">analytics</span><span>Vendas</span></a>
  <a href="pagamento.php" class="menu-item"><span class="material-icons">shopping_cart</span><span>Pagamento</span></a>
</nav>

<header>
  <div class="topo">
    <div class="topo-center">
      <h1>PRODUTOS</h1>
      <div class="search-container">
        <span class="material-icons">search</span>
        <input type="text" id="search-input" placeholder="Pesquisar...">
        <button id="search-btn" type="button">Pesquisar</button>
      </div>
    </div>
    <div class="topo-right">
      <a href="cadprod.php" id="add-button" class="hidden">
        <button class="icon-btn add-btn" title="Adicionar">
          <span class="material-icons">add</span>
        </button>
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
        <th>Nome</th>
        <th>Categoria</th>
        <th>Preço</th>
        <th>Unidade</th>
        <th>Validade</th>
        <th>Quantidade</th>
        <th class="action-header hidden">Ação</th>
      </tr>
    </thead>
    <tbody id="prod-table-body">
      <?php
      $sql = "SELECT 
                p.ID_produto, 
                f.Nome_forn, 
                c.nome_categoria,
                p.Nome_prod, 
                p.Preco_unitario, 
                p.Unid_medida, 
                p.Validade, 
                p.Qntd_produto
              FROM produtos p
              LEFT JOIN fornecedores f ON p.ID_forn = f.ID_forn
              LEFT JOIN categorias c ON p.id_categorias = c.id_categorias";
      $stmt = $pdo->query($sql);
      $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($produtos as $prod): ?>
      <tr>
        <td data-label="ID"><?= htmlspecialchars($prod['ID_produto']) ?></td>
        <td data-label="Fornecedor"><?= htmlspecialchars($prod['Nome_forn']) ?></td>        
        <td data-label="Nome"><?= htmlspecialchars($prod['Nome_prod']) ?></td>
        <td data-label="Categoria"><?= htmlspecialchars($prod['nome_categoria']) ?></td>
        <td data-label="Preço"><?= htmlspecialchars($prod['Preco_unitario']) ?></td>
        <td data-label="Unidade"><?= htmlspecialchars($prod['Unid_medida']) ?></td>
        <td data-label="Validade"><?= htmlspecialchars($prod['Validade']) ?></td>
        <td data-label="Quantidade"><?= htmlspecialchars($prod['Qntd_produto']) ?></td>
        <td class="action-cell hidden">
          <a href="alterar/alterar_produtos.php?id=<?= $prod['ID_produto'] ?>" class="icon-btn" title="Editar">
            <span class="material-icons">edit</span>
          </a>
          <form action="exclusoes/excluir_produtos.php" method="POST" style="display:inline;">
            <input type="hidden" name="id" value="<?= htmlspecialchars($prod['ID_produto']) ?>">
            <button type="submit" class="icon-btn delete-btn" onclick="return confirm('Deseja realmente excluir este produto?')">
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
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('edit-toggle');
  const actionHeader = document.querySelector('th.action-header');
  const addButton = document.getElementById('add-button');
  const tableBody = document.getElementById('prod-table-body');
  const searchInput = document.getElementById('search-input');
  const searchBtn = document.getElementById('search-btn');

  const getActionCells = () => document.querySelectorAll('td.action-cell');

  toggleBtn.addEventListener('click', () => {
    actionHeader.classList.toggle('hidden');
    getActionCells().forEach(td => td.classList.toggle('hidden'));
    addButton.classList.toggle('hidden');
  });

  function doSearch() {
    const term = searchInput.value.trim().toLowerCase();
    Array.from(tableBody.rows).forEach(row => {
      const match = Array.from(row.cells).slice(0,8)
        .some(td => td.textContent.toLowerCase().includes(term));
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
