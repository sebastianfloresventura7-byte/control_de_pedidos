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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pedidos PDF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #ffc107; color: #000; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .no-print { margin-bottom: 20px; text-align: right; }
        .btn-print { background-color: #dc3545; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Imprimir / Guardar como PDF</button>
    </div>

    <h2>POLLERÍA "EL BUEN SABOR" - REPORTE DE PEDIDOS</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Teléfono</th>
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
                        <td><?php echo htmlspecialchars($row['cliente'] ?? 'Sin Registro'); ?></td>
                        <td><?php echo htmlspecialchars($row['telefono'] ?? '-'); ?></td>
                        <td>S/ <?php echo number_format($row['total'] ?? 0, 2); ?></td>
                        <td><?php echo !empty($row['fecha']) ? date('d/m/Y H:i', strtotime($row['fecha'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($row['estado'] ?? 'Pendiente'); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay registros de pedidos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>