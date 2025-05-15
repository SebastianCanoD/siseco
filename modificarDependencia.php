<?php
// modificarDependencia.php
require __DIR__ . '/includes/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: buscarDependencia.php');
    exit;
}
$id = (int) $_GET['id'];

// Traer dependencia
$sql = "SELECT * FROM dependencia WHERE id_dependencia = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$dependencia = $stmt->get_result()->fetch_assoc();
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Modificar Dependencia</h2>
    </div>
  </div>

  <form action="modificarDependenciaProceso.php" method="POST" class="mt-4">
    <!-- ID oculto -->
    <input type="hidden" name="id_dependencia" value="<?= $dependencia['id_dependencia'] ?>">

    <div class="card mb-4">
      <div class="card-header">Datos de la Dependencia</div>
      <div class="card-body">
        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre de la Dependencia</label>
          <input
            type="text"
            name="nombre"
            id="nombre"
            class="form-control"
            required
            value="<?= htmlspecialchars($dependencia['nombre']) ?>"
          >
        </div>
        <!-- Abreviatura -->
        <div class="mb-3">
          <label for="abreviatura" class="form-label">Abreviatura</label>
          <input
            type="text"
            name="abreviatura"
            id="abreviatura"
            class="form-control"
            required
            value="<?= htmlspecialchars($dependencia['abreviatura']) ?>"
          >
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="buscarDependencia.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
