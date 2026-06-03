<?php
// index.php
// Iniciamos la sesión para poder guardar los datos del usuario una vez que ingrese
session_start();

// Si el usuario ya está logueado, lo redirigimos a su dashboard correspondiente
if (isset($_SESSION['usuario_rol'])) {
    if ($_SESSION['usuario_rol'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: vendedor/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CRM Funeraria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Un poco de CSS personalizado para centrar el formulario (UI) */
        body {
            background-color: #f4f6f9; /* Color de fondo estilo AdminLTE */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 400px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="text-center mb-4">
        <h2><b>CRM</b> Ventas</h2>
        <p class="text-muted">Ingresa tus datos para iniciar sesión</p>
    </div>

    <form action="ajax/auth.php" method="POST">
        <div class="mb-3">
            <label for="correo" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@crm.cl" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="******" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>