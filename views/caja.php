<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificación de sesión
if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: ../index.php?accion=login");
    exit;
}

$rol = $_SESSION['rol'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Restricción: Solo cajeros y administrador pueden acceder a la caja
if ($rol !== 'cajero' && $rol !== 'admin') {
    echo "<div class='container my-5'><div class='alert alert-danger'>No tienes permisos para acceder a este módulo de caja. <a href='../index.php?accion=sistema'>Volver</a></div></div>";
    exit;
}

// Conexión a la base de datos
$conn = mysqli_connect("localhost", "root", "", "control_de_pedidos");
if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$mensaje = "";

// 2. Procesar Apertura o Cierre de Caja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['abrir_caja'])) {
        $monto_inicial = floatval($_POST['monto_inicial']);
        $turno = mysqli_real_escape_string($conn, $_POST['turno']);
        
        // Verificar si el usuario ya tiene una caja abierta
        $check = mysqli_query($conn, "SELECT * FROM cajas WHERE usuario_id = $usuario_id AND estado = 'Abierta'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO cajas (usuario_id, monto_inicial, turno, estado, fecha_apertura) VALUES ($usuario_id, $monto_inicial, '$turno', 'Abierta', NOW())");
            $mensaje = "¡Caja abierta exitosamente para el $turno!";
        }
    } elseif (isset($_POST['cerrar_caja'])) {
        $id_caja = intval($_POST['id_caja']);
        $monto_cierre = floatval($_POST['monto_cierre']);
        
        // Calcular el total de ventas acumulado
        $res_ventas = mysqli_query($conn, "SELECT SUM(total) as total FROM pedidos");
        $row_v = mysqli_fetch_assoc($res_ventas);
        $total_ventas = $row_v['total'] ?? 0;

        mysqli_query($conn, "UPDATE cajas SET monto_cierre = $monto_cierre, total_ventas = $total_ventas, estado = 'Cerrada', fecha_cierre = NOW() WHERE id_caja = $id_caja");
        $mensaje = "¡Caja cerrada y rendición de cuentas guardada correctamente!";
    }
}

// Consultar la caja abierta del usuario actual
$caja_abierta = mysqli_query($conn, "SELECT * FROM cajas WHERE usuario_id = $usuario_id AND estado = 'Abierta' LIMIT 1");
$info_caja = mysqli_fetch_assoc($caja_abierta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Caja y Turnos - Pollería El Buen Sabor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container my-5" style="max-width: 650px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-cash-register text-warning me-2"></i> Gestión de Caja y Turnos</h3>
        <a href="../index.php?accion=sistema" class="btn btn-outline-dark btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Volver al Sistema</a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!$info_caja): ?>
        <!-- FORMULARIO DE APERTURA DE CAJA CON SELECTOR DE TURNOS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning fw-bold text-dark fs-5">
                <i class="fa-solid fa-lock-open me-2"></i> Apertura de Caja (Colaborador: <?= htmlspecialchars($nombreUsuario) ?>)
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar Turno de Trabajo *</label>
                        <select name="turno" class="form-select form-select-lg" required>
                            <option value="" disabled selected>-- Elige el turno correspondiente --</option>
                            <option value="Turno Mañana">Turno Mañana</option>
                            <option value="Turno Tarde">Turno Tarde</option>
                        </select>
                        <small class="text-muted">Selecciona el turno en el que realizarás la apertura de caja.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Monto Inicial en Efectivo / Sencillo (S/) *</label>
                        <input type="number" step="0.50" min="0" name="monto_inicial" class="form-control form-control-lg" placeholder="Ej. 100.00" required>
                        <small class="text-muted">Ingrese el fondo inicial entregado para dar vueltos.</small>
                    </div>

                    <button type="submit" name="abrir_caja" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
                        <i class="fa-solid fa-play me-2"></i> Abrir Caja e Iniciar Turno
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- FORMULARIO DE CIERRE DE CAJA -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white fw-bold fs-5">
                <i class="fa-solid fa-lock me-2"></i> Caja Abierta - Rendición de Cuentas
            </div>
            <div class="card-body p-4">
                <div class="bg-light p-3 rounded mb-3 border">
                    <p class="mb-1"><strong>Trabajador:</strong> <?= htmlspecialchars($nombreUsuario) ?></p>
                    <p class="mb-1"><strong>Turno Activo:</strong> <span class="badge bg-dark text-warning"><?= htmlspecialchars($info_caja['turno']) ?></span></p>
                    <p class="mb-1"><strong>Apertura Realizada:</strong> <?= date('d/m/Y H:i', strtotime($info_caja['fecha_apertura'])) ?></p>
                    <p class="mb-0"><strong>Fondo Inicial:</strong> <span class="text-success fw-bold">S/ <?= number_format($info_caja['monto_inicial'], 2) ?></span></p>
                </div>

                <hr class="my-4">

                <form method="POST">
                    <input type="hidden" name="id_caja" value="<?= $info_caja['id_caja'] ?>">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Monto Total en Efectivo al Cierre (S/) *</label>
                        <input type="number" step="0.50" min="0" name="monto_cierre" class="form-control form-control-lg" placeholder="Ej. 450.00" required>
                        <small class="text-muted">Cuente todo el efectivo acumulado en caja para finalizar su turno.</small>
                    </div>

                    <button type="submit" name="cerrar_caja" class="btn btn-danger btn-lg w-100 fw-bold shadow-sm" onclick="return confirm('¿Está seguro de cerrar la caja y guardar su rendición de cuentas?');">
                        <i class="fa-solid fa-stop me-2"></i> Cerrar Caja y Finalizar Turno
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>