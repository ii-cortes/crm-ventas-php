<?php
// ajax/estado_producto.php
session_start();
require_once '../includes/db.php';

// Debug: Verificar si el script recibe los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: El script solo acepta peticiones POST.");
}

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    die("Error: No tienes permisos de administrador.");
}

$id = $_POST['id_producto'] ?? null;
$estado_actual = $_POST['estado_actual'] ?? null;

if (!$id) {
    die("Error: No se recibió el ID del producto.");
}

// Inversión lógica: si estado actual es 1, pasamos a 0. Si no, a 1.
$nuevo_estado = ($estado_actual == 1) ? 0 : 1;

try {
    $sql = "UPDATE catalogo SET estado = :estado WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);

    // Redirección exitosa
    header("Location: ../admin/catalogo.php?success=1");
    exit();
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>