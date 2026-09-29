<?php
// views/reportes.php

// ----------------------------------------------------
// 1. PROCESAR REGISTRO DE NUEVO PEDIDO (SI SE ENVIÓ EL FORMULARIO)
// ----------------------------------------------------
$mensaje_exito = "";
$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pedido'])) {
    $cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
    $cliente_telefono = trim($_POST['cliente_telefono'] ?? '');
    $producto = $_POST['producto'] ?? '';
    $cantidad = intval($_POST['cantidad'] ?? 1);
    $precio_unitario = floatval($_POST['precio_unitario'] ?? 0);

    if (!empty($cliente_nombre) && !empty($producto) && $precio_unitario > 0) {
        try {
            // Verificar o registrar cliente
            $stmt_c = $conexion->prepare("SELECT id_cliente FROM clientes WHERE nombre = :nombre LIMIT 1");
            $stmt_c->execute([':nombre' => $cliente_nombre]);
            $cliente = $stmt_c->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $id_cliente = $cliente['id_cliente'];
            } else {
                $stmt_inst_c = $conexion->prepare("INSERT INTO clientes (nombre, telefono) VALUES (:nombre, :telefono)");
                $stmt_inst_c->execute([':nombre' => $cliente_nombre, ':telefono' => $cliente_telefono]);
                $id_cliente = $conexion->lastInsertId();
            }

            // Registrar pedido
            $total = $precio_unitario * $cantidad;
            $stmt_p = $conexion->prepare("INSERT INTO pedidos (id_cliente, total, estado, fecha) VALUES (:id_cliente, :total, 'Pendiente', NOW())");
            $stmt_p->execute([':id_cliente' => $id_cliente, ':total' => $total]);

            $mensaje_exito = "¡Pedido registrado correctamente!";
        } catch (Exception $e) {
            $mensaje_error = "Error al registrar el pedido: " . $e->getMessage();
        }
    } else {
        $mensaje_error = "Por favor completa todos los campos obligatorios del pedido.";
    }
}

// ----------------------------------------------------
// 2. CONSULTA Y FILTROS PARA EL REPORTE DE VENTAS
// ----------------------------------------------------
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

// Cálculo de métricas
$total_ingresos = 0;
$total_pedidos = count($ventas);

foreach ($ventas as $v) {
    if ($v['estado'] !== 'Anulado') {
        $total_ingresos += $v['precio'];
    }
}

$ticket_promedio = $total_pedidos > 0 ? ($total_ingresos / $total_pedidos) : 0;
?>

<!-- Estilos Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<div class="container-fluid py-4 bg-light min-vh-100">

    <!-- Mensajes de Estado -->
    <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success alert-dismissible fade show fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= $mensaje_exito ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $mensaje_error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN 1: FORMULARIO DE REGISTRO DE PEDIDOS -->
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
        <div class="card-header bg-warning text-dark fw-bold py-3">
            <i class="bi bi-shop me-2"></i> POLLERÍA "EL BUEN SABOR" - REGISTRO DE PEDIDOS
        </div>
        <div class="card-body p-4">
            <form method="POST" action="index.php?accion=reportes" class="row g-3">
                <input type="hidden" name="registrar_pedido" value="1">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nombre del Cliente *</label>
                    <input type="text" name="cliente_nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="cliente_telefono" class="form-control" placeholder="Ej. 987654321">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Producto / Plato *</label>
                    <select name="producto" id="producto_select" class="form-select" onchange="actualizarPrecio()" required>
                        <option value="" data-precio="0">-- Seleccionar Opción --</option>
                        <optgroup label="🍗 Pollo a la Brasa">
                            <option value="1/8 de Pollo" data-precio="12.00">1/8 de Pollo + Papas</option>
                            <option value="1/4 de Pollo" data-precio="18.50">1/4 de Pollo + Papas + Ensalada</option>
                            <option value="1/2 Pollo" data-precio="35.00">1/2 Pollo + Papas + Ensalada</option>
                            <option value="1 Pollo Entero" data-precio="65.00">1 Pollo Entero + Papas + Ensalada</option>
                            <option value="Mostrito" data-precio="22.00">Mostrito (1/4 Pollo + Chaufa + Papas)</option>
                        </optgroup>
                        <optgroup label="🥤 Bebidas">
                            <option value="Inka Kola 1.5L" data-precio="9.50">Inka Kola 1.5L</option>
                            <option value="Coca Cola 1.5L" data-precio="9.50">Coca Cola 1.5L</option>
                            <option value="Inka Kola Personal" data-precio="4.50">Inka Kola Personal</option>
                            <option value="Coca Cola Personal" data-precio="4.50">Coca Cola Personal</option>
                            <option value="Chicha Morada 1L" data-precio="8.00">Chicha Morada 1L</option>
                        </optgroup>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label fw-semibold">Cant.</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" onchange="calcularTotal()" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Precio Unit. (S/)</label>
                    <input type="number" step="0.10" name="precio_unitario" id="precio_unitario" class="form-control" placeholder="0.00" onchange="calcularTotal()" required>
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <span class="fs-5 fw-bold text-secondary">Total a Pagar: </span>
                        <span class="fs-4 fw-bold text-success" id="total_pagar">S/ 0.00</span>
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Guardar y Registrar Pedido
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SECCIÓN 2: TARJETAS DE MÉTRICAS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success fs-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Total Ingresos</span>
                        <h4 class="fw-bold mb-0 text-dark">S/ <?= number_format($total_ingresos, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary fs-3">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Total Pedidos</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= $total_pedidos ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info fs-3">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold">Ticket Promedio</span>
                        <h4 class="fw-bold mb-0 text-dark">S/ <?= number_format($ticket_promedio, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: FILTROS DE BÚSQUEDA Y REPORTE -->
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="accion" value="reportes">
                
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Búsqueda (Cliente / ID):</label>
                    <input type="text" name="busqueda" class="form-control" placeholder="Nombre de cliente o N° pedido" value="<?= htmlspecialchars($busqueda) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fecha Desde:</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fecha Hasta:</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold w-100"><i class="bi bi-filter"></i> Filtrar</button>
                    <a href="index.php?accion=reportes" class="btn btn-outline-secondary fw-bold w-100">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- BOTONES DE EXPORTACIÓN -->
    <div class="mb-3 d-flex gap-2">
        <a href="exportar_excel.php?busqueda=<?= urlencode($busqueda) ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>" class="btn btn-success fw-bold">
            <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
        </a>
        <a href="exportar_pdf.php?busqueda=<?= urlencode($busqueda) ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>" target="_blank" class="btn btn-danger fw-bold">
            <i class="bi bi-file-earmark-pdf me-1"></i> Exportar a PDF / Imprimir
        </a>
    </div>

    <!-- SECCIÓN 4: TABLA DE HISTORIAL DE PEDIDOS -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3"># Pedido</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ventas)): ?>
                            <?php foreach ($ventas as $v): ?>
                                <tr>
                                    <td class="ps-3 fw-bold">#<?= $v['id_pedido'] ?></td>
                                    <td><?= htmlspecialchars($v['cliente']) ?></td>
                                    <td><?= htmlspecialchars($v['telefono'] ?? '-') ?></td>
                                    <td class="fw-semibold">S/ <?= number_format($v['precio'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($v['estado']) ?></span>
                                    </td>
                                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No se encontraron datos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para autocompletar precios y totales -->
<script>
function actualizarPrecio() {
    const select = document.getElementById('producto_select');
    const precio = select.options[select.selectedIndex].getAttribute('data-precio');
    if (precio) {
        document.getElementById('precio_unitario').value = precio;
    }
    calcularTotal();
}

function calcularTotal() {
    const cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
    const precio = parseFloat(document.getElementById('precio_unitario').value) || 0;
    const total = cantidad * precio;
    document.getElementById('total_pagar').innerText = 'S/ ' + total.toFixed(2);
}
</script>