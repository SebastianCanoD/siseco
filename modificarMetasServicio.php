<?php
// modificarMetasServicio.php
require __DIR__ . '/includes/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: buscarMetasServicio.php');
    exit;
}
$id = (int) $_GET['id'];

// Consulta para obtener los datos de la meta de servicio
$sql = "SELECT * FROM unidad_servicio WHERE id_servicio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$metaServicio = $stmt->get_result()->fetch_assoc();
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Modificar Meta de Servicio</h2>
    </div>
  </div>

  <form action="modificarMetasServicioProceso.php" method="POST">
    <input type="hidden" name="id_servicio" value="<?= $metaServicio['id_servicio'] ?>">

    <div class="card mb-4">
      <div class="card-header">Datos de la Meta de Servicio</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="nombre" class="form-label">Meta de Servicio</label>
          <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($metaServicio['nombre']) ?>" required>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="buscarMetasServicio.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
    