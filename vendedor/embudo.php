<?php
// vendedor/embudo.php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
include '../includes/header.php';

$id_vendedor = $_SESSION['usuario_id'];

// Consultamos TODOS los clientes que pertenecen a este vendedor
try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_vendedor = :id_vendedor ORDER BY fecha_registro DESC");
    $stmt->execute([':id_vendedor' => $id_vendedor]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar los clientes: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark m-0"><i class="bi bi-funnel-fill me-2 text-primary"></i>Embudo de Conversión</h3>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalProspecto">
        <i class="bi bi-person-plus-fill me-2"></i>Nuevo Prospecto
    </button>
</div>

<div class="row flex-nowrap overflow-auto pb-3" style="min-height: 60vh;">
    
    <div class="col-md-3 min-w-250">
        <div class="bg-light border rounded-3 p-3 h-100 shadow-sm border-top border-4 border-secondary">
            <h6 class="fw-bold text-secondary text-uppercase mb-3">1. Prospectos</h6>
            
            <?php foreach ($clientes as $cliente): ?>
                <?php if ($cliente['etapa_actual'] == '1'): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold m-0"><?php echo htmlspecialchars($cliente['nombre_completo']); ?></h6>
                            <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($cliente['comuna']); ?></p>
                            <p class="small mb-3"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($cliente['telefono']); ?></p>
                            
                            <button class="btn btn-sm btn-outline-dark w-100 rounded-pill" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalAgendar"
                                    data-id="<?php echo $cliente['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($cliente['nombre_completo']); ?>">
                                Avanzar a Agendar <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
        </div>
    </div>

    <div class="col-md-3 min-w-250">
        <div class="bg-light border rounded-3 p-3 h-100 shadow-sm border-top border-4 border-warning">
            <h6 class="fw-bold text-warning text-uppercase mb-3 text-dark">2. Agendados</h6>
            
            <?php foreach ($clientes as $cliente): ?>
                <?php if ($cliente['etapa_actual'] == '2'): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold m-0"><?php echo htmlspecialchars($cliente['nombre_completo']); ?></h6>
                            <p class="small text-muted mb-2"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($cliente['correo']); ?></p>
                            
                            <button class="btn btn-sm btn-outline-dark w-100 rounded-pill" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalCita"
                                    data-id="<?php echo $cliente['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($cliente['nombre_completo']); ?>">
                                Registrar Cita Realizada <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-md-3 min-w-250">
        <div class="bg-light border rounded-3 p-3 h-100 shadow-sm border-top border-4 border-danger">
            <h6 class="fw-bold text-danger text-uppercase mb-3">3. Citas Realizadas</h6>
            
            <?php foreach ($clientes as $cliente): ?>
                <?php if ($cliente['etapa_actual'] == '3'): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold m-0"><?php echo htmlspecialchars($cliente['nombre_completo']); ?></h6>
                            <p class="small text-muted mb-2"><i class="bi bi-check-circle-fill text-danger"></i> Reunión concretada</p>
                            
                            <button class="btn btn-sm btn-danger w-100 rounded-pill">
                                Avanzar a Venta <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-md-3 min-w-250">
        <div class="bg-light border rounded-3 p-3 h-100 shadow-sm border-top border-4 border-success">
            <h6 class="fw-bold text-success text-uppercase mb-3">4. Ventas Cerradas</h6>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProspecto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Registrar Nuevo Prospecto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../ajax/guardar_prospecto.php" method="POST">
          <div class="modal-body">
              <p class="text-muted small">Fase de baja fricción. Ingresa solo los datos básicos iniciales.</p>
              <div class="mb-3">
                  <label class="form-label fw-bold">Nombre Completo</label>
                  <input type="text" class="form-control" name="nombre_completo" required placeholder="Ej. Juan Pérez" maxlength="50" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Solo se permiten letras y espacios">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Comuna</label>
                  <input type="text" class="form-control" name="comuna" required placeholder="Ej. Maipú" maxlength="40" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingresa una comuna válida">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Teléfono de Contacto</label>
                  <input type="tel" class="form-control" name="telefono" required placeholder="Ej. 912345678" minlength="9" maxlength="9" pattern="^9\d{8}$" title="El teléfono debe tener exactamente 9 dígitos y comenzar con 9">
              </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary px-4">Guardar Prospecto</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAgendar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-event me-2"></i>Agendar Reunión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../ajax/avanzar_etapa2.php" method="POST">
          <div class="modal-body">
              <p class="text-muted small">Estás agendando una cita para: <b id="modalAgendarNombre" class="text-dark"></b>.</p>
              
              <input type="hidden" name="id_cliente" id="modalAgendarId">
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Correo Electrónico</label>
                  <input type="email" class="form-control" name="correo" required placeholder="ejemplo@correo.cl" maxlength="100">
              </div>
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Fecha de la Reunión</label>
                      <input type="date" class="form-control" name="fecha_cita" required>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Hora</label>
                      <input type="text" class="form-control" name="hora_cita" id="inputHoraFormato" required 
                             placeholder="Ej: 14:30" 
                             maxlength="5"
                             pattern="^([01]\d|2[0-3]):([0-5]\d)$"
                             title="Ingresa una hora válida en formato 24h (Ej: 14:30)">
                  </div>
              </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning fw-bold text-dark px-4">Confirmar Agenda</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCita" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-chat-square-text me-2"></i>Registrar Cita</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../ajax/avanzar_etapa3.php" method="POST">
          <div class="modal-body">
              <p class="text-muted small">Registra el resultado de la reunión con <b id="modalCitaNombre" class="text-dark"></b>.</p>
              
              <input type="hidden" name="id_cliente" id="modalCitaId">
              
              <div class="mb-3">
                  <label class="form-label fw-bold">Observaciones / Resumen</label>
                  <textarea class="form-control" name="observaciones" rows="3" required placeholder="Ej: El cliente mostró interés en el servicio premium. Debe consultarlo con su familia..."></textarea>
              </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger fw-bold px-4">Confirmar Cita</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
// Escucha del Modal 2 (Agendar)
document.getElementById('modalAgendar').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; 
    const id = button.getAttribute('data-id'); 
    const nombre = button.getAttribute('data-nombre'); 
    
    document.getElementById('modalAgendarId').value = id;
    document.getElementById('modalAgendarNombre').textContent = nombre;
});

// Escucha del Modal 3 (Cita Realizada) - Ahora independiente
document.getElementById('modalCita').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; 
    const id = button.getAttribute('data-id'); 
    const nombre = button.getAttribute('data-nombre'); 
    
    document.getElementById('modalCitaId').value = id;
    document.getElementById('modalCitaNombre').textContent = nombre;
});
</script>

<script>
const inputHora = document.getElementById('inputHoraFormato');

inputHora.addEventListener('input', function (e) {
    if (e.inputType === 'deleteContentBackward') return;

    let valor = e.target.value.replace(/\D/g, '');
    
    if (valor.length >= 2) {
        e.target.value = valor.substring(0, 2) + ':' + valor.substring(2, 4);
    } else {
        e.target.value = valor;
    }
});
</script>

<?php include '../includes/footer.php'; ?>