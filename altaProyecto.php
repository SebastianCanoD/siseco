<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Encabezado en una card con fondo marrón -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center mb-0">Alta de Proyecto</h2>
        </div>
    </div>

    <!-- Formulario para dar de alta el proyecto -->
    <form action="altaProyectoProceso.php" method="POST" class="mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Campo para ingresar el nombre del proyecto -->
                <div class="mb-3">
                    <label for="proyectoNombre" class="form-label">Proyecto</label>
                    <input type="text" class="form-control" id="proyectoNombre" name="proyectoNombre" placeholder="Introduce el nombre del proyecto" required>
                </div>
            </div>
        </div>
        <!-- Botón de envío -->
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
