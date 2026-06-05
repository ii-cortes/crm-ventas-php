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
    // 1. OBTENER LAS METAS CORPORATIVAS (CON EL DICCIONARIO INVERSO DEL ENUM)
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
    
    // Fallback por si la base de datos no trae alguna etapa
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
        
        // Evitamos división por cero
        $porcentaje = $meta > 0 ? round(($logro / $meta) * 100) : 0;
        
        $min_amarillo = (int)$metas[$i]['min_amarillo'];
        $min_verde = (int)$metas[$i]['min_verde'];

        // LÓGICA MATEMÁTICA DEL SEMÁFORO
        if ($porcentaje >= $min_verde) {
            $color_clase = 'success';
            $texto_estado = '¡Meta Superada!';
            $icono_semaforo = 'bi-check-circle-fill';
        } elseif ($porcentaje >= $min_amarillo) {
            $color_clase = 'warning';
            $texto_estado = 'En Progreso';
            $icono_semaforo = 'bi-exclamation-triangle-fill';
        } else {
            $color_clase = 'danger';
            $texto_estado = 'Rendimiento Bajo';
            $icono_semaforo = 'bi-x-circle-fill';
        }
        
        // Si superan el 100%, la barra visual se topa en 100 para no romper el diseño CSS
        $porcentaje_barra = $porcentaje > 100 ? 100 : $porcentaje;
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0 border-bottom border-4 border-<?php echo $color_clase; ?> h-100" style="background-color: #626f8d;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-white text-uppercase mb-0">Etapa <?php echo $i; ?></h6>
                    <i class="bi <?php echo $iconos_etapas[$i]; ?> fs-4 text-white opacity-75"></i>
                </div>
                
                <h5 class="fw-bold text-white mb-1"><?php echo $nombres_etapas[$i]; ?></h5>
                <h2 class="display-5 fw-bold text-<?php echo $color_clase; ?> mb-3">
                    <?php echo $logro; ?> <span class="fs-6 text-white opacity-50 fw-normal">/ <?php echo $meta; ?> meta</span>
                </h2>

                <div class="d-flex justify-content-between text-white small fw-bold mb-1">
                    <span>Avance</span>
                    <span><?php echo $porcentaje; ?>%</span>
                </div>
                
                <div class="progress mb-3" style="height: 10px; background-color: #343e55;">
                    <div class="progress-bar bg-<?php echo $color_clase; ?> progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: <?php echo $porcentaje_barra; ?>%;">
                    </div>
                </div>

                <div class="text-center mt-auto">
                    <span class="badge bg-<?php echo $color_clase; ?> bg-opacity-25 text-white w-100 py-2 border border-<?php echo $color_clase; ?>">
                        <i class="bi <?php echo $icono_semaforo; ?> me-1"></i><?php echo $texto_estado; ?>
                    </span>
                    <div class="mt-2 text-white small opacity-50" style="font-size: 0.7rem;">
                        [Amarillo: <?php echo $min_amarillo; ?>% | Verde: <?php echo $min_verde; ?>%]
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<div class="alert bg-dark text-white border-0 shadow-sm d-flex align-items-center p-4 rounded-3" style="background-color: #343e55 !important;">
    <i class="bi bi-lightbulb text-warning display-4 me-4"></i>
    <div>
        <h5 class="fw-bold mb-1">Consejo de Ingeniería de Ventas</h5>
        <p class="mb-0 text-light opacity-75">Las metas están definidas por la corporación y se actualizan en tiempo real. Un semáforo en <span class="text-danger fw-bold">rojo</span> no significa fracaso, sino una oportunidad para redirigir tu esfuerzo hacia esa etapa del embudo. ¡Concéntrate en avanzar tus prospectos a la zona <span class="text-success fw-bold">verde</span>!</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>