<?php
// admin/catalogo.php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
include '../includes/header.php';

try {
    // Forzamos el nombre de la columna con un ALIAS (estado_prod) para evitar conflictos de mayúsculas/minúsculas
    $stmt = $pdo->query("SELECT id, nombre, precio, tipo, descripcion_corta, descripcion_larga, estado AS estado_prod FROM catalogo ORDER BY id DESC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar el catálogo: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-4">
    <h2 class="fw-bold text-dark"><i class="bi bi-journal-text me-2"></i>Catálogo de Servicios</h2>
    <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
        <i class="bi bi-plus-lg me-1"></i> Añadir Producto
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Acción realizada con éxito en el catálogo.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre / Descripción Breve</th>
                        <th>Categoría (Tipo)</th>
                        <th>Precio (CLP)</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?php echo $p['id']; ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <small class="text-muted d-block text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($p['descripcion_larga'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($p['descripcion_corta'] ?? 'Sin descripción breve', ENT_QUOTES, 'UTF-8'); ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-dark text-white border px-2 py-1.5"><?php echo htmlspecialchars($p['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                        <td class="text-success fw-bold">$<?php echo number_format($p['precio'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($p['estado_prod'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary btn-editar-prod" 
                                    data-bs-toggle="modal" data-bs-target="#modalEditarProducto"
                                    data-id="<?php echo $p['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-precio="<?php echo $p['precio']; ?>" 
                                    data-tipo="<?php echo htmlspecialchars($p['tipo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-desccorta="<?php echo htmlspecialchars($p['descripcion_corta'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-desclarga="<?php echo htmlspecialchars($p['descripcion_larga'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            
                            <form action="../ajax/estado_producto.php" method="POST" class="d-inline">
                                <input type="hidden" name="id_producto" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="estado_actual" value="<?php echo $p['estado_prod']; ?>">
                                <?php if ($p['estado_prod'] == 1): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar" onclick="return confirm('¿Ocultar este producto del catálogo de ventas?');">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activar">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($productos)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No hay productos en el catálogo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Añadir Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/guardar_producto.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre del Servicio</label>
                            <input type="text" class="form-control" name="nombre" required maxlength="100" placeholder="Ej: Urna Premium Madera">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Categoría (Tipo)</label>
                            <input type="text" class="form-control" name="tipo" required maxlength="50" placeholder="Ej: Cremación">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Precio de Venta (CLP)</label>
                        <input type="text" class="form-control input-precio" name="precio" required placeholder="Ej: 1500000">
                        <div class="form-text">Ingrese solo números, sin puntos ni signos.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción Breve (Máx. 255 caracteres)</label>
                        <input type="text" class="form-control" name="descripcion_corta" required maxlength="255" placeholder="Resumen rápido de lo que incluye el servicio">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción Detallada / Larga</label>
                        <textarea class="form-control" name="descripcion_larga" rows="4" placeholder="Escriba el desglose completo del servicio funerario..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border text-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/editar_producto.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="editProdId">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre del Servicio</label>
                            <input type="text" class="form-control" name="nombre" id="editProdNombre" required maxlength="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Categoría (Tipo)</label>
                            <input type="text" class="form-control" name="tipo" id="editProdTipo" required maxlength="50">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Precio de Venta (CLP)</label>
                        <input type="text" class="form-control input-precio" name="precio" id="editProdPrecio" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción Breve</label>
                        <input type="text" class="form-control" name="descripcion_corta" id="editProdDescCorta" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción Detallada</label>
                        <textarea class="form-control" name="descripcion_larga" id="editProdDescLarga" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border text-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark fw-bold px-4">Actualizar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Mapeo dinámico completo de todos los atributos al abrir la edición
    document.querySelectorAll('.btn-editar-prod').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editProdId').value = this.getAttribute('data-id');
            document.getElementById('editProdNombre').value = this.getAttribute('data-nombre');
            document.getElementById('editProdPrecio').value = Math.round(this.getAttribute('data-precio'));
            document.getElementById('editProdTipo').value = this.getAttribute('data-tipo');
            document.getElementById('editProdDescCorta').value = this.getAttribute('data-desccorta');
            document.getElementById('editProdDescLarga').value = this.getAttribute('data-desclarga');
        });
    });

    // Poka-Yoke de teclado para precios
    document.querySelectorAll('.input-precio').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});
</script>