<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Bienvenido a SISECO</h1>
    <p class="text-center">Sistema web para el control y evaluación de obras</p>
</div>
<div style="text-align: center;">
<img src="assets/fondo.jpg" alt="Descripción de la imagen" width="1000" height="600" />
</div>
</body>
</html>

<?php include 'includes/footer.php'; ?>
