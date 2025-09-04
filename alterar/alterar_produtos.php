<?php
session_start(); // Inicia a sessão para verificar o login e nível de acesso
require_once '../conexao.php'; // Inclui a conexão com o banco de dados

// Verifica se o usuário é administrador (nível 1)
if ($_SESSION['nivel'] != 1) {
  echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../produtos.php';</script>";
  exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
  echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
  exit;
}

// Obtém o ID do produto via GET ou POST, validando como inteiro
$id = filter_input(INPUT_GET, 'ID_produto', FILTER_VALIDATE_INT);
if (!$id) {
    $id = filter_input(INPUT_POST, 'ID_produto', FILTER_VALIDATE_INT);
}

if (!$id) {
    echo "ID do produto inválido.";
    exit;
}

// Busca o produto no banco pelo ID
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE ID_produto = :id");
$stmt->execute([':id' => $id]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou, exibe mensagem
if (!$prod) {
    echo "Produto não encontrado.";
    exit;
}

// Função para formatar datas do banco (YYYY-MM-DD) para DD/MM/YYYY
function formatarData($data_sql) {
    if (!$data_sql) return '';
    $data_sql = trim($data_sql);
    if ($data_sql === '0000-00-00' || $data_sql === '0000-00-00 00:00:00') return '';

    $ts = strtotime($data_sql);
    if ($ts !== false) return date('d/m/Y', $ts);

    return '';
}

// Formata a validade do produto para exibir no formulário
$validade_formatada = formatarData($prod['Validade'] ?? null);

// Pega a unidade de medida já existente
$unid_medida = trim($prod['Unid_medida'] ?? '');

// Carrega todos os fornecedores e categorias para os selects
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
/* Reset e estilo básico */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
  background: rgb(59, 75, 93);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
header {
  background: rgb(27, 68, 95);
  padding: 15px 20px;
  color: white;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.15);
}
header .back-btn {
  background: transparent;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 24px;
}
header h1 {
  flex: 1;
  font-weight: 700;
  font-size: 1.5rem;
  user-select: none;
}

main {
  flex: 1;
  display: flex;
  justify-content: center;
  padding: 25px 15px;
}

/* Formulário */
form {
  background: #fff;
  padding: 30px 35px;
  border-radius: 15px;
  box-shadow: 0 12px 25px rgba(0,0,0,0.12);
  max-width: 600px;
  width: 100%;
}
form h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #2c3e50;
  font-size: 1.8rem;
}

label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #34495e;
}

input, select, textarea {
  width: 100%;
  padding: 12px 15px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

input:focus, select:focus, textarea:focus {
  border-color: rgb(27, 68, 95);
  box-shadow: 0 0 8px rgba(52,152,219,0.3);
  outline: none;
}

/* Botão */
button[type="submit"] {
  width: 100%;
  padding: 14px;
  background: rgb(27, 68, 95);
  border: none;
  color: white;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.3s;
}
button[type="submit"]:hover {
  background: rgb(0, 153, 255);
}

/* Grupos flexíveis */
.flex-group {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}
.flex-group > div {
  flex: 1;
}

/* Mensagens de erro */
.erro {
  color: #e74c3c;
  font-size: 0.85rem;
  margin-top: -10px;
  margin-bottom: 10px;
  display: block;
}

/* Responsividade */
@media(max-width: 600px) {
  .flex-group {
    flex-direction: column;
  }
}
</style>
</head>
<body>

<header>
  <!-- Botão de voltar -->
  <button class="back-btn" onclick="window.location.href='../produtos.php'" title="Voltar">
    <span class="material-icons">arrow_back</span>
  </button>
  <h1>Editar Produto</h1>
</header>

<main>
  <!-- Formulário de edição -->
  <form method="POST" action="alterar_produtos.php" novalidate>
      <h2>Editar Produto</h2>
      <!-- ID oculto para saber qual produto atualizar -->
      <input type="hidden" name="ID_produto" value="<?= htmlspecialchars($prod['ID_produto'], ENT_QUOTES) ?>" />

      <!-- Select de fornecedores -->
      <label for="ID_forn">Fornecedor:</label>
      <select name="ID_forn" id="ID_forn" required>
          <option value="">-- Nenhum --</option>
          <?php foreach ($fornecedores as $forn): ?>
              <option value="<?= $forn['ID_forn'] ?>" <?= ($prod['ID_forn'] == $forn['ID_forn']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($forn['Nome_forn'], ENT_QUOTES) ?>
              </option>
          <?php endforeach; ?>
      </select>

      <!-- Select de categorias -->
      <label for="id_categorias">Categoria:</label>
      <select name="id_categorias" id="id_categorias" required>
          <option value="">Selecione</option>
          <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id_categorias'] ?>" <?= ($prod['id_categorias'] == $cat['id_categorias']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nome_categoria'], ENT_QUOTES) ?>
              </option>
          <?php endforeach; ?>
      </select>

      <!-- Nome do produto -->
      <label for="Nome_prod">Nome do Produto:</label>
      <input type="text" name="Nome_prod" id="Nome_prod" maxlength="60" required value="<?= htmlspecialchars($prod['Nome_prod'], ENT_QUOTES) ?>" />

      <!-- Preço unitário -->
      <label for="Preco_unitario">Preço Unitário (R$):</label>
      <input type="number" step="0.01" min="0" name="Preco_unitario" id="Preco_unitario" value="<?= htmlspecialchars($prod['Preco_unitario'], ENT_QUOTES) ?>" />

      <!-- Unidade de medida -->
      <label for="Unid_medida">Unidade de medida:</label>
      <select name="Unid_medida" id="Unid_medida" required>
        <option value="">Selecione</option>
        <option value="kg" <?= (strtolower($unid_medida) === 'kg') ? 'selected' : '' ?>>kg</option>
        <option value="mL" <?= (strtolower($unid_medida) === 'ml') ? 'selected' : '' ?>>mL</option>
        <option value="g" <?= (strtolower($unid_medida) === 'g') ? 'selected' : '' ?>>g</option>
      </select>

      <!-- Validade -->
      <label for="Validade">Validade (dd/mm/aaaa):</label>
      <input type="text" name="Validade" id="Validade" maxlength="10" placeholder="dd/mm/aaaa" value="<?= htmlspecialchars($validade_formatada, ENT_QUOTES) ?>" />

      <!-- Quantidade -->
      <label for="Qntd_produto">Quantidade em Estoque:</label>
      <input type="number" name="Qntd_produto" id="Qntd_produto" min="0" value="<?= htmlspecialchars($prod['Qntd_produto'], ENT_QUOTES) ?>" />

      <button type="submit">Alterar Produto</button>
  </form>
</main>

<!-- Script para máscara de data -->
<script>
document.addEventListener("DOMContentLoaded", function(){
  const validade = document.getElementById("Validade");
  validade.addEventListener("input", () => {
    let v = validade.value.replace(/\D/g,"").slice(0,8); // Remove tudo que não é número
    if(v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
    if(v.length > 5) v = v.slice(0,5) + '/' + v.slice(5);
    validade.value = v;
  });
});
</script> 

</body>
</html>
