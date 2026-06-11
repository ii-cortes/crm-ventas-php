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

            $env_path = __DIR__ . '/../.env';
            if (!file_exists($env_path)) {
                die("Error de configuracion.");
            }
            $env = parse_ini_file($env_path);

            $client_id = $env['GOOGLE_CLIENT_ID'] ?? '';
            $client_secret = $env['GOOGLE_CLIENT_SECRET'] ?? '';
            $refresh_token = $env['GOOGLE_REFRESH_TOKEN'] ?? '';

            $chToken = curl_init();
            curl_setopt($chToken, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($chToken, CURLOPT_POST, true);
            curl_setopt($chToken, CURLOPT_POSTFIELDS, http_build_query([
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token'
            ]));
            curl_setopt($chToken, CURLOPT_RETURNTRANSFER, true);
            $resToken = curl_exec($chToken);
            curl_close($chToken);

            $jsonToken = json_decode($resToken, true);
            $access_token = $jsonToken['access_token'] ?? '';

            if (!empty($access_token)) {
                $fin_cita = date('H:i', strtotime($hora_cita) + 3600);

                $event = [
                    'summary' => 'Reunion de Asesoria Comercial',
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
            }

            $stmtVend = $pdo->prepare("SELECT correo FROM usuarios WHERE id = :id");
            $stmtVend->execute([':id' => $id_vendedor]);
            $rowVend = $stmtVend->fetch(PDO::FETCH_ASSOC);
            $correo_vendedor = $rowVend ? $rowVend['correo'] : '';

            $to = $correo;
            $subject = "Invitacion a Reunion Comercial";
            $message = "Hola, tu reunion ha sido agendada con exito para el " . $fecha_cita . " a las " . $hora_cita . " horas.";

            $headers = "From: notificaciones@crmfuneraria.cl\r\n";
            if (!empty($correo_vendedor)) {
                $headers .= "Cc: " . $correo_vendedor . "\r\n";
            }
            $headers .= "Reply-To: soporte@crmfuneraria.cl\r\nX-Mailer: PHP/" . phpversion();

            mail($to, $subject, $message, $headers);

            header("Location: ../vendedor/embudo.php?success=agendado");
            exit();
        } else {
            die("Error en la actualizacion de etapa.");
        }
    } catch (PDOException $e) {
        die("Error BD.");
    }
} else {
    header("Location: ../index.php");
    exit();
}
