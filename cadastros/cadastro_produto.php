<?php
session_start();
require_once '../conexao.php';


// Apenas admins podem cadastrar
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit;
}
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}
// Recebe os dados do POST
$nome         = $_POST['Nome_prod'] ?? null;
$preco        = $_POST['Preco_unitario'] ?? null;
$unidade      = $_POST['Unid_medida'] ?? null;
$quantidade   = $_POST['Qntd_produto'] ?? null;
$fornecedor   = $_POST['ID_forn'] ?? null;
$validade     = $_POST['Validade'] ?? null;
$categoria    = $_POST['id_categorias'] ?? null;

// Validações básicas
if (!$nome || strlen($nome) < 3) {
    exit(json_encode(['erro'=>'Nome inválido']));
}
if (!$preco || $preco < 0.10) {
    exit(json_encode(['erro'=>'Preço inválido']));
}
if (!$unidade) {
    exit(json_encode(['erro'=>'Unidade não selecionada']));
}
if (!$quantidade || $quantidade < 1) {
    exit(json_encode(['erro'=>'Quantidade inválida']));
}
if (!$fornecedor) {
    exit(json_encode(['erro'=>'Fornecedor não selecionado']));
}
if (!$validade) {
    exit(json_encode(['erro'=>'Validade não informada']));
}
// Converte validade para formato YYYY-MM-DD
$partes = explode('/', $validade);
if (count($partes) != 3) {
    exit(json_encode(['erro'=>'Formato de validade inválido']));
}
$validade_sql = "{$partes[2]}-{$partes[1]}-{$partes[0]}";

// Inserção no banco
try {
    $sql = "INSERT INTO produtos (ID_forn, Nome_prod, Preco_unitario, Unid_medida, Qntd_produto, Validade, id_categorias)
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
    echo json_encode(['sucesso' => 'Produto cadastrado com sucesso']);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao cadastrar: '.$e->getMessage()]);
}
