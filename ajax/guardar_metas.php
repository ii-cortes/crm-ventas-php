<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {

    try {
        $pdo->beginTransaction();

        $diccionario_etapas = [
            1 => 'prospectos',
            2 => 'agendas',
            3 => 'citas',
            4 => 'ventas'
        ];

        for ($i = 1; $i <= 4; $i++) {

            $meta_diaria = (int)$_POST["meta_diaria_{$i}"];
            $min_amarillo = (int)$_POST["min_amarillo_{$i}"];
            $min_verde = (int)$_POST["min_verde_{$i}"];

            $etapa_enum = $diccionario_etapas[$i];

            if ($min_amarillo >= $min_verde) {
                $pdo->rollBack();
                die("Error crítico: En la etapa {$i}, el mínimo amarillo no puede superar o igualar al mínimo verde.");
            }

            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM metas_corporativas WHERE etapa = :etapa");
            $checkStmt->bindParam(':etapa', $etapa_enum, PDO::PARAM_STR);
            $checkStmt->execute();
            $existe = $checkStmt->fetchColumn();

            if ($existe > 0) {
                $updateStmt = $pdo->prepare("UPDATE metas_corporativas 
                                             SET meta_diaria = :m, min_amarillo = :a, min_verde = :v 
                                             WHERE etapa = :e");
                $updateStmt->bindParam(':m', $meta_diaria, PDO::PARAM_INT);
                $updateStmt->bindParam(':a', $min_amarillo, PDO::PARAM_INT);
                $updateStmt->bindParam(':v', $min_verde, PDO::PARAM_INT);
                $updateStmt->bindParam(':e', $etapa_enum, PDO::PARAM_STR);
                $updateStmt->execute();
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO metas_corporativas (etapa, meta_diaria, min_amarillo, min_verde) 
                                             VALUES (:e, :m, :a, :v)");
                $insertStmt->bindParam(':e', $etapa_enum, PDO::PARAM_STR);
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
        die("Error de BD: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
