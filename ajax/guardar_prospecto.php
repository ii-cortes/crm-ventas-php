<?php
// ajax/guardar_prospecto.php
session_start();
require_once '../includes/db.php';

// Verificamos si la petición es POST y si el usuario está logueado como vendedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    // 1. Capturar los datos del formulario (Sanitizados ligeramente)
    $id_vendedor = $_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre_completo']);
    $comuna = trim($_POST['comuna']);
    $telefono = trim($_POST['telefono']);
    
    // La etapa por defecto al insertar es '1' (Prospecto)
    $etapa_actual = '1'; 

    try {
        // 2. Preparar la consulta SQL (INSERT)
        $sql = "INSERT INTO clientes (id_vendedor, etapa_actual, nombre_completo, comuna, telefono) 
                VALUES (:id_vendedor, :etapa_actual, :nombre_completo, :comuna, :telefono)";
        
        $stmt = $pdo->prepare($sql);
        
        // 3. Ejecutar la consulta inyectando los datos de forma segura
        $stmt->execute([
            ':id_vendedor' => $id_vendedor,
            ':etapa_actual' => $etapa_actual,
            ':nombre_completo' => $nombre,
            ':comuna' => $comuna,
            ':telefono' => $telefono
        ]);
        
        // 4. Redirigir de vuelta al embudo
        // Idealmente en el futuro enviaremos una variable de éxito (?success=1)
        header("Location: ../vendedor/embudo.php");
        exit();

    } catch (PDOException $e) {
        die("Error al guardar el prospecto: " . $e->getMessage());
    }

} else {
    // Si alguien intenta acceder sin permiso, lo expulsamos
    header("Location: ../index.php");
    exit();
}
?>