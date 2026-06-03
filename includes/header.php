<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Ventas | Funeraria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1a2a40;">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold tracking-wider" href="#">FUNERARIA UDP</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="../admin/catalogo.php">Catálogo</a></li>
            <li class="nav-item"><a class="nav-link" href="../admin/metas.php">Metas Corporativas</a></li>
        <?php elseif (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'vendedor'): ?>
            <li class="nav-item"><a class="nav-link" href="../vendedor/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="../vendedor/embudo.php">Embudo (Secuencial)</a></li>
        <?php endif; ?>
      </ul>
      
      <div class="d-flex align-items-center text-white">
          <span class="me-3">
              <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Asesor'); ?>
          </span>
          <a href="../logout.php" class="btn btn-sm btn-outline-light">Salir</a>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid my-4 px-4">