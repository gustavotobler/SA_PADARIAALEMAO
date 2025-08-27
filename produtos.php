<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Produtos - Padaria do Alemão</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
/* ====== Reset e base ====== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body {
  background: #f4f6f8;
  color: #333;
  transition: padding-left 0.3s ease;
  padding-left: 240px;
}
.sidebar-collapsed body {
  padding-left: 70px;
}

/* ====== Sidebar ====== */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 240px;
  background: #1f2937;
  color: #f3f4f6;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: width 0.3s;
  z-index: 1100;
  overflow: hidden;
}
.sidebar.collapsed {
  width: 70px;
}
.sidebar-logo {
  margin: 1.5rem 0;
  cursor: pointer;
  text-align: center;
  transition: all 0.3s;
}
.sidebar-logo img {
  width: 100px;
  transition: width 0.3s;
}
.sidebar.collapsed .sidebar-logo img {
  width: 40px;
}
.sidebar-logo span {
  display: block;
  margin-top: 0.5rem;
  font-weight: 700;
  font-size: 1.3rem;
  color: #fbbf24;
  transition: opacity 0.3s;
}
.sidebar.collapsed .sidebar-logo span {
  opacity: 0;
  height: 0;
  overflow: hidden;
}

/* Menu itens */
.menu-item {
  width: 100%;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #f3f4f6;
  text-decoration: none;
  border-radius: 8px;
  transition: 0.3s;
  font-weight: 500;
}
.menu-item:hover {
  background: #fbbf24;
  color: #1f2937;
}
.menu-icon {
  font-size: 22px;
  min-width: 24px;
  text-align: center;
}
.sidebar.collapsed .menu-item span.text {
  display: none;
}
.sidebar.collapsed .menu-item {
  justify-content: center;
  padding: 12px 0;
}

/* ====== Main e tabela ====== */
main {
  padding: 40px 30px;
}
h2 {
  text-align: center;
  margin-bottom: 25px;
  font-size: 1.8rem;
  color: #1f2937;
}

.search-container {
  max-width: 400px;
  margin: 0 auto 20px auto;
  display: flex;
  align-items: center;
  gap: 10px;
}
.search-container input {
  flex: 1;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  outline: none;
  font-size: 0.95rem;
  transition: 0.3s;
}
.search-container input:focus {
  border-color: #fbbf24;
  box-shadow: 0 0 5px rgba(251, 191, 36, 0.5);
}
.search-container button {
  background: #fbbf24;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  color: #1f2937;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}
.search-container button:hover {
  background: #f59e0b;
  color: #fff;
}

table {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  border-collapse: collapse;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 6px 15px rgba(0,0,0,0.05);
}
thead {
  background: #fbbf24;
  color: #1f2937;
}
thead th {
  padding: 14px;
  font-size: 1rem;
  text-align: center;
}
tbody tr {
  transition: 0.3s;
  cursor: default;
}
tbody tr:hover {
  background: #fef3c7;
  font-weight: 600;
}
tbody td {
  padding: 12px 15px;
  text-align: center;
  font-size: 0.95rem;
  border-bottom: 1px solid #e5e7eb;
}
.action-cell a {
  margin: 0 4px;
  color: #1f2937;
  text-decoration: none;
}
.action-cell a:hover {
  color: #fbbf24;
}

.hidden {
  display: none;
}

/* ====== Responsividade ====== */
@media (max-width: 900px) {
  body {
    padding-left: 0;
  }
  .sidebar {
    position: relative;
    width: 100%;
    height: auto;
    flex-direction: row;
    overflow-x: auto;
  }
  .sidebar.collapsed {
    width: 100%;
  }
  main {
    padding: 20px 15px;
  }
  table {
    font-size: 0.85rem;
  }
  .search-container {
    flex-direction: column;
    gap: 8px;
  }
}
</style>

</head>
<body>

<nav class="sidebar" id="sidebar">
  <div class="sidebar-logo" id="sidebar-logo">
    <img src="img/Logopadaria.png" alt="Padaria do Alemão" />
    <span>Padaria do Alemão</span>
  </div>
  <a href="produtos.php" class="menu-item"><span class="material-icons menu-icon">bakery_dining</span><span class="text">Produtos</span></a>
  <a href="funcionarios.php" class="menu-item"><span class="material-icons menu-icon">person</span><span class="text">Funcionários</span></a>
  <a href="fornecedores.php" class="menu-item"><span class="material-icons menu-icon">work</span><span class="text">Fornecedores</span></a>
  <a href="vendas.php" class="menu-item"><span class="material-icons menu-icon">analytics</span><span class="text">Vendas</span></a>
  <a href="pagamento.php" class="menu-item"><span class="material-icons menu-icon">shopping_cart</span><span class="text">Pagamento</span></a>
</nav>

<main>
  <h2 style="text-align:center;margin-bottom:20px;">Produtos</h2>
  <div class="search-container">
          <span class="material-icons">search</span>
          <form method="POST" action="buscar_funcionarios.php">
            <input type="text" id="search-input" placeholder="Pesquisar...">
            <button id="search-btn" type="button">Pesquisar</button>
          </form>
        </div>
      </div>
  <table>
  <thead>
    <tr>
      <th>ID</th>  
      <th>ID FORNECEDOR</th>
      <th>Nome do produto</th>
      <th>Preço</th>
      <th>Unidade de medida</th>
      <th>Validade</th>
      <th>Quantidade</th>
      <th class="hidden">Ações</th>
    </tr>
  </thead>
  <tbody id="supplier-table-body">
    <?php
    require_once 'conexao.php';

    $sql = "SELECT ID_produto, ID_forn, Nome_prod, Preco_unitario, Unid_medida, Validade, Qntd_produto FROM produtos";
    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produtos as $prod): ?>
      <tr>
        <td><?= htmlspecialchars($prod['ID_produto']) ?></td>
        <td><?= htmlspecialchars($prod['ID_forn']) ?></td>
        <td><?= htmlspecialchars($prod['Nome_prod']) ?></td>
        <td><?= htmlspecialchars($prod['Preco_unitario']) ?></td>
        <td><?= htmlspecialchars($prod['Unid_medida']) ?></td>
        <td><?= htmlspecialchars($prod['Validade']) ?></td>
        <td><?= htmlspecialchars($prod['Qntd_produto']) ?></td>
        <td class="action-cell hidden">
          <a href="alterar/alterar_produto.php?id=<?= $prod['ID_produto'] ?>" class="icon-btn" title="Editar">
            <span class="material-icons">edit</span>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</main>

<script>
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
