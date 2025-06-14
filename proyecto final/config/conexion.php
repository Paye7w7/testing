<?php
function obtenerConexion() {
    $host = '127.0.0.1';
    $port = 3306;
    $database = 'sistema_hoteles';
    $username = 'root';
    $password = '13760584';

    $conexion = new mysqli($host, $username, $password, $database, $port);
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    return $conexion;
}
?>
