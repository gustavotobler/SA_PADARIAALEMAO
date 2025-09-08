<?php
// comanda_single_styled.php
// Arquivo atualizado: CSS/Sidebar alinhados; addItemForm via AJAX atualiza tabela sem reload.
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
      $p = $pdo->prepare("SELECT Preco_unitario, Qntd_produto, Nome_prod FROM produtos WHERE ID_produto = ?");
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
        // devolver também informações do item recém-adicionado para atualização no client
        send_json([
          'success' => true,
          'msg' => 'Item adicionado',
          'novo_total' => (float) ($totalRow['total'] ?? 0),
          'item' => [
            'ID_produto' => $id_prod,
            'Nome_prod' => $prod['Nome_prod'] ?? '',
            'Quantidade' => $qtd,
            'valor_unitario' => number_format($valor_unitario, 2, '.', ''),
            'valor_total' => number_format($valor_total, 2, '.', '')
          ]
        ]);
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

// ===== Impressão: se ?print=1 presente, gerar página de impressão minimalista =====
if (isset($_GET['print']) && $comanda) {
  // reusar impressão do primeiro arquivo
  $itemsHtml = '';
  foreach ($comanda['itens'] as $it) {
    $nome = htmlspecialchars($it['Nome_prod'] ?? '-');
    $qtd = (int) $it['Quantidade'];
    $valor = number_format($it['valor_total'], 2, ',', '.');
    $itemsHtml .= "<tr><td style=\"padding:6px 0;\">" . $nome . "</td><td style=\"text-align:center;padding:6px 0;width:60px\">" . $qtd . "</td><td style=\"text-align:right;padding:6px 0;width:90px\">R$ " . $valor . "</td></tr>";
  }

  $vendaData = htmlspecialchars($comanda['venda_data'] ?? '');
  $total = 'R$ ' . number_format($comanda['total'], 2, ',', '.');
  $func = htmlspecialchars($comanda['Nome_func'] ?? '-');
  $idv = (int) $comanda['ID_vendas'];

  echo "<!doctype html><html lang=\"pt-br\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Imprimir Comanda #$idv</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;color:#111;margin:20px}
    .ticket{max-width:420px;margin:0 auto}
    h1{font-size:18px;margin:0 0 8px}
    .meta{font-size:13px;color:#444;margin-bottom:10px}
    table{width:100%;border-collapse:collapse;margin-bottom:10px}
    td{font-size:13px}
    .total{font-weight:800;font-size:16px;border-top:1px dashed #ccc;padding-top:8px}
    @media print{body{margin:0} .no-print{display:none}}
  </style>
  </head><body onload=\"setTimeout(()=>{window.print();window.close()},300);\">
  <div class=\"ticket\">
    <h1>Padaria do Alemão</h1>
    <div class=\"meta\">Comanda: <strong>#$idv</strong> — Funcionário: <strong>$func</strong><br>Data: <strong>$vendaData</strong></div>
    <table>
      <thead>
        <tr><th style=\"text-align:left\">Produto</th><th style=\"text-align:center\">Qtd</th><th style=\"text-align:right\">Valor</th></tr>
      </thead>
      <tbody>
        $itemsHtml
      </tbody>
    </table>
    <div class=\"total\">TOTAL: <span style=\"float:right\">$total</span></div>
    <div style=\"clear:both;height:8px\"></div>
    <div style=\"font-size:12px;color:#666;margin-top:12px\">Obrigado pela preferência!</div>
    <div class=\"no-print\" style=\"margin-top:12px;\"><button onclick=\"window.print()\">📄 Imprimir</button></div>
  </div>
  </body></html>";
  exit();
}

?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Comanda — Editar</title>

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root {
      --sidebar-width: 240px;
      --sidebar-collapsed-width: 60px;
      --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
      --primary-text: #f8f9fa;
      --hover-bg: #1e3a5f;
      --highlight: #0077b6;
      --card-bg: #1c2a3a;
      /* cartão acinzentado */
      --card-contrast: #243447;
      /* contraste mais acinzentado */
      --green: #06a34a;
      --blue: #00b4d8;
      --text: #f8f9fa;
      --muted: #94a3b8;
      --accent: #0077b6;
      --success: #16a34a;
      --danger: #ef4444;
      --glass-border: rgba(255, 255, 255, 0.04);
    }

    /* Reset + base */
    * {
      box-sizing: border-box
    }

    html,
    body {
      height: 100%
    }

    body {
      margin: 0;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, 'Helvetica Neue', Arial;
      background: linear-gradient(180deg, rgb(59, 75, 93), #0b2e3f);
      /* fundo acinzentado */
      min-height: 100vh;
      color: var(--text);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* Sidebar (mantida sem alterações) */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding-top: 20px;
      transition: width .3s;
      box-shadow: 3px 0 10px rgba(0, 0, 0, .3);
    }

    .sidebar.collapsed {
      width: var(--sidebar-collapsed-width);
    }

    .sidebar a {
      display: flex;
      align-items: center;
      color: var(--primary-text);
      text-decoration: none;
      padding: 15px 20px;
      white-space: nowrap;
      transition: background .2s, padding .3s
    }

    .sidebar a:hover {
      background: var(--hover-bg);
      border-left: 4px solid var(--highlight);
      padding-left: 16px
    }

    .sidebar .icon {
      margin-right: 8px
    }

    .sidebar.collapsed .text {
      display: none
    }

    .sidebar.collapsed .icon {
      margin-right: 0;
      justify-content: center
    }

    .toggle-btn {
      cursor: pointer;
      text-align: center;
      margin-bottom: 20px;
      font-size: 22px;
      color: var(--primary-text)
    }

    /* page-wrap / container */
    .page-wrap {
      margin-left: var(--sidebar-width);
      padding: 28px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      transition: margin-left .28s ease
    }

    .container {
      width: 100%;
      max-width: 1100px;
      padding: 20px;
      border-radius: 14px;
      background: linear-gradient(180deg, rgba(28, 42, 58, 0.85), rgba(23, 34, 49, 0.95));
      /* acinzentado mais elegante */
      box-shadow: 0 18px 50px rgba(2, 12, 24, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.02);
      border: 1px solid var(--glass-border);
      color: var(--text);
    }

    /* header / brand */
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 14px;
      flex-wrap: wrap
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px
    }

    .logo {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 800;
      font-size: 20px;
      background: linear-gradient(135deg, #0e6190, #0093d0);
      box-shadow: 0 8px 24px rgba(13, 73, 119, 0.12)
    }

    .meta {
      display: flex;
      flex-direction: column
    }

    .meta .title {
      font-size: 20px;
      font-weight: 800;
      color: var(--text)
    }

    .meta .sub {
      font-size: 13px;
      color: var(--muted);
      margin-top: 6px
    }

    /* status */
    .status-block {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 6px
    }

    .badge {
      padding: 8px 12px;
      border-radius: 10px;
      color: #fff;
      font-weight: 800;
      font-size: 13px
    }

    .badge.open {
      background: linear-gradient(90deg, var(--success), #12b45a)
    }

    .badge.closed {
      background: linear-gradient(90deg, var(--danger), #ff6b6b)
    }

    .badge.cancel {
      background: linear-gradient(90deg, #6b7280, #334155)
    }

    .info-line {
      font-size: 13px;
      color: var(--muted)
    }

    /* form inputs */
    input[type=text],
    select,
    input[type=number],
    input[type=search] {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--glass-border);
      padding: 10px 12px;
      border-radius: 10px;
      font-size: 15px;
      color: var(--text);
      outline: none;
      min-height: 44px;
      transition: box-shadow .12s, border-color .12s;
      -webkit-appearance: none;
    }

    input::placeholder {
      color: rgba(230, 244, 251, 0.45)
    }

    select {
      min-width: 260px;
      padding-right: 38px
    }

    /* formulário de adicionar item (alinhado e responsivo) */
    #addItemForm {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
      background: rgba(255, 255, 255, 0.02);
      padding: 10px;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.02);
      margin-bottom: 12px;
    }

    #addItemForm>* {
      margin: 0
    }

    #produtoSearch {
      min-width: 180px
    }

    #produtoSelect {
      min-width: 280px
    }

    #quantidadeInput {
      width: 110px;
      min-height: 44px;
      padding: 10px;
      border-radius: 10px
    }

    /* buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
      padding: 10px 14px;
      border-radius: 9px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      color: #fff;
      font-size: 14px;
      transition: transform .08s, box-shadow .12s
    }

    .btn:active {
      transform: translateY(1px)
    }

    .btn-primary {
      background: linear-gradient(90deg, #1373b8, #0093d0);
      box-shadow: 0 10px 30px rgba(4, 30, 60, 0.25)
    }

    .btn-ghost {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.06);
      color: var(--text);
      padding: 9px 12px
    }

    .btn-success {
      background: linear-gradient(90deg, #14a352, #16ca6b)
    }

    .btn-danger {
      background: linear-gradient(90deg, #ff5b5b, #ff7b7b)
    }

    /* table */
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      border-radius: 10px;
      overflow: hidden;
      background: transparent
    }

    .table thead th {
      background: linear-gradient(90deg, #063a52, #0e6190);
      color: #fff;
      padding: 12px 14px;
      text-align: left;
      font-weight: 700;
      font-size: 14px
    }

    .table tbody td {
      padding: 12px 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      color: var(--text);
      font-size: 14px
    }

    .table tbody tr:hover td {
      background: rgba(12, 45, 70, 0.04)
    }

    .table tfoot td {
      font-weight: 800;
      padding: 12px 14px;
      background: transparent;
      color: var(--text);
      font-size: 15px
    }

    /* empty / messages */
    .empty {
      padding: 20px;
      text-align: center;
      color: var(--muted)
    }

    .message {
      padding: 12px;
      border-radius: 10px;
      margin-bottom: 12px;
      font-weight: 700
    }

    .message.ok {
      background: rgba(18, 163, 74, 0.08);
      color: var(--success)
    }

    .message.err {
      background: rgba(239, 68, 68, 0.08);
      color: var(--danger)
    }

    /* actions */
    .actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      margin-top: 18px
    }

    .print-link {
      display: inline-block;
      padding: 10px 14px;
      border-radius: 10px;
      background: transparent;
      color: var(--text);
      border: 1px solid rgba(255, 255, 255, 0.04);
      text-decoration: none
    }

    /* small screens */
    @media(max-width:900px) {
      .page-wrap {
        margin-left: 0;
        padding: 16px
      }

      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px
      }

      select {
        min-width: 100%
      }

      #addItemForm {
        justify-content: stretch
      }

      .actions {
        justify-content: stretch
      }
    }

    /* Inputs e selects mais claros */
    input[type=text],
    select,
    input[type=number],
    input[type=search] {
      background: rgba(255, 255, 255, 0.12);
      /* cinza claro translúcido */
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #fff;
      /* texto branco */
    }

    select option {
      background: #1c2a3a;
      /* fundo escuro no dropdown */
      color: #fff;
    }


    /* Botão fantasma (limpar) mais visível */
    .btn-ghost {
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: #e6f4fb;
    }
  </style>
</head>

<body>
  <!-- SIDEBAR: alinhada ao segundo arquivo -->
  <nav class="sidebar" id="sidebar" aria-label="Menu lateral">
    <div class="toggle-btn" onclick="toggleSidebar()" title="Abrir/Fechar menu">☰</div>

    <a href="inicial1.php" title="Voltar">
      <span class="icon"><span class="material-icons">arrow_back</span></span>
      <span class="text">Voltar</span>
    </a>

    <a href="comanda.php" title="Criar/Ver comanda" class="active">
      <span class="icon"><span class="material-icons">receipt</span></span>
      <span class="text">Criar Comanda</span>
    </a>

    <a href="ver_comandas.php" title="Ver todas as comandas">
      <span class="icon"><span class="material-icons">visibility</span></span>
      <span class="text">Ver Comandas</span>
    </a>
  </nav>

  <!-- page-wrap (reserva espaço para a sidebar) -->
  <div class="page-wrap" id="pageWrap">
    <div class="container" role="main" aria-live="polite">
      <div class="header">
        <div class="brand">
          <div class="logo">PD</div>
          <div class="meta">
            <div class="title">Comanda — Edição</div>
            <div class="sub">Operação rápida — painel de vendas</div>
          </div>
        </div>

        <?php if ($comanda): ?>
          <?php
          $st = $comanda['status'] ?? 'ABERTA';
          $cls = $st === 'ABERTA' ? 'open' : ($st === 'FECHADA' ? 'closed' : 'cancel');
          ?>
          <div class="status-block">
            <div class="badge <?= $cls ?>"><?= htmlspecialchars($comanda['status']) ?></div>
            <div class="info-line">ID <?= htmlspecialchars($comanda['ID_vendas']) ?> —
              <?= htmlspecialchars($comanda['Nome_func'] ?? '-') ?></div>
          </div>
        <?php else: ?>
          <div class="status-block">
            <div class="badge" style="background:transparent;color:var(--muted);border:1px dashed rgba(12,45,70,0.06)">Sem
              comanda</div>
            <div class="info-line">Clique em criar para iniciar</div>
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
          <div class="message <?= strpos($msg, 'Erro') === 0 ? 'err' : 'ok' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

      <?php else: ?>

        <?php if ($msg): ?>
          <div class="message <?= strpos($msg, 'Erro') === 0 ? 'err' : 'ok' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div style="color:var(--muted);margin-bottom:8px">
          <strong>Comanda:</strong> <?= htmlspecialchars($comanda['ID_vendas']) ?> —
          <strong>Status:</strong> <?= htmlspecialchars($comanda['status']) ?> —
          <strong>Funcionário:</strong> <?= htmlspecialchars($comanda['Nome_func'] ?? '-') ?>
        </div>

        <?php if ($comanda['status'] === 'ABERTA'): ?>
          <!-- FORM: corrigido, sem tags soltas -->
          <form id="addItemForm" method="post" style="margin-bottom:8px">
            <input type="hidden" name="acao" value="add_item">
            <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <input type="text" id="produtoSearch" name="produtoSearch" placeholder="Pesquisar produto..."
              aria-label="Pesquisar produto">
            <select name="id_produto" required aria-label="Produto" id="produtoSelect">
              <option value="">-- Selecionar produto --</option>
              <?php foreach ($produtos as $p): ?>
                <option value="<?= $p['ID_produto'] ?>"><?= htmlspecialchars($p['Nome_prod']) ?> — R$
                  <?= number_format($p['Preco_unitario'], 2, ',', '.') ?> (Est: <?= (int) $p['Qntd_produto'] ?>)</option>
              <?php endforeach; ?>
            </select>

            <input type="number" name="quantidade" value="1" min="1" aria-label="Quantidade" id="quantidadeInput">
            <button class="btn btn-primary" type="submit" title="Adicionar item">➕ Adicionar</button>
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('addItemForm').reset();"
              title="Limpar campos">Limpar</button>
          </form>
        <?php endif; ?>

        <table class="table" role="table" aria-label="Itens da comanda" id="itensTable">
          <thead>
            <tr>
              <th>Produto</th>
              <th style="width:90px;text-align:center">Qtd</th>
              <th style="width:140px;text-align:right">Valor</th>
            </tr>
          </thead>
          <tbody id="itensBody">
            <?php if (!empty($comanda['itens'])):
              foreach ($comanda['itens'] as $it): ?>
                <tr>
                  <td><?= htmlspecialchars($it['Nome_prod'] ?? '-') ?></td>
                  <td style="text-align:center"><?= (int) $it['Quantidade'] ?></td>
                  <td style="text-align:right">R$ <?= number_format($it['valor_total'], 2, ',', '.') ?></td>
                </tr>
              <?php endforeach; else: ?>
              <tr id="noItemsRow">
                <td colspan="3" class="empty">Nenhum item nesta comanda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr>
              <td style="text-align:left">TOTAL</td>
              <td></td>
              <td style="text-align:right" id="totalCell">R$ <?= number_format($comanda['total'], 2, ',', '.') ?></td>
            </tr>
          </tfoot>
        </table>

        <div class="actions">
          <?php if ($comanda['status'] === 'ABERTA'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="acao" value="fechar">
              <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <button class="btn btn-success" type="submit" onclick="return confirm('Confirma fechar esta comanda?')">✔
                Fechar</button>
            </form>

            <form method="post" style="display:inline">
              <input type="hidden" name="acao" value="cancelar">
              <input type="hidden" name="id_venda" value="<?= (int) $comanda['ID_vendas'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <button class="btn btn-danger" type="submit" onclick="return confirm('Confirma cancelar esta comanda?')">✖
                Cancelar</button>
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

          <a class="print-link" href="?id=<?= (int) $comanda['ID_vendas'] ?>&print=1" target="_blank"
            title="Abrir impressão">🖨 Abrir impressão</a>
        </div>

      <?php endif; ?>
    </div>
  </div> <!-- .page-wrap -->

  <script>
    // toggle da sidebar (mantendo comportamento do segundo arquivo)
    const sidebar = document.getElementById('sidebar');
    function toggleSidebar() {
      sidebar.classList.toggle('collapsed');
      const pageWrap = document.getElementById('pageWrap');
      const rootStyles = getComputedStyle(document.documentElement);
      const collapsed = rootStyles.getPropertyValue('--sidebar-collapsed-width').trim() || '60px';
      const normal = rootStyles.getPropertyValue('--sidebar-width').trim() || '240px';
      pageWrap.style.marginLeft = sidebar.classList.contains('collapsed') ? collapsed : normal;
    }

    // margem inicial correta
    (function initPageWrapMargin() {
      const pw = document.getElementById('pageWrap');
      pw.style.marginLeft = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width').trim();
    })();

    // ---------- AJAX: adicionar item sem reload (atualiza DOM) ----------
    const addForm = document.getElementById('addItemForm');
    if (addForm) {
      addForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(addForm);
        try {
          const res = await fetch(window.location.pathname + window.location.search, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
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
              // se o servidor mandar redirect, segue
              if (j.redirect) { window.location = j.redirect; return; }

              // se veio item + novo_total, atualiza tabela dinamicamente
              if (j.item) {
                // remover linha "nenhum item" se existir
                const noRow = document.getElementById('noItemsRow');
                if (noRow) noRow.remove();

                const tbody = document.getElementById('itensBody');
                const tr = document.createElement('tr');
                const tdNome = document.createElement('td'); tdNome.textContent = j.item.Nome_prod || '-';
                const tdQtd = document.createElement('td'); tdQtd.style.textAlign = 'center'; tdQtd.textContent = j.item.Quantidade || '1';
                const tdVal = document.createElement('td'); tdVal.style.textAlign = 'right';
                const valorFormatado = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(j.item.valor_total || 0));
                tdVal.textContent = valorFormatado;
                tr.appendChild(tdNome); tr.appendChild(tdQtd); tr.appendChild(tdVal);
                tbody.appendChild(tr);

                // atualizar total no rodapé
                const totalCell = document.getElementById('totalCell');
                if (totalCell && (typeof j.novo_total !== 'undefined')) {
                  totalCell.textContent = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(j.novo_total || 0));
                }

                // limpar seleção e quantidade
                const sel = document.getElementById('produtoSelect');
                const qtdInp = document.getElementById('quantidadeInput');
                if (sel) sel.value = '';
                if (qtdInp) qtdInp.value = '1';

              } else {
                // fallback: recarrega
                location.reload();
              }

            } else {
              alert(j.msg || 'Falha');
            }
          } else {
            // resposta não-json -> recarrega
            location.reload();
          }

        } catch (err) {
          console.error(err);
          alert('Erro: ' + err.message);
        }
      });
    }
    // filtro de produtos (pesquisar)
    const produtoSearch = document.getElementById('produtoSearch');
    const produtoSelect = document.getElementById('produtoSelect');
    if (produtoSearch && produtoSelect) {
      produtoSearch.addEventListener('input', function (e) {
        const q = produtoSearch.value.trim().toLowerCase();
        let any = false;
        for (let i = 0; i < produtoSelect.options.length; i++) {
          const opt = produtoSelect.options[i];
          const text = (opt.textContent || opt.innerText || '').toLowerCase();
          if (!q) {
            opt.hidden = false; opt.style.display = '';
            if (opt.value !== '') any = true;
          } else {
            const match = text.indexOf(q) !== -1;
            opt.hidden = !match;
            opt.style.display = match ? '' : 'none';
            if (match && opt.value !== '') any = true;
          }
        }
        // se não houver opções visíveis, limpa a seleção
        if (!any && produtoSelect) produtoSelect.value = '';
      });
    }

    // manter compatibilidade com outras ações do modal/print do segundo arquivo se necessário
    const phpSelf = "<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>";

  </script>
</body>

</html>