<?php
// carrinho.php (sidebar ajustada ao estilo fornecido; método como dropdown custom)
session_start();

// Config DB (ajuste se necessário)
$host = '127.0.0.1';
$db = 'padariadoalemao';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Exception $e) {
  die('Erro na conexão com o banco.');
}

// CSRF
if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
function check_csrf(array $post) {
  if (!isset($post['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $post['csrf'])) {
    throw new Exception('Requisição inválida (CSRF).');
  }
}

// AJAX helper
function is_ajax() {
  return (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
  );
}
function send_json($data, $status = 200) {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit();
}

// Autenticação mínima
if (!isset($_SESSION['ID_func'])) {
  if (is_ajax()) send_json(['success' => false, 'msg' => 'Sessão expirada'], 401);
  echo "<script>alert('Sessão expirada. Faça login.'); window.location='login.php';</script>";
  exit();
}

// ===== helpers de comanda =====
function criar_comanda($pdo, $id_func) {
  $stmt = $pdo->prepare("INSERT INTO vendas (ID_func, venda_data, status) VALUES (?, NOW(), 'ABERTA')");
  $stmt->execute([$id_func]);
  return $pdo->lastInsertId();
}
function carregar_comanda($pdo, $id) {
  $stmt = $pdo->prepare("SELECT v.*, f.Nome_func FROM vendas v LEFT JOIN funcionario f ON f.ID_func = v.ID_func WHERE v.ID_vendas = ? LIMIT 1");
  $stmt->execute([(int)$id]);
  $cab = $stmt->fetch();
  if (!$cab) return null;
  $it = $pdo->prepare("SELECT iv.*, p.Nome_prod, p.Preco_unitario FROM itens_vendas iv LEFT JOIN produtos p ON p.ID_produto = iv.ID_produto WHERE iv.ID_vendas = ?");
  $it->execute([(int)$id]);
  $cab['itens'] = $it->fetchAll();
  $total = 0.0;
  foreach ($cab['itens'] as $i) $total += (float)($i['valor_total'] ?? 0);
  $cab['total'] = $total;
  return $cab;
}
function get_open_comandas($pdo, $limit = 20) {
  $st = $pdo->prepare("SELECT ID_vendas, venda_data, status FROM vendas WHERE status = 'ABERTA' ORDER BY venda_data DESC LIMIT ?");
  $st->bindValue(1, (int)$limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function resolve_item_identifier(PDO $pdo, $id_item_raw, $id_venda = null) {
  if (is_numeric($id_item_raw) && intval($id_item_raw) > 0) {
    return ['pk_col' => 'id', 'pk' => intval($id_item_raw)];
  }
  if (preg_match('/^p(\d+)_(\d+)$/', (string)$id_item_raw, $m)) {
    $id_prod = (int)$m[1];
    $qtd = (int)$m[2];
    if (!$id_venda) return null;
    $st = $pdo->prepare("SELECT * FROM itens_vendas WHERE ID_vendas = ? AND ID_produto = ? AND Quantidade = ? LIMIT 1");
    $st->execute([$id_venda, $id_prod, $qtd]);
    $row = $st->fetch();
    if (!$row) return null;
    $possiblePks = ['id','ID_itens_vendas','ID_itens','ID_itens_venda','ID_item','ID_itens_vendas'];
    foreach ($possiblePks as $col) {
      if (array_key_exists($col, $row) && is_numeric($row[$col]) && intval($row[$col])>0) {
        return ['pk_col' => $col, 'pk' => intval($row[$col])];
      }
    }
    return ['row' => $row];
  }
  if (preg_match('/^([A-Za-z0-9_]+):(\d+)$/', (string)$id_item_raw, $m2)) {
    $col = $m2[1]; $val = (int)$m2[2];
    try {
      $st = $pdo->prepare("SELECT * FROM itens_vendas WHERE $col = ? LIMIT 1");
      $st->execute([$val]);
      $row = $st->fetch();
      if ($row) return ['pk_col' => $col, 'pk' => $val];
    } catch (Exception $e) {}
  }
  return null;
}

// ---- AJAX / POST actions ----
try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf($_POST);
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'nova') {
      $novaId = criar_comanda($pdo, $_SESSION['ID_func']);
      if (is_ajax()) {
        send_json(['success' => true, 'id' => $novaId, 'redirect' => '?id=' . $novaId]);
      }
      header('Location: ?id=' . $novaId);
      exit();
    }

    if ($acao === 'edit_qty') {
      if (!is_ajax()) throw new Exception('Somente AJAX');
      $id_item = $_POST['id_item'] ?? '';
      $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
      $qtd = isset($_POST['quantidade']) ? max(1, (int)$_POST['quantidade']) : 1;
      if (!$id_item) send_json(['success'=>false,'msg'=>'ID item inválido'],400);

      $resolved = resolve_item_identifier($pdo, $id_item, $id_venda);
      if (!$resolved) send_json(['success'=>false,'msg'=>'Item não encontrado (resolução falhou)'],404);

      if (isset($resolved['pk'])) {
        $col = $resolved['pk_col'];
        $pkVal = $resolved['pk'];
        $st = $pdo->prepare("SELECT ID_vendas, ID_produto, Quantidade, valor_unitario FROM itens_vendas WHERE {$col} = ? LIMIT 1");
        $st->execute([$pkVal]);
        $row = $st->fetch();
        if (!$row) send_json(['success'=>false,'msg'=>'Item não encontrado (por PK)'],404);

        $valor_unit = (float)$row['valor_unitario'];
        $valor_total = number_format($valor_unit * $qtd, 2, '.', '');

        $upd = $pdo->prepare("UPDATE itens_vendas SET Quantidade = ?, valor_total = ? WHERE {$col} = ?");
        $upd->execute([$qtd, $valor_total, $pkVal]);

        $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
        $stmt->execute([$row['ID_vendas']]);
        $totalRow = $stmt->fetch();

        send_json([
          'success' => true,
          'msg' => 'Quantidade atualizada',
          'novo_total' => (float)($totalRow['total'] ?? 0),
          'item_total' => (float)$valor_total
        ]);
      } else {
        $row = $resolved['row'];
        $valor_unit = (float)$row['valor_unitario'];
        $valor_total = number_format($valor_unit * $qtd, 2, '.', '');
        $oldQtd = $row['Quantidade'];
        $upd = $pdo->prepare("UPDATE itens_vendas SET Quantidade = ?, valor_total = ? WHERE ID_vendas = ? AND ID_produto = ? AND Quantidade = ? LIMIT 1");
        $upd->execute([$qtd, $valor_total, $row['ID_vendas'], $row['ID_produto'], $oldQtd]);

        $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
        $stmt->execute([$row['ID_vendas']]);
        $totalRow = $stmt->fetch();

        send_json([
          'success' => true,
          'msg' => 'Quantidade atualizada',
          'novo_total' => (float)($totalRow['total'] ?? 0),
          'item_total' => (float)$valor_total
        ]);
      }
    }

    if ($acao === 'remove_item') {
      if (!is_ajax()) throw new Exception('Somente AJAX');
      $id_item = $_POST['id_item'] ?? '';
      $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
      if (!$id_item) send_json(['success'=>false,'msg'=>'ID item inválido'],400);

      $resolved = resolve_item_identifier($pdo, $id_item, $id_venda);
      if (!$resolved) send_json(['success'=>false,'msg'=>'Item não encontrado'],404);

      if (isset($resolved['pk'])) {
        $col = $resolved['pk_col']; $pk = $resolved['pk'];
        $st = $pdo->prepare("SELECT ID_vendas FROM itens_vendas WHERE {$col} = ? LIMIT 1");
        $st->execute([$pk]);
        $r = $st->fetch();
        if (!$r) send_json(['success'=>false,'msg'=>'Item não encontrado antes de deletar'],404);
        $id_venda_real = (int)$r['ID_vendas'];

        $del = $pdo->prepare("DELETE FROM itens_vendas WHERE {$col} = ?");
        $del->execute([$pk]);

        $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
        $stmt->execute([$id_venda_real]);
        $totalRow = $stmt->fetch();

        send_json(['success'=>true,'msg'=>'Item removido','novo_total'=>(float)($totalRow['total'] ?? 0)]);
      } else {
        $row = $resolved['row'];
        $del = $pdo->prepare("DELETE FROM itens_vendas WHERE ID_vendas = ? AND ID_produto = ? AND Quantidade = ? LIMIT 1");
        $del->execute([$row['ID_vendas'], $row['ID_produto'], $row['Quantidade']]);

        $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
        $stmt->execute([$row['ID_vendas']]);
        $totalRow = $stmt->fetch();

        send_json(['success'=>true,'msg'=>'Item removido','novo_total'=>(float)($totalRow['total'] ?? 0)]);
      }
    }

    if ($acao === 'pagar') {
      $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
      if ($id_venda <= 0) throw new Exception('ID da comanda inválido');

      $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
      $stmt->execute([$id_venda]);
      $totalRow = $stmt->fetch();
      $total = (float)($totalRow['total'] ?? 0);

      // método vem de um campo (hidden input) ou fallback
      $metodo = substr(trim($_POST['metodo'] ?? 'DINHEIRO'), 0, 50);
      $valor_pago = isset($_POST['valor_pago']) ? (float) $_POST['valor_pago'] : $total;
      $auto_close = isset($_POST['auto_close']) && ($_POST['auto_close'] === '1' || $_POST['auto_close'] === 'on');

      if ($total <= 0) throw new Exception('Comanda sem itens (total zero).');
      if ($auto_close && $valor_pago < $total) {
        throw new Exception('Valor pago menor que total. Desmarque "Fechar automaticamente" ou pague o valor integral.');
      }

      try {
        $pdo->beginTransaction();

        $troco = max(0, $valor_pago - $total);
        $ins = $pdo->prepare("INSERT INTO pagamentos (ID_vendas, metodo, valor_pago, troco, ID_func_registro) VALUES (?,?,?,?,?)");
        $ins->execute([$id_venda, $metodo, number_format($valor_pago,2,'.',''), number_format($troco,2,'.',''), $_SESSION['ID_func']]);

        $itens = $pdo->prepare("SELECT ID_produto, Quantidade, valor_total FROM itens_vendas WHERE ID_vendas = ?");
        $itens->execute([$id_venda]);
        $itensArr = $itens->fetchAll();
        foreach ($itensArr as $it) {
          try {
            $pdo->prepare("INSERT INTO pagamentos_itens (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)")
              ->execute([$id_venda, $it['ID_produto'], $it['Quantidade'], $it['valor_total']]);
          } catch (Exception $e) {}
        }

        if ($auto_close) {
          $lock = $pdo->prepare("SELECT iv.ID_produto, iv.Quantidade, p.Qntd_produto FROM itens_vendas iv JOIN produtos p ON p.ID_produto = iv.ID_produto WHERE iv.ID_vendas = ? FOR UPDATE");
          $lock->execute([$id_venda]);
          $itemsLock = $lock->fetchAll();
          foreach ($itemsLock as $it) {
            if ((int)$it['Qntd_produto'] < (int)$it['Quantidade']) {
              throw new Exception('Estoque insuficiente para o produto ID ' . $it['ID_produto'] . '.');
            }
            $upd = $pdo->prepare("UPDATE produtos SET Qntd_produto = Qntd_produto - ? WHERE ID_produto = ?");
            $upd->execute([(int)$it['Quantidade'], (int)$it['ID_produto']]);
          }
          $pdo->prepare("UPDATE vendas SET status='FECHADA' WHERE ID_vendas = ?")->execute([$id_venda]);
        }

        $pdo->commit();

        if (is_ajax()) {
          send_json(['success'=>true,'msg'=>'Pagamento registrado'.($auto_close?' e comanda fechada.':'').'','total'=>$total,'troco'=>$troco]);
        } else {
          header('Location: comandas_visualizar.php?id=' . $id_venda);
          exit();
        }
      } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
      }
    }

  }
} catch (Exception $e) {
  if (is_ajax()) send_json(['success'=>false,'msg'=>$e->getMessage()],400);
  $error = $e->getMessage();
}

// Se GET: renderiza a página
$idParam = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comanda = $idParam ? carregar_comanda($pdo, $idParam) : null;
if ($idParam && !$comanda) {
  $error = 'Comanda #' . $idParam . ' não encontrada.';
}

$metodos = ['DINHEIRO','CARTÃO DÉBITO','CARTÃO CRÉDITO','PIX','OUTRO'];
$openComandas = get_open_comandas($pdo, 20);

// buscar nome do funcionário (opcional)
$stmtNome = $pdo->prepare("SELECT Nome_func FROM funcionario WHERE ID_func = :ID_func LIMIT 1");
$stmtNome->execute([':ID_func' => $_SESSION['ID_func']]);
$nomeFunc = $stmtNome->fetchColumn() ?? ($_SESSION['nome_func'] ?? 'Usuário');

?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Carrinho — Comanda <?= $comanda ? htmlspecialchars($comanda['ID_vendas']) : '' ?></title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    :root {
      --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
      --main-bg: rgb(59, 75, 93);
      --primary-text: #f8f9fa;
      --hover-bg: #1e3a5f;
      --highlight: #0077b6;
      --card-bg: #1c2a3a;
      --muted: #94a3b8;
      --btn-surface: rgba(255,255,255,0.03);
      --btn-border: rgba(255,255,255,0.06);
      --glass: rgba(255,255,255,0.02);
      --sidebar-width: 240px;
      --sidebar-collapsed: 60px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial;background:linear-gradient(180deg,var(--main-bg),#0b2e3f);color:var(--primary-text);min-height:100vh;display:flex}
    /* Sidebar conforme arquivo que você mandou */
    .sidebar {
      width:var(--sidebar-width);
      background:var(--sidebar-bg);
      height:100vh;
      position:fixed;
      display:flex;
      flex-direction:column;
      padding-top:20px;
      transition:width .3s;
      box-shadow:3px 0 10px rgba(0,0,0,.3);
      z-index:20;
    }
    .sidebar.collapsed{width:var(--sidebar-collapsed)}
    .sidebar a{display:flex;align-items:center;color:var(--primary-text);text-decoration:none;padding:15px 20px;white-space:nowrap;transition:background .2s,padding .3s}
    .sidebar a:hover{background:var(--hover-bg);border-left:4px solid var(--highlight);padding-left:16px}
    .sidebar .icon{margin-right:8px}
    .sidebar.collapsed .text{display:none}
    .sidebar.collapsed .icon{margin-right:0;justify-content:center}
    .toggle-btn{cursor:pointer;text-align:center;margin-bottom:20px;font-size:22px;color:var(--primary-text)}

    .main-wrap{margin-left:var(--sidebar-width);flex:1;padding:28px;display:flex;justify-content:center;transition:margin-left .25s ease}
    .container{max-width:1100px;width:100%}
    .card{background:var(--card-bg);padding:16px;border-radius:12px;border:1px solid var(--glass);box-shadow:0 18px 40px rgba(0,0,0,0.5);color:var(--primary-text)}
    h1{margin:0 0 8px}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px}
    .comanda-meta{color:var(--muted);font-size:14px}
    .grid{display:grid;grid-template-columns:1fr 360px;gap:16px}
    @media(max-width:900px){ .grid{grid-template-columns:1fr} .sidebar{display:none} .main-wrap{margin-left:0} }
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;font-size:14px}
    th{background:linear-gradient(90deg,#063a52,#0e6190);color:#fff;font-weight:700;text-align:left}
    td.qty{width:120px;text-align:center}
    td.val{width:120px;text-align:right}
    .muted{color:var(--muted);font-size:13px}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
    .btn{padding:10px 14px;border-radius:9px;border:none;cursor:pointer;font-weight:700;color:#fff}
    .btn-primary{background:linear-gradient(90deg,#1373b8,#0093d0)}
    .btn-ghost{background:transparent;border:1px solid rgba(255,255,255,0.06)}
    .btn-danger{background:linear-gradient(90deg,#ff5b5b,#ff7b7b)}
    input[type=number], input[type=text], select {padding:8px;border-radius:8px;border:1px solid var(--btn-border);background:var(--btn-surface);color:var(--primary-text);width:100%}
    .note{font-size:13px;color:var(--muted);margin-top:6px}
    .item-sub{font-weight:700}

    /* controles abrir comanda */
    .open-controls{ display:flex; gap:8px; align-items:center; flex-wrap:nowrap; }
    .open-controls form{ display:flex; gap:6px; align-items:center; }
    .open-controls input[type=number]{ width:150px; min-width:120px; max-width:220px; padding:8px; border-radius:8px; border:1px solid var(--btn-border); background:var(--btn-surface); color:var(--primary-text) }

    /* open-comandas (igual ao anterior) */
    .open-comandas { position:relative; display:inline-block; min-width:220px; flex:0 0 auto; }
    .open-comandas .select-btn { background: var(--btn-surface); color:var(--primary-text); border:1px solid var(--btn-border); padding:8px 12px; border-radius:8px; cursor:pointer; width:100%; text-align:left; display:inline-flex; align-items:center; justify-content:space-between; gap:8px; }
    .open-comandas .select-btn .caret { opacity:0.8; font-size:18px; }
    .open-comandas .select-list { position:absolute; top:calc(100% + 6px); left:0; right:0; background:linear-gradient(180deg,#0d1b2a,#12263a); border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.5); max-height:220px; overflow:auto; z-index:200; padding:6px 0; display:none; list-style:none; margin:0; }
    .open-comandas .select-list li { padding:8px 12px; cursor:pointer; color:var(--primary-text); white-space:nowrap; }
    .open-comandas .select-list li:hover { background:#1b3b57; color:#fff; }
    .open-comandas .select-list li.empty { color:var(--muted); cursor:default; }

    /* método: reaproveita o mesmo visual */
    .metodo-wrap { position:relative; width:100%; }
    .metodo-btn { background: var(--btn-surface); color:var(--primary-text); border:1px solid var(--btn-border); padding:8px 12px; border-radius:8px; cursor:pointer; width:100%; text-align:left; display:inline-flex; align-items:center; justify-content:space-between; gap:8px; }
    .metodo-list { position:absolute; top:calc(100% + 6px); left:0; right:0; background:linear-gradient(180deg,#0d1b2a,#12263a); border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.5); max-height:220px; overflow:auto; z-index:210; padding:6px 0; display:none; list-style:none; margin:0; }
    .metodo-list li { padding:8px 12px; cursor:pointer; color:var(--primary-text); white-space:nowrap; }
    .metodo-list li:hover { background:#1b3b57; color:#fff; }
    .metodo-list li.active { background:#164a6a; color:#fff; }

    @media(max-width:700px){
      .open-controls{ flex-wrap:wrap; gap:8px; }
      .open-comandas{ min-width:160px; width:100%; }
      .open-controls form{ width:100%; }
      .open-controls input[type=number]{ width:100%; }
      .grid{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar" aria-label="Menu lateral">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php"><span class="material-icons icon" aria-hidden="true">arrow_back</span><span class="text">Voltar</span></a>
    <!-- ADICIONEI: link para criar comanda (mesmo ícone/estilo que você pediu) -->
    <a href="comanda.php"><span class="material-icons icon" aria-hidden="true">receipt</span><span class="text">Criar Comanda</span></a>
    <a href="ver_comandas.php"><span class="material-icons icon" aria-hidden="true">visibility</span><span class="text">Ver Comandas</span></a>
    <a href="carrinho.php"><span class="material-icons icon" aria-hidden="true">shopping_cart</span><span class="text">Carrinho</span></a>
  </nav>

  <!-- Main -->
  <div class="main-wrap" id="mainWrap">
    <div class="container">
      <div class="card" role="main" aria-live="polite">
        <div class="topbar">
          <div>
            <h1>Carrinho — Comanda <?= $comanda ? htmlspecialchars($comanda['ID_vendas']) : '-' ?></h1>
            <div class="comanda-meta">Funcionário: <?= $comanda ? htmlspecialchars($comanda['Nome_func'] ?? '-') : htmlspecialchars($nomeFunc) ?> · Status: <?= $comanda ? htmlspecialchars($comanda['status']) : '-' ?></div>
            <?php if (!empty($error)): ?><div class="note" style="color:#ffb4a6;margin-top:8px"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          </div>

          <div class="open-controls" aria-hidden="false">
            <form method="get" action="">
              <input type="number" name="id" min="1" placeholder="Abrir comanda Nº" aria-label="Número da comanda">
              <button class="btn btn-ghost" type="submit">Abrir</button>
            </form>

            <div class="open-comandas" id="openComandas">
              <button type="button" class="select-btn" id="openComBtn" aria-haspopup="listbox" aria-expanded="false">
                <span>Abrir comanda aberta...</span>
                <span class="caret">▾</span>
              </button>
              <ul class="select-list" id="openComList" role="listbox" aria-hidden="true">
                <?php if (empty($openComandas)): ?>
                  <li class="empty">Nenhuma comanda aberta</li>
                <?php else: ?>
                  <?php foreach ($openComandas as $oc): ?>
                    <li data-id="<?= (int)$oc['ID_vendas'] ?>">#<?= (int)$oc['ID_vendas'] ?> — <?= htmlspecialchars(substr($oc['venda_data'],0,16)) ?></li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>

        <?php if (!$comanda): ?>
          <div class="note">Informe uma comanda válida (ex: <code>?id=123</code>) ou abra uma comanda usando o campo acima.</div>
        <?php else: ?>

        <div class="grid" style="margin-top:10px">
          <!-- left: itens -->
          <div>
            <div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">
              <div class="muted">Itens na comanda</div>
              <div class="muted">ID <?= (int)$comanda['ID_vendas'] ?></div>
            </div>

            <div style="overflow:auto;max-height:460px">
              <table aria-label="Itens do carrinho" id="itensTable">
                <thead>
                  <tr><th>Produto</th><th class="qty">Qtd</th><th class="val">V. Unit.</th><th class="val">Subtotal</th><th></th></tr>
                </thead>
                <tbody>
                  <?php if (!empty($comanda['itens'])):
                    foreach ($comanda['itens'] as $it):
                      $item_pk = $it['id'] ?? ($it['ID_itens_vendas'] ?? ($it['ID_itens'] ?? ($it['ID_itens_venda'] ?? null)));
                      if (!$item_pk) $item_pk = 'p' . $it['ID_produto'] . '_' . $it['Quantidade'];
                  ?>
                    <tr data-item-id="<?= htmlspecialchars($item_pk) ?>">
                      <td><?= htmlspecialchars($it['Nome_prod'] ?? '-') ?></td>
                      <td class="qty">
                        <input type="number" class="qtyInput" min="1" value="<?= (int)$it['Quantidade'] ?>" data-item-id="<?= htmlspecialchars($item_pk) ?>" style="width:80px;padding:6px">
                      </td>
                      <td class="val">R$ <?= number_format($it['valor_unitario'] ?? $it['Preco_unitario'] ?? 0,2,',','.') ?></td>
                      <td class="val item-sub">R$ <?= number_format($it['valor_total'],2,',','.') ?></td>
                      <td class="right"><button class="btn btn-danger small-btn removeBtn" data-item-id="<?= htmlspecialchars($item_pk) ?>">Remover</button></td>
                    </tr>
                  <?php endforeach; else: ?>
                    <tr><td colspan="5" class="muted">Nenhum item nesta comanda.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <div class="note" style="margin-top:8px">Edite quantidades e clique fora para aplicar. Remover exclui o item da comanda.</div>
          </div>

          <!-- right: resumo e pagamento -->
          <div>
            <div style="margin-bottom:8px"><strong>Resumo</strong></div>

            <div style="display:flex;flex-direction:column;gap:8px">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <div class="muted">Subtotal:</div>
                <div id="subtotalText" style="font-weight:800">R$ <?= number_format($comanda['total'],2,',','.') ?></div>
              </div>

              <!-- método: dropdown custom -->
              <div style="display:flex;align-items:center;gap:8px">
                <label class="muted" for="metodo" style="min-width:88px">Método</label>
                <div class="metodo-wrap" style="flex:1">
                  <button type="button" class="metodo-btn" id="metodoBtn" aria-haspopup="listbox" aria-expanded="false">
                    <span id="metodoLabel"><?= htmlspecialchars($metodos[0]) ?></span>
                    <span class="caret">▾</span>
                  </button>
                  <ul class="metodo-list" id="metodoList" aria-hidden="true">
                    <?php foreach ($metodos as $m): ?>
                      <li data-val="<?= htmlspecialchars($m) ?>" <?= ($m === $metodos[0]) ? 'class="active"' : '' ?>><?= htmlspecialchars($m) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <!-- hidden input usado pelo JS/backend -->
                  <input type="hidden" id="metodo" name="metodo" value="<?= htmlspecialchars($metodos[0]) ?>">
                </div>
              </div>

              <div style="display:flex;align-items:center;gap:8px">
                <label class="muted" for="valor_pago" style="min-width:88px">Valor pago</label>
                <input type="number" id="valor_pago" placeholder="0.00" step="0.01" min="0" style="flex:1">
              </div>

              <div style="display:flex;align-items:center;gap:8px">
                <label class="muted" style="min-width:88px">Troco</label>
                <div id="trocoText" style="margin-left:auto;font-weight:800">R$ 0,00</div>
              </div>

              <div style="display:flex;align-items:center;gap:8px;margin-top:8px">
                <label class="muted"><input type="checkbox" id="auto_close"> Fechar automaticamente após pagamento</label>
              </div>

              <div class="actions">
                <button id="btnPay" class="btn btn-primary">💵 Registrar Pagamento</button>
                <a class="btn btn-ghost" href="comandas_visualizar.php?id=<?= (int)$comanda['ID_vendas'] ?>">↩ Voltar</a>
                <button id="btnPrint" class="btn btn-ghost" onclick="window.open('comandas_visualizar.php?id=<?= (int)$comanda['ID_vendas'] ?>&print=1','_blank')">🖨 Imprimir</button>
              </div>

              <div class="note" id="msgBox" style="display:none;margin-top:8px"></div>
            </div>
          </div>
        </div>

        <?php endif; ?>
      </div>
    </div>
  </div>

<script>
  // sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const mainWrap = document.getElementById('mainWrap');
  function toggleSidebar(){
    if(!sidebar) return;
    sidebar.classList.toggle('collapsed');
    mainWrap.style.marginLeft = sidebar.classList.contains('collapsed') ? '60px' : '240px';
  }

  (function(){
    const comandaId = <?= $comanda ? (int)$comanda['ID_vendas'] : 0 ?>;
    const csrf = "<?= htmlspecialchars($_SESSION['csrf']) ?>";
    const formatBRL = v => new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(v||0));

    function recalcClient(){
      let subtotal = 0;
      document.querySelectorAll('#itensTable tbody tr').forEach(tr=>{
        const txt = tr.querySelector('.item-sub')?.textContent || '';
        const num = parseFloat((txt.replace(/[^\d,.-]/g,'').replace(/\./g,'').replace(',','.')) || 0);
        subtotal += num;
      });
      document.getElementById('subtotalText').textContent = formatBRL(subtotal);
      return subtotal;
    }

    async function postJson(body){
      const res = await fetch(window.location.pathname + window.location.search, {
        method: 'POST',
        headers: {'Accept':'application/json'},
        body: body,
        credentials: 'same-origin'
      });
      const ct = res.headers.get('content-type') || '';
      if (ct.indexOf('application/json') !== -1) return res.json();
      else {
        const txt = await res.text();
        throw new Error('Resposta não JSON: ' + txt);
      }
    }

    // qty edits
    document.querySelectorAll('.qtyInput').forEach(inp=>{
      inp.addEventListener('change', async (e)=>{
        const newQ = Math.max(1, parseInt(inp.value) || 1);
        inp.value = newQ;
        const itemId = inp.dataset.itemId;
        const fd = new FormData();
        fd.append('csrf', csrf);
        fd.append('acao','edit_qty');
        fd.append('id_item', itemId);
        fd.append('id_venda', comandaId);
        fd.append('quantidade', newQ);
        try {
          const j = await postJson(fd);
          if (j.success) {
            const tr = document.querySelector(`tr[data-item-id="${itemId}"]`);
            if (tr && typeof j.item_total !== 'undefined') {
              tr.querySelector('.item-sub').textContent = formatBRL(j.item_total);
            }
            document.getElementById('subtotalText').textContent = formatBRL(j.novo_total || recalcClient());
            showMsg(j.msg, true);
          } else {
            showMsg(j.msg || 'Erro', false);
          }
        } catch (err) {
          console.error(err);
          showMsg(err.message, false);
        }
      });
    });

    // remove item
    document.querySelectorAll('.removeBtn').forEach(btn=>{
      btn.addEventListener('click', async (e)=>{
        if (!confirm('Remover este item da comanda?')) return;
        const id = btn.dataset.itemId;
        const fd = new FormData();
        fd.append('csrf', csrf);
        fd.append('acao','remove_item');
        fd.append('id_item', id);
        fd.append('id_venda', comandaId);
        try {
          const j = await postJson(fd);
          if (j.success) {
            const tr = document.querySelector(`tr[data-item-id="${id}"]`);
            if (tr) tr.remove();
            document.getElementById('subtotalText').textContent = formatBRL(j.novo_total || recalcClient());
            showMsg(j.msg, true);
          } else showMsg(j.msg || 'Erro', false);
        } catch (err) {
          console.error(err);
          showMsg(err.message, false);
        }
      });
    });

    function calcTotalsAndTroco(){
      const subtotalText = document.getElementById('subtotalText').textContent || 'R$ 0,00';
      const subtotalNum = parseFloat(subtotalText.replace(/[^\d,.-]/g,'').replace(/\./g,'').replace(',','.')) || 0;
      const total = subtotalNum; // sem desconto
      const valorPago = parseFloat(document.getElementById('valor_pago').value || 0) || 0;
      const troco = Math.max(0, valorPago - total);
      document.getElementById('trocoText').textContent = formatBRL(troco);
      return {subtotal: subtotalNum, total, valorPago, troco};
    }

    document.getElementById('valor_pago')?.addEventListener('input', calcTotalsAndTroco);

    function showMsg(msg, ok=true){
      const box = document.getElementById('msgBox');
      if(!box) return;
      box.style.display = 'block';
      box.style.color = ok ? '#a2f5c7' : '#ffb4a6';
      box.textContent = msg;
      setTimeout(()=>{ box.style.display = 'none'; }, 6000);
    }

    // pagar
    document.getElementById('btnPay')?.addEventListener('click', async ()=>{
      if (!confirm('Registrar pagamento para esta comanda?')) return;
      const totals = calcTotalsAndTroco();
      const fd = new FormData();
      fd.append('csrf', csrf);
      fd.append('acao','pagar');
      fd.append('id_venda', comandaId);
      // método agora vem do input hidden (#metodo)
      fd.append('metodo', document.getElementById('metodo').value || 'DINHEIRO');
      fd.append('valor_pago', Number((document.getElementById('valor_pago').value || totals.total)).toFixed(2));
      fd.append('auto_close', document.getElementById('auto_close').checked ? '1' : '0');

      try {
        const j = await postJson(fd);
        if (j.success) {
          showMsg(j.msg || 'Pagamento registrado', true);
          if (j.troco !== undefined) document.getElementById('trocoText').textContent = formatBRL(j.troco);
          if (document.getElementById('auto_close').checked) {
            window.location = 'comandas_visualizar.php?id=' + comandaId;
          } else {
            document.getElementById('valor_pago').value = parseFloat(fd.get('valor_pago')).toFixed(2);
            calcTotalsAndTroco();
          }
        } else {
          showMsg(j.msg || 'Erro', false);
        }
      } catch (err) {
        console.error(err);
        showMsg(err.message || 'Erro no servidor', false);
      }
    });

    // open-comandas dropdown behaviour
    const openBtn = document.getElementById('openComBtn');
    const openList = document.getElementById('openComList');
    if (openBtn && openList) {
      openBtn.addEventListener('click', (e)=>{
        const visible = openList.style.display === 'block';
        openList.style.display = visible ? 'none' : 'block';
        openBtn.setAttribute('aria-expanded', !visible);
        openList.setAttribute('aria-hidden', visible);
      });
      openList.addEventListener('click', (e)=>{
        const li = e.target.closest('li[data-id]');
        if (!li) return;
        const id = li.getAttribute('data-id');
        window.location = '?id=' + encodeURIComponent(id);
      });
      document.addEventListener('click', (e)=>{
        if (!openBtn.contains(e.target) && !openList.contains(e.target)) {
          openList.style.display = 'none';
          openBtn.setAttribute('aria-expanded', 'false');
          openList.setAttribute('aria-hidden', 'true');
        }
      });
    }

    // método dropdown behaviour (custom)
    const metodoBtn = document.getElementById('metodoBtn');
    const metodoList = document.getElementById('metodoList');
    const metodoHidden = document.getElementById('metodo');
    const metodoLabel = document.getElementById('metodoLabel');
    if (metodoBtn && metodoList && metodoHidden) {
      metodoBtn.addEventListener('click', ()=>{
        const vis = metodoList.style.display === 'block';
        metodoList.style.display = vis ? 'none' : 'block';
        metodoBtn.setAttribute('aria-expanded', !vis);
        metodoList.setAttribute('aria-hidden', vis);
      });
      metodoList.addEventListener('click', (e)=>{
        const li = e.target.closest('li[data-val]');
        if (!li) return;
        const val = li.getAttribute('data-val');
        // set active class
        metodoList.querySelectorAll('li').forEach(x => x.classList.remove('active'));
        li.classList.add('active');
        metodoHidden.value = val;
        metodoLabel.textContent = val;
        metodoList.style.display = 'none';
        metodoBtn.setAttribute('aria-expanded', 'false');
        metodoList.setAttribute('aria-hidden', 'true');
      });
      // close when clicking outside
      document.addEventListener('click', (e)=>{
        if (!metodoBtn.contains(e.target) && !metodoList.contains(e.target)) {
          metodoList.style.display = 'none';
          metodoBtn.setAttribute('aria-expanded', 'false');
          metodoList.setAttribute('aria-hidden', 'true');
        }
      });
    }

    // inicial
    recalcClient();
    calcTotalsAndTroco();
  })();
</script>
</body>
</html>
