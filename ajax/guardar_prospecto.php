<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {

    $id_vendedor = $_SESSION['usuario_id'];
    $nombre = trim($_POST['nombre_completo']);
    $telefono = trim($_POST['telefono']);
    $comuna = trim($_POST['comuna']);

    try {
        $sql = "INSERT INTO clientes (id_vendedor, nombre_completo, telefono, comuna, etapa_actual) 
                VALUES (:id_vendedor, :nombre, :telefono, :comuna, '1')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_vendedor' => $id_vendedor,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':comuna' => $comuna
        ]);

        header("Location: ../vendedor/embudo.php?success=creado");
        exit();
    } catch (PDOException $e) {
        die("Error al guardar prospecto: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
