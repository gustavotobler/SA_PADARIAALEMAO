<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php'; 
use Dompdf\Dompdf;

if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
    die("Acesso negado.");
}

$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $sql = "
    SELECT 
        v.ID_vendas,
        v.venda_data,
        COALESCE(f.Nome_func,'-') AS Nome_func,
        COALESCE(p.Nome_prod,'-') AS Nome_prod,
        COALESCE(iv.Quantidade,0) AS quant_vendas,
        COALESCE(p.Preco_unitario,0) AS preco_unit,
        COALESCE(iv.valor_total,0) AS preco_total,
        COALESCE(v.forma_pagamento,'-') AS forma_pagamento
    FROM vendas v
    LEFT JOIN funcionario f ON v.ID_func = f.ID_func
    LEFT JOIN itens_vendas iv ON v.ID_vendas = iv.ID_vendas
    LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
    ORDER BY v.venda_data DESC";

    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Calcular total geral
$totalGeral = array_sum(array_column($rows, 'preco_total'));

// Caminho absoluto da logo
$logoPath = realpath('img/logopadaria.png'); // ajuste conforme a pasta correta

// Montar HTML estilizado
$html = '
<div style="text-align:center;margin-bottom:20px;">
    <img src="file://'.$logoPath.'" width="120" style="margin-bottom:10px;">
    <h1 style="margin:0;">Relatório de Vendas</h1>
</div>
<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif;">
<thead>
<tr style="background-color:#0077b6; color:#fff; text-align:center;">
<th>ID Venda</th>
<th>Data</th>
<th>Funcionário</th>
<th>Produto</th>
<th>Qtd</th>
<th>Preço Unit.</th>
<th>Total</th>
<th>Pagamento</th>
</tr>
</thead>
<tbody>';

$alt = false;
foreach ($rows as $row) {
    $bg = $alt ? '#f2f2f2' : '#ffffff';
    $alt = !$alt;
    $dataFormatada = date('d/m/Y', strtotime($row['venda_data']));
    $html .= '<tr style="background-color:'.$bg.'; text-align:center;">
        <td>'.htmlspecialchars($row['ID_vendas']).'</td>
        <td>'.$dataFormatada.'</td>
        <td>'.htmlspecialchars($row['Nome_func']).'</td>
        <td>'.htmlspecialchars($row['Nome_prod']).'</td>
        <td>'.number_format($row['quant_vendas'], 0, ',', '.').'</td>
        <td>R$ '.number_format($row['preco_unit'], 2, ',', '.').'</td>
        <td>R$ '.number_format($row['preco_total'], 2, ',', '.').'</td>
        <td>'.htmlspecialchars($row['forma_pagamento']).'</td>
    </tr>';
}

// Linha de total geral
$html .= '<tr style="background-color:#e0e0e0; font-weight:bold; text-align:center;">
    <td colspan="6">Total Geral</td>
    <td>R$ '.number_format($totalGeral, 2, ',', '.').'</td>
    <td>-</td>
</tr>';

$html .= '</tbody></table>';

// Gerar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Limpar output buffer
if (ob_get_length()) ob_end_clean();

// Forçar download
$dompdf->stream("relatorio_vendas.pdf", ["Attachment" => true]);
exit();
