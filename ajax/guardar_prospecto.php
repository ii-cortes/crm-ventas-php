<?php
// ajax/guardar_prospecto.php
session_start();
require_once '../includes/db.php';

// Validación de seguridad: Solo peticiones POST de usuarios con rol vendedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    // Captura y limpieza de espacios en blanco de los 3 datos obligatorios de Etapa 1
    $id_vendedor = $_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre_completo']);
    $telefono = trim($_POST['telefono']);
    $comuna = trim($_POST['comuna']);
    
    try {
        // CORRECCIÓN ARQUITECTÓNICA: Eliminamos el campo 'fecha_creacion' y su valor 'NOW()'
        // de la consulta, adecuando el código a la estructura real de tu base de datos.
        $sql = "INSERT INTO clientes (id_vendedor, nombre_completo, telefono, comuna, etapa_actual) 
                VALUES (:id_vendedor, :nombre, :telefono, :comuna, '1')";
        
        // Uso de Sentencias Preparadas para mitigar Inyecciones SQL (Seguridad no funcional)
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_vendedor' => $id_vendedor,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':comuna' => $comuna
        ]);

        // Redirección limpia notificando el éxito de la creación
        header("Location: ../vendedor/embudo.php?success=creado");
        exit();

    } catch (PDOException $e) {
        die("Error al guardar prospecto: " . $e->getMessage());
    }
} else {
    // Intento de acceso malicioso o directo por URL
    header("Location: ../index.php");
    exit();
}
?>