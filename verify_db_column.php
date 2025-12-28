<?php
$host = "localhost";
$user = "alquilav_ndb";
$password = "&^L1s,)Z_W56";
$dbname = "alquilav_ndb";

$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SHOW COLUMNS FROM config_general LIKE 'max_intentos_cancelacion'");

if ($result && $result->num_rows > 0) {
    echo "Column exists. Test Passed.\n";
    // Also try to select it
    $q = $mysqli->query("SELECT max_intentos_cancelacion FROM config_general LIMIT 1");
    if ($row = $q->fetch_assoc()) {
        echo "Value: " . $row['max_intentos_cancelacion'] . "\n";
    }
} else {
    echo "Column DOES NOT exist. Test Failed.\n";
}
$mysqli->close();
?>
