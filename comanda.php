<?php
session_start();

// =========================
// comanda.php (melhorado) - PATCH AJAX
// - PDO seguro (exceções, desativar emulate prepares)
// - CSRF token
// - salvar valor_unitario em itens_vendas
// - fechamento com TRANSACTION + SELECT ... FOR UPDATE (checar estoque)
// - prepared statements sempre
// - tratamento de erros e mensagens friendly
// - suporte JSON/AJAX: is_ajax() e send_json()
// OBS: execute as migrações no DB para adicionar colunas/ tabelas conforme comentado abaixo.
// =========================

// === Configurações de ambiente (não deixar EXIBIR ERROS em produção) ===
// Em desenvolvimento é útil exibir erros; em produção comente as três linhas abaixo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ====== CONFIGURAÇÃO DO BANCO ====== */
$host = "127.0.0.1";
$db   = "padariadoalemao";
$user = "root"; // troque para um usuário com privilégios mínimos
$pass = "";
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // Em produção, registre o erro em arquivo/monitoramento em vez de mostrar
    die('Erro de conexão com o banco.');
}

// ====== CSRF ======
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function check_csrf(array $post) {
    if (!isset($post['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $post['csrf'])) {
        throw new Exception('Requisição inválida (CSRF).');
    }
}

// ====== Helpers AJAX/JSON ======
function is_ajax() {
    return (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (!empty($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
}

function send_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

/* ====== LOGIN ====== */
if (!isset($_SESSION['ID_func'])) {
    // se for AJAX, informe que a sessão expirou em JSON
    if (is_ajax()) send_json(['success' => false, 'msg' => 'Sessão expirada'], 401);

    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location='login.php';</script>";
    exit();
}

/* ====== NOTA DE MIGRAÇÃO (rodar no DB) ======
ALTER TABLE itens_vendas
  ADD COLUMN valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN valor_total DECIMAL(10,2) NOT NULL DEFAULT 0;

CREATE TABLE pagamentos (
  ID_pagamento INT AUTO_INCREMENT PRIMARY KEY,
  ID_vendas INT NOT NULL,
  metodo VARCHAR(50),
  valor_pago DECIMAL(10,2) NOT NULL,
  troco DECIMAL(10,2) DEFAULT 0,
  data DATETIME DEFAULT CURRENT_TIMESTAMP,
  ID_func_registro INT,
  FOREIGN KEY (ID_vendas) REFERENCES vendas(ID_vendas)
);

-- Garanta InnoDB nas tabelas para suportar TRANSACTIONS e FOR UPDATE
*/

/* ====== FUNÇÕES ====== */
function listarComandas($pdo, $limit = 100) {
    $sql = "SELECT v.*, f.Nome_func
              FROM vendas v
              LEFT JOIN funcionario f ON f.ID_func = v.ID_func
             ORDER BY v.venda_data DESC
             LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function detalheComanda($pdo, $id) {
    $cab = $pdo->prepare("SELECT v.*, f.Nome_func
                            FROM vendas v
                            LEFT JOIN funcionario f ON f.ID_func = v.ID_func
                           WHERE v.ID_vendas = ?");
    $cab->execute([$id]);
    $cab = $cab->fetch();
    if (!$cab) return null;

    $itens = $pdo->prepare("SELECT iv.*, p.Nome_prod
                              FROM itens_vendas iv
                              JOIN produtos p ON p.ID_produto = iv.ID_produto
                             WHERE iv.ID_vendas = ?");
    $itens->execute([$id]);
    $cab['itens'] = $itens->fetchAll();

    $total = 0;
    foreach ($cab['itens'] as $it) $total += (float)$it['valor_total'];
    $cab['total'] = $total;

    return $cab;
}

/* ====== AÇÕES ====== */
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf($_POST);

        $acao = $_POST['acao'] ?? '';

        if ($acao === 'nova') {
            $stmt = $pdo->prepare("INSERT INTO vendas (ID_func, venda_data, status) VALUES (?, NOW(), 'ABERTA')");
            $stmt->execute([$_SESSION['ID_func']]);
            $novaId = $pdo->lastInsertId();

            if (is_ajax()) {
                send_json(['success' => true, 'redirect' => 'comanda.php?id=' . $novaId]);
            }

            header("Location: comanda.php?id=" . $novaId);
            exit();
        }

        if ($acao === 'add_item') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            $id_prod  = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;
            $qtd      = isset($_POST['quantidade']) ? max(1, (int)$_POST['quantidade']) : 1;

            if ($id_venda <= 0 || $id_prod <= 0) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'Dados inválidos'], 400);
                throw new Exception('Dados inválidos.');
            }

            // Buscar preço e estoque atual
            $p = $pdo->prepare("SELECT Preco_unitario, Qntd_produto FROM produtos WHERE ID_produto = ?");
            $p->execute([$id_prod]);
            $prod = $p->fetch();
            if (!$prod) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'Produto não encontrado'], 404);
                throw new Exception('Produto não encontrado.');
            }

            if ((int)$prod['Qntd_produto'] < $qtd) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'Estoque insuficiente'], 400);
                throw new Exception('Estoque insuficiente (Disponível: ' . (int)$prod['Qntd_produto'] . ').');
            }

            $valor_unitario = number_format($prod['Preco_unitario'], 2, '.', '');
            $valor_total = number_format($valor_unitario * $qtd, 2, '.', '');

            $ins = $pdo->prepare("INSERT INTO itens_vendas (ID_vendas, ID_produto, Quantidade, valor_unitario, valor_total) VALUES (?,?,?,?,?)");
            $ins->execute([$id_venda, $id_prod, $qtd, $valor_unitario, $valor_total]);

            if (is_ajax()) {
                // opcional: calcule novo total para retornar à UI
                $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
                $stmt->execute([$id_venda]);
                $totalRow = $stmt->fetch();
                $novo_total = (float)($totalRow['total'] ?? 0);

                send_json(['success' => true, 'msg' => 'Item adicionado', 'novo_total' => $novo_total]);
            }

            $msg = 'Item adicionado!';
        }

        if ($acao === 'fechar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda <= 0) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'ID inválido'], 400);
                throw new Exception('ID inválido.');
            }

            try {
                $pdo->beginTransaction();

                // Seleciona itens e guarda lock nas linhas de produtos
                $itens = $pdo->prepare("SELECT iv.ID_produto, iv.Quantidade, p.Qntd_produto
                                         FROM itens_vendas iv
                                         JOIN produtos p ON p.ID_produto = iv.ID_produto
                                        WHERE iv.ID_vendas = ?
                                        FOR UPDATE");
                $itens->execute([$id_venda]);
                $itensList = $itens->fetchAll();

                if (!$itensList) {
                    // Não há itens — ainda assim é possível fechar ou forçar
                }

                foreach ($itensList as $i) {
                    if ((int)$i['Qntd_produto'] < (int)$i['Quantidade']) {
                        if (is_ajax()) send_json(['success' => false, 'msg' => 'Estoque insuficiente para produto ' . $i['ID_produto']], 400);
                        throw new Exception('Estoque insuficiente para produto ' . $i['ID_produto'] . '. Disponível: ' . $i['Qntd_produto']);
                    }
                    $upd = $pdo->prepare("UPDATE produtos SET Qntd_produto = Qntd_produto - ? WHERE ID_produto = ?");
                    $upd->execute([(int)$i['Quantidade'], (int)$i['ID_produto']]);
                }

                $pdo->prepare("UPDATE vendas SET status='FECHADA' WHERE ID_vendas=?")->execute([$id_venda]);

                $pdo->commit();

                if (is_ajax()) send_json(['success' => true, 'redirect' => 'comanda.php?id=' . $id_venda]);

                header("Location: comanda.php?id=" . $id_venda);
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e; // será capturado no catch externo
            }
        }

        if ($acao === 'cancelar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda <= 0) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'ID inválido'], 400);
                throw new Exception('ID inválido');
            }
            $pdo->prepare("UPDATE vendas SET status='CANCELADA' WHERE ID_vendas=?")->execute([$id_venda]);
            if (is_ajax()) send_json(['success' => true, 'redirect' => 'comanda.php?id=' . $id_venda]);
            header("Location: comanda.php?id=" . $id_venda);
            exit();
        }

        if ($acao === 'reabrir') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda <= 0) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'ID inválido'], 400);
                throw new Exception('ID inválido');
            }
            $pdo->prepare("UPDATE vendas SET status='ABERTA' WHERE ID_vendas=?")->execute([$id_venda]);
            if (is_ajax()) send_json(['success' => true, 'redirect' => 'comanda.php?id=' . $id_venda]);
            header("Location: comanda.php?id=" . $id_venda);
            exit();
        }

        if ($acao === 'salvar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda <= 0) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'ID inválido'], 400);
                throw new Exception('ID inválido');
            }
            $pdo->prepare("UPDATE vendas SET venda_data=NOW() WHERE ID_vendas=?")->execute([$id_venda]);
            if (is_ajax()) send_json(['success' => true, 'msg' => 'Comanda salva com sucesso!']);
            $msg = 'Comanda salva com sucesso!';
        }

        if ($acao === 'pagar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda <= 0) {
                if (is_ajax()) send_json(['success' => false, 'msg' => 'ID inválido'], 400);
                throw new Exception('ID inválido');
            }

            // Calcular total atual da comanda
            $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
            $stmt->execute([$id_venda]);
            $totalRow = $stmt->fetch();
            $total = (float)($totalRow['total'] ?? 0);

            // Registrar pagamento na tabela pagamentos (assumindo migração criada)
            $metodo = substr(trim($_POST['metodo'] ?? 'DINHEIRO'), 0, 50);
            $valor_pago = isset($_POST['valor_pago']) ? (float)$_POST['valor_pago'] : $total;
            $troco = max(0, $valor_pago - $total);

            $ins = $pdo->prepare("INSERT INTO pagamentos (ID_vendas, metodo, valor_pago, troco, ID_func_registro) VALUES (?,?,?,?,?)");
            $ins->execute([$id_venda, $metodo, number_format($valor_pago,2,'.',''), number_format($troco,2,'.',''), $_SESSION['ID_func']]);

            // Opcional: copiar itens para pagamentos_itens para histórico (se tabela existir)
            $itens = $pdo->prepare("SELECT ID_produto, Quantidade, valor_total FROM itens_vendas WHERE ID_vendas=?");
            $itens->execute([$id_venda]);
            $itens = $itens->fetchAll();
            foreach ($itens as $i) {
                $pdo->prepare("INSERT INTO pagamentos_itens (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)")
                    ->execute([$id_venda, $i['ID_produto'], $i['Quantidade'], $i['valor_total']]);
            }

            if (is_ajax()) send_json(['success' => true, 'msg' => 'Pagamento registrado com sucesso!', 'total' => $total]);

            $msg = 'Pagamento registrado com sucesso! Total: R$ ' . number_format($total, 2, ',', '.');
        }

    } catch (Exception $e) {
        // em produção, envie $e->getMessage() para logs
        if (is_ajax()) send_json(['success' => false, 'msg' => $e->getMessage()], 400);
        $msg = 'Erro: ' . $e->getMessage();
    }
}

$comandas = listarComandas($pdo);
$comandaSel = isset($_GET['id']) ? detalheComanda($pdo, (int)$_GET['id']) : null;
$produtos = $pdo->query("SELECT ID_produto, Nome_prod, Preco_unitario, Qntd_produto FROM produtos ORDER BY Nome_prod LIMIT 500")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Comandas - Melhorado</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
:root { --bg:#1b263b; --sidebar-bg:linear-gradient(180deg,#0d1b2a,#1b263b); --text-color:#f8f9fa; --highlight:#0077b6; --card-bg:#fff; }
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{display:flex;background:var(--bg);color:var(--text-color);}
.sidebar{width:240px;background:var(--sidebar-bg);height:100vh;position:fixed;display:flex;flex-direction:column;padding-top:20px;transition:.3s;box-shadow:3px 0 10px rgba(0,0,0,.3);}
.sidebar.collapsed{width:60px;}
.sidebar a{display:flex;align-items:center;color:var(--text-color);text-decoration:none;padding:15px 20px;transition:.2s;white-space:nowrap;}
.sidebar a:hover{background:#1e3a5f;border-left:4px solid var(--highlight);padding-left:16px;}
.sidebar .icon{margin-right:8px;}
.sidebar.collapsed .text{display:none;}
.sidebar.collapsed .icon{margin:0 auto;}
.toggle-btn{cursor:pointer;text-align:center;margin-bottom:20px;font-size:22px;color:var(--text-color);}
.sidebar form{padding:0 20px 10px;}
.sidebar form button{width:100%;margin-top:10px;}
.main-content{margin-left:240px;padding:30px;width:100%;transition:.3s;}
.main-content.collapsed{margin-left:60px;}
h1{margin-bottom:20px;color:#fff;text-align:center;}
.container{display:grid;grid-template-columns:300px 1fr;gap:20px;}
.card{background:var(--card-bg);padding:20px;border-radius:10px;box-shadow:0 3px 8px rgba(0,0,0,.2);color:#333;}
.btn{padding:10px 15px;border:none;border-radius:6px;cursor:pointer;}
.btn.green{background:#4caf50;color:#fff;}
.btn.red{background:#e53935;color:#fff;}
.btn.blue{background:#1e88e5;color:#fff;}
.list-item{padding:10px;border-bottom:1px solid #ddd;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;border-bottom:1px solid #ccc;text-align:left;}
tfoot td{font-weight:bold;}
.msg{padding:10px;border-radius:6px;margin-bottom:10px;}
.msg.ok{background:#c8e6c9;color:#2e7d32;}
.modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background: rgba(0,0,0,0.5);}
.modal-content{background:#fff;margin:10% auto;padding:20px;border-radius:10px;width:90%;max-width:500px;color:#333;}
.close{float:right;font-size:28px;cursor:pointer;}
</style>
</head>
<body>
<nav class="sidebar" id="sidebar">
<div class="toggle-btn" onclick="toggleSidebar()">☰</div>
<form method="post"><input type="hidden" name="acao" value="nova"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><button class="btn green">Nova Comanda</button></form>
<a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
<a href="produtos.php"><span class="material-icons icon">bakery_dining</span><span class="text">Produtos</span></a>
<a href="funcionarios.php"><span class="material-icons icon">person</span><span class="text">Funcionários</span></a>
<a href="fornecedores.php"><span class="material-icons icon">work</span><span class="text">Fornecedores</span></a>
<a href="estoque.php"><span class="material-icons icon">analytics</span><span class="text">Estoque</span></a>
<a href="relatorio_vendas_padaria_alemao1.php"><span class="material-icons icon">analytics</span><span class="text">Vendas</span></a>
<a href="comanda.php"><span class="material-icons icon">receipt_long</span><span class="text">Comanda</span></a>
</nav>

<main class="main-content" id="mainContent">
<h1>Sistema de Comandas</h1>
<div class="container">
<div class="card">
<h2>Comandas</h2>
<?php if($msg): ?><div class="msg ok"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<hr>
<?php foreach($comandas as $c): ?>
<div class="list-item">
<a href="?id=<?= $c['ID_vendas'] ?>"><b>#<?= $c['ID_vendas'] ?></b></a><br>
Criada por: <?= htmlspecialchars($c['Nome_func'] ?? 'N/A') ?><br>
Status: <?= htmlspecialchars($c['status'] ?? 'N/A') ?><br>
Data: <?= htmlspecialchars($c['venda_data'] ?? '') ?>
</div>
<?php endforeach; ?>
</div>

<div class="card">
<h2>Detalhes</h2>
<?php if(!$comandaSel): ?>
<p>Selecione uma comanda.</p>
<?php else: ?>
<p>
<b>ID:</b> <?= htmlspecialchars($comandaSel['ID_vendas']) ?> | 
<b>Status:</b> <?= htmlspecialchars($comandaSel['status']) ?> | 
<b>Criada por:</b> <?= htmlspecialchars($comandaSel['Nome_func']) ?> | 
<b>Usuário atual:</b> <?= htmlspecialchars($_SESSION['Nome_func'] ?? '') ?>
</p>

<?php if($comandaSel['status'] === 'ABERTA'): ?>
<form id="addItemForm" method="post" style="margin-bottom:10px">
<input type="hidden" name="acao" value="add_item">
<input type="hidden" name="id_venda" value="<?= $comandaSel['ID_vendas'] ?>">
<input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
<select name="id_produto" required>
<option value="">Selecione produto</option>
<?php foreach($produtos as $p): ?>
<option value="<?= $p['ID_produto'] ?>"><?= htmlspecialchars($p['Nome_prod']) ?> - R$ <?= number_format($p['Preco_unitario'],2,',','.') ?> (Estoque: <?= (int)$p['Qntd_produto'] ?>)</option>
<?php endforeach; ?>
</select>
<input type="number" name="quantidade" value="1" min="1" required>
<button class="btn blue">Adicionar</button>
</form>
<?php endif; ?>

<table>
<thead><tr><th>Produto</th><th>Qtd</th><th>Valor</th></tr></thead>
<tbody>
<?php foreach($comandaSel['itens'] as $i): ?>
<tr>
<td><?= htmlspecialchars($i['Nome_prod']) ?></td>
<td><?= (int)$i['Quantidade'] ?></td>
<td>R$ <?= number_format($i['valor_total'],2,',','.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr><td colspan="2">Total</td><td>R$ <?= number_format($comandaSel['total'],2,',','.') ?></td></tr>
</tfoot>
</table>

<?php if($comandaSel['status'] === 'ABERTA'): ?>
<div style="margin-top:10px;">
<form method="post" style="display:inline-block;"><input type="hidden" name="acao" value="fechar"><input type="hidden" name="id_venda" value="<?= $comandaSel['ID_vendas'] ?>"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><button class="btn green">Fechar Comanda</button></form>
<form method="post" style="display:inline-block;"><input type="hidden" name="acao" value="cancelar"><input type="hidden" name="id_venda" value="<?= $comandaSel['ID_vendas'] ?>"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><button class="btn red">Cancelar</button></form>
<form method="post" style="display:inline-block;"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id_venda" value="<?= $comandaSel['ID_vendas'] ?>"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><button class="btn blue">Salvar Comanda</button></form>

<!-- Botão ir para pagamento -->
<form method="post" style="display:inline-block;" onsubmit="return confirm('Registrar pagamento?');"><input type="hidden" name="acao" value="pagar"><input type="hidden" name="id_venda" value="<?= $comandaSel['ID_vendas'] ?>"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><button class="btn green">Confirmar Pagamento</button></form>
</div>
<?php endif; ?>

<form method="get" action="comanda_pdf.php" target="_blank" style="display:inline-block;margin-top:10px">
<input type="hidden" name="id" value="<?= $comandaSel['ID_vendas'] ?>">
<button class="btn blue">📄 Baixar PDF</button>
</form>

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

// AJAX seguro: checa content-type e trata redirects JSON
const addForm = document.getElementById('addItemForm');
if (addForm) {
  addForm.addEventListener('submit', async function(e){
    e.preventDefault();
    const data = new FormData(addForm);
    try {
      const res = await fetch('comanda.php', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: data,
        credentials: 'same-origin'
      });

      const contentType = res.headers.get('content-type') || '';

      if (!res.ok) {
        if (contentType.indexOf('application/json') !== -1) {
          const errJson = await res.json();
          alert(errJson.msg || 'Erro no servidor');
        } else {
          const txt = await res.text();
          console.error('Resposta não-JSON (erro):', txt);
          alert('Erro no servidor (resposta inesperada). Veja console.');
        }
        return;
      }

      if (contentType.indexOf('application/json') !== -1) {
        const json = await res.json();
        if (json.success) {
          if (json.redirect) { window.location = json.redirect; return; }
          // Atualize a UI dinamicamente (ex.: novo total)
          location.reload();
        } else {
          alert(json.msg || 'Falha ao adicionar item');
        }
      } else {
        const txt = await res.text();
        console.error('Resposta não-JSON:', txt);
        alert('Resposta inesperada do servidor. Verifique se você está logado.');
      }

    } catch (err) {
      console.error('Erro fetch:', err);
      alert('Erro na requisição: ' + err.message);
    }
  });
}
</script>
</body>
</html>
