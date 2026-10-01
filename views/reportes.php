<?php
// Configuración de conexión a la base de datos
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

// 1. PROCESAR ACCIONES (Eliminar / Cambiar Estado / Registrar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registrar_pedido'])) {
        $cliente_nom = mysqli_real_escape_string($conn, trim($_POST['cliente']));
        $telefono = mysqli_real_escape_string($conn, trim($_POST['telefono']));
        
        $pedido_input = trim($_POST['pedido_select'] ?? $_POST['pedido'] ?? '');
        if ($pedido_input === 'Otro' && !empty($_POST['pedido_otro'])) {
            $pedido_desc = mysqli_real_escape_string($conn, trim($_POST['pedido_otro']));
        } else {
            $pedido_desc = mysqli_real_escape_string($conn, $pedido_input);
        }

        $total = floatval($_POST['total']);
        $estado = 'Pendiente';

        // Buscar o crear cliente
        $res_cli = mysqli_query($conn, "SELECT id_cliente FROM clientes WHERE nombre = '$cliente_nom' LIMIT 1");
        if ($res_cli && mysqli_num_rows($res_cli) > 0) {
            $cli_data = mysqli_fetch_assoc($res_cli);
            $id_cliente = $cli_data['id_cliente'];
        } else {
            mysqli_query($conn, "INSERT INTO clientes (nombre, telefono) VALUES ('$cliente_nom', '$telefono')");
            $id_cliente = mysqli_insert_id($conn);
        }

        // Intenta insertar en la columna activa
        $sql_ins = "INSERT INTO pedidos (id_cliente, detalle, total, estado, fecha) VALUES ('$id_cliente', '$pedido_desc', '$total', '$estado', NOW())";
        $ok = mysqli_query($conn, $sql_ins);

        if (!$ok) {
            $sql_ins = "INSERT INTO pedidos (id_cliente, descripcion, total, estado, fecha) VALUES ('$id_cliente', '$pedido_desc', '$total', '$estado', NOW())";
            $ok = mysqli_query($conn, $sql_ins);
        }

        if (!$ok) {
            $sql_ins = "INSERT INTO pedidos (id_cliente, pedido, total, estado, fecha) VALUES ('$id_cliente', '$pedido_desc', '$total', '$estado', NOW())";
            mysqli_query($conn, $sql_ins);
        }

        header("Location: index.php?accion=sistema&msj=registrado");
        exit;
    }
}

if (isset($_GET['cambiar_estado']) && isset($_GET['id'])) {
    $id_ped = intval($_GET['id']);
    $nuevo_est = mysqli_real_escape_string($conn, $_GET['cambiar_estado']);
    mysqli_query($conn, "UPDATE pedidos SET estado = '$nuevo_est' WHERE id_pedido = $id_ped");
    header("Location: index.php?accion=sistema");
    exit;
}

if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id_ped = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM pedidos WHERE id_pedido = $id_ped");
    header("Location: index.php?accion=sistema");
    exit;
}

// 2. FILTROS Y BÚSQUEDA
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

$sql = "SELECT 
            p.*,
            c.nombre AS cliente,
            c.telefono
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

// 3. MÉTRICAS
$sql_metricas = "SELECT COUNT(*) as total_pedidos, SUM(total) as total_ingresos FROM pedidos";
$res_metricas = mysqli_query($conn, $sql_metricas);
$metricas = mysqli_fetch_assoc($res_metricas);

$total_ingresos = $metricas['total_ingresos'] ?? 0;
$total_pedidos = $metricas['total_pedidos'] ?? 0;
$ticket_promedio = ($total_pedidos > 0) ? ($total_ingresos / $total_pedidos) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollería El Buen Sabor - Control de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .header-title { background-color: #ffc107; font-weight: bold; }
        .card-stat { border-radius: 12px; border: none; }
    </style>
</head>
<body>

<div class="container my-4">
    <!-- Encabezado -->
    <div class="header-title p-3 rounded d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fa-solid fa-store me-2"></i> POLLERÍA "EL BUEN SABOR" - REGISTRO DE PEDIDOS Y CONTROL DE VENTAS</h4>
        <a href="index.php" class="btn btn-outline-dark btn-sm"><i class="fa-solid fa-house me-1"></i> Inicio</a>
    </div>

    <!-- Formulario de Registro de Nuevo Pedido -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="fa-solid fa-cart-plus me-2 text-warning"></i> Registrar Nuevo Pedido
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?accion=sistema" class="row g-3">
                <input type="hidden" name="registrar_pedido" value="1">
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Nombre del Cliente *</label>
                    <input type="text" name="cliente" class="form-control" placeholder="Ej. Juan Pérez" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" placeholder="Ej. 900234398">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Pedido / Producto *</label>
                    <select name="pedido_select" id="pedido_select" class="form-select" onchange="toggleOtroPedido(this)" required>
                        <option value="" disabled selected>-- Seleccione plato --</option>
                        <option value="1 Pollo a la Brasa + Papas + Ensalada">1 Pollo + Papas + Ensalada</option>
                        <option value="1/2 Pollo a la Brasa + Papas + Ensalada">1/2 Pollo + Papas + Ensalada</option>
                        <option value="1/4 Pollo a la Brasa + Papas + Ensalada">1/4 Pollo + Papas + Ensalada</option>
                        <option value="1/8 Pollo a la Brasa + Papas">1/8 Pollo + Papas</option>
                        <option value="Mostrito (1/4 Pollo + Chaufa + Papas)">Mostrito (1/4 Pollo + Chaufa)</option>
                        <option value="Inka Kola 1.5L">Inka Kola 1.5L</option>
                        <option value="Coca Cola 1.5L">Coca Cola 1.5L</option>
                        <option value="Jarra de Chicha Morada">Jarra de Chicha Morada</option>
                        <option value="Otro">Otro / Combo Personalizado...</option>
                    </select>
                    <input type="text" name="pedido_otro" id="pedido_otro" class="form-control mt-2 d-none" placeholder="Escriba el detalle del pedido...">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Monto Total (S/) *</label>
                    <input type="number" step="0.50" name="total" class="form-control" placeholder="0.00" required>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-warning fw-bold w-100">
                        <i class="fa-solid fa-plus me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filtros de Búsqueda y Botones de Exportación -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="accion" value="sistema">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Búsqueda (Cliente / ID):</label>
                    <input type="text" name="busqueda" class="form-control" placeholder="Nombre de cliente o N° pedido" value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha Desde:</label>
                    <input type="date" name="fecha_desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha Hasta:</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                </div>
                <div class="col-md-2 gap-1 d-flex">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <a href="index.php?accion=sistema" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
                <div class="col-md-3 d-flex gap-2 justify-content-end">
                    <a href="views/exportar_excel.php" class="btn btn-success fw-bold notranslate" translate="no"><i class="fa-solid fa-file-excel me-1"></i> Excel</a>
                    <a href="views/exportar_pdf.php" target="_blank" class="btn btn-danger fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> PDF</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Métricas KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-stat bg-white shadow-sm p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success fs-3">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div>
                    <small class="text-muted fw-bold">Total Ingresos</small>
                    <h3 class="m-0 fw-bold">S/ <?php echo number_format($total_ingresos, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat bg-white shadow-sm p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary fs-3">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <small class="text-muted fw-bold">Total Pedidos</small>
                    <h3 class="m-0 fw-bold"><?php echo $total_pedidos; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat bg-white shadow-sm p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info fs-3">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <div>
                    <small class="text-muted fw-bold">Ticket Promedio</small>
                    <h3 class="m-0 fw-bold">S/ <?php echo number_format($ticket_promedio, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th># Pedido</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Pedido / Detalle</th>
                            <th>Total (S/)</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                    // Escanea todas las claves de la fila buscando el contenido real de la columna
                                    $texto_pedido = '';
                                    foreach ($row as $clave => $valor) {
                                        if (in_array(strtolower($clave), ['detalle', 'descripcion', 'pedido', 'producto', 'plato', 'items']) && !empty(trim($valor))) {
                                            $texto_pedido = $valor;
                                            break;
                                        }
                                    }
                                    if (empty(trim($texto_pedido))) {
                                        $texto_pedido = '-';
                                    }
                                ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo htmlspecialchars($row['id_pedido']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cliente'] ?? 'Sin Registro'); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefono'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($texto_pedido); ?></td>
                                    <td class="fw-bold text-success">S/ <?php echo number_format($row['total'] ?? 0, 2); ?></td>
                                    <td><?php echo !empty($row['fecha']) ? date('d/m/Y H:i', strtotime($row['fecha'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($row['estado'] == 'Pendiente') ? 'warning text-dark' : (($row['estado'] == 'En preparación') ? 'info text-dark' : 'success'); ?>">
                                            <?php echo htmlspecialchars($row['estado'] ?? 'Pendiente'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm me-1">
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Estado
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="index.php?accion=sistema&cambiar_estado=Pendiente&id=<?php echo $row['id_pedido']; ?>">Pendiente</a></li>
                                                <li><a class="dropdown-item" href="index.php?accion=sistema&cambiar_estado=En preparación&id=<?php echo $row['id_pedido']; ?>">En preparación</a></li>
                                                <li><a class="dropdown-item" href="index.php?accion=sistema&cambiar_estado=Completado&id=<?php echo $row['id_pedido']; ?>">Completado</a></li>
                                            </ul>
                                        </div>
                                        <a href="index.php?accion=sistema&eliminar=1&id=<?php echo $row['id_pedido']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('¿Seguro de eliminar el pedido #<?php echo $row['id_pedido']; ?>?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-muted py-4">No se encontraron pedidos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleOtroPedido(select) {
    var inputOtro = document.getElementById('pedido_otro');
    if (select.value === 'Otro') {
        inputOtro.classList.remove('d-none');
        inputOtro.required = true;
    } else {
        inputOtro.classList.add('d-none');
        inputOtro.required = false;
    }
}
</script>
</body>
</html>