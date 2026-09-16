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
            <div class="d-flex align-items-center gap-3">
                <img src="assets/img/pollo.jpg" alt="Logo Pollería" style="height: 90px; object-fit: contain;">
                <div>
                    <h1 class="m-0 fw-bold">Pollería El Buen Sabor</h1>
                    <small class="text-muted">Sistema de Control de Pedidos</small>
                </div>
            </div>
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
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No hay pedidos registrados</td></tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $p): ?>
                                <tr>
                                    <td>#<?= $p['id_pedido'] ?></td>
                                    <td><?= htmlspecialchars($p['cliente'] ?? 'Cliente General') ?></td>
                                    <td>S/ <?= number_format($p['total'], 2) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= $p['estado'] ?></span></td>
                                    <td><?= $p['fecha'] ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <form action="index.php?accion=cambiar_estado" method="POST" class="flex-grow-1">
                                                <input type="hidden" name="id" value="<?= $p['id_pedido'] ?>">
                                                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="Pendiente" <?= $p['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                    <option value="En Preparación" <?= $p['estado'] == 'En Preparación' ? 'selected' : '' ?>>En Preparación</option>
                                                    <option value="Entregado" <?= $p['estado'] == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                                                    <option value="Cancelado" <?= $p['estado'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                                </select>
                                            </form>

                                            <form action="index.php?accion=eliminar" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este pedido?');">
                                                <input type="hidden" name="id" value="<?= $p['id_pedido'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
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