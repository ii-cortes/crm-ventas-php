<?php
// ajax/auth.php

// 1. Iniciamos la sesión para poder recordar quién es el usuario mientras navega
session_start();

// 2. Traemos nuestra conexión a la base de datos
require_once '../includes/db.php';

// 3. Verificamos que los datos vengan del formulario (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturamos el correo ingresado
    $correo = $_POST['correo'] ?? '';
    
    try {
        // 4. Preparamamos la consulta segura (evitando Inyección SQL)
        $stmt = $pdo->prepare("SELECT id, nombre, correo, rol FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 5. Validamos si el usuario existe
        if ($usuario) {
            // ¡Éxito! Guardamos sus datos en variables de sesión "globales"
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Redirigimos dependiendo del rol (Como pide el requerimiento)
            if ($usuario['rol'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../vendedor/dashboard.php");
            }
            exit();
        } else {
            // Falla el login: El correo no existe en la BD. 
            // Lo devolvemos al index con un mensaje de error en la URL
            header("Location: ../index.php?error=1");
            exit();
        }
    } catch (PDOException $e) {
        die("Error en la consulta: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar a este archivo directamente por la URL, lo echamos al login
    header("Location: ../index.php");
    exit();
}
?>