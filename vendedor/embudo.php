<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';
include '../includes/header.php';

$id_vendedor = $_SESSION['usuario_id'];

try {
    $sqlClientes = "SELECT clientes.*, catalogo.nombre AS producto_nombre 
                    FROM clientes 
                    LEFT JOIN catalogo ON clientes.id_producto_venta = catalogo.id 
                    WHERE clientes.id_vendedor = :id_vendedor 
                    ORDER BY clientes.id DESC";
                    
    $stmt = $pdo->prepare($sqlClientes);
    $stmt->execute([':id_vendedor' => $id_vendedor]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtCat = $pdo->query("SELECT * FROM catalogo WHERE estado = 1");
    $productos = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de base de datos.");
}

$etapa1 = []; $etapa2 = []; $etapa3 = []; $etapa4 = [];
foreach ($clientes as $cliente) {
    switch ($cliente['etapa_actual']) {
        case '1': $etapa1[] = $cliente; break;
        case '2': $etapa2[] = $cliente; break;
        case '3': $etapa3[] = $cliente; break;
        case '4': $etapa4[] = $cliente; break;
    }
}
?>

<main class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-4 text-dark">
        <h2 class="fw-bold"><i class="bi bi-funnel me-2"></i>Embudo de Ventas</h2>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProspecto">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Prospecto
        </button>
    </header>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>Operación realizada exitosamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <section class="row flex-nowrap overflow-x-auto g-3 pb-4">
        
        <article class="col-11 col-md-3">
            <div class="bg-light p-3 rounded shadow-sm border-top border-4 border-primary h-100">
                <h6 class="fw-bold text-primary text-uppercase mb-3">1. Prospectos (<span class="badge bg-primary text-white"><?php echo count($etapa1); ?></span>)</h6>
                <?php foreach ($etapa1 as $c): ?>
                    <aside class="card mb-2 shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h6>
                                <button type="button" class="btn btn-sm btn-outline-primary border-0 p-0 px-1 btn-editar-contacto" 
                                        data-bs-toggle="modal" data-bs-target="#modalEditarContacto"
                                        data-id="<?php echo $c['id']; ?>" 
                                        data-nombre="<?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-telefono="<?php echo htmlspecialchars($c['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                        data-comuna="<?php echo htmlspecialchars($c['comuna'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                            <div class="mb-2 mt-1">
                                <p class="small text-muted mb-0"><i class="bi bi-telephone me-1 text-primary"></i><?php echo htmlspecialchars($c['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars($c['comuna'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary w-100 btn-agendar" data-bs-toggle="modal" data-bs-target="#modalAgendar"
                                    data-id="<?php echo $c['id']; ?>" data-nombre="<?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                Avanzar a Agendar <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </aside>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="col-11 col-md-3">
            <div class="bg-light p-3 rounded shadow-sm border-top border-4 border-warning h-100">
                <h6 class="fw-bold text-warning text-uppercase mb-3">2. Agendados (<span class="badge bg-warning text-dark"><?php echo count($etapa2); ?></span>)</h6>
                <?php foreach ($etapa2 as $c): ?>
                    <aside class="card mb-2 shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h6>
                                <button type="button" class="btn btn-sm btn-outline-warning border-0 p-0 px-1 btn-editar-agenda" 
                                        data-bs-toggle="modal" data-bs-target="#modalEditarAgendamiento"
                                        data-id="<?php echo $c['id']; ?>" 
                                        data-fecha="<?php echo htmlspecialchars($c['fecha_cita'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-hora="<?php echo htmlspecialchars($c['hora_cita'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-correo="<?php echo htmlspecialchars($c['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-clock-history text-dark"></i>
                                </button>
                            </div>
                            <div class="mb-2 mt-1">
                                <p class="small text-muted mb-0"><i class="bi bi-telephone me-1 text-primary"></i><?php echo htmlspecialchars($c['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars($c['comuna'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-calendar-event me-1 text-primary"></i><?php echo htmlspecialchars($c['fecha_cita'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-clock me-1 text-primary"></i>A las <?php echo htmlspecialchars($c['hora_cita'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0 text-break"><i class="bi bi-envelope me-1 text-success"></i><?php echo htmlspecialchars($c['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning w-100 btn-cita" data-bs-toggle="modal" data-bs-target="#modalCita"
                                    data-id="<?php echo $c['id']; ?>" data-nombre="<?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                Registrar Cita <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </aside>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="col-11 col-md-3">
            <div class="bg-light p-3 rounded shadow-sm border-top border-4 border-danger h-100">
                <h6 class="fw-bold text-danger text-uppercase mb-3">3. Citas (<span class="badge bg-danger"><?php echo count($etapa3); ?></span>)</h6>
                <?php foreach ($etapa3 as $c): ?>
                    <aside class="card mb-2 shadow-sm border-0 border-start border-3 border-danger">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-0 px-1 btn-editar-perfilado" 
                                        data-bs-toggle="modal" data-bs-target="#modalEditarPerfilado"
                                        data-id="<?php echo $c['id']; ?>" 
                                        data-rut="<?php echo htmlspecialchars($c['rut'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-genero="<?php echo htmlspecialchars($c['genero'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                        data-nacimiento="<?php echo htmlspecialchars($c['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-person-vcard"></i>
                                </button>
                            </div>
                            <div class="mb-2 mt-1">
                                <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars($c['comuna'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-person-badge me-1 text-danger"></i>RUT: <?php echo htmlspecialchars($c['rut'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-cake2 me-1 text-dark"></i>F. Nac: <?php echo htmlspecialchars($c['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-gender-ambiguous me-1 text-dark"></i>Género: <?php echo htmlspecialchars($c['genero'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger w-100 btn-cerrar-venta" data-bs-toggle="modal" data-bs-target="#modalCerrarVenta"
                                    data-id="<?php echo $c['id']; ?>" data-nombre="<?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                Cerrar Venta <i class="bi bi-check2-circle"></i>
                            </button>
                        </div>
                    </aside>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="col-11 col-md-3">
            <div class="bg-light p-3 rounded shadow-sm border-top border-4 border-success h-100">
                <h6 class="fw-bold text-success text-uppercase mb-3">4. Ventas (<span class="badge bg-success"><?php echo count($etapa4); ?></span>)</h6>
                <?php foreach ($etapa4 as $c): ?>
                    <aside class="card mb-2 shadow-sm border-0 border-start border-3 border-success bg-white opacity-75">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($c['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h6>
                            <div class="mb-2 mt-1">
                                <p class="small text-muted mb-0"><i class="bi bi-telephone me-1 text-primary"></i><?php echo htmlspecialchars($c['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1 text-danger"></i><?php echo htmlspecialchars($c['comuna'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0 text-break"><i class="bi bi-envelope me-1 text-success"></i><?php echo htmlspecialchars($c['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-person-badge me-1 text-danger"></i>RUT: <?php echo htmlspecialchars($c['rut'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-cake2 me-1 text-dark"></i>F. Nac: <?php echo htmlspecialchars($c['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-muted mb-0"><i class="bi bi-gender-ambiguous me-1 text-dark"></i>Género: <?php echo htmlspecialchars($c['genero'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <hr class="my-1 text-muted opacity-25">
                                <p class="small text-dark mb-0"><i class="bi bi-box-seam me-1 text-primary"></i><b>Servicio:</b> <?php echo htmlspecialchars($c['producto_nombre'] ?? 'Desconocido', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="small text-success fw-bold mb-0"><i class="bi bi-cash me-1"></i><b>Monto:</b> $<?php echo number_format($c['precio_venta'] ?? 0, 0, ',', '.'); ?></p>
                            </div>
                            <?php if(!empty($c['documento_venta'])): ?>
                                <a href="../uploads/<?php echo $c['documento_venta']; ?>" target="_blank" class="badge bg-primary text-decoration-none d-inline-block mt-1">
                                    <i class="bi bi-file-earmark-text"></i> Ver Documento
                                </a>
                            <?php endif; ?>
                        </div>
                    </aside>
                <?php endforeach; ?>
            </div>
        </article>

    </section>
</main>

<div class="modal fade" id="modalNuevoProspecto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Nuevo Prospecto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/guardar_prospecto.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" class="form-control input-nombre" name="nombre_completo" required maxlength="50" pattern="[A-Za-zÁ-Úá-úñÑ\s]+" title="Solo letras permitidas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" class="form-control input-telefono" name="telefono" required maxlength="9" pattern="^9[0-9]{8}$" placeholder="Ej: 912345678" title="Debe empezar con 9 y tener 9 dígitos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comuna</label>
                        <input type="text" class="form-control input-comuna" name="comuna" required maxlength="50" pattern="[A-Za-zÁ-Úá-úñÑ\s]+" title="Solo letras permitidas. Sin números.">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border text-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Prospecto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgendar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i>Agendar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/avanzar_etapa2.php" method="POST">
                <div class="modal-body">
                    <p class="text-muted small">Agendando reunión con <b id="modalAgendarNombre" class="text-dark"></b></p>
                    <input type="hidden" name="id_cliente" id="modalAgendarId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de Cita</label>
                            <input type="date" class="form-control" name="fecha_cita" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Hora</label>
                            <input type="text" class="form-control input-hora" name="hora_cita" required placeholder="HH:MM" maxlength="5" pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Correo Electrónico del Cliente</label>
                        <input type="email" class="form-control" name="correo" required maxlength="100" placeholder="ejemplo@correo.com">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">Confirmar Agenda</button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/avanzar_etapa3.php" method="POST">
                <div class="modal-body">
                    <p class="text-muted small">Registrando perfil de: <b id="modalCitaNombre" class="text-dark"></b></p>
                    <input type="hidden" name="id_cliente" id="modalCitaId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">RUT</label>
                        <input type="text" class="form-control input-rut-dinamico" name="rut" required placeholder="Ej: 12345678-9" maxlength="10" pattern="^[0-9]{7,8}-[0-9Kk]{1}$">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Género</label>
                            <select class="form-select" name="genero" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Otro">Otro</option>
                                <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">F. Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" required 
                                   min="1900-01-01" 
                                   max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" 
                                   title="El cliente debe ser mayor de edad">
                        </div>
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

<div class="modal fade" id="modalCerrarVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-award-fill me-2"></i>Cerrar Venta (Etapa 4)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/avanzar_etapa4.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-success bg-opacity-10 border-success mb-4">
                        Consolidando venta con: <b id="modalCerrarNombre" class="text-dark"></b>
                    </div>
                    <input type="hidden" name="id_cliente" id="modalCerrarId">
                    
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-success">1. Detalles Comerciales</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Producto / Servicio</label>
                            <select class="form-select" name="id_producto" required>
                                <option value="" disabled selected>Selecciona del catálogo...</option>
                                <?php foreach($productos as $p): ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['nombre']); ?> - $<?php echo number_format($p['precio'], 0, ',', '.'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Documento Adjunto (PDF, JPG)</label>
                            <input type="file" class="form-control" name="documento" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3 mt-3 text-success">2. Referidos Obligatorios</h6>
                    
                    <?php for($i=1; $i<=3; $i++): ?>
                    <div class="row bg-light p-2 rounded mb-2 border">
                        <div class="col-md-4">
                            <label class="small fw-bold">Nombre Ref <?php echo $i; ?></label>
                            <input type="text" class="form-control form-control-sm input-nombre" name="ref<?php echo $i; ?>_nombre" required maxlength="50" pattern="[A-Za-zÁ-Úá-úñÑ\s]+">
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold">Teléfono Ref <?php echo $i; ?></label>
                            <input type="text" class="form-control form-control-sm input-telefono" name="ref<?php echo $i; ?>_telefono" required maxlength="9" pattern="^9[0-9]{8}$" placeholder="Ej: 912345678">
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold">Comuna Ref <?php echo $i; ?></label>
                            <input type="text" class="form-control form-control-sm input-comuna" name="ref<?php echo $i; ?>_comuna" required maxlength="50">
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Confirmar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Corregir Contacto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/editar_prospecto.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" id="editId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" class="form-control input-nombre" name="nombre_completo" id="editNombre" required maxlength="50" pattern="[A-Za-zÁ-Úá-úñÑ\s]+">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" class="form-control input-telefono" name="telefono" id="editTelefono" required maxlength="9" pattern="^9[0-9]{8}$">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comuna</label>
                        <input type="text" class="form-control input-comuna" name="comuna" id="editComuna" required maxlength="50" pattern="[A-Za-zÁ-Úá-úñÑ\s]+">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border text-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarAgendamiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i>Reprogramar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/editar_agendamiento.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" id="editAgendaId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nueva Fecha</label>
                            <input type="date" class="form-control" name="fecha_cita" id="editAgendaFecha" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nueva Hora</label>
                            <input type="text" class="form-control input-hora" name="hora_cita" id="editAgendaHora" required placeholder="HH:MM" maxlength="5" pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Corregir Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" id="editAgendaCorreo" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">Guardar Horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarPerfilado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-vcard me-2"></i>Corregir Perfilado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/editar_perfilado.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" id="editPerfiladoId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Corregir RUT</label>
                        <input type="text" class="form-control input-rut-dinamico" name="rut" id="editPerfiladoRut" required maxlength="10" pattern="^[0-9]{7,8}-[0-9Kk]{1}$">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Corregir Género</label>
                            <select class="form-select" name="genero" id="editPerfiladoGenero" required>
                                <option value="" disabled>Seleccione...</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Otro">Otro</option>
                                <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Corregir F. Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="editPerfiladoNacimiento" required 
                                   min="1900-01-01" 
                                   max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">Guardar Perfilado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        document.querySelectorAll('.btn-agendar').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalAgendarId').value = this.getAttribute('data-id');
                document.getElementById('modalAgendarNombre').innerText = this.getAttribute('data-nombre');
            });
        });

        document.querySelectorAll('.btn-cita').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalCitaId').value = this.getAttribute('data-id');
                document.getElementById('modalCitaNombre').innerText = this.getAttribute('data-nombre');
            });
        });

        document.querySelectorAll('.btn-cerrar-venta').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalCerrarId').value = this.getAttribute('data-id');
                document.getElementById('modalCerrarNombre').innerText = this.getAttribute('data-nombre');
            });
        });

        document.querySelectorAll('.btn-editar-contacto').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('editId').value = this.getAttribute('data-id');
                document.getElementById('editNombre').value = this.getAttribute('data-nombre');
                document.getElementById('editTelefono').value = this.getAttribute('data-telefono');
                document.getElementById('editComuna').value = this.getAttribute('data-comuna');
            });
        });

        document.querySelectorAll('.btn-editar-agenda').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('editAgendaId').value = this.getAttribute('data-id');
                document.getElementById('editAgendaFecha').value = this.getAttribute('data-fecha');
                document.getElementById('editAgendaHora').value = this.getAttribute('data-hora');
                document.getElementById('editAgendaCorreo').value = this.getAttribute('data-correo');
            });
        });

        document.querySelectorAll('.btn-editar-perfilado').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('editPerfiladoId').value = this.getAttribute('data-id');
                document.getElementById('editPerfiladoRut').value = this.getAttribute('data-rut');
                let genero = this.getAttribute('data-genero');
                let selectGenero = document.getElementById('editPerfiladoGenero');
                if(genero) { selectGenero.value = genero; } else { selectGenero.value = ""; }
                document.getElementById('editPerfiladoNacimiento').value = this.getAttribute('data-nacimiento');
            });
        });
    } catch (error) {
        console.error(error);
    }

    document.querySelectorAll('.input-telefono').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    document.querySelectorAll('.input-nombre').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-zÁ-Úá-úñÑ\s]/g, '');
        });
    });

    document.querySelectorAll('.input-comuna').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-zÁ-Úá-úñÑ\s]/g, '');
        });
    });

    document.querySelectorAll('.input-hora').forEach(input => {
        input.addEventListener('input', function(e) {
            if (e.inputType === 'deleteContentBackward') return;
            let valor = e.target.value.replace(/[^0-9]/g, '');
            if (valor.length >= 2) {
                e.target.value = valor.slice(0,2) + ':' + valor.slice(2,4);
            } else {
                e.target.value = valor;
            }
        });
    });

    document.querySelectorAll('.input-rut-dinamico').forEach(input => {
        input.addEventListener('input', function (e) {
            if (e.inputType === 'deleteContentBackward') return; 
            let valorLimpio = e.target.value.replace(/[^0-9kK]/g, ''); 
            if (valorLimpio.length > 1) {
                let cuerpo = valorLimpio.slice(0, -1);
                let dv = valorLimpio.slice(-1).toUpperCase(); 
                e.target.value = cuerpo + '-' + dv;
            } else {
                e.target.value = valorLimpio;
            }
        });
    });
});
</script>