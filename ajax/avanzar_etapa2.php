<?php
session_start();
ini_set('display_errors', 0);
error_reporting(0);
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    $id_cliente = trim($_POST['id_cliente'] ?? '');
    $id_vendedor = $_SESSION['usuario_id'];
    $fecha_cita = trim($_POST['fecha_cita'] ?? '');
    $hora_cita = trim($_POST['hora_cita'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if (empty($id_cliente) || empty($fecha_cita) || empty($hora_cita) || empty($correo)) {
        die("Error de validacion.");
    }

    try {
        $sql = "UPDATE clientes 
                SET fecha_cita = :fecha_cita, 
                    hora_cita = :hora_cita, 
                    correo = :correo,
                    etapa_actual = '2' 
                WHERE id = :id_cliente AND id_vendedor = :id_vendedor AND etapa_actual = '1'";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            ':fecha_cita' => $fecha_cita,
            ':hora_cita' => $hora_cita,
            ':correo' => $correo,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        if ($stmt->rowCount() > 0) {
            
            $access_token = 'TU_TOKEN_OAUTH2_API_AQUI'; 
            $fin_cita = date('H:i', strtotime($hora_cita) + 3600);
            
            $event = [
                'summary' => 'Reunión de Asesoría Comercial',
                'start' => ['dateTime' => $fecha_cita . 'T' . $hora_cita . ':00-04:00', 'timeZone' => 'America/Santiago'],
                'end' => ['dateTime' => $fecha_cita . 'T' . $fin_cita . ':00-04:00', 'timeZone' => 'America/Santiago'],
                'attendees' => [['email' => $correo]]
            ];

            $ch = curl_init('https://www.googleapis.com/calendar/v3/calendars/primary/events?sendUpdates=all');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ]);
            curl_exec($ch);
            curl_close($ch);

            $to = $correo;
            $subject = "Invitacion a Reunion Comercial";
            $message = "Hola, tu reunion ha sido agendada con exito para el " . $fecha_cita . " a las " . $hora_cita . " horas.";
            $headers = "From: notificaciones@crmfuneraria.cl\r\nReply-To: soporte@crmfuneraria.cl\r\nX-Mailer: PHP/" . phpversion();
            mail($to, $subject, $message, $headers);

            header("Location: ../vendedor/embudo.php?success=agendado");
            exit();
        } else {
            die("Error en la actualización de etapa.");
        }

    } catch (PDOException $e) {
        die("Error BD.");
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>