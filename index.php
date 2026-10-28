<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/usuario_model.php';

// Procesar el Inicio de Sesión
$error_login = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_login'])) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($usuario) && !empty($password)) {
        $usuarioModel = new UsuarioModel();
        $datosUsuario = $usuarioModel->obtenerPorUsuario($usuario);

        // Validar credenciales contra la base de datos
        if ($datosUsuario && $datosUsuario['password'] === $password) {
            $_SESSION['usuario_logueado'] = true;
            $_SESSION['usuario_id'] = $datosUsuario['id'];
            $_SESSION['usuario_nombre'] = $datosUsuario['nombre'];
            $_SESSION['rol'] = $datosUsuario['rol']; // Guardamos el rol (admin, cajero, mesero, cocinero)
            
            header("Location: index.php?accion=sistema");
            exit();
        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
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
        // Si ya está logueado, lo mandamos directo al sistema
        if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true) {
            header("Location: index.php?accion=sistema");
            exit();
        }
        if (file_exists('views/login.php')) {
            require_once 'views/login.php';
        } else {
            // Si aún no creas la carpeta views, puedes mostrar un formulario básico o crearlo dentro de views/
            die("Error: No se encontró el archivo views/login.php");
        }
        break;

    case 'sistema':
    case 'reportes':
    case 'pedidos':
        // Protección de ruta: verificar inicio de sesión
        if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
            header("Location: index.php?accion=login");
            exit();
        }
        
        // Aquí puedes cargar una vista general del panel o redirigir según el rol
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