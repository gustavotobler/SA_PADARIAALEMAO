<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Produtos - Padaria do Alemão</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
/* ====== Reset e base ====== */
* {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body {background:#f5f5f5;color:#292929;transition: padding-left 0.3s ease;padding-left:230px;}
.sidebar-collapsed body {padding-left:70px;}

/* ====== Sidebar ====== */
.sidebar {
  position: fixed; top:0; left:0; height:100vh;
  background:#292929; color:#f7b975;
  display:flex; flex-direction:column; align-items:center;
  width:230px; transition: width 0.3s ease; z-index:1100;
  overflow:hidden;
}
.sidebar.collapsed {width:70px;}
.sidebar-logo {margin:1rem 0 2rem; cursor:pointer; display:flex; flex-direction:column; align-items:center; transition: all 0.3s;}
.sidebar-logo img {width:100px; transition: width 0.3s;}
.sidebar.collapsed .sidebar-logo img {width:40px;}
.sidebar-logo span {margin-top:0.5rem; font-weight:bold; font-size:1.4rem; color:#f7b975; transition: opacity 0.3s;}
.sidebar.collapsed .sidebar-logo span {opacity:0; height:0; overflow:hidden;}

/* Menu itens */
.menu-item {
  width:100%; display:flex; align-items:center; gap:12px;
  padding:12px 20px; cursor:pointer; color:#f7b975; text-decoration:none;
  border-radius:6px; transition:0.3s; white-space:nowrap;
}
.menu-item:hover {background:#f7b975; color:#292929;}
.menu-icon {font-size:20px; min-width:24px; text-align:center;}
.sidebar.collapsed .menu-item span.text {display:none;}
.sidebar.collapsed .menu-item {justify-content:center; padding:12px 0;}

/* ====== Main e tabela ====== */
main {padding:40px;}
table {
  width:100%; max-width:1200px; margin:0 auto;
  border-collapse:collapse; border-radius:8px; overflow:hidden;
  box-shadow:0 4px 12px rgba(0,0,0,0.1); background:#fff;
}
thead tr {background:#f7b975; color:#292929; font-weight:bold; text-align:center;}
thead th {padding:12px 15px; font-size:1rem;}
tbody tr {transition:0.3s; cursor:default;}
tbody tr:hover {background:#f7f1e5; color:#292929; font-weight:bold;}
tbody td {padding:12px 15px; text-align:center; font-size:0.95rem; border-bottom:1px solid #ddd;}
.hidden {display:none;}

/* Responsividade */
@media(max-width:800px){
  body {padding-left:0;}
  .sidebar {position:relative;width:100%;height:auto;flex-direction:row;overflow-x:auto;}
  .sidebar.collapsed {width:100%;}
  .menu-item {justify-content:center;padding:10px;}
  main {padding:20px;}
  table {font-size:0.85rem;}
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
      <?php
      require_once 'conexao.php';

      $sql = "SELECT ID_produto, ID_forn, Nome_prod, Preco_unitario, Unid_medida, Validade, Qntd_produto FROM produtos";
      $stmt = $pdo->query($sql);
      $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($produtos as $prod):?>
      <tr>
          <td><?= htmlspecialchars($prod['ID_produto']) ?></td>
          <td><?= htmlspecialchars($prod['ID_forn']) ?></td>
          <td><?= htmlspecialchars($prod['Nome_prod']) ?></td>
          <td><?= htmlspecialchars($prod['Preco_unitario']) ?></td>
          <td><?= htmlspecialchars($prod['Unid_medida']) ?></td>
          <td><?= htmlspecialchars($prod['Validade']) ?></td>
          <td><?= htmlspecialchars($prod['Qntd_produto']) ?></td>
          
          <!-- CELULA DE AÇÕES: lápis e deletar -->
          <td class="action-cell hidden">
            <!-- Editar redirecionando para outro formulário -->
            <a href="alterar/alterar_produto.php?id=<?= $prod['ID_produto'] ?>" class="icon-btn" title="Editar">
              <span class="material-icons">edit</span>
            </a>
      </tr>
      <?php endforeach;?>
    </thead>
    <tbody id="supplier-table-body">
      
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
