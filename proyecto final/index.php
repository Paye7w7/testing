<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: views/auth/login.php");
    exit();
}

if ($_SESSION['usuario']['id_rol'] == 1) {
    header("Location: views/admin/index.php");
} else {
    header("Location: views/huesped/index.php");
}
exit();
?>
