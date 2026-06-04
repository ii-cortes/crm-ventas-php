<?php
// ajax/editar_prospecto.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    $id_cliente = $_POST['id_cliente'];
    $id_vendedor = $_SESSION['usuario_id']; 
    
    $nombre = trim($_POST['nombre_completo']);
    $telefono = trim($_POST['telefono']);
    $comuna = trim($_POST['comuna']);

    try {
        // ACTUALIZAMOS SOLO NOMBRE, TELÉFONO Y COMUNA
        $sql = "UPDATE clientes 
                SET nombre_completo = :nombre, 
                    telefono = :telefono, 
                    comuna = :comuna 
                WHERE id = :id_cliente AND id_vendedor = :id_vendedor";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':comuna' => $comuna,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        header("Location: ../vendedor/embudo.php?success=contacto_editado");
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar el cliente: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>