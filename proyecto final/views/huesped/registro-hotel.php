<?php
session_start();
require_once '../../config/conexion.php';

// Verificar si el usuario está logueado y es huésped (rol 2)
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 2) {
    header("Location: ../auth/login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Hotel - Sistema de Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hotel-form {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .preview-image {
            max-width: 300px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>

<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container">
        <div class="hotel-form">
            <h2 class="mb-4">Registra tu Hotel</h2>

            <?php if (isset($_SESSION['error_hotel'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_hotel'];
                                                unset($_SESSION['error_hotel']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['exito_hotel'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['exito_hotel'];
                                                    unset($_SESSION['exito_hotel']); ?></div>
            <?php endif; ?>

            <form action="../../controllers/registroHotelController.php" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre del Hotel*</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6">
                        <label for="comunidad" class="form-label">Comunidad/Localidad*</label>
                        <input type="text" class="form-control" id="comunidad" name="comunidad" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="id_tipo_hotel" class="form-label">Tipo de Hotel*</label>
                    <select class="form-select" id="id_tipo_hotel" name="id_tipo_hotel" required>
                        <option value="" selected disabled>Seleccione un tipo</option>
                        <?php
                        $conexion = obtenerConexion();
                        $tipos = $conexion->query("SELECT * FROM tipos_hoteles");
                        while ($tipo = $tipos->fetch_assoc()):
                        ?>
                            <option value="<?php echo $tipo['id_tipo_hotel']; ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Servicios disponibles*</label>
                    <?php
                    $servicios = $conexion->query("SELECT * FROM servicios WHERE activo = 1");
                    while ($servicio = $servicios->fetch_assoc()):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicios[]" value="<?php echo $servicio['id_servicio']; ?>" id="servicio_<?php echo $servicio['id_servicio']; ?>">
                            <label class="form-check-label" for="servicio_<?php echo $servicio['id_servicio']; ?>">
                                <?php echo htmlspecialchars($servicio['nombre']); ?>
                            </label>
                        </div>

                    <?php endwhile; ?>
                </div>

                <div class="mb-3">
                    <label for="otro_servicio" class="form-label">Otro servicio (opcional)</label>
                    <input type="text" class="form-control" name="otro_servicio" id="otro_servicio" placeholder="Ej: Spa, Piscina, etc.">
                </div>



                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen del Hotel*</label>
                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
                    <img id="preview" class="preview-image" src="#" alt="Vista previa de la imagen">
                </div>

                <div class="mb-3">
                    <label for="informacion" class="form-label">Información del Hotel*</label>
                    <textarea class="form-control" id="informacion" name="informacion" rows="5" required></textarea>
                </div>

                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">

                <button type="submit" class="btn btn-primary w-100 py-2">Registrar Hotel</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar vista previa de la imagen seleccionada
        document.getElementById('imagen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>