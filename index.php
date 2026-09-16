<?php
require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/controllers/PedidoController.php";

$controller = new PedidoController();
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'inicio';

switch ($accion) {
    case 'inicio':
        $controller->mostrarDashboard();
        break;
    case 'nuevo':
        $controller->mostrarFormularioNuevo();
        break;
    case 'guardar':
        $controller->guardarPedido();
        break;
    case 'cambiar_estado':
        $controller->cambiarEstado();
        break;
    default:
        $controller->mostrarDashboard();
        break;
}
?>