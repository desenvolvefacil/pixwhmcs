<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

include "dfpix/phpqrcode/qrlib.php";
include "dfpix/funcoes_pix.php";

function dfpix_MetaData() {
    return array(
        'DisplayName' => 'DF Pix',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function dfpix_config() {
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'DF Pix',
        ),
        // a text field type allows for single line text input
        'chavePix' => array(
            'FriendlyName' => 'Chave Pix',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Coloque sua Chave Pix CPF/CNPJ | Telefone | Email | Chave Aleátoria',
        ),
        // a text field type allows for single line text input
        'nome' => array(
            'FriendlyName' => 'Nome | Razão Social',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Digite seu nome até 25 caracteres',
        ),
        // a text field type allows for single line text input
        'cidade' => array(
            'FriendlyName' => 'Cidade',
            'Type' => 'text',
            'Size' => '15',
            'Default' => '',
            'Description' => 'Digite sua Cidade até 15 caracteres',
        ),
        // a text field type allows for single line text input
        'prefixo' => array(
            'FriendlyName' => 'Prefixo',
            'Type' => 'text',
            'Size' => '15',
            'Default' => 'SF-',
            'Description' => 'Digite o Prefixo de seu identificador, para diferenciar de outros sistemas',
        ),
        'aviso' => array(
            'FriendlyName' => 'Ajude a Manter este Projeto',
            'Type' => 'label',
            'Size' => '15',
            'Description' => '<p><b>Ajude a Manter este Projeto</b></p><p> Faça uma doação de qualquer valor para o PIX</p><img style="width: 200px" src="/modules/gateways/dfpix/pix-doacao.jpeg" /><p>Chave Pix: 04f9c930-6907-4160-bcc7-bb778c51fc2f</p>',
        ),
    );
}

function dfpix_link($params) {
    // Gateway Configuration Parameters

    $chavePix = $params['chavePix'];
    $nome = $params['nome'];
    $cidade = $params['cidade'];
    $prefixo = $params['prefixo'];
    /* $accountId = $params['accountID'];
      $secretKey = $params['secretKey'];
      $testMode = $params['testMode'];
      $dropdownField = $params['dropdownField'];
      $radioField = $params['radioField'];
      $textareaField = $params['textareaField']; */

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    /* monta o pix */
    $px[00] = "01"; //Payload Format Indicator, Obrigatório, valor fixo: 01
    // Se o QR Code for para pagamento único (só puder ser utilizado uma vez), descomente a linha a seguir.
    $px[01] = "12"; //Se o valor 12 estiver presente, significa que o BR Code só pode ser utilizado uma vez. 
    $px[26][00] = "BR.GOV.BCB.PIX"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
    $px[26][01] = $chavePix; //Chave do destinatário do pix, pode ser EVP, e-mail, CPF ou CNPJ.
    $px[26][02] = "Fatura " . $invoiceId; // Descrição da transação, opcional.

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



    $formatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);


    $htmlOutput = '<script type="text/javascript">
        function copiarPix() {

            try {
                var aux = document.createElement("input");

                link = "' . $pix . '";

                // Get the text from the element passed into the input
                aux.setAttribute("value", link);

                // Append the aux input to the body
                document.body.appendChild(aux);

                // Highlight the content
                aux.select();

                // Execute the copy command
                document.execCommand("copy");

                // Remove the input from the body
                document.body.removeChild(aux);

                //document.getElementById(btId).innerHTML = "Copiado";

                /* Alert the copied text */
                alert("Código Pix Copiado: " + link);
            } catch (e) {
                //alert("Erro");
            }

        }
    </script>';


    $htmlOutput .= '<p><img style="max-width:150px;" src="/modules/gateways/dfpix/logo_pix.png" /></p>'
            . '<p /><p>Total a Pagar: <br/><b>' . $formatter->formatCurrency($amount, 'BRL') . '</b></p><p /><p />'
            . '<p>' . '<img src="data:image/png;base64,' . $imageString . '">' . '</p>'
            . '<p>Clique para copiar o código</p>'
            . '<input style="max-width: 300px;" type="button" onclick="javascript:copiarPix();" value="' . $pix . '" />'
            . '<p /><p /><p />'
            . '<textarea name="textarea"
   rows="5" cols="30"
   minlength="10" maxlength="20">' . $pix . '</textarea>'
            . '</p>';

    return $htmlOutput;
}
