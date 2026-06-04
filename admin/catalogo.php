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
    $stmt = $pdo->query("SELECT * FROM catalogo ORDER BY id DESC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar el catálogo: " . $e->getMessage());
}
?>

<div class="mb-4 border-bottom pb-3">
    <a href="dashboard.php" class="btn btn-outline-secondary me-2"><i class="bi bi-speedometer2 me-1"></i> Dashboard Metas</a>
    <a href="catalogo.php" class="btn btn-dark"><i class="bi bi-box-seam me-1"></i> Mantenedor de Catálogo</a>
    <a href="../logout.php" class="btn btn-outline-danger float-end"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
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
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Producto / Servicio</th>
                        <th>Precio (CLP)</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?php echo $p['id']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-success fw-bold">$<?php echo number_format($p['precio'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($p['estado'] == 1): ?>
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
                                    data-precio="<?php echo $p['precio']; ?>" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            
                            <form action="../ajax/estado_producto.php" method="POST" class="d-inline">
                                <input type="hidden" name="id_producto" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="estado_actual" value="<?php echo $p['estado']; ?>">
                                
                                <?php if ($p['estado'] == 1): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar">
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
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay productos en el catálogo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Añadir Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/guardar_producto.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Servicio</label>
                        <input type="text" class="form-control" name="nombre" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Precio de Venta (CLP)</label>
                        <input type="text" class="form-control input-precio" name="precio" required>
                        <div class="form-text">Ingrese solo números, sin puntos ni signos.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/editar_producto.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="editProdId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Servicio</label>
                        <input type="text" class="form-control" name="nombre" id="editProdNombre" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Precio de Venta (CLP)</label>
                        <input type="text" class="form-control input-precio" name="precio" id="editProdPrecio" required>
                        <div class="form-text">Ingrese solo números, sin puntos ni signos.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark fw-bold px-4">Actualizar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-editar-prod').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editProdId').value = this.getAttribute('data-id');
            document.getElementById('editProdNombre').value = this.getAttribute('data-nombre');
            document.getElementById('editProdPrecio').value = Math.round(this.getAttribute('data-precio'));
        });
    });

    document.querySelectorAll('.input-precio').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});
</script>