<?php
require_once '../modelo/db.php';
require_once '../modelo/helpers.php';
require_once '../modelo/notifications.php';
$conn = conect();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'cambiar_status') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    $stmt->execute();
    echo 'ok';
}

if ($action == 'tomar_recaudo') {
    $id = $_POST['id'];             // ID del conductor
    $monedero = $_POST['monedero']; // Valor a transferir
    $negocio = $_POST['negocio'];   // ID del negocio

    // Seguridad básica
    $id = intval($id);
    $monedero = intval($monedero);
    $negocio = intval($negocio);

    // 1. Registrar la transacción
    $stmt = $conn->prepare("INSERT INTO transacciones_cobro (origen_id, destino_id, monto, descripcion) VALUES (?, ?, ?, ?)");
    $descripcion = 'Recaudo entregado por conductor al negocio';
    $stmt->bind_param("iiis", $id, $negocio, $monedero, $descripcion);
    $stmt->execute();

    // 2. Limpiar monedero del conductor
    $stmt = $conn->prepare("UPDATE usuarios SET monedero = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // 3. Sumar al monedero del negocio
    $stmt = $conn->prepare("UPDATE usuarios SET monedero = monedero + ? WHERE id = ?");
    $stmt->bind_param("ii", $monedero, $negocio);
    $stmt->execute();

    echo 'ok';
}

if ($action == 'obtener_usuario') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, nombre, correo FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
}

if ($action == 'editar_usuario') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $correo, $id);
    $stmt->execute();
    echo 'ok';
}
if ($action == 'crear_usuario_app') {
    $nombre_usuario = $_POST['usuario_nombre'];
    $apellido_usuario = $_POST['usuario_apellido'];
    $telefono_usuario = $_POST['usuario_telefono'];
    $correo_usuario = $_POST['usuario_correo'];
    $usuario_usuario = $_POST['usuario_usuario'];
    $negocio = $_POST['id'];
 
    $plainPassword = generarContrasenaAleatoria(); // genera algo como "A3b8Xz"
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    $rol_id = 3; // rol 3 para conductores

    // Validar que el email no exista
    if (email_exists($conn, $correo_usuario)) {
        echo 'error_correo_duplicado';
        exit;
    }

    // Insertar usuario - CORREGIDO: usar $hashedPassword no $contrasena
    $stmt_user = $conn->prepare("INSERT INTO usuarios (nombre, apellido, telefono,  correo, usuario, contrasena, rol_id,conductor_negocio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_user->bind_param("ssssssii", $nombre_usuario, $apellido_usuario, $telefono_usuario,  $correo_usuario, $usuario_usuario, $hashedPassword, $rol_id, $negocio);
    if ($stmt_user->execute()) {
        echo 'ok';
    } else {
        echo 'error_crear_usuario';
        exit;
    }

    require '../controllers/mailNewUser.php';
    enviarCorreoUsuarioNuevo($correo_usuario, $plainPassword);
}


function generarContrasenaAleatoria($longitud = 6) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $contrasena = '';
    for ($i = 0; $i < $longitud; $i++) {
        $contrasena .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $contrasena;
}

?>
