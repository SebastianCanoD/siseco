<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Encabezado en una card con fondo marrón -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center mb-0">Alta de Metas de Beneficio</h2>
        </div>
    </div>

    <!-- Formulario para dar de alta la meta de beneficio -->
    <form action="altaMetasBeneficioProceso.php" method="POST" class="mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Campo para ingresar el nombre de la meta de beneficio -->
                <div class="mb-3">
                    <label for="metaBeneficio" class="form-label">Meta de Beneficio</label>
                    <input type="text" class="form-control" id="metaBeneficio" name="metaBeneficio" placeholder="Introduce el nombre de la meta de beneficio" required>
                </div>
            </div>
        </div>
        <!-- Botón de envío -->
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
