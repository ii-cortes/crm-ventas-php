<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    $id_vendedor = $_SESSION['usuario_id'];
    
    try {
        $pdo->beginTransaction();

        $diccionario_etapas = [1 => 'prospectos', 2 => 'agendas', 3 => 'citas', 4 => 'ventas'];

        for ($i = 1; $i <= 4; $i++) {
            $meta_personal = !empty($_POST["meta_diaria_{$i}"]) ? (int)$_POST["meta_diaria_{$i}"] : null;
            $min_amarillo = (int)$_POST["min_amarillo_{$i}"];
            $min_verde = (int)$_POST["min_verde_{$i}"];
            $etapa_enum = $diccionario_etapas[$i]; 

            if ($min_amarillo >= $min_verde) {
                $pdo->rollBack();
                die("Error: El minimo amarillo no puede superar al verde.");
            }

            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM metas_vendedor WHERE id_vendedor = :id_v AND etapa = :etapa");
            $checkStmt->execute([':id_v' => $id_vendedor, ':etapa' => $etapa_enum]);
            $existe = $checkStmt->fetchColumn();

            if ($existe > 0) {
                $updateStmt = $pdo->prepare("UPDATE metas_vendedor 
                                             SET meta_diaria_personal = :m, min_amarillo_personal = :a, min_verde_personal = :v 
                                             WHERE id_vendedor = :id_v AND etapa = :e");
                $updateStmt->execute([':m' => $meta_personal, ':a' => $min_amarillo, ':v' => $min_verde, ':id_v' => $id_vendedor, ':e' => $etapa_enum]);
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO metas_vendedor (id_vendedor, etapa, meta_diaria_personal, min_amarillo_personal, min_verde_personal) 
                                             VALUES (:id_v, :e, :m, :a, :v)");
                $insertStmt->execute([':id_v' => $id_vendedor, ':e' => $etapa_enum, ':m' => $meta_personal, ':a' => $min_amarillo, ':v' => $min_verde]);
            }
        }

        $pdo->commit();
        header("Location: ../vendedor/dashboard.php?success_metas=1");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error de BD.");
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>