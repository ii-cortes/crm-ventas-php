<?php

$host = 'localhost';
$dbname = 'crm_funeraria_udp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error crítico: No se pudo conectar a la base de datos. Detalles: " . $e->getMessage());
}
