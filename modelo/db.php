<?php
function conect(){
    $servername = "localhost";  // Cambia estos datos según tu configuración
    $username = "root";
    $password = "";
    $dbname = "lavadora";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    return $conn;
}
?>