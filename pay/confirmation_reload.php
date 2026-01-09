<?php
// confirmacion.php

// Configuración DB
$host = "localhost";
$dbname = "alquilav_ndb";
$user = "alquilav_ndb";
$pass = "&^L1s,)Z_W56";



$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// PayU envía por POST
$referenceCode = $_POST['reference_sale'] ?? '';
$transactionState = $_POST['state_pol'] ?? '';
$transactionId = $_POST['transaction_id'] ?? '';
$metodoPago = $_POST['payment_method_name'] ?? '';
$amount = $_POST['value'] ?? 0;
$currency = $_POST['currency'] ?? '';
$userId = $_POST['extra1'] ?? 0; // o extra1 si así lo envías

if (!empty($referenceCode)) {
    // Convertir estado
    switch ($transactionState) {
        case '4':
            $estado = 'APROBADO';
            break;
        case '6':
            $estado = 'RECHAZADO';
            break;
        case '7':
            $estado = 'PENDIENTE';
            break;
        default:
            $estado = 'DESCONOCIDO';
    }
    
    
    if($estado=='APROBADO'){
              $parts = explode('-', $referenceCode);
            $extractedUserId = 0;
            if (count($parts) >= 3) {
                $extractedUserId = (int)$parts[1];
            }
    
         if ($extractedUserId > 0) {
            $stmtUpdate = $conn->prepare("UPDATE usuarios SET monedero = monedero + ? WHERE id = ?");
            $stmtUpdate->bind_param("i",$amount, $extractedUserId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            $token = getUserFMC( $id_usuario);
            enviarNotificacionFCM($token, "Recarga", "Se ha realizado una recarga de $".$amount, "", "mensaje");
        }
        
    }

    // Verificar si existe
    $stmt = $conn->prepare("SELECT id FROM pagos_pay WHERE reference_code = ?");
    $stmt->bind_param("s", $referenceCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar pago
        $stmt = $conn->prepare("UPDATE pagos_pay SET estado=?, transaction_id=?, metodo_pago=?, fecha_actualizacion=NOW() WHERE reference_code=?");
        $stmt->bind_param("ssss", $estado, $transactionId, $metodoPago, $referenceCode. "-Recarga");
        $stmt->execute();
    } else {
        // Insertar nuevo pago
        $stmt = $conn->prepare("INSERT INTO pagos_pay (user_id, reference_code, amount, currency, estado, transaction_id, metodo_pago) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssss", $userId, $referenceCode, $amount, $currency, $estado, $transactionId, $metodoPago);
        $stmt->execute();
    }
}

$conn->close();

function getUserFMC($id_usuario) {
    global $conn;
    $id_usuario = intval($id_usuario);
    if ($id_usuario <= 0) {
        return null;
    }

    $sql = "SELECT fcm FROM usuarios WHERE id = $id_usuario LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        return $row['fcm'];
    }

    return null;
}

function enviarNotificacionFCM($token, $titulo, $mensaje, $id_servico,$type)
{
    $fcm_token = $token;
    $titulo = $titulo;
    $mensaje = $mensaje;

    // Ruta hacia tu script de envío de notificación
    $url = 'https://alquilav.com/firebase/enviar.php';

    // Datos a enviar por POST
    $data = [
        'token' => $fcm_token,
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'id_servicio' => $id_servico,
        'type' =>$type
    ];

    // Inicializar cURL
    $ch = curl_init($url);

    // Configurar opciones
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Verificar errores
    if ($response === false) {
       // echo 'Error en cURL: ' . curl_error($ch);
    } else {
       // echo 'Respuesta de Firebase: ' . $response;
    }

    curl_close($ch);

}

// PayU requiere respuesta "OK"
echo "OK";
?>
