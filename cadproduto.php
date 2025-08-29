<?php
session_start();
require_once 'conexao.php';
error_reporting(E_ALL);

// Verifica permissão
if ($_SESSION['nivel'] != 1) {
    echo "Acesso negado!";
    exit;
}

// Função para limpar dados
function limpar($dado) {
    return trim(htmlspecialchars($dado));
}

// Busca fornecedores e categorias para popular selects
$fornecedores = $pdo->query("SELECT ID_forn, Nome_forn FROM fornecedores ORDER BY Nome_forn")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT id_categorias, nome_categoria FROM categorias ORDER BY nome_categoria")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_forn       = limpar($_POST['ID_forn'] ?? null);
    $id_categorias = limpar($_POST['id_categorias'] ?? null);
    $nome_prod     = limpar($_POST['Nome_prod'] ?? null);
    $preco_unitario= limpar($_POST['Preco_unitario'] ?? null);
    $unid_medida   = limpar($_POST['Unid_medida'] ?? null);
    $validade      = limpar($_POST['Validade'] ?? null);
    $qntd_produto  = limpar($_POST['Qntd_produto'] ?? null);

    // Validações básicas
    if (!$id_categorias) {
        echo "<script>alert('Selecione uma categoria.');</script>";
    } elseif (!$nome_prod) {
        echo "<script>alert('Informe o nome do produto.');</script>";
    } elseif ($preco_unitario !== null && (!is_numeric($preco_unitario) || $preco_unitario < 0)) {
        echo "<script>alert('Preço inválido.');</script>";
    } elseif ($qntd_produto !== null && (!is_numeric($qntd_produto) || $qntd_produto < 0)) {
        echo "<script>alert('Quantidade inválida.');</script>";
    } else {
        $sql = "INSERT INTO produtos
            (ID_forn, id_categorias, Nome_prod, Preco_unitario, Unid_medida, Validade, Qntd_produto)
            VALUES
            (:ID_forn, :id_categorias, :Nome_prod, :Preco_unitario, :Unid_medida, :Validade, :Qntd_produto)";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':ID_forn', $id_forn);
        $stmt->bindParam(':id_categorias', $id_categorias);
        $stmt->bindParam(':Nome_prod', $nome_prod);
        $stmt->bindParam(':Preco_unitario', $preco_unitario);
        $stmt->bindParam(':Unid_medida', $unid_medida);
        $stmt->bindParam(':Validade', $validade);
        $stmt->bindParam(':Qntd_produto', $qntd_produto);

        if ($stmt->execute()) {
            echo "<script>alert('Produto cadastrado com sucesso!');window.location.href='produtos.php'</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar produto.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro de Produto</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
  /* Reset */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  body {
    background: #eef2f7;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* Header */
  header {
    background:rgb(27, 68, 95);
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

  /* Container principal */
  main {
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 25px 15px;
  }

  /* Formulário estilo card */
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
    resize: vertical;
  }
  input:focus, select:focus, textarea:focus {
    border-color:rgb(27, 68, 95);
    box-shadow: 0 0 8px rgba(52,152,219,0.3);
    outline: none;
  }
  button[type="submit"] {
    width: 100%;
    padding: 14px;
    background:rgb(27, 68, 95);
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

  /* Flex container para inputs lado a lado */
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

  @media(max-width: 600px) {
    .flex-group {
      flex-direction: column;
    }
  }
</style>
</head>
<body>

<header>
  <button class="back-btn" onclick="window.location.href='produtos.php'" title="Voltar">
    <span class="material-icons">arrow_back</span>
  </button>
  <h1>Cadastro de Produto</h1>
</header>

<main>
  <form method="POST" novalidate>
    <h2>Cadastro de Produto</h2>

    <label for="ID_forn">Fornecedor:</label>
    <select name="ID_forn" id="ID_forn">
      <option value="">-- Nenhum --</option>
      <?php foreach ($fornecedores as $forn): ?>
        <option value="<?= $forn['ID_forn'] ?>"><?= htmlspecialchars($forn['Nome_forn']) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="id_categorias">Categoria:</label>
    <select name="id_categorias" id="id_categorias" required>
      <option value="">Selecione</option>
      <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id_categorias'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="Nome_prod">Nome do Produto:</label>
    <input type="text" name="Nome_prod" id="Nome_prod" maxlength="60" required />

    <label for="Preco_unitario">Preço Unitário (R$):</label>
    <input type="number" step="0.01" min="0" name="Preco_unitario" id="Preco_unitario" />

    <label for="Unid_medida">Unidade de Medida (ex: kg, un):</label>
    <input type="text" name="Unid_medida" id="Unid_medida" maxlength="2" />

    <label for="Validade">Validade (dd/mm/aaaa):</label>
    <input type="text" name="Validade" id="Validade" maxlength="15" placeholder="dd/mm/aaaa" />

    <label for="Qntd_produto">Quantidade em Estoque:</label>
    <input type="number" name="Qntd_produto" id="Qntd_produto" min="0" />

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const validade = document.getElementById("Validade");

  // Máscara simples para validade no formato dd/mm/aaaa
  validade.addEventListener("input", () => {
    let v = validade.value.replace(/\D/g,"").slice(0,8);
    if(v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
    if(v.length > 5) v = v.slice(0,5) + '/' + v.slice(5);
    validade.value = v;
  });

  // Validação simples no submit
  document.querySelector("form").addEventListener("submit",(e)=>{
    const preco = document.getElementById("Preco_unitario");
    const qntd = document.getElementById("Qntd_produto");

    if(preco.value && preco.value < 0){
      alert("O preço deve ser maior ou igual a zero.");
      e.preventDefault();
    }
    if(qntd.value && qntd.value < 0){
      alert("Quantidade não pode ser negativa.");
      e.preventDefault();
    }
  });
});
</script>

</body>
</html>
