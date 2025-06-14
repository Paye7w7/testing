<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 2) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

require_once '../config/conexion.php';
$conexion = obtenerConexion();

if (!isset($_GET['hotel_id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

$hotelId = intval($_GET['hotel_id']);

// Obtener información del hotel
$stmtHotel = $conexion->prepare("SELECT nombre, comunidad FROM hoteles WHERE id_hotel = ? AND activo = 1");
$stmtHotel->bind_param("i", $hotelId);
$stmtHotel->execute();
$resultHotel = $stmtHotel->get_result();

if ($resultHotel->num_rows === 0) {
    echo '<div class="alert alert-warning">Hotel no encontrado o no disponible.</div>';
    exit();
}

$hotel = $resultHotel->fetch_assoc();

// Obtener habitaciones disponibles del hotel
$sql = "SELECT h.*, th.nombre as tipo_habitacion 
        FROM habitaciones h
        INNER JOIN tipos_habitaciones th ON h.id_tipo_habitacion = th.id_tipo_habitacion
        WHERE h.id_hotel = ? AND h.activo = 1 AND h.estado = 'libre'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $hotelId);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo '<div class="alert alert-info">No hay habitaciones disponibles en este momento.</div>';
    exit();
}
?>

<div class="mb-4">
    <h4><?php echo htmlspecialchars($hotel['nombre']); ?></h4>
    <p class="text-muted"><?php echo htmlspecialchars($hotel['comunidad']); ?></p>
</div>

<div class="row row-cols-1 row-cols-md-2 g-4">
    <?php while ($habitacion = $resultado->fetch_assoc()): ?>
        <?php
        $precio_dolares = $habitacion['precio_noche'];
        $precio_bs = $precio_dolares * 6.96; // Tipo de cambio fijo
        ?>
        <div class="col">
            <div class="card h-100">
                <div class="row g-0">
                    <?php if (!empty($habitacion['imagenes'])): ?>
                        <?php
                        $imagenes = explode(',', $habitacion['imagenes']);
                        $primeraImagen = $imagenes[0];
                        ?>
                        <div class="col-md-5">
                            <img src="../../img/habitaciones/<?php echo htmlspecialchars($primeraImagen); ?>"
                                class="img-fluid rounded-start habitacion-img h-100 w-100"
                                alt="Habitación <?php echo htmlspecialchars($habitacion['numero_habitacion']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="col-md-5 bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-7">
                        <div class="card-body">
                            <h5 class="card-title">Habitación <?php echo htmlspecialchars($habitacion['numero_habitacion']); ?></h5>
                            <p class="card-text">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($habitacion['tipo_habitacion']); ?></span>
                                <span class="badge bg-success">Piso: <?php echo htmlspecialchars($habitacion['piso']); ?></span>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-currency-dollar"></i>
                                <strong><?php echo number_format($precio_dolares, 2); ?></strong> por noche
                                <br>
                                <span class="text-muted small">(Bs. <?php echo number_format($precio_bs, 2); ?>)</span>
                            </p>

                            <div class="mt-3">
                                <a href="reservas.php?id_habitacion=<?php echo $habitacion['id_habitacion']; ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-calendar-plus"></i> Reservar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
    function reservarHabitacion(idHabitacion) {
        // Aquí puedes implementar la lógica para reservar
        alert('Función de reserva para la habitación ' + idHabitacion + ' será implementada');
        // window.location.href = `reservar.php?habitacion_id=${idHabitacion}`;
    }
</script>