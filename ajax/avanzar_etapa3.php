<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {

    $id_cliente = $_POST['id_cliente'];
    $id_vendedor = $_SESSION['usuario_id'];

    $rut = trim($_POST['rut']);
    $genero = $_POST['genero'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    try {
        $sql = "UPDATE clientes 
                SET rut = :rut, 
                    genero = :genero, 
                    fecha_nacimiento = :fecha_nacimiento, 
                    etapa_actual = '3' 
                WHERE id = :id_cliente 
                  AND id_vendedor = :id_vendedor 
                  AND etapa_actual = '2'";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':rut' => $rut,
            ':genero' => $genero,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        header("Location: ../vendedor/embudo.php?success=etapa3");
        exit();
    } catch (PDOException $e) {
        die("Error al actualizar a la etapa 3: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
