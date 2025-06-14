<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = $_POST['gmail'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';

    $conexion = obtenerConexion();
    
    // Preparar la consulta para evitar inyección SQL
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE gmail = ?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($contraseña, $usuario['contraseña'])) {
            // Obtener el hotel asociado al usuario (si es administrador)
            $hotelUsuario = null;
            if ($usuario['id_rol'] == 1) { // Si es admin
                $stmtHotel = $conexion->prepare("SELECT * FROM hoteles WHERE id_usuario = ? AND activo = 1 LIMIT 1");
                $stmtHotel->bind_param("i", $usuario['id_usuario']);
                $stmtHotel->execute();
                $resultadoHotel = $stmtHotel->get_result();
                $hotelUsuario = $resultadoHotel->fetch_assoc();
            }
            
            // Iniciar sesión
            $_SESSION['usuario'] = [
                'id_usuario' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'apellido_paterno' => $usuario['apellido_paterno'],
                'apellido_materno' => $usuario['apellido_materno'],
                'gmail' => $usuario['gmail'],
                'imagen' => $usuario['imagen'],
                'rol' => $usuario['id_rol'],
                'id_hotel' => $hotelUsuario ? $hotelUsuario['id_hotel'] : null,
                'nombre_hotel' => $hotelUsuario ? $hotelUsuario['nombre'] : null
            ];
            
            // Redirigir según el rol
            if ($usuario['id_rol'] == 1) {
                header("Location: ../views/admin/index.php");
            } else {
                header("Location: ../views/huesped/index.php");
            }
            exit();
        }
    }
    
    // Si llega aquí es porque falló la autenticación
    $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos";
    header("Location: ../views/auth/login.php");
    exit();
} else {
    header("Location: ../views/auth/login.php");
    exit();
}
?>