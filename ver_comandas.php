<?php
session_start();

/* ===== Config DB ===== */
$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

/* ===== Segurança simples ===== */
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}

/* ===== Nome do funcionário para a sidebar (mantive fetch, mas iremos exibir 'kerry king' conforme solicitado) ===== */
$id_Nome = $_SESSION['ID_func'] ?? null;
$nomeFuncSess = 'Usuário';
if ($id_Nome) {
    $stmtNome = $conn->prepare("SELECT Nome_func FROM funcionario WHERE ID_func = :ID_func LIMIT 1");
    $stmtNome->execute([':ID_func' => $id_Nome]);
    $nomeFuncSess = $stmtNome->fetchColumn() ?? $nomeFuncSess;
}

/* Forçar exibição como 'kerry king' conforme pedido */
$nomeFunc = 'kerry king';

/* ===== CSRF ===== */
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf'];

/* ===== Helpers ===== */
function is_ajax()
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}
function send_json($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

/* ===== Endpoints: details (JSON), print (HTML imprimível), report (HTML) ===== */
if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sqlV = "SELECT v.ID_vendas, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
             FROM vendas v
             LEFT JOIN funcionario f ON v.ID_func = f.ID_func
             WHERE v.ID_vendas = :id LIMIT 1";
    $stmtV = $conn->prepare($sqlV);
    $stmtV->execute([':id' => $id]);
    $venda = $stmtV->fetch();

    if (!$venda) {
        send_json(['error' => 'Comanda não encontrada'], 404);
    }

    $sqlItems = "SELECT p.Nome_prod, iv.Quantidade, iv.valor_unitario, iv.valor_total
                 FROM itens_vendas iv
                 LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
                 WHERE iv.ID_vendas = :id";
    $stmtI = $conn->prepare($sqlItems);
    $stmtI->execute([':id' => $id]);
    $items = $stmtI->fetchAll();

    $total = 0.0;
    foreach ($items as $it) $total += floatval($it['valor_total'] ?? 0);

    $resp = [
        'id' => $venda['ID_vendas'],
        'venda_data' => $venda['venda_data'],
        'hora' => isset($venda['venda_data']) ? substr($venda['venda_data'], 11, 8) : '',
        'funcionario' => $venda['Nome_func'] ?? null,
        'status' => $venda['status'] ?? null,
        'forma_pagamento' => $venda['forma_pagamento'] ?? null,
        'total_comanda' => number_format($total, 2, '.', ''),
        'items' => $items
    ];
    send_json($resp);
}

/* ===== Imprimir comanda (abre em nova aba) ===== */
if (isset($_GET['action']) && $_GET['action'] === 'print' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sqlV = "SELECT v.ID_vendas, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
             FROM vendas v
             LEFT JOIN funcionario f ON v.ID_func = f.ID_func
             WHERE v.ID_vendas = :id LIMIT 1";
    $stmtV = $conn->prepare($sqlV);
    $stmtV->execute([':id' => $id]);
    $venda = $stmtV->fetch();

    if (!$venda) {
        echo "<p>Comanda não encontrada.</p>";
        exit;
    }

    $sqlItems = "SELECT p.Nome_prod, iv.Quantidade, iv.valor_unitario, iv.valor_total
                 FROM itens_vendas iv
                 LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
                 WHERE iv.ID_vendas = :id";
    $stmtI = $conn->prepare($sqlItems);
    $stmtI->execute([':id' => $id]);
    $items = $stmtI->fetchAll();

    $total = 0.0;
    foreach ($items as $it) $total += floatval($it['valor_total'] ?? 0);

    // saída HTML imprimível
    ?>
    <!doctype html>
    <html lang="pt-br">
    <head>
      <meta charset="utf-8">
      <title>Imprimir Comanda #<?= htmlspecialchars($venda['ID_vendas']) ?></title>
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <style>
        body{font-family:Arial,Helvetica,sans-serif;color:#111;padding:18px}
        h1{font-size:20px;margin-bottom:6px}
        .meta{margin-bottom:12px}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{padding:8px;border-bottom:1px solid #ddd;text-align:left}
        .total{margin-top:12px;font-weight:800}
        @media print{button{display:none}}
        .print-btn{padding:8px 12px;background:#0077b6;color:#fff;border:none;border-radius:6px;cursor:pointer;margin-bottom:12px}
      </style>
    </head>
    <body>
      <button class="print-btn" onclick="window.print()">Imprimir</button>
      <h1>Comanda #<?= htmlspecialchars($venda['ID_vendas']) ?></h1>
      <div class="meta">
        <div><strong>Funcionário:</strong> <?= htmlspecialchars($venda['Nome_func'] ?: '-') ?></div>
        <div><strong>Data / Hora:</strong> <?= htmlspecialchars($venda['venda_data'] ?: '-') ?></div>
        <div><strong>Status:</strong> <?= htmlspecialchars($venda['status'] ?: '-') ?></div>
        <div><strong>Pagamento:</strong> <?= htmlspecialchars($venda['forma_pagamento'] ?: '-') ?></div>
      </div>

      <table>
        <thead>
          <tr><th>Produto</th><th>Qtd</th><th>Preço un.</th><th>Total</th></tr>
        </thead>
        <tbody>
        <?php if ($items): foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['Nome_prod'] ?? '-') ?></td>
            <td><?= htmlspecialchars($it['Quantidade'] ?? '-') ?></td>
            <td><?= isset($it['valor_unitario']) ? number_format($it['valor_unitario'],2,',','.') : '-' ?></td>
            <td><?= isset($it['valor_total']) ? number_format($it['valor_total'],2,',','.') : '-' ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4">Nenhum item registrado nesta comanda.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <div class="total">TOTAL: R$ <?= number_format($total,2,',','.') ?></div>
    </body>
    </html>
    <?php
    exit;
}

/* ===== Relatório: individual (id) ou geral com filtros ===== */
if (isset($_GET['action']) && $_GET['action'] === 'report') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($id) {
        $sqlV = "SELECT v.ID_vendas, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
                 FROM vendas v
                 LEFT JOIN funcionario f ON v.ID_func = f.ID_func
                 WHERE v.ID_vendas = :id LIMIT 1";
        $stmtV = $conn->prepare($sqlV);
        $stmtV->execute([':id' => $id]);
        $venda = $stmtV->fetch();

        if (!$venda) { echo "<p>Comanda não encontrada.</p>"; exit; }

        $sqlItems = "SELECT p.Nome_prod, iv.Quantidade, iv.valor_unitario, iv.valor_total
                     FROM itens_vendas iv
                     LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
                     WHERE iv.ID_vendas = :id";
        $stmtI = $conn->prepare($sqlItems);
        $stmtI->execute([':id' => $id]);
        $items = $stmtI->fetchAll();

        $total = 0.0;
        foreach ($items as $it) $total += floatval($it['valor_total'] ?? 0);
        ?>
        <!doctype html>
        <html lang="pt-br">
        <head>
          <meta charset="utf-8">
          <title>Relatório — Comanda #<?= htmlspecialchars($venda['ID_vendas']) ?></title>
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <style>
            body{font-family:Arial,Helvetica,sans-serif;color:#111;padding:18px}
            .top{display:flex;justify-content:space-between;align-items:center;gap:12px}
            h1{font-size:20px;margin-bottom:6px}
            table{width:100%;border-collapse:collapse;margin-top:8px}
            th,td{padding:8px;border-bottom:1px solid #ddd;text-align:left}
            .total{margin-top:12px;font-weight:800}
            .printbar{margin-bottom:12px}
            @media print{.printbar{display:none}}
            button{padding:8px 12px;border-radius:6px;border:none;cursor:pointer}
          </style>
        </head>
        <body>
          <div class="printbar">
            <button onclick="window.print()">Gerar PDF / Imprimir</button>
            <button onclick="window.close()">Fechar</button>
          </div>
          <div class="top">
            <h1>Relatório — Comanda #<?= htmlspecialchars($venda['ID_vendas']) ?></h1>
            <div>
              <div><strong>Funcionário:</strong> <?= htmlspecialchars($venda['Nome_func'] ?: '-') ?></div>
              <div><strong>Data / Hora:</strong> <?= htmlspecialchars($venda['venda_data'] ?: '-') ?></div>
              <div><strong>Status:</strong> <?= htmlspecialchars($venda['status'] ?: '-') ?></div>
              <div><strong>Pagamento:</strong> <?= htmlspecialchars($venda['forma_pagamento'] ?: '-') ?></div>
            </div>
          </div>

          <table>
            <thead>
              <tr><th>Produto</th><th>Qtd</th><th>Preço un.</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php if ($items): foreach ($items as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it['Nome_prod'] ?? '-') ?></td>
                <td><?= htmlspecialchars($it['Quantidade'] ?? '-') ?></td>
                <td><?= isset($it['valor_unitario']) ? number_format($it['valor_unitario'],2,',','.') : '-' ?></td>
                <td><?= isset($it['valor_total']) ? number_format($it['valor_total'],2,',','.') : '-' ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4">Nenhum item registrado nesta comanda.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>

          <div class="total">TOTAL: R$ <?= number_format($total,2,',','.') ?></div>
        </body>
        </html>
        <?php
        exit;
    } else {
        $start = trim($_GET['start'] ?? '');
        $end = trim($_GET['end'] ?? '');
        $filterFunc = trim($_GET['func'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');

        $where = [];
        $params = [];
        if ($start && $end) {
            $where[] = "DATE(v.venda_data) BETWEEN :start AND :end";
            $params[':start'] = $start; $params[':end'] = $end;
        } elseif ($start) {
            $where[] = "DATE(v.venda_data) >= :start";
            $params[':start'] = $start;
        } elseif ($end) {
            $where[] = "DATE(v.venda_data) <= :end";
            $params[':end'] = $end;
        }
        if ($filterFunc !== '') { $where[] = "f.Nome_func LIKE :func"; $params[':func'] = '%' . $filterFunc . '%'; }
        if ($filterStatus !== '') { $where[] = "v.status = :status"; $params[':status'] = $filterStatus; }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT
                v.ID_vendas,
                v.venda_data,
                f.Nome_func,
                COALESCE(SUM(iv.valor_total),0) AS total_comanda,
                v.forma_pagamento,
                v.status
            FROM vendas v
            LEFT JOIN funcionario f ON v.ID_func = f.ID_func
            LEFT JOIN itens_vendas iv ON v.ID_vendas = iv.ID_vendas
            LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
            $whereSql
            GROUP BY v.ID_vendas, v.venda_data, f.Nome_func, v.forma_pagamento, v.status
            ORDER BY v.venda_data ASC
        ";
        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $comandasReport = $stmt->fetchAll();

        $totalGeral = 0.0;
        foreach ($comandasReport as $c) $totalGeral += floatval($c['total_comanda'] ?? 0);

        ?>
        <!doctype html>
        <html lang="pt-br">
        <head>
          <meta charset="utf-8">
          <title>Relatório — Comandas</title>
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <style>
            body{font-family:Arial,Helvetica,sans-serif;color:#111;padding:18px}
            h1{font-size:20px;margin-bottom:6px}
            table{width:100%;border-collapse:collapse;margin-top:8px}
            th,td{padding:8px;border-bottom:1px solid #ddd;text-align:left}
            .total{margin-top:12px;font-weight:800}
            .printbar{margin-bottom:12px}
            @media print{.printbar{display:none}}
            button{padding:8px 12px;border-radius:6px;border:none;cursor:pointer}
          </style>
        </head>
        <body>
          <div class="printbar">
            <button onclick="window.print()">Gerar PDF / Imprimir</button>
            <button onclick="window.close()">Fechar</button>
          </div>

          <h1>Relatório — Comandas</h1>
          <div>Filtros: <strong><?= htmlspecialchars($start ?: '∞') ?></strong> a <strong><?= htmlspecialchars($end ?: '∞') ?></strong><?php if ($filterFunc) echo ' • Funcionário: <strong>'.htmlspecialchars($filterFunc).'</strong>'; if ($filterStatus) echo ' • Status: <strong>'.htmlspecialchars($filterStatus).'</strong>'; ?></div>

          <table>
            <thead>
              <tr><th>#</th><th>Data / Hora</th><th>Funcionário</th><th>Status</th><th>Pagamento</th><th>Total (R$)</th></tr>
            </thead>
            <tbody>
            <?php if ($comandasReport): foreach ($comandasReport as $c): ?>
              <tr>
                <td><?= htmlspecialchars($c['ID_vendas']) ?></td>
                <td><?= htmlspecialchars($c['venda_data']) ?></td>
                <td><?= htmlspecialchars($c['Nome_func'] ?: '-') ?></td>
                <td><?= htmlspecialchars($c['status'] ?: '-') ?></td>
                <td><?= htmlspecialchars($c['forma_pagamento'] ?: '-') ?></td>
                <td style="text-align:right"><?= number_format($c['total_comanda'] ?? 0,2,',','.') ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="6">Nenhuma comanda encontrada para esse filtro.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>

          <div class="total">TOTAL GERAL: R$ <?= number_format($totalGeral,2,',','.') ?></div>
        </body>
        </html>
        <?php
        exit;
    }
}

/* ===== Página principal: filtros e listagem (GROUP_CONCAT preview) ===== */
$start = trim($_GET['start'] ?? '');
$end = trim($_GET['end'] ?? '');
$filterFunc = trim($_GET['func'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$comandas = [];

if ($start || $end || $filterFunc || $filterStatus) {
    $where = [];
    $params = [];
    if ($start && $end) {
        $where[] = "DATE(v.venda_data) BETWEEN :start AND :end";
        $params[':start'] = $start;
        $params[':end'] = $end;
    } elseif ($start) {
        $where[] = "DATE(v.venda_data) >= :start";
        $params[':start'] = $start;
    } elseif ($end) {
        $where[] = "DATE(v.venda_data) <= :end";
        $params[':end'] = $end;
    }
    if ($filterFunc !== '') { $where[] = "f.Nome_func LIKE :func"; $params[':func'] = '%' . $filterFunc . '%'; }
    if ($filterStatus !== '') { $where[] = "v.status = :status"; $params[':status'] = $filterStatus; }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT
            v.ID_vendas,
            v.venda_data,
            f.Nome_func,
            COALESCE(SUM(iv.valor_total),0) AS total_comanda,
            v.forma_pagamento,
            v.status,
            GROUP_CONCAT(CONCAT(p.Nome_prod,' (',iv.Quantidade,'x)') SEPARATOR '||') AS produtos_preview
        FROM vendas v
        LEFT JOIN funcionario f ON v.ID_func = f.ID_func
        LEFT JOIN itens_vendas iv ON v.ID_vendas = iv.ID_vendas
        LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
        $whereSql
        GROUP BY v.ID_vendas, v.venda_data, f.Nome_func, v.forma_pagamento, v.status
        ORDER BY v.venda_data ASC
    ";
    $stmt = $conn->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    $comandas = $stmt->fetchAll();
}

/* ===== HTML da página principal (filtros, cards, modal) ===== */
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Comandas — Relatório</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
:root{
  --sidebar-width: 240px;
  --sidebar-collapsed-width: 60px;
  --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
  --main-bg: rgb(59, 75, 93);
  --primary-text: #f8f9fa;
  --hover-bg: #1e3a5f;
  --highlight: #0077b6;
  --card-bg: #10161b; /* card mais neutro/acinzentado escuro */
  --card-contrast: #17222b;
  --green: #06a34a;
  --blue: #00b4d8;
  --text: #e6f4fb;
  --muted: #9ab0bf;
  --accent: #0093d0;
  --success: #16a34a;
  --danger: #ef4444;
  --glass-border: rgba(255,255,255,0.03);
  --soft-white: rgba(255,255,255,0.06);
}

/* Reset + base */
*{margin:0;padding:0;box-sizing:border-box;font-family:Inter, 'Segoe UI', Arial, sans-serif}
body{
  background:linear-gradient(180deg,var(--main-bg),#0b2e3f);
  color:var(--muted);
  min-height:100vh;
  display:block;
  -webkit-font-smoothing:antialiased;
}

/* Sidebar (mantido sem alterações) */
.sidebar {
    width: var(--sidebar-width);
    background:var(--sidebar-bg);
    height:100vh;
    position:fixed;
    display:flex;
    flex-direction:column;
    padding-top:20px;
    transition:width .3s;
    box-shadow:3px 0 10px rgba(0,0,0,.3);
}
.sidebar.collapsed{ width: var(--sidebar-collapsed-width); }
.sidebar a{
    display:flex;align-items:center;color:var(--primary-text);text-decoration:none;
    padding:15px 20px;white-space:nowrap;transition:background .2s,padding .3s
}
.sidebar a:hover{background:var(--hover-bg);border-left:4px solid var(--highlight);padding-left:16px}
.sidebar .icon{margin-right:8px}
.sidebar.collapsed .text{display:none}
.sidebar.collapsed .icon{margin-right:0;justify-content:center}
.toggle-btn{cursor:pointer;text-align:center;margin-bottom:20px;font-size:22px;color:var(--primary-text)}

/* Page container — reserva o espaço da sidebar via variável e centraliza o conteúdo */
.container{margin-left:var(--sidebar-width);padding:32px;display:flex;justify-content:center;min-height:100vh}

/* wrapper central onde o conteúdo realmente fica */
.main{width:100%;max-width:1200px}

/* Header */
.header{display:flex;flex-direction:column;align-items:center;gap:10px;margin-bottom:18px}
.h-title{color:#fff;font-size:20px;font-weight:700}

/* Filter panel — mais estilizado */
.filter-panel{
  background:linear-gradient(180deg, rgba(28, 42, 58, 0.85), rgba(28, 42, 58, 0.85));
  border-radius:12px;
  padding:14px 18px;
  display:flex;
  align-items:center;
  gap:12px;
  justify-content:space-between;
  box-shadow:0 10px 28px rgba(2,8,23,0.45);
  border:1px solid var(--glass-border);
  flex-wrap:wrap;
}
.filter-panel .field{display:flex;flex-direction:column;min-width:160px}
.filter-panel label{font-size:12px;color:var(--muted);margin-bottom:6px}
.filter-panel input[type=date], .filter-panel input[type=text], .filter-panel select, .filter-panel input[type=number]{
  padding:10px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);
  background: rgba(255,255,255,0.03); color:var(--text); min-width:160px;
  outline:none;
}
.filter-panel input[type=number]{-moz-appearance: textfield; appearance: none;}
.filter-panel input[type=number]::-webkit-outer-spin-button,
.filter-panel input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

/* search comanda area - destaque */
.field-comanda { min-width:220px; flex:0 0 220px; }
.field-comanda .input-wrap { display:flex; gap:8px; align-items:center; }
.field-comanda input { flex:1; }

/* Buttons */
.filter-actions{display:flex;gap:10px;align-items:center}
.btn{padding:10px 14px;border-radius:10px;border:none;cursor:pointer;font-weight:700;display:inline-flex;gap:8px;align-items:center}
.btn.primary{
  background:linear-gradient(90deg,var(--accent),#0e85c4);
  color:#fff;
  box-shadow: 0 8px 24px rgba(6,120,180,0.18);
  border:1px solid rgba(255,255,255,0.04);
}
.btn.primary:hover{transform:translateY(-2px);box-shadow: 0 14px 40px rgba(6,120,180,0.22);}
.btn.ghost{
  background:transparent;border:1px solid rgba(255,255,255,0.08);color:#fff;padding:10px 12px;
}
.btn.report{
  background:linear-gradient(90deg,#0ea5a4,#06b6d4);color:#012;
  box-shadow: 0 8px 20px rgba(6,150,160,0.12);
}

/* Results & cards */
.result-strip{display:flex;justify-content:center;margin-top:14px;color:#cfe9f8}

/* Cards layout: updated look */
.cards{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
  gap:16px;
  margin-top:20px;
}
.card{
  background: linear-gradient(180deg, var(--card-bg), var(--card-contrast));
  color:var(--text);
  padding:16px;border-radius:12px;
  box-shadow: 0 12px 36px rgba(2,8,23,0.5);
  cursor:pointer;transition:transform .14s,box-shadow .14s,border-color .14s;
  border:1px solid rgba(255,255,255,0.02);
}
.card:hover{transform:translateY(-8px);box-shadow:0 22px 60px rgba(2,8,23,0.6);border-color:rgba(0,147,208,0.12)}
.card .row{display:flex;justify-content:space-between;align-items:center}
.card .id{font-size:18px;font-weight:800;color:var(--accent)}
.card .meta{font-size:13px;color:var(--muted);margin-top:8px}
.card .preview{margin-top:10px;font-size:13px;color:var(--muted)}
.card .total{margin-top:12px;font-weight:800;color:var(--green);text-align:right}

/* improved badge */
.badge{padding:6px 10px;border-radius:999px;font-weight:700;font-size:12px;color:#102029;background:transparent}
.badge.open{background:linear-gradient(90deg,#fbbf24,#f97316); color:#082022;}
.badge.closed{background:linear-gradient(90deg,#10b981,#059669); color:#042018;}
.badge.canceled{background:linear-gradient(90deg,#ef4444,#f97373); color:#fff;}

/* empty state */
.empty{padding:18px;border-radius:10px;background:rgba(255,255,255,0.02);color:#cfe9f8;text-align:center}

/* modal styling */
.modal-overlay{position:fixed;inset:0;background:rgba(2,6,23,0.6);display:none;align-items:center;justify-content:center;padding:20px;z-index:999}
.modal{background:linear-gradient(180deg,#ffffff,#f7f9fb);color:#04202a;border-radius:12px;max-width:900px;width:100%;max-height:90vh;overflow:auto;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.6)}
.modal .modal-header{display:flex;justify-content:space-between;align-items:center;gap:12px}
.modal .meta{color:#475569}
.items table{width:100%;border-collapse:collapse;margin-top:12px}
.items th{text-align:left;padding:8px;color:#042; font-weight:700}
.items td{padding:8px;border-top:1px solid #eee;color:#042}

/* modal action buttons */
.modal .actions { display:flex; gap:8px; margin-top:10px; }
.modal .actions button { padding:8px 12px; border-radius:8px; border:none; cursor:pointer; font-weight:700; }
.modal .actions .print { background: linear-gradient(90deg, var(--accent), #0ea5ff); color:#fff; }
.modal .actions .report { background: #0ea5a4; color:#012; }

/* responsive */
@media(max-width:980px){
  .sidebar{display:none}
  .container{margin-left:0;padding:16px}
  .filter-panel{flex-direction:column;align-items:stretch}
  .filter-panel .field{width:100%}
  .filter-actions{justify-content:stretch}
  .field-comanda{width:100%}
}
</style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
    <a href="comanda.php"><span class="material-icons icon">receipt</span><span class="text">Criar Comanda</span></a>
    <a href="ver_comandas.php"><span class="material-icons icon">visibility</span><span class="text">Ver Comandas</span></a>
    <!-- Adição solicitada: link do carrinho na sidebar -->
    <a href="carrinho.php"><span class="material-icons icon">shopping_cart</span><span class="text">Carrinho</span></a>
</nav>

<!-- Main content -->
<div class="container">
  <div class="main">
    <div class="header">
    </div>

    <form id="filterForm" method="get" class="filter-panel" onsubmit="">
      <div class="field field-comanda">
        <label for="id_search">Comanda #</label>
        <div class="input-wrap">
          <input type="number" id="id_search" name="id_search" placeholder="Buscar por número" min="1" value="">
          <button type="button" class="btn primary" id="btnSearch" title="Buscar comanda">🔎 Buscar</button>
        </div>
      </div>

      <div class="field">
        <label for="start">Data inicial</label>
        <input type="date" id="start" name="start" value="<?= htmlspecialchars($start) ?>">
      </div>

      <div class="field">
        <label for="end">Data final</label>
        <input type="date" id="end" name="end" value="<?= htmlspecialchars($end) ?>">
      </div>

      <div class="field">
        <label for="func">Funcionário</label>
        <input type="text" id="func" name="func" placeholder="Nome ou parte do nome" value="<?= htmlspecialchars($filterFunc) ?>">
      </div>

      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="">— Todos —</option>
          <option value="ABERTA" <?= $filterStatus === 'ABERTA' ? 'selected' : '' ?>>ABERTA</option>
          <option value="FECHADA" <?= $filterStatus === 'FECHADA' ? 'selected' : '' ?>>FECHADA</option>
          <option value="CANCELADA" <?= $filterStatus === 'CANCELADA' ? 'selected' : '' ?>>CANCELADA</option>
        </select>
      </div>

      <div class="filter-actions">
        <button class="btn primary" type="submit">Mostrar</button>
        <button class="btn ghost" type="button" onclick="clearAll()">Limpar</button>
        <button class="btn report" type="button" onclick="openReport()" title="Gerar relatório com os filtros atuais">Gerar relatório</button>
      </div>
    </form>

    <div class="result-strip" id="resultInfo">
      <?php if ($start || $end || $filterFunc || $filterStatus): ?>
        Mostrando: <strong style="margin-left:8px;color:#fff"><?= htmlspecialchars($start ?: '∞') ?></strong>
        <span style="margin:0 6px;color:#9fb3c8">→</span>
        <strong style="color:#fff"><?= htmlspecialchars($end ?: '∞') ?></strong>
        <?php if ($filterFunc): ?>
          <span style="margin-left:10px;color:#9fb3c8">•</span>
          <span style="margin-left:8px;color:#fff">Funcionário: <?= htmlspecialchars($filterFunc) ?></span>
        <?php endif; ?>
        <?php if ($filterStatus): ?>
          <span style="margin-left:10px;color:#9fb3c8">•</span>
          <span style="margin-left:8px;color:#fff">Status: <?= htmlspecialchars($filterStatus) ?></span>
        <?php endif; ?>
        <span style="margin-left:12px;color:#9fb3c8">•</span>
        <span style="margin-left:8px;color:#fff">Resultados: <strong><?= count($comandas) ?></strong></span>
      <?php else: ?>
        <!-- intentionally left blank as requested -->
      <?php endif; ?>
    </div>

    <div class="cards" id="cards">
      <?php if (!($start || $end || $filterFunc || $filterStatus)): ?>
        <!-- removed the 'Nenhum filtro selecionado...' empty box as requested -->
      <?php elseif (count($comandas) === 0): ?>
        <div class="empty">Nenhuma comanda encontrada para esse filtro.</div>
      <?php else: ?>
        <?php foreach ($comandas as $c):
          $hora = isset($c['venda_data']) ? substr($c['venda_data'], 11, 8) : '';
          $status = strtoupper(trim($c['status'] ?? ''));
          $badgeClass = 'open';
          if ($status === 'FECHADA') $badgeClass = 'closed';
          if ($status === 'CANCELADA') $badgeClass = 'canceled';
          $preview = $c['produtos_preview'] ?? '';
          $previewList = $preview !== '' ? explode('||', $preview) : [];
        ?>
          <div class="card" tabindex="0" data-id="<?= htmlspecialchars($c['ID_vendas']) ?>" onclick="openDetails(<?= htmlspecialchars($c['ID_vendas']) ?>)">
            <div class="row">
              <div class="id">#<?= htmlspecialchars($c['ID_vendas']) ?></div>
              <div class="badge <?= $badgeClass ?>"><?= htmlspecialchars($c['status'] ?: '-') ?></div>
            </div>
            <div class="meta">Feita por: <strong><?= htmlspecialchars($c['Nome_func'] ?: '—') ?></strong></div>
            <div class="meta">Hora: <?= htmlspecialchars($hora ?: '—') ?></div>

            <?php if ($previewList): ?>
              <div class="preview">
                <?php
                  $countPreview = 0;
                  foreach ($previewList as $pr) {
                    if ($countPreview >= 3) break;
                    echo '<div>• ' . htmlspecialchars($pr) . '</div>';
                    $countPreview++;
                  }
                  if (count($previewList) > 3) {
                    echo '<div style="margin-top:6px;color:var(--muted)">… e ' . (count($previewList)-3) . ' outros</div>';
                  }
                ?>
              </div>
            <?php endif; ?>

            <div class="total">Total: R$ <?= number_format($c['total_comanda'] ?? 0, 2, ',', '.') ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div> <!-- .main -->
</div> <!-- .container -->

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" role="dialog" aria-hidden="true">
  <div class="modal" id="modal">
    <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center">
      <div>
        <div style="font-size:18px;font-weight:800" id="modalTitle">Comanda #</div>
        <div class="meta" id="modalMeta">—</div>
      </div>
      <div>
        <button class="close-btn" onclick="closeModal()" aria-label="Fechar" style="border:none;background:transparent;font-size:20px;cursor:pointer">✕</button>
      </div>
    </div>

    <div id="modalBody" style="margin-top:10px">
      <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:center">
        <div><strong>Pagamento:</strong> <span id="modalPayment">—</span></div>
        <div><strong>Status:</strong> <span id="modalStatus">—</span></div>
        <div><strong>Total:</strong> <span id="modalTotal">R$ 0,00</span></div>
      </div>

      <div class="actions" style="margin-top:10px">
        <button class="print" onclick="openPrintTab()">Abrir versão para imprimir</button>
        <button class="report" onclick="openReportSingle()">Gerar relatório</button>
      </div>

      <div class="items" id="itemsSection" style="margin-top:12px">
        <table id="itemsTable" aria-describedby="itemsDesc">
          <thead>
            <tr><th>Produto</th><th>Qtd</th><th>Preço un.</th><th>Total</th></tr>
          </thead>
          <tbody id="itemsBody">
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
/* Sidebar toggle */
const sidebar = document.getElementById('sidebar');
function toggleSidebar(){
    sidebar.classList.toggle('collapsed');

    const containerEl = document.querySelector('.container');
    const rootStyles = getComputedStyle(document.documentElement);
    const collapsed = rootStyles.getPropertyValue('--sidebar-collapsed-width').trim() || '60px';
    const normal = rootStyles.getPropertyValue('--sidebar-width').trim() || '240px';
    containerEl.style.marginLeft = sidebar.classList.contains('collapsed') ? collapsed : normal;
}

/* Filter helpers */
function clearAll(){
  document.getElementById('start').value = '';
  document.getElementById('end').value = '';
  document.getElementById('func').value = '';
  document.getElementById('status').value = '';
  document.getElementById('id_search').value = '';
  const url = new URL(window.location);
  url.searchParams.delete('start');
  url.searchParams.delete('end');
  url.searchParams.delete('func');
  url.searchParams.delete('status');
  window.location = url.toString();
}

/* Modal + fetch details */
const phpSelf = "<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>";
let currentModalId = null;

function openDetails(id){
  if (!id) return;
  currentModalId = id;
  const overlay = document.getElementById('modalOverlay');
  const title = document.getElementById('modalTitle');
  const meta = document.getElementById('modalMeta');
  const payment = document.getElementById('modalPayment');
  const status = document.getElementById('modalStatus');
  const total = document.getElementById('modalTotal');
  const itemsBody = document.getElementById('itemsBody');

  title.textContent = 'Comanda #' + id;
  meta.textContent = 'Carregando...';
  payment.textContent = '—';
  status.textContent = '—';
  total.textContent = 'R$ 0,00';
  itemsBody.innerHTML = '';

  overlay.style.display = 'flex';
  overlay.setAttribute('aria-hidden', 'false');

  fetch(phpSelf + '?action=details&id=' + encodeURIComponent(id))
    .then(r => r.json())
    .then(data => {
      if (data.error) { meta.textContent = data.error; return; }
      meta.textContent = (data.funcionario ? 'Feita por: ' + data.funcionario + ' — ' : '') + (data.hora ? 'Hora: ' + data.hora : '');
      payment.textContent = data.forma_pagamento || '—';
      status.textContent = data.status || '—';
      const formattedTotal = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(data.total_comanda || 0));
      total.textContent = formattedTotal;

      itemsBody.innerHTML = '';
      if (data.items && Array.isArray(data.items) && data.items.length) {
        data.items.forEach(it => {
          const tr = document.createElement('tr');
          const nome = document.createElement('td'); nome.textContent = it.Nome_prod ?? '-';
          const qtd = document.createElement('td'); qtd.textContent = it.Quantidade ?? '-';
          const pu = document.createElement('td'); pu.textContent = (it.valor_unitario !== null && it.valor_unitario !== undefined) ? new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(parseFloat(it.valor_unitario)) : '-';
          const tot = document.createElement('td'); tot.textContent = (it.valor_total !== null && it.valor_total !== undefined) ? new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(parseFloat(it.valor_total)) : '-';
          tr.appendChild(nome); tr.appendChild(qtd); tr.appendChild(pu); tr.appendChild(tot);
          itemsBody.appendChild(tr);
        });
      } else {
        const tr = document.createElement('tr'); const td = document.createElement('td'); td.setAttribute('colspan','4'); td.textContent = 'Nenhum item registrado nesta comanda.'; tr.appendChild(td); itemsBody.appendChild(tr);
      }
    })
    .catch(err => { meta.textContent = 'Erro ao carregar dados.'; console.error(err); });
}

function closeModal(){ const overlay = document.getElementById('modalOverlay'); overlay.style.display = 'none'; overlay.setAttribute('aria-hidden', 'true'); }
document.getElementById('modalOverlay').addEventListener('click', function(e){ if (e.target === this) closeModal(); });
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });

function openPrintTab(){ if (!currentModalId) return; window.open(phpSelf + '?action=print&id=' + encodeURIComponent(currentModalId), '_blank'); }
function openReportSingle(){ if (!currentModalId) return; window.open(phpSelf + '?action=report&id=' + encodeURIComponent(currentModalId), '_blank'); }
function openReport(){ const url = new URL(window.location); url.searchParams.set('action', 'report'); window.open(url.toString(), '_blank'); }

/* --- Search by comanda number (new) --- */
const btnSearch = document.getElementById('btnSearch');
const idSearchInput = document.getElementById('id_search');
const filterForm = document.getElementById('filterForm');

btnSearch.addEventListener('click', function(){
  const val = idSearchInput.value.trim();
  if (!val) {
    idSearchInput.focus();
    idSearchInput.style.boxShadow = '0 0 0 3px rgba(255,200,100,0.12)';
    setTimeout(()=> idSearchInput.style.boxShadow = '', 900);
    return;
  }
  openDetails(val);
});

/* allow Enter to trigger search if id_search has value, otherwise submit filter form */
filterForm.addEventListener('submit', function(e){
  const val = idSearchInput.value.trim();
  if (val) {
    e.preventDefault();
    openDetails(val);
  }
});
</script>
</body>
</html>
