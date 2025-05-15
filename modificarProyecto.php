<?php
// modificarProyecto.php
require __DIR__ . '/includes/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: buscarProyecto.php');
    exit;
}
$id = (int) $_GET['id'];

// Consulta para obtener los datos del proyecto
$sql = "SELECT * FROM proyecto WHERE id_proyecto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$proyecto = $stmt->get_result()->fetch_assoc();
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Modificar Proyecto</h2>
    </div>
  </div>

  <form action="modificarProyectoProceso.php" method="POST">
    <input type="hidden" name="id_proyecto" value="<?= $proyecto['id_proyecto'] ?>">

    <div class="card mb-4">
      <div class="card-header">Datos del Proyecto</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="nombre" class="form-label">Proyecto</label>
          <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($proyecto['proyecto_nombre']) ?>" required>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="buscarProyecto.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
