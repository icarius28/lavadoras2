<?php
require_once '../modelo/db.php'; // Asegúrate que conecta correctamente
$conn = conect();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Cambiar estado del pago
if ($action == 'cambiar_status_pago') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE pagos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    echo 'ok';
}

// Obtener pago por ID
if ($action == 'obtener_pago') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM pagos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
}

// Editar pago
if ($action == 'editar_pago') {
    $id = $_POST['id'];
    $monto = $_POST['monto'];
    $metodo_pago = $_POST['metodo_pago'];

    $stmt = $conn->prepare("UPDATE pagos SET monto = ?, metodo_pago = ? WHERE id = ?");
    $stmt->bind_param("dsi", $monto, $metodo_pago, $id);
    $stmt->execute();
    echo 'ok';
}

// Crear nuevo pago
if ($action == 'crear_pago') {
    $referencia = $_POST['referencia'];
    $monto = $_POST['valor'];
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $estado = $_POST['estado'] ?? 1;
    $id = $_POST['usuario_id'];

    $stmt = $conn->prepare("INSERT INTO pagos (referencia, valor, metodo_pago, estado, id_usuario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $referencia, $monto, $metodo_pago, $estado, $id);

    $stmt2 = $conn->prepare("UPDATE usuarios SET monedero = monedero + ? WHERE id = ?");
    $stmt2->bind_param("ii", $monto,  $id);
    $stmt2->execute();
    
    $token = getUserFMC($conn, $id);
    enviarNotificacionFCM($token, "Recarga", "Se ha realizado una recarga", "", "recarga");

    if ($stmt->execute()) {
        echo 'ok';
    } else {
        echo 'error_pago';
    }
}


function getUserFMC($mysqli, $id_usuario) {
    $id_usuario = intval($id_usuario);
    if ($id_usuario <= 0) {
        return null;
    }

    $sql = "SELECT fcm FROM usuarios WHERE id = $id_usuario LIMIT 1";
    $result = $mysqli->query($sql);

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


?>
