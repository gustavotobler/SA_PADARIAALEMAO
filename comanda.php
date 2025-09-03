<?php
session_start();

$host = "127.0.0.1";
$db   = "padariadoalemao";
$user = "root";
$pass = "";
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}

if (!isset($_SESSION['ID_func'])) {
    $_SESSION['ID_func'] = 1;
    $_SESSION['Nome_func'] = "Funcionário Demo";
}

function listarComandas($pdo) {
    $sql = "SELECT v.*, f.Nome_func FROM vendas v 
            LEFT JOIN funcionario f ON f.ID_func = v.ID_func 
            ORDER BY v.venda_data DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function detalheComanda($pdo, $id) {
    $cab = $pdo->prepare("SELECT v.*, f.Nome_func FROM vendas v 
                          LEFT JOIN funcionario f ON f.ID_func = v.ID_func 
                          WHERE v.ID_vendas = ?");
    $cab->execute([$id]);
    $cab = $cab->fetch(PDO::FETCH_ASSOC);
    if (!$cab) return null;

    $itens = $pdo->prepare("SELECT iv.*, p.Nome_prod FROM itens_vendas iv
                            JOIN produtos p ON p.ID_produto = iv.ID_produto
                            WHERE iv.ID_vendas = ?");
    $itens->execute([$id]);
    $cab['itens'] = $itens->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($cab['itens'] as $it) $total += $it['valor_total'];
    $cab['total'] = $total;

    return $cab;
}

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? "";

    if ($acao === "nova") {
        $stmt = $pdo->prepare("INSERT INTO vendas (ID_func, venda_data, status) VALUES (?, NOW(), 'ABERTA')");
        $stmt->execute([$_SESSION['ID_func']]);
        $msg = "Nova comanda criada!";
    }

    if ($acao === "add_item") {
        $id_venda = (int)$_POST['id_venda'];
        $id_prod  = (int)$_POST['id_produto'];
        $qtd      = (int)$_POST['quantidade'];

        $p = $pdo->prepare("SELECT Preco_unitario, Qntd_produto FROM produtos WHERE ID_produto = ?");
        $p->execute([$id_prod]);
        $dados_prod = $p->fetch(PDO::FETCH_ASSOC);

        if (!$dados_prod || $dados_prod['Qntd_produto'] < $qtd) {
            $msg = "Estoque insuficiente para o produto selecionado!";
        } else {
            $preco = $dados_prod['Preco_unitario'];
            $valor_total = $preco * $qtd;

            $ver = $pdo->prepare("SELECT ID_itens_vendas, Quantidade FROM itens_vendas WHERE ID_vendas = ? AND ID_produto = ?");
            $ver->execute([$id_venda, $id_prod]);
            $existente = $ver->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $novaQtd = $existente['Quantidade'] + $qtd;
                $novoTotal = $novaQtd * $preco;
                $upd = $pdo->prepare("UPDATE itens_vendas SET Quantidade = ?, valor_total = ? WHERE ID_itens_vendas = ?");
                $upd->execute([$novaQtd, $novoTotal, $existente['ID_itens_vendas']]);
            } else {
                $ins = $pdo->prepare("INSERT INTO itens_vendas (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)");
                $ins->execute([$id_venda, $id_prod, $qtd, $valor_total]);
            }

            $msg = "Item adicionado!";
        }
    }

    if ($acao === "fechar") {
        $id_venda = (int)$_POST['id_venda'];
        $itens = $pdo->prepare("SELECT ID_produto, Quantidade FROM itens_vendas WHERE ID_vendas = ?");
        $itens->execute([$id_venda]);
        foreach ($itens as $i) {
            $upd = $pdo->prepare("UPDATE produtos SET Qntd_produto = Qntd_produto - ? WHERE ID_produto = ?");
            $upd->execute([$i['Quantidade'], $i['ID_produto']]);
        }
        $pdo->prepare("UPDATE vendas SET status='FECHADA' WHERE ID_vendas=?")->execute([$id_venda]);
        $msg = "Comanda fechada!";
    }

    if ($acao === "cancelar") {
        $id_venda = (int)$_POST['id_venda'];
        $pdo->prepare("UPDATE vendas SET status='CANCELADA' WHERE ID_vendas=?")->execute([$id_venda]);
        $msg = "Comanda cancelada!";
    }
}

$comandas = listarComandas($pdo);
$comandaSel = isset($_GET['id']) && is_numeric($_GET['id']) ? detalheComanda($pdo, (int)$_GET['id']) : null;
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Comandas</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    body {margin: 0; font-family: Arial, sans-serif; background: #eef1f5;}
    header {background: #222; padding: 10px 20px; color: #fff; display: flex; align-items: center; justify-content: space-between;}
    header h1 {margin: 0; font-size: 22px;}
    nav .dropdown {position: relative; display: inline-block;}
    .dropbtn {background: #444; color: #fff; padding: 10px; border: none; border-radius: 5px; cursor: pointer;}
    .dropdown-content {display: none; position: absolute; background: #fff; min-width: 160px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 1;}
    .dropdown-content a {color: #333; padding: 12px 16px; text-decoration: none; display: block;}
    .dropdown-content a:hover {background: #f1f1f1;}
    .dropdown:hover .dropdown-content {display: block;}

    .container {display: grid; grid-template-columns: 300px 1fr; gap: 20px; padding: 20px;}
    .card {background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);}
    .btn {padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer;}
    .btn.green {background: #4caf50; color: #fff;}
    .btn.red {background: #e53935; color: #fff;}
    .btn.blue {background: #1e88e5; color: #fff;}
    .list-item {padding: 10px; border-bottom: 1px solid #ddd;}
    .msg {padding: 10px; margin: 10px 0; border-radius: 6px;}
    .msg.ok {background: #c8e6c9;}
    .msg.err {background: #ffcdd2;}
    table {width: 100%; border-collapse: collapse; margin-top: 10px;}
    th, td {padding: 8px; border-bottom: 1px solid #ddd; text-align: left;}
    tfoot td {font-weight: bold;}
    .status-ABERTA {color: #1e88e5; font-weight: bold;}
    .status-FECHADA {color: #4caf50; font-weight: bold;}
    .status-CANCELADA {color: #e53935; font-weight: bold;}
  </style>
</head>
<body>
<header>
  <h1><span class="material-icons">receipt_long</span>&nbsp;Sistema de Comandas</h1>
  <nav class="dropdown">
    <button class="dropbtn">Menu</button>
    <div class="dropdown-content">
      <a href="cadfunc.html">Cadastro de Funcionário</a>
      <a href="cadforn.html">Cadastro de Fornecedor</a>
      <a href="comandas.php">Comandas</a>
      <a href="logout.php">Sair</a>
    </div>
  </nav>
</header>

<div class="container">
  <div class="card">
    <h2>Comandas</h2>
    <?php if ($msg): ?>
      <div class="msg ok"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="acao" value="nova">
      <button class="btn green">+ Nova Comanda</button>
    </form><hr>
    <?php foreach ($comandas as $c): ?>
      <div class="list-item">
        <a href="?id=<?php echo $c['ID_vendas']; ?>"><b>#<?php echo $c['ID_vendas']; ?></b></a><br>
        Func.: <?php echo $c['Nome_func']; ?><br>
        Status: <span class="status-<?php echo $c['status']; ?>"><?php echo $c['status']; ?></span><br>
        Data: <?php echo $c['venda_data']; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <h2>Detalhes da Comanda</h2>
    <?php if (!$comandaSel): ?>
      <p>Selecione uma .</p>
    <?php else: ?>
      <p><b>ID:</b> <?php echo $comandaSel['ID_vendas']; ?> | <b>Status:</b> <span class="status-<?php echo $comandaSel['status']; ?>"><?php echo $comandaSel['status']; ?></span></p>
      <p><b>Funcionário:</b> <?php echo $comandaSel['Nome_func']; ?></p>
      <form method="post">
        <input type="hidden" name="acao" value="add_item">
        <input type="hidden" name="id_venda" value="<?php echo $comandaSel['ID_vendas']; ?>">
        <select name="id_produto" required>
          <option value="">Selecione produto</option>
          <?php foreach ($produtos as $p): ?>
            <option value="<?php echo $p['ID_produto']; ?>">
              <?php echo $p['Nome_prod']; ?> - R$ <?php echo number_format($p['Preco_unitario'], 2, ',', '.'); ?> (Estoque: <?php echo $p['Qntd_produto']; ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="quantidade" value="1" min="1" required>
        <button class="btn blue">Adicionar</button>
      </form>

      <table>
        <thead><tr><th>Produto</th><th>Qtd</th><th>Valor</th></tr></thead>
        <tbody>
          <?php foreach ($comandaSel['itens'] as $i): ?>
            <tr>
              <td><?php echo $i['Nome_prod']; ?></td>
              <td><?php echo $i['Quantidade']; ?></td>
              <td>R$ <?php echo number_format($i['valor_total'], 2, ',', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="2">Total</td><td>R$ <?php echo number_format($comandaSel['total'], 2, ',', '.'); ?></td></tr>
        </tfoot>
      </table>

      <?php if ($comandaSel['status'] === "ABERTA"): ?>
        <form method="post" style="margin-top:10px;display:inline-block">
          <input type="hidden" name="acao" value="fechar">
          <input type="hidden" name="id_venda" value="<?php echo $comandaSel['ID_vendas']; ?>">
          <button class="btn green">Fechar Comanda</button>
        </form>
        <form method="post" style="margin-top:10px;display:inline-block">
          <input type="hidden" name="acao" value="cancelar">
          <input type="hidden" name="id_venda" value="<?php echo $comandaSel['ID_vendas']; ?>">
          <button class="btn red">Cancelar</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
