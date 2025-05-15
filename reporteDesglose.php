<?php 
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Extraer datos de sesión
$nivel_usuario = $_SESSION['nivel'] ?? null;
$id_dependencia_usuario = $_SESSION['id_dependencia'] ?? null;

include 'includes/header.php'; 
include 'includes/conexion.php';

// Consulta para obtener las dependencias en función del rol
if ($nivel_usuario == 1) {
    // Si es administrador, se obtienen todas las dependencias
    $queryDep = "SELECT * FROM dependencia ORDER BY nombre ASC";
    $resDep = mysqli_query($conn, $queryDep);
} else {
    // Si es capturista, se obtiene únicamente la dependencia asignada al usuario
    $queryDep = "SELECT * FROM dependencia WHERE id_dependencia = " . (int)$id_dependencia_usuario;
    $resDep = mysqli_query($conn, $queryDep);
}
?>

<div class="container mt-4">
    <h1 class="mb-4">Reporte por ejercicio fiscal: Desglose</h1>
    <form action="reporteDesgloseMostrar.php" method="GET">
        <!-- Sección: Datos del Reporte -->
        <div class="card mb-4">
            <div class="card-header">
                Datos del Reporte
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Dependencia -->
                    <div class="col-md-6 mb-3">
                        <label for="dependencia" class="form-label">Dependencia:</label>
                        <?php if ($nivel_usuario == 1): ?>
                            <!-- Para administradores, se muestra el select completo -->
                            <select class="form-select" id="dependencia" name="dependencia" required>
                                <option value="">-----</option>
                                <?php 
                                if ($resDep) {
                                    while ($dep = mysqli_fetch_assoc($resDep)) {
                                        echo "<option value=\"" . htmlspecialchars($dep['nombre']) . "\">" . htmlspecialchars($dep['nombre']) . "</option>";
                                    }
                                } else {
                                    echo "<option value=\"\">No hay dependencias</option>";
                                }
                                ?>
                            </select>
                        <?php else: 
                                // Para capturistas, se extrae la dependencia asignada
                                $dep = mysqli_fetch_assoc($resDep);
                                $depNombre = $dep['nombre'] ?? '';
                        ?>
                            <!-- Se envía la dependencia en un campo oculto y se muestra de forma estática -->
                            <input type="hidden" name="dependencia" value="<?php echo htmlspecialchars($depNombre); ?>">
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($depNombre); ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- Campo: Ejercicio Fiscal -->
                    <div class="col-md-6 mb-3">
                        <label for="ejercicio_fiscal" class="form-label">Ejercicio Fiscal:</label>
                        <select class="form-select" id="ejercicio_fiscal" name="ejercicio_fiscal" required>
                            <option value="">Seleccione...</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botón de Búsqueda -->
        <button type="submit" class="btn btn-primary">BUSCAR</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
