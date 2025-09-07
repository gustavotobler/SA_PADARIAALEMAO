<?php
// comanda_single.php
// Página enxuta: criar / editar / operar uma única comanda
// Salve como comanda_single.php

session_start();

// -> Em desenvolvimento, útil ativar; em produção comente.
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

// ===== Config DB =====
$host = '127.0.0.1';
$db   = 'padariadoalemao';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

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
if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
function check_csrf(array $post) {
    if (!isset($post['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $post['csrf'])) {
        throw new Exception('Requisição inválida (CSRF).');
    }
}

// ===== Helpers AJAX/JSON =====
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

// ===== Autenticação mínima =====
if (!isset($_SESSION['ID_func'])) {
    if (is_ajax()) send_json(['success'=>false,'msg'=>'Sessão expirada'],401);
    echo "<script>alert('Sessão expirada. Faça login.'); window.location='login.php';</script>";
    exit();
}

// ===== Funções específicas =====
function get_products($pdo) {
    return $pdo->query("SELECT ID_produto, Nome_prod, Preco_unitario, Qntd_produto FROM produtos ORDER BY Nome_prod LIMIT 500")->fetchAll();
}

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
    $it = $pdo->prepare("SELECT iv.*, p.Nome_prod FROM itens_vendas iv LEFT JOIN produtos p ON p.ID_produto = iv.ID_produto WHERE iv.ID_vendas = ?");
    $it->execute([(int)$id]);
    $cab['itens'] = $it->fetchAll();
    $total = 0.0; foreach ($cab['itens'] as $i) $total += (float)($i['valor_total'] ?? 0);
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
            if (is_ajax()) send_json(['success'=>true,'id'=>$novaId]);
            header('Location: ?id='.$novaId);
            exit();
        }

        if ($acao === 'add_item') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            $id_prod  = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;
            $qtd      = isset($_POST['quantidade']) ? max(1,(int)$_POST['quantidade']) : 1;
            if ($id_venda<=0 || $id_prod<=0) { if (is_ajax()) send_json(['success'=>false,'msg'=>'Dados inválidos'],400); throw new Exception('Dados inválidos'); }

            // buscar preço e validar estoque
            $p = $pdo->prepare("SELECT Preco_unitario, Qntd_produto FROM produtos WHERE ID_produto = ?");
            $p->execute([$id_prod]);
            $prod = $p->fetch();
            if (!$prod) { if (is_ajax()) send_json(['success'=>false,'msg'=>'Produto não encontrado'],404); throw new Exception('Produto não encontrado'); }
            if ((int)$prod['Qntd_produto'] < $qtd) { if (is_ajax()) send_json(['success'=>false,'msg'=>'Estoque insuficiente'],400); throw new Exception('Estoque insuficiente'); }

            $valor_unitario = number_format($prod['Preco_unitario'],2,'.','');
            $valor_total = number_format($valor_unitario * $qtd,2,'.','');

            $ins = $pdo->prepare("INSERT INTO itens_vendas (ID_vendas, ID_produto, Quantidade, valor_unitario, valor_total) VALUES (?,?,?,?,?)");
            $ins->execute([$id_venda, $id_prod, $qtd, $valor_unitario, $valor_total]);

            if (is_ajax()) {
                $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
                $stmt->execute([$id_venda]);
                $totalRow = $stmt->fetch();
                send_json(['success'=>true,'msg'=>'Item adicionado','novo_total'=> (float)($totalRow['total'] ?? 0)]);
            }

            $msg = "Item adicionado.";
        }

        if ($acao === 'fechar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda<=0) { if (is_ajax()) send_json(['success'=>false,'msg'=>'ID inválido'],400); throw new Exception('ID inválido'); }

            try {
                $pdo->beginTransaction();
                $itens = $pdo->prepare("SELECT iv.ID_produto, iv.Quantidade, p.Qntd_produto FROM itens_vendas iv JOIN produtos p ON p.ID_produto = iv.ID_produto WHERE iv.ID_vendas = ? FOR UPDATE");
                $itens->execute([$id_venda]);
                $itensList = $itens->fetchAll();

                foreach ($itensList as $i) {
                    if ((int)$i['Qntd_produto'] < (int)$i['Quantidade']) {
                        if (is_ajax()) send_json(['success'=>false,'msg'=>'Estoque insuficiente para produto '.$i['ID_produto']],400);
                        throw new Exception('Estoque insuficiente para produto '.$i['ID_produto']);
                    }
                    $upd = $pdo->prepare("UPDATE produtos SET Qntd_produto = Qntd_produto - ? WHERE ID_produto = ?");
                    $upd->execute([(int)$i['Quantidade'], (int)$i['ID_produto']]);
                }

                $pdo->prepare("UPDATE vendas SET status='FECHADA' WHERE ID_vendas=?")->execute([$id_venda]);
                $pdo->commit();

                if (is_ajax()) send_json(['success'=>true,'redirect'=>'?id='.$id_venda]);
                header('Location: ?id='.$id_venda);
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        if ($acao === 'cancelar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda<=0) { if (is_ajax()) send_json(['success'=>false,'msg'=>'ID inválido'],400); throw new Exception('ID inválido'); }
            $pdo->prepare("UPDATE vendas SET status='CANCELADA' WHERE ID_vendas=?")->execute([$id_venda]);
            if (is_ajax()) send_json(['success'=>true,'redirect'=>'?id='.$id_venda]);
            header('Location:?id='.$id_venda); exit();
        }

        if ($acao === 'reabrir') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda<=0) { if (is_ajax()) send_json(['success'=>false,'msg'=>'ID inválido'],400); throw new Exception('ID inválido'); }
            $pdo->prepare("UPDATE vendas SET status='ABERTA' WHERE ID_vendas=?")->execute([$id_venda]);
            if (is_ajax()) send_json(['success'=>true,'redirect'=>'?id='.$id_venda]);
            header('Location:?id='.$id_venda); exit();
        }

        if ($acao === 'salvar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda<=0) { if (is_ajax()) send_json(['success'=>false,'msg'=>'ID inválido'],400); throw new Exception('ID inválido'); }
            $pdo->prepare("UPDATE vendas SET venda_data=NOW() WHERE ID_vendas=?")->execute([$id_venda]);
            if (is_ajax()) send_json(['success'=>true,'msg'=>'Comanda salva com sucesso!']);
            $msg = 'Comanda salva com sucesso!';
        }

        if ($acao === 'pagar') {
            $id_venda = isset($_POST['id_venda']) ? (int)$_POST['id_venda'] : 0;
            if ($id_venda<=0) { if (is_ajax()) send_json(['success'=>false,'msg'=>'ID inválido'],400); throw new Exception('ID inválido'); }

            // calcular total
            $stmt = $pdo->prepare("SELECT SUM(valor_total) as total FROM itens_vendas WHERE ID_vendas = ?");
            $stmt->execute([$id_venda]);
            $totalRow = $stmt->fetch();
            $total = (float)($totalRow['total'] ?? 0);

            $metodo = substr(trim($_POST['metodo'] ?? 'DINHEIRO'),0,50);
            $valor_pago = isset($_POST['valor_pago']) ? (float)$_POST['valor_pago'] : $total;
            $troco = max(0, $valor_pago - $total);

            $ins = $pdo->prepare("INSERT INTO pagamentos (ID_vendas, metodo, valor_pago, troco, ID_func_registro) VALUES (?,?,?,?,?)");
            $ins->execute([$id_venda, $metodo, number_format($valor_pago,2,'.',''), number_format($troco,2,'.',''), $_SESSION['ID_func']]);

            // opcional: pagamentos_itens (silenciosamente ignoramos erro se tabela não existir)
            $itens = $pdo->prepare("SELECT ID_produto, Quantidade, valor_total FROM itens_vendas WHERE ID_vendas=?");
            $itens->execute([$id_venda]);
            $itensArr = $itens->fetchAll();
            foreach ($itensArr as $i) {
                try {
                    $pdo->prepare("INSERT INTO pagamentos_itens (ID_vendas, ID_produto, Quantidade, valor_total) VALUES (?,?,?,?)")
                        ->execute([$id_venda, $i['ID_produto'], $i['Quantidade'], $i['valor_total']]);
                } catch (Exception $e) { /* ignore */ }
            }

            if (is_ajax()) send_json(['success'=>true,'msg'=>'Pagamento registrado','total'=>$total]);
            $msg = 'Pagamento registrado com sucesso! Total: R$ '.number_format($total,2,',','.');
        }

    } catch (Exception $e) {
        if (is_ajax()) send_json(['success'=>false,'msg'=>$e->getMessage()],400);
        $msg = 'Erro: '.$e->getMessage();
    }
}

// ===== Dados para a página =====
$idParam = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
  body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;color:#222;padding:20px}
  .box{max-width:980px;margin:0 auto;background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.08)}
  h1{margin-top:0}
  form{margin:0}
  select,input[type=number],input[type=text]{padding:8px;border-radius:6px;border:1px solid #ccc}
  .row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{padding:8px;border:1px solid #e6e6e6;text-align:left}
  .actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
  button{padding:8px 12px;border-radius:6px;border:none;cursor:pointer}
  .btn-primary{background:#0d6efd;color:#fff}
  .btn-success{background:#16a34a;color:#fff}
  .btn-danger{background:#e53935;color:#fff}
  .msg{padding:10px;border-radius:6px;margin-bottom:10px}
  .msg.ok{background:#e6ffed;color:#064}
  .msg.err{background:#ffe6e6;color:#700}
</style>
</head>
<body>
<div class="box">
  <h1>Comanda — Edição</h1>

  <?php if (!$comanda): ?>
    <p>Sem comanda selecionada.</p>
    <form method="post" style="display:inline-block">
      <input type="hidden" name="acao" value="nova">
      <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
      <button class="btn-primary" type="submit">➕ Criar nova comanda</button>
    </form>
    <?php if ($msg): ?>
      <div class="msg <?= strpos($msg,'Erro')===0 ? 'err' : 'ok' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
  <?php else: ?>
    <?php if ($msg): ?>
      <div class="msg <?= strpos($msg,'Erro')===0 ? 'err' : 'ok' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <p><strong>ID:</strong> <?= htmlspecialchars($comanda['ID_vendas']) ?> —
       <strong>Status:</strong> <?= htmlspecialchars($comanda['status']) ?> —
       <strong>Funcionário:</strong> <?= htmlspecialchars($comanda['Nome_func'] ?? '-') ?></p>

    <?php if ($comanda['status'] === 'ABERTA'): ?>
    <form id="addItemForm" method="post" style="margin-bottom:8px">
      <input type="hidden" name="acao" value="add_item">
      <input type="hidden" name="id_venda" value="<?= (int)$comanda['ID_vendas'] ?>">
      <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
      <div class="row">
        <select name="id_produto" required style="flex:1">
          <option value="">-- Selecionar produto --</option>
          <?php foreach($produtos as $p): ?>
            <option value="<?= $p['ID_produto'] ?>"><?= htmlspecialchars($p['Nome_prod']) ?> — R$ <?= number_format($p['Preco_unitario'],2,',','.') ?> (Est: <?= (int)$p['Qntd_produto'] ?>)</option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="quantidade" value="1" min="1" style="width:90px">
        <button class="btn-primary" type="submit">Adicionar</button>
      </div>
    </form>
    <?php endif; ?>

    <table>
      <thead><tr><th>Produto</th><th>Qtd</th><th>Valor</th></tr></thead>
      <tbody>
        <?php if (!empty($comanda['itens'])): foreach ($comanda['itens'] as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['Nome_prod'] ?? '-') ?></td>
            <td><?= (int)$it['Quantidade'] ?></td>
            <td>R$ <?= number_format($it['valor_total'],2,',','.') ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="3">Nenhum item nesta comanda.</td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot><tr><td colspan="2"><strong>TOTAL</strong></td><td><strong>R$ <?= number_format($comanda['total'],2,',','.') ?></strong></td></tr></tfoot>
    </table>

    <div class="actions">
      <?php if ($comanda['status'] === 'ABERTA'): ?>
        <form method="post" style="display:inline">
          <input type="hidden" name="acao" value="fechar">
          <input type="hidden" name="id_venda" value="<?= (int)$comanda['ID_vendas'] ?>">
          <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
          <button class="btn-success" type="submit" onclick="return confirm('Confirma fechar esta comanda?')">Fechar</button>
        </form>

        <form method="post" style="display:inline">
          <input type="hidden" name="acao" value="cancelar">
          <input type="hidden" name="id_venda" value="<?= (int)$comanda['ID_vendas'] ?>">
          <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
          <button class="btn-danger" type="submit" onclick="return confirm('Confirma cancelar esta comanda?')">Cancelar</button>
        </form>

        <form method="post" style="display:inline">
          <input type="hidden" name="acao" value="salvar">
          <input type="hidden" name="id_venda" value="<?= (int)$comanda['ID_vendas'] ?>">
          <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
          <button type="submit">Salvar</button>
        </form>

        <form method="post" style="display:inline" onsubmit="return confirm('Registrar pagamento?')">
          <input type="hidden" name="acao" value="pagar">
          <input type="hidden" name="id_venda" value="<?= (int)$comanda['ID_vendas'] ?>">
          <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
          <input type="hidden" name="metodo" value="DINHEIRO">
          <input type="hidden" name="valor_pago" value="<?= number_format($comanda['total'],2,'.','') ?>">
          <button class="btn-success" type="submit">Confirmar Pagamento</button>
        </form>
      <?php else: ?>
        <form method="post" style="display:inline">
          <input type="hidden" name="acao" value="reabrir">
          <input type="hidden" name="id_venda" value="<?= (int)$comanda['ID_vendas'] ?>">
          <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
          <button type="submit">Reabrir</button>
        </form>
      <?php endif; ?>

      <a href="?id=<?= (int)$comanda['ID_vendas'] ?>&print=1" target="_blank" style="text-decoration:none;padding:8px 12px;border-radius:6px;background:#64748b;color:#fff">Abrir impressão</a>
    </div>

  <?php endif; ?>
</div>

<script>
// JS minimal para captura AJAX do formulário de adicionar item
const addForm = document.getElementById('addItemForm');
if (addForm) {
  addForm.addEventListener('submit', async function(e){
    e.preventDefault();
    const data = new FormData(addForm);
    try {
      const res = await fetch(window.location.pathname + window.location.search, {
        method: 'POST',
        headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
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