<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión activa
if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: ../index.php?accion=login");
    exit;
}

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "control_de_pedidos";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $usuario, $password, $base_datos);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Recibir filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Consulta SQL con filtros
$sql = "SELECT p.*, c.nombre AS cliente, c.telefono 
        FROM pedidos p 
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
        WHERE 1=1";

if (!empty($busqueda)) {
    $busqueda_esc = mysqli_real_escape_string($conn, $busqueda);
    $sql .= " AND (c.nombre LIKE '%$busqueda_esc%' OR p.id_pedido = '$busqueda_esc')";
}

if (!empty($fecha_desde)) {
    $fecha_desde_esc = mysqli_real_escape_string($conn, $fecha_desde);
    $sql .= " AND DATE(p.fecha) >= '$fecha_desde_esc'";
}

if (!empty($fecha_hasta)) {
    $fecha_hasta_esc = mysqli_real_escape_string($conn, $fecha_hasta);
    $sql .= " AND DATE(p.fecha) <= '$fecha_hasta_esc'";
}

$sql .= " ORDER BY p.id_pedido DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pedidos - Pollería El Buen Sabor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #fff; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ffc107; padding-bottom: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-danger"><i class="fa-solid fa-print me-1"></i> Guardar como PDF / Imprimir</button>
</div>

<div class="header">
    <h2>Pollería "El Buen Sabor"</h2>
    <h4>Reporte Control de Pedidos y Ventas</h4>
    <p class="text-muted small">Fecha de generación: <?php echo date('d/m/Y H:i'); ?></p>
</div>

<table class="table table-bordered align-middle text-center">
    <thead class="table-light">
        <tr>
            <th># Pedido</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Detalle del Pedido</th>
            <th>Total (S/)</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php 
                    $texto_pedido = '';
                    foreach ($row as $clave => $valor) {
                        if (in_array(strtolower($clave), ['detalle', 'descripcion', 'pedido', 'producto', 'plato', 'items']) && !empty(trim($valor))) {
                            $texto_pedido = $valor;
                            break;
                        }
                    }
                    if (empty(trim($texto_pedido))) { $texto_pedido = '-'; }
                ?>
                <tr>
                    <td class="fw-bold">#<?php echo $row['id_pedido']; ?></td>
                    <td><?php echo htmlspecialchars($row['cliente'] ?? 'Sin Registro'); ?></td>
                    <td><?php echo htmlspecialchars($row['telefono'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($texto_pedido); ?></td>
                    <td>S/ <?php echo number_format($row['total'] ?? 0, 2); ?></td>
                    <td><?php echo !empty($row['fecha']) ? date('d/m/Y H:i', strtotime($row['fecha'])) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($row['estado'] ?? 'Pendiente'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No se encontraron registros.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    window.onload = function() {
        window.print();
    }
</script>
</body>
</html>