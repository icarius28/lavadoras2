<?php
require_once '../modelo/db.php'; // Asegúrate que conecta correctamente
$conn = conect();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'cambiar_status') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE negocios SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    $stmt->execute();
    echo 'ok';
}

if ($action == 'obtener_negocio') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM negocios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
}

if ($action == 'editar_negocio') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $ciudad = $_POST['ciudad'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];
    $stmt = $conn->prepare("UPDATE negocios SET nombre = ?, direccion = ?, telefono = ?, ciudad = ?, latitud = ?, longitud = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nombre, $direccion, $telefono, $ciudad, $latitud, $longitud, $id);
    $stmt->execute();
    echo 'ok';
}

if ($action == 'crear_negocio') {

    // Datos del usuario
    $nombre_usuario = $_POST['usuario_nombre'];
    $apellido_usuario = $_POST['usuario_apellido'];
    $telefono_usuario = $_POST['usuario_telefono'];
    $correo_usuario = $_POST['usuario_correo'];
    $usuario_usuario = $_POST['usuario_usuario'];
    $direccion_usuario = $_POST['direccion'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol_id = 2;

    // 1️⃣ Verificar si el correo ya existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo_usuario);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo 'error_correo'; // El correo está repetido
        exit;
    }
    $check->close();

    // 2️⃣ Insertar usuario
    $stmt_user = $conn->prepare("INSERT INTO usuarios 
        (nombre, apellido, telefono, direccion, correo, usuario, contrasena, rol_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt_user->bind_param(
        "sssssssi",
        $nombre_usuario,
        $apellido_usuario,
        $telefono_usuario,
        $direccion_usuario,
        $correo_usuario,
        $usuario_usuario,
        $contrasena,
        $rol_id
    );

    if ($stmt_user->execute()) {

        $id_usuario = $stmt_user->insert_id;

        // Datos del negocio
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $ciudad = $_POST['ciudad'];
        $latitud = $_POST['latitud'];
        $longitud = $_POST['longitud'];
        $status = 1;

        // 3️⃣ Insertar negocio
        $stmt_negocio = $conn->prepare("INSERT INTO negocios 
            (nombre, direccion, telefono, ciudad, latitud, longitud, usuario_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_negocio->bind_param(
            "ssssddii",
            $nombre,
            $direccion,
            $telefono,
            $ciudad,
            $latitud,
            $longitud,
            $id_usuario,
            $status
        );

        if ($stmt_negocio->execute()) {
            echo 'ok';
        } else {
            echo 'error_negocio';
        }

    } else {
        echo 'error_usuario';
    }
}


?>
