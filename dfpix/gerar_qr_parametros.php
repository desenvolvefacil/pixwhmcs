<?php

include "../dfpix/phpqrcode/qrlib.php";
include "../dfpix/funcoes_pix.php";

/*
 * Gera o QrCode Pix recebdno parametros por URL
 */
header("Content-type: image/png");


$amount = filter_input(INPUT_GET, 'amount', FILTER_SANITIZE_STRING);
$chavePix = filter_input(INPUT_GET, 'chavePix', FILTER_SANITIZE_STRING);
$nome = filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_STRING);
$cidade = filter_input(INPUT_GET, 'cidade', FILTER_SANITIZE_STRING);
$prefixo = filter_input(INPUT_GET, 'prefixo', FILTER_SANITIZE_STRING);
$invoiceId = filter_input(INPUT_GET, 'invoiceid', FILTER_SANITIZE_STRING);

$amount = str_ireplace("R$", $replace, $amount);
$amount = str_ireplace(",", ".", $amount);
$amount = str_ireplace(" ", "", $amount);

//echo $amount; exit;

/*
https://financeiro.servidorfacil.com.br/modules/gateways/dfpix/gerar_qr_parametros.php?
amount={$invoice_total}
&chavePix=CHAVEPIX
&nome=NOME
&cidade=CIDADE
&prefixo=PREFIXO
&invoiceid={$invoice_id}
*/
/*
{$whmcs_url}/modules/gateways/dfpix/gerar_qr_parametros.php?amount={$invoice_total}&chavePix=CHAVEPIX&nome=NOME&cidade=CIDADE&prefixo=PREFIXO&invoiceid={$invoice_id}
*/

/*
<img src="{$whmcs_url}/modules/gateways/dfpix/gerar_qr_parametros.php?amount={$invoice_total}&chavePix=CHAVEPIX&nome=NOME&cidade=CIDADE&prefixo=PREFIXO&invoiceid={$invoice_id}" alt="Fatura #{$invoice_id}" />
 */

/*
https://financeiro.servidorfacil.com.br/modules/gateways/dfpix/gerar_qr_parametros.php?amount=R$ 10,54&chavePix=31529132819&nome=Carlos Martins&cidade=Sao Jose do Rio Preto&prefixo=SF-&invoiceid=10
 */

/* monta o pix */
$px[00] = "01"; //Payload Format Indicator, Obrigatório, valor fixo: 01
// Se o QR Code for para pagamento único (só puder ser utilizado uma vez), descomente a linha a seguir.
//$px[01] = "12"; //Se o valor 12 estiver presente, significa que o BR Code só pode ser utilizado uma vez. 
$px[26][00] = "BR.GOV.BCB.PIX"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
$px[26][01] = $chavePix; //Chave do destinatário do pix, pode ser EVP, e-mail, CPF ou CNPJ.
$px[26][02] = "Fatura-" . $invoiceId; // Descrição da transação, opcional.

$px[52] = "0000"; //Merchant Category Code “0000” ou MCC ISO18245
$px[53] = "986"; //Moeda, “986” = BRL: real brasileiro - ISO4217
$px[54] = $amount; //Valor da transação, se comentado o cliente especifica o valor da transação no próprio app. Utilizar o . como separador decimal. Máximo: 13 caracteres.
$px[58] = "BR"; //“BR” – Código de país ISO3166-1 alpha 2
$px[59] = $nome; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
$px[60] = $cidade; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
$px[62][05] = $prefixo . $invoiceId; //Identificador de transação, quando gerado automaticamente usar ***. Vide nota abaixo.

$pix = montaPix($px);
$pix .= "6304"; //Adiciona o campo do CRC no fim da linha do pix.
$pix .= crcChecksum($pix); //Calcula o checksum CRC16 e acrescenta ao final.
//gera o qrcode do PIX
ob_start();
QRCode::png($pix, null, 'M', 5);
$imageString = base64_encode(ob_get_contents());
ob_end_clean();

// Exibe a imagem diretamente no navegador codificada em base64.

echo base64_decode($imageString);

echo $data;

exit(0);
