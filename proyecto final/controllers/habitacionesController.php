<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/conexion.php';
$conexion = obtenerConexion();

$usuario = $_SESSION['usuario'];
$id_hotel_usuario = $usuario['id_hotel'] ?? null;

if (!$id_hotel_usuario) {
    die("No tiene un hotel asignado. No puede gestionar habitaciones.");
}

// Obtener tipos de habitaciones activos
$sqlTipos = "SELECT * FROM tipos_habitaciones WHERE activo = 1";
$resultTipos = $conexion->query($sqlTipos);

// Función para subir imágenes
function subirImagenes($files) {
    $carpetaDestino = __DIR__ . '/../img/habitaciones/';
    $nombres = [];
    foreach ($files['name'] as $index => $name) {
        $tmpName = $files['tmp_name'][$index];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $nuevoNombre = uniqid('hab_') . '.' . $ext;
        if (move_uploaded_file($tmpName, $carpetaDestino . $nuevoNombre)) {
            $nombres[] = $nuevoNombre;
        }
    }
    return implode(',', $nombres);
}

// Crear habitación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    // Verificar que el hotel enviado coincide con el del usuario
    if ($_POST['id_hotel'] != $id_hotel_usuario) {
        die("No tiene permisos para crear habitaciones en este hotel");
    }

    $precio_noche = $_POST['precio_noche'];
    $piso = $_POST['piso'];
    $id_tipo_habitacion = $_POST['id_tipo_habitacion'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $id_hotel = $_POST['id_hotel'];
    $numero_habitacion = $_POST['numero_habitacion'];
    $estado = $_POST['estado'];

    $imagenesSubidas = '';
    if (!empty($_FILES['imagenes']['name'][0])) {
        $imagenesSubidas = subirImagenes($_FILES['imagenes']);
    }

    $stmt = $conexion->prepare("INSERT INTO habitaciones (precio_noche, piso, id_tipo_habitacion, activo, id_hotel, numero_habitacion, estado, imagenes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("diiiisss", $precio_noche, $piso, $id_tipo_habitacion, $activo, $id_hotel, $numero_habitacion, $estado, $imagenesSubidas);
    $stmt->execute();
    header("Location: ../views/admin/habitaciones.php");
    exit();
}

// Obtener habitación para editar
$habitacionEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = intval($_GET['editar']);
    $stmt = $conexion->prepare("SELECT * FROM habitaciones WHERE id_habitacion = ? AND id_hotel = ?");
    $stmt->bind_param("ii", $idEditar, $id_hotel_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $habitacionEditar = $resultado->fetch_assoc();

    if (!$habitacionEditar) {
        die("No tiene permisos para editar esta habitación");
    }
}

// Editar habitación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $id_habitacion = $_POST['id_habitacion'];
    
    // Verificar que la habitación pertenece al hotel del usuario
    $stmtCheck = $conexion->prepare("SELECT id_hotel FROM habitaciones WHERE id_habitacion = ?");
    $stmtCheck->bind_param("i", $id_habitacion);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $habitacionCheck = $resCheck->fetch_assoc();
    
    if (!$habitacionCheck || $habitacionCheck['id_hotel'] != $id_hotel_usuario) {
        die("No tiene permisos para editar esta habitación");
    }

    $precio_noche = $_POST['precio_noche'];
    $piso = $_POST['piso'];
    $id_tipo_habitacion = $_POST['id_tipo_habitacion'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $id_hotel = $_POST['id_hotel'];
    $numero_habitacion = $_POST['numero_habitacion'];
    $estado = $_POST['estado'];

    // Obtener imágenes actuales
    $stmt = $conexion->prepare("SELECT imagenes FROM habitaciones WHERE id_habitacion = ?");
    $stmt->bind_param("i", $id_habitacion);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $imagenesActuales = $row['imagenes'];

    $imagenesNuevas = $imagenesActuales;

    if (!empty($_FILES['imagenes']['name'][0])) {
        $imagenesSubidas = subirImagenes($_FILES);
        if ($imagenesNuevas) {
            $imagenesNuevas .= ',' . $imagenesSubidas;
        } else {
            $imagenesNuevas = $imagenesSubidas;
        }
    }

    $stmt = $conexion->prepare("UPDATE habitaciones SET precio_noche = ?, piso = ?, id_tipo_habitacion = ?, activo = ?, id_hotel = ?, numero_habitacion = ?, estado = ?, imagenes = ? WHERE id_habitacion = ?");
    $stmt->bind_param("diiiisssi", $precio_noche, $piso, $id_tipo_habitacion, $activo, $id_hotel, $numero_habitacion, $estado, $imagenesNuevas, $id_habitacion);
    $stmt->execute();

    header("Location: ../views/admin/habitaciones.php");
    exit();
}

// Cambiar estado activo/desactivar
if (isset($_GET['desactivar'])) {
    $idDesactivar = intval($_GET['desactivar']);
    
    // Verificar que la habitación pertenece al hotel del usuario
    $stmtCheck = $conexion->prepare("SELECT id_hotel, activo FROM habitaciones WHERE id_habitacion = ?");
    $stmtCheck->bind_param("i", $idDesactivar);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $row = $resCheck->fetch_assoc();
    
    if (!$row || $row['id_hotel'] != $id_hotel_usuario) {
        die("No tiene permisos para modificar esta habitación");
    }
    
    $nuevoEstado = $row['activo'] ? 0 : 1;

    $stmt = $conexion->prepare("UPDATE habitaciones SET activo = ? WHERE id_habitacion = ?");
    $stmt->bind_param("ii", $nuevoEstado, $idDesactivar);
    $stmt->execute();

    header("Location: ../views/admin/habitaciones.php");
    exit();
}

// Listar habitaciones del hotel del usuario
$sqlHabitaciones = "SELECT h.*, ht.nombre AS tipo_nombre, ho.nombre AS hotel_nombre
                    FROM habitaciones h
                    INNER JOIN tipos_habitaciones ht ON h.id_tipo_habitacion = ht.id_tipo_habitacion
                    INNER JOIN hoteles ho ON h.id_hotel = ho.id_hotel
                    WHERE h.id_hotel = ?";
$stmtHabitaciones = $conexion->prepare($sqlHabitaciones);
$stmtHabitaciones->bind_param("i", $id_hotel_usuario);
$stmtHabitaciones->execute();
$resultHabitaciones = $stmtHabitaciones->get_result();