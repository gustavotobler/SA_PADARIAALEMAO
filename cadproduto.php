<?php
session_start();
require_once("conexao.php");

// Busca fornecedores e categorias para popular selects
$fornecedores = $pdo->query("SELECT ID_forn, Nome_forn FROM fornecedores ORDER BY Nome_forn")->fetchAll(PDO::FETCH_ASSOC);
$categorias   = $pdo->query("SELECT id_categorias, nome_categoria FROM categorias ORDER BY nome_categoria")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_forn       = (int)($_POST["ID_forn"] ?? 0);
    $id_categorias = (int)($_POST["id_categorias"] ?? 0);
    $nome_prod     = trim($_POST["Nome_prod"] ?? "");
    $preco_unitario= trim($_POST["Preco_unitario"] ?? "");
    $unid_medida   = trim($_POST["Unid_medida"] ?? "");
    $validade      = trim($_POST["Validade"] ?? "");
    $qntd_produto  = (int)($_POST["Qntd_produto"] ?? 0);

    // Validação simples
    if (!$id_categorias) {
        echo "<script>alert('Selecione uma categoria.');</script>";
    } elseif (!$nome_prod) {
        echo "<script>alert('Informe o nome do produto.');</script>";
    } elseif ($preco_unitario !== "" && (!is_numeric($preco_unitario) || $preco_unitario < 0)) {
        echo "<script>alert('Preço inválido.');</script>";
    } elseif ($qntd_produto < 0) {
        echo "<script>alert('Quantidade inválida.');</script>";
    } else {
        $sql = "INSERT INTO produtos 
                (ID_forn, id_categorias, Nome_prod, Preco_unitario, Unid_medida, Validade, Qntd_produto) 
                VALUES 
                (:ID_forn, :id_categorias, :Nome_prod, :Preco_unitario, :Unid_medida, :Validade, :Qntd_produto)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':ID_forn', $id_forn, PDO::PARAM_INT);
        $stmt->bindParam(':id_categorias', $id_categorias, PDO::PARAM_INT);
        $stmt->bindParam(':Nome_prod', $nome_prod, PDO::PARAM_STR);
        $stmt->bindParam(':Preco_unitario', $preco_unitario);
        $stmt->bindParam(':Unid_medida', $unid_medida, PDO::PARAM_STR);
        $stmt->bindParam(':Validade', $validade, PDO::PARAM_STR);
        $stmt->bindParam(':Qntd_produto', $qntd_produto, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<script>alert('Produto cadastrado com sucesso!');window.location.href='produtos.php'</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar produto!');</script>";
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
</head>
<body>
<h2>Cadastro de Produto</h2>

<form method="POST" novalidate>
    <label for="ID_forn">Fornecedor:</label>
    <select name="ID_forn" id="ID_forn">
        <option value="">-- Nenhum --</option>
        <?php foreach($fornecedores as $forn): ?>
            <option value="<?= $forn['ID_forn'] ?>"><?= htmlspecialchars($forn['Nome_forn']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="id_categorias">Categoria:</label>
    <select name="id_categorias" id="id_categorias" required>
        <option value="">Selecione</option>
        <?php foreach($categorias as $cat): ?>
            <option value="<?= $cat['id_categorias'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="Nome_prod">Nome do Produto:</label>
    <input type="text" name="Nome_prod" id="Nome_prod" maxlength="60" required />

    <label for="Preco_unitario">Preço Unitário (R$):</label>
    <input type="number" step="0.01" min="0" name="Preco_unitario" id="Preco_unitario" />

    <label for="Unid_medida">Unidade de medida:</label>
    <select name="Unid_medida" id="Unid_medida" required>
        <option value="">Selecione</option>
        <option>kg</option>
        <option>g</option>
        <option>mL</option>
    </select>

    <label for="Validade">Validade (dd/mm/aaaa):</label>
    <input type="text" name="Validade" id="Validade" maxlength="10" placeholder="dd/mm/aaaa" />

    <label for="Qntd_produto">Quantidade em Estoque:</label>
    <input type="number" name="Qntd_produto" id="Qntd_produto" min="0" />

    <button type="submit">Cadastrar Produto</button>
</form>
</body>
</html>
