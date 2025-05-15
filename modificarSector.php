<?php
// modificarSector.php
require __DIR__ . '/includes/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: buscarSector.php');
    exit;
}
$id = (int) $_GET['id'];

// Consulta para obtener los datos del sector
$sql = "SELECT * FROM sector WHERE id_sector = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$sector = $stmt->get_result()->fetch_assoc();
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Modificar Sector</h2>
    </div>
  </div>

  <form action="modificarSectorProceso.php" method="POST">
    <input type="hidden" name="id_sector" value="<?= $sector['id_sector'] ?>">

    <div class="card mb-4">
      <div class="card-header">Datos del Sector</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="nombre" class="form-label">Sector</label>
          <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($sector['nombre']) ?>" required>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="buscarSector.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
