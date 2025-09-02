<?php
// pix-payload.php - versão aprimorada
header('Content-Type: application/json; charset=utf-8');
// CORS - ajuste conforme sua necessidade (coloque domínio específico em produção)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// Recebe parâmetros (GET ou POST)
$in = array_merge($_GET ?? [], $_POST ?? []);
$chave  = $in['chave']   ?? '13913801936';
$nome   = $in['nome']    ?? 'PADARIA DO ALEMÃO';
$cidade = $in['cidade']  ?? 'JOINVILLE';
$info   = $in['info']    ?? 'PEDIDO PDV';
$valor_raw = isset($in['valor']) ? $in['valor'] : null;
$txid   = $in['txid']    ?? '***';

// normaliza valor
if ($valor_raw === null || $valor_raw === '') {
    $valor = null; // sem campo 54
} else {
    $valor = number_format((float)str_replace(',', '.', $valor_raw), 2, '.', '');
}

// sanitize para campos EMV (sem acentos, maiúsculas)
function sanitize_emv($s, $maxLen = null){
    if ($s === null) return '';
    if(function_exists('mb_strtoupper')) $s = mb_strtoupper($s, 'UTF-8');
    else $s = strtoupper($s);
    if(function_exists('iconv')) $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    $s = preg_replace('/[^A-Z0-9 \-\\.]/', '', $s);
    $s = trim($s);
    if ($maxLen) $s = substr($s, 0, $maxLen);
    return $s;
}

// valida/sanitiza chave PIX (telefone/email/cpf/cnpj/aleatoria)
function normalize_key($k){
    $k = trim($k);
    if ($k === '') return $k;
    // email
    if (filter_var($k, FILTER_VALIDATE_EMAIL)) {
        return strtolower($k);
    }
    // telefone ou CPF/CNPJ (somente dígitos e opcional +)
    // remove espaços e caracteres não-numéricos, exceto +
    $clean = preg_replace('/[^\d\+]/', '', $k);
    // se começar com 0 ou sem 55, permitir, pois pode ser local
    return $clean;
}

// TLV helper
function tlv($id, $value){
    $len = strlen($value);
    return $id . str_pad($len, 2, '0', STR_PAD_LEFT) . $value;
}

// CRC16 EMV
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

// aplicar normalizações
$chave = normalize_key($chave);
$nome  = sanitize_emv($nome, 25);
$cidade = sanitize_emv($cidade, 15);
$info  = sanitize_emv($info, 25);
$txid  = substr(preg_replace('/[^A-Za-z0-9\-\_]/','', (string)$txid), 0, 25);
if ($txid === '') $txid = '***';

// Monta Merchant Account Info (ID 26)
$mai  = tlv('00', 'BR.GOV.BCB.PIX');
$mai .= tlv('01', $chave);
if (!empty($info)) $mai .= tlv('02', $info);
$mai = tlv('26', $mai);

// Additional Data Field Template (ID 62) com txid dinâmico
$add = tlv('05', $txid);
$add = tlv('62', $add);

// Monta payload
$payload  = tlv('00', '01');     // payload format
// opcional: ponto de iniciação estático - se quiser remover, comente a próxima linha
$payload .= tlv('01', '11');     // '11' estatico
$payload .= $mai;
$payload .= tlv('52', '0000');
$payload .= tlv('53', '986');
if ($valor !== null && $valor !== '0.00') {
    $payload .= tlv('54', $valor);
}
$payload .= tlv('58', 'BR');
$payload .= tlv('59', $nome);
$payload .= tlv('60', $cidade);
$payload .= $add;

// CRC
$crc = crc16_emv($payload . '6304');
$payloadCompleto = $payload . '6304' . $crc;

// resposta JSON com alguns campos úteis para debug
$response = [
    'payload' => $payloadCompleto,
    'meta' => [
        'chave' => $chave,
        'nome' => $nome,
        'cidade' => $cidade,
        'valor' => $valor === null ? null : $valor,
        'txid' => $txid
    ]
];

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
