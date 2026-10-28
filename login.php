<?php
session_start();
// Ruta corregida para encontrar el modelo dentro de la carpeta models/
require_once 'models/usuario_model.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    $usuarioModel = new UsuarioModel();
    $datosUsuario = $usuarioModel->obtenerPorUsuario($usuario);

    // Validación de usuario y contraseña
    if ($datosUsuario && $datosUsuario["password"] == $password) {
        $_SESSION["usuario_logueado"] = true; // Añadido para compatibilidad con las validaciones
        $_SESSION["usuario_id"] = $datosUsuario["id"];
        $_SESSION["nombre"]     = $datosUsuario["nombre"];
        $_SESSION["usuario_nombre"] = $datosUsuario["nombre"]; // Para que coincida con reportes.php
        $_SESSION["rol"]        = $datosUsuario["rol"];

        // Redirige al panel principal después de iniciar sesión correctamente
        header("Location: index.php?accion=sistema");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Pollería El Buen Sabor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 shadow-sm" style="width: 380px;">
        <h3 class="text-center mb-3 text-warning fw-bold">Pollería El Buen Sabor</h3>
        <p class="text-center text-muted small mb-4">Ingrese sus credenciales según su rol</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger p-2 fs-6 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold">Usuario</label>
                <input type="text" name="usuario" class="form-control" required placeholder="Ej. admin o juan_caja">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="123">
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold py-2 text-dark">Ingresar al Sistema</button>
        </form>
    </div>
</body>
</html>