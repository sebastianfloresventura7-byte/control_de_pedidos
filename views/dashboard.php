<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>POLLERÍA "EL BUEN SABOR" - REGISTRO DE PEDIDOS Y CONTROL DE VENTAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra Superior -->
    <div class="bg-warning p-2 d-flex justify-content-between align-items-center px-4 mb-3">
        <h5 class="m-0 fw-bold">🏪 POLLERÍA "EL BUEN SABOR" - REGISTRO DE PEDIDOS Y CONTROL DE VENTAS</h5>
        <div>
            <a href="index.php" class="btn btn-light btn-sm fw-bold">🏠 Inicio</a>
            <a href="index.php?accion=logout" class="btn btn-dark btn-sm fw-bold">↪ Salir</a>
        </div>
    </div>

    <div class="container-fluid px-4">

        <!-- Formulario de Registro de Pedido -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3">🛒 Registrar Nuevo Pedido</h6>
                <form action="index.php?accion=guardar" method="POST" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Nombre del Cliente *</label>
                        <input type="text" name="cliente" class="form-control form-control-sm" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Teléfono</label>
                        <input type="text" name="telefono" class="form-control form-control-sm" placeholder="Ej. 900234398">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Pedido / Producto *</label>
                        <select name="producto" class="form-select form-select-sm" required>
                            <option value="">-- Seleccione plato --</option>
                            <option value="1/4 Pollo a la Brasa">1/4 Pollo a la Brasa</option>
                            <option value="1/2 Pollo a la Brasa">1/2 Pollo a la Brasa</option>
                            <option value="1 Pollo Entero">1 Pollo Entero</option>
                            <option value="Mostrito">Mostrito</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Monto Total (S/) *</label>
                        <input type="number" step="0.01" name="total" class="form-control form-control-sm" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100 fw-bold btn-sm">+ Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filtros y Reportes -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form action="index.php" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Búsqueda (Cliente / ID):</label>
                        <input type="text" name="busqueda" class="form-control form-control-sm" placeholder="Nombre de cliente o N° pedido">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Fecha Desde:</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Fecha Hasta:</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">🔍 Filtrar</button>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">🔄</a>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end gap-1">
                        <a href="views/exportar_excel.php" class="btn btn-success btn-sm fw-bold">📊 Excel</a>
                        <a href="views/exportar_pdf.php" class="btn btn-danger btn-sm fw-bold">📄 PDF</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tarjetas de Métricas -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 bg-white d-flex flex-row align-items-center gap-3">
                    <div class="bg-success-subtle p-3 rounded-circle text-success fs-3">💲</div>
                    <div>
                        <small class="text-muted fw-bold">Total Ingresos</small>
                        <h4 class="m-0 fw-bold">S/ 43.50</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 bg-white d-flex flex-row align-items-center gap-3">
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary fs-3">📑</div>
                    <div>
                        <small class="text-muted fw-bold">Total Pedidos</small>
                        <h4 class="m-0 fw-bold"><?= count($pedidos ?? []) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 bg-white d-flex flex-row align-items-center gap-3">
                    <div class="bg-info-subtle p-3 rounded-circle text-info fs-3">🧮</div>
                    <div>
                        <small class="text-muted fw-bold">Ticket Promedio</small>
                        <h4 class="m-0 fw-bold">S/ 8.70</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Pedidos Principal -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th># Pedido</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Pedido / Detalle</th>
                            <th>Total (S/)</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                            <tr><td colspan="8" class="text-center text-muted p-4">No hay pedidos registrados</td></tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $p): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $p['id_pedido'] ?></td>
                                    <td><?= htmlspecialchars($p['cliente'] ?? 'Cliente General') ?></td>
                                    <td><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['detalle'] ?? '-') ?></td>
                                    <td class="fw-bold text-success">S/ <?= number_format($p['total'], 2) ?></td>
                                    <td><?= $p['fecha'] ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?= $p['estado'] ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Cambiar Estado -->
                                            <form action="index.php?accion=cambiar_estado" method="POST" class="m-0">
                                                <input type="hidden" name="id" value="<?= $p['id_pedido'] ?>">
                                                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="Pendiente" <?= $p['estado'] == 'Pendiente' ? 'selected' : '' ?>>Estado</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="En Preparación">En Preparación</option>
                                                    <option value="Entregado">Entregado</option>
                                                    <option value="Cancelado">Cancelado</option>
                                                </select>
                                            </form>

                                            <!-- BOTÓN VERDE PARA COBRAR PEDIDO -->
                                            <a href="views/cobro.php?id=<?= $p['id_pedido'] ?>" class="btn btn-success btn-sm" title="Cobrar Pedido">
                                                💳
                                            </a>

                                            <!-- Eliminar -->
                                            <form action="index.php?accion=eliminar" method="POST" class="m-0" onsubmit="return confirm('¿Seguro que deseas eliminar este pedido?');">
                                                <input type="hidden" name="id" value="<?= $p['id_pedido'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
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