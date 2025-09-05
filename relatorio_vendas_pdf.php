<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php'; // ajuste conforme pasta do Dompdf
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

    // SQL ajustado com COALESCE para evitar null
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

// Montar HTML do PDF
$html = '<h1 style="text-align:center;">Relatório de Vendas</h1>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse;">
<thead>
<tr style="background:#f2f2f2;">
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

foreach ($rows as $row) {
    $html .= '<tr>
    <td>'.htmlspecialchars($row['ID_vendas']).'</td>
    <td>'.htmlspecialchars($row['venda_data']).'</td>
    <td>'.htmlspecialchars($row['Nome_func']).'</td>
    <td>'.htmlspecialchars($row['Nome_prod']).'</td>
    <td>'.number_format($row['quant_vendas'], 0, ',', '.').'</td>
    <td>R$ '.number_format($row['preco_unit'], 2, ',', '.').'</td>
    <td>R$ '.number_format($row['preco_total'], 2, ',', '.').'</td>
    <td>'.htmlspecialchars($row['forma_pagamento']).'</td>
    </tr>';
}

$html .= '</tbody></table>';

// Gerar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // landscape para melhor visualização
$dompdf->render();

// Limpar output buffer
if (ob_get_length()) ob_end_clean();

// Forçar download
$dompdf->stream("relatorio_vendas.pdf", ["Attachment" => true]);
exit();
