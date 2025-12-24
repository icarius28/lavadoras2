<?php
require_once '../modelo/db.php'; // Asegúrate que conecta correctamente
$conn = conect();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Cambiar estado del proveedor
if ($action == 'cambiar_status_proveedor') {
    $id = $_POST['id'];
    $estado = $_POST['status'];
    $stmt = $conn->prepare("UPDATE proveedores SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);
    $stmt->execute();
    echo 'ok';
}

// Obtener proveedor por ID
if ($action == 'obtener_proveedor') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM proveedores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
}

// Editar proveedor
if ($action == 'editar_proveedor') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];

    $stmt = $conn->prepare("UPDATE proveedores SET nombre = ?, telefono = ?, correo = ?, direccion = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nombre, $telefono, $correo, $direccion, $id);
    $stmt->execute();
    echo 'ok';
}

// Crear proveedor
if ($action == 'crear_proveedor') {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $estado = $_POST['estado'] ?? 'activo';
    $negocio = $_POST['negocio'];

    $stmt = $conn->prepare("INSERT INTO proveedores (nombre, telefono, correo, direccion, estado, negocio_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nombre, $telefono, $correo, $direccion, $estado, $negocio);

    if ($stmt->execute()) {
        echo 'ok';
    } else {
        echo 'error_proveedor';
    }
}

if ($action == 'eliminar_proveedor') {
    $id = $_POST['id'];
    
    $stmt = $conn->prepare("UPDATE proveedores SET estado = 'eliminado' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo 'ok';
    } else {
        echo 'error';
    }
}
?>
