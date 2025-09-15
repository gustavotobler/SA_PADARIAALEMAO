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

/**
 * Função utilitária para tentar criar um objeto DateTime a partir de várias
 * formatações comuns (d/m/Y, Y-m-d, timestamp) e retornar null se inválido.
 */
function parseDateSafe(?string $val): ?DateTime {
    $val = trim((string)$val);
    if ($val === '' || $val === '0000-00-00' || $val === '0000-00-00 00:00:00') return null;

    // Tenta d/m/Y (formato brasileiro)
    $d = DateTime::createFromFormat('d/m/Y', $val);
    $err = DateTime::getLastErrors();
    if ($d && empty($err['warning_count']) && empty($err['error_count'])) return $d;

    // Tenta d/m/Y H:i:s
    $d = DateTime::createFromFormat('d/m/Y H:i:s', $val);
    $err = DateTime::getLastErrors();
    if ($d && empty($err['warning_count']) && empty($err['error_count'])) return $d;

    // Tenta Y-m-d (ISO)
    $d = DateTime::createFromFormat('Y-m-d', $val);
    $err = DateTime::getLastErrors();
    if ($d && empty($err['warning_count']) && empty($err['error_count'])) return $d;

    // Tenta Y-m-d H:i:s
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $val);
    $err = DateTime::getLastErrors();
    if ($d && empty($err['warning_count']) && empty($err['error_count'])) return $d;

    // Tenta parse geral (strtotime/new DateTime)
    try {
        $d = new DateTime($val);
        return $d;
    } catch (Exception $e) {
        return null;
    }
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- TRATAMENTO DE AÇÕES (CRIAR / ATUALIZAR) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['form_action'])) {
        $action = $_POST['form_action'];
        try {
            if ($action === 'create') {
                $nome = trim($_POST['nome'] ?? '');
                $id_forn = !empty($_POST['id_forn']) ? intval($_POST['id_forn']) : null;
                $id_cat = !empty($_POST['id_cat']) ? intval($_POST['id_cat']) : null;
                $preco = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
                $preco = ($preco === '') ? 0 : (float)$preco;
                $unidade = trim($_POST['unidade'] ?? '');
                $qnt = str_replace(',', '.', trim($_POST['qnt'] ?? '0'));
                $qnt = ($qnt === '') ? 0 : (float)$qnt;
                $validade = trim($_POST['validade'] ?? '');
                $validadeDb = null;
                if ($validade !== '') {
                    $d = parseDateSafe($validade);
                    if ($d) $validadeDb = $d->format('Y-m-d');
                }

                $sql = "INSERT INTO produtos (Nome_prod, ID_forn, id_categorias, Preco_unitario, Unid_medida, Validade, Qntd_produto)
                        VALUES (:nome, :id_forn, :id_cat, :preco, :unidade, :validade, :qnt)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nome'=>$nome,
                    ':id_forn'=>$id_forn,
                    ':id_cat'=>$id_cat,
                    ':preco'=>$preco,
                    ':unidade'=>$unidade,
                    ':validade'=>$validadeDb,
                    ':qnt'=>$qnt
                ]);
                $_SESSION['flash'] = 'Produto cadastrado com sucesso.';
                header('Location: '.$_SERVER['PHP_SELF']); exit;
            }

            if ($action === 'update' && !empty($_POST['ID_produto'])) {
                $id = intval($_POST['ID_produto']);
                $nome = trim($_POST['nome'] ?? '');
                $id_forn = !empty($_POST['id_forn']) ? intval($_POST['id_forn']) : null;
                $id_cat = !empty($_POST['id_cat']) ? intval($_POST['id_cat']) : null;
                $preco = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
                $preco = ($preco === '') ? 0 : (float)$preco;
                $unidade = trim($_POST['unidade'] ?? '');
                $qnt = str_replace(',', '.', trim($_POST['qnt'] ?? '0'));
                $qnt = ($qnt === '') ? 0 : (float)$qnt;
                $validade = trim($_POST['validade'] ?? '');
                $validadeDb = null;
                if ($validade !== '') {
                    $d = parseDateSafe($validade);
                    if ($d) $validadeDb = $d->format('Y-m-d');
                }

                $sql = "UPDATE produtos SET Nome_prod=:nome, ID_forn=:id_forn, id_categorias=:id_cat,
                        Preco_unitario=:preco, Unid_medida=:unidade, Validade=:validade, Qntd_produto=:qnt
                        WHERE ID_produto = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nome'=>$nome, ':id_forn'=>$id_forn, ':id_cat'=>$id_cat,
                    ':preco'=>$preco, ':unidade'=>$unidade, ':validade'=>$validadeDb, ':qnt'=>$qnt, ':id'=>$id
                ]);
                $_SESSION['flash'] = 'Produto atualizado.';
                header('Location: '.$_SERVER['PHP_SELF']); exit;
            }

        } catch (Exception $e) {
            $_SESSION['flash_err'] = 'Erro: ' . $e->getMessage();
            header('Location: '.$_SERVER['PHP_SELF']); exit;
        }
    }

    // --- Buscar fornecedores e categorias (para selects do formulário) ---
    $fornecedores = $conn->query("SELECT ID_forn, Nome_forn FROM fornecedores ORDER BY Nome_forn")->fetchAll(PDO::FETCH_ASSOC);
    $categorias = $conn->query("SELECT id_categorias, nome_categoria FROM categorias ORDER BY nome_categoria")->fetchAll(PDO::FETCH_ASSOC);

    // ---------- Consulta produtos (usando prepare/execute com verificação) ----------
    $sql = "
    SELECT 
        p.ID_produto,
        p.Nome_prod,
        f.Nome_forn,
        p.Unid_medida,
        p.Qntd_produto,
        p.Preco_unitario,
        (p.Qntd_produto * p.Preco_unitario) AS valor_total,
        p.Validade,
        c.id_categorias,
        c.nome_categoria
    FROM produtos p
    LEFT JOIN fornecedores f ON p.ID_forn = f.ID_forn
    LEFT JOIN categorias c ON p.id_categorias = c.id_categorias
    ORDER BY p.Nome_prod ASC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $err = $conn->errorInfo();
        throw new Exception('Erro preparando SQL produtos: ' . ($err[2] ?? 'sem mensagem'));
    }
    if (!$stmt->execute()) {
        $err = $stmt->errorInfo();
        throw new Exception('Erro executando SQL produtos: ' . ($err[2] ?? 'sem mensagem'));
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Estatísticas básicas
    $totalProducts = count($rows);
    $totalQuantity = 0.0;
    $totalValue = 0.0;
    $expiredCount = 0;
    $soonCount = 0;
    $okCount = 0;
    $noDateCount = 0;
    $lowStock = [];
    $soonItems = [];
    $expiredItems = [];
    $lowStockThreshold = 5; // ajuste se quiser

    $today = (new DateTime())->setTime(0,0,0);
    $in30 = (clone $today)->modify('+30 days');

    foreach ($rows as $r) {
        $q = floatval($r['Qntd_produto'] ?? 0);
        $v = floatval($r['valor_total'] ?? 0);
        $totalQuantity += $q;
        $totalValue += $v;

        $val = $r['Validade'] ?? '';
        $d = parseDateSafe($val);
        if ($d === null) {
            $noDateCount++;
        } else {
            // compara apenas a data (sem hora)
            $dOnly = (clone $d)->setTime(0,0,0);
            if ($dOnly < $today) {
                $expiredCount++;
                $expiredItems[] = [
                    'id' => $r['ID_produto'],
                    'nome' => $r['Nome_prod'],
                    'q' => $q,
                    'validade' => $dOnly
                ];
            } elseif ($dOnly <= $in30) {
                $soonCount++;
                $soonItems[] = [
                    'id' => $r['ID_produto'],
                    'nome' => $r['Nome_prod'],
                    'q' => $q,
                    'validade' => $dOnly
                ];
            } else {
                $okCount++;
            }
        }

        if ($q <= $lowStockThreshold) {
            $lowStock[] = [
                'id' => $r['ID_produto'],
                'nome' => $r['Nome_prod'],
                'q' => $q
            ];
        }
    }

    usort($soonItems, function($a,$b){ return $a['validade'] <=> $b['validade']; });
    $soonItems = array_slice($soonItems, 0, 12);
    usort($expiredItems, function($a,$b){ return $a['validade'] <=> $b['validade']; });
    $expiredItems = array_slice($expiredItems, 0, 50); // lista maior para revisar
    usort($lowStock, function($a,$b){ return $a['q'] <=> $b['q']; });
    $lowStock = array_slice($lowStock, 0, 12);

    // Prepara dados avançados para o painel (top fornecedores, categorias, produtos)
    $suppliers = []; $categoriesTotals = []; $productByQty = []; $productByValue = []; $zeroStock = [];
    $sumUnitPrice = 0.0; $unitCount = 0;
    foreach ($rows as $r) {
        $q = floatval($r['Qntd_produto'] ?? 0);
        $v = floatval($r['valor_total'] ?? 0);
        $forn = trim($r['Nome_forn'] ?: '—');
        $cat = trim($r['nome_categoria'] ?: '—');
        $name = trim($r['Nome_prod']);

        if (!isset($suppliers[$forn])) $suppliers[$forn] = ['q'=>0,'v'=>0];
        $suppliers[$forn]['q'] += $q;
        $suppliers[$forn]['v'] += $v;

        if (!isset($categoriesTotals[$cat])) $categoriesTotals[$cat] = 0;
        $categoriesTotals[$cat] += $v;

        $productByQty[] = ['name'=>$name,'q'=>$q,'v'=>$v];
        $productByValue[] = ['name'=>$name,'v'=>floatval($v),'q'=>$q];

        if (isset($r['Preco_unitario']) && $r['Preco_unitario'] !== null && $r['Preco_unitario'] !== '') { $sumUnitPrice += floatval($r['Preco_unitario']); $unitCount++; }
        if ($q <= 0) $zeroStock[] = ['id'=>$r['ID_produto'],'nome'=>$name,'q'=>$q];
    }

    usort($productByQty, function($a,$b){ return $b['q'] <=> $a['q']; });
    usort($productByValue, function($a,$b){ return $b['v'] <=> $a['v']; });

    // --- Preparar JSON para gráficos --- 
    arsort($categoriesTotals);
    $catLabels = array_slice(array_keys($categoriesTotals), 0, 10);
    $catValues = array_slice(array_values($categoriesTotals), 0, 10);
    $catLabelsJSON = json_encode(array_values($catLabels));
    $catValuesJSON = json_encode(array_values($catValues));

    $prodByValTop = array_slice($productByValue, 0, 12);
    $prodByQtyTop = array_slice($productByQty, 0, 12);
    $prodByValJSON = json_encode(array_values(array_map(function($p){ return ['name'=>$p['name'],'v'=>floatval($p['v'])]; }, $prodByValTop)));
    $prodByQtyJSON = json_encode(array_values(array_map(function($p){ return ['name'=>$p['name'],'q'=>floatval($p['q'])]; }, $prodByQtyTop)));

    $supArr = [];
    foreach ($suppliers as $name => $vals) $supArr[] = ['name'=>$name,'q'=>$vals['q'],'v'=>$vals['v']];
    usort($supArr, function($a,$b){ return $b['v'] <=> $a['v']; });
    $supArrTop = array_slice($supArr, 0, 10);
    $suppliersJSON = json_encode($supArrTop);

    $lowStockJSON = json_encode(array_values(array_map(function($p){ return ['name'=>$p['nome'],'q'=>floatval($p['q'])]; }, $lowStock)));
    $zeroStockJSON = json_encode(array_values(array_map(function($p){ return ['name'=>$p['nome'],'q'=>floatval($p['q'])]; }, $zeroStock)));

    $stockStatusJSON = json_encode([
        'expired' => $expiredCount,
        'soon' => $soonCount,
        'ok' => $okCount,
        'nodate' => $noDateCount
    ]);

    // expiredItems / soonItems JSON (formatando datas para exibição)
    $expiredItemsForJson = array_map(function($it){
        return ['id'=>$it['id'],'name'=>$it['nome'],'q'=>floatval($it['q']),'validade'=>$it['validade']->format('Y-m-d'),'validade_br'=>$it['validade']->format('d/m/Y')];
    }, $expiredItems);
    $soonItemsForJson = array_map(function($it){
        return ['id'=>$it['id'],'name'=>$it['nome'],'q'=>floatval($it['q']),'validade'=>$it['validade']->format('Y-m-d'),'validade_br'=>$it['validade']->format('d/m/Y')];
    }, $soonItems);

    $expiredItemsJSON = json_encode($expiredItemsForJson);
    $soonItemsJSON = json_encode($soonItemsForJson);

    // --- Export All Rows JSON (para export CSV do front) ---
    $exportRows = [];
    foreach ($rows as $r) {
        $valBr = '';
        $d = parseDateSafe($r['Validade'] ?? '');
        if ($d) $valBr = $d->format('d/m/Y');
        $exportRows[] = [
            'id' => $r['ID_produto'],
            'nome' => $r['Nome_prod'],
            'fornecedor' => $r['Nome_forn'] ?? '',
            'categoria' => $r['nome_categoria'] ?? '',
            'unidade' => $r['Unid_medida'],
            'quantidade' => floatval($r['Qntd_produto']),
            'preco_unitario' => floatval($r['Preco_unitario']),
            'valor_total' => floatval($r['valor_total']),
            'validade' => $valBr
        ];
    }
    $exportRowsJSON = json_encode($exportRows);

    // --- Saídas (vendas) — últimos períodos (7 e 30 dias)
    $date7 = (new DateTime())->modify('-7 days')->format('Y-m-d H:i:s');
    $date30 = (new DateTime())->modify('-30 days')->format('Y-m-d H:i:s');

    $sqlSaida = "SELECT p.ID_produto, p.Nome_prod, SUM(iv.Quantidade) AS qty, SUM(iv.valor_total) AS total
        FROM itens_vendas iv
        JOIN vendas v ON iv.ID_vendas = v.ID_vendas
        JOIN produtos p ON iv.ID_produto = p.ID_produto
        WHERE v.venda_data >= :date AND v.status = 'FECHADA'
        GROUP BY p.ID_produto
        ORDER BY qty DESC
        LIMIT 12";

    $stmt7 = $conn->prepare($sqlSaida);
    if ($stmt7 === false) throw new Exception('Erro preparando SQL saídas 7d');
    $stmt7->execute([':date' => $date7]);
    $saidas7 = $stmt7->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmt30 = $conn->prepare($sqlSaida);
    if ($stmt30 === false) throw new Exception('Erro preparando SQL saídas 30d');
    $stmt30->execute([':date' => $date30]);
    $saidas30 = $stmt30->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $saidas7JSON = json_encode(array_map(function($r){ return ['id'=>intval($r['ID_produto']),'name'=>$r['Nome_prod'],'qty'=>floatval($r['qty']),'total'=>floatval($r['total'])]; }, $saidas7));
    $saidas30JSON = json_encode(array_map(function($r){ return ['id'=>intval($r['ID_produto']),'name'=>$r['Nome_prod'],'qty'=>floatval($r['qty']),'total'=>floatval($r['total'])]; }, $saidas30));

} catch (PDOException $e) {
    die("Erro na conexão/consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos — Padaria do Alemão (Estoque)</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* (Estilos idênticos aos já preparados no projeto para manter consistência) */
:root{
  --sidebar-bg: linear-gradient(180deg,#0d1b2a,#1b263b);
  --primary-text:#f8f9fa;
  --main-bg: rgb(59,75,93);
  --card-bg:#ffffff;
  --accent:#1b263b;
  --highlight:#0077b6;
}
*{box-sizing:border-box;margin:0;padding:0;font-family:"Segoe UI",Tahoma,Arial,sans-serif}
body{background:var(--main-bg);display:flex}

/* Sidebar */
.sidebar{width:240px;background:var(--sidebar-bg);height:100vh;position:fixed;display:flex;flex-direction:column;padding-top:20px;transition:width .3s;box-shadow:3px 0 10px rgba(0,0,0,.3);} 
.sidebar.collapsed{width:60px}
.sidebar a{display:flex;align-items:center;color:var(--primary-text);text-decoration:none;padding:15px 20px;white-space:nowrap;transition:background .2s,padding .3s}
.sidebar a:hover{background:#1e3a5f;border-left:4px solid var(--highlight);padding-left:16px}
.sidebar .icon{margin-right:8px}
.sidebar.collapsed .text{display:none}
.sidebar.collapsed .icon{margin-right:0;justify-content:center}
.toggle-btn { cursor: pointer; text-align: center; margin-bottom: 20px; font-size: 22px; color: var(--primary-text); }

/* Main */
.main-content{margin-left:240px;padding:20px 30px;width:100%;transition:margin-left .3s}
.main-content.collapsed{margin-left:60px}
h1{color:#fff;text-align:center;margin-bottom:12px}

/* filters */
.filter-row{display:flex;gap:12px;align-items:end;flex-wrap:wrap;margin-bottom:14px}
.field{display:flex;flex-direction:column}
.filter-row label{color:var(--primary-text);font-size:13px;margin-bottom:6px}
input[type="date"], input[type="text"]{padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,0.03);color:var(--primary-text)}

/* buttons -- anchor and button parity */
.btn{display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border-radius:8px;border:none;background:var(--highlight);color:#fff;cursor:pointer;font-weight:700;height:36px;line-height:1;font-size:14px;text-decoration:none}
.btn.ghost{background:transparent;border:1px solid rgba(255,255,255,.06)}

/* action-row remains */
.action-row{display:flex;gap:8px;align-items:center}

/* table */
table{width:100%;border-collapse:collapse;background:var(--card-bg);border-radius:12px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,0.15)}
thead{background:var(--accent);color:var(--primary-text)}
thead th{padding:14px 10px;text-align:left;font-size:.9rem;font-weight:600}
tbody td{padding:12px 10px;border-bottom:1px solid #eee;font-size:.9rem}
tbody tr:nth-child(even){background:#f9fbfd}
tbody tr:hover{background:var(--highlight);color:#fff;transition:.2s}
td[data-label="Quantidade"], td[data-label="Preço Unit."], td[data-label="Valor Total"]{text-align:right}

/* status badges */
.status-badge{display:inline-block;padding:6px 8px;border-radius:999px;font-weight:700;font-size:12px;color:#fff}
.status-expired{background:#d9534f}
.status-soon{background:#f0ad4e;color:#071a1f}
.status-ok{background:#28a745}
.status-nodate{background:#6c757d}

/* row highlights */
.row-expired td{background:rgba(255,107,107,0.06)}
.row-soon td{background:rgba(245,158,11,0.06)}
.row-lowstock td{box-shadow:inset 0 0 0 1px rgba(245,158,11,0.04)}

/* action buttons (links styled as buttons) */
.icon-btn{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;border:none;cursor:pointer;background:#f1f3f5;margin-left:6px;text-decoration:none}
.icon-btn span{font-size:18px;color:#1b263b;display:inline-block;line-height:1}
.icon-btn.edit{background:#0077b6}
.icon-btn.edit span{color:#fff}
.action-col{width:110px;text-align:center}

/* panels */
.info-panel,.charts-panel,.saidas-panel{position:fixed;top:0;right:0;height:100vh;width:420px;max-width:92%;background:linear-gradient(180deg,#021727,#063749);color:var(--primary-text);box-shadow:-20px 0 40px rgba(0,0,0,.6);transform:translateX(110%);transition:transform .25s ease;z-index:1200;display:flex;flex-direction:column;padding:16px}
.info-panel.open,.charts-panel.open,.saidas-panel.open{transform:translateX(0)}
.panel-card{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.04);border-radius:10px;padding:10px;margin-bottom:10px}
.panel-card .muted{color:rgba(159,179,200,1);font-size:13px}
.panel-card .big{font-weight:800;font-size:18px;margin-top:6px;color:#fff}
.compact-list{max-height:140px;overflow:auto;margin-top:8px}
.compact-item{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed rgba(255,255,255,0.02)}
.chart-wrapper{position:relative;min-height:180px}
.chart-canvas{width:100%;height:240px;display:block;margin-bottom:12px;background:rgba(255,255,255,0.02);border-radius:8px;padding:8px}
.small-list{list-style:none;padding:0;margin:6px 0 0 0;max-height:220px;overflow:auto}
.small-list li{padding:8px 6px;border-bottom:1px dashed rgba(255,255,255,0.03);display:flex;justify-content:space-between;align-items:center}
.small-list .meta{font-size:12px;color:rgba(200,220,240,0.8)}
.expired-badge{background:#ff6b6b;color:#fff;padding:4px 8px;border-radius:999px;font-size:12px;font-weight:800;margin-left:8px}
.expired-text{color:#ffb4a6;font-weight:700}

@media(max-width:768px){
  .main-content{margin-left:0;padding:1rem}
  thead{display:none}
  tbody td{display:block;text-align:right;padding:8px;border:none;border-bottom:1px solid #eee}
  tbody td::before{content:attr(data-label);float:left;font-weight:600;color:#555}
  .filter-row{gap:8px}
  .filter-row .search-field{flex:1 1 auto}
}
@media(min-width:1000px){ .charts-panel{width:920px} }
</style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>

    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
    <a href="#" onclick="openInfoPanel();return false;"><span class="material-icons icon">insights</span><span class="text">Informações</span></a>
    <a href="#" onclick="openChartsPanel();return false;"><span class="material-icons icon">bar_chart</span><span class="text">Gráficos</span></a>
    <a href="#" onclick="openSaidasPanel();return false;"><span class="material-icons icon">exit_to_app</span><span class="text">Saídas</span></a>
</nav>

<!-- painel de informações -->
<aside id="infoPanel" class="info-panel" aria-hidden="true">
    <div style="display:flex;justify-content:space-between;align-items:center">
        <h3 style="margin:0">Informações do Estoque</h3>
        <div style="display:flex;gap:8px;align-items:center">
            <button id="exportAllBtn" class="btn btn-ghost" title="Exportar todos (CSV)">Exportar CSV</button>
            <button class="close-btn" onclick="closeInfoPanel()" aria-label="Fechar" style="background:transparent;border:none;color:var(--primary-text);font-size:20px;cursor:pointer">✕</button>
        </div>
    </div>

    <div style="margin-top:12px;overflow:auto;padding-right:6px;flex:1">
        <!-- Resumo -->
        <div class="panel-card">
            <div class="muted">Resumo</div>
            <div class="big">Produtos: <?= number_format($totalProducts) ?></div>
            <div style="margin-top:6px" class="muted">Quantidade total: <strong style="color:#fff"><?= number_format($totalQuantity,2,',','.') ?></strong></div>
            <div class="muted" style="margin-top:4px">Valor estimado: <strong style="color:#fff">R$ <?= number_format($totalValue,2,',','.') ?></strong></div>
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
                <div style="padding:6px 8px;border-radius:8px;background:#ff6b6b;color:#fff;font-weight:700">Expirados: <?= $expiredCount ?></div>
                <div style="padding:6px 8px;border-radius:8px;background:#f59e0b;color:#071a1f;font-weight:700">Próx.30d: <?= $soonCount ?></div>
                <div style="padding:6px 8px;border-radius:8px;background:#10b981;color:#042018;font-weight:700">OK: <?= $okCount ?></div>
                <div style="padding:6px 8px;border-radius:8px;background:#64748b;color:#fff;font-weight:700">Sem data: <?= $noDateCount ?></div>
            </div>
        </div>

        <!-- Métricas -->
        <div class="panel-card">
            <div class="muted">Métricas</div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
                <div>
                    <div class="muted">Média preço unitário</div>
                    <div class="big">R$ <?= $unitCount ? number_format($sumUnitPrice/$unitCount,2,',','.') : '0,00' ?></div>
                </div>
                <div style="text-align:right">
                    <div class="muted">Categorias distintas</div>
                    <div class="big"><?= count($categoriesTotals) ?></div>
                </div>
            </div>
            <div style="margin-top:8px" class="muted">Itens sem estoque: <strong style="color:#fff"><?= count($zeroStock) ?></strong></div>
        </div>

        <!-- Estoque baixo -->
        <div class="panel-card">
            <div class="muted">Estoque baixo (limiar: <?= $lowStockThreshold ?>)</div>
            <div class="compact-list">
                <?php if(count($lowStock)===0): ?>
                    <div class="compact-item"><div style="font-weight:700">Nenhum item com estoque baixo</div></div>
                <?php else: foreach($lowStock as $it): ?>
                    <div class="compact-item">
                        <div style="font-weight:700"><?= htmlspecialchars(mb_strimwidth($it['nome'],0,32,'...')) ?></div>
                        <div style="color:var(--muted)"><?= number_format($it['q'],2,',','.') ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Próximas validades -->
        <div class="panel-card">
            <div class="muted">Próximas validades</div>
            <div class="compact-list">
                <?php if(count($soonItems)===0): ?>
                    <div class="compact-item"><div style="font-weight:700">Nenhum item próximo da validade</div></div>
                <?php else: foreach($soonItems as $it): ?>
                    <div class="compact-item">
                        <div style="font-weight:700"><?= htmlspecialchars(mb_strimwidth($it['nome'],0,32,'...')) ?></div>
                        <div style="color:var(--muted)"><?= $it['validade']->format('d/m/Y') ?> • <?= number_format($it['q'],2,',','.') ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <div style="margin-top:8px;display:flex;gap:8px">
                <button id="exportSoonBtn" class="btn btn-ghost">Exportar próximos (CSV)</button>
            </div>
        </div>

        <!-- Produtos expirados -->
        <div class="panel-card">
            <div class="muted">Produtos expirados</div>
            <ul class="small-list">
                <?php if(count($expiredItems) === 0): ?>
                    <li>Nenhum produto expirado</li>
                <?php else: foreach($expiredItems as $it): ?>
                    <li>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="expired-text"><?= htmlspecialchars(mb_strimwidth($it['nome'],0,36,'...')) ?></div>
                            <span class="expired-badge">EXPIRADO</span>
                        </div>
                        <div class="meta"><?= $it['validade']->format('d/m/Y') ?> • <?= number_format($it['q'],2,',','.') ?></div>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
            <div style="margin-top:8px;display:flex;gap:8px">
                <button id="exportExpiredBtn" class="btn btn-ghost">Exportar expirados (CSV)</button>
            </div>
        </div>

        <!-- Produtos sem estoque -->
        <div class="panel-card">
            <div class="muted">Produtos sem estoque</div>
            <ul class="small-list">
                <?php if(count($zeroStock) === 0): ?>
                    <li>Nenhum produto sem estoque</li>
                <?php else: foreach($zeroStock as $z): ?>
                    <li>
                        <div style="font-weight:700"><?= htmlspecialchars(mb_strimwidth($z['nome'],0,36,'...')) ?></div>
                        <div class="meta"><?= number_format($z['q'],2,',','.') ?></div>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
            <div style="margin-top:8px;display:flex;gap:8px">
                <button id="exportZeroBtn" class="btn btn-ghost">Exportar sem estoque (CSV)</button>
            </div>
        </div>

        <!-- Top / Fornecedores / etc (mantidos) -->
        <div class="panel-card">
            <div class="muted">Top produtos (quantidade)</div>
            <div class="compact-list">
                <?php foreach(array_slice($productByQty,0,12) as $p): ?>
                    <div class="compact-item">
                        <div style="font-weight:700"><?= htmlspecialchars(mb_strimwidth($p['name'],0,28,'...')) ?></div>
                        <div style="color:var(--muted)"><?= number_format($p['q'],2,',','.') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel-card">
            <div class="muted">Top produtos (valor)</div>
            <div class="compact-list">
                <?php foreach(array_slice($productByValue,0,12) as $p): ?>
                    <div class="compact-item">
                        <div style="font-weight:700"><?= htmlspecialchars(mb_strimwidth($p['name'],0,28,'...')) ?></div>
                        <div style="color:var(--muted)">R$ <?= number_format($p['v'],2,',','.') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel-card">
            <div class="muted">Fornecedores (top por valor)</div>
            <div class="compact-list">
                <?php foreach(array_slice($supArrTop,0,12) as $s): ?>
                    <div class="compact-item">
                        <div style="font-weight:700"><?= htmlspecialchars(mb_strimwidth($s['name'],0,28,'...')) ?></div>
                        <div style="color:var(--muted)"><?= number_format($s['q'],2,',','.') ?> • R$ <?= number_format($s['v'],2,',','.') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div style="padding-top:8px;display:flex;justify-content:flex-end;gap:8px">
        <button class="btn btn-ghost" onclick="closeInfoPanel()" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--primary-text)">Fechar</button>
        <button class="btn" onclick="window.print()">Imprimir</button>
    </div>
</aside>

<!-- painel de GRÁFICOS -->
<aside id="chartsPanel" class="charts-panel" aria-hidden="true">
    <div style="display:flex;justify-content:space-between;align-items:center">
        <h3 style="margin:0">Gráficos — Detalhes de Produtos</h3>
        <button class="close-btn" onclick="closeChartsPanel()" aria-label="Fechar" style="background:transparent;border:none;color:var(--primary-text);font-size:20px;cursor:pointer">✕</button>
    </div>

    <div style="margin-top:12px;overflow:auto;padding-right:6px;flex:1">
        <div class="panel-card">
            <div class="muted">Categorias (valor) — Top 10</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartCategories" class="chart-canvas"></canvas></div>
        </div>

        <div class="panel-card">
            <div class="muted">Fornecedores (valor) — Top 10</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartSuppliers" class="chart-canvas"></canvas></div>
        </div>

        <div class="panel-card">
            <div class="muted">Status do estoque</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartStockStatus" class="chart-canvas"></canvas></div>
        </div>

        <div class="panel-card">
            <div class="muted">Estoque baixo (quantidade)</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartLowStock" class="chart-canvas"></canvas></div>
        </div>

        <div class="panel-card">
            <div class="muted">Top produtos (valor) — Top 12</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartTopProducts" class="chart-canvas"></canvas></div>
        </div>
    </div>

    <div style="padding-top:8px;display:flex;justify-content:flex-end;gap:8px">
        <button class="btn btn-ghost" onclick="closeChartsPanel()" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--primary-text)">Fechar</button>
    </div>
</aside>

<!-- painel de SAÍDAS (novo) -->
<aside id="saidasPanel" class="saidas-panel" aria-hidden="true">
    <div style="display:flex;justify-content:space-between;align-items:center">
        <h3 style="margin:0">Saídas — Itens Mais Vendidos</h3>
        <div style="display:flex;gap:8px;align-items:center">
            <button id="exportSaidas7" class="btn btn-ghost" title="Exportar (7 dias)">Exportar 7d</button>
            <button id="exportSaidas30" class="btn btn-ghost" title="Exportar (30 dias)">Exportar 30d</button>
            <button class="close-btn" onclick="closeSaidasPanel()" aria-label="Fechar" style="background:transparent;border:none;color:var(--primary-text);font-size:20px;cursor:pointer">✕</button>
        </div>
    </div>

    <div style="margin-top:12px;overflow:auto;padding-right:6px;flex:1">
        <div class="panel-card">
            <div class="muted">Top vendidos — últimos 7 dias</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartSaidas7" class="chart-canvas"></canvas></div>
            <ul id="listSaidas7" class="small-list"></ul>
        </div>

        <div class="panel-card">
            <div class="muted">Top vendidos — últimos 30 dias</div>
            <div style="margin-top:8px" class="chart-wrapper"><canvas id="chartSaidas30" class="chart-canvas"></canvas></div>
            <ul id="listSaidas30" class="small-list"></ul>
        </div>
    </div>

    <div style="padding-top:8px;display:flex;justify-content:flex-end;gap:8px">
        <button class="btn btn-ghost" onclick="closeSaidasPanel()" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--primary-text)">Fechar</button>
    </div>
</aside>

<main class="main-content" id="mainContent">
    <h1>RELATÓRIO DE ESTOQUE</h1>

    <div class="filter-row">
        <div class="field"><label for="startDate">Data inicial</label><input type="date" id="startDate" name="startDate"></div>
        <div class="field"><label for="endDate">Data final</label><input type="date" id="endDate" name="endDate"></div>
        <div class="field search-field"><label for="search">Pesquisar</label><input type="text" id="search" placeholder="Produto / Cat. / Forn."></div>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button id="clearFilters" class="btn btn-ghost">Limpar</button>
            <button id="toggleActionsBtn" class="btn btn-ghost" title="Mostrar/Ocultar Ações">Ocultar Ações</button>
            <a href="cadproduto.php" class="btn" id="openCreateBtn">+ Cadastrar</a>
            <form method="POST" action="relatorio_estoque_pdf.php" target="_blank" style="display:inline-flex;gap:8px;align-items:end">
                <input type="hidden" name="startDate" id="pdfStartDate">
                <input type="hidden" name="endDate" id="pdfEndDate">
                <input type="hidden" name="search" id="pdfSearch">
                <button class="btn" type="submit">📄 Baixar PDF</button>
            </form>
        </div>
    </div>

    <div style="margin-top:14px;overflow:auto" id="tabela">
        <table id="estoqueTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Fornecedor</th>
                    <th>Categoria</th>
                    <th>Unidade</th>
                    <th style="text-align:right">Quantidade</th>
                    <th style="text-align:right">Preço Unit.</th>
                    <th style="text-align:right">Valor Total</th>
                    <th>Validade</th>
                    <th>Status</th>
                    <th class="action-col" style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
<?php
                foreach ($rows as $row):
                    $statusClass = 'status-ok';
                    $statusText = 'OK';
                    $rowClass = '';
                    $valStr = $row['Validade'] ?? '';
                    $d = parseDateSafe($valStr);
                    if ($d === null) {
                        $statusClass = 'status-nodate';
                        $statusText = 'Sem data';
                        $rowClass = 'row-nodate';
                    } else {
                        $dOnly = (clone $d)->setTime(0,0,0);
                        if ($dOnly < $today) {
                            $statusClass = 'status-expired';
                            $statusText = 'Expirado';
                            $rowClass = 'row-expired';
                        } elseif ($dOnly <= $in30) {
                            $statusClass = 'status-soon';
                            $statusText = 'Próx. 30d';
                            $rowClass = 'row-soon';
                        } else {
                            $statusClass = 'status-ok';
                            $statusText = 'OK';
                        }
                    }
                    $q = floatval($row['Qntd_produto'] ?? 0);
                    if ($q <= $lowStockThreshold) $rowClass .= ' row-lowstock';
                    $data_validade = '';
                    $draw = parseDateSafe($row['Validade'] ?? '');
                    if ($draw) $data_validade = $draw->format('Y-m-d');
                ?>
                <tr class="<?= $rowClass ?>"
                    data-id="<?= $row['ID_produto'] ?>"
                    data-nome="<?= htmlspecialchars($row['Nome_prod'], ENT_QUOTES) ?>"
                    data-id_forn="<?= htmlspecialchars($row['id_categorias'] ? ($row['id_categorias']) : '') ?>"
                    data-id_cat="<?= htmlspecialchars($row['id_categorias'] ? ($row['id_categorias']) : '') ?>"
                    data-preco="<?= htmlspecialchars($row['Preco_unitario']) ?>"
                    data-unidade="<?= htmlspecialchars($row['Unid_medida']) ?>"
                    data-validade="<?= $data_validade ?>"
                    data-qnt="<?= htmlspecialchars($row['Qntd_produto']) ?>"
                >
                    <td data-label="ID"><?= $row['ID_produto'] ?></td>
                    <td data-label="Nome"><?= htmlspecialchars($row['Nome_prod']) ?></td>
                    <td data-label="Fornecedor"><?= htmlspecialchars($row['Nome_forn'] ?? '---') ?></td>
                    <td data-label="Categoria"><?= htmlspecialchars($row['nome_categoria'] ?? '---') ?></td>
                    <td data-label="Unidade"><?= htmlspecialchars($row['Unid_medida']) ?></td>
                    <td data-label="Quantidade"><?= number_format($row['Qntd_produto'],2,',','.') ?></td>
                    <td data-label="Preço Unit.">R$ <?= number_format($row['Preco_unitario'],2,',','.') ?></td>
                    <td data-label="Valor Total">R$ <?= number_format($row['valor_total'],2,',','.') ?></td>
                    <td data-label="Validade"><?= ($d) ? $d->format('d/m/Y') : '---' ?></td>
                    <td data-label="Status"><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                    <td class="action-col" style="text-align:center;">
                        <a href="alterar/alterar_produtos.php?id=<?= urlencode($row['ID_produto']) ?>" class="icon-btn edit-btn" title="Alterar"><span class="material-icons">edit</span></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div id="pagination" style="margin-top:16px;text-align:center;"></div>
</main>

<script>
// abrir/fechar painel
function openInfoPanel(){ const p=document.getElementById('infoPanel'); p.classList.add('open'); p.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
function closeInfoPanel(){ const p=document.getElementById('infoPanel'); p.classList.remove('open'); p.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
function openChartsPanel(){ const p=document.getElementById('chartsPanel'); p.classList.add('open'); p.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; renderCharts(true); }
function closeChartsPanel(){ const p=document.getElementById('chartsPanel'); p.classList.remove('open'); p.setAttribute('aria-hidden','true'); document.body.style.overflow=''; if (chartInstances.length){ chartInstances.forEach(c=>{ try{ c.destroy() }catch(e){} }); chartInstances=[]; chartsInitialized=false; } }
function openSaidasPanel(){ const p=document.getElementById('saidasPanel'); p.classList.add('open'); p.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; renderSaidas(); }
function closeSaidasPanel(){ const p=document.getElementById('saidasPanel'); p.classList.remove('open'); p.setAttribute('aria-hidden','true'); document.body.style.overflow=''; if (saidasChartInstances.length){ saidasChartInstances.forEach(c=>{ try{ c.destroy() }catch(e){} }); saidasChartInstances=[]; saidasChartsInitialized=false; } }

// sidebar toggle
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
function toggleSidebar(){ sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('collapsed'); }

document.querySelectorAll('.toggle-btn').forEach(btn=>{ btn.addEventListener('keydown', (e)=>{ if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleSidebar(); } }); });

// 
/* filtros, paginação e ações */
const estoqueTbody = document.querySelector('#estoqueTable tbody');
let estoqueRows = Array.from(estoqueTbody ? estoqueTbody.querySelectorAll('tr') : []);
const startDateEl = document.getElementById('startDate');
const endDateEl = document.getElementById('endDate');
const searchEl = document.getElementById('search');
const clearBtn = document.getElementById('clearFilters');
const pdfStart = document.getElementById('pdfStartDate');
const pdfEnd = document.getElementById('pdfEndDate');
const pdfSearch = document.getElementById('pdfSearch');
const toggleActionsBtn = document.getElementById('toggleActionsBtn');
const openCreateBtn = document.getElementById('openCreateBtn');

let rowsPerPage = 9; // solicitado
let currentPage = 1;
let filteredRows = [...estoqueRows];

function brToISO(dateStr){
    if (!dateStr) return null;
    const parts = dateStr.split('/');
    if(parts.length !== 3) return null;
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
}

function refreshFilteredRows(){
    const s = startDateEl && startDateEl.value ? new Date(startDateEl.value) : null;
    const e = endDateEl && endDateEl.value ? new Date(endDateEl.value) : null;
    const term = (searchEl && searchEl.value || '').trim().toLowerCase();

    filteredRows = estoqueRows.filter(tr => {
        const cells = tr.querySelectorAll('td');
        const valStr = cells[8] ? cells[8].textContent.trim() : '---';
        const valDate = (valStr === '---') ? null : new Date(brToISO(valStr));
        if (s && valDate && valDate < s) return false;
        if (e && valDate && valDate > e) return false;
        const text = Array.from(cells).slice(1,4).map(c => c.textContent.toLowerCase()).join(' ');
        if (term && !text.includes(term)) return false;
        return true;
    });
    currentPage = 1;
    renderTablePage(currentPage);
    renderPagination();
}

function renderTablePage(page){
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    estoqueRows.forEach(row => row.style.display = 'none');
    filteredRows.forEach((row, index) => {
        row.style.display = (index >= start && index < end) ? '' : 'none';
    });
}

function renderPagination(){
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    const pageCount = Math.ceil(filteredRows.length / rowsPerPage);
    if (pageCount <= 1) return;

    const prev = document.createElement('button');
    prev.textContent = '<';
    prev.style.margin = '0 6px'; prev.style.padding='6px 10px'; prev.disabled = (currentPage===1);
    prev.addEventListener('click', ()=>{ if(currentPage>1){ currentPage--; renderTablePage(currentPage); renderPagination(); } });
    pagination.appendChild(prev);

    for (let i = 1; i <= pageCount; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.style.margin = "0 4px";
      btn.style.padding = "6px 10px";
      btn.style.border = "none";
      btn.style.borderRadius = "6px";
      btn.style.cursor = "pointer";
      btn.style.background = (i === currentPage) ? "#0077b6" : "#f1f1f1";
      btn.style.color = (i === currentPage) ? "#fff" : "#000";

      btn.addEventListener('click', () => {
        currentPage = i;
        renderTablePage(currentPage);
        renderPagination();
      });
      pagination.appendChild(btn);
    }

    const next = document.createElement('button');
    next.textContent = '>';
    next.style.margin = '0 6px'; next.style.padding='6px 10px'; next.disabled = (currentPage===pageCount);
    next.addEventListener('click', ()=>{ if(currentPage<pageCount){ currentPage++; renderTablePage(currentPage); renderPagination(); } });
    pagination.appendChild(next);
}

// Eventos
if (startDateEl) startDateEl.addEventListener('input', refreshFilteredRows);
if (endDateEl) endDateEl.addEventListener('input', refreshFilteredRows);
if (searchEl) searchEl.addEventListener('input', refreshFilteredRows);
if (clearBtn) clearBtn.addEventListener('click', () => { if (startDateEl) startDateEl.value=''; if (endDateEl) endDateEl.value=''; if (searchEl) searchEl.value=''; if (pdfStart) pdfStart.value=''; if (pdfEnd) pdfEnd.value=''; if (pdfSearch) pdfSearch.value=''; refreshFilteredRows(); });

const pdfForm = document.querySelector('form[action="relatorio_estoque_pdf.php"]');
if (pdfForm) pdfForm.addEventListener('submit', function(){ if (pdfStart) pdfStart.value = startDateEl ? startDateEl.value : ''; if (pdfEnd) pdfEnd.value = endDateEl ? endDateEl.value : ''; if (pdfSearch) pdfSearch.value = searchEl ? searchEl.value : ''; });

// Toggle ações (mostra/oculta coluna de ações)
let actionsVisible = true;
function setActionsVisibility(visible){
    document.querySelectorAll('th.action-col').forEach(h => h.style.display = visible ? '' : 'none');
    document.querySelectorAll('td.action-col').forEach(td => td.style.display = visible ? '' : 'none');
}
toggleActionsBtn?.addEventListener('click', () => {
    actionsVisible = !actionsVisible;
    setActionsVisibility(actionsVisible);
    toggleActionsBtn.textContent = actionsVisible ? 'Ocultar Ações' : 'Mostrar Ações';
});

// Inicializa: paginar e ajustar ações
renderTablePage(currentPage);
renderPagination();
setActionsVisibility(actionsVisible);

/* --- Dados injetados do PHP (para export e charts) --- */
const exportAllData = <?php echo $exportRowsJSON ?? '[]'; ?>;
const expiredItemsData = <?php echo $expiredItemsJSON ?? '[]'; ?>;
const soonItemsData = <?php echo $soonItemsJSON ?? '[]'; ?>;
const zeroStockData = <?php echo $zeroStockJSON ?? '[]'; ?>;
const catLabels = <?php echo $catLabelsJSON ?? '[]'; ?>;
const catValues = <?php echo $catValuesJSON ?? '[]'; ?>;
const prodByVal = <?php echo $prodByValJSON ?? '[]'; ?>;
const suppliersData = <?php echo $suppliersJSON ?? '[]'; ?>;
const lowStockData = <?php echo $lowStockJSON ?? '[]'; ?>;
const stockStatus = <?php echo $stockStatusJSON ?? '{}'; ?>;
const saidas7 = <?php echo $saidas7JSON ?? '[]'; ?>;
const saidas30 = <?php echo $saidas30JSON ?? '[]'; ?>;

/* CSV export util */
function downloadCSV(filename, rows) {
    if (!rows || !rows.length) { alert('Nenhum dado para exportar'); return; }
    const headers = Object.keys(rows[0]);
    const escape = (v) => { if (v === null || v === undefined) return ''; const s = String(v).replace(/"/g,'""'); return `"${s}"`; };
    const csv = [headers.map(h=>escape(h)).join(',')].concat(
        rows.map(r => headers.map(h => escape(r[h])).join(','))
    ).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
}

/* hook dos botões de export */
/* export buttons */
document.getElementById('exportAllBtn')?.addEventListener('click', ()=> downloadCSV('estoque_todos.csv', exportAllData));
document.getElementById('exportExpiredBtn')?.addEventListener('click', ()=> downloadCSV('expirados.csv', expiredItemsData));
document.getElementById('exportSoonBtn')?.addEventListener('click', ()=> downloadCSV('proximos_validade.csv', soonItemsData));
document.getElementById('exportZeroBtn')?.addEventListener('click', ()=> downloadCSV('sem_estoque.csv', zeroStockData));
document.getElementById('exportSaidas7')?.addEventListener('click', ()=> downloadCSV('saidas_7dias.csv', saidas7.map(s=>({id:s.id,name:s.name,quantidade:s.qty,valor_total:s.total}))));
document.getElementById('exportSaidas30')?.addEventListener('click', ()=> downloadCSV('saidas_30dias.csv', saidas30.map(s=>({id:s.id,name:s.name,quantidade:s.qty,valor_total:s.total}))));

let chartsInitialized = false;
let chartInstances = [];

/* Chart rendering functions */
function drawNoDataMessage(canvas, msg) {
    if (!canvas) return;
    try {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0,0,canvas.width, canvas.height);
        ctx.fillStyle = 'rgba(255,255,255,0.02)'; ctx.fillRect(0,0,canvas.width, canvas.height);
        ctx.fillStyle = 'rgba(255,255,255,0.7)';
        const fontSize = Math.max(12, Math.floor(canvas.height * 0.08));
        ctx.font = `bold ${fontSize}px sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(msg, canvas.width / 2, canvas.height / 2);
    } catch (e) { console.warn(e); }
}

function renderCharts(force=false){
    if (chartsInitialized && !force) return;
    if (typeof Chart === 'undefined') {
        ['chartCategories','chartSuppliers','chartStockStatus','chartLowStock','chartTopProducts'].forEach(id=>{ const c=document.getElementById(id); if(c) drawNoDataMessage(c,'Chart.js ausente');});
        chartsInitialized = true; return;
    }
    requestAnimationFrame(()=>{
        setTimeout(()=>{
            if (chartInstances.length) { chartInstances.forEach(c=>{ try{ c.destroy(); } catch(e){} }); chartInstances = []; }
            function fit(id){ const el=document.getElementById(id); if(!el) return; const w=Math.max(300, Math.round(el.clientWidth)); const h=(el.clientHeight && el.clientHeight>20)?Math.round(el.clientHeight):240; if(el.width!==w||el.height!==h){el.width=w;el.height=h;} }
            ['chartCategories','chartSuppliers','chartStockStatus','chartLowStock','chartTopProducts'].forEach(fit);
            const palette = ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf'];

            // Categories
            try {
                const el=document.getElementById('chartCategories');
                if (el && Array.isArray(catLabels) && catLabels.length) {
                    const ctx=el.getContext('2d');
                    const bg = catLabels.map((_,i)=>palette[i%palette.length]);
                    const chart = new Chart(ctx,{ type:'bar', data:{ labels:catLabels, datasets:[{ label:'Valor (R$)', data:catValues, backgroundColor:bg, borderWidth:1 }] }, options:{ maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true } } }});
                    chartInstances.push(chart);
                } else drawNoDataMessage(el,'Sem dados (categorias)');
            } catch(e){ console.error(e); }

            // Suppliers
            try {
                const el=document.getElementById('chartSuppliers');
                if (el && Array.isArray(suppliersData) && suppliersData.length) {
                    const labels = suppliersData.map(s=>s.name);
                    const values = suppliersData.map(s=>Number(s.v));
                    const bg = labels.map((_,i)=>palette[i%palette.length]);
                    const ctx=el.getContext('2d');
                    const chart = new Chart(ctx,{ type:'bar', data:{ labels, datasets:[{ label:'Valor (R$)', data:values, backgroundColor:bg, borderWidth:1 }] }, options:{ maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true } } }});
                    chartInstances.push(chart);
                } else drawNoDataMessage(el,'Sem dados (fornecedores)');
            } catch(e){ console.error(e); }

            // Stock status
            try {
                const el=document.getElementById('chartStockStatus');
                if (el && stockStatus) {
                    const values=[Number(stockStatus.expired||0),Number(stockStatus.soon||0),Number(stockStatus.ok||0),Number(stockStatus.nodate||0)];
                    const total = values.reduce((a,b)=>a+b,0);
                    if (total>0) {
                        const ctx=el.getContext('2d');
                        const chart = new Chart(ctx,{ type:'pie', data:{ labels:['Expirados','Próx.30d','OK','Sem data'], datasets:[{ data:values, backgroundColor:['#ff6b6b','#f59e0b','#10b981','#64748b'] }] }, options:{ maintainAspectRatio:false }});
                        chartInstances.push(chart);
                    } else drawNoDataMessage(el,'Sem dados (status)');
                }
            } catch(e){ console.error(e); }

            // Low stock
            try {
                const el=document.getElementById('chartLowStock');
                if (el && Array.isArray(lowStockData) && lowStockData.length) {
                    const labels=lowStockData.map(p=>p.name);
                    const values=lowStockData.map(p=>Number(p.q));
                    const bg=labels.map((_,i)=>palette[i%palette.length]);
                    const ctx=el.getContext('2d');
                    const chart=new Chart(ctx,{ type:'bar', data:{ labels, datasets:[{ label:'Quantidade', data:values, backgroundColor:bg, borderWidth:1 }] }, options:{ maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true } } }});
                    chartInstances.push(chart);
                } else drawNoDataMessage(el,'Sem dados (estoque baixo)');
            } catch(e){ console.error(e); }

            // Top products
            try {
                const el=document.getElementById('chartTopProducts');
                if (el && Array.isArray(prodByVal) && prodByVal.length) {
                    const labels=prodByVal.map(p=>p.name);
                    const values=prodByVal.map(p=>Number(p.v));
                    const bg=labels.map((_,i)=>palette[i%palette.length]);
                    const ctx=el.getContext('2d');
                    const chart=new Chart(ctx,{ type:'bar', data:{ labels, datasets:[{ label:'Valor (R$)', data:values, backgroundColor:bg, borderWidth:1 }] }, options:{ indexAxis:'y', maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ x:{ beginAtZero:true } } }});
                    chartInstances.push(chart);
                } else drawNoDataMessage(el,'Sem dados (top produtos)');
            } catch(e){ console.error(e); }

            chartsInitialized = true;
        }, 80);
    });
}

// --- SAÍDAS: gráfico e lista ---
let saidasChartsInitialized=false; let saidasChartInstances=[];
function renderSaidas(force=false){
    if (saidasChartsInitialized && !force) return;
    if (typeof Chart === 'undefined') {
        ['chartSaidas7','chartSaidas30'].forEach(id=>{ const c=document.getElementById(id); if(c) drawNoDataMessage(c,'Chart.js ausente');}); saidasChartsInitialized=true; return;
    }
    requestAnimationFrame(()=>{
        setTimeout(()=>{
            if (saidasChartInstances.length) { saidasChartInstances.forEach(c=>{ try{ c.destroy() } catch(e){} }); saidasChartInstances = []; }
            function fit(id){ const el=document.getElementById(id); if(!el) return; const w=Math.max(300, Math.round(el.clientWidth)); const h=(el.clientHeight && el.clientHeight>20)?Math.round(el.clientHeight):240; if(el.width!==w||el.height!==h){el.width=w;el.height=h;} }
            ['chartSaidas7','chartSaidas30'].forEach(fit);
            const palette = ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf'];

            // 7 dias
            try{
                const el=document.getElementById('chartSaidas7');
                if (el && Array.isArray(saidas7) && saidas7.length){
                    const labels=saidas7.map(s=>s.name);
                    const values=saidas7.map(s=>Number(s.qty));
                    const bg=labels.map((_,i)=>palette[i%palette.length]);
                    const ctx=el.getContext('2d');
                    const chart=new Chart(ctx,{ type:'bar', data:{ labels, datasets:[{ label:'Qtd vendida', data:values, backgroundColor:bg, borderWidth:1 }] }, options:{ maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true } } }});
                    saidasChartInstances.push(chart);

                    // preencher lista
                    const ul=document.getElementById('listSaidas7'); ul.innerHTML='';
                    saidas7.forEach(s=>{ const li=document.createElement('li'); li.innerHTML = `<div style="font-weight:700">${s.name}</div><div class="meta">${Number(s.qty)} • R$ ${Number(s.total).toFixed(2)}</div>`; ul.appendChild(li); });
                } else drawNoDataMessage(el,'Sem dados (7 dias)');
            }catch(e){console.error(e);} 

            // 30 dias
            try{
                const el=document.getElementById('chartSaidas30');
                if (el && Array.isArray(saidas30) && saidas30.length){
                    const labels=saidas30.map(s=>s.name);
                    const values=saidas30.map(s=>Number(s.qty));
                    const bg=labels.map((_,i)=>palette[i%palette.length]);
                    const ctx=el.getContext('2d');
                    const chart=new Chart(ctx,{ type:'bar', data:{ labels, datasets:[{ label:'Qtd vendida', data:values, backgroundColor:bg, borderWidth:1 }] }, options:{ maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true } } }});
                    saidasChartInstances.push(chart);

                    const ul=document.getElementById('listSaidas30'); ul.innerHTML='';
                    saidas30.forEach(s=>{ const li=document.createElement('li'); li.innerHTML = `<div style="font-weight:700">${s.name}</div><div class="meta">${Number(s.qty)} • R$ ${Number(s.total).toFixed(2)}</div>`; ul.appendChild(li); });
                } else drawNoDataMessage(el,'Sem dados (30 dias)');
            }catch(e){console.error(e);} 

            saidasChartsInitialized = true;
        },80);
    });
}

/* Exibe flash messages se houver (vindo do PHP) */
<?php if (!empty($_SESSION['flash'])): ?>
    alert("<?= addslashes($_SESSION['flash']) ?>");
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_err'])): ?>
    alert("<?= addslashes($_SESSION['flash_err']) ?>");
    <?php unset($_SESSION['flash_err']); ?>
<?php endif; ?>

</script>
</body>
</html>
