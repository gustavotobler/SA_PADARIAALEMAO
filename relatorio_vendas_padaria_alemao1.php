<?php
session_start();
$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

/*
 * Endpoint: detalhes JSON (action=details&id=...) - usado pelo modal
 * Endpoint: impressão (action=print&id=...) - gera página imprimível
 * Endpoint: relatório (action=report&id=... OR action=report with filters) - gera página para relatório/print/PDF
 */
if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sqlV = "SELECT v.ID_vendas, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
             FROM vendas v
             LEFT JOIN funcionario f ON v.ID_func = f.ID_func
             WHERE v.ID_vendas = :id LIMIT 1";
    $stmtV = $conn->prepare($sqlV);
    $stmtV->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtV->execute();
    $venda = $stmtV->fetch();

    if (!$venda) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Comanda não encontrada']);
        exit;
    }

    $sqlItems = "SELECT p.Nome_prod, iv.Quantidade, iv.valor_unitario, iv.valor_total
                 FROM itens_vendas iv
                 LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
                 WHERE iv.ID_vendas = :id";
    $stmtI = $conn->prepare($sqlItems);
    $stmtI->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtI->execute();
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resp);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'print' && isset($_GET['id'])) {
    // pagina imprimível simples (abre em nova aba)
    $id = intval($_GET['id']);
    $sqlV = "SELECT v.ID_vendas, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
             FROM vendas v
             LEFT JOIN funcionario f ON v.ID_func = f.ID_func
             WHERE v.ID_vendas = :id LIMIT 1";
    $stmtV = $conn->prepare($sqlV);
    $stmtV->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtV->execute();
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
    $stmtI->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtI->execute();
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

// --- Novo endpoint: report ---
if (isset($_GET['action']) && $_GET['action'] === 'report') {
    // Se for report de 1 comanda (id) -> página detalhada pronta para print/PDF
    // Senão, gera relatório para o filtro atual (start/end/func/status)
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($id) {
        // detalhe único em formato de relatório (melhor layout que "print")
        $sqlV = "SELECT v.ID_vendas, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
                 FROM vendas v
                 LEFT JOIN funcionario f ON v.ID_func = f.ID_func
                 WHERE v.ID_vendas = :id LIMIT 1";
        $stmtV = $conn->prepare($sqlV);
        $stmtV->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtV->execute();
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
        $stmtI->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtI->execute();
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
        // relatório geral usando os filtros atuais (start,end,func,status)
        $start = trim($_GET['start'] ?? '');
        $end = trim($_GET['end'] ?? '');
        $filterFunc = trim($_GET['func'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');

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
        if ($filterFunc !== '') {
            $where[] = "f.Nome_func LIKE :func";
            $params[':func'] = '%' . $filterFunc . '%';
        }
        if ($filterStatus !== '') {
            $where[] = "v.status = :status";
            $params[':status'] = $filterStatus;
        }
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
        $comandas = $stmt->fetchAll();

        // Calcular total geral
        $totalGeral = 0.0;
        foreach ($comandas as $c) $totalGeral += floatval($c['total_comanda'] ?? 0);

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
            <?php if ($comandas): foreach ($comandas as $c): ?>
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

// Página principal: filtros e listagem (preview de itens via GROUP_CONCAT)
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
    if ($filterFunc !== '') {
        // busca por nome do funcionário (LIKE)
        $where[] = "f.Nome_func LIKE :func";
        $params[':func'] = '%' . $filterFunc . '%';
    }
    if ($filterStatus !== '') {
        $where[] = "v.status = :status";
        $params[':status'] = $filterStatus;
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Produtos preview como concat separado por '||' para fácil split no PHP
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

?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Comandas — Relatório</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
:root {
    --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
    --primary-text: #f8f9fa;
    --hover-bg: #1e3a5f;
    --main-bg: rgb(59, 75, 93);
    --card-bg: #ffffff;
    --accent: #1b263b;
    --highlight: #0077b6;
    --success: #16a34a;
    --danger: #ef4444;
}

/* Reset */
*{box-sizing:border-box;font-family:"Segoe UI",Tahoma,Arial;margin:0;padding:0}
body{min-height:100vh;background-color:var(--main-bg);color:var(--primary-text);display:flex}

/* Sidebar */
.sidebar {
    width:220px;
    padding:20px;
    background:var(--sidebar-bg);
    height:100vh;
    position:fixed;
    box-shadow:2px 0 10px rgba(0,0,0,.4);
}
.sidebar .brand{font-weight:700;font-size:18px;margin-bottom:12px;color:#fff}
.sidebar a{display:flex;align-items:center;color:var(--primary-text);text-decoration:none;padding:12px;border-radius:6px;gap:6px}
.sidebar a:hover{background:var(--hover-bg);border-left:4px solid var(--highlight)}

/* Main */
.main{margin-left:220px;padding:28px;flex:1}
.header{display:flex;justify-content:space-between;align-items:end;gap:12px;flex-wrap:wrap}
.title{font-size:20px;font-weight:700;margin-bottom:6px}
.controls{display:flex;gap:12px;align-items:center}
.controls .field{display:flex;flex-direction:column}
.controls label{font-size:13px;color:#fff;margin-bottom:6px}
input[type="date"], input[type="text"], select{
    padding:10px;border-radius:10px;border:1px solid #ccc;
    background:#f0f0f0;color:#000;min-width:160px
}
button{padding:10px 12px;border-radius:10px;border:none;background:var(--highlight);color:#fff;cursor:pointer;font-weight:600}
button.ghost{background:transparent;border:1px solid #ccc;color:#fff}
.info{margin-top:14px;color:#ddd}

/* Cards */
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:18px}
.card{background:var(--card-bg);color:#072433;padding:14px;border-radius:12px;
      box-shadow:0 6px 16px rgba(0,0,0,.25);cursor:pointer;transition:transform .14s,box-shadow .14s;
      display:flex;flex-direction:column;gap:8px}
.card:hover{transform:translateY(-6px);box-shadow:0 14px 30px rgba(0,0,0,.3)}
.card .row{display:flex;justify-content:space-between;align-items:center}
.card .id{font-size:20px;font-weight:800;color:var(--highlight)}
.card .meta{font-size:13px;color:#555}
.card .hora{font-size:12px;color:#333}
.badge{padding:6px 8px;border-radius:999px;font-weight:700;font-size:12px;color:#fff}
.badge.open{background:#f59e0b}
.badge.closed{background:var(--success)}
.badge.canceled{background:var(--danger)}
.total{font-weight:800;margin-top:6px;font-size:16px;color:var(--accent)}
.preview-list{margin-top:8px;font-size:13px;color:#163046}

/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(2,6,23,0.6);display:none;align-items:center;justify-content:center;padding:20px;z-index:999}
.modal{background:#fff;color:#04202a;border-radius:12px;max-width:900px;width:100%;max-height:90vh;overflow:auto;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.6)}
.modal .modal-header{display:flex;justify-content:space-between;align-items:center;gap:8px}
.modal .modal-title{font-size:18px;font-weight:800;color:var(--highlight)}
.modal .close-btn{background:transparent;border:none;font-size:22px;cursor:pointer}
.modal .meta{color:#475569;margin-top:6px}
.items{margin-top:12px;border-top:1px solid #ddd;padding-top:12px}
.items table{width:100%;border-collapse:collapse}
.items th{text-align:left;padding:8px;color:#023; font-weight:700}
.items td{padding:8px;border-top:1px solid #eee;color:#012}

/* Empty */
.empty{padding:18px;border-radius:10px;background:rgba(255,255,255,0.05);color:#fff;margin-top:12px}

/* Responsivo */
@media(max-width:760px){
  .sidebar{display:none}
  .main{margin-left:0;padding:16px}
  .cards{grid-template-columns:repeat(auto-fill,minmax(180px,1fr))}
}

</style>
</head>
<body>
<nav class="sidebar">
  <div class="brand">Painel • Padaria</div>
  <a href="inicial1.php"><span class="material-icons" style="vertical-align:middle">arrow_back</span> Voltar</a>
  <a href="#" onclick="location.reload()">Atualizar</a>
</nav>

<main class="main">
  <div class="header">
    <div>
      <div class="title">Comandas — filtro</div>
      <div class="info">Escolha intervalo de datas e/ou filtre por funcionário e status. Clique em uma comanda para ver detalhes e gerar relatório.</div>
    </div>

    <form id="filterForm" method="get" class="controls" onsubmit="">
      <div class="field">
        <label for="start">Data inicial</label>
        <input type="date" id="start" name="start" value="<?= htmlspecialchars($start) ?>">
      </div>
      <div class="field">
        <label for="end">Data final</label>
        <input type="date" id="end" name="end" value="<?= htmlspecialchars($end) ?>">
      </div>
      <div class="field">
        <label for="func">Funcionário (nome)</label>
        <input type="text" id="func" name="func"  value="<?= htmlspecialchars($filterFunc) ?>">
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
      <div style="display:flex;gap:8px;align-items:center">
        <button type="submit">Mostrar</button>
        <button type="button" class="ghost" onclick="clearAll()">Limpar</button>
        <button type="button" onclick="openReport()" title="Gerar relatório com os filtros atuais">Gerar relatório</button>
      </div>
    </form>
  </div>

  <div class="info" id="resultInfo">
    <?php if ($start || $end || $filterFunc || $filterStatus): ?>
      Mostrando filtros:
      <strong><?= htmlspecialchars($start ?: '∞') ?></strong>
      a <strong><?= htmlspecialchars($end ?: '∞') ?></strong>
      <?php if ($filterFunc): ?> • Funcionário: <strong><?= htmlspecialchars($filterFunc) ?></strong><?php endif; ?>
      <?php if ($filterStatus): ?> • Status: <strong><?= htmlspecialchars($filterStatus) ?></strong><?php endif; ?>
      • Resultados: <strong><?= count($comandas) ?></strong>.
    <?php else: ?>
      Selecione filtros para listar as comandas.
    <?php endif; ?>
  </div>

  <div class="cards" id="cards">
    <?php if (!($start || $end || $filterFunc || $filterStatus)): ?>
      <div class="empty">Nenhum filtro selecionado — escolha intervalo ou outros filtros e clique em "Mostrar".</div>
    <?php elseif (count($comandas) === 0): ?>
      <div class="empty">Nenhuma comanda encontrada para esse filtro.</div>
    <?php else: ?>
      <?php foreach ($comandas as $c):
          $hora = isset($c['venda_data']) ? substr($c['venda_data'], 11, 8) : '';
          $status = strtolower($c['status'] ?? '');
          $badgeClass = 'open';
          if ($status === 'fechada' || $status === 'FECHADA') $badgeClass = 'closed';
          if ($status === 'cancelada' || $status === 'CANCELADA') $badgeClass = 'canceled';
          $preview = $c['produtos_preview'] ?? '';
          $previewList = $preview !== '' ? explode('||', $preview) : [];
      ?>
        <div class="card" tabindex="0" data-id="<?= htmlspecialchars($c['ID_vendas']) ?>" onclick="openDetails(<?= htmlspecialchars($c['ID_vendas']) ?>)">
          <div class="row">
            <div class="id">#<?= htmlspecialchars($c['ID_vendas']) ?></div>
            <div class="badge <?= $badgeClass ?>"><?= htmlspecialchars($c['status'] ?: '-') ?></div>
          </div>
          <div class="meta">Feita por: <strong><?= htmlspecialchars($c['Nome_func'] ?: '—') ?></strong></div>
          <div class="hora">Hora: <?= htmlspecialchars($hora ?: '—') ?></div>

          <?php if ($previewList): ?>
            <div class="preview-list">
              <?php
                // mostrar até 3 itens como preview
                $countPreview = 0;
                foreach ($previewList as $pr) {
                  if ($countPreview >= 3) break;
                  echo '<div class="preview-item">• ' . htmlspecialchars($pr) . '</div>';
                  $countPreview++;
                }
                if (count($previewList) > 3) {
                  echo '<div class="preview-item">… e ' . (count($previewList)-3) . ' outros</div>';
                }
              ?>
            </div>
          <?php endif; ?>

          <div class="total">Total: R$ <?= number_format($c['total_comanda'] ?? 0, 2, ',', '.') ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" role="dialog" aria-hidden="true">
  <div class="modal" id="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title" id="modalTitle">Comanda #</div>
        <div class="meta" id="modalMeta">—</div>
      </div>
      <div>
        <button class="close-btn" onclick="closeModal()" aria-label="Fechar">✕</button>
      </div>
    </div>

    <div id="modalBody" style="margin-top:10px">
      <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:center">
        <div><strong>Pagamento:</strong> <span id="modalPayment">—</span></div>
        <div><strong>Status:</strong> <span id="modalStatus">—</span></div>
        <div><strong>Total:</strong> <span id="modalTotal">R$ 0,00</span></div>
      </div>

      <div style="margin-top:10px;display:flex;gap:8px">
        <!-- botão de impressão rápido removido conforme solicitado -->
        <button onclick="openPrintTab()" style="background:#0077b6;border:none;padding:8px 10px;border-radius:8px;color:#fff;cursor:pointer">Abrir versão para imprimir</button>
        <button onclick="openReportSingle()" style="background:#0ea5a4;border:none;padding:8px 10px;border-radius:8px;color:#012;cursor:pointer">Gerar relatório</button>
      </div>

      <div class="items" id="itemsSection">
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
function clearAll(){
  document.getElementById('start').value = '';
  document.getElementById('end').value = '';
  document.getElementById('func').value = '';
  document.getElementById('status').value = '';
  const url = new URL(window.location);
  url.searchParams.delete('start');
  url.searchParams.delete('end');
  url.searchParams.delete('func');
  url.searchParams.delete('status');
  window.location = url.toString();
}

const phpSelf = "<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>";
let currentModalId = null;

function openDetails(id){
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
      if (data.error) {
        meta.textContent = data.error;
        return;
      }
      meta.textContent = (data.funcionario ? 'Feita por: ' + data.funcionario + ' — ' : '') + (data.hora ? 'Hora: ' + data.hora : '');
      payment.textContent = data.forma_pagamento || '—';
      status.textContent = data.status || '—';
      const formattedTotal = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(data.total_comanda || 0));
      total.textContent = formattedTotal;

      itemsBody.innerHTML = '';
      if (data.items && Array.isArray(data.items) && data.items.length) {
        data.items.forEach(it => {
          const tr = document.createElement('tr');
          const nome = document.createElement('td');
          nome.textContent = it.Nome_prod ?? '-';
          const qtd = document.createElement('td');
          qtd.textContent = it.Quantidade ?? '-';
          const pu = document.createElement('td');
          pu.textContent = (it.valor_unitario !== null && it.valor_unitario !== undefined) ? new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(parseFloat(it.valor_unitario)) : '-';
          const tot = document.createElement('td');
          tot.textContent = (it.valor_total !== null && it.valor_total !== undefined) ? new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(parseFloat(it.valor_total)) : '-';
          tr.appendChild(nome); tr.appendChild(qtd); tr.appendChild(pu); tr.appendChild(tot);
          itemsBody.appendChild(tr);
        });
      } else {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.setAttribute('colspan','4');
        td.textContent = 'Nenhum item registrado nesta comanda.';
        tr.appendChild(td);
        itemsBody.appendChild(tr);
      }
    })
    .catch(err => {
      meta.textContent = 'Erro ao carregar dados.';
      console.error(err);
    });
}

function closeModal(){
  const overlay = document.getElementById('modalOverlay');
  overlay.style.display = 'none';
  overlay.setAttribute('aria-hidden', 'true');
}

document.getElementById('modalOverlay').addEventListener('click', function(e){
  if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') closeModal();
});

// Abre a versão imprimível (endpoint action=print) em nova aba
function openPrintTab(){
  if (!currentModalId) return;
  window.open(phpSelf + '?action=print&id=' + encodeURIComponent(currentModalId), '_blank');
}

// Novo: abre a versão de relatório (action=report)... para a comanda atual
function openReportSingle(){
  if (!currentModalId) return;
  window.open(phpSelf + '?action=report&id=' + encodeURIComponent(currentModalId), '_blank');
}

// Novo: gerar relatório geral com os filtros atuais
function openReport(){
  const url = new URL(window.location);
  // manter filtros existentes, apenas assegurar action=report
  url.searchParams.set('action', 'report');
  window.open(url.toString(), '_blank');
}
</script>
</body>
</html>
