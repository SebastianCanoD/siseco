<?php
// modificarUsuario.php
require __DIR__ . '/includes/conexion.php';
if (session_status() !== PHP_SESSION_ACTIVE)
  session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: buscarUsuario.php');
  exit;
}
$id = (int) $_GET['id'];

$sql = "SELECT u.*, d.id_dependencia, d.nombre AS nombre_dependencia
        FROM usuario u
        LEFT JOIN dependencia d ON u.id_dependencia = d.id_dependencia
        WHERE u.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$deps = $conn->query("SELECT id_dependencia, nombre FROM dependencia ORDER BY nombre");

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h2 class="card-title text-center">Modificar Usuario</h2>
    </div>
  </div>

  <form action="ModificarUsuarioProceso.php" method="POST">
    <input type="hidden" name="id_usuario" value="<?= $user['id_usuario'] ?>">

    <div class="card mb-4">
      <div class="card-header">Datos del Usuario</div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control"
              value="<?= htmlspecialchars($user['nombre']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="paterno" class="form-label">Apellido Paterno</label>
            <input type="text" name="paterno" id="paterno" class="form-control"
              value="<?= htmlspecialchars($user['paterno']) ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="materno" class="form-label">Apellido Materno</label>
            <input type="text" name="materno" id="materno" class="form-control"
              value="<?= htmlspecialchars($user['materno']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label for="notrabaja" class="form-label">Número de Trabajador</label>
            <input type="text" name="notrabaja" id="notrabaja" class="form-control"
              value="<?= htmlspecialchars($user['notrabaja']) ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="contrasena" class="form-label">Nueva Contraseña (opcional)</label>
            <input type="password" name="contrasena" id="contrasena" class="form-control"
              placeholder="Dejar en blanco para no cambiar">
          </div>
          <div class="col-md-6 mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <input type="text" name="cargo" id="cargo" class="form-control"
              value="<?= htmlspecialchars($user['cargo']) ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="mail" class="form-label">Correo</label>
            <input type="email" name="mail" id="mail" class="form-control"
              value="<?= htmlspecialchars($user['mail']) ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label for="nivel" class="form-label">Nivel</label>
            <select class="form-select" name="nivel" id="nivel" required>
              <option value="1" <?= ($user['nivel'] == 1) ? 'selected' : '' ?>>Administrador</option>
              <option value="0" <?= ($user['nivel'] == 0) ? 'selected' : '' ?>>Capturista</option>
            </select>
          </div>

        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="id_dependencia" class="form-label">Dependencia</label>
            <select name="id_dependencia" id="id_dependencia" class="form-select" required>
              <?php while ($d = $deps->fetch_assoc()): ?>
                <option value="<?= $d['id_dependencia'] ?>"
                  <?= $d['id_dependencia'] == $user['id_dependencia'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($d['nombre']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    <a href="buscarUsuario.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>