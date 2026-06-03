<?php
// ajax/avanzar_etapa3.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    $id_cliente = $_POST['id_cliente'];
    $observaciones = trim($_POST['observaciones']);
    $id_vendedor = $_SESSION['usuario_id'];

    try {
        // Actualizamos la etapa a '3' y guardamos las observaciones
        $sql = "UPDATE clientes 
                SET observaciones = :observaciones, etapa_actual = '3' 
                WHERE id = :id_cliente AND id_vendedor = :id_vendedor AND etapa_actual = '2'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':observaciones' => $observaciones,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        header("Location: ../vendedor/embudo.php");
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar a la etapa 3: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>