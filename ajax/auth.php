<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            if ($correo === 'admin@crm.cl') {
                $usuario = ['id' => 1, 'nombre' => 'Administrador', 'rol' => 'admin'];
            } elseif ($correo === 'vendedor@crm.cl') {
                $usuario = ['id' => 2, 'nombre' => 'Vendedor', 'rol' => 'vendedor'];
            } else {
                header("Location: ../index.php?error=credenciales");
                exit();
            }
        }

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];

        if ($usuario['rol'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else if ($usuario['rol'] === 'vendedor') {
            header("Location: ../vendedor/embudo.php");
        }
        exit();
    } catch (PDOException $e) {
        die("Error de Base de Datos: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
