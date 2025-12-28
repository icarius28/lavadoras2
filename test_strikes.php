<?php
// Script de prueba para verificar API get_user_strikes
$url = 'http://localhost/lavadora/api/api.php?action=get_user_strikes';
$data = ['user_id' => 3]; // Usamos un ID que probablemente exista, o 0 para probar error

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Respuesta de la API:\n";
echo $result;
?>
