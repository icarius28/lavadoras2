<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = "localhost";
$user = "alquilav_ndb";
$password = "&^L1s,)Z_W56";
$dbname = "alquilav_ndb";


$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión']));
}

$result = $mysqli->query("SELECT * FROM config_general LIMIT 1");

if ($config_general = $result->fetch_assoc()) {
    $km = $config_general['km'];
    $valor_minimo = $config_general['valor_minimo'];
    $porcentaje = $config_general['porcentaje']; 
    $global_tarifa = $config_general['global_tarifa'];
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        register($mysqli, $data);
        break;
    case 'login':
        login($mysqli, $data);
        break;
    case 'login_domiciliario':
        login_domiciliario($mysqli, $data);
        break;
    case 'login_google':
        login_google($mysqli, $data);
        break;
    case 'rent_machine':
        rent_machine($mysqli, $data);
        break;
    case 'finish_rental':
        finish_rental($mysqli, $data);
        break;
    case 'get_user':
        get_user($mysqli, $data);
        break;
    case 'get_rental':
        get_rental($mysqli, $data);
        break;
    case 'get_rental_all':
        get_rental_all($mysqli, $data);
        break;
    case 'sum_rent_machine':
        sum_rent_machine($mysqli, $data);
        break;
    case 'available_machines':
        $resultado = available_machines($mysqli, $data);
        log_api($mysqli, 'available_machines', $data, $resultado);
        break;
    case 'edit_user':
        edit_user($mysqli, $data);
        break;
    case 'accept_service':
        accept_service($mysqli, $data);
        break;
    case 'get_pending_deliveries':
        get_pending_deliveries($mysqli, $data);
        break;
    case 'get_delivery_service':
        get_delivery_service($mysqli, $data);
        break;
    case 'delivered':
        mark_delivered_or_collected($mysqli, $data, 'delivered');
        break;
    case 'collected':
        mark_delivered_or_collected($mysqli, $data, 'collected');
        break;
    case 'simulate_delivery':
        simulate_delivery($mysqli, $data);
        break;
    case  'simulate_collection':
        simulate_collection($mysqli, $data);
    case  'lavadoras_asignadas':
        lavadoras_asignadas($mysqli, $data);
        break;
    case  'update_ubicacion_domiciliario':
        update_ubicacion_domiciliario($mysqli, $data);
        break;
    case  'get_servicio_solicitud_domicialiario':
        get_servicio_solicitud_domicialiario($mysqli, $data);
        break;
     case  'get_detail_service':
        get_detail_service($mysqli, $data);
        break;
     case  'aceptar_servicio':
        aceptar_servicio($mysqli, $data);
        break;
    case  'entregar_servicio':
        entregar_servicio($mysqli, $data);
      break;
    case  'get_rental_all_delivery':
        get_rental_all_delivery($mysqli, $data);
        break;

    case  'get_motivos':
        get_motivos($mysqli, $data);
                break;
    case  'cancelar_servicio':
        cancelar_servicio($mysqli, $data);
            break;
    case  'servicio_pendiente':
        servicio_pendiente($mysqli, $data);
        break;
    case 'forgot_password':
        forgot_password($mysqli, $data);
        break;
    case 'get_ubication_domicialiario':
        get_ubication_domicialiario($mysqli, $data);
        break;
   case 'get_ubication_domicialiario_from_deviery':
        get_ubication_domicialiario_from_deviery($mysqli, $data);
        break;
    case 'terminos_cliente':
        terminos_cliente($mysqli, $data);
        break;
    case 'terminos_delivery':
        terminos_delivery($mysqli, $data);
        break;
    case 'update_password_config':
        update_password($mysqli, $data);
    break;
    case 'pendiente_recoger':
        pendiente_recoger($mysqli, $data);
    break;
    case 'get_detail_service_finish':
        get_detail_service_finish($mysqli, $data);
    break;
    case 'recoger':
        recoger($mysqli, $data);
    break;
    case 'recaudado':
        recaudado($mysqli, $data);
    break;
    case 'check_cancelacion_permitida':
        check_cancelacion_permitida($mysqli, $data);
    break;
    case 'get_config_general':
        get_config_general($mysqli, $data);
    break;
    case 'get_banner':
        get_banner($mysqli, $data);
    break;
    case 'send_chat_message':
        send_chat_message($mysqli, $data);
    break;
    case 'get_chat_messages':
        get_chat_messages($mysqli, $data);
    break;
    case 'mark_chat_read':
        get_chat_messages($mysqli, $data);
    break;
    case 'save_fcm':
        save_fcm($mysqli, $data);
    break;
     case 'get_pagos_realizados':
        get_pagos_realizados($mysqli, $data);
    break;
     case 'get_pagos_payu':
        get_pagos_payu($mysqli, $data);
    break;
    
    case 'get_pagos_payu':
        get_pagos_payu($mysqli, $data);
    break;
    
    case 'asignar_lavadora':
        asignar_lavadora($mysqli, $data);
    break;
    
    case 'lavadoras_de_negocio':
        lavadoras_de_negocio($mysqli, $data);
    break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        break;
}

function asignar_lavadora($mysqli, $data) {
    $id_lavadora = intval($data['id_lavadora'] ?? 0);
    $id_domiciliario = intval($data['id_domiciliario'] ?? 0);
    $nuevo_estado_en = $data['en'] ?? ''; // Puede ser 'delivery' o 'bodega'

    // Validaciones básicas
    if ($id_lavadora <= 0 || $id_domiciliario <= 0 || !in_array($nuevo_estado_en, ['delivery', 'bodega'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        return;
    }

    // Verificar si la lavadora existe
    $stmt_check = $mysqli->prepare("SELECT id, en FROM lavadoras WHERE id = ?");
    $stmt_check->bind_param("i", $id_lavadora);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Lavadora no encontrada']);
        return;
    }

    $lavadora = $result->fetch_assoc();
    $stmt_check->close();

    // Actualizar asignación y ubicación
    $stmt = $mysqli->prepare("
        UPDATE lavadoras 
        SET id_domiciliario = ?, en = ? 
        WHERE id = ?
    ");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta']);
        return;
    }

    $stmt->bind_param("isi", $id_domiciliario, $nuevo_estado_en, $id_lavadora);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'ok',
            'message' => 'Lavadora asignada correctamente',
            'data' => [
                'id_lavadora' => $id_lavadora,
                'id_domiciliario' => $id_domiciliario,
                'nuevo_estado' => $nuevo_estado_en
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la lavadora']);
    }

    $stmt->close();
}


function lavadoras_de_negocio($mysqli, $data) {
    // Validar y obtener id del negocio
    $id_negocio = intval($data['id_negocio'] ?? 0);

    if ($id_negocio <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de negocio inválido']);
        return;
    }

    // Preparar la consulta para evitar inyección SQL
    $stmt = $mysqli->prepare("SELECT * FROM lavadoras WHERE negocio_id = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta']);
        return;
    }

    $stmt->bind_param("i", $id_negocio);
    $stmt->execute();
    $result = $stmt->get_result();

    $lavadoras = [];
    while ($row = $result->fetch_assoc()) {
        $lavadoras[] = $row;
    }

    if (!empty($lavadoras)) {
        echo json_encode(['status' => 'ok', 'disponibles' => $lavadoras]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay lavadoras asignadas']);
    }

    $stmt->close();
}





function get_pagos_payu($mysqli, $data) {
    $id_usuario = intval($data['id_usuario'] ?? 0);

    if (!$id_usuario) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de usuario requerido'
        ]);
        return;
    }

    // El user_id está en el reference_code como PayU-{user_id}-xxxx
    $sql = "SELECT id, reference_code, amount, currency, estado, transaction_id, metodo_pago, fecha_pago, fecha_actualizacion
            FROM pagos_pay
            WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(reference_code, '-', 2), '-', -1) = '$id_usuario'
            ORDER BY fecha_pago DESC";

    $result = $mysqli->query($sql);
    $pagos = [];
    while ($row = $result->fetch_assoc()) {
        $pagos[] = $row;
    }

    echo json_encode([
        'status' => 'ok',
        'pagos' => $pagos
    ]);
}


function get_pagos_realizados($mysqli, $data) {
    $id_usuario = intval($data['id_usuario'] ?? 0);

    if (!$id_usuario) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de usuario requerido'
        ]);
        return;
    }

    $sql = "SELECT id, referencia, valor, metodo_pago, fecha, negocio_id, estado 
            FROM pagos 
            WHERE id_usuario = $id_usuario 
            ORDER BY fecha DESC";

    $result = $mysqli->query($sql);
    $pagos = [];
    while ($row = $result->fetch_assoc()) {
        $pagos[] = $row;
    }

    echo json_encode([
        'status' => 'ok',
        'pagos' => $pagos
    ]);
}

function getFMCByServicio($mysqli, $id_servicio, $tipo = 'usuario') {
    $id_servicio = intval($id_servicio);
    $tipo = strtolower($tipo);

    if ($id_servicio <= 0) {
        return null;
    }

    // Consultamos el alquiler
    $sql = "SELECT user_id, conductor_id FROM alquileres WHERE id = $id_servicio LIMIT 1";
    $result = $mysqli->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        if ($tipo === 'usuario') {
            $id_usuario = intval($row['user_id']);
        } elseif ($tipo === 'domiciliario') {
            $id_usuario = intval($row['conductor_id']);
        } else {
            return null; // tipo inválido
        }

        if ($id_usuario > 0) {
            // Buscar el FMC del usuario encontrado
            $sqlUser = "SELECT fcm FROM usuarios WHERE id = $id_usuario LIMIT 1";
            $resultUser = $mysqli->query($sqlUser);

            if ($resultUser && $rowUser = $resultUser->fetch_assoc()) {
                return $rowUser['fcm'];
            }
        }
    }

    return null;
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

function save_fcm($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);
    $fcm = $mysqli->real_escape_string($data['token'] ?? '');

    if ($userId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario requerido']);
        return;
    }

    $query = "UPDATE usuarios SET fcm = '$fcm' WHERE id = $userId";

    if ($mysqli->query($query)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al editar usuario']);
    }
}






function send_chat_message($mysqli, $data) {
   
    $id_alquiler = intval($data['id_alquiler'] ?? 0);
    $id_usuario = intval($data['id_usuario'] ?? 0);
    $id_domiciliario = intval($data['id_domiciliario'] ?? 0);
    $remitente = $mysqli->real_escape_string($data['tipo'] ?? '');
    $mensaje = $mysqli->real_escape_string($data['mensaje'] ?? '');

    if (!$id_alquiler || !$id_usuario || !$id_domiciliario || !$remitente || !$mensaje) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Faltan datos para enviar el mensaje'
        ]);
        return;
    }

    $sql = "INSERT INTO chat_mensajes (id_alquiler, id_usuario, id_domiciliario, remitente, mensaje) 
            VALUES ($id_alquiler, $id_usuario, $id_domiciliario, '$remitente', '$mensaje')";
    
    if($remitente == "usuario"){
        $token = getUserFMC($mysqli, $id_domiciliario);
    }else{
        $token = getUserFMC($mysqli, $id_usuario);
    }
    
    if ($token) {
        enviarNotificacionFCM($token, "Nuevo Mensaje", "Has recibido un nuevo mensaje", "", "mensaje");
    }
    if ($mysqli->query($sql)) {
        echo json_encode([
            'status' => 'ok',
            'message' => 'Mensaje enviado',
            'id_chat' => $mysqli->insert_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al enviar el mensaje: ' . $mysqli->error
        ]);
    }
}

function get_chat_messages($mysqli, $data) {
    $id_alquiler = intval($data['id_alquiler'] ?? 0);

    if (!$id_alquiler) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de alquiler requerido'
        ]);
        return;
    }

    $sql = "SELECT id_chat, id_alquiler, id_usuario, id_domiciliario, remitente, mensaje, fecha_envio, leido 
            FROM chat_mensajes 
            WHERE id_alquiler = $id_alquiler 
            ORDER BY fecha_envio ASC";

    $result = $mysqli->query($sql);
    $mensajes = [];
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }

    echo json_encode([
        'status' => 'ok',
        'mensajes' => $mensajes
    ]);
}

function mark_chat_read($mysqli, $data) {
    $id_alquiler = intval($data['id_alquiler'] ?? 0);
    $remitente = $mysqli->real_escape_string($data['remitente'] ?? '');

    if (!$id_alquiler || !$remitente) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Faltan datos para marcar como leído'
        ]);
        return;
    }

    $sql = "UPDATE chat_mensajes 
            SET leido = 1 
            WHERE id_alquiler = $id_alquiler AND remitente != '$remitente'";

    if ($mysqli->query($sql)) {
        echo json_encode([
            'status' => 'ok',
            'message' => 'Mensajes marcados como leídos'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al actualizar: ' . $mysqli->error
        ]);
    }
}




function get_banner($mysqli, $data) {
    $result = $mysqli->query("SELECT banner FROM config_general WHERE id = 1 LIMIT 1");

    if ($result && $config = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'ok',
            'banner' => "https://alquilav.com/upload/".$config['banner'] 
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontró configuración'
        ]);
    }
}


function get_config_general($mysqli, $data) {
    $result = $mysqli->query("SELECT *
        FROM config_general WHERE id = 1 LIMIT 1");

    if ($config = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'ok',
            'config' => $config
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontró configuración'
        ]);
    }
}






function check_cancelacion_permitida($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado']);
        return;
    }

    // Obtener cantidad actual de cancelaciones
    $stmt = $mysqli->prepare("SELECT cantidad FROM ban_user WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($cantidad);
    $stmt->fetch();
    $stmt->close();

    $cantidad = $cantidad ?? 0; // Si no existe, asumimos 0

    // Obtener límite desde config_general
    $result = $mysqli->query("SELECT max_intentos_cancelacion FROM config_general LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la configuración']);
        return;
    }
    $row = $result->fetch_assoc();
    $max_intentos = $row['max_intentos_cancelacion'];

    // Comparar
    if ($cantidad >= $max_intentos) {
        echo json_encode(['status' => 'denegado', 'message' => 'Límite de cancelaciones alcanzado']);
    } else {
        echo json_encode([
            'status' => 'ok',
            'message' => 'Cancelación permitida',
            'cancelaciones_realizadas' => $cantidad,
            'max_intentos' => $max_intentos
        ]);
    }
}



function recaudado($mysqli, $data) {
     $user_id = $data['user_id'] ?? 0;
    // status_sevicio 1 = pendiente, 2 = en curso, 3 = por retirar, 4 = finalizado
    $result = $mysqli->query("SELECT monedero FROM usuarios where id = $user_id LIMIT 1");

    if ($usuario = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'recaudado' => $usuario['monedero']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquiler activo']);
    }
}

function get_detail_service_finish($mysqli, $data) {
    $id_alquiler = $data['id_alquiler'] ?? 0;
    $user_id = $data['user_id'] ?? 0;
    

    if (!$id_alquiler) {
        echo json_encode(['status' => 'error', 'message' => 'ID de alquiler no proporcionado']);
        return;
    }

   
    $sql = "
        SELECT 
            alquileres.latitud AS lat_client,
            alquileres.longitud AS long_client,
            u_delivery.latitud AS lat_delivery,
            u_delivery.longitud AS long_delivery,
            alquileres.user_id AS user_id,
            u_cliente.nombre AS nombre,
            u_cliente.direccion AS direccion,
            u_cliente.telefono AS telefono,
            lavadoras.*,
            alquileres.conductor_id,
            precios_lavado.precio,
            alquileres.fecha_inicio,
            alquileres.fecha_fin
        FROM alquileres
        JOIN usuarios AS u_cliente ON alquileres.user_id = u_cliente.id
        LEFT JOIN usuarios AS u_delivery ON $user_id = u_delivery.id
        JOIN lavadoras ON alquileres.lavadora_id = lavadoras.id
        JOIN precios_lavado on precios_lavado.id_negocio = alquileres.negocio_id and lavadoras.type = precios_lavado.tipo_lavadora
        WHERE alquileres.id = $id_alquiler
          AND alquileres.status = 'finalizado'
        LIMIT 1
    ";

    $result = $mysqli->query($sql);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró servicio activo']);
        return;
    }

    $data = $result->fetch_assoc();

    echo json_encode([
        'status' => 'ok',
        'servicio' => $data
    ]);
}

function recoger($mysqli, $data) {
        global $porcentaje;
    $user_id = $data['user_id'] ?? 0;
    $id_alquiler = $data['id_alquiler'] ?? null;
    $total = $data['total'] ?? null;


    if (!$user_id || $id_alquiler === null ) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
            
           $descuento =  calcularPorcentaje($total, $porcentaje);
         
     
          $stmt = $mysqli->prepare("UPDATE usuarios SET monedero = monedero - ?   WHERE id = ?");
            $stmt->bind_param("ii", $descuento,  $user_id);
            $stmt->execute();

    
         $token = getFMCByServicio($mysqli, $id_alquiler, $tipo = 'usuario');
    
        if ($token) {
            enviarNotificacionFCM($token, "Actualización de servicio", "La lavadora ha sido recogida", $id_alquiler, 'update_rental');
        }

    $stmt = $mysqli->prepare("UPDATE alquileres SET status_servicio = 4   WHERE id = ?");
    $stmt->bind_param("i",  $id_alquiler);
    $stmt->execute();

    $result = $mysqli->query("SELECT lavadora_id  FROM alquileres WHERE id = $id_alquiler");
   
    $row = $result->fetch_assoc();
    $lavadora_id = $row['lavadora_id'];

      $stmt = $mysqli->prepare("UPDATE lavadoras SET status = 'disponible'   WHERE id = ?");
    $stmt->bind_param("i",  $lavadora_id);
    $stmt->execute();



    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Servicio aceptado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al aceptar']);
    }

    $stmt->close();
}


function pendiente_recoger($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado']);
        return;
    }

    // Obtener el ID del negocio asignado al conductor
    $result = $mysqli->query("SELECT conductor_negocio FROM usuarios WHERE id = $user_id");
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        return;
    }

    $row = $result->fetch_assoc();
    $conductor_negocio = $row['conductor_negocio'];

    // Buscar si tiene un servicio activo con status_servicio = 1
    $query = $mysqli->query("SELECT * FROM alquileres WHERE negocio_id = $conductor_negocio AND conductor_id = $user_id AND status_servicio = 3 AND status = 'finalizado' LIMIT 1");

    if ($query && $query->num_rows > 0) {
        $servicio = $query->fetch_assoc();

        echo json_encode([
            'status' => 'ok',
            'servicio' => $servicio
        ]);
    } else {
        echo json_encode(['status' => 'ok', 'servicio' => null]);
    }
}

function update_password($mysqli, $data) {
      $contrasena = $data['password'] ?? '';
    $id = $data['id'] ?? 0;

    if (empty($contrasena) || empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        return;
    }

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Contraseña actualizada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la contraseña']);
    }

    $stmt->close();
}

function terminos_delivery($mysqli, $data) {
 
    // status_sevicio 1 = pendiente, 2 = en curso, 3 = por retirar, 4 = finalizado
    $result = $mysqli->query("SELECT terminos_delivery, terminos_uso_delivery FROM terminos_condiciones ");

    if ($terminos = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'terminos' => $terminos]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquiler activo']);
    }
}

function terminos_cliente($mysqli, $data) {
 
    // status_sevicio 1 = pendiente, 2 = en curso, 3 = por retirar, 4 = finalizado
    $result = $mysqli->query("SELECT terminos, terminos_uso FROM terminos_condiciones ");

    if ($terminos = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'terminos' => $terminos]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquiler activo']);
    }
}


function get_ubication_domicialiario_from_deviery($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;

    $result = $mysqli->query("
        SELECT 
            alquileres.latitud AS latitud_servicio, 
            alquileres.longitud AS longitud_servicio, 
            usuarios.latitud AS latitud_delivery, 
            usuarios.longitud AS longitud_delivery 
        FROM usuarios, alquileres 
        WHERE alquileres.conductor_id = usuarios.id 
          AND alquileres.status_servicio = 1 
          AND alquileres.user_id = $user_id
    ");

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'ok',
            'ubication' => [
                'servicio' => [
                    'latitud' => $row['latitud_servicio'],
                    'longitud' => $row['longitud_servicio']
                ],
                'domiciliario' => [
                    'latitud' => $row['latitud_delivery'],
                    'longitud' => $row['longitud_delivery']
                ]
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontraron ubicaciones'
        ]);
    }
}

function forgot_password($mysqli, $data) {
    $email = $data['email'] ?? null;

    if (!$email) {
        echo json_encode(['status' => 'error', 'message' => 'Correo electrónico requerido']);
        return;
    }

    // Verificar si el usuario existe
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Generar un token aleatorio de 10 caracteres
        $token = substr(str_shuffle(str_repeat(
            $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ceil(10 / strlen($x))
        )), 1, 10);

        // Guardar el token en la base de datos
        $stmt1 = $mysqli->prepare("UPDATE usuarios SET tocken_recovery = ? WHERE id = ?");
        $stmt1->bind_param("si", $token, $user['id']);
        $stmt1->execute();
        $stmt1->close(); // <- Asegúrate de cerrar el statement

        // Enviar el correo
        require 'mailRecovery.php';
        $resultado = enviarCorreoRecuperacion($email, $token);

        echo json_encode(['status' => 'ok', 'message' => 'Correo enviado con éxito']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Correo electrónico no registrado']);
    }

    $stmt->close(); // <- Cerrar también este statement al final
}

function servicio_pendiente($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado']);
        return;
    }

    // Obtener el ID del negocio asignado al conductor
    $result = $mysqli->query("SELECT conductor_negocio FROM usuarios WHERE id = $user_id");
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        return;
    }

    $row = $result->fetch_assoc();
    $conductor_negocio = $row['conductor_negocio'];

    // Buscar si tiene un servicio activo con status_servicio = 1
    $query = $mysqli->query("SELECT * FROM alquileres WHERE negocio_id = $conductor_negocio AND conductor_id = $user_id AND status_servicio = 1 AND status = 'activo' LIMIT 1");

    if ($query && $query->num_rows > 0) {
        $servicio = $query->fetch_assoc();

        echo json_encode([
            'status' => 'ok',
            'servicio' => $servicio
        ]);
    } else {
        echo json_encode(['status' => 'ok', 'servicio' => null]);
    }
}


function cancelar_servicio($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;
    $id_alquiler = $data['id_alquiler'] ?? null;
    $id_motivo = $data['motivo'] ?? null;

    if (!$user_id || $id_alquiler === null) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
     $token = getFMCByServicio($mysqli, $service_id, $tipo = 'usuario');
    
    if ($token) {
        enviarNotificacionFCM($token, "Actualización de servicio", "El servicio ha sido cancelado", $id_alquiler, 'update_rental');
    }


     $token = getFMCByServicio($mysqli, $service_id, $tipo = 'domiciliario');
        
        if ($token) {
            enviarNotificacionFCM($token, "Actualización de servicio", "El servicio ha sido cancelado", $id_alquiler, 'update_rental');
        }

    
    
    $stmt = $mysqli->prepare("SELECT cantidad FROM ban_user WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // 2. Si existe, obtener cantidad actual y sumarle 1
        $row = $result->fetch_assoc();
        $cantidadActual = (int)$row['cantidad'];
        $nuevaCantidad = $cantidadActual + 1;
    
        $stmt_update = $mysqli->prepare("UPDATE ban_user SET cantidad = ? WHERE id_user = ?");
        $stmt_update->bind_param("ii", $nuevaCantidad, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // 3. Si no existe, insertar con cantidad = 1
        $cantidadInicial = 1;
        $stmt_insert = $mysqli->prepare("INSERT INTO ban_user (id_user, cantidad) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $user_id, $cantidadInicial);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    

    // Hora actual del servidor
    $fecha_actual = date('Y-m-d H:i:s');

    // Actualizar la tabla alquileres
    $stmt1 = $mysqli->prepare("UPDATE alquileres SET motivo = ?, status_servicio = 5 WHERE id = ?");
    $stmt1->bind_param("ii", $id_motivo, $id_alquiler);
    
    if (!$stmt1->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar alquiler']);
        $stmt1->close();
        return;
    }
    $stmt1->close();

    // Obtener lavadora_id
    $result = $mysqli->query("SELECT lavadora_id FROM alquileres WHERE id = $id_alquiler");
    if (!$result || $result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Lavadora no encontrada']);
        return;
    }
    $row = $result->fetch_assoc();
    $lavadora_id = $row['lavadora_id'];
    
    
        $stmt_check = $mysqli->prepare("SELECT cantidad FROM ban_user WHERE id_user = ?");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Ya existe, actualizar cantidad
        $stmt_check->bind_result($cantidad_actual);
        $stmt_check->fetch();


        $nueva_cantidad = $cantidad_actual + 1;
        $stmt_update = $mysqli->prepare("UPDATE ban_user SET cantidad = ? WHERE id_user = ?");
        $stmt_update->bind_param("ii", $nueva_cantidad, $user_id);
        $stmt_update->execute();
  
    } else {
        // No existe, crear nuevo registro

        $stmt_insert = $mysqli->prepare("INSERT INTO ban_user (id_user, cantidad) VALUES (?, 1)");
        $stmt_insert->bind_param("i", $user_id);
        $stmt_insert->execute();

    }

    // Actualizar la lavadora como alquilada
    $stmt2 = $mysqli->prepare("UPDATE lavadoras SET status = 'disponible' WHERE id = ?");
    $stmt2->bind_param("i", $lavadora_id);
    
    if ($stmt2->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Servicio entregado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar lavadora']);
    }

    $stmt2->close();
}


function get_motivos($mysqli, $data) {

    $result = $mysqli->query("SELECT * FROM motivo ");

    $motivos = [];
    while ($motivo = $result->fetch_assoc()) {
        $motivos[] = $motivo;  
    }

    if (count($motivos) > 0) {
        echo json_encode(['status' => 'ok', 'motivos' => $motivos]);  
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquileres finalizados']);
    }
}


function get_rental_all_delivery($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    $result = $mysqli->query("SELECT * FROM alquileres WHERE conductor_id = $userId ");

    $rentals = [];
    while ($rental = $result->fetch_assoc()) {
        $rentals[] = $rental;  // Añade cada alquiler a la lista de rentals
    }

    if (count($rentals) > 0) {
        echo json_encode(['status' => 'ok', 'rentals' => $rentals]);  // Devuelve la lista de alquileres
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquileres finalizados']);
    }
}

function entregar_servicio($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;
    $id_alquiler = $data['id_alquiler'] ?? null;

    if (!$user_id || $id_alquiler === null) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
         $token = getFMCByServicio($mysqli, $id_alquiler, $tipo = 'usuario');
    
        if ($token) {
            enviarNotificacionFCM($token, "Actualización de servicio", "La lavadora ha sido entregada", $id_alquiler, 'update_rental');
        }


    // Hora actual del servidor
    $fecha_actual = date('Y-m-d H:i:s');

    // Actualizar la tabla alquileres
    $stmt1 = $mysqli->prepare("UPDATE alquileres SET start_time = ?, status_servicio = 2 WHERE id = ?");
    $stmt1->bind_param("si", $fecha_actual, $id_alquiler);
    
    if (!$stmt1->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar alquiler']);
        $stmt1->close();
        return;
    }

    // Obtener lavadora_id
    $result = $mysqli->query("SELECT lavadora_id FROM alquileres WHERE id = $id_alquiler");
    if (!$result || $result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Lavadora no encontrada']);
        return;
    }
    $row = $result->fetch_assoc();
    $lavadora_id = $row['lavadora_id'];

    // Actualizar la lavadora como alquilada
    $stmt2 = $mysqli->prepare("UPDATE lavadoras SET status = 'alquilada' WHERE id = ?");
    $stmt2->bind_param("i", $lavadora_id);
    
    if ($stmt2->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Servicio entregado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar lavadora']);
    }


}




function aceptar_servicio($mysqli, $data) {
    global $porcentaje;
    $user_id = $data['user_id'] ?? 0;
    $id_alquiler = $data['id_alquiler'] ?? null;


    if (!$user_id || $id_alquiler === null ) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
    
       $sql = "
        SELECT 
            total
        FROM alquileres

        WHERE alquileres.id = $id_alquiler
        
        LIMIT 1
    ";

$result = $mysqli->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $total = $row['total'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Alquiler no encontrado']);
    return;
}



    // Hora actual del servidor
    $fecha_actual = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare("UPDATE alquileres SET conductor_id = ?, fecha_aceptado = ?  WHERE id = ?");
    $stmt->bind_param("isi", $user_id, $fecha_actual, $id_alquiler);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Servicio aceptado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al aceptar']);
    }

    $stmt->close();
}

function calcularPorcentaje($valor, $porcentaje) {
    return ($valor * $porcentaje) / 100;
}

function get_servicio_solicitud_domicialiario($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado']);
        return;
    }

    // Obtener el ID del negocio asignado al conductor
    $result = $mysqli->query("SELECT conductor_negocio FROM usuarios WHERE id = $user_id");
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        return;
    }

    $row = $result->fetch_assoc();
    $conductor_negocio = $row['conductor_negocio'];

    // Buscar si tiene un servicio activo con status_servicio = 1
    $query = $mysqli->query("SELECT * FROM alquileres WHERE negocio_id = $conductor_negocio AND conductor_id != $user_id AND status_servicio = 1 AND status = 'activo' LIMIT 1");

    if ($query && $query->num_rows > 0) {
        $servicio = $query->fetch_assoc();

        echo json_encode([
            'status' => 'ok',
            'servicio' => $servicio
        ]);
    } else {
        echo json_encode(['status' => 'ok', 'servicio' => null]);
    }
}

function get_detail_service($mysqli, $data) {
    $id_alquiler = $data['id_alquiler'] ?? 0;
    $user_id = $data['user_id'] ?? 0;
    

    if (!$id_alquiler) {
        echo json_encode(['status' => 'error', 'message' => 'ID de alquiler no proporcionado']);
        return;
    }

    // Consulta para obtener el detalle del servicio asignado al conductor
    $sql = "
        SELECT 
            alquileres.latitud AS lat_client,
            alquileres.longitud AS long_client,
            alquileres.user_id AS user_id,
            
            u_delivery.latitud AS lat_delivery,
            u_delivery.longitud AS long_delivery,
            u_cliente.nombre AS nombre,
            u_cliente.direccion AS direccion,
            u_cliente.telefono AS telefono,
            lavadoras.*,
            alquileres.conductor_id
        FROM alquileres
        JOIN usuarios AS u_cliente ON alquileres.user_id = u_cliente.id
        LEFT JOIN usuarios AS u_delivery ON $user_id = u_delivery.id
        JOIN lavadoras ON alquileres.lavadora_id = lavadoras.id
        WHERE alquileres.id = $id_alquiler
          AND alquileres.status = 'activo'
        LIMIT 1
    ";

    $result = $mysqli->query($sql);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró servicio activo']);
        return;
    }

    $data = $result->fetch_assoc();

    echo json_encode([
        'status' => 'ok',
        'servicio' => $data
    ]);
}

function update_ubicacion_domiciliario($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;
    $latitud = $data['latitud'] ?? null;
    $longitud = $data['longitud'] ?? null;

    if (!$user_id || $latitud === null || $longitud === null) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }

    // Hora actual del servidor
    $fecha_actual = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare("UPDATE usuarios SET latitud = ?, longitud = ?, ultima_actualizacion_ubicacion = ? WHERE id = ?");
    $stmt->bind_param("sssi", $latitud, $longitud, $fecha_actual, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Ubicación actualizada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar ubicación']);
    }

    $stmt->close();
}
function register($mysqli, $data) {

    $nombre = $mysqli->real_escape_string($data['nombre'] ?? '');
    $apellido = $mysqli->real_escape_string($data['apellido'] ?? '');
    $telefono = $mysqli->real_escape_string($data['telefono'] ?? '');
    $direccion = $mysqli->real_escape_string($data['direccion'] ?? '');
    $correo = $mysqli->real_escape_string($data['correo'] ?? '');
    $usuario = $mysqli->real_escape_string($data['usuario'] ?? '');
    $contrasena = $mysqli->real_escape_string($data['password'] ?? '');
    $google_token = $mysqli->real_escape_string($data['google_token'] ?? '');

    if ($correo == '') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Correo requerido']);
        return;
    }

    if ($google_token == '') {
        if ($usuario == '' || $contrasena == '') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Usuario y contraseña requeridos']);
            return;
        }
        $contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
    }

    $query = "INSERT INTO usuarios (nombre, apellido, telefono, direccion, correo, usuario, contrasena, google_token)
              VALUES ('$nombre', '$apellido', '$telefono', '$direccion', '$correo', '$usuario', '$contrasena', '$google_token')";

    if ($mysqli->query($query)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'user_id' => $mysqli->insert_id]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar usuario']);
    }
}

function login($mysqli, $data) {
    $usuario = $mysqli->real_escape_string($data['correo'] ?? '');
    $contrasena = $mysqli->real_escape_string($data['contrasena'] ?? '');

    if ($usuario == '' || $contrasena == '') {
        echo json_encode(['status' => 'error', 'message' => 'Datos requeridos']);
        return;
    }

    $result = $mysqli->query("SELECT * FROM usuarios WHERE correo = '$usuario' LIMIT 1");

    if ($user = $result->fetch_assoc()) {
        if (password_verify($contrasena, $user['contrasena'])) {
            echo json_encode(['status' => 'ok', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }
}

function mark_delivered_or_collected($mysqli, $data, $type) {
    $service_id = (int)($data['service_id'] ?? 0);

    if ($service_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de servicio inválido']);
        return;
    }

    // Define el nuevo estado según el tipo de acción
    $new_status = $type === 'delivered' ? 2 : ($type === 'collected' ? 4 : null);

    if ($new_status === null) {
        echo json_encode(['status' => 'error', 'message' => 'Tipo de acción inválido']);
        return;
    }

    $stmt = $mysqli->prepare("UPDATE alquileres SET status_servicio = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $service_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar estado']);
    }

    $stmt->close();
}


function get_pending_deliveries($mysqli, $data) {
 
    // status_sevicio 1 = pendiente, 2 = en curso, 3 = por retirar, 4 = finalizado
    $result = $mysqli->query("SELECT * FROM alquileres WHERE status_servicio in (1,3) AND conductor_id = 0 LIMIT 1");

    if ($rental = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'rental' => $rental]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquiler activo']);
    }
}

function get_delivery_service($mysqli, $data) {
    $delivery_id = $mysqli->real_escape_string($data['delivery_id'] ?? '');
    // status_sevicio 1 = pendiente, 2 = en curso, 3 = por retirar, 4 = finalizado
    $result = $mysqli->query("SELECT * FROM alquileres WHERE status_servicio in (1,3) AND conductor_id = $delivery_id LIMIT 1");

    if ($rental = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'rental' => $rental]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquiler activo']);
    }
}

function accept_service($mysqli, $data) {

    $delivery_id = $mysqli->real_escape_string($data['delivery_id'] ?? '');
    $service_id = $mysqli->real_escape_string($data['service_id'] ?? '');

 
    $query = "UPDATE alquileres SET conductor_id = $delivery_id WHERE id = $service_id";
    
    $token = getFMCByServicio($mysqli, $service_id, $tipo = 'usuario');
    
    if ($token) {
        enviarNotificacionFCM($token, "Actualización de servicio", "El servicio ha sido aceptado", $service_id, 'update_rental');
    }

    if ($mysqli->query($query)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al aceptar servicio']);
    }
}

function login_domiciliario($mysqli, $data) {
    $usuario = $mysqli->real_escape_string($data['correo'] ?? '');
    $contrasena = $mysqli->real_escape_string($data['contrasena'] ?? '');

    if ($usuario == '' || $contrasena == '') {
        echo json_encode(['status' => 'error', 'message' => 'Datos requeridos']);
        return;
    }

    $result = $mysqli->query("SELECT * FROM usuarios WHERE correo = '$usuario' and id_rol=3 LIMIT 1");

    if ($user = $result->fetch_assoc()) {
        if (password_verify($contrasena, $user['contrasena'])) {
            echo json_encode(['status' => 'ok', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }
}

function login_google($mysqli, $data) {
    $correo = $mysqli->real_escape_string($data['correo'] ?? '');

    if ($correo == '') {
        echo json_encode(['status' => 'error', 'message' => 'Correo requerido']);
        return;
    }

    $result = $mysqli->query("SELECT * FROM usuarios WHERE correo = '$correo' and status=1 LIMIT 1");

    if ($user = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'user' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no registrado']);
    }
}

function rent_machine($mysqli, $data) {
    global $km, $global_tarifa;
    $userId = intval($data['user_id'] ?? 0);
    $tiempo = intval($data['tiempo'] ?? 0);


    if ($userId == 0 || $tiempo == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    $latitud = floatval($data['latitud'] ?? 0);
    $longitud = floatval($data['longitud'] ?? 0);


    // debe existir id_lavadora
    if(!isset($data['id_lavadora'])) {
        $negocios = [];
        $result = $mysqli->query("SELECT id, latitud, longitud FROM negocios");
        while ($row = $result->fetch_assoc()) {
            $distancia = calcularDistancia($latitud, $longitud, $row['latitud'], $row['longitud']);
            if ($distancia <= $km) {
                $row['distancia_km'] = $distancia;
                $negocios[] = $row;
            }
        }
        
        if (!empty($negocios)) {
            $ids_negocios = array_column($negocios, 'id');
            $ids_sql = implode(',', array_map('intval', $ids_negocios)); // sanitizar IDs
        
            $result = $mysqli->query("SELECT * FROM lavadoras WHERE status = 'disponible' AND negocio_id IN ($ids_sql) LIMIT 1");
           
            if ($lavadora = $result->fetch_assoc()) {
             
            } else {
                echo json_encode(["mensaje" => "No hay lavadoras disponibles en negocios cercanos"]);
                die();
            }
        } else {
            echo json_encode(["mensaje" => "No hay negocios dentro de $km km"]);
            die();
        }
    }else{
        $result = $mysqli->query("SELECT * FROM lavadoras WHERE status = 'disponible' AND id = {$data['id_lavadora']} LIMIT 1");
        
    }
 
    if ($lavadora = $result->fetch_assoc() || isset($data['id_lavadora'])) {
        
        if(isset($data['id_lavadora'])){
            $result = $mysqli->query("SELECT * FROM lavadoras WHERE status = 'disponible' AND id =".$data['id_lavadora']);
     
            $lavadora = $result->fetch_assoc();
        }

        $lavadoraId = $lavadora['id'];
        $negocio = $lavadora['negocio_id'];
        $metodo = $data['payment_method'];
        $total_amount = $data['total_amount'];


        $result = $mysqli->query("SELECT * FROM config WHERE id_negocio = $negocio LIMIT 1");
        $config = $result->fetch_assoc();
        $tarifa =  $global_tarifa;
        if ($config) {
            $tarifa = $config['tarifa'];
        }
        
      

        $mysqli->query("UPDATE lavadoras SET status = 'alquilada' WHERE id = $lavadoraId");
        $mensaje = "Se registro nuevo alquiler";
        $notify = "INSERT INTO notificaciones (mensaje, negocio)
                  VALUES ('$mensaje', $negocio)";
        $mysqli->query($notify);
        $query = "INSERT INTO alquileres (user_id, lavadora_id, tiempo_alquiler, status, fecha_inicio, latitud, longitud, valor_servicio, negocio_id, metodo_pago, total)
                  VALUES ($userId, $lavadoraId, $tiempo, 'activo', NOW(), '$latitud', '$longitud', $tarifa, $negocio, '$metodo', $total_amount)";

        if ($mysqli->query($query)) {
            echo json_encode(['status' => 'ok', 'lavadora_id' => $lavadoraId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al realizar alquiler']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay lavadoras disponibles']);
    }
}

function sum_rent_machine($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    if ($userId <= 0) {
        return [
            'status' => 'error',
            'message' => 'ID de usuario inválido'
        ];
    }

    // Verificar el tiempo adicional actual
    $check = $mysqli->query("SELECT adicional_time FROM alquileres WHERE user_id = $userId AND status = 'activo' LIMIT 1");

    if ($check && $row = $check->fetch_assoc()) {
        $adicional = intval($row['adicional_time']);

        if ($adicional >= 2) {
            echo json_encode( [
                'status' => 'error',
                'message' => 'No se puede aumentar más el tiempo adicional (máximo 2)'
            ]);
        }

        // Si es menor que 2, actualizamos
        $sql = "UPDATE alquileres 
                SET tiempo_alquiler = tiempo_alquiler + 1, adicional_time = adicional_time + 1 
                WHERE user_id = $userId AND status = 'activo' LIMIT 1";

        if ($mysqli->query($sql)) {
            echo json_encode( [
                'status' => 'ok',
                'message' => 'Tiempo adicional agregado'
            ]);
        } else {
            echo json_encode( [
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $mysqli->error
            ]);
        }
    }


}


function finish_rental($mysqli, $data) {
    $rentalId = intval($data['rental_id'] ?? 0);

    if ($rentalId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de alquiler requerido']);
        return;
    }

    $result = $mysqli->query("SELECT lavadora_id FROM alquileres WHERE id = $rentalId AND status = 'activo' LIMIT 1");

        $token = getFMCByServicio($mysqli, $rentalId, $tipo = 'domiciliario');
    
        if ($token) {
            enviarNotificacionFCM($token, "Actualización de servicio", "Servicio finalizado, pendiente de recogida", $rentalId, 'update_rental');
        }

    if ($rental = $result->fetch_assoc()) {
        $lavadoraId = $rental['lavadora_id'];

        $mysqli->query("UPDATE lavadoras SET status = 'disponible' WHERE id = $lavadoraId");
        $mysqli->query("UPDATE alquileres SET status = 'finalizado', status_servicio = 3, fecha_fin = NOW() WHERE id = $rentalId");

        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Alquiler no encontrado o ya finalizado']);
    }
}

function get_user($mysqli, $data) {
    $userId = intval($data['id'] ?? 0);

    $result = $mysqli->query("SELECT * FROM usuarios WHERE id = $userId LIMIT 1");

    if ($user = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'user' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }
}

function get_rental($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    $result = $mysqli->query("SELECT * FROM alquileres WHERE user_id = $userId AND status_servicio in (1,2,3) LIMIT 1");

    if ($rental = $result->fetch_assoc()) {
        echo json_encode(['status' => 'ok', 'rental' => $rental]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquiler activo']);
    }
}

function get_rental_all($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    $result = $mysqli->query("SELECT * FROM alquileres WHERE user_id = $userId AND status = 'finalizado'");

    $rentals = [];
    while ($rental = $result->fetch_assoc()) {
        $rentals[] = $rental;  // Añade cada alquiler a la lista de rentals
    }

    if (count($rentals) > 0) {
        echo json_encode(['status' => 'ok', 'rentals' => $rentals]);  // Devuelve la lista de alquileres
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay alquileres finalizados']);
    }
}


function lavadoras_asignadas($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    $result = $mysqli->query("SELECT lavadoras.*, precios_lavado.precio from lavadoras, precios_lavado where lavadoras.negocio_id = precios_lavado.id_negocio and lavadoras.type = precios_lavado.tipo_lavadora and tipo_servicio='normal' and $userId = lavadoras.id_domiciliario");

    $asings = [];
    while ($asing = $result->fetch_assoc()) {
        $asings[] = $asing;  // Añade cada alquiler a la lista de rentals
    }

    if (count($asings) > 0) {
        echo json_encode(['status' => 'ok', 'asignadas' => $asings]);  // Devuelve la lista de alquileres
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay lavadoras asignadas']);
    }
}


function edit_user($mysqli, $data) {
    $userId = intval($data['id'] ?? 0);
    $nombre = $mysqli->real_escape_string($data['nombre'] ?? '');
    $apellido = $mysqli->real_escape_string($data['apellido'] ?? '');
    $telefono = $mysqli->real_escape_string($data['telefono'] ?? '');
    $direccion = $mysqli->real_escape_string($data['direccion'] ?? '');

    if ($userId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario requerido']);
        return;
    }

    $query = "UPDATE usuarios SET nombre = '$nombre', apellido = '$apellido', telefono = '$telefono', direccion = '$direccion' WHERE id = $userId";

    if ($mysqli->query($query)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al editar usuario']);
    }
}


function available_machines($mysqli, $data) {
    global $km, $global_tarifa, $valor_minimo;
    $latitud = floatval($data['latitud'] ?? 0);
    $longitud = floatval($data['longitud'] ?? 0);

  if ($latitud == 0 || $longitud == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }

   $tipos_lavadora = [
    'Manual doble tina sin bomba',
    'Manual doble tina con bomba',
    'Automática de 18 libras',
    'Automática de 24 libras'
];

$response = [];

$tipos_lavadora = [
    'Manual doble tina sin bomba',
    'Manual doble tina con bomba',
    'Automática de 18 libras',
    'Automática de 24 libras'
];

$response = ['status' => 'ok', 'data' => []];

foreach ($tipos_lavadora as $tipo) {
    // Buscar lavadoras disponibles de ese tipo
    $query = "SELECT lavadoras.*, usuarios.latitud, usuarios.longitud,  usuarios.monedero FROM lavadoras
    JOIN usuarios ON lavadoras.id_domiciliario = usuarios.id
     WHERE lavadoras.status = 'disponible' AND lavadoras.type = '$tipo'";
    $result = $mysqli->query($query);
    $disponibles = $result->num_rows;

    $lavadora = $result->fetch_assoc(); // Tomamos la primera lavadora como ejemplo

    if ($lavadora) {
        $id_negocio = $lavadora['negocio_id'];
        $id_lavadora = $lavadora['id'];

        // Obtener todas las tarifas para este tipo de lavadora y negocio
        $tarifas_query = "SELECT tipo_servicio, precio FROM precios_lavado 
                          WHERE tipo_lavadora = '$tipo' AND id_negocio = $id_negocio";
        $tarifas_result = $mysqli->query($tarifas_query);

        $tarifas = [
            'normal' => 0,
            '24horas' => 0,
            'nocturno' => 0
        ];
        $is_in_range = estaDentroDelRango($latitud, $longitud, $lavadora['latitud'], $lavadora['longitud'], $km);
       
        if ($is_in_range and $lavadora['monedero'] >=  $valor_minimo) {
            $tarifas['normal'] = $global_tarifa;
     
        while ($row = $tarifas_result->fetch_assoc()) {
            $tipo_servicio = $row['tipo_servicio'];
            $tarifas[$tipo_servicio] = (float)$row['precio'];
        }

        $response['data'][] = [
            'type' => $tipo,
            'disponibles' => $disponibles,
            'id_lavadora' => (int)$id_lavadora,
            'tarifas' => $tarifas
        ];
    }else{

  $response['data'][] = [
            'type' => $tipo,
            'disponibles' => 0,
            'id_lavadora' => 0,
            'tarifas' => [
                'normal' => 0,
                '24horas' => 0,
                'nocturno' => 0
            ]
        ];

    }
    } else {
        $response['data'][] = [
            'type' => $tipo,
            'disponibles' => 0,
            'id_lavadora' => 0,
            'tarifas' => [
                'normal' => 0,
                '24horas' => 0,
                'nocturno' => 0
            ]
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
return $response;
}

function estaDentroDelRango($lat1, $lon1, $lat2, $lon2, $km_maximo) {
    $radioTierra = 6371; // Radio de la Tierra en kilómetros

    // Convertir grados a radianes
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Fórmula de Haversine
    $difLat = $lat2 - $lat1;
    $difLon = $lon2 - $lon1;

    $a = sin($difLat / 2) * sin($difLat / 2) +
         cos($lat1) * cos($lat2) *
         sin($difLon / 2) * sin($difLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distancia = $radioTierra * $c;

    // Retorna true si está dentro del rango
    return $distancia <= $km_maximo;
}

function get_ubication_domicialiario($mysqli, $data) {
    $user_id = $data['user_id'] ?? 0;
    $result = $mysqli->query("SELECT latitud, longitud FROM usuarios WHERE id = $user_id");
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'ok', "ubication" => [ 'latitud' => $row['latitud'], 'longitud' => $row['longitud']]]);
}

function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $radioTierra = 6371; // Radio de la tierra en km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $radioTierra * $c;
}


function simulate_collection($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    $temporalUserDelivery = 25;

    if ($userId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario requerido']);
        return;
    }
    // actualizo el servicio y la fecha de inicio del servicio a la fecha actual
    $query = "UPDATE alquileres SET  status_servicio = 4 WHERE user_id = $userId ";


    if ($mysqli->query($query)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al editar usuario']);
    }
}



function simulate_delivery($mysqli, $data) {
    $userId = intval($data['user_id'] ?? 0);

    $temporalUserDelivery = 25;

    if ($userId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario requerido']);
        return;
    }
    // actualizo el servicio y la fecha de inicio del servicio a la fecha actual
    $query = "UPDATE alquileres SET start_time = NOW(), conductor_id = $temporalUserDelivery, status_servicio = 2 WHERE user_id = $userId ";


    if ($mysqli->query($query)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al editar usuario']);
    }
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

function log_api($mysqli, $accion, $entrada, $salida) {
    $stmt = $mysqli->prepare("INSERT INTO api_logs (accion, entrada, salida) VALUES (?, ?, ?)");
    $entrada_json = json_encode($entrada);
    $salida_json = json_encode($salida);
    $stmt->bind_param("sss", $accion, $entrada_json, $salida_json);
    $stmt->execute();
    $stmt->close();
}


?>
