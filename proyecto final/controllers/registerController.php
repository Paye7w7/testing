<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellido_paterno = $_POST['apellido_paterno'] ?? '';
    $apellido_materno = $_POST['apellido_materno'] ?? '';
    $gmail = $_POST['gmail'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $id_rol = 2; // Rol de huésped

    // Validar datos básicos
    if (empty($nombre) || empty($apellido_paterno) || empty($gmail) || empty($contraseña)) {
        $_SESSION['error_registro'] = "Todos los campos obligatorios deben ser completados";
        header("Location: ../views/auth/register.php");
        exit();
    }

    // Verificar si el correo ya existe
    $conexion = obtenerConexion();
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE gmail = ?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $_SESSION['error_registro'] = "El correo electrónico ya está registrado";
        header("Location: ../views/auth/register.php");
        exit();
    }

    // Procesar imagen
    $imagen = null;
    $directorioImagenes = "D:/7mo/proyecto final/img/usuarios/";
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '.' . $extension;
        $rutaCompleta = $directorioImagenes . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
            $imagen = $nombreArchivo;
        }
    } else {
        // Si GD no está disponible, simplemente guardamos null en la imagen
        $imagen = null;
        
        // Alternativa: puedes usar una imagen por defecto
        // $imagen = 'default-avatar.png';
        // (necesitarías tener esta imagen en tu carpeta de imágenes)
    }

    // Hash de la contraseña
    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    // Insertar usuario en la base de datos
    $stmt = $conexion->prepare("INSERT INTO usuarios (id_rol, imagen, gmail, contraseña, apellido_paterno, apellido_materno, telefono, nombre) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $id_rol, $imagen, $gmail, $contraseñaHash, $apellido_paterno, $apellido_materno, $telefono, $nombre);
    
    if ($stmt->execute()) {
        // Iniciar sesión automáticamente después del registro
        $id_usuario = $conexion->insert_id;
        $_SESSION['usuario'] = [
            'id' => $id_usuario,
            'nombre' => $nombre,
            'apellido_paterno' => $apellido_paterno,
            'apellido_materno' => $apellido_materno,
            'gmail' => $gmail,
            'imagen' => $imagen,
            'rol' => $id_rol
        ];
        
        header("Location: ../views/huesped/index.php");
        exit();
    } else {
        $_SESSION['error_registro'] = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
        header("Location: ../views/auth/register.php");
        exit();
    }
} else {
    header("Location: ../views/auth/register.php");
    exit();
}
?>