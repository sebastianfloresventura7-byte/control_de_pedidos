<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
$conexion = @mysqli_connect('localhost', 'root', '', 'control_de_pedidos');

if (!$conexion && file_exists('../conexion.php')) {
    include('../conexion.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = $_POST['id_pedido'] ?? null;
    $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';

    if ($id_pedido && isset($conexion)) {
        $id_pedido = mysqli_real_escape_string($conexion, $id_pedido);
        $metodo_pago = mysqli_real_escape_string($conexion, $metodo_pago);

        // Actualizar el estado del pedido a 'Completado' o 'Pagado'
        $query = "UPDATE pedidos SET estado = 'Completado' WHERE id_pedido = '$id_pedido'";
        mysqli_query($conexion, $query);
    }
}

// Redirigir de regreso al panel principal del sistema
header("Location: ../index.php?accion=sistema&msg=pagado");
exit();
?>