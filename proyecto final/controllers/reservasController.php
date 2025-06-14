<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reservar'])) {
    $id_habitacion = intval($_POST['id_habitacion']);
    $id_usuario = intval($_POST['id_usuario']);
    $precio_noche = floatval($_POST['precio_noche']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validación básica de fechas
    if ($fecha_inicio >= $fecha_fin) {
        echo "La fecha de fin debe ser posterior a la fecha de inicio.";
        exit();
    }

    // Calcular noches y monto total
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $noches = $inicio->diff($fin)->days;
    $monto_total = $noches * $precio_noche;

    $conexion = obtenerConexion();

    // Insertar en la tabla reservas
    $sql = "INSERT INTO reservas (fecha_inicio, fecha_fin, monto_total, estado, id_habitacion, id_usuario, activo)
            VALUES (?, ?, ?, 'realizada', ?, ?, 1)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdii", $fecha_inicio, $fecha_fin, $monto_total, $id_habitacion, $id_usuario);

    if ($stmt->execute()) {
        // Marcar habitación como ocupada
        $conexion->query("UPDATE habitaciones SET estado = 'ocupada' WHERE id_habitacion = $id_habitacion");

        header("Location: ../views/huesped/index.php?reserva=ok"); //aqui se pone donde quiere ir despues de hacer la reserva(se puede poner otra ruta como la del pago)
        exit();
    } else {
        echo "Error al guardar la reserva: " . $conexion->error;
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    exit();
}
