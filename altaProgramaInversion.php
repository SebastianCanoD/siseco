<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Header de la página en una barra de color marrón -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center mb-0">Alta de Programa de Inversión</h2>
        </div>
    </div>

    <!-- Formulario para dar de alta el programa de inversión -->
    <form action="altaProgramaInversionProceso.php" method="POST" class="mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Campo para ingresar el nombre del programa -->
                <div class="mb-3">
                    <label for="programaInversion" class="form-label">Programa de Inversión</label>
                    <input type="text" class="form-control" id="programaInversion" name="programaInversion" placeholder="Introduce el Nombre del Programa de Inversión" required>
                </div>
            </div>
        </div>
        <!-- Botón de envío -->
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
