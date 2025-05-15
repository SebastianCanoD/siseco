<?php
// modificarProgramaInversion.php
require __DIR__ . '/includes/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: buscarProgramaInversion.php');
    exit;
}
$id = (int) $_GET['id'];

// Consulta para obtener los datos del programa de inversión
$sql = "SELECT * FROM programa_inversion WHERE id_programaInv = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$programaInversion = $stmt->get_result()->fetch_assoc();
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Modificar Programa de Inversión</h2>
    </div>
  </div>

  <form action="modificarProgramaInversionProceso.php" method="POST">
    <input type="hidden" name="id_programaInv" value="<?= $programaInversion['id_programaInv'] ?>">

    <div class="card mb-4">
      <div class="card-header">Datos del Programa de Inversión</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="nombre" class="form-label">Programa de Inversión</label>
          <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($programaInversion['nombre']) ?>" required>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="buscarProgramaInversion.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
