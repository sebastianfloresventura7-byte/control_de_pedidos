<?php
// exportar_pdf.php (En la raíz del proyecto)

require_once __DIR__ . "/config/conexion.php";

$conexionObj = new Conexion();
$conexion = $conexionObj->conectar();

// Recibir los parámetros del filtro desde la URL
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Consulta SQL
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

$total_ventas = 0;
foreach ($ventas as $v) {
    if ($v['estado'] !== 'Anulado') {
        $total_ventas += $v['precio'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="bg-white p-4">

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="javascript:history.back()" class="btn btn-secondary fw-bold">⬅️ Volver</a>
        <button onclick="window.print()" class="btn btn-danger fw-bold">🖨️ Imprimir / Guardar como PDF</button>
    </div>

    <div class="text-center mb-4">
        <h2 class="fw-bold">REPORTE GENERAL DE VENTAS Y PEDIDOS</h2>
        <p class="text-muted">Fecha de emisión: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th># Pedido</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ventas)): ?>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td class="text-center"><strong>#<?= $v['id_pedido'] ?></strong></td>
                        <td><?= htmlspecialchars($v['cliente']) ?></td>
                        <td><?= htmlspecialchars($v['telefono'] ?? '-') ?></td>
                        <td class="text-end">S/ <?= number_format($v['precio'], 2) ?></td>
                        <td class="text-center"><?= $v['estado'] ?></td>
                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No se encontraron datos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end fs-5">TOTAL RECAUDADO:</th>
                <th class="text-end fs-5 text-success">S/ <?= number_format($total_ventas, 2) ?></th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

</body>
</html>
<?php
exit;
?>