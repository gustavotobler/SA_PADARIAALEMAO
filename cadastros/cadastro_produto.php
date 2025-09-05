<?php
session_start(); 
require_once '../conexao.php'; // Conexão com o banco de dados

// 🔒 SEGURANÇA: Apenas usuários logados e administradores podem cadastrar produtos
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit;
}
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../produtos.php';</script>";
    exit;
}

// 📥 RECEBE OS DADOS DO FORMULÁRIO (POST)
$nome       = $_POST['Nome_prod'] ?? null;
$preco      = $_POST['Preco_unitario'] ?? null;
$unidade    = $_POST['Unid_medida'] ?? null;
$quantidade = $_POST['Qntd_produto'] ?? null;
$fornecedor = $_POST['ID_forn'] ?? null;
$validade   = $_POST['Validade'] ?? null;
$categoria  = $_POST['id_categorias'] ?? null;

// 🔧 Corrige o preço (ex: "R$ 1.234,56" → 1234.56)
if ($preco) {
    $preco = str_replace(['R$', ' '], '', $preco);
    $preco = str_replace('.', '', $preco);
    $preco = str_replace(',', '.', $preco);
    $preco = (float) $preco;
}

// ✅ VALIDAÇÕES BÁSICAS
if (!$nome || strlen($nome) < 3) {
    exit("<script>alert('Nome inválido');window.history.back();</script>");
}
if (!$preco || $preco < 0.10) {
    exit("<script>alert('Preço inválido');window.history.back();</script>");
}
if (!$unidade) {
    exit("<script>alert('Unidade de medida não selecionada');window.history.back();</script>");
}
if (!$quantidade || $quantidade < 1) {
    exit("<script>alert('Quantidade inválida');window.history.back();</script>");
}
if (!$fornecedor) {
    exit("<script>alert('Fornecedor não selecionado');window.history.back();</script>");
}
if (!$categoria) {
    exit("<script>alert('Categoria não selecionada');window.history.back();</script>");
}
if (!$validade) {
    exit("<script>alert('Validade não informada');window.history.back();</script>");
}

// 🗓️ Converte validade (dd/mm/yyyy → yyyy-mm-dd)
$partes = explode('/', $validade);
if (count($partes) != 3) {
    exit("<script>alert('Formato de validade inválido');window.history.back();</script>");
}
$validade_sql = "{$partes[2]}-{$partes[1]}-{$partes[0]}";

// 🔍 Valida se fornecedor existe
$stmtF = $pdo->prepare("SELECT COUNT(*) FROM fornecedores WHERE ID_forn = ?");
$stmtF->execute([$fornecedor]);
if ($stmtF->fetchColumn() == 0) {
    exit("<script>alert('Fornecedor inválido');window.history.back();</script>");
}

// 🔍 Valida se categoria existe
$stmtC = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE id_categorias = ?");
$stmtC->execute([$categoria]);
if ($stmtC->fetchColumn() == 0) {
    exit("<script>alert('Categoria inválida');window.history.back();</script>");
}

// 💾 INSERÇÃO NO BANCO
try {
    $sql = "INSERT INTO produtos 
        (ID_forn, Nome_prod, Preco_unitario, Unid_medida, Qntd_produto, Validade, id_categorias)
        VALUES (:fornecedor, :nome, :preco, :unidade, :quantidade, :validade, :categoria)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fornecedor' => $fornecedor,
        ':nome'       => $nome,
        ':preco'      => $preco,
        ':unidade'    => $unidade,
        ':quantidade' => $quantidade,
        ':validade'   => $validade_sql,
        ':categoria'  => $categoria
    ]);

    echo "<script>alert('Produto cadastrado com sucesso!');window.location.href='../produtos.php'</script>";
} catch (PDOException $e) {
    echo "<script>alert('Erro ao cadastrar: ".$e->getMessage()."');window.history.back();</script>";
}
