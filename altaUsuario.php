<?php
// altaUsuario.php

// 1. Conexión a la base de datos y obtención de dependencias
include __DIR__ . '/includes/conexion.php';
$dependencias = $conn->query(
    "SELECT id_dependencia, nombre AS nombre
     FROM dependencia
     ORDER BY nombre"
);

// 2. Incluir encabezado
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center">Alta de usuario</h2>
        </div>
    </div>

    <form action="altaUsuarioProceso.php" method="POST">
        <!-- Card para agrupar los datos del usuario -->
        <div class="card mb-4">
            <div class="card-header">
                Datos del Usuario
            </div>
            <div class="card-body">
                <!-- Primera fila: Nombre y Apellido Paterno -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                            placeholder="Ingrese el nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="paterno" class="form-label">Apellido Paterno</label>
                        <input type="text" name="paterno" id="paterno" class="form-control"
                            placeholder="Ingrese apellido paterno" required>
                    </div>
                </div>
                <!-- Segunda fila: Apellido Materno y Número de Trabajador -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="materno" class="form-label">Apellido Materno</label>
                        <input type="text" name="materno" id="materno" class="form-control"
                            placeholder="Ingrese apellido materno">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="notrabaja" class="form-label">Número de Trabajador</label>
                        <input type="text" name="notrabaja" id="notrabaja" class="form-control"
                            placeholder="Ingrese número de trabajador" required>
                    </div>
                </div>
                <!-- Tercera fila: Contraseña y Cargo -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" id="contrasena" class="form-control"
                            placeholder="Ingrese contraseña inicial" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <input type="text" name="cargo" id="cargo" class="form-control" placeholder="Ingrese el cargo">
                    </div>
                </div>
                <!-- Cuarta fila: Correo y Nivel -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mail" class="form-label">Correo</label>
                        <input type="email" name="mail" id="mail" class="form-control"
                            placeholder="Ingrese el correo electrónico">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nivel" class="form-label">Nivel</label>
                        <select class="form-select" name="nivel" id="nivel" required>
                            <option value="">Seleccione...</option>
                            <option value="1">Administrador</option>
                            <option value="0">Capturista</option>
                        </select>
                    </div>

                </div>
                <!-- Quinta fila: Dependencia -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_dependencia" class="form-label">Dependencia</label>
                        <select name="id_dependencia" id="id_dependencia" class="form-select" required>
                            <option value="">Seleccione una dependencia</option>
                            <?php while ($row = $dependencias->fetch_assoc()): ?>
                                <option value="<?= $row['id_dependencia'] ?>"><?= htmlspecialchars($row['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botones: Enviar y Resetear -->
        <button type="submit" class="btn btn-primary">Agregar Usuario</button>
        <button type="reset" class="btn btn-secondary">Limpiar</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>