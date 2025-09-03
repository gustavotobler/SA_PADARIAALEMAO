<?php
session_start();

/* ====== CONFIGURAÇÃO DO BANCO ====== */
$host = "127.0.0.1";
$db   = "padariadoalemao";
$user = "root";
$pass = ""; // ajuste conforme sua instalação
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}

/* ====== LOGIN DE DEMONSTRAÇÃO ====== */
if (!isset($_SESSION['ID_func'])) {
    // login automático como funcionário 1
    $_SESSION['ID_func'] = 1;
    $_SESSION['Nome_func'] = "Funcionário Demo";
}

/* ====== FUNÇÕES ====== */
function listarComandas($pdo) {
    $sql = "SELECT v.*, f.Nome_func 
              FROM vendas v 
              LEFT JOIN funcionario f ON f.ID_func = v.ID_func 
             ORDER BY v.venda_data DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function detalheComanda($pdo, $id) {
    $cab = $pdo->prepare("SELECT v.*, f.Nome_func 
                            FROM vendas v 
                            LEFT JOIN funcionario f ON f.ID_func = v.ID_func 
                           WHERE v.ID_vendas = ?");
    $cab->execute([$id]);
    $cab = $cab->fetch(PDO::FETCH_ASSOC);
    if (!$cab) return null;

    $itens = $pdo->prepare("SELECT iv.*, p.Nome_prod 
                              FROM itens_vendas iv
                              JOIN produtos p ON p.ID_produto = iv.ID_produto
                             WHERE iv.ID_vendas = ?");
    $itens->execute([$id]);
    $cab['itens'] = $itens->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($cab['itens'] as $it) $total += $it['valor_total'];
    $cab['total'] = $total;

    return $cab;
}

/* ====== AÇÕES ====== */
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

        $p = $pdo->prepare("SELECT Preco_unitario FROM produtos WHERE ID_produto = ?");
        $p->execute([$id_prod]);
        $preco = $p->fetchColumn();

        $valor_total = $preco * $qtd;

        $ins = $pdo->prepare("INSERT INTO itens_vendas (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)");
        $ins->execute([$id_venda, $id_prod, $qtd, $valor_total]);

        $msg = "Item adicionado!";
    }

    if ($acao === "fechar") {
        $id_venda = (int)$_POST['id_venda'];

        // baixa estoque
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

/* ====== DADOS PARA TELA ====== */
$comandas = listarComandas($pdo);
$comandaSel = isset($_GET['id']) ? detalheComanda($pdo, $_GET['id']) : null;
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Tela de Comandas</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
header{background:#222;color:#fff;padding:10px 20px;display:flex;align-items:center}
header h1{margin:0;font-size:20px}
.container{display:grid;grid-template-columns:300px 1fr;gap:20px;padding:20px}
.card{background:#fff;padding:15px;border-radius:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1)}
.btn{padding:8px 12px;border:none;border-radius:6px;cursor:pointer}
.btn.green{background:#4caf50;color:#fff}
.btn.red{background:#e53935;color:#fff}
.btn.blue{background:#1e88e5;color:#fff}
.list-item{padding:10px;border-bottom:1px solid #ddd}
.msg{padding:10px;margin:10px 0;border-radius:6px}
.msg.ok{background:#c8e6c9}
.msg.err{background:#ffcdd2}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{padding:8px;border-bottom:1px solid #ddd;text-align:left}
tfoot td{font-weight:bold}
</style>
</head>
<body>
<header>
  <span class="material-icons">receipt_long</span>
  <h1>&nbsp;Sistema de Comandas</h1>
</header>
<div class="container">
  <!-- Lista de Comandas -->
  <div class="card">
    <h2>Comandas</h2>
    <?php if($msg): ?><div class="msg ok"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="acao" value="nova">
      <button class="btn green">+ Nova Comanda</button>
    </form>
    <hr>
    <?php foreach($comandas as $c): ?>
      <div class="list-item">
        <a href="?id=<?=$c['ID_vendas']?>"><b>#<?=$c['ID_vendas']?></b></a><br>
        Func.: <?=$c['Nome_func']?><br>
        Status: <?=$c['status']?><br>
        Data: <?=$c['venda_data']?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Detalhe -->
  <div class="card">
    <h2>Detalhes da Comanda</h2>
    <?php if(!$comandaSel): ?>
      <p>Selecione uma comanda.</p>
    <?php else: ?>
      <p><b>ID:</b> <?=$comandaSel['ID_vendas']?> | <b>Status:</b> <?=$comandaSel['status']?></p>
      <p><b>Funcionário:</b> <?=$comandaSel['Nome_func']?></p>
      <form method="post">
        <input type="hidden" name="acao" value="add_item">
        <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>">
        <select name="id_produto" required>
          <option value="">Selecione produto</option>
          <?php foreach($produtos as $p): ?>
            <option value="<?=$p['ID_produto']?>"><?=$p['Nome_prod']?> - R$ <?=$p['Preco_unitario']?> (Estoque: <?=$p['Qntd_produto']?>)</option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="quantidade" value="1" min="1" required>
        <button class="btn blue">Adicionar</button>
      </form>
      <table>
        <thead>
          <tr><th>Produto</th><th>Qtd</th><th>Valor</th></tr>
        </thead>
        <tbody>
          <?php foreach($comandaSel['itens'] as $i): ?>
            <tr>
              <td><?=$i['Nome_prod']?></td>
              <td><?=$i['Quantidade']?></td>
              <td>R$ <?=$i['valor_total']?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="2">Total</td><td>R$ <?=$comandaSel['total']?></td></tr>
        </tfoot>
      </table>
      <?php if($comandaSel['status']==="ABERTA"): ?>
      <form method="post" style="margin-top:10px;display:inline-block">
        <input type="hidden" name="acao" value="fechar">
        <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>">
        <button class="btn green">Fechar Comanda</button>
      </form>
      <form method="post" style="margin-top:10px;display:inline-block">
        <input type="hidden" name="acao" value="cancelar">
        <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>">
        <button class="btn red">Cancelar</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
