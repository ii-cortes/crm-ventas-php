<?php
// ajax/guardar_producto.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    
    // Captura limpia y sanitizada del modelo completo de datos
    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
    $descripcion_larga = trim($_POST['descripcion_larga'] ?? '');

    // Validación básica perimetral
    if (empty($nombre) || empty($tipo) || empty($precio) || empty($descripcion_corta)) {
        die("Error: Todos los campos descriptivos obligatorios deben ser completados.");
    }

    try {
        // Almacenamos el registro con su estado activo (1) por defecto
        $sql = "INSERT INTO catalogo (nombre, tipo, precio, descripcion_corta, descripcion_larga, estado) 
                VALUES (:nombre, :tipo, :precio, :descripcion_corta, :descripcion_larga, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':precio' => $precio,
            ':descripcion_corta' => $descripcion_corta,
            ':descripcion_larga' => $descripcion_larga
        ]);

        header("Location: ../admin/catalogo.php?success=creado");
        exit();

    } catch (PDOException $e) {
        die("Error crítico al insertar el producto en el catálogo SQL: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>