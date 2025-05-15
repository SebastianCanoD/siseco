<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Encabezado en una card con fondo marrón -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center mb-0">Alta de Metas de Servicio</h2>
        </div>
    </div>

    <!-- Formulario para dar de alta la meta de servicio -->
    <form action="altaMetasServicioProceso.php" method="POST" class="mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Campo para ingresar el nombre de la meta de servicio -->
                <div class="mb-3">
                    <label for="metaServicio" class="form-label">Meta de Servicio</label>
                    <input type="text" class="form-control" id="metaServicio" name="metaServicio" placeholder="Introduce el nombre de la meta de servicio" required>
                </div>
            </div>
        </div>
        <!-- Botón de envío -->
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
