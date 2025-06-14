<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el usuario está logueado y es huésped (rol 2)
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 2) {
        header("Location: ../views/auth/login.php");
        exit();
    }

    // Recoger datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $comunidad = $_POST['comunidad'] ?? '';
    $id_tipo_hotel = $_POST['id_tipo_hotel'] ?? '';
    $informacion = $_POST['informacion'] ?? '';
    $id_usuario = $_POST['id_usuario'] ?? '';
    $activo = 1; // Hotel activo por defecto

    // Validar datos básicos
    if (empty($nombre) || empty($comunidad) || empty($id_tipo_hotel) || empty($informacion)) {
        $_SESSION['error_hotel'] = "Todos los campos obligatorios deben ser completados";
        header("Location: ../views/huesped/registro-hotel.php");
        exit();
    }

    // Procesar imagen
    $imagen = null;
    $directorioImagenes = "D:/7mo/proyecto final/img/hoteles/";

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '.' . $extension;
        $rutaCompleta = $directorioImagenes . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
            $imagen = $nombreArchivo;
        } else {
            $_SESSION['error_hotel'] = "Error al subir la imagen del hotel";
            header("Location: ../views/huesped/registro-hotel.php");
            exit();
        }
    } else {
        $_SESSION['error_hotel'] = "Debes subir una imagen del hotel";
        header("Location: ../views/huesped/registro-hotel.php");
        exit();
    }

    // Insertar hotel en la base de datos
    $conexion = obtenerConexion();
    $stmt = $conexion->prepare("INSERT INTO hoteles (imagen, id_tipo_hotel, activo, id_usuario, nombre, comunidad, informacion, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("siiisss", $imagen, $id_tipo_hotel, $activo, $id_usuario, $nombre, $comunidad, $informacion);

    if ($stmt->execute()) {

        // Obtener el ID del hotel recién insertado
        $id_hotel = $stmt->insert_id;

        // Insertar servicios seleccionados
        if (!empty($_POST['servicios']) && is_array($_POST['servicios'])) {
            $serviciosSeleccionados = $_POST['servicios'];
            $stmtServicio = $conexion->prepare("INSERT INTO hoteles_servicios (id_hotel, id_servicio) VALUES (?, ?)");
            foreach ($serviciosSeleccionados as $id_servicio) {
                $stmtServicio->bind_param("ii", $id_hotel, $id_servicio);
                $stmtServicio->execute();
            }
        }

        // Verificar si se ingresó un nuevo servicio personalizado
        if (!empty($_POST['otro_servicio'])) {
            $otroServicio = trim($_POST['otro_servicio']);
            if ($otroServicio !== '') {
                // Insertar el nuevo servicio en la tabla servicios
                $stmtNuevoServicio = $conexion->prepare("INSERT INTO servicios (activo, nombre) VALUES (1, ?)");
                $stmtNuevoServicio->bind_param("s", $otroServicio);
                if ($stmtNuevoServicio->execute()) {
                    $idNuevoServicio = $stmtNuevoServicio->insert_id;

                    // Insertar en hoteles_servicios también
                    $stmtInsertPivot = $conexion->prepare("INSERT INTO hoteles_servicios (id_hotel, id_servicio) VALUES (?, ?)");
                    $stmtInsertPivot->bind_param("ii", $id_hotel, $idNuevoServicio);
                    $stmtInsertPivot->execute();
                }
            }
        }


        // Actualizar el rol del usuario a administrador (1)
        $stmt = $conexion->prepare("UPDATE usuarios SET id_rol = 1 WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        // Actualizar la sesión con el nuevo rol
        $_SESSION['usuario']['rol'] = 1;

        $_SESSION['exito_hotel'] = "Hotel registrado exitosamente. Ahora eres administrador.";
        header("Location: ../views/admin/index.php");
        exit();
    } else {
        $_SESSION['error_hotel'] = "Error al registrar el hotel. Por favor, inténtalo de nuevo.";
        header("Location: ../views/huesped/registro-hotel.php");
        exit();
    }
} else {
    header("Location: ../views/huesped/registro-hotel.php");
    exit();
}
