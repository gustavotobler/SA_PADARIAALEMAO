<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ====== CONFIGURAÇÃO DO BANCO ====== */
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

/* ====== LOGIN (corrigido) ====== */
if (!isset($_SESSION['ID_func'])) {
    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location='login.php';</script>";
    exit();
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
        $novaId = $pdo->lastInsertId();
        header("Location: comanda.php?id=" . $novaId);
        exit();
    }

    if ($acao === "add_item") {
        $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
        $id_prod  = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;
        $qtd      = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

        $p = $pdo->prepare("SELECT Preco_unitario FROM produtos WHERE ID_produto = ?");
        $p->execute([$id_prod]);
        $preco = $p->fetchColumn();

        $valor_total = $preco * $qtd;

        $ins = $pdo->prepare("INSERT INTO itens_vendas (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)");
        $ins->execute([$id_venda, $id_prod, $qtd, $valor_total]);
        $msg = "Item adicionado!";
    }

    if ($acao === "fechar") {
        $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;

        $itens = $pdo->prepare("SELECT ID_produto, Quantidade FROM itens_vendas WHERE ID_vendas = ?");
        $itens->execute([$id_venda]);
        $itens = $itens->fetchAll(PDO::FETCH_ASSOC);
        foreach ($itens as $i) {
            $upd = $pdo->prepare("UPDATE produtos SET Qntd_produto = Qntd_produto - ? WHERE ID_produto = ?");
            $upd->execute([$i['Quantidade'], $i['ID_produto']]);
        }

        $pdo->prepare("UPDATE vendas SET status='FECHADA' WHERE ID_vendas=?")->execute([$id_venda]);
        header("Location: comanda.php?id=" . $id_venda);
        exit();
    }

    if ($acao === "cancelar") {
        $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
        $pdo->prepare("UPDATE vendas SET status='CANCELADA' WHERE ID_vendas=?")->execute([$id_venda]);
        header("Location: comanda.php?id=" . $id_venda);
        exit();
    }

    if ($acao === "reabrir") {
        $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
        $pdo->prepare("UPDATE vendas SET status='ABERTA' WHERE ID_vendas=?")->execute([$id_venda]);
        header("Location: comanda.php?id=" . $id_venda);
        exit();
    }

    if ($acao === "salvar") {
        $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
        $pdo->prepare("UPDATE vendas SET venda_data=NOW() WHERE ID_vendas=?")->execute([$id_venda]);
        $msg = "Comanda salva com sucesso!";
    }
}

$comandas = listarComandas($pdo);
$comandaSel = isset($_GET['id']) ? detalheComanda($pdo, $_GET['id']) : null;
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Comandas</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
/* ====== ESTILOS ====== */
:root {
  --bg: #1b263b;
  --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
  --text-color: #f8f9fa;
  --highlight: #0077b6;
  --card-bg: #ffffff;
}
* { box-sizing: border-box; margin:0; padding:0; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
body { display:flex; background:var(--bg); color:var(--text-color); }

.sidebar {
  width:240px; background:var(--sidebar-bg); height:100vh; position:fixed;
  display:flex; flex-direction:column; padding-top:20px; transition:0.3s; box-shadow:3px 0 10px rgba(0,0,0,0.3);
}
.sidebar.collapsed { width:60px; }
.sidebar a { display:flex; align-items:center; color:var(--text-color); text-decoration:none; padding:15px 20px; transition:0.2s; white-space:nowrap;}
.sidebar a:hover { background:#1e3a5f; border-left:4px solid var(--highlight); padding-left:16px;}
.sidebar .icon { margin-right:8px; }
.sidebar.collapsed .text { display:none; }
.sidebar.collapsed .icon { margin:0 auto; }
.toggle-btn { cursor:pointer; text-align:center; margin-bottom:20px; font-size:22px; color:var(--text-color);}
.sidebar form { padding:0 20px 10px;}
.sidebar form button { width:100%; margin-top:10px;}
.main-content { margin-left:240px; padding:30px; width:100%; transition:0.3s;}
.main-content.collapsed { margin-left:60px;}
h1 { margin-bottom:20px; color:#fff; text-align:center; }
.container { display:grid; grid-template-columns:300px 1fr; gap:20px; }
.card { background:var(--card-bg); padding:20px; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.2); color:#333;}
.btn { padding:10px 15px; border:none; border-radius:6px; cursor:pointer; }
.btn.green { background:#4caf50; color:#fff; }
.btn.red { background:#e53935; color:#fff; }
.btn.blue { background:#1e88e5; color:#fff; }
.list-item { padding:10px; border-bottom:1px solid #ddd; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th,td { padding:10px; border-bottom:1px solid #ccc; text-align:left; }
tfoot td { font-weight:bold; }
.msg { padding:10px; border-radius:6px; margin-bottom:10px; }
.msg.ok { background:#c8e6c9; color:#2e7d32; }
@media(max-width:768px) { .container{grid-template-columns:1fr;} .main-content{margin-left:0; padding:15px;} }
</style>
</head>
<body>

<nav class="sidebar" id="sidebar">
  <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
  <form method="post">
    <input type="hidden" name="acao" value="nova">
    <button type="submit" class="btn green">Nova Comanda</button>
  </form>
  <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
  <a href="produtos.php"><span class="material-icons icon">bakery_dining</span><span class="text">Produtos</span></a>
  <a href="funcionarios.php"><span class="material-icons icon">person</span><span class="text">Funcionários</span></a>
  <a href="fornecedores.php"><span class="material-icons icon">work</span><span class="text">Fornecedores</span></a>
  <a href="estoque.php"><span class="material-icons icon">analytics</span><span class="text">Estoque</span></a>
  <a href="relatorio_vendas_padaria_alemao1.php"><span class="material-icons icon">analytics</span><span class="text">Vendas</span></a>
  <a href="selecionar_itens.php"><span class="material-icons icon">shopping_cart</span><span class="text">Pagamento</span></a>
  <a href="comanda.php"><span class="material-icons icon">receipt_long</span><span class="text">Comanda</span></a>
</nav>

<main class="main-content" id="mainContent">
<h1>Sistema de Comandas</h1>
<div class="container">
  <!-- Lista de Comandas -->
  <div class="card">
    <h2>Comandas</h2>
    <?php if($msg): ?><div class="msg ok"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <hr>
    <?php foreach($comandas as $c): ?>
      <div class="list-item">
        <a href="?id=<?=$c['ID_vendas']?>"><b>#<?=$c['ID_vendas']?></b></a><br>
        Criada por: <?=htmlspecialchars($c['Nome_func'])?><br>
        Status: <?=isset($c['status']) ? htmlspecialchars($c['status']) : 'N/A'?><br>
        Data: <?=$c['venda_data']?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Detalhes da Comanda -->
  <div class="card">
    <h2>Detalhes</h2>
    <?php if(!$comandaSel): ?>
      <p>Selecione uma comanda.</p>
    <?php else: ?>
      <p>
  <b>ID:</b> <?= htmlspecialchars($comandaSel['ID_vendas']) ?> | 
  <b>Status:</b> <?= htmlspecialchars($comandaSel['status']) ?> | 
  <b>Criada por:</b> <?= htmlspecialchars($comandaSel['Nome_func']) ?> | 
  <b>Usuário atual:</b> <?= htmlspecialchars($_SESSION['Nome_func']) ?>
  </p>

<?php if($comandaSel['status'] === "ABERTA"): ?>
<form method="post" style="margin-bottom:10px">
  <input type="hidden" name="acao" value="add_item">
  <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>">

  <select name="id_produto" required>
    <option value="">Selecione produto</option>
    <?php foreach($produtos as $p): ?>
      <option value="<?= $p['ID_produto'] ?>"><?= htmlspecialchars($p['Nome_prod']) ?> - R$ <?= $p['Preco_unitario'] ?> (Estoque: <?= $p['Qntd_produto'] ?>)</option>
    <?php endforeach; ?>
  </select>
  <input type="number" name="quantidade" value="1" min="1" required>
  <button class="btn blue">Adicionar</button>
</form>
<?php endif; ?>

<table>
  <thead>
    <tr><th>Produto</th><th>Qtd</th><th>Valor</th></tr>
  </thead>
  <tbody>
    <?php foreach($comandaSel['itens'] as $i): ?>
      <tr>
        <td><?=htmlspecialchars($i['Nome_prod'])?></td>
        <td><?=$i['Quantidade']?></td>
        <td>R$ <?=$i['valor_total']?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr><td colspan="2">Total</td><td>R$ <?=$comandaSel['total']?></td></tr>
  </tfoot>
</table>

<?php if($comandaSel['status'] === "ABERTA"): ?>
<form method="post" style="display:inline-block;margin-top:10px">
    <input type="hidden" name="acao" value="fechar">
    <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>"> 
    <button class="btn green">Fechar Comanda</button>
</form>
<form method="post" style="display:inline-block;margin-top:10px">
    <input type="hidden" name="acao" value="cancelar">
    <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>">
    <button class="btn red">Cancelar</button>
</form>
<form method="post" style="display:inline-block;margin-top:10px">
    <input type="hidden" name="acao" value="salvar">
    <input type="hidden" name="id_venda" value="<?=$comandaSel['ID_vendas']?>">
    <button class="btn blue">Salvar Comanda</button>
    <?php if($comandaSel): ?>
<?php endif; ?>
</form>
<form method="get" action="comanda_pdf.php" target="_blank" style="display:inline-block;margin-top:10px">
    <input type="hidden" name="id" value="<?=$comandaSel['ID_vendas']?>">
    <button class="btn blue">📄 Baixar PDF</button>
</form>
<?php else: ?>
    <p style="margin-top:10px;color:red"><b>Esta comanda está <?=$comandaSel['status']?> e não pode mais ser alterada.</b></p>
<?php endif; ?>

<?php endif; ?>
  </div>
</div>
</main>

<script>
function toggleSidebar(){
  const sidebar = document.getElementById('sidebar');
  const content = document.getElementById('mainContent');
  sidebar.classList.toggle('collapsed');
  content.classList.toggle('collapsed');
}
</script>

</body>
</html>
