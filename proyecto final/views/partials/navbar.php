<style>
/* Estilos personalizados para el navbar */
.navbar-custom {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-bottom: 3px solid #f8f9fa;
    padding: 0.8rem 0;
}

.navbar-brand {
    font-weight: 700 !important;
    font-size: 1.5rem !important;
    color: #ffffff !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.navbar-brand:hover {
    color: #ffd700 !important;
    transform: scale(1.05);
}

.navbar-nav .nav-link {
    color: #e8f4f8 !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    margin: 0 0.2rem;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
}

.navbar-nav .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffd700 !important;
    transform: translateY(-2px);
}

.navbar-nav .nav-link::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #ffd700;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.navbar-nav .nav-link:hover::before {
    width: 80%;
}

.dropdown-toggle::after {
    color: #ffd700;
    margin-left: 0.5rem;
}

.dropdown-menu {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    padding: 0.5rem 0;
    margin-top: 0.5rem;
}

.dropdown-item {
    color: #333 !important;
    padding: 0.7rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 0.2rem 0.5rem;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #2a5298, #1e3c72);
    color: white !important;
    transform: translateX(5px);
}

.dropdown-divider {
    border-color: #dee2e6;
    margin: 0.5rem 1rem;
}

.navbar-toggler {
    border: 2px solid #ffd700;
    padding: 0.4rem 0.6rem;
    border-radius: 8px;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23ffd700' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Efectos adicionales */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive improvements */
@media (max-width: 991.98px) {
    .navbar-nav {
        padding: 1rem 0;
    }
    
    .navbar-nav .nav-link {
        margin: 0.2rem 0;
        text-align: center;
    }
    
    .dropdown-menu {
        background: rgba(30, 60, 114, 0.95);
        border-radius: 8px;
    }
    
    .dropdown-item {
        color: #e8f4f8 !important;
    }
    
    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffd700 !important;
    }
}

</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">
            Sistema de Hoteles
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../admin/index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Reservas</a>
                </li>
                <?php if($_SESSION['usuario']['rol'] == 1): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Administración
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="../admin/habitaciones.php">Habitaciones</a></li>
                            <li><a class="dropdown-item" href="../admin/comentarios.php">Comentarios</a></li>
                            <li><a class="dropdown-item" href="../admin/pagos.php">Pagos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../admin/reportes.php">Reportes</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <?php if($_SESSION['usuario']['rol'] == 2): ?>
                            <li><a class="dropdown-item" href="../huesped/registro-hotel.php">Registra tu hotel</a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="#">Mi perfil</a></li>
                        <li><a class="dropdown-item" href="../configuracion/ajustes.php">Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../../logout.php">Cerrar sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>