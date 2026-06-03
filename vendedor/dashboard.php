<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}

// 1. Incluimos la cabecera (Esto trae el menú y Bootstrap)
include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="fw-bold text-secondary">Mi Rendimiento</h2>
        <p>Bienvenido a tu panel de control, <b><?php echo $_SESSION['usuario_nombre']; ?></b>.</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card shadow-sm text-center border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">Prospectos de Hoy</h5>
                <h1 class="display-4 fw-bold">0</h1>
            </div>
        </div>
    </div>
</div>

<?php
// 3. Incluimos el pie de página
include '../includes/footer.php';
?>