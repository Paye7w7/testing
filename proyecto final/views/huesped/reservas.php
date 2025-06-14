<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 2) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/conexion.php';
$conexion = obtenerConexion();

if (!isset($_GET['id_habitacion'])) {
    echo "Habitación no especificada.";
    exit();
}

$id_habitacion = intval($_GET['id_habitacion']);
$id_usuario = $_SESSION['usuario']['id_usuario'];

// Obtener datos de la habitación
$sql = "SELECT * FROM habitaciones WHERE id_habitacion = ? AND estado = 'libre' AND activo = 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_habitacion);
$stmt->execute();
$result = $stmt->get_result();
$habitacion = $result->fetch_assoc();

if (!$habitacion) {
    echo "Habitación no disponible.";
    exit();
}

// Conversión de dólares a bolivianos
$precio_dolares = $habitacion['precio_noche'];
$tipo_cambio = 6.96;
$precio_bolivianos = $precio_dolares * $tipo_cambio;

// Fecha mínima para los inputs (hoy)
$hoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container mt-4">
        <h3>Reservar Habitación <?php echo htmlspecialchars($habitacion['numero_habitacion']); ?></h3>

        <form action="../../controllers/reservasController.php" method="POST" id="formReserva">
            <input type="hidden" name="id_habitacion" value="<?php echo $id_habitacion; ?>" />
            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>" />
            <input type="hidden" name="precio_noche" id="precio_noche" value="<?php echo $precio_dolares; ?>" />

            <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required
                    min="<?php echo $hoy; ?>" />
            </div>

            <div class="mb-3">
                <label for="fecha_fin" class="form-label">Fecha de fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required
                    min="<?php echo $hoy; ?>" />
            </div>

            <div class="mb-3">
                <p><strong>Precio por noche:</strong> 
                    $<?php echo number_format($precio_dolares, 2); ?> USD / Bs <?php echo number_format($precio_bolivianos, 2); ?>
                </p>
            </div>

            <div class="mb-3">
                <p><strong>Monto total:</strong> 
                    <span id="monto_total_usd">0.00</span> USD / 
                    <span id="monto_total_bs">0.00</span> Bs
                </p>
            </div>

            <button type="submit" name="reservar" class="btn btn-primary" id="btnReservar" disabled>
                Confirmar Reserva
            </button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        const montoUsd = document.getElementById('monto_total_usd');
        const montoBs = document.getElementById('monto_total_bs');
        const precioNoche = parseFloat(document.getElementById('precio_noche').value);
        const tipoCambio = <?php echo $tipo_cambio; ?>;
        const btnReservar = document.getElementById('btnReservar');

        function calcularMonto() {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);

            // Solo calcular si ambas fechas son válidas
            if (fechaInicio.value && fechaFin.value) {
                if (fin > inicio) {
                    const diffTime = fin - inicio;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);

                    const totalUsd = diffDays * precioNoche;
                    const totalBs = totalUsd * tipoCambio;

                    montoUsd.textContent = totalUsd.toFixed(2);
                    montoBs.textContent = totalBs.toFixed(2);

                    btnReservar.disabled = false;
                } else {
                    // Fecha fin debe ser mayor a inicio
                    montoUsd.textContent = '0.00';
                    montoBs.textContent = '0.00';
                    btnReservar.disabled = true;
                }
            } else {
                montoUsd.textContent = '0.00';
                montoBs.textContent = '0.00';
                btnReservar.disabled = true;
            }
        }

        fechaInicio.addEventListener('change', () => {
            // Ajustar mínimo para fecha_fin que sea >= fecha_inicio + 1 día
            if (fechaInicio.value) {
                const minFin = new Date(fechaInicio.value);
                minFin.setDate(minFin.getDate() + 1);
                fechaFin.min = minFin.toISOString().split('T')[0];

                // Si la fecha_fin actual es menor que el mínimo, limpiar
                if (fechaFin.value && fechaFin.value < fechaFin.min) {
                    fechaFin.value = '';
                }
            } else {
                fechaFin.min = '<?php echo $hoy; ?>';
            }
            calcularMonto();
        });

        fechaFin.addEventListener('change', calcularMonto);
    </script>
</body>

</html>
