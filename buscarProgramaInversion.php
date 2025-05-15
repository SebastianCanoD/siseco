<?php
// Conexión a la base de datos
require __DIR__ . '/includes/conexion.php';

// Consultar todos los programas de inversión de la tabla 'programa_inversion'
$sql = "SELECT id_programaInv, nombre FROM programa_inversion ORDER BY id_programaInv";
$resultado = $conn->query($sql);

include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Encabezado -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center mb-0">Búsqueda de Programa de Inversión</h2>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <div class="row mt-4">
        <div class="col-md-12">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre...">
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="table-responsive mt-4">
        <table class="table table-bordered" id="dataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($programa = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $programa['id_programaInv'] ?></td>
                        <td><?= htmlspecialchars($programa['nombre']) ?></td>
                        <td>
                            <a href="modificarProgramaInversion.php?id=<?= $programa['id_programaInv'] ?>" class="btn btn-sm btn-warning">Modificar</a>
                            <a href="eliminarProgramaInversion.php?id=<?= $programa['id_programaInv'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script para filtrar la tabla según la búsqueda -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll('#dataTable tbody tr');
    rows.forEach(function(row) {
        var cellText = row.cells[1].textContent.toLowerCase(); // Compara por el nombre (columna 1)
        row.style.display = cellText.includes(value) ? '' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
