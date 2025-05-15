<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio de Sesión - SISECO</title>
    <!-- Enlace a Bootstrap CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .header-container {
            text-align: center;
            padding: 10px;
        }

        .header-image img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>

    <!-- Header con la imagen -->
    <div class="header-container">
        <div class="header-image">
            <img src="assets/siseco.jpg" alt="SISECO" class="img-fluid">
        </div>
    </div>

    <!-- Sección de Login -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3>Inicio de Sesión</h3>
                        <p class="mb-0">Sistema para el Control y Evaluación de Obras</p>
                    </div>
                    <div class="card-body">
                        <form action="login_process.php" method="POST">
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario"
                                    placeholder="Ingrese su usuario" required>
                            </div>
                            <div class="form-group">
                                <label for="clave">Clave</label>
                                <input type="password" class="form-control" id="clave" name="clave"
                                    placeholder="Ingrese su clave" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Acceso</button>
                            <button type="reset" class="btn btn-secondary btn-block mt-2">Limpiar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enlaces a jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="/siseco/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>