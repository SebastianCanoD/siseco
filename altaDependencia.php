<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Encabezado con fondo marrón y título en verde -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center">Alta de Dependencia</h2>
        </div>
    </div>

    <!-- Formulario para dar de alta la dependencia -->
    <form action="altaDependenciaProceso.php" method="POST" class="mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Nombre de la dependencia -->
                <div class="mb-3">
                    <label for="dependencia" class="form-label">Nombre de la Dependencia</label>
                    <input
                        type="text"
                        class="form-control"
                        id="dependencia"
                        name="dependencia"
                        placeholder="Introduce el nombre de la dependencia"
                        required
                    >
                </div>
                <!-- Abreviatura -->
                <div class="mb-3">
                    <label for="abreviatura" class="form-label">Abreviatura</label>
                    <input
                        type="text"
                        class="form-control"
                        id="abreviatura"
                        name="abreviatura"
                        placeholder="Ejemplo: SEC"
                        required
                    >
                </div>
            </div>
        </div>
        <!-- Botones -->
        <button type="submit" class="btn btn-primary">Registrar Dependencia</button>
        <button type="reset" class="btn btn-secondary">Limpiar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
