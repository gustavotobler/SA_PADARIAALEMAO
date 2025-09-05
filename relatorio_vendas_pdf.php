<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

if (!isset($_SESSION['ID_func'])) {
    die("Sessão expirada.");
}

$host = "127.0.0.1";
$db   = "padariadoalemao";
$user = "root";
$pass = "";
$pdo  = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Buscar todas as vendas
$sql = "SELECT v.ID_vendas, v.ID_func, v.venda_data, v.forma_pagamento, v.status, f.Nome_func
        FROM vendas v
        LEFT JOIN funcionario f ON f.ID_func = v.ID_func
        ORDER BY v.venda_data ASC";
$stmt = $pdo->query($sql);
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total de cada venda
foreach ($vendas as &$v) {
    $itens = $pdo->prepare("
        SELECT (iv.Quantidade * p.Preco_unitario) AS valor_total
        FROM itens_vendas iv
        JOIN produtos p ON p.ID_produto = iv.ID_produto
        WHERE iv.ID_vendas = ?
    ");
    $itens->execute([$v['ID_vendas']]);
    $totais = $itens->fetchAll(PDO::FETCH_COLUMN);
    $v['total'] = array_sum($totais);
}



// Usuário e hora da geração
$usuario = isset($_SESSION['Nome_func']) ? $_SESSION['Nome_func'] : '-';
$hora_geracao = date('d/m/Y H:i');

// Montar HTML estilizado
$html = '
<div style="text-align:center;margin-bottom:10px;">
    <h1 style="margin:0;">Relatório de Vendas</h1>
    <p style="margin:0; font-size:0.9rem;">Gerado por: <b>'.$usuario.'</b> | Em: <b>'.$hora_geracao.'</b></p>
</div>

<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif;">
<thead>
<tr style="background-color:#0077b6; color:#fff; text-align:center;">
<th>ID Venda</th>
<th>Status</th>
<th>Usuário</th>
<th>Forma Pagamento</th>
<th>Total</th>
</tr>
</thead>
<tbody>';

$alt = false;
foreach ($vendas as $v) {
    $bg = $alt ? '#f2f2f2' : '#ffffff';
    $alt = !$alt;
    $html .= '<tr style="background-color:'.$bg.'; text-align:center;">
        <td>'.$v['ID_vendas'].'</td>
        <td>'.$v['status'].'</td>
        <td>'.htmlspecialchars($v['Nome_func']).'</td>
        <td>'.htmlspecialchars($v['forma_pagamento']).'</td>
        <td>R$ '.number_format($v['total'],2,',','.').'</td>
    </tr>';
}

$html .= '</tbody>
</table>';

// Gerar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

if (ob_get_length()) ob_end_clean();
$dompdf->stream("relatorio_vendas.pdf", ["Attachment" => true]);
exit();
?>
