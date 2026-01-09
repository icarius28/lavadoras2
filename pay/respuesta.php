<?php
// respuesta.php

$estado = $_REQUEST['transactionState'] ?? '';
$mensaje = '';

switch ($estado) {
    case '4':
        $mensaje = '✅ Pago aprobado. ¡Gracias por tu compra!';
        break;
    case '6':
        $mensaje = '❌ Pago rechazado. Por favor intenta nuevamente.';
        break;
    case '7':
        $mensaje = '⏳ Pago pendiente. Te notificaremos cuando se confirme.';
        break;
    default:
        $mensaje = '⚠️ Estado de pago desconocido.';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Resultado del pago</title>
</head>
<body>
    <h1><?php echo $mensaje; ?></h1>
</body>
</html>
