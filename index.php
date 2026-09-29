<?php
// index.php

require_once "config/conexion.php";

$conexionObj = new Conexion();
$conexion = $conexionObj->conectar();

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'dashboard';

switch ($accion) {
    case 'dashboard':
        require_once "views/dashboard.php";
        break;
        
    case 'reportes':
        require_once "views/reportes.php";
        break;

    case 'exportar_excel':
        require_once "exportar_excel.php";
        exit; // Cambiado break por exit

    case 'exportar_pdf':
        require_once "exportar_pdf.php";
        exit; // Cambiado break por exit

    default:
        require_once "views/dashboard.php";
        break;
}
?>