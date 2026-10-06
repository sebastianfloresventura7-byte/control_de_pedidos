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

// Configurar cabeceras para descarga en Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Pedidos_" . date('Y-m-d_H-i') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // UTF-8 BOM
?>
<table border="1">
    <thead>
        <tr style="background-color: #ffc107; font-weight: bold;">
            <th># Pedido</th>
            <th>Cliente</th>
            <th>Telefono</th>
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
                    <td>#<?php echo $row['id_pedido']; ?></td>
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