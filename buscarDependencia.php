<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<?php
// Conexión a la base de datos
require __DIR__ . '/includes/conexion.php';

// Consultar todas las dependencias
$sql = "SELECT id_dependencia, nombre FROM dependencia ORDER BY id_dependencia";
$resultado = $conn->query($sql);

include 'includes/header.php';
?>

<div class="container mt-4">
  <!-- Encabezado -->
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center" style="color: white;">Búsqueda de Dependencia</h2>
    </div>
  </div>

  <!-- Barra de búsqueda -->
  <div class="card mt-4">
    <div class="card-body">
      <div class="mb-3">
        <label for="busqueda" class="form-label">Buscar por nombre:</label>
        <input type="text" id="busqueda" class="form-control" placeholder="Escribe el nombre..." onkeyup="filtrarDependencias()">
      </div>
    </div>
  </div>

  <!-- Tabla de resultados -->
  <div class="table-responsive mt-4">
    <table class="table table-bordered" id="tablaDependencias">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($dep = $resultado->fetch_assoc()): ?>
          <tr>
            <td><?= $dep['id_dependencia'] ?></td>
            <td><?= htmlspecialchars($dep['nombre']) ?></td>
            <td>
              <a href="modificarDependencia.php?id=<?= $dep['id_dependencia'] ?>" class="btn btn-sm btn-warning">Modificar</a>
              <a href="eliminarDependencia.php?id=<?= $dep['id_dependencia'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filtrarDependencias() {
  var input = document.getElementById("busqueda");
  var filter = input.value.toUpperCase();
  var table = document.getElementById("tablaDependencias");
  var tr = table.getElementsByTagName("tr");

  for (var i = 1; i < tr.length; i++) {
    var td = tr[i].getElementsByTagName("td")[1]; // columna del nombre
    if (td) {
      var texto = td.textContent || td.innerText;
      tr[i].style.display = texto.toUpperCase().indexOf(filter) > -1 ? "" : "none";
    }
  }
}
</script>

<?php include 'includes/footer.php'; ?>
