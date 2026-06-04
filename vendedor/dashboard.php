<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
include '../includes/header.php';

$id_vendedor = $_SESSION['usuario_id'];

// 1. Consultamos la base de datos para contar cuántos clientes hay en cada etapa para ESTE vendedor
try {
    $stmt = $pdo->prepare("SELECT etapa_actual, COUNT(*) as total FROM clientes WHERE id_vendedor = :id_vendedor GROUP BY etapa_actual");
    $stmt->execute([':id_vendedor' => $id_vendedor]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inicializamos contadores en 0
    $clientes_por_etapa = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    
    // Poblamos el arreglo con los datos reales de la BD
    foreach ($resultados as $fila) {
        $clientes_por_etapa[$fila['etapa_actual']] = $fila['total'];
    }
} catch (PDOException $e) {
    die("Error al cargar métricas: " . $e->getMessage());
}

// 2. Metas Diarias (Por ahora fijas, luego vendrán del Administrador)
$metas_diarias = [1 => 10, 2 => 5, 3 => 4, 4 => 2];

// 3. Función para calcular el color del semáforo según el porcentaje de cumplimiento
function calcularColorSemaforo($logrado, $meta) {
    $porcentaje = ($meta > 0) ? ($logrado / $meta) * 100 : 0;
    
    // Umbrales sugeridos en el requerimiento
    if ($porcentaje >= 80) {
        return ['clase' => 'bg-success text-white', 'texto' => 'Óptimo', 'icono' => 'bi-check-circle'];
    } elseif ($porcentaje >= 41) {
        return ['clase' => 'bg-warning text-dark', 'texto' => 'Regular', 'icono' => 'bi-exclamation-triangle'];
    } else {
        return ['clase' => 'bg-danger text-white', 'texto' => 'Crítico', 'icono' => 'bi-x-circle'];
    }
}
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
        <h4 class="text-uppercase text-secondary fw-bold tracking-wide">Control de Metas Diarias (Semáforo Dinámico)</h4>
    </div>
</div>

<div class="row g-3">
    <?php 
    $nombres_etapas = [
        1 => 'Prospectos', 
        2 => 'Agendamientos', 
        3 => 'Citas Realizadas', 
        4 => 'Ventas Cerradas'
    ];

    for ($i = 1; $i <= 4; $i++): 
        $logrado = $clientes_por_etapa[$i];
        $meta = $metas_diarias[$i];
        $semaforo = calcularColorSemaforo($logrado, $meta);
    ?>
    <div class="col-md-3">
        <div class="card shadow-sm p-3 h-100 <?php echo $semaforo['clase']; ?>" style="border-radius: 12px; border: none;">
            <div class="d-flex justify-content-between align-items-center opacity-75">
                <span class="small fw-bold text-uppercase">Etapa <?php echo $i; ?></span>
                <span><i class="bi <?php echo $semaforo['icono']; ?>"></i> <?php echo $semaforo['texto']; ?></span>
            </div>
            <div class="card-body p-0 mt-3">
                <h6 class="card-title m-0 opacity-75"><?php echo $nombres_etapas[$i]; ?></h6>
                <h2 class="display-4 fw-bold my-1"><?php echo $logrado; ?></h2>
                <p class="small m-0 fw-bold">Meta diaria: <?php echo $meta; ?></p>
            </div>
            <div class="progress mt-3" style="height: 6px; background-color: rgba(255,255,255,0.3);">
                <div class="progress-bar bg-white" role="progressbar" style="width: <?php echo min(($logrado/$meta)*100, 100); ?>%"></div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<div class="row mt-4 g-4">
    <div class="col-md-6">
        <div class="card shadow-sm p-4 h-100 bg-white">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Configuración de Umbrales Personales</h5>
            <p class="text-muted small">Sobrescribe los rangos base sugeridos por el administrador corporativo para elevar tus niveles de autoexigencia.</p>
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
                <p class="text-muted small m-0">El paso de una fase a otra exige el ingreso progresivo de datos obligatorios en el sistema.</p>
            </div>
            <div class="pt-3">
                <a href="embudo.php" class="btn btn-dark w-100 rounded-pill py-2">
                    <i class="bi bi-briefcase me-2"></i> Abrir Mi Tablero de Clientes
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>