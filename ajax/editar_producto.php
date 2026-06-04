<?php
// ajax/editar_producto.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    $id = $_POST['id_producto'];
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);

    try {
        $sql = "UPDATE catalogo SET nombre = :nombre, precio = :precio WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':precio' => $precio, ':id' => $id]);

        header("Location: ../admin/catalogo.php?success=editado");
        exit();
    } catch (PDOException $e) {
        die("Error al editar producto: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>