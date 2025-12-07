<?php
require_once '../modelo/db.php'; // Asegúrate que conecta correctamente
$conn = conect();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action == 'guardar_precios_lavadoras') {
    session_start();
    $id_negocio = $_SESSION['negocio'] ?? null;
    if (!$id_negocio) {
        http_response_code(400);
        echo "ID de negocio no definido.";
        exit;
    }

    $precios = $_POST['precios'] ?? [];

    foreach ($precios as $lavadora => $servicios) {
        foreach ($servicios as $tipo_servicio => $precio) {
            // Verificar si ya existe
            $stmt_check = $conn->prepare("SELECT id FROM precios_lavado WHERE tipo_lavadora = ? AND tipo_servicio = ? AND id_negocio = ?");
            $stmt_check->bind_param("ssi", $lavadora, $tipo_servicio, $id_negocio);
            $stmt_check->execute();
            $result = $stmt_check->get_result();

            if ($result->num_rows > 0) {
                // Actualizar
                $stmt_update = $conn->prepare("UPDATE precios_lavado SET precio = ? WHERE tipo_lavadora = ? AND tipo_servicio = ? AND id_negocio = ?");
                $stmt_update->bind_param("dssi", $precio, $lavadora, $tipo_servicio, $id_negocio);
                $stmt_update->execute();
            } else {
                // Insertar
                $stmt_insert = $conn->prepare("INSERT INTO precios_lavado (tipo_lavadora, tipo_servicio, precio, id_negocio) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssdi", $lavadora, $tipo_servicio, $precio, $id_negocio);
                $stmt_insert->execute();
            }
        }
    }

    echo "Precios guardados correctamente.";
    exit;
}

?>
