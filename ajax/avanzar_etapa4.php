<?php
// ajax/avanzar_etapa4.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor') {
    
    $id_cliente = $_POST['id_cliente'];
    $id_vendedor = $_SESSION['usuario_id'];
    $id_producto = $_POST['id_producto'];

    // 1. GESTIÓN DEL ARCHIVO (Captura Documental)
    if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['documento']['tmp_name'];
        $fileName = $_FILES['documento']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $newFileName = uniqid() . '.' . $fileExtension;
        $uploadFileDir = '../uploads/';
        
        // ==========================================
        // NUEVO: POKA-YOKE DE INFRAESTRUCTURA
        // Si la carpeta 'uploads' no existe, PHP la crea automáticamente
        // ==========================================
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $dest_path)) {
            die("Error crítico al guardar el documento en el servidor. Verifica los permisos de carpeta.");
        }
    } else {
        die("El documento adjunto es obligatorio para cerrar la venta.");
    }

    try {
        // 2. INICIO DE LA TRANSACCIÓN (ACID)
        $pdo->beginTransaction();

        // 3. Obtener el precio actual del catálogo (Histórico de Precios)
        $stmtPrecio = $pdo->prepare("SELECT precio FROM catalogo WHERE id = :id_producto");
        $stmtPrecio->execute([':id_producto' => $id_producto]);
        $producto = $stmtPrecio->fetch(PDO::FETCH_ASSOC);
        $precio_historico = $producto['precio'];

        // 4. Actualizar al cliente a Etapa 4 (Cierre de Venta)
        $sqlVenta = "UPDATE clientes 
                     SET etapa_actual = '4', 
                         id_producto_venta = :id_producto, 
                         precio_venta = :precio_historico, 
                         documento_venta = :documento 
                     WHERE id = :id_cliente AND id_vendedor = :id_vendedor AND etapa_actual = '3'";
        $stmtVenta = $pdo->prepare($sqlVenta);
        $stmtVenta->execute([
            ':id_producto' => $id_producto,
            ':precio_historico' => $precio_historico,
            ':documento' => $newFileName,
            ':id_cliente' => $id_cliente,
            ':id_vendedor' => $id_vendedor
        ]);

        // 5. Inserción de los 3 Referidos (Como nuevos prospectos Etapa 1)
        $sqlReferido = "INSERT INTO clientes (id_vendedor, nombre_completo, telefono, comuna, etapa_actual) 
                        VALUES (:id_vendedor, :nombre, :telefono, :comuna, '1')";
        $stmtRef = $pdo->prepare($sqlReferido);

        for ($i = 1; $i <= 3; $i++) {
            $stmtRef->execute([
                ':id_vendedor' => $id_vendedor,
                ':nombre' => trim($_POST["ref{$i}_nombre"]),
                ':telefono' => trim($_POST["ref{$i}_telefono"]),
                ':comuna' => trim($_POST["ref{$i}_comuna"])
            ]);
        }

        // 6. Confirmamos los cambios (COMMIT)
        $pdo->commit();

        header("Location: ../vendedor/embudo.php?success=venta_cerrada");
        exit();

    } catch (PDOException $e) {
        // Si algo falla, deshacemos todos los cambios (ROLLBACK)
        $pdo->rollBack();
        die("Error en la transacción: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>