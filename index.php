<?php
require_once "config/conexion.php";
require_once "controllers/PedidoController.php";

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
    default:
        $controller->mostrarDashboard();
        break;
}
?>