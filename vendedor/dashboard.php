<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 bg-white rounded-3 shadow-sm border-start border-4 border-primary">
            <h2 class="fw-bold m-0 text-dark">Asesor: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
            <p class="text-muted m-0 mt-1"><i class="bi bi-geo-alt-fill text-danger"></i> Región Metropolitana — Fuerza de ventas en terreno</p>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h4 class="text-uppercase text-secondary fw-bold tracking-wide">Control de Metas Diarias (Semáforo)</h4>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card mockup-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-uppercase opacity-75">Etapa 1</span>
                <span class="badge bg-success rounded-pill">Verde</span>
            </div>
            <div class="card-body p-0 mt-2">
                <h5 class="card-title m-0">Prospectos</h5>
                <h2 class="display-6 fw-bold my-1">12</h2>
                <p class="small m-0 text-light opacity-75">Meta: 10 diarios</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mockup-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-uppercase opacity-75">Etapa 2</span>
                <span class="badge bg-warning text-dark rounded-pill">Amarillo</span>
            </div>
            <div class="card-body p-0 mt-2">
                <h5 class="card-title m-0">Agendamientos</h5>
                <h2 class="display-6 fw-bold my-1">4</h2>
                <p class="small m-0 text-light opacity-75">Meta: 5 diarios</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mockup-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-uppercase opacity-75">Etapa 3</span>
                <span class="badge bg-danger rounded-pill">Rojo</span>
            </div>
            <div class="card-body p-0 mt-2">
                <h5 class="card-title m-0">Citas Realizadas</h5>
                <h2 class="display-6 fw-bold my-1">1</h2>
                <p class="small m-0 text-light opacity-75">Meta: 4 de hoy</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mockup-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-uppercase opacity-75">Etapa 4</span>
                <span class="badge bg-success rounded-pill">Verde</span>
            </div>
            <div class="card-body p-0 mt-2">
                <h5 class="card-title m-0">Ventas Cerradas</h5>
                <h2 class="display-6 fw-bold my-1">3</h2>
                <p class="small m-0 text-light opacity-75">Cierre del ciclo</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 g-4">
    <div class="col-md-6">
        <div class="card shadow-sm p-4 h-100 bg-white">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Configuración de Umbrales Personales</h5>
            <p class="text-muted small">Sobrescribe los rangos base sugeridos por el administrador corporativo para elevar tus niveles de autoexigencia sin alterar las métricas de tus compañeros.</p>
            
            <form action="../ajax/actualizar_meta.php" method="POST" class="mt-3">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small text-secondary">Mínimo Amarillo (%)</label>
                        <input type="number" class="form-control" name="min_amarillo" value="41" min="1" max="100">
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-secondary">Mínimo Verde (%)</label>
                        <input type="number" class="form-control" name="min_verde" value="80" min="1" max="100">
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary px-4 rounded-pill">Guardar Umbrales</button>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm p-4 h-100 bg-white d-flex flex-column justify-content-between">
            <div>
                <h5 class="fw-bold mb-2 text-dark"><i class="bi bi-arrow-right-circle me-2 text-primary"></i>Operación del Embudo</h5>
                <p class="text-muted small m-0">Cada asesor debe registrar de manera estricta y cronológica el avance de sus prospectos. El paso de una fase a otra exige el ingreso progresivo de datos obligatorios en el sistema.</p>
            </div>
            <div class="pt-3">
                <a href="embudo.php" class="btn btn-dark w-100 rounded-pill py-2">
                    <i class="bi bi-briefcase me-2"></i> Abrir Mi Tablero de Clientes
                </a>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>