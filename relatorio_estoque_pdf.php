<?php
session_start();

$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $sql = "
    SELECT 
        p.ID_produto,
        p.Nome_prod,
        f.Nome_forn,
        c.nome_categoria,
        p.Unid_medida,
        p.Qntd_produto,
        p.Preco_unitario,
        (p.Qntd_produto * p.Preco_unitario) AS valor_total,
        p.Validade
    FROM produtos p
    LEFT JOIN fornecedores f ON p.ID_forn = f.ID_forn
    LEFT JOIN categorias c ON p.id_categorias = c.id_categorias
    ORDER BY p.Nome_prod ASC
    ";

    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll();

    // Corrigir possíveis valores null
    foreach ($rows as &$row) {
        $row['ID_produto'] = $row['ID_produto'] ?? '';
        $row['Nome_prod'] = $row['Nome_prod'] ?? '';
        $row['Nome_forn'] = $row['Nome_forn'] ?? '';
        $row['nome_categoria'] = $row['nome_categoria'] ?? '';
        $row['Unid_medida'] = $row['Unid_medida'] ?? '';
        $row['Qntd_produto'] = $row['Qntd_produto'] ?? 0;
        $row['Preco_unitario'] = $row['Preco_unitario'] ?? 0;
        $row['valor_total'] = $row['valor_total'] ?? 0;
        $row['Validade'] = $row['Validade'] ?? '';
    }

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Obter usuário que gerou o relatório e data/hora
$usuario = $_SESSION['Nome_func'] ?? 'Desconhecido';
$dataEmissao = date('d/m/Y H:i:s');

// Montar HTML do PDF
$html = '
<div style="text-align:center;margin-bottom:20px;">
    <h1 style="margin:0;">Relatório de Estoque</h1>
    <p style="margin:0;"><b>Gerado por:</b> '.$usuario.' | <b>Data/Hora:</b> '.$dataEmissao.'</p>
</div>
<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif;">
<thead>
<tr style="background-color:#0077b6; color:#fff; text-align:center;">
<th>ID Produto</th>
<th>Nome</th>
<th>Fornecedor</th>
<th>Categoria</th>
<th>Unidade</th>
<th>Quantidade</th>
<th>Preço Unitário</th>
<th>Valor Total</th>
<th>Validade</th>
</tr>
</thead>
<tbody>';

$alt = false;
foreach ($rows as $row) {
    $bg = $alt ? '#f2f2f2' : '#ffffff';
    $alt = !$alt;
    $validadeFormatada = !empty($row['Validade']) ? date('d/m/Y', strtotime($row['Validade'])) : '---';
    $html .= '<tr style="background-color:'.$bg.'; text-align:center;">
        <td>'.htmlspecialchars($row['ID_produto']).'</td>
        <td>'.htmlspecialchars($row['Nome_prod']).'</td>
        <td>'.htmlspecialchars($row['Nome_forn']).'</td>
        <td>'.htmlspecialchars($row['nome_categoria']).'</td>
        <td>'.htmlspecialchars($row['Unid_medida']).'</td>
        <td>'.number_format($row['Qntd_produto'],2,',','.').'</td>
        <td>R$ '.number_format($row['Preco_unitario'],2,',','.').'</td>
        <td>R$ '.number_format($row['valor_total'],2,',','.').'</td>
        <td>'.$validadeFormatada.'</td>
    </tr>';
}

$html .= '</tbody>
</table>';

// Gerar PDF
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
if (ob_get_length()) ob_end_clean();
$dompdf->stream("relatorio_estoque.pdf", ["Attachment" => true]);
exit();
