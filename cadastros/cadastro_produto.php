<?php
session_start(); 
require_once '../conexao.php'; // Conexão com o banco de dados

// 🔒 SEGURANÇA: Apenas usuários logados e administradores podem cadastrar produtos

// 1️⃣ Verifica se o usuário está logado
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
    echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
    exit; // Interrompe o script
}

// 2️⃣ Verifica se o usuário é administrador (nível 1)
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='../produtos.php';</script>";
    exit; // Interrompe o script
}

// 📥 RECEBE OS DADOS DO FORMULÁRIO (POST)
$nome         = $_POST['Nome_prod'] ?? null;
$preco        = $_POST['Preco_unitario'] ?? null;
$unidade      = $_POST['Unid_medida'] ?? null;
$quantidade   = $_POST['Qntd_produto'] ?? null;
$fornecedor   = $_POST['ID_forn'] ?? null;
$validade     = $_POST['Validade'] ?? null;
$categoria    = $_POST['id_categorias'] ?? null;

// ✅ VALIDAÇÕES BÁSICAS
// Verifica se os dados enviados fazem sentido
if (!$nome || strlen($nome) < 3) { // Nome precisa ter pelo menos 3 caracteres
    exit(json_encode(['erro'=>'Nome inválido']));
}
if (!$preco || $preco < 0.10) { // Preço mínimo
    exit(json_encode(['erro'=>'Preço inválido']));
}
if (!$unidade) { // Unidade de medida precisa ser escolhida
    exit(json_encode(['erro'=>'Unidade não selecionada']));
}
if (!$quantidade || $quantidade < 1) { // Quantidade mínima
    exit(json_encode(['erro'=>'Quantidade inválida']));
}
if (!$fornecedor) { // Fornecedor precisa estar selecionado
    exit(json_encode(['erro'=>'Fornecedor não selecionado']));
}
if (!$validade) { // Validade precisa estar preenchida
    exit(json_encode(['erro'=>'Validade não informada']));
}

// 🗓️ Converte a validade de formato "dd/mm/yyyy" para "yyyy-mm-dd" (formato do MySQL)
$partes = explode('/', $validade);
if (count($partes) != 3) { // Se não tiver 3 partes, é inválido
    exit(json_encode(['erro'=>'Formato de validade inválido']));
}
$validade_sql = "{$partes[2]}-{$partes[1]}-{$partes[0]}";

// 💾 INSERÇÃO NO BANCO
try {
    // Prepara a query para cadastrar o produto
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

    // Se tudo deu certo, retorna mensagem de sucesso em JSON
    echo json_encode(['sucesso' => 'Produto cadastrado com sucesso']);
} catch (PDOException $e) {
    // Se houver erro no banco, retorna mensagem de erro em JSON
    echo json_encode(['erro' => 'Erro ao cadastrar: '.$e->getMessage()]);
}
