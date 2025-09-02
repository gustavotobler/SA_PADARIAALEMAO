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

</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">
    <img src="img/Logopadaria.png" alt="Padaria do Alemão">
    <span style="color:white;">Padaria do Alemão</span>
  </div>
  <a href="produtos.php" class="menu-item"><span class="material-icons">bakery_dining</span><span>Produtos</span></a>
  <a href="funcionarios.php" class="menu-item"><span class="material-icons">person</span><span>Funcionários</span></a>
  <a href="fornecedores.php" class="menu-item"><span class="material-icons">work</span><span>Fornecedores</span></a>
  <a href="vendas.php" class="menu-item"><span class="material-icons">analytics</span><span>Vendas</span></a>
  <a href="pagamento.php" class="menu-item"><span class="material-icons">shopping_cart</span><span>Pagamento</span></a>
  <a href="inicial1.php" class="menu-item">Tela principal</a>
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
      <a href="cadproduto.php" id="add-button" class="hidden">
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
        <th>Preço</th>
        <th>Unidade</th>
        <th>Validade</th>
        <th>Quantidade</th>
        <th class="action-header hidden">Ação</th>
      </tr>
    </thead>
    <tbody id="prod-table-body">
      <?php
      $sql = "SELECT p.ID_produto, f.Nome_forn, p.Nome_prod, p.Preco_unitario, p.Unid_medida, p.Validade, p.Qntd_produto
              FROM produtos p
              LEFT JOIN fornecedores f ON p.ID_forn = f.ID_forn";
      $stmt = $pdo->query($sql);
      $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($produtos as $prod): ?>
      <tr>
        <td data-label="ID"><?= htmlspecialchars($prod['ID_produto']) ?></td>
        <td data-label="Fornecedor"><?= htmlspecialchars($prod['Nome_forn']) ?></td>
        <td data-label="Nome"><?= htmlspecialchars($prod['Nome_prod']) ?></td>
        <td data-label="Preço"><?= htmlspecialchars($prod['Preco_unitario']) ?></td>
        <td data-label="Unidade"><?= htmlspecialchars($prod['Unid_medida']) ?></td>
        <td data-label="Validade"><?= htmlspecialchars($prod['Validade']) ?></td>
        <td data-label="Quantidade"><?= htmlspecialchars($prod['Qntd_produto']) ?></td>
        <td class="action-cell hidden">
          <a href="alterar/alterar_produto.php?id=<?= $prod['ID_produto'] ?>" class="icon-btn" title="Editar">
            <span class="material-icons">edit</span>
          </a>
          <form action="exclusoes/excluir_produto.php" method="POST" style="display:inline;">
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
      const match = Array.from(row.cells).slice(0,7)
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
