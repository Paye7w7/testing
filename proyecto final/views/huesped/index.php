<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 2) {
    header("Location: ../auth/login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

require_once '../../config/conexion.php';
$conexion = obtenerConexion();

// Obtener los hoteles activos
$sql = "SELECT * FROM hoteles WHERE activo = 1";
$resultado = $conexion->query($sql);

// Función para obtener ícono por nombre de servicio
function obtenerIconoServicio($nombre) {
    $nombre = strtolower($nombre);
    if (strpos($nombre, 'wifi') !== false) return 'bi-wifi';
    if (strpos($nombre, 'restaurante') !== false) return 'bi-cup-hot';
    if (strpos($nombre, 'comedor') !== false) return 'bi-egg-fried';
    if (strpos($nombre, 'transporte') !== false) return 'bi-car-front';
    if (strpos($nombre, 'jardín') !== false || strpos($nombre, 'jardin') !== false) return 'bi-flower1';
    return 'bi-star';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Sistema de Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .welcome-section {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .hotel-img {
            height: 200px;
            object-fit: cover;
        }

        .badge i {
            margin-right: 4px;
        }
        
        .habitacion-img {
            height: 150px;
            object-fit: cover;
        }
        
        .modal-habitaciones .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if (!empty($usuario['imagen'])): ?>
                            <img src="../../img/usuarios/<?php echo $usuario['imagen']; ?>" class="profile-img mb-3" alt="Foto de perfil">
                        <?php else: ?>
                            <div class="profile-img mb-3 bg-secondary d-flex align-items-center justify-content-center text-white">
                                <?php
                                $iniciales = substr($usuario['nombre'], 0, 1) . substr($usuario['apellido_paterno'], 0, 1);
                                echo strtoupper($iniciales);
                                ?>
                            </div>
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']); ?></h4>
                        <p class="text-muted">Huésped</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="welcome-section">
                    <h1>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                    <p class="lead">Gracias por ser parte de nuestro sistema de hoteles.</p>
                    <p>Desde aquí podrás realizar reservas, ver tus estadías anteriores y gestionar tu perfil.</p>

                    <div class="mt-4">
                        <a href="#" class="btn btn-primary me-2">Realizar reserva</a>
                        <a href="#" class="btn btn-outline-secondary">Ver mis reservas</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Hoteles -->
        <div class="row mt-5">
            <h2 class="mb-4">Hoteles disponibles</h2>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($hotel = $resultado->fetch_assoc()): ?>
                    <?php
                    $idHotel = $hotel['id_hotel'];
                    
                    // Obtener servicios del hotel
                    $stmtServicios = $conexion->prepare("
                        SELECT s.nombre 
                        FROM servicios s
                        INNER JOIN hoteles_servicios hs ON s.id_servicio = hs.id_servicio
                        WHERE hs.id_hotel = ? AND s.activo = 1");
                    $stmtServicios->bind_param("i", $idHotel);
                    $stmtServicios->execute();
                    $resultServicios = $stmtServicios->get_result();
                    $servicios = [];
                    while ($rowServicio = $resultServicios->fetch_assoc()) {
                        $servicios[] = $rowServicio['nombre'];
                    }
                    
                    // Obtener cantidad de habitaciones disponibles
                    $stmtHabitaciones = $conexion->prepare("
                        SELECT COUNT(*) as total 
                        FROM habitaciones 
                        WHERE id_hotel = ? AND activo = 1 AND estado = 'libre'");
                    $stmtHabitaciones->bind_param("i", $idHotel);
                    $stmtHabitaciones->execute();
                    $resultHabitaciones = $stmtHabitaciones->get_result();
                    $totalHabitaciones = $resultHabitaciones->fetch_assoc()['total'];
                    ?>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($hotel['imagen'])): ?>
                                <img src="../../img/hoteles/<?php echo $hotel['imagen']; ?>" class="card-img-top hotel-img" alt="Imagen del hotel">
                            <?php else: ?>
                                <div class="hotel-img bg-light d-flex align-items-center justify-content-center text-muted">
                                    Sin imagen
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($hotel['nombre']); ?></h5>
                                <p class="card-text"><strong>Ubicación:</strong> <?php echo htmlspecialchars($hotel['comunidad']); ?></p>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($hotel['informacion'])); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-door-open"></i> 
                                        <?php echo $totalHabitaciones; ?> habitaciones disponibles
                                    </span>
                                </div>
                                
                                <?php if (!empty($servicios)): ?>
                                    <div class="mt-3">
                                        <h6 class="text-primary">Servicios disponibles:</h6>
                                        <ul class="list-inline">
                                            <?php foreach ($servicios as $servicio): ?>
                                                <li class="list-inline-item badge bg-light text-dark border mb-1">
                                                    <i class="bi <?php echo obtenerIconoServicio($servicio); ?>"></i>
                                                    <?php echo htmlspecialchars($servicio); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer text-center">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" 
                                    data-bs-target="#modalHabitaciones" 
                                    data-hotel-id="<?php echo $idHotel; ?>"
                                    data-hotel-nombre="<?php echo htmlspecialchars($hotel['nombre']); ?>">
                                    Ver habitaciones
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No hay hoteles disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para mostrar habitaciones -->
    <div class="modal fade" id="modalHabitaciones" tabindex="-1" aria-labelledby="modalHabitacionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHabitacionesLabel">Habitaciones disponibles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoHabitaciones">
                        <!-- Aquí se cargarán las habitaciones dinámicamente -->
                        <div class="text-center my-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p>Cargando habitaciones...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Manejar la apertura del modal
        document.addEventListener('DOMContentLoaded', function() {
            const modalHabitaciones = document.getElementById('modalHabitaciones');
            
            modalHabitaciones.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const hotelId = button.getAttribute('data-hotel-id');
                const hotelNombre = button.getAttribute('data-hotel-nombre');
                
                // Actualizar el título del modal
                const modalTitle = modalHabitaciones.querySelector('.modal-title');
                modalTitle.textContent = `Habitaciones disponibles - ${hotelNombre}`;
                
                // Cargar las habitaciones via AJAX
                fetch(`../../controllers/habitacionesHuespedController.php?hotel_id=${hotelId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('contenidoHabitaciones').innerHTML = data;
                    })
                    .catch(error => {
                        document.getElementById('contenidoHabitaciones').innerHTML = `
                            <div class="alert alert-danger">
                                Error al cargar las habitaciones. Por favor, intente nuevamente.
                            </div>
                        `;
                        console.error('Error:', error);
                    });
            });
            
            // Limpiar contenido al cerrar el modal
            modalHabitaciones.addEventListener('hidden.bs.modal', function() {
                document.getElementById('contenidoHabitaciones').innerHTML = `
                    <div class="text-center my-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p>Cargando habitaciones...</p>
                    </div>
                `;
            });
        });
    </script>
</body>

</html>