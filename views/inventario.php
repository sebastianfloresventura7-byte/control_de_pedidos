<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: ../index.php?accion=login");
    exit;
}

$rol = $_SESSION['rol'] ?? '';
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

if ($rol !== 'admin' && $rol !== 'cocinero' && $rol !== 'cajero') {
    echo "<div class='container my-5'><div class='alert alert-danger'>No tienes permisos para ver el inventario. <a href='../index.php?accion=sistema'>Volver</a></div></div>";
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "control_de_pedidos");
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_stock'])) {
    if ($rol === 'admin' || $rol === 'cocinero') {
        $id_producto = intval($_POST['id_producto']);
        $nuevo_stock = intval($_POST['nuevo_stock']);
        
        mysqli_query($conn, "UPDATE inventario SET stock_actual = $nuevo_stock WHERE id_producto = $id_producto");
        $mensaje = "¡Stock actualizado correctamente!";
    }
}

$resultado_inventario = mysqli_query($conn, "SELECT * FROM inventario ORDER BY id_producto ASC");

$res_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventario");
$total_insumos = mysqli_fetch_assoc($res_total)['total'] ?? 0;

$res_criticos = mysqli_query($conn, "SELECT COUNT(*) as criticos FROM inventario WHERE stock_actual <= stock_minimo");
$total_criticos = mysqli_fetch_assoc($res_criticos)['criticos'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollería El Buen Sabor - Control de Inventario y Cocina</title>
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
    <div class="header-title p-3 rounded d-flex justify-content-between align-items-center mb-4 shadow-sm">
        <div>
            <h4 class="m-0"><i class="fa-solid fa-boxes-stacked me-2"></i> INVENTARIO Y BODEGA - COCINA</h4>
            <small class="text-dark">Usuario: <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong> | Rol: <span class="badge bg-dark text-warning text-uppercase"><?php echo htmlspecialchars($rol); ?></span></small>
        </div>
        <div>
            <a href="../index.php?accion=sistema" class="btn btn-outline-dark btn-sm fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Volver al Sistema</a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card card-stat bg-white shadow-sm p-3 d-flex flex-row align-items-center border-start border-warning border-4">
                <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3 text-dark fs-3">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <div>
                    <small class="text-muted fw-bold">Total Insumos Registrados</small>
                    <h3 class="m-0 fw-bold"><?php echo $total_insumos; ?> Ítems</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-stat bg-white shadow-sm p-3 d-flex flex-row align-items-center border-start border-danger border-4">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-3">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <small class="text-muted fw-bold">Alertas de Stock Bajo / Reponer</small>
                    <h3 class="m-0 fw-bold text-danger"><?php echo $total_criticos; ?> Insumos</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-warehouse me-2 text-primary"></i> Stock Actual en Bodega e Insumos de Cocina</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th># ID</th>
                            <th class="text-start">Insumo / Producto</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Unidad</th>
                            <th>Estado de Alerta</th>
                            <?php if ($rol === 'admin' || $rol === 'cocinero'): ?>
                            <th>Acción</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_inventario && mysqli_num_rows($resultado_inventario) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($resultado_inventario)): ?>
                                <?php $es_critico = ($row['stock_actual'] <= $row['stock_minimo']); ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $row['id_producto']; ?></td>
                                    <td class="text-start fw-bold text-dark"><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $es_critico ? 'danger' : 'success'; ?> fs-6 px-3">
                                            <?php echo $row['stock_actual']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-muted"><?php echo $row['stock_minimo']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['unidad_medida']); ?></span></td>
                                    <td>
                                        <?php if ($es_critico): ?>
                                            <span class="badge bg-danger text-white"><i class="fa-solid fa-triangle-exclamation me-1"></i> Stock Bajo / Reponer</span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Stock Óptimo</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($rol === 'admin' || $rol === 'cocinero'): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalStock<?php echo $row['id_producto']; ?>">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Actualizar
                                        </button>

                                        <div class="modal fade" id="modalStock<?php echo $row['id_producto']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header bg-warning">
                                                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-boxes-stacked me-2"></i> Actualizar Stock</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-start p-4">
                                                            <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                                                            <input type="hidden" name="actualizar_stock" value="1">
                                                            <p class="mb-2"><strong>Insumo:</strong> <?php echo htmlspecialchars($row['nombre_producto']); ?></p>
                                                            <p class="mb-3 text-muted">Unidad: <?php echo htmlspecialchars($row['unidad_medida']); ?></p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nueva Cantidad en Stock *</label>
                                                                <input type="number" name="nuevo_stock" class="form-control form-control-lg" value="<?php echo $row['stock_actual']; ?>" min="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-warning fw-bold btn-sm text-dark"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-muted py-4">No hay insumos o productos registrados en el inventario.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>