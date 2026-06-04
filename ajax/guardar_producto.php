<?php
// ajax/guardar_producto.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']); // Convertimos a número con decimales de forma segura

    try {
        // Estado por defecto es 1 (Activo)
        $sql = "INSERT INTO catalogo (nombre, precio, estado) VALUES (:nombre, :precio, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':precio' => $precio]);

        header("Location: ../admin/catalogo.php?success=creado");
        exit();
    } catch (PDOException $e) {
        die("Error al guardar producto: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>