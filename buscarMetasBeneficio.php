<?php
// Conexión a la base de datos
require __DIR__ . '/includes/conexion.php';

// Consultar todos los servicios (metas de beneficio) de la tabla 'unidad_servicio'
$sql = "SELECT id_beneficiarios, nombre FROM unidad_beneficiarios ORDER BY id_beneficiarios";
$resultado = $conn->query($sql);

include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Encabezado -->
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h2 class="card-title text-center mb-0">Búsqueda de Metas de Beneficio</h2>
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
                <?php while ($beneficio = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $beneficio['id_beneficiarios'] ?></td>
                        <td><?= htmlspecialchars($beneficio['nombre']) ?></td>
                        <td>
                            <a href="modificarMetasBeneficio.php?id=<?= $beneficio['id_beneficiarios'] ?>" class="btn btn-sm btn-warning">Modificar</a>
                            <a href="eliminarMetasBeneficio.php?id=<?= $beneficio['id_beneficiarios'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Filtrar las filas de la tabla según el texto ingresado en el input de búsqueda
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
