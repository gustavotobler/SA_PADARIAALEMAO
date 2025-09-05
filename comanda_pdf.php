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

$id_venda = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_venda <= 0) die("ID da comanda inválido.");

// Buscar dados da comanda
$cab = $pdo->prepare("SELECT v.*, f.Nome_func 
                      FROM vendas v 
                      LEFT JOIN funcionario f ON f.ID_func = v.ID_func 
                      WHERE v.ID_vendas = ?");
$cab->execute([$id_venda]);
$comanda = $cab->fetch(PDO::FETCH_ASSOC);
if (!$comanda) die("Comanda não encontrada.");

// Buscar itens
$itens = $pdo->prepare("SELECT iv.*, p.Nome_prod, p.Preco_unitario 
                        FROM itens_vendas iv
                        JOIN produtos p ON p.ID_produto = iv.ID_produto
                        WHERE iv.ID_vendas = ?");
$itens->execute([$id_venda]);
$comanda['itens'] = $itens->fetchAll(PDO::FETCH_ASSOC);

// Calcular total
$total = 0;
foreach ($comanda['itens'] as $i) $total += $i['valor_total'];
$comanda['total'] = $total;

// Montar HTML estilizado
$html = '
<div style="text-align:center;margin-bottom:20px;">
    <h1 style="margin:0;">Comanda #'.$comanda['ID_vendas'].'</h1>
    <p style="margin:0;"><b>Status:</b> '.$comanda['status'].' | <b>Criada por:</b> '.$comanda['Nome_func'].'</p>
    <p style="margin:0;"><b>Data:</b> '.$comanda['venda_data'].'</p>
</div>
<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif;">
<thead>
<tr style="background-color:#0077b6; color:#fff; text-align:center;">
<th>Produto</th>
<th>Quantidade</th>
<th>Preço Unit.</th>
<th>Valor</th>
</tr>
</thead>
<tbody>';

$alt = false;
foreach ($comanda['itens'] as $i) {
    $bg = $alt ? '#f2f2f2' : '#ffffff';
    $alt = !$alt;
    $html .= '<tr style="background-color:'.$bg.'; text-align:center;">
        <td>'.htmlspecialchars($i['Nome_prod']).'</td>
        <td>'.$i['Quantidade'].'</td>
        <td>R$ '.number_format($i['Preco_unitario'],2,',','.').'</td>
        <td>R$ '.number_format($i['valor_total'],2,',','.').'</td>
    </tr>';
}

$html .= '</tbody>
<tfoot>
<tr style="background-color:#e0e0e0; font-weight:bold; text-align:center;">
    <td colspan="3" style="text-align:right;">Total</td>
    <td>R$ '.number_format($comanda['total'],2,',','.').'</td>
</tr>
</tfoot>
</table>';

// Gerar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
if (ob_get_length()) ob_end_clean();
$dompdf->stream("comanda_".$comanda['ID_vendas'].".pdf", ["Attachment" => true]);
exit();
