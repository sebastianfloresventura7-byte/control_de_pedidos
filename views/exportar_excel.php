<?php
$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "control_de_pedidos";

$conn = mysqli_connect($host, $usuario, $password, $base_datos);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Pedidos_" . date('Y-m-d_H-i') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = "SELECT 
            p.id_pedido,
            c.nombre AS cliente,
            c.telefono,
            p.total,
            p.fecha,
            p.estado
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        ORDER BY p.id_pedido DESC";

$result = mysqli_query($conn, $sql);
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1">
    <thead>
        <tr style="background-color: #ffc107; font-weight: bold;">
            <th>ID Pedido</th>
            <th>Cliente</th>
            <th>Telefono</th>
            <th>Total (S/)</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['id_pedido']; ?></td>
                    <td><?php echo $row['cliente'] ?? 'Sin Registro'; ?></td>
                    <td><?php echo $row['telefono'] ?? '-'; ?></td>
                    <td>S/ <?php echo number_format($row['total'] ?? 0, 2); ?></td>
                    <td><?php echo !empty($row['fecha']) ? date('d/m/Y H:i', strtotime($row['fecha'])) : '-'; ?></td>
                    <td><?php echo $row['estado'] ?? 'Pendiente'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No hay registros de pedidos.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>