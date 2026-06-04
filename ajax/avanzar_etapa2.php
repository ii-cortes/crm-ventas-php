<?php
// ajax/avanzar_etapa2.php
session_start();

// Habilitar la visualización de errores para depurar (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db.php';

// Validar que la petición sea POST y venga de un vendedor autenticado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    // Captura estricta de variables.
    // Usamos trim() para limpiar espacios invisibles que puedan romper la base de datos.
    $id_cliente = trim($_POST['id_cliente'] ?? '');
    $id_vendedor = $_SESSION['usuario_id'];
    $fecha_cita = trim($_POST['fecha_cita'] ?? '');
    $hora_cita = trim($_POST['hora_cita'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    // Validación extra de backend (Poka-Yoke estructural)
    if (empty($id_cliente) || empty($fecha_cita) || empty($hora_cita) || empty($correo)) {
        die("Error: Faltan datos obligatorios para agendar la cita.");
    }

    try {
        // Actualizamos al cliente asegurándonos de que esté en la Etapa 1
        $sql = "UPDATE clientes 
                SET fecha_cita = :fecha_cita, 
                    hora_cita = :hora_cita, 
                    correo = :correo,
                    etapa_actual = '2' 
                WHERE id = :id_cliente AND id_vendedor = :id_vendedor AND etapa_actual = '1'";
        
        $stmt = $pdo->prepare($sql);
        
        // Ejecución con parámetros nombrados (previene Inyecciones SQL)
        $resultado = $stmt->execute([
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita,
            ':correo' => $correo,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        // Verificamos si realmente se actualizó alguna fila
        if ($stmt->rowCount() > 0) {
            header("Location: ../vendedor/embudo.php?success=agendado");
            exit();
        } else {
            // Si entra aquí, es porque el ID no existía, el vendedor no era el dueño, o ya estaba en Etapa 2
            die("Error crítico: No se pudo actualizar el cliente. Verifica que el cliente esté en Etapa 1.");
        }

    } catch (PDOException $e) {
        die("Error de Base de Datos al avanzar a etapa 2: " . $e->getMessage());
    }
} else {
    // Intento de acceso malicioso
    header("Location: ../index.php");
    exit();
}
?>