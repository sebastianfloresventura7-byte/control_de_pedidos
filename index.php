<?php
// index.php - Pantalla de Bienvenida y Carga del Dashboard Unificado
require_once 'config/conexion.php';

// Verificamos cuál es la variable que creó config/conexion.php y la asignamos a $conexion
if (!isset($conexion)) {
    if (isset($conn)) {
        $conexion = $conn;
    } elseif (isset($db)) {
        $conexion = $db;
    } elseif (isset($link)) {
        $conexion = $link;
    } elseif (isset($pdo)) {
        $conexion = $pdo;
    }
}

$accion = $_GET['accion'] ?? 'inicio';

// Si se pulsa en "Comenzar / Ingresar al Sistema", cargamos la vista unificada
if ($accion === 'sistema' || $accion === 'reportes') {
    require_once 'views/reportes.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Pollería El Buen Sabor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

    <div class="card border-0 shadow-lg text-center p-5 rounded-4 bg-white" style="max-width: 500px;">
        <!-- Logo de la Pollería -->
        <div class="mb-3">
            <img src="assets/img/pollo.jpg" alt="Pollería Logo" class="img-fluid" style="max-height: 140px;">
        </div>
        
        <!-- Mensaje de Bienvenida -->
        <h1 class="fw-bold text-dark h3 mb-2">Pollería "El Buen Sabor"</h1>
        <p class="text-muted mb-4">Sistema Integrado de Control de Pedidos y Ventas</p>

        <!-- Botón de Ingreso -->
        <a href="index.php?accion=sistema" class="btn btn-warning btn-lg fw-bold px-4 py-3 shadow-sm rounded-pill w-100">
            <i class="bi bi-rocket-takeoff-fill me-2"></i> Comenzar / Ingresar al Sistema
        </a>
    </div>

</body>
</html>