<?php
session_start();
require_once 'conexao.php';
error_reporting(E_ALL);

// Verifica permissão
if ($_SESSION['nivel'] != 1) {
    echo "Acesso negado!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Pegando com segurança
  $nome_produto = $_POST['Nome_produto'] ?? null;
  $codigo       = $_POST['Codigo'] ?? null;
  $descricao    = $_POST['Descricao'] ?? null;
  $categoria    = $_POST['Categoria'] ?? null;
  $preco       = $_POST['Preco'] ?? null;
  $quantidade  = $_POST['Quantidade'] ?? null;
  $fornecedor  = $_POST['Fornecedor'] ?? null;

  function formatarDataBanco($data){
      if(!$data) return null;
      $partes = explode("/", $data);
      if(count($partes) == 3){
          return $partes[2]."-".$partes[1]."-".$partes[0];
      }
      return null;
  }
  $data_validade = formatarDataBanco($_POST['Data_validade'] ?? null);

  $sql = "INSERT INTO produto 
  (Nome_produto, Codigo, Descricao, Categoria, Preco, Quantidade, Fornecedor, Data_validade)
  VALUES 
  (:Nome_produto, :Codigo, :Descricao, :Categoria, :Preco, :Quantidade, :Fornecedor, :Data_validade)";

  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(":Nome_produto", $nome_produto);
  $stmt->bindParam(":Codigo", $codigo);
  $stmt->bindParam(":Descricao", $descricao);
  $stmt->bindParam(":Categoria", $categoria);
  $stmt->bindParam(":Preco", $preco);
  $stmt->bindParam(":Quantidade", $quantidade);
  $stmt->bindParam(":Fornecedor", $fornecedor);
  $stmt->bindParam(":Data_validade", $data_validade);

  if($stmt->execute()){
      echo "<script>alert('Produto cadastrado com sucesso!');window.location.href='produtos.php'</script>";
  } else{
      echo "<script>alert('Erro ao cadastrar produto');</script>";
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

    <label for="Nome_produto">Nome do Produto:</label>
    <input type="text" name="Nome_produto" id="Nome_produto" required />

    <label for="Codigo">Código:</label>
    <input type="text" name="Codigo" id="Codigo" required />

    <label for="Descricao">Descrição:</label>
    <textarea name="Descricao" id="Descricao" rows="3"></textarea>

    <label for="Categoria">Categoria:</label>
    <select name="Categoria" id="Categoria" required>
      <option value="">Selecione</option>
      <option>Alimentos</option>
      <option>Bebidas</option>
      <option>Higiene</option>
      <option>Limpeza</option>
      <option>Outros</option>
    </select>

    <div class="flex-group">
      <div>
        <label for="Preco">Preço (R$):</label>
        <input type="number" step="0.01" name="Preco" id="Preco" required />
      </div>
      <div>
        <label for="Quantidade">Quantidade em Estoque:</label>
        <input type="number" name="Quantidade" id="Quantidade" required />
      </div>
    </div>

    <label for="Fornecedor">Fornecedor:</label>
    <input type="text" name="Fornecedor" id="Fornecedor" />

    <label for="Data_validade">Data de Validade:</label>
    <input type="text" name="Data_validade" id="Data_validade" placeholder="dd/mm/aaaa" />

    <button type="submit">Cadastrar</button>
  </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const dataValidade = document.getElementById("Data_validade");

  // Máscara datas dd/mm/aaaa
  function mascaraData(el){
    el.addEventListener("input", () => {
      let v = el.value.replace(/\D/g,"").slice(0,8);
      if(v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
      if(v.length > 5) v = v.slice(0,5) + '/' + v.slice(5);
      el.value = v;
    });
  }
  mascaraData(dataValidade);

  // Validação simples antes de enviar
  document.querySelector("form").addEventListener("submit",(e)=>{
    let ok = true;

    const preco = document.getElementById("Preco");
    const quantidade = document.getElementById("Quantidade");

    if(preco.value <= 0){
      alert("O preço deve ser maior que zero.");
      ok = false;
    }

    if(quantidade.value < 0){
      alert("Quantidade não pode ser negativa.");
      ok = false;
    }

    if(!ok) e.preventDefault();
  });
});
</script>

</body>
</html>
