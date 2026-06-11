<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Ventas - Funeraria UDP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-light text-secondary">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="#">
                <img src="../assets/img/logo.png" alt="Logo Universidad" width="35" height="35" class="me-2 logo-blanco">
                CRM Funeraria
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['usuario_rol'])): ?>

                        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold <?php echo ($pagina_actual === 'dashboard.php') ? 'active text-white' : ''; ?>" href="dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard Metas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold <?php echo ($pagina_actual === 'catalogo.php') ? 'active text-white' : ''; ?>" href="catalogo.php">
                                    <i class="bi bi-box-seam me-1"></i> Mantenedor de Catálogo
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['usuario_rol'] === 'vendedor'): ?>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold <?php echo ($pagina_actual === 'embudo.php') ? 'active text-white' : ''; ?>" href="embudo.php">
                                    <i class="bi bi-funnel me-1"></i> Embudo de Ventas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-semibold <?php echo ($pagina_actual === 'dashboard.php') ? 'active text-white' : ''; ?>" href="dashboard.php">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>
                </ul>

                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="navbar-text text-light small d-none d-sm-inline">
                            <i class="bi bi-person-circle me-1 text-primary"></i>
                            Conectado como: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong>
                            (<span class="text-capitalize text-warning"><?php echo $_SESSION['usuario_rol']; ?></span>)
                        </span>
                        <a href="../logout.php" class="btn btn-sm btn-outline-danger fw-bold">
                            <i class="bi bi-box-arrow-right me-1"></i>Salir
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-4">