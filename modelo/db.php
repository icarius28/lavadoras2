<?php
function conect(){
    $servername = "localhost";  // Cambia estos datos según tu configuración
    $username = "alquilav_ndb";
    $password = "&^L1s,)Z_W56";
    $dbname = "alquilav_ndb";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    return $conn;
}
?>