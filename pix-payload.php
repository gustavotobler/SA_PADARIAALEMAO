<?php
// pix-payload.php
header('Content-Type: application/json; charset=utf-8');

// ======= CONFIGURE SUA CHAVE PIX =======
$chave  = $_GET['chave']  ?? '13913801936'; // chave PIX
$nome   = $_GET['nome']   ?? 'PADARIA DO ALEMÃO';           // nome do recebedor (máx. 25 caracteres)
$cidade = $_GET['cidade'] ?? 'JOINVILLE';           // cidade do recebedor (máx. 15 caracteres)
$info   = $_GET['info']   ?? 'PEDIDO PDV';          // descrição opcional
$valor  = isset($_GET['valor'])
        ? number_format((float)str_replace(',', '.', $_GET['valor']), 2, '.', '')
        : '0.00';

// Função para sanitizar campos EMV (sem acentos, maiúsculas)
function sanitize($s, $maxLen = null){
    if(function_exists('mb_strtoupper')) $s = mb_strtoupper($s, 'UTF-8');
    else $s = strtoupper($s);
    if(function_exists('iconv')){
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    }
    $s = preg_replace('/[^A-Z0-9 \-\.]/', '', $s);
    $s = trim($s);
    if($maxLen) $s = substr($s, 0, $maxLen);
    return $s;
}
$nome   = sanitize($nome, 25);
$cidade = sanitize($cidade, 15);
$info   = sanitize($info, 25);

// Função TLV (ID + tamanho + valor)
function tlv($id, $value){
    $len = strlen($value);
    return $id . str_pad($len, 2, '0', STR_PAD_LEFT) . $value;
}

// Função CRC16 para PIX EMV
function crc16_emv($payload){
    $polynom = 0x1021; $result = 0xFFFF;
    $bytes = unpack('C*', $payload);
    foreach($bytes as $b){
        $result ^= ($b << 8);
        for($i=0; $i<8; $i++){
            if(($result & 0x8000) !== 0){
                $result = (($result << 1) ^ $polynom) & 0xFFFF;
            } else {
                $result = ($result << 1) & 0xFFFF;
            }
        }
    }
    return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));
}

// Monta Merchant Account Info (ID 26)
$mai  = tlv('00', 'BR.GOV.BCB.PIX');
$mai .= tlv('01', $chave);
if(!empty($info)) $mai .= tlv('02', $info);
$mai = tlv('26', $mai);

// Additional Data Field Template (ID 62)
$add = tlv('05', '***');
$add = tlv('62', $add);

// Monta payload PIX
$payload  = tlv('00', '01');     // Payload Format Indicator
$payload .= tlv('01', '11');     // Ponto de Iniciação: Estático (11)
$payload .= $mai;
$payload .= tlv('52', '0000');   // Categoria MCC genérica
$payload .= tlv('53', '986');    // Moeda: BRL
if((float)$valor > 0){
    $payload .= tlv('54', $valor); // Valor
}
$payload .= tlv('58', 'BR');     // País
$payload .= tlv('59', $nome);    // Nome
$payload .= tlv('60', $cidade);  // Cidade
$payload .= $add;

// Calcula CRC16
$crc = crc16_emv($payload . '6304');
$payloadCompleto = $payload . '6304' . $crc;

echo json_encode(['payload' => $payloadCompleto], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
