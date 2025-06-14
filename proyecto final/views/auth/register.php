<?php
session_start();
if(isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            overflow: hidden;
        }
        .avatar-initials {
            font-size: 36px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="logo">
                <h2>Registro de Usuario</h2>
            </div>
            
            <?php if(isset($_SESSION['error_registro'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_registro']; unset($_SESSION['error_registro']); ?></div>
            <?php endif; ?>
            
            <form action="../../controllers/registerController.php" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6">
                        <label for="gmail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="gmail" name="gmail" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                    </div>
                    <div class="col-md-6">
                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="col-md-6">
                        <label for="contraseña" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="imagen" class="form-label">Foto de perfil (opcional)</label>
                    <div class="avatar-preview" id="avatarPreview">
                        <div class="avatar-initials" id="avatarInitials">IN</div>
                    </div>
                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Registrarse</button>
            </form>
            <div class="mt-3 text-center">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar iniciales dinámicas
        document.getElementById('nombre').addEventListener('input', updateInitials);
        document.getElementById('apellido_paterno').addEventListener('input', updateInitials);
        
        function updateInitials() {
            const nombre = document.getElementById('nombre').value;
            const apellido = document.getElementById('apellido_paterno').value;
            let initials = '';
            
            if (nombre.length > 0) initials += nombre[0].toUpperCase();
            if (apellido.length > 0) initials += apellido[0].toUpperCase();
            
            document.getElementById('avatarInitials').textContent = initials || 'IN';
        }
        
        // Mostrar vista previa de la imagen seleccionada
        document.getElementById('imagen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = `<img src="${event.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>