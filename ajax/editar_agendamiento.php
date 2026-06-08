<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    $id_cliente = $_POST['id_cliente'];
    $id_vendedor = $_SESSION['usuario_id'];
    
    $fecha_cita = $_POST['fecha_cita'];
    $hora_cita = $_POST['hora_cita'];
    $correo = trim($_POST['correo']);

    try {
        $sql = "UPDATE clientes 
                SET fecha_cita = :fecha_cita, 
                    hora_cita = :hora_cita,
                    correo = :correo
                WHERE id = :id_cliente AND id_vendedor = :id_vendedor AND etapa_actual = '2'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita,
            ':correo' => $correo,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        header("Location: ../vendedor/embudo.php?success=agenda_actualizada");
        exit();

    } catch (PDOException $e) {
        die("Error al reprogramar la cita: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>