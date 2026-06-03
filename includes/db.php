<?php
// includes/db.php

// Credenciales por defecto de XAMPP
$host = 'localhost';
$dbname = 'crm_funeraria_udp';
$username = 'root'; 
$password = ''; // En XAMPP, la contraseña del usuario root viene vacía por defecto

try {
    // Usamos PDO (PHP Data Objects) en lugar de mysqli. 
    // Es el estándar actual en la industria porque es más seguro contra ataques de Inyección SQL.
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuramos PDO para que nos avise con detalles si hay algún error en nuestras consultas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si la conexión falla, detenemos la ejecución y mostramos el error
    die("Error crítico: No se pudo conectar a la base de datos. Detalles: " . $e->getMessage());
}
?>