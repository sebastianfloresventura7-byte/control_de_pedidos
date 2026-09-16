<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pedidos - Pollería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sistema de Control de Pedidos</h2>
            <a href="index.php?accion=nuevo" class="btn btn-primary">+ Nuevo Pedido</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Cliente</th>
                            <th>Total (S/)</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No hay pedidos registrados</td></tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $p): ?>
                                <tr>
                                    <td>#<?= $p['id_pedido'] ?></td>
                                    <td><?= htmlspecialchars($p['cliente'] ?? 'Cliente General') ?></td>
                                    <td>S/ <?= number_format($p['total'], 2) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= $p['estado'] ?></span></td>
                                    <td><?= $p['fecha'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>