<?php
session_start();
require_once '../conexao.php'; // manter ../ se o arquivo estiver em alterar/

// Verifica se o usuário está logado
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='../inicial1.php';</script>";
    exit;
}

// Verifica se o usuário é administrador (nível 1)
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../estoque.php';</script>";
    exit;
}

// Função utilitária: converte dd/mm/aaaa -> YYYY-MM-DD, retorna null se vazio
function brDateToSql($datebr) {
    $datebr = trim((string)$datebr);
    if ($datebr === '' || $datebr === '0000-00-00') return null;
    // aceita dd/mm/yyyy ou dd-mm-yyyy
    $datebr = str_replace('-', '/', $datebr);
    $parts = explode('/', $datebr);
    if (count($parts) !== 3) return null;
    [$d, $m, $y] = $parts;
    if (!checkdate((int)$m, (int)$d, (int)$y)) return null;
    return sprintf('%04d-%02d-%02d', (int)$y, (int)$m, (int)$d);
}

// Pega ID via GET (abrir a página) ou POST (quando enviar)
// Aceita tanto 'id' quanto 'ID_produto' para compatibilidade com links antigos
$id = null;
$g1 = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$g2 = filter_input(INPUT_GET, 'ID_produto', FILTER_VALIDATE_INT);
$p1 = filter_input(INPUT_POST, 'ID_produto', FILTER_VALIDATE_INT);
$p2 = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($g1 !== null && $g1 !== false) $id = $g1;
elseif ($g2 !== null && $g2 !== false) $id = $g2;
elseif ($p1 !== null && $p1 !== false) $id = $p1;
elseif ($p2 !== null && $p2 !== false) $id = $p2;

if (!$id) {
    echo "<script>alert('ID do produto inválido.');window.location.href='../estoque.php';</script>";
    exit;
}

// Se for POST, processa atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e valida campos
    $ID_forn = filter_input(INPUT_POST, 'ID_forn', FILTER_VALIDATE_INT);
    $id_categorias = filter_input(INPUT_POST, 'id_categorias', FILTER_VALIDATE_INT);
    $Nome_prod = trim((string)filter_input(INPUT_POST, 'Nome_prod', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $Preco_unitario_raw = str_replace(',', '.', trim((string)filter_input(INPUT_POST, 'Preco_unitario', FILTER_UNSAFE_RAW)));
    $Preco_unitario = ($Preco_unitario_raw === '') ? null : (is_numeric($Preco_unitario_raw) ? (float)$Preco_unitario_raw : false);
    $Unid_medida = trim((string)filter_input(INPUT_POST, 'Unid_medida', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $Validade_raw = trim((string)filter_input(INPUT_POST, 'Validade', FILTER_UNSAFE_RAW));
    $Validade_sql = brDateToSql($Validade_raw); // null ou yyyy-mm-dd
    $Qntd_produto_raw = trim((string)filter_input(INPUT_POST, 'Qntd_produto', FILTER_UNSAFE_RAW));
    // aceita ponto ou vírgula e numeros decimais
    $Qntd_produto_raw = str_replace(',', '.', $Qntd_produto_raw);
    $Qntd_produto = ($Qntd_produto_raw === '') ? 0 : (is_numeric($Qntd_produto_raw) ? (float)$Qntd_produto_raw : false);

    // Validações básicas
    if ($Nome_prod === '') {
        echo "<script>alert('O nome do produto não pode ficar vazio.');history.back();</script>";
        exit;
    }
    if ($Preco_unitario === false) {
        echo "<script>alert('Preço unitário inválido. Use números (ex: 5.50 ou 5,50).');history.back();</script>";
        exit;
    }
    if ($Qntd_produto === false) {
        echo "<script>alert('Quantidade inválida.');history.back();</script>";
        exit;
    }

    try {
        // Monta update com parâmetros (inclui somente colunas esperadas)
        $sql = "UPDATE produtos SET 
                    ID_forn = :ID_forn,
                    id_categorias = :id_categorias,
                    Nome_prod = :Nome_prod,
                    Preco_unitario = :Preco_unitario,
                    Unid_medida = :Unid_medida,
                    Validade = :Validade,
                    Qntd_produto = :Qntd_produto
                WHERE ID_produto = :ID_produto
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        // Binds com tratamento de NULL corretamente
        if ($ID_forn !== null && $ID_forn !== false) {
            $stmt->bindValue(':ID_forn', (int)$ID_forn, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':ID_forn', null, PDO::PARAM_NULL);
        }

        if ($id_categorias !== null && $id_categorias !== false) {
            $stmt->bindValue(':id_categorias', (int)$id_categorias, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':id_categorias', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':Nome_prod', $Nome_prod, PDO::PARAM_STR);

        if ($Preco_unitario === null) {
            $stmt->bindValue(':Preco_unitario', null, PDO::PARAM_NULL);
        } else {
            // armazena como string com ponto decimal (formato SQL)
            $stmt->bindValue(':Preco_unitario', number_format((float)$Preco_unitario, 2, '.', ''), PDO::PARAM_STR);
        }

        $stmt->bindValue(':Unid_medida', ($Unid_medida === '' ? null : $Unid_medida), PDO::PARAM_STR);

        if ($Validade_sql === null) {
            $stmt->bindValue(':Validade', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':Validade', $Validade_sql, PDO::PARAM_STR);
        }

        $stmt->bindValue(':Qntd_produto', (int)$Qntd_produto, PDO::PARAM_INT);
        $stmt->bindValue(':ID_produto', (int)$id, PDO::PARAM_INT);

        $ok = $stmt->execute();

        if ($ok) {
            echo "<script>alert('Produto alterado com sucesso.');window.location.href='../estoque.php';</script>";
            exit;
        } else {
            $err = $stmt->errorInfo();
            echo "<script>alert('Falha ao alterar produto: " . addslashes($err[2] ?? 'erro desconhecido') . "');history.back();</script>";
            exit;
        }
    } catch (Exception $e) {
        echo "<script>alert('Erro ao alterar produto: " . addslashes($e->getMessage()) . "');history.back();</script>";
        exit;
    }
}

// Se não foi POST, busca o produto e mostra o formulário
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE ID_produto = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prod) {
    echo "<script>alert('Produto não encontrado.');window.location.href='../estoque.php';</script>";
    exit;
}

// Formata data para dd/mm/yyyy
function formatarData($data_sql) {
    if (!$data_sql) return '';
    $data_sql = trim($data_sql);
    if ($data_sql === '0000-00-00' || $data_sql === '0000-00-00 00:00:00') return '';
    $ts = strtotime($data_sql);
    if ($ts !== false) return date('d/m/Y', $ts);
    return '';
}
$validade_formatada = formatarData($prod['Validade'] ?? null);
$unid_medida = trim($prod['Unid_medida'] ?? '');

// Carrega fornecedores e categorias
$fornecedores = $pdo->query("SELECT ID_forn, Nome_forn FROM fornecedores ORDER BY Nome_forn")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT id_categorias, nome_categoria FROM categorias ORDER BY nome_categoria")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Editar Produto</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
/* (mantive seu CSS original — pode colar o seu CSS aqui) */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: rgb(59, 75, 93); min-height: 100vh; display: flex; flex-direction: column; }
header { background: rgb(27, 68, 95); padding: 15px 20px; color: white; display: flex; align-items: center; gap: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.15); }
header .back-btn { background: transparent; border: none; color: white; cursor: pointer; font-size: 24px; }
header h1 { flex: 1; font-weight: 700; font-size: 1.5rem; user-select: none; }
main { flex: 1; display: flex; justify-content: center; padding: 25px 15px; }
form { background: #fff; padding: 30px 35px; border-radius: 15px; box-shadow: 0 12px 25px rgba(0,0,0,0.12); max-width: 600px; width: 100%; }
form h2 { text-align: center; margin-bottom: 30px; color: #2c3e50; font-size: 1.8rem; }
label { display: block; margin-bottom: 6px; font-weight: 600; color: #34495e; }
input, select, textarea { width: 100%; padding: 12px 15px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s ease; }
input:focus, select:focus, textarea:focus { border-color: rgb(27, 68, 95); box-shadow: 0 0 8px rgba(52,152,219,0.3); outline: none; }
button[type="submit"] { width: 100%; padding: 14px; background: rgb(27, 68, 95); border: none; color: white; font-size: 1rem; font-weight: 600; border-radius: 10px; cursor: pointer; transition: background-color 0.3s; }
button[type="submit"]:hover { background: rgb(0, 153, 255); }
.flex-group { display: flex; gap: 10px; margin-bottom: 15px; }
.flex-group > div { flex: 1; }
.erro { color: #e74c3c; font-size: 0.85rem; margin-top: -10px; margin-bottom: 10px; display: block; }
@media(max-width: 600px) { .flex-group { flex-direction: column; } }
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='../estoque.php'" title="Voltar">
    <span class="material-icons">arrow_back</span>
  </button>
  <h1>Editar Produto</h1>
</header>

<main>
  <form method="POST" action="" novalidate>
      <h2>Editar Produto</h2>
      <input type="hidden" name="ID_produto" value="<?= htmlspecialchars($prod['ID_produto'], ENT_QUOTES) ?>" />

      <label for="ID_forn">Fornecedor:</label>
      <select name="ID_forn" id="ID_forn" required>
          <option value="">-- Nenhum --</option>
          <?php foreach ($fornecedores as $forn): ?>
              <option value="<?= $forn['ID_forn'] ?>" <?= ($prod['ID_forn'] == $forn['ID_forn']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($forn['Nome_forn'], ENT_QUOTES) ?>
              </option>
          <?php endforeach; ?>
      </select>

      <label for="id_categorias">Categoria:</label>
      <select name="id_categorias" id="id_categorias" required>
          <option value="">Selecione</option>
          <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id_categorias'] ?>" <?= ($prod['id_categorias'] == $cat['id_categorias']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nome_categoria'], ENT_QUOTES) ?>
              </option>
          <?php endforeach; ?>
      </select>

      <label for="Nome_prod">Nome do Produto:</label>
      <input type="text" name="Nome_prod" id="Nome_prod" maxlength="60" required value="<?= htmlspecialchars($prod['Nome_prod'], ENT_QUOTES) ?>" />

      <label for="Preco_unitario">Preço Unitário (R$):</label>
      <input type="text" name="Preco_unitario" id="Preco_unitario" value="<?= htmlspecialchars($prod['Preco_unitario'], ENT_QUOTES) ?>" />

      <label for="Unid_medida">Unidade de medida:</label>
      <select name="Unid_medida" id="Unid_medida" required>
        <option value="">Selecione</option>
        <option value="kg" <?= (strtolower($unid_medida) === 'kg') ? 'selected' : '' ?>>kg</option>
        <option value="ml" <?= (strtolower($unid_medida) === 'ml') ? 'selected' : '' ?>>mL</option>
        <option value="g" <?= (strtolower($unid_medida) === 'g') ? 'selected' : '' ?>>g</option>
      </select>

      <label for="Validade">Validade (dd/mm/aaaa):</label>
      <input type="text" name="Validade" id="Validade" maxlength="10" placeholder="dd/mm/aaaa" value="<?= htmlspecialchars($validade_formatada, ENT_QUOTES) ?>" />

      <label for="Qntd_produto">Quantidade em Estoque:</label>
      <input type="number" name="Qntd_produto" id="Qntd_produto" min="0" step="0.01" value="<?= htmlspecialchars($prod['Qntd_produto'], ENT_QUOTES) ?>" />

      <button type="submit">Alterar Produto</button>
  </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const validade = document.getElementById("Validade");
  validade.addEventListener("input", () => {
    let v = validade.value.replace(/\D/g,"").slice(0,8);
    if(v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
    if(v.length > 5) v = v.slice(0,5) + '/' + v.slice(5);
    validade.value = v;
  });
});
</script>

</body>
</html>
