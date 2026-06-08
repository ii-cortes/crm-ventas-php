<?php
// vendedor/dashboard.php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
include '../includes/header.php';

$id_vendedor = $_SESSION['usuario_id'];

// 1. CAPTURA Y SANITIZACIÓN DEL FILTRO TEMPORAL
$filtro = $_GET['filtro'] ?? 'diario';
if (!in_array($filtro, ['diario', 'semanal', 'mensual'])) {
    $filtro = 'diario';
}

$sql_tiempo = " AND DATE(clientes.fecha_registro) = CURDATE()"; 
$texto_temporal = "hoy";

if ($filtro === 'semanal') {
    $sql_tiempo = " AND clientes.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $texto_temporal = "esta semana";
} elseif ($filtro === 'mensual') {
    $sql_tiempo = " AND clientes.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $texto_temporal = "este mes";
}

try {
    // 2. OBTENER LAS METAS EN CASCADA (Ajustado a tu tabla metas_vendedor)
    $sqlMetas = "
        SELECT 
            c.etapa,
            c.meta_diaria, /* Siempre se rige por la meta corporativa */
            COALESCE(v.min_amarillo_personal, c.min_amarillo) as min_amarillo,
            COALESCE(v.min_verde_personal, c.min_verde) as min_verde,
            IF(v.id IS NOT NULL, 1, 0) as es_personalizada
        FROM metas_corporativas c
        LEFT JOIN metas_vendedor v ON c.etapa = v.etapa AND v.id_vendedor = :id_vendedor
    ";
    
    $stmtMetas = $pdo->prepare($sqlMetas);
    $stmtMetas->execute([':id_vendedor' => $id_vendedor]);
    $rowsMetas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);
    
    $diccionario_inverso = ['prospectos' => 1, 'agendas' => 2, 'citas' => 3, 'ventas' => 4];
    $metas = [];
    $hay_metas_personalizadas = false;

    foreach ($rowsMetas as $r) {
        if (isset($diccionario_inverso[$r['etapa']])) {
            $metas[$diccionario_inverso[$r['etapa']]] = $r;
            if($r['es_personalizada']) $hay_metas_personalizadas = true;
        }
    }
    
    $factor_meta = 1;
    if ($filtro === 'semanal') $factor_meta = 5;
    if ($filtro === 'mensual') $factor_meta = 20;

    for ($i = 1; $i <= 4; $i++) {
        if (!isset($metas[$i])) {
            $metas[$i] = ['meta_diaria' => 10, 'min_amarillo' => 41, 'min_verde' => 80];
        }
        $metas[$i]['meta_calculada'] = $metas[$i]['meta_diaria'] * $factor_meta;
    }

    // 3. OBTENER EL RENDIMIENTO REAL 
    $sqlStats = "SELECT etapa_actual, COUNT(*) as total 
                 FROM clientes 
                 WHERE id_vendedor = :id_vendedor $sql_tiempo 
                 GROUP BY etapa_actual";
                 
    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute([':id_vendedor' => $id_vendedor]);
    $stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

    $logros = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    foreach($stats as $row) {
        $logros[(int)$row['etapa_actual']] = (int)$row['total'];
    }

} catch (PDOException $e) {
    die("Error crítico en el motor de estadísticas: " . $e->getMessage());
}

$nombres_etapas = [1 => 'Prospectos', 2 => 'Agendados', 3 => 'Citas', 4 => 'Ventas'];

// 4. ALGORITMO DE TEXTOS MOTIVADORES
$prospectos_actuales = $logros[1];
$prospectos_meta = $metas[1]['meta_calculada'];
$ventas_actuales = $logros[4];

if ($ventas_actuales > 0) {
    $texto_motivador = "¡Espectacular! Llevas <b>$ventas_actuales</b> cierres de contratos exitosos $texto_temporal. ¡Sigue así!";
} else {
    $texto_motivador = "Llevas <b>$prospectos_actuales de $prospectos_meta</b> prospectos capturados $texto_temporal. ¡Acelera el embudo!";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-4">
    <div>
        <h2 class="fw-bold text-dark">
            <i class="bi bi-pie-chart-fill text-primary me-2"></i>Mi Panel de Rendimiento
            <?php if($hay_metas_personalizadas): ?>
                <span class="badge bg-info text-dark fs-6 ms-2" title="Estás operando con tus propios semáforos">Semáforos Personalizados</span>
            <?php endif; ?>
        </h2>
        <p class="text-muted small mb-0">Monitoreo interactivo de KPI comerciales para terreno.</p>
    </div>
    
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMetasPersonales">
            <i class="bi bi-gear-fill me-1"></i> Configurar Semáforos
        </button>
        <div class="btn-group shadow-sm" role="group">
            <a href="dashboard.php?filtro=diario" class="btn btn-sm <?php echo $filtro === 'diario' ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold px-3">Diario</a>
            <a href="dashboard.php?filtro=semanal" class="btn btn-sm <?php echo $filtro === 'semanal' ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold px-3">Semanal</a>
            <a href="dashboard.php?filtro=mensual" class="btn btn-sm <?php echo $filtro === 'mensual' ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold px-3">Mensual</a>
        </div>
    </div>
</div>

<?php if (isset($_GET['success_metas'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Tus semáforos de rendimiento han sido actualizados con éxito.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-primary bg-opacity-10 text-primary border-primary d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bi bi-lightning-charge-fill fs-4 me-3"></i>
    <div><?php echo $texto_motivador; ?></div>
</div>

<div class="row g-3 mb-4">
    <?php for ($i = 1; $i <= 4; $i++): 
        $meta_c = $metas[$i]['meta_calculada'];
        $logro_c = $logros[$i];
        $pct = $meta_c > 0 ? round(($logro_c / $meta_c) * 100) : 0;
        
        if ($pct >= $metas[$i]['min_verde']) { 
            $clase = 'success'; $status = 'Excelente'; $bg_clase = 'bg-success bg-opacity-10'; 
        } elseif ($pct >= $metas[$i]['min_amarillo']) { 
            $clase = 'warning'; $status = 'En Progreso'; $bg_clase = 'bg-warning bg-opacity-10'; 
        } else { 
            $clase = 'danger'; $status = 'Bajo'; $bg_clase = 'bg-danger bg-opacity-10'; 
        }
    ?>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-bottom border-4 border-<?php echo $clase; ?> <?php echo $bg_clase; ?> h-100">
            <div class="card-body py-3">
                <span class="badge bg-<?php echo $clase; ?> <?php echo $clase==='warning'?'text-dark':'text-white'; ?> float-end small"><?php echo $status; ?></span>
                <h6 class="text-muted fw-bold mb-1 small"><?php echo $nombres_etapas[$i]; ?></h6>
                
                <h3 class="fw-bold text-dark mb-0"><?php echo $logro_c; ?> <span class="fs-6 fw-normal text-muted">/ <?php echo $meta_c; ?></span></h3>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-8">
        <div class="card bg-white border shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold text-dark border-0 pt-3 px-3">
                <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Logro Real vs Meta Esperada
            </div>
            <div class="card-body">
                <canvas id="chartBarras" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-white border shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold text-dark border-0 pt-3 px-3">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Composición del Embudo
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="chartAnillo" style="max-height: 240px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMetasPersonales" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../ajax/guardar_metas_personales.php" method="POST">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2 text-primary"></i>Personalizar Mis Semáforos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-4">La meta diaria es asignada por la empresa, pero puedes ajustar los umbrales de tus semáforos para ser más exigente con tu propio rendimiento.</p>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="text-secondary small border-bottom">
                                    <th>Etapa</th>
                                    <th class="text-center">Meta Corp. (Fija)</th>
                                    <th class="text-center">Min. Amarillo %</th>
                                    <th class="text-center">Min. Verde %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                <tr>
                                    <td class="fw-bold fs-6"><?php echo $nombres_etapas[$i]; ?></td>
                                    <td class="text-center text-muted fw-bold">
                                        <?php echo htmlspecialchars($metas[$i]['meta_diaria']); ?>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center" 
                                               name="min_amarillo_<?php echo $i; ?>" min="1" max="98" required 
                                               value="<?php echo htmlspecialchars($metas[$i]['min_amarillo']); ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center" 
                                               name="min_verde_<?php echo $i; ?>" min="2" max="100" required 
                                               value="<?php echo htmlspecialchars($metas[$i]['min_verde']); ?>">
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm">Guardar Mis Semáforos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const etapas = ['Prospectos', 'Agendados', 'Citas', 'Ventas'];
    const datosLogros = [<?php echo $logros[1]; ?>, <?php echo $logros[2]; ?>, <?php echo $logros[3]; ?>, <?php echo $logros[4]; ?>];
    const datosMetas = [<?php echo $metas[1]['meta_calculada']; ?>, <?php echo $metas[2]['meta_calculada']; ?>, <?php echo $metas[3]['meta_calculada']; ?>, <?php echo $metas[4]['meta_calculada']; ?>];

    new Chart(document.getElementById('chartBarras'), {
        type: 'bar',
        data: {
            labels: etapas,
            datasets: [
                { label: 'Mi Logro Real', data: datosLogros, backgroundColor: '#0d6efd', borderRadius: 5 },
                { label: 'Meta Corporativa', data: datosMetas, backgroundColor: '#e2e8f0', borderRadius: 5 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('chartAnillo'), {
        type: 'doughnut',
        data: {
            labels: etapas,
            datasets: [{ data: datosLogros, backgroundColor: ['#0d6efd', '#ffc107', '#dc3545', '#198754'], borderWidth: 2, hoverOffset: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
    });
});
</script>

<?php include '../includes/footer.php'; ?>