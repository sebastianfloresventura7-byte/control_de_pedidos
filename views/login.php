<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollería El Buen Sabor - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            background: #ffffff;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
            font-weight: bold;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <!-- Icono / Logo -->
    <div class="mb-3">
        <img src="assets/img/pollo.jpg" alt="Pollo" style="width: 70px; height: auto;">
    </div>
    
    <h3 class="fw-bold mb-1">El Buen Sabor</h3>
    <p class="text-muted small mb-4">Ingrese sus credenciales para acceder</p>

    <?php if (!empty($error_login)): ?>
        <div class="alert alert-danger p-2 small text-start mb-3">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($error_login); ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de Inicio de Sesión -->
    <form action="index.php?accion=login" method="POST" class="text-start">
        <div class="mb-3">
            <label for="usuario" class="form-label fw-semibold small">Usuario</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Ej. admin" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label fw-semibold small">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" name="btn_login" class="btn btn-warning w-100 py-2">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>