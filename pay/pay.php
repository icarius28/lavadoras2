<?php
// Recibir datos (o definir valores por defecto)
$apiKey = "4Vj8eK4rloUd272L48hsrarnUA";
$merchantId = $_GET['merchantId'] ?? "508029";
$accountId = $_GET['accountId'] ?? "512321";
$description = $_GET['description'] ?? "Pago multa";
$referenceCode = $_GET['referenceCode'] ?? "TestPayUs001";
$amount = $_GET['amount'] ?? "0";
$tax = $_GET['tax'] ?? "0";
$taxReturnBase = $_GET['taxReturnBase'] ?? "0";
$currency = $_GET['currency'] ?? "COP";
$buyerEmail = $_GET['buyerEmail'] ?? "test@test.com";
$test = $_GET['test'] ?? "1";
$responseUrl = $_GET['responseUrl'] ?? "https://alquilav.com/response.php";
$confirmationUrl = $_GET['confirmationUrl'] ?? "https://alquilav.com/confirmation.php";

// Generar firma
$signature = md5($apiKey . "~" . $merchantId . "~" . $referenceCode . "~" . $amount . "~" . $currency);

// Sanitizar para HTML (evitar XSS)
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Redirigiendo a PayU...</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
        Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
      background: #f8fafc;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      padding: 1rem;
      text-align: center;
      color: #333;
    }
    .container {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }
    .loader {
      border: 5px solid #eee;
      border-top: 5px solid #3b82f6;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      margin: 1rem auto;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body onload="document.forms['payu_form'].submit()">
  <div class="container">
    <h1>Redirigiendo a PayU...</h1>
    <div class="loader"></div>
    <p>Por favor, espera mientras te conectamos al sistema de pagos.</p>

    <form id="payu_form" method="post" action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/">
      <input name="merchantId"      type="hidden"  value="<?= esc($merchantId) ?>">
      <input name="accountId"       type="hidden"  value="<?= esc($accountId) ?>">
      <input name="description"     type="hidden"  value="<?= esc($description) ?>">
      <input name="referenceCode"   type="hidden"  value="<?= esc($referenceCode) ?>">
      <input name="amount"          type="hidden"  value="<?= esc($amount) ?>">
      <input name="tax"             type="hidden"  value="<?= esc($tax) ?>">
      <input name="taxReturnBase"   type="hidden"  value="<?= esc($taxReturnBase) ?>">
      <input name="currency"        type="hidden"  value="<?= esc($currency) ?>">
      <input name="signature"       type="hidden"  value="<?= esc($signature) ?>">
      <input name="test"            type="hidden"  value="<?= esc($test) ?>">
      <input name="buyerEmail"      type="hidden"  value="<?= esc($buyerEmail) ?>">
      <input name="responseUrl"     type="hidden"  value="<?= esc($responseUrl) ?>">
      <input name="confirmationUrl" type="hidden"  value="<?= esc($confirmationUrl) ?>">
    </form>
  </div>
</body>
</html>
