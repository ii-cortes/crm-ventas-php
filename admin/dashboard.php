<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
include '../includes/header.php';

try {
    $stmt = $pdo->query("SELECT id, etapa, meta_diaria, min_amarillo, min_verde FROM metas_corporativas");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $diccionario_inverso = [
        'prospectos' => 1,
        'agendas' => 2,
        'citas' => 3,
        'ventas' => 4
    ];

    $metas = [];
    foreach ($rows as $r) {
        if (isset($diccionario_inverso[$r['etapa']])) {
            $indice = $diccionario_inverso[$r['etapa']];
            $metas[$indice] = $r;
        }
    }
    
    for ($i = 1; $i <= 4; $i++) {
        if (!isset($metas[$i])) {
            $metas[$i] = ['meta_diaria' => 10, 'min_amarillo' => 41, 'min_verde' => 80];
        }
    }
} catch (PDOException $e) {
    die("Error critico.");
}

$nombres_etapas = [
    1 => '1. Clientes',
    2 => '2. Agendados',
    3 => '3. Citas Realizadas',
    4 => '4. Ventas Cerradas'
];
?>

<main class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark m-0"><i class="bi bi-sliders me-2"></i>Configuración de Metas Corporativas</h2>
    </header>
    
    <p class="text-muted small mb-4">Ajuste los valores de meta diaria y los umbrales mínimos del semáforo de rendimiento para cada etapa del embudo comercial de la funeraria.</p>

    <?php if (isset($_GET['success'])): ?>
        <section class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>Estrategia corporativa de metas y semáforos actualizada con éxito.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </section>
    <?php endif; ?>

    <section>
        <form action="../ajax/guardar_metas.php" method="POST">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="w-25 ps-4">Etapa del Embudo</th>
                                    <th class="text-center text-nowrap">Meta Diaria (Entero)</th>
                                    <th class="text-center text-nowrap">Min. Amarillo (%)</th>
                                    <th class="text-center text-nowrap">Min. Verde (%)</th>
                                    <th class="w-25 text-center pe-4">Rangos del Semáforo Calculados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                <tr class="fila-meta" data-etapa="<?php echo $i; ?>">
                                    <td class="fw-bold text-secondary fs-6 ps-4">
                                        <?php echo $nombres_etapas[$i]; ?>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center fw-bold fs-5 input-meta-diaria" 
                                               name="meta_diaria_<?php echo $i; ?>" min="1" step="1" required 
                                               value="<?php echo htmlspecialchars($metas[$i]['meta_diaria']); ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center fw-bold fs-5 input-amarillo" 
                                               name="min_amarillo_<?php echo $i; ?>" min="1" max="98" required 
                                               value="<?php echo htmlspecialchars($metas[$i]['min_amarillo']); ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center fw-bold fs-5 input-verde" 
                                               name="min_verde_<?php echo $i; ?>" min="2" max="100" required 
                                               value="<?php echo htmlspecialchars($metas[$i]['min_verde']); ?>">
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex flex-column gap-1 small text-center fw-bold px-2">
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger txt-rango-rojo">Rojo: 0% - --%</span>
                                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning txt-rango-amarillo">Amarillo: --% - --%</span>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success txt-rango-verde">Verde: --%+</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm px-5">
                    <i class="bi bi-save me-2"></i>Guardar Parámetros Corporativos
                </button>
            </div>
        </form>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalcularFila(fila) {
        const inputAmarillo = fila.querySelector('.input-amarillo');
        const inputVerde = fila.querySelector('.input-verde');
        const txtRangoRojo = fila.querySelector('.txt-rango-rojo');
        const txtRangoAmarillo = fila.querySelector('.txt-rango-amarillo');
        const txtRangoVerde = fila.querySelector('.txt-rango-verde');

        let minAmarillo = parseInt(inputAmarillo.value) || 0;
        let minVerde = parseInt(inputVerde.value) || 0;

        if (minAmarillo >= minVerde) {
            minVerde = minAmarillo + 1;
            inputVerde.value = minVerde;
        }
        inputVerde.min = minAmarillo + 1;

        let maxRojo = minAmarillo - 1;
        let maxAmarillo = minVerde - 1;

        txtRangoRojo.innerText = `Rojo: 0% a ${maxRojo}%`;
        txtRangoAmarillo.innerText = `Amarillo: ${minAmarillo}% a ${maxAmarillo}%`;
        txtRangoVerde.innerText = `Verde: ${minVerde}% o más`;
    }

    document.querySelectorAll('.fila-meta').forEach(fila => {
        const inputAmarillo = fila.querySelector('.input-amarillo');
        const inputVerde = fila.querySelector('.input-verde');
        inputAmarillo.addEventListener('input', () => recalcularFila(fila));
        inputVerde.addEventListener('input', () => recalcularFila(fila));
        recalcularFila(fila);
    });
});
</script>