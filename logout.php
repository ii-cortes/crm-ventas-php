<?php
// logout.php

// 1. Nos unimos a la sesión existente para poder manipularla
session_start();

// 2. Limpiamos todas las variables de sesión
$_SESSION = array();

// 3. Destruimos la sesión por completo en el servidor
session_destroy();

// 4. Redirigimos al usuario limpiamente a la pantalla de login
header("Location: index.php");
exit();
?>