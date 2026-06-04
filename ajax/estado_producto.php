<?php
// ajax/estado_producto.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    $id = $_POST['id_producto'];
    $estado_actual = $_POST['estado_actual'];
    
    // Inversión lógica: si es 1, pasa a 0. Si es 0, pasa a 1.
    $nuevo_estado = ($estado_actual == 1) ? 0 : 1;

    try {
        $sql = "UPDATE catalogo SET estado = :estado WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);

        header("Location: ../admin/catalogo.php?success=estado_actualizado");
        exit();
    } catch (PDOException $e) {
        die("Error al cambiar estado: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>