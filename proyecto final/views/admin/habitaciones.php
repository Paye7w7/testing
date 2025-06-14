<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/conexion.php';
$conexion = obtenerConexion();

$usuario = $_SESSION['usuario'];
$id_hotel_usuario = $usuario['id_hotel'] ?? null;
$nombre_hotel_usuario = $usuario['nombre_hotel'] ?? null;

if (!$id_hotel_usuario) {
    die("No tiene un hotel asignado. No puede gestionar habitaciones.");
}

// Obtener tipos de habitaciones activos
$sqlTipos = "SELECT * FROM tipos_habitaciones WHERE activo = 1";
$resultTipos = $conexion->query($sqlTipos);

// Control para editar
$habitacionEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = intval($_GET['editar']);
    $stmt = $conexion->prepare("SELECT * FROM habitaciones WHERE id_habitacion = ? AND id_hotel = ?");
    $stmt->bind_param("ii", $idEditar, $id_hotel_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $habitacionEditar = $res->fetch_assoc();
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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Habitaciones - <?php echo htmlspecialchars($nombre_hotel_usuario); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .hotel-display {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container mt-4">
        <h1>Gestión de Habitaciones</h1>
        <div class="hotel-display">
            Hotel: <?php echo htmlspecialchars($nombre_hotel_usuario); ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h3><?php echo $habitacionEditar ? 'Editar Habitación' : 'Crear Habitación'; ?></h3>
                <form action="../../controllers/habitacionesController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $habitacionEditar ? 'editar' : 'crear'; ?>">
                    <?php if ($habitacionEditar): ?>
                        <input type="hidden" name="id_habitacion" value="<?php echo $habitacionEditar['id_habitacion']; ?>">
                    <?php endif; ?>

                    <input type="hidden" name="id_hotel" value="<?php echo $id_hotel_usuario; ?>">

                    <div class="mb-3">
                        <label for="numero_habitacion" class="form-label">Número de Habitación</label>
                        <input type="text" class="form-control" name="numero_habitacion" id="numero_habitacion" required
                            value="<?php echo $habitacionEditar ? htmlspecialchars($habitacionEditar['numero_habitacion']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="precio_noche" class="form-label">Precio por noche</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="precio_noche" id="precio_noche" required
                            value="<?php echo $habitacionEditar ? htmlspecialchars($habitacionEditar['precio_noche']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="piso" class="form-label">Piso</label>
                        <select name="piso" id="piso" class="form-select" required>
                            <?php
                            $pisos = [
                                'planta baja' => 'Planta baja',
                                'primer piso' => 'Primer piso',
                                'segundo piso' => 'Segundo piso',
                                'tercer piso' => 'Tercer piso',
                            ];
                            $pisoSeleccionado = $habitacionEditar ? $habitacionEditar['piso'] : '';
                            foreach ($pisos as $valor => $nombre) {
                                $selected = ($pisoSeleccionado === $valor) ? 'selected' : '';
                                echo "<option value=\"$valor\" $selected>$nombre</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_tipo_habitacion" class="form-label">Tipo de Habitación</label>
                        <select name="id_tipo_habitacion" id="id_tipo_habitacion" class="form-select" required>
                            <option value="">Seleccione un tipo</option>
                            <?php 
                            $resultTipos->data_seek(0); // Reiniciar el puntero para volver a recorrer
                            while ($tipo = $resultTipos->fetch_assoc()): ?>
                                <option value="<?php echo $tipo['id_tipo_habitacion']; ?>"
                                    <?php echo ($habitacionEditar && $habitacionEditar['id_tipo_habitacion'] == $tipo['id_tipo_habitacion']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="libre" <?php echo ($habitacionEditar && $habitacionEditar['estado'] == 'libre') ? 'selected' : ''; ?>>Libre</option>
                            <option value="ocupada" <?php echo ($habitacionEditar && $habitacionEditar['estado'] == 'ocupada') ? 'selected' : ''; ?>>Ocupada</option>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="activo" id="activo"
                            <?php echo (!$habitacionEditar || $habitacionEditar['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>

                    <div class="mb-3">
                        <label for="imagenes" class="form-label">Imágenes de la habitación</label>
                        <input type="file" class="form-control" name="imagenes[]" id="imagenes" multiple accept="image/*">
                        <?php if ($habitacionEditar && $habitacionEditar['imagenes']): ?>
                            <div class="mt-2">
                                <strong>Imágenes actuales:</strong><br>
                                <?php
                                $imagenesArray = explode(',', $habitacionEditar['imagenes']);
                                foreach ($imagenesArray as $img) {
                                    echo '<img src="../../img/habitaciones/' . htmlspecialchars($img) . '" alt="imagen" style="height:80px; margin-right:5px;">';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary"><?php echo $habitacionEditar ? 'Actualizar' : 'Crear'; ?></button>
                    <?php if ($habitacionEditar): ?>
                        <a href="habitaciones.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="col-md-6">
                <h3>Lista de Habitaciones</h3>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Precio/Noche</th>
                            <th>Piso</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Activo</th>
                            <th>Imágenes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($habitacion = $resultHabitaciones->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $habitacion['id_habitacion']; ?></td>
                                <td><?php echo htmlspecialchars($habitacion['numero_habitacion']); ?></td>
                                <td><?php echo number_format($habitacion['precio_noche'], 2); ?></td>
                                <td><?php echo $habitacion['piso']; ?></td>
                                <td><?php echo htmlspecialchars($habitacion['tipo_nombre']); ?></td>
                                <td><?php echo ucfirst($habitacion['estado']); ?></td>
                                <td><?php echo $habitacion['activo'] ? 'Sí' : 'No'; ?></td>
                                <td>
                                    <?php
                                    if ($habitacion['imagenes']) {
                                        $imgs = explode(',', $habitacion['imagenes']);
                                        foreach ($imgs as $img) {
                                            echo '<img src="../../img/habitaciones/' . htmlspecialchars($img) . '" alt="img" style="height:40px; margin-right:3px;">';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="habitaciones.php?editar=<?php echo $habitacion['id_habitacion']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="../../controllers/habitacionesController.php?desactivar=<?php echo $habitacion['id_habitacion']; ?>"
                                        class="btn btn-sm btn-<?php echo $habitacion['activo'] ? 'danger' : 'success'; ?>"
                                        onclick="return confirm('¿Seguro quieres <?php echo $habitacion['activo'] ? 'desactivar' : 'activar'; ?> esta habitación?');">
                                        <?php echo $habitacion['activo'] ? 'Desactivar' : 'Activar'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>