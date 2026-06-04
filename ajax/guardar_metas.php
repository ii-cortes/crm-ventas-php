<?php
// ajax/guardar_metas.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    
    try {
        $pdo->beginTransaction();

        for ($i = 1; $i <= 4; $i++) {
            
            // 1. Capturamos los datos
            $meta_diaria = (int)$_POST["meta_diaria_{$i}"];
            $min_amarillo = (int)$_POST["min_amarillo_{$i}"];
            $min_verde = (int)$_POST["min_verde_{$i}"];
            
            // CORRECCIÓN MAGISTRAL: Convertimos explícitamente el contador a texto
            // para que coincida con el tipo ENUM('1','2','3','4') de tu base de datos
            $etapa_enum = (string)$i; 

            // Poka-Yoke: Evitamos guardados ilógicos
            if ($min_amarillo >= $min_verde) {
                $pdo->rollBack();
                die("Error crítico: En la etapa {$i}, el mínimo amarillo no puede superar o igualar al mínimo verde.");
            }

            // 2. PATRÓN UPSERT con casteo de parámetros
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM metas_corporativas WHERE etapa = :etapa");
            // Forzamos a PDO a enviar el dato como un STRING (texto) para engañar al ENUM
            $checkStmt->bindParam(':etapa', $etapa_enum, PDO::PARAM_STR);
            $checkStmt->execute();
            $existe = $checkStmt->fetchColumn();

            if ($existe > 0) {
                // UPDATE
                $updateStmt = $pdo->prepare("UPDATE metas_corporativas 
                                             SET meta_diaria = :m, min_amarillo = :a, min_verde = :v 
                                             WHERE etapa = :e");
                $updateStmt->bindParam(':m', $meta_diaria, PDO::PARAM_INT);
                $updateStmt->bindParam(':a', $min_amarillo, PDO::PARAM_INT);
                $updateStmt->bindParam(':v', $min_verde, PDO::PARAM_INT);
                $updateStmt->bindParam(':e', $etapa_enum, PDO::PARAM_STR); // Forzado a texto
                $updateStmt->execute();
            } else {
                // INSERT
                $insertStmt = $pdo->prepare("INSERT INTO metas_corporativas (etapa, meta_diaria, min_amarillo, min_verde) 
                                             VALUES (:e, :m, :a, :v)");
                $insertStmt->bindParam(':e', $etapa_enum, PDO::PARAM_STR); // Forzado a texto
                $insertStmt->bindParam(':m', $meta_diaria, PDO::PARAM_INT);
                $insertStmt->bindParam(':a', $min_amarillo, PDO::PARAM_INT);
                $insertStmt->bindParam(':v', $min_verde, PDO::PARAM_INT);
                $insertStmt->execute();
            }
        }

        $pdo->commit();

        header("Location: ../admin/dashboard.php?success=1");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error de Base de Datos al guardar parámetros corporativos. Revise el diseño de la tabla. Error: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>