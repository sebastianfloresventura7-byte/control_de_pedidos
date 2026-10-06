<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Procesar el Inicio de Sesión
$error_login = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_login'])) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validación básica (puedes cambiar las credenciales o conectar a BD si prefieres)
    if (!empty($usuario) && !empty($password)) {
        $_SESSION['usuario_logueado'] = true;
        $_SESSION['usuario_nombre'] = $usuario;
        
        header("Location: index.php?accion=sistema");
        exit();
    } else {
        $error_login = "Por favor ingrese usuario y contraseña.";
    }
}

// Cerrar Sesión
if (isset($_GET['accion']) && $_GET['accion'] === 'logout') {
    session_destroy();
    header("Location: index.php?accion=login");
    exit();
}

// Determinar la vista a cargar
$accion = $_GET['accion'] ?? 'login';

switch ($accion) {
    case 'login':
        require_once 'views/login.php';
        break;

    case 'sistema':
    case 'reportes':
    case 'pedidos':
        // Protección de ruta: verificar inicio de sesión
        if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
            header("Location: index.php?accion=login");
            exit();
        }
        
        if (file_exists('views/reportes.php')) {
            require_once 'views/reportes.php';
        } else {
            die("Error: No se encontró el archivo views/reportes.php");
        }
        break;

    default:
        require_once 'views/login.php';
        break;
}
?>