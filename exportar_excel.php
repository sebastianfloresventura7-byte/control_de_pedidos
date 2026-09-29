<?php
// exportar_excel.php

if (ob_get_length()) {
    ob_end_clean();
}

require_once __DIR__ . "/config/conexion.php";

$conexionObj = new Conexion();
$conexion = $conexionObj->conectar();

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

$sql = "SELECT p.id_pedido, c.nombre AS cliente, c.telefono, p.total AS precio, p.fecha, p.estado 
        FROM pedidos p 
        INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
        WHERE 1=1";

$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (c.nombre LIKE :busqueda OR p.id_pedido LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

if (!empty($fecha_inicio)) {
    $sql .= " AND DATE(p.fecha) >= :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $sql .= " AND DATE(p.fecha) <= :fecha_fin";
    $params[':fecha_fin'] = $fecha_fin;
}

$sql .= " ORDER BY p.fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Ventas_" . date('Y-m-d_H-i') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";
?>
<table border="1">
    <thead>
        <tr style="background-color: #198754; color: #ffffff; font-weight: bold; text-align: center;">
            <th># Pedido</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Total (S/)</th>
            <th>Estado</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($ventas)): ?>
            <?php foreach ($ventas as $v): ?>
                <tr>
                    <td>#<?= $v['id_pedido'] ?></td>
                    <td><?= htmlspecialchars($v['cliente']) ?></td>
                    <td><?= htmlspecialchars($v['telefono'] ?? '-') ?></td>
                    <td><?= number_format($v['precio'], 2) ?></td>
                    <td><?= $v['estado'] ?></td>
                    <td><?= $v['fecha'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No se encontraron registros.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
exit;
?>