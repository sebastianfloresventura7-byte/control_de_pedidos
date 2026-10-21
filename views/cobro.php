<?php
// Iniciar sesión si es necesario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_pedido = $_GET['id'] ?? null;
$pedido = null;

// Conexión automática a la base de datos control_de_pedidos
$conexion = @mysqli_connect('localhost', 'root', '', 'control_de_pedidos');

// Si no conecta directamente, busca archivos de conexión alternativos
if (!$conexion && file_exists('../conexion.php')) {
    include('../conexion.php');
} elseif (!$conexion && file_exists('conexion.php')) {
    include('conexion.php');
}

// Consultar el pedido si tenemos conexión e ID válido
if ($id_pedido && isset($conexion) && $conexion) {
    $id_pedido = mysqli_real_escape_string($conexion, $id_pedido);
    $query = "SELECT * FROM pedidos WHERE id_pedido = '$id_pedido'";
    $res = mysqli_query($conexion, $query);
    if ($res && mysqli_num_rows($res) > 0) {
        $pedido = mysqli_fetch_assoc($res);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cobrar Pedido - Pollería El Buen Sabor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Barra Superior -->
    <div class="bg-warning p-2 d-flex justify-content-between align-items-center px-4 mb-4 shadow-sm">
        <h5 class="m-0 fw-bold">🏪 POLLERÍA "EL BUEN SABOR" - MÓDULO DE COBRO</h5>
        <div>
            <a href="../index.php?accion=sistema" class="btn btn-dark btn-sm fw-bold">⬅ Volver al Sistema</a>
        </div>
    </div>

    <div class="container" style="max-width: 600px;">
        <div class="card shadow border-0">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-cash-register me-2"></i> Procesar Cobro de Pedido</h4>
                
                <?php if ($pedido): ?>
                    <div class="alert alert-secondary mb-3">
                        <p class="mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente'] ?? $pedido['nombre_cliente'] ?? 'Cliente General') ?></p>
                        <p class="mb-0"><strong>N° Pedido:</strong> #<?= $pedido['id_pedido'] ?></p>
                    </div>

                    <form action="procesar_pago.php" method="POST">
                        <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total a Pagar (S/)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold">S/</span>
                                <input type="text" name="total" class="form-control fw-bold fs-5 text-success" value="<?= number_format($pedido['total'] ?? $pedido['monto'] ?? 0, 2) ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Método de Pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select" required onchange="toggleEfectivo(this)">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Yape">Yape / Plin</option>
                                <option value="Tarjeta">Tarjeta de Crédito / Débito</option>
                            </select>
                        </div>

                        <div class="mb-3" id="campo_efectivo">
                            <label class="form-label fw-bold">Efectivo Recibido (S/)</label>
                            <input type="number" step="0.01" name="efectivo_recibido" id="efectivo_recibido" class="form-control" placeholder="0.00" oninput="calcularVuelto(<?= $pedido['total'] ?? $pedido['monto'] ?? 0 ?>)">
                        </div>

                        <div class="mb-3" id="campo_vuelto">
                            <label class="form-label fw-bold text-primary">Vuelto a Entregar (S/)</label>
                            <input type="text" id="vuelto_calculado" class="form-control fw-bold text-primary bg-light" value="S/ 0.00" readonly>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 mt-3">
                            <i class="fa-solid fa-check-circle me-1"></i> Confirmar Pago y Cerrar Pedido
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <p class="mb-2">No se encontró información para el pedido <strong>#<?= htmlspecialchars($id_pedido ?? '') ?></strong> en la base de datos <code>control_de_pedidos</code>.</p>
                        <a href="../index.php?accion=sistema" class="btn btn-primary btn-sm">Regresar al panel</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function toggleEfectivo(select) {
        const campoEfectivo = document.getElementById('campo_efectivo');
        const campoVuelto = document.getElementById('campo_vuelto');
        if (select.value === 'Efectivo') {
            campoEfectivo.style.display = 'block';
            campoVuelto.style.display = 'block';
        } else {
            campoEfectivo.style.display = 'none';
            campoVuelto.style.display = 'none';
        }
    }

    function calcularVuelto(total) {
        const recibido = parseFloat(document.getElementById('efectivo_recibido').value) || 0;
        const vuelto = recibido - total;
        document.getElementById('vuelto_calculado').value = 'S/ ' + (vuelto > 0 ? vuelto.toFixed(2) : '0.00');
    }
    </script>
</body>
</html>