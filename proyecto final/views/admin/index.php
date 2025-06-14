<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 1) {
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
    <title>Panel de Administración - Sistema de Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if($usuario['imagen']): ?>
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
                        <p class="text-muted">Administrador</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="welcome-section">
                    <h1>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                    <p class="lead">Panel de Administración del Sistema de Hoteles.</p>
                    <p>Desde aquí podrás gestionar tu hotel, reservas y configuraciones.</p>
                    
                    <div class="mt-4">
                        <a href="gestion-hotel.php" class="btn btn-primary me-2">Gestionar mi hotel</a>
                        <a href="#" class="btn btn-outline-secondary">Ver reservas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>