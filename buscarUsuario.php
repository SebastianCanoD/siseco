<?php
// buscarUsuario.php

// 1. Conexión y obtención de datos
require __DIR__ . '/includes/conexion.php';

// Traer todas las dependencias para el filtro
$dependencias = $conn->query(
    "SELECT id_dependencia, nombre AS nombre
     FROM dependencia
     ORDER BY nombre"
);

// Traer todos los usuarios junto con su dependencia
$sql = "SELECT u.id_usuario,
               u.nombre,
               u.paterno,
               u.materno,
               u.nivel,
               d.nombre AS dependencia
        FROM usuario u
        LEFT JOIN dependencia d ON u.id_dependencia = d.id_dependencia
        ORDER BY u.id_usuario";
$usuarios = $conn->query($sql);

// Incluir header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <!-- Encabezado -->
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Búsqueda de Usuario(s)</h2>
    </div>
  </div>

  <!-- Filtro por dependencia -->
  <div class="card mt-4">
    <div class="card-body">
      <div class="row align-items-end">
        <div class="col-md-4">
          <label for="dependencia" class="form-label">Dependencia:</label>
          <select name="dependencia" id="dependencia" class="form-select" onchange="filtrarUsuarios()">
            <option value="">Todas</option>
            <?php while ($dep = $dependencias->fetch_assoc()): ?>
              <option value="<?= $dep['nombre'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de resultados -->
  <div class="table-responsive mt-4">
    <table class="table table-bordered" id="tablaUsuarios">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellido Paterno</th>
          <th>Apellido Materno</th>
          <th>Nivel</th>
          <th>Dependencia</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($user = $usuarios->fetch_assoc()): ?>
          <tr>
            <td><?= $user['id_usuario'] ?></td>
            <td><?= htmlspecialchars($user['nombre']) ?></td>
            <td><?= htmlspecialchars($user['paterno']) ?></td>
            <td><?= htmlspecialchars($user['materno']) ?></td>
            <td><?= $user['nivel'] ?></td>
            <td><?= htmlspecialchars($user['dependencia']) ?></td>
            <td>
              <a href="modificarUsuario.php?id=<?= $user['id_usuario'] ?>" class="btn btn-sm btn-warning">Modificar</a>
              <a href="eliminarUsuario.php?id=<?= $user['id_usuario'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filtrarUsuarios() {
  var select = document.getElementById('dependencia');
  var filtro = select.value.toUpperCase();
  var tabla = document.getElementById('tablaUsuarios');
  var filas = tabla.getElementsByTagName('tr');

  for (var i = 1; i < filas.length; i++) {
    var celda = filas[i].getElementsByTagName('td')[5]; // índice 5 = Dependencia
    if (celda) {
      var texto = celda.textContent || celda.innerText;
      filas[i].style.display = (filtro === "" || texto.toUpperCase().indexOf(filtro) > -1) ? "" : "none";
    }
  }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>