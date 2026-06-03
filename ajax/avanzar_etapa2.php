<?php
// ajax/avanzar_etapa2.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    $id_cliente = $_POST['id_cliente'];
    $correo = trim($_POST['correo']);
    $id_vendedor = $_SESSION['usuario_id'];

    // 1. Capturamos los dos campos nuevos
    $fecha = $_POST['fecha_cita']; // Ej: 2026-06-03
    $hora = $_POST['hora_cita'];   // Ej: 15:30
    
    // 2. Los concatenamos (unimos) separándolos con un espacio y agregando ":00" para los segundos
    // Resultado final: "2026-06-03 15:30:00" (Exactamente lo que MySQL necesita)
    $fecha_hora_mysql = $fecha . ' ' . $hora . ':00';

    try {
        // Actualizamos el correo, la etapa y ahora también guardamos la fecha en un campo (si tuviéramos uno, 
        // pero por ahora el requerimiento solo pide cambiar a Etapa 2 y guardar el correo).
        // Nota: Si en el futuro agregas una columna 'fecha_agendada' en la BD, usarías $fecha_hora_mysql aquí.
        
        $sql = "UPDATE clientes 
                SET correo = :correo, etapa_actual = '2' 
                WHERE id = :id_cliente AND id_vendedor = :id_vendedor AND etapa_actual = '1'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':correo' => $correo,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        header("Location: ../vendedor/embudo.php");
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar la etapa: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>