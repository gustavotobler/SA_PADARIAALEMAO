<?php
// comanda_single.php
// Página enxuta: criar / editar / operar uma única comanda
// Salve como comanda_single.php

session_start();

// -> Em desenvolvimento, útil ativar; em produção comente.
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

// ===== Config DB =====
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

// ===== CSRF =====
if (!isset($_SESSION['csrf']))
  $_SESSION['csrf'] = bin2hex(random_bytes(24));
function check_csrf(array $post)
{
  if (!isset($post['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $post['csrf'])) {
    throw new Exception('Requisição inválida (CSRF).');
  }
}

// ===== Helpers AJAX/JSON =====
function is_ajax()
{
  return (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (!empty($_SERVER['HTTP_ACCEPT']) &&
      strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
  );
}
function send_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit();
}

// ===== Autenticação mínima =====
if (!isset($_SESSION['ID_func'])) {
  if (is_ajax())
    send_json(['success' => false, 'msg' => 'Sessão expirada'], 401);
  echo "<script>alert('Sessão expirada. Faça login.'); window.location='login.php';</script>";
  exit();
}

// ===== Funções específicas =====
function get_products($pdo)
{
  return $pdo->query("SELECT ID_produto, Nome_prod, Preco_unitario, Qntd_produto FROM produtos ORDER BY Nome_prod LIMIT 500")->fetchAll();
}

function criar_comanda($pdo, $id_func)
{
  $stmt = $pdo->prepare("INSERT INTO vendas (ID_func, venda_data, status) VALUES (?, NOW(), 'ABERTA')");
  $stmt->execute([$id_func]);
  return $pdo->lastInsertId();
}

function carregar_comanda($pdo, $id)
{
  $stmt = $pdo->prepare("SELECT v.*, f.Nome_func FROM vendas v LEFT JOIN funcionario f ON f.ID_func = v.ID_func WHERE v.ID_vendas = ? LIMIT 1");
  $stmt->execute([(int) $id]);
  $cab = $stmt->fetch();
  if (!$cab)
    return null;
  $it = $pdo->prepare("SELECT iv.*, p.Nome_prod FROM itens_vendas iv LEFT JOIN produtos p ON p.ID_produto = iv.ID_produto WHERE iv.ID_vendas = ?");
  $it->execute([(int) $id]);
  $cab['itens'] = $it->fetchAll();
  $total = 0.0;
  foreach ($cab['itens'] as $i)
    $total += (float) ($i['valor_total'] ?? 0);
  $cab['total'] = $total;
  return $cab;
}

// ===== POST actions (criar, add_item, fechar, cancelar, reabrir, salvar, pagar) =====
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    check_csrf($_POST);
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'nova') {
      $novaId = criar_comanda($pdo, $_SESSION['ID_func']);
      if (is_ajax())
        send_json(['success' => true, 'id' => $novaId]);
      header('Location: ?id=' . $novaId);
      exit();
    }

    if ($acao === 'add_item') {
      $id_venda = isset($_POST['id_venda']) ? (int) $_POST['id_venda'] : 0;
      $id_prod = isset($_POST['id_produto']) ? (int) $_POST['id_produto'] : 0;
      $qtd = isset($_POST['quantidade']) ? max(1, (int) $_POST['quantidade']) : 1;
      if ($id_venda <= 0 || $id_prod <= 0) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'Dados inválidos'], 400);
        throw new Exception('Dados inválidos');
      }

      // buscar preço e validar estoque
      $p = $pdo->prepare("SELECT Preco_unitario, Qntd_produto FROM produtos WHERE ID_produto = ?");
      $p->execute([$id_prod]);
      $prod = $p->fetch();
      if (!$prod) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'Produto não encontrado'], 404);
        throw new Exception('Produto não encontrado');
      }
      if ((int) $prod['Qntd_produto'] < $qtd) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'Estoque insuficiente'], 400);
        throw new Exception('Estoque insuficiente');
      }

      $valor_unitario = number_format($prod['Preco_unitario'], 2, '.', '');
      $valor_total = number_format($valor_unitario * $qtd, 2, '.', '');

      $ins = $pdo->prepare("INSERT INTO itens_vendas (ID_vendas, ID_produto, Quantidade, valor_unitario, valor_total) VALUES (?,?,?,?,?)");
      $ins->execute([$id_venda, $id_prod, $qtd, $valor_unitario, $valor_total]);

      if (is_ajax()) {
        $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
        $stmt->execute([$id_venda]);
        $totalRow = $stmt->fetch();
        send_json(['success' => true, 'msg' => 'Item adicionado', 'novo_total' => (float) ($totalRow['total'] ?? 0)]);
      }

      $msg = "Item adicionado.";
    }

    if ($acao === 'fechar') {
      $id_venda = isset($_POST['id_venda']) ? (int) $_POST['id_venda'] : 0;
      if ($id_venda <= 0) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'ID inválido'], 400);
        throw new Exception('ID inválido');
      }

      try {
        $pdo->beginTransaction();
        $itens = $pdo->prepare("SELECT iv.ID_produto, iv.Quantidade, p.Qntd_produto FROM itens_vendas iv JOIN produtos p ON p.ID_produto = iv.ID_produto WHERE iv.ID_vendas = ? FOR UPDATE");
        $itens->execute([$id_venda]);
        $itensList = $itens->fetchAll();

        foreach ($itensList as $i) {
          if ((int) $i['Qntd_produto'] < (int) $i['Quantidade']) {
            if (is_ajax())
              send_json(['success' => false, 'msg' => 'Estoque insuficiente para produto ' . $i['ID_produto']], 400);
            throw new Exception('Estoque insuficiente para produto ' . $i['ID_produto']);
          }
          $upd = $pdo->prepare("UPDATE produtos SET Qntd_produto = Qntd_produto - ? WHERE ID_produto = ?");
          $upd->execute([(int) $i['Quantidade'], (int) $i['ID_produto']]);
        }

        $pdo->prepare("UPDATE vendas SET status='FECHADA' WHERE ID_vendas=?")->execute([$id_venda]);
        $pdo->commit();

        if (is_ajax())
          send_json(['success' => true, 'redirect' => '?id=' . $id_venda]);
        header('Location: ?id=' . $id_venda);
        exit();
      } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
      }
    }

    if ($acao === 'cancelar') {
      $id_venda = isset($_POST['id_venda']) ? (int) $_POST['id_venda'] : 0;
      if ($id_venda <= 0) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'ID inválido'], 400);
        throw new Exception('ID inválido');
      }
      $pdo->prepare("UPDATE vendas SET status='CANCELADA' WHERE ID_vendas=?")->execute([$id_venda]);
      if (is_ajax())
        send_json(['success' => true, 'redirect' => '?id=' . $id_venda]);
      header('Location:?id=' . $id_venda);
      exit();
    }

    if ($acao === 'reabrir') {
      $id_venda = isset($_POST['id_venda']) ? (int) $_POST['id_venda'] : 0;
      if ($id_venda <= 0) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'ID inválido'], 400);
        throw new Exception('ID inválido');
      }
      $pdo->prepare("UPDATE vendas SET status='ABERTA' WHERE ID_vendas=?")->execute([$id_venda]);
      if (is_ajax())
        send_json(['success' => true, 'redirect' => '?id=' . $id_venda]);
      header('Location:?id=' . $id_venda);
      exit();
    }

    if ($acao === 'salvar') {
      $id_venda = isset($_POST['id_venda']) ? (int) $_POST['id_venda'] : 0;
      if ($id_venda <= 0) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'ID inválido'], 400);
        throw new Exception('ID inválido');
      }
      $pdo->prepare("UPDATE vendas SET venda_data=NOW() WHERE ID_vendas=?")->execute([$id_venda]);
      if (is_ajax())
        send_json(['success' => true, 'msg' => 'Comanda salva com sucesso!']);
      $msg = 'Comanda salva com sucesso!';
    }

    if ($acao === 'pagar') {
      $id_venda = isset($_POST['id_venda']) ? (int) $_POST['id_venda'] : 0;
      if ($id_venda <= 0) {
        if (is_ajax())
          send_json(['success' => false, 'msg' => 'ID inválido'], 400);
        throw new Exception('ID inválido');
      }

      // calcular total
      $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
      $stmt->execute([$id_venda]);
      $totalRow = $stmt->fetch();
      $total = (float) ($totalRow['total'] ?? 0);

      $metodo = substr(trim($_POST['metodo'] ?? 'DINHEIRO'), 0, 50);
      $valor_pago = isset($_POST['valor_pago']) ? (float) $_POST['valor_pago'] : $total;
      $troco = max(0, $valor_pago - $total);

      $ins = $pdo->prepare("INSERT INTO pagamentos (ID_vendas, metodo, valor_pago, troco, ID_func_registro) VALUES (?,?,?,?,?)");
      $ins->execute([$id_venda, $metodo, number_format($valor_pago, 2, '.', ''), number_format($troco, 2, '.', ''), $_SESSION['ID_func']]);

      // opcional: pagamentos_itens (silenciosamente ignoramos erro se tabela não existir)
      $itens = $pdo->prepare("SELECT ID_produto, Quantidade, valor_total FROM itens_vendas WHERE ID_vendas=?");
      $itens->execute([$id_venda]);
      $itensArr = $itens->fetchAll();
      foreach ($itensArr as $i) {
        try {
          $pdo->prepare("INSERT INTO pagamentos_itens (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)")
            ->execute([$id_venda, $i['ID_produto'], $i['Quantidade'], $i['valor_total']]);
        } catch (Exception $e) { /* ignore */
        }
      }

      if (is_ajax())
        send_json(['success' => true, 'msg' => 'Pagamento registrado', 'total' => $total]);
      $msg = 'Pagamento registrado com sucesso! Total: R$ ' . number_format($total, 2, ',', '.');
    }

  } catch (Exception $e) {
    if (is_ajax())
      send_json(['success' => false, 'msg' => $e->getMessage()], 400);
    $msg = 'Erro: ' . $e->getMessage();
  }
}

// ===== Dados para a página =====
$idParam = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$comanda = $idParam ? carregar_comanda($pdo, $idParam) : null;
$produtos = get_products($pdo);

?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Comanda — Editar</title>

  <style>
:root {
    --bg: rgb(59, 75, 93);               /* fundo geral do sistema */
    --card: #ffffff;                     /* cartão branco do sistema */
    --card-contrast: #1b263b;            /* contraste azul escuro */
    --text: #072433;                      /* texto escuro para cards */
    --muted: #555;                        /* texto secundário */
    --highlight: #0077b6;                 /* destaque azul */
    --success: #16a34a;
    --danger: #ef4444;
    --input-bg: #f0f0f0;                  /* inputs claros como no sistema */
    --shadow: 0 6px 16px rgba(0,0,0,0.25);
}

/* reset + base */
*{box-sizing:border-box;}
html,body{height:100%;}
body{
  margin:0;
  font-family:"Segoe UI",Tahoma,Arial,sans-serif;
  background-color:var(--bg);
  color:var(--text);
  display:flex;
  align-items:flex-start;
  justify-content:center;
  padding:28px;
}

/* container */
.box{
  width:100%;
  max-width:980px;
  background: var(--card);
  border-radius:14px;
  padding:20px;
  box-shadow:var(--shadow);
  border: 1px solid rgba(0,0,0,0.1);
  overflow:hidden;
}

/* header */
.comanda-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  margin-bottom:18px;
}
.comanda-title{
  display:flex;
  gap:14px;
  align-items:center;
}
.logo-chip{
  width:64px;height:64px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,var(--highlight),var(--card-contrast));
  box-shadow: var(--shadow);
  font-weight:700;font-size:20px;color:#fff;
}
.meta{
  display:flex;flex-direction:column;
}
.meta .title{font-size:20px;font-weight:700;color:var(--text)}
.meta .subtitle{font-size:13px;color:var(--muted);margin-top:3px}

.status-badge{
  padding:8px 12px;border-radius:999px;
  color:#fff;
  font-weight:600;
  display:inline-flex;align-items:center;gap:10px;
  box-shadow:var(--shadow);
}
.status-open{ background: var(--highlight);}
.status-closed{ background: var(--success);}
.status-cancel{ background: var(--danger);}

/* forms */
form{margin:0}
.controls{
  display:flex;
  gap:12px;
  align-items:center;
  margin-bottom:14px;
  flex-wrap:wrap;
}

select, input[type=number]{
  background: var(--input-bg);
  border:1px solid #ccc;
  color:#000;
  padding:10px 12px;
  border-radius:10px;
  font-size:15px;
  outline: none;
}
select{appearance:none; -webkit-appearance:none; padding-right:40px;}

/* buttons */
.btn{
  display:inline-flex;align-items:center;gap:10px;justify-content:center;
  padding:10px 16px;border-radius:12px;border:none;cursor:pointer;
  font-weight:700;color:#fff;font-size:14px;
  transition: transform .12s ease, box-shadow .12s ease, opacity .12s;
  box-shadow: var(--shadow);
}

.btn:active{transform:translateY(1px);}
.btn:disabled{opacity:.6;cursor:not-allowed;box-shadow:none;transform:none;}

.btn-primary{background: var(--highlight);}
.btn-success{background: var(--success);}
.btn-danger{background: var(--danger);}
.btn-ghost{background:transparent;border:1px solid #ccc;color:#fff;padding:8px 12px;border-radius:10px;}

/* tabela */
.items-table{
  width:100%;border-collapse:collapse;margin-top:8px;
  border-radius:12px;overflow:hidden;background:transparent;
}
.items-table thead th{
  background: var(--card-contrast);
  color:#fff;
  padding:12px 14px;font-weight:700;font-size:13px;text-align:left;
}
.items-table tbody td{
  padding:14px;border-bottom:1px dashed rgba(0,0,0,0.05);
  color:var(--text);font-size:14px;
}
.items-table tbody tr:hover td{ background: rgba(0,119,182,0.1); transform: translateY(-1px); }
.total-row td{font-weight:800;font-size:15px;padding:12px 14px;background:transparent}

/* ações */
.actions{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:16px}

/* mensagens */
.msg{padding:12px;border-radius:10px;margin-bottom:12px;font-weight:700;color:var(--text)}
.msg.ok{background: rgba(22,163,74,0.12); color:var(--success)}
.msg.err{background: rgba(239,68,68,0.12); color:var(--danger)}

/* responsivo */
@media(max-width:820px){
  .comanda-header{flex-direction:column;align-items:flex-start;gap:10px}
  .controls{flex-direction:column;align-items:stretch}
  .logo-chip{width:56px;height:56px}
  .actions{justify-content:stretch}
  .items-table thead th{font-size:12px}
}

  </style>
</head>

<body>
  <div class="box">
    <div class="comanda-header">
      <div class="comanda-title">
        <div class="logo-chip">PD</div>
        <div class="meta">
          <div class="title">Comanda — Edição</div>
          <div class="subtitle">Operação rápida — painel de vendas</div>
        </div>
      </div>

      <?php if ($comanda): ?>
        <?php
          $st = $comanda['status'] ?? 'ABERTA';
          $cls = $st === 'ABERTA' ? 'status-open' : ($st === 'FECHADA' ? 'status-closed' : 'status-cancel');
        ?>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
          <div class="status-badge <?= $cls ?>"><?= htmlspecialchars($comanda['status']) ?></div>
          <div style="font-size:12px;color:var(--muted)">ID <?= htmlspecialchars($comanda['ID_vendas']) ?> — <?= htmlspecialchars($comanda['Nome_func'] ?? '-') ?></div>
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
          <div class="status-badge" style="background:transparent;color:var(--muted);border:1px dashed rgba(255,255,255,0.03)">Sem comanda</div>
          <div style="font-size:12px;color:var(--muted)">Clique em criar para iniciar</div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!$comanda): ?>
      <p style="color:var(--muted);margin-bottom:12px">Nenhuma comanda selecionada no momento.</p>
      <form method="post" style="display:inline-block">
        <input type="hidden" name="acao" value="nova">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <button class="btn btn-primary" type="submit" title="Criar nova comanda">➕ Criar nova comanda</button>
      </form>
      <?php if ($msg): ?>
        <div class="msg <?= strpos($msg, 'Erro') === 0 ? 'err' : 'ok' ?>"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

    <?php else: ?>

      <?php if ($msg): ?>
        <div class="msg <?= strpos($msg, 'Erro') === 0 ? 'err' : 'ok' ?>"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div style="color:var(--muted);margin-bottom:8px">
        <strong>Comanda:</strong> <?= htmlspecialchars($comanda['ID_vendas']) ?> —
        <strong>Status:</strong> <?= htmlspecialchars($comanda['status']) ?> —
        <strong>Funcionário:</strong> <?= htmlspecialchars($comanda['Nome_func'] ?? '-') ?>
      </div>

      <?php if ($comanda['status'] === 'ABERTA'): ?>
        <form id="addItemForm" method="post" style="margin-bottom:8px">
          <input type="hidden" name="acao" value="add_item">
          <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
          <div class="controls">
            <select name="id_produto" required style="min-width:260px">
              <option value="">-- Selecionar produto --</option>
              <?php foreach ($produtos as $p): ?>
                <option value="<?= $p['ID_produto'] ?>"><?= htmlspecialchars($p['Nome_prod']) ?> — R$ <?= number_format($p['Preco_unitario'], 2, ',', '.') ?> (Est: <?= (int) $p['Qntd_produto'] ?>)</option>
              <?php endforeach; ?>
            </select>

            <input type="number" name="quantidade" value="1" min="1" style="width:110px">

            <button class="btn btn-primary" type="submit" title="Adicionar item">
              <!-- emoji + texto mantêm compatibilidade -->
              ➕ Adicionar
            </button>

            <button type="button" class="btn btn-ghost" onclick="document.getElementById('addItemForm').reset();" title="Limpar campos">Limpar</button>
          </div>
        </form>
      <?php endif; ?>

      <table class="items-table" role="table">
        <thead>
          <tr>
            <th>Produto</th>
            <th style="width:90px;text-align:center">Qtd</th>
            <th style="width:140px;text-align:right">Valor</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($comanda['itens'])):
            foreach ($comanda['itens'] as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it['Nome_prod'] ?? '-') ?></td>
                <td style="text-align:center"><?= (int) $it['Quantidade'] ?></td>
                <td style="text-align:right">R$ <?= number_format($it['valor_total'], 2, ',', '.') ?></td>
              </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="3" style="text-align:center;color:var(--muted);padding:22px">Nenhum item nesta comanda.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td style="text-align:left">TOTAL</td>
            <td></td>
            <td style="text-align:right">R$ <?= number_format($comanda['total'], 2, ',', '.') ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="actions">
        <?php if ($comanda['status'] === 'ABERTA'): ?>
          <form method="post" style="display:inline">
            <input type="hidden" name="acao" value="fechar">
            <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <button class="btn btn-success" type="submit" onclick="return confirm('Confirma fechar esta comanda?')">✔ Fechar</button>
          </form>

          <form method="post" style="display:inline">
            <input type="hidden" name="acao" value="cancelar">
            <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <button class="btn btn-danger" type="submit" onclick="return confirm('Confirma cancelar esta comanda?')">✖ Cancelar</button>
          </form>

          <form method="post" style="display:inline">
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <button class="btn btn-ghost" type="submit">💾 Salvar</button>
          </form>

          <form method="post" style="display:inline" onsubmit="return confirm('Registrar pagamento?')">
            <input type="hidden" name="acao" value="pagar">
            <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="metodo" value="DINHEIRO">
            <input type="hidden" name="valor_pago" value="<?= number_format($comanda['total'], 2, '.', '') ?>">
            <button class="btn btn-primary" type="submit">💵 Confirmar Pagamento</button>
          </form>
        <?php else: ?>
          <form method="post" style="display:inline">
            <input type="hidden" name="acao" value="reabrir">
            <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <button class="btn btn-primary" type="submit">🔄 Reabrir</button>
          </form>
        <?php endif; ?>

        <a class="print-link" href="?id=<?= (int) $comanda['ID_vendas'] ?>&print=1" target="_blank">🖨 Abrir impressão</a>
      </div>

    <?php endif; ?>
  </div>

  <script>
    // JS minimal para captura AJAX do formulário de adicionar item
    const addForm = document.getElementById('addItemForm');
    if (addForm) {
      addForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const data = new FormData(addForm);
        try {
          const res = await fetch(window.location.pathname + window.location.search, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: data,
            credentials: 'same-origin'
          });
          const ct = res.headers.get('content-type') || '';
          if (!res.ok) {
            if (ct.indexOf('application/json') !== -1) {
              const j = await res.json();
              alert(j.msg || 'Erro');
            } else {
              const t = await res.text();
              console.error(t);
              alert('Erro no servidor');
            }
            return;
          }
          if (ct.indexOf('application/json') !== -1) {
            const j = await res.json();
            if (j.success) {
              if (j.redirect) { window.location = j.redirect; return; }
              location.reload();
            } else alert(j.msg || 'Falha');
          } else {
            location.reload();
          }
        } catch (err) {
          console.error(err);
          alert('Erro: ' + err.message);
        }
      });
    }
  </script>
</body>

</html>
