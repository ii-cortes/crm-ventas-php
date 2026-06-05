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

// 1. CAPTURA Y SANITIZACIÓN DEL FILTRO TEMPORAL (CRITERIO DE ACEPTACIÓN 2)
$filtro = $_GET['filtro'] ?? 'diario';
if (!in_array($filtro, ['diario', 'semanal', 'mensual'])) {
    $filtro = 'diario';
}

// CORRECCIÓN SQL: Usamos tu columna real 'fecha_registro'
$sql_tiempo = " AND DATE(clientes.fecha_registro) = CURDATE()"; // Por defecto Diario
$texto_temporal = "hoy";

if ($filtro === 'semanal') {
    $sql_tiempo = " AND clientes.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $texto_temporal = "esta semana";
} elseif ($filtro === 'mensual') {
    $sql_tiempo = " AND clientes.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $texto_temporal = "este mes";
}

try {
    // 2. OBTENER LAS METAS CORPORATIVAS (DICCIONARIO ENUM)
    $stmtMetas = $pdo->query("SELECT etapa, meta_diaria, min_amarillo, min_verde FROM metas_corporativas");
    $rowsMetas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);
    
    $diccionario_inverso = ['prospectos' => 1, 'agendas' => 2, 'citas' => 3, 'ventas' => 4];
    $metas = [];
    foreach ($rowsMetas as $r) {
        if (isset($diccionario_inverso[$r['etapa']])) {
            $metas[$diccionario_inverso[$r['etapa']]] = $r;
        }
    }
    
    // Ajuste proporcional de metas según el filtro (Si es semanal multiplicamos por 5, si es mensual por 20)
    $factor_meta = 1;
    if ($filtro === 'semanal') $factor_meta = 5;
    if ($filtro === 'mensual') $factor_meta = 20;

    for ($i = 1; $i <= 4; $i++) {
        if (!isset($metas[$i])) {
            $metas[$i] = ['meta_diaria' => 10, 'min_amarillo' => 41, 'min_verde' => 80];
        }
        // Escalamos la meta numéricamente para que sea realista con el filtro
        $metas[$i]['meta_calculada'] = $metas[$i]['meta_diaria'] * $factor_meta;
    }

    // 3. OBTENER EL RENDIMIENTO REAL FILTRADO EN TIEMPO REAL (CRITERIO DE ACEPTACIÓN 1)
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

// 4. ALGORITMO DE TEXTOS MOTIVADORES DINÁMICOS
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
        <h2 class="fw-bold text-dark"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Mi Panel de Rendimiento</h2>
        <p class="text-muted small mb-0">Monitoreo interactivo de KPI comerciales para terreno.</p>
    </div>
    
    <div class="btn-group shadow-sm" role="group">
        <a href="dashboard.php?filtro=diario" class="btn btn-sm <?php echo $filtro === 'diario' ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold px-3">Diario</a>
        <a href="dashboard.php?filtro=semanal" class="btn btn-sm <?php echo $filtro === 'semanal' ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold px-3">Semanal</a>
        <a href="dashboard.php?filtro=mensual" class="btn btn-sm <?php echo $filtro === 'mensual' ? 'btn-primary' : 'btn-outline-primary'; ?> fw-bold px-3">Mensual</a>
    </div>
</div>

<div class="alert alert-primary bg-opacity-10 text-primary border-primary d-flex align-items-center mb-4 shadow-sm" role="alert">
    <i class="bi bi-lightning-charge-fill fs-4 me-3"></i>
    <div><?php echo $texto_motivador; ?></div>
</div>

<div class="row g-3 mb-4">
    <?php for ($i = 1; $i <= 4; $i++): 
        $meta_c = $metas[$i]['meta_calculada'];
        $logro_c = $logros[$i];
        $pct = $meta_c > 0 ? round(($logro_c / $meta_c) * 100) : 0;
        
        // Mantenemos los fondos pastel y tu ajuste de números oscuros
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const etapas = ['Prospectos', 'Agendados', 'Citas', 'Ventas'];
    const datosLogros = [<?php echo $logros[1]; ?>, <?php echo $logros[2]; ?>, <?php echo $logros[3]; ?>, <?php echo $logros[4]; ?>];
    const datosMetas = [<?php echo $metas[1]['meta_calculada']; ?>, <?php echo $metas[2]['meta_calculada']; ?>, <?php echo $metas[3]['meta_calculada']; ?>, <?php echo $metas[4]['meta_calculada']; ?>];

    // 1. RENDERIZACIÓN DEL GRÁFICO DE BARRAS
    new Chart(document.getElementById('chartBarras'), {
        type: 'bar',
        data: {
            labels: etapas,
            datasets: [
                {
                    label: 'Mi Logro Real',
                    data: datosLogros,
                    backgroundColor: '#0d6efd', // Color azul Bootstrap para que combine
                    borderRadius: 5
                },
                {
                    label: 'Meta',
                    data: datosMetas,
                    backgroundColor: '#e2e8f0',
                    borderRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. RENDERIZACIÓN DEL GRÁFICO DE ANILLO
    new Chart(document.getElementById('chartAnillo'), {
        type: 'doughnut',
        data: {
            labels: etapas,
            datasets: [{
                data: datosLogros,
                backgroundColor: ['#0d6efd', '#ffc107', '#dc3545', '#198754'], 
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>