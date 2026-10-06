<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validación de credenciales
    if ($usuario === 'admin' && $password === '123456') {
        $_SESSION['usuario_logueado'] = true;
        $_SESSION['usuario_nombre'] = $usuario;

        header("Location: ../index.php?accion=sistema");
        exit;
    } else {
        header("Location: ../index.php?accion=login&error=1");
        exit;
    }
} else {
    header("Location: ../index.php?accion=login");
    exit;
}