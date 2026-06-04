<?php
// ajax/auth.php
session_start();
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    // Ignoramos la clave inventada para cumplir con el requerimiento de "Simular Ingreso"

    try {
        // Buscamos si el usuario existe en la BD
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si la base de datos está vacía, usamos un "Mock" (Simulación en duro)
        if (!$usuario) {
            if ($correo === 'admin@crm.cl') {
                $usuario = ['id' => 1, 'nombre' => 'Administrador', 'rol' => 'admin'];
            } elseif ($correo === 'vendedor@crm.cl') {
                $usuario = ['id' => 2, 'nombre' => 'Vendedor', 'rol' => 'vendedor'];
            } else {
                // Solo rechaza si el correo no es ni de admin ni de vendedor
                header("Location: ../index.php?error=credenciales");
                exit();
            }
        }

        // Se inician las variables de sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];

        // Redirección correcta según el rol
        if ($usuario['rol'] === 'admin') {
            header("Location: ../admin/catalogo.php"); 
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
?>