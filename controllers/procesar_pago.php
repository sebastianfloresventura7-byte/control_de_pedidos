<?php
// Incluir conexión y modelos necesarios
require_once("../config/conexion.php"); // Ajusta según la ruta de tu conexión

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pedido = $_POST['id_pedido'];
    $metodo_pago = $_POST['metodo_pago'];
    $monto_pagado = isset($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : 0;
    
    // Aquí puedes consultar el total real del pedido desde tu base de datos
    // Supongamos que el total del pedido es 45.00 para este ejemplo:
    $total_a_pagar = 45.00; 

    $vuelto = 0;
    if ($metodo_pago === 'Efectivo') {
        if ($monto_pagado < $total_a_pagar) {
            echo "<script>alert('El monto pagado es menor al total.'); window.history.back();</script>";
            exit;
        }
        $vuelto = $monto_pagado - $total_a_pagar;
    } else {
        // Si paga con Yape, Plin o Tarjeta, el monto pagado es exacto al total
        $monto_pagado = $total_a_pagar;
    }

    // Actualizar el estado de pago en la base de datos
    $sql = "UPDATE pedidos SET metodo_pago = '$metodo_pago', estado_pago = 'Pagado', monto_pagado = $monto_pagado, vuelto = $vuelto WHERE id = $id_pedido";
    
    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('¡Pago registrado con éxito! Vuelto: S/. " . number_format($vuelto, 2) . "'); window.location.href='../views/pedidos.php';</script>";
    } else {
        echo "Error al registrar el pago: " . mysqli_error($conexion);
    }
}
?>
