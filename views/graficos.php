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

if ($rol !== 'admin' && $rol !== 'cajero') {
    echo "<div class='container my-5'><div class='alert alert-danger shadow-sm'>No tienes permisos para ver el módulo de estadísticas. <a href='../index.php?accion=sistema'>Volver</a></div></div>";
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "control_de_pedidos");
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Capturar el filtro de turno seleccionado (Todos, Mañana o Noche)
$turno_filtro = $_GET['turno'] ?? 'todos';

$where_turno = "";
if ($turno_filtro === 'manana') {
    // Definimos turno mañana de 08:00 a 16:00
    $where_turno = " AND TIME(fecha) BETWEEN '08:00:00' AND '15:59:59'";
} elseif ($turno_filtro === 'noche') {
    // Definimos turno noche de 16:00 a 23:59
    $where_turno = " AND TIME(fecha) BETWEEN '16:00:00' AND '23:59:59'";
}

// 2. Consulta: Ventas por Día con filtro de turno
$sql_dias = "SELECT DATE_FORMAT(fecha, '%d/%m/%Y') as etiqueta, SUM(total) as total 
             FROM pedidos 
             WHERE 1=1 $where_turno
             GROUP BY DATE(fecha) 
             ORDER BY fecha DESC LIMIT 7";
$res_dias = mysqli_query($conn, $sql_dias);

$labels_dias = [];
$data_dias = [];
if ($res_dias) {
    while ($r = mysqli_fetch_assoc($res_dias)) {
        $labels_dias[] = $r['etiqueta'];
        $data_dias[] = (float)$r['total'];
    }
    $labels_dias = array_reverse($labels_dias);
    $data_dias = array_reverse($data_dias);
}

// 3. Consulta: Ventas por Mes
$sql_meses = "SELECT DATE_FORMAT(fecha, '%m/%Y') as etiqueta, SUM(total) as total 
              FROM pedidos 
              WHERE YEAR(fecha) = YEAR(CURRENT_DATE()) $where_turno
              GROUP BY YEAR(fecha), MONTH(fecha) 
              ORDER BY fecha ASC";
$res_meses = mysqli_query($conn, $sql_meses);

$labels_meses = [];
$data_meses = [];
if ($res_meses) {
    while ($r = mysqli_fetch_assoc($res_meses)) {
        $labels_meses[] = $r['etiqueta'];
        $data_meses[] = (float)$r['total'];
    }
}

// 4. Consulta: Ventas por Año
$sql_anios = "SELECT YEAR(fecha) as etiqueta, SUM(total) as total 
              FROM pedidos 
              WHERE 1=1 $where_turno
              GROUP BY YEAR(fecha) 
              ORDER BY etiqueta ASC";
$res_anios = mysqli_query($conn, $sql_anios);

$labels_anios = [];
$data_anios = [];
if ($res_anios) {
    while ($r = mysqli_fetch_assoc($res_anios)) {
        $labels_anios[] = $r['etiqueta'];
        $data_anios[] = (float)$r['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollería El Buen Sabor - Estadísticas de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .header-title { background-color: #ffc107; font-weight: bold; }
        .nav-tabs .nav-link { color: #495057; border: none; border-bottom: 3px solid transparent; font-weight: 600; }
        .nav-tabs .nav-link.active { color: #000; background-color: transparent; border-bottom: 3px solid #ffc107; }
        .card-grafico { border-radius: 14px; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }
        .leyenda-indicadores {
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container my-4">
    <!-- Encabezado -->
    <div class="header-title p-3 rounded d-flex justify-content-between align-items-center mb-4 shadow-sm">
        <div>
            <h4 class="m-0"><i class="fa-solid fa-chart-column me-2"></i> ESTADÍSTICAS Y GRÁFICOS DE VENTAS</h4>
            <small class="text-dark">Usuario: <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong> | Rol: <span class="badge bg-dark text-warning text-uppercase"><?php echo htmlspecialchars($rol); ?></span></small>
        </div>
        <div>
            <a href="../index.php?accion=sistema" class="btn btn-outline-dark btn-sm fw-bold"><i class="fa-solid fa-arrow-left me-1"></i> Volver al Sistema</a>
        </div>
    </div>

    <!-- Filtro por Turno -->
    <div class="card p-3 mb-4 border-0 shadow-sm bg-white">
        <form method="GET" action="graficos.php" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="fw-bold text-dark"><i class="fa-solid fa-business-time text-warning me-1"></i> Filtrar por Turno:</label>
            </div>
            <div class="col-auto">
                <select name="turno" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    <option value="todos" <?php echo ($turno_filtro === 'todos') ? 'selected' : ''; ?>>Todos los Turnos (Día Completo)</option>
                    <option value="manana" <?php echo ($turno_filtro === 'manana') ? 'selected' : ''; ?>>Turno Mañana / Almuerzo (08:00 - 16:00)</option>
                    <option value="noche" <?php echo ($turno_filtro === 'noche') ? 'selected' : ''; ?>>Turno Noche / Cena (16:00 - 00:00)</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Pestañas -->
    <ul class="nav nav-tabs mb-4 gap-2" id="ventasTabs" role="tablist">
        <li class="nav-item bg-white rounded shadow-sm px-3" role="presentation">
            <button class="nav-link active py-2" id="dia-tab" data-bs-toggle="tab" data-bs-target="#tab-dia" type="button" role="tab"><i class="fa-solid fa-calendar-day me-1 text-warning"></i> Ventas por Día</button>
        </li>
        <li class="nav-item bg-white rounded shadow-sm px-3" role="presentation">
            <button class="nav-link py-2" id="mes-tab" data-bs-toggle="tab" data-bs-target="#tab-mes" type="button" role="tab"><i class="fa-solid fa-calendar-days me-1 text-warning"></i> Ventas por Mes</button>
        </li>
        <li class="nav-item bg-white rounded shadow-sm px-3" role="presentation">
            <button class="nav-link py-2" id="anio-tab" data-bs-toggle="tab" data-bs-target="#tab-anio" type="button" role="tab"><i class="fa-solid fa-calendar me-1 text-warning"></i> Ventas por Año</button>
        </li>
    </ul>

    <!-- Leyenda explicativa de colores -->
    <div class="d-flex gap-3 mb-3 leyenda-indicadores text-muted align-items-center">
        <span><i class="fa-solid fa-square text-success"></i> En Ascenso (Subió)</span>
        <span><i class="fa-solid fa-square text-danger"></i> En Descenso (Bajó)</span>
        <span><i class="fa-solid fa-square text-warning"></i> Base / Estable</span>
    </div>

    <div class="tab-content" id="ventasTabsContent">
        <!-- Día -->
        <div class="tab-pane fade show active" id="tab-dia" role="tabpanel">
            <div class="card card-grafico p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chart-bar me-2 text-warning"></i> Comportamiento de Ingresos por Día (S/)</h5>
                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Últimos registros</span>
                </div>
                <div class="chart-container">
                    <canvas id="chartDias"></canvas>
                </div>
            </div>
        </div>

        <!-- Mes -->
        <div class="tab-pane fade" id="tab-mes" role="tabpanel">
            <div class="card card-grafico p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chart-bar me-2 text-warning"></i> Comportamiento de Ingresos por Mes (S/)</h5>
                    <span class="badge bg-primary text-white"><i class="fa-solid fa-calendar me-1"></i> Año actual</span>
                </div>
                <div class="chart-container">
                    <canvas id="chartMeses"></canvas>
                </div>
            </div>
        </div>

        <!-- Año -->
        <div class="tab-pane fade" id="tab-anio" role="tabpanel">
            <div class="card card-grafico p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chart-bar me-2 text-warning"></i> Comparativa Histórica por Año (S/)</h5>
                    <span class="badge bg-success text-white"><i class="fa-solid fa-chart-line me-1"></i> Histórico General</span>
                </div>
                <div class="chart-container">
                    <canvas id="chartAnios"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function obtenerColoresDinamicos(dataArray) {
    return dataArray.map((valor, index, arr) => {
        if (index === 0) return '#ffc107'; 
        if (valor > arr[index - 1]) return '#198754'; 
        if (valor < arr[index - 1]) return '#dc3545'; 
        return '#ffc107'; 
    });
}

const opcionesComunes = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { 
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
            grid: { display: false }
        }
    }
};

const dataDias = <?php echo json_encode($data_dias); ?>;
const dataMeses = <?php echo json_encode($data_meses); ?>;
const dataAnios = <?php echo json_encode($data_anios); ?>;

new Chart(document.getElementById('chartDias').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_dias); ?>,
        datasets: [{
            label: 'Ingresos Diarios (S/)',
            data: dataDias,
            backgroundColor: obtenerColoresDinamicos(dataDias),
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: opcionesComunes
});

new Chart(document.getElementById('chartMeses').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_meses); ?>,
        datasets: [{
            label: 'Ingresos Mensuales (S/)',
            data: dataMeses,
            backgroundColor: obtenerColoresDinamicos(dataMeses),
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: opcionesComunes
});

new Chart(document.getElementById('chartAnios').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_anios); ?>,
        datasets: [{
            label: 'Ingresos Anuales (S/)',
            data: dataAnios,
            backgroundColor: obtenerColoresDinamicos(dataAnios),
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: opcionesComunes
});
</script>
</body>
</html>