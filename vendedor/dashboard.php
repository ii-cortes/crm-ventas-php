<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="p-5 mb-4 bg-light rounded-3 shadow">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Bienvenido Vendedor: <?php echo $_SESSION['usuario_nombre']; ?></h1>
            <p class="col-md-8 fs-4">Aquí veremos tus gráficos de rendimiento y embudo de ventas.</p>
            <a href="../logout.php" class="btn btn-danger btn-lg">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>