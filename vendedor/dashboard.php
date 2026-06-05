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

try {
    // 1. OBTENER LAS METAS CORPORATIVAS DESDE LA BD
    $stmtMetas = $pdo->query("SELECT etapa, meta_diaria, min_amarillo, min_verde FROM metas_corporativas");
    $rowsMetas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);
    
    $diccionario_inverso = [
        'prospectos' => 1,
        'agendas' => 2,
        'citas' => 3,
        'ventas' => 4
    ];

    $metas = [];
    foreach ($rowsMetas as $r) {
        if (isset($diccionario_inverso[$r['etapa']])) {
            $indice = $diccionario_inverso[$r['etapa']];
            $metas[$indice] = $r;
        }
    }
    
    // Fallback preventivo de seguridad
    for ($i = 1; $i <= 4; $i++) {
        if (!isset($metas[$i])) {
            $metas[$i] = ['meta_diaria' => 10, 'min_amarillo' => 41, 'min_verde' => 80];
        }
    }

    // 2. OBTENER EL RENDIMIENTO REAL DEL VENDEDOR DESDE EL EMBUDO
    $sqlStats = "SELECT etapa_actual, COUNT(*) as total FROM clientes WHERE id_vendedor = :id_vendedor GROUP BY etapa_actual";
    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute([':id_vendedor' => $id_vendedor]);
    $stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

    $logros = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    foreach($stats as $row) {
        $logros[(int)$row['etapa_actual']] = (int)$row['total'];
    }

} catch (PDOException $e) {
    die("Error al cargar las estadísticas: " . $e->getMessage());
}

$nombres_etapas = [
    1 => 'Prospectos Capturados',
    2 => 'Citas Agendadas',
    3 => 'Perfilados Realizados',
    4 => 'Ventas Cerradas'
];

$iconos_etapas = [
    1 => 'bi-person-plus',
    2 => 'bi-calendar-check',
    3 => 'bi-chat-square-text',
    4 => 'bi-award'
];
?>

<div class="mb-4 border-bottom pb-3 mt-4">
    <h2 class="fw-bold text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Mis Estadísticas de Rendimiento</h2>
    <p class="text-muted small">Aquí puedes monitorear tu progreso frente a las metas impuestas por la corporación para el día de hoy.</p>
</div>

<div class="row g-4 mb-5">
    <?php for ($i = 1; $i <= 4; $i++): 
        $meta = (int)$metas[$i]['meta_diaria'];
        $logro = (int)$logros[$i];
        
        // Evitamos división por cero al calcular el avance real
        $porcentaje = $meta > 0 ? round(($logro / $meta) * 100) : 0;
        
        $min_amarillo = (int)$metas[$i]['min_amarillo'];
        $min_verde = (int)$metas[$i]['min_verde'];

        // LÓGICA MATEMÁTICA DEL SEMÁFORO DE RENDIMIENTO
        // Restauramos los fondos de colores suaves (bg-opacity-10) que pediste mantener
        if ($porcentaje >= $min_verde) {
            $color_clase = 'success';
            $bg_clase = 'bg-success bg-opacity-10';
            $texto_estado = '¡Meta Superada!';
            $icono_semaforo = 'bi-check-circle-fill';
        } elseif ($porcentaje >= $min_amarillo) {
            $color_clase = 'warning';
            $bg_clase = 'bg-warning bg-opacity-10';
            $texto_estado = 'En Progreso';
            $icono_semaforo = 'bi-exclamation-triangle-fill';
        } else {
            $color_clase = 'danger';
            $bg_clase = 'bg-danger bg-opacity-10';
            $texto_estado = 'Rendimiento Bajo';
            $icono_semaforo = 'bi-x-circle-fill';
        }
        
        $porcentaje_barra = $porcentaje > 100 ? 100 : $porcentaje;
        $texto_badge = $color_clase === 'warning' ? 'text-dark' : 'text-white';
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0 border-bottom border-4 border-<?php echo $color_clase; ?> <?php echo $bg_clase; ?> h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-muted text-uppercase mb-0">Etapa <?php echo $i; ?></h6>
                    <i class="bi <?php echo $iconos_etapas[$i]; ?> fs-4 text-muted opacity-75"></i>
                </div>
                
                <h5 class="fw-bold text-dark mb-1"><?php echo $nombres_etapas[$i]; ?></h5>
                
                <h2 class="display-5 fw-bold text-dark mb-3">
                    <?php echo $logro; ?> <span class="fs-6 text-muted fw-normal">/ <?php echo $meta; ?> meta</span>
                </h2>

                <div class="d-flex justify-content-between text-dark small fw-bold mb-1">
                    <span>Avance</span>
                    <span><?php echo $porcentaje; ?>%</span>
                </div>
                
                <div class="progress mb-3" style="height: 10px; background-color: rgba(0,0,0,0.08);">
                    <div class="progress-bar bg-<?php echo $color_clase; ?> progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: <?php echo $porcentaje_barra; ?>%;">
                    </div>
                </div>

                <div class="text-center mt-auto">
                    <span class="badge bg-<?php echo $color_clase; ?> <?php echo $texto_badge; ?> w-100 py-2 border border-<?php echo $color_clase; ?>">
                        <i class="bi <?php echo $icono_semaforo; ?> me-1"></i><?php echo $texto_estado; ?>
                    </span>
                    <div class="mt-2 text-muted small fw-semibold" style="font-size: 0.75rem;">
                        [Amarillo: <?php echo $min_amarillo; ?>% | Verde: <?php echo $min_verde; ?>%]
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<?php include '../includes/footer.php'; ?>