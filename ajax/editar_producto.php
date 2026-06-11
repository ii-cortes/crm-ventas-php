<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {

    $id = $_POST['id_producto'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
    $descripcion_larga = trim($_POST['descripcion_larga'] ?? '');

    if (empty($id) || empty($nombre) || empty($tipo) || empty($precio) || empty($descripcion_corta)) {
        die("Error: Faltan parámetros clave para procesar la actualización del catálogo.");
    }

    try {
        $sql = "UPDATE catalogo 
                SET nombre = :nombre, 
                    tipo = :tipo, 
                    precio = :precio, 
                    descripcion_corta = :descripcion_corta, 
                    descripcion_larga = :descripcion_larga 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':precio' => $precio,
            ':descripcion_corta' => $descripcion_corta,
            ':descripcion_larga' => $descripcion_larga,
            ':id' => $id
        ]);

        header("Location: ../admin/catalogo.php?success=editado");
        exit();
    } catch (PDOException $e) {
        die("Error crítico al actualizar el catálogo SQL: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
