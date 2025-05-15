<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos de sesión
$nivel_usuario = $_SESSION['nivel'] ?? null;
$id_dependencia_usuario = $_SESSION['id_dependencia'] ?? null;

include 'includes/header.php';
include 'includes/conexion.php';

// Obtener las dependencias según el nivel del usuario.
if ($nivel_usuario == 1) {
    // Para administradores, se obtienen todas las dependencias.
    $queryDeps = "SELECT * FROM dependencia ORDER BY nombre ASC";
} else {
    // Para capturistas, obtener únicamente la dependencia asignada.
    $queryDeps = "SELECT * FROM dependencia WHERE id_dependencia = " . (int) $id_dependencia_usuario;
}
$resDeps = mysqli_query($conn, $queryDeps);


// Recibir los parámetros de búsqueda (vía GET)
$clave = isset($_GET['claveObra']) ? trim($_GET['claveObra']) : '';
$dependenciaInput = isset($_GET['dependencia']) ? trim($_GET['dependencia']) : '';
$ejercicioFiscalInput = isset($_GET['ejercicioFiscal']) ? trim($_GET['ejercicioFiscal']) : '';
$tipoCaptura = isset($_GET['tipoCaptura']) ? strtolower(trim($_GET['tipoCaptura'])) : 'total';

// Construimos la consulta principal para unir obra e inversión y traer algunos datos relacionados.
// Nota: Puesto que la columna de municipio viene a través de la localidad, se realiza un LEFT JOIN con municipio usando l.id_municipio.
$sql = "SELECT 
            o.id_obra AS clave,
            o.ano_ejercicio_fiscal,
            d.nombre AS dependencia,
            o.nombre AS obra,
            t.nombre AS tipo_obra,
            l.nombre AS localidad,
            m.nombre AS municipio,
            o.status_obra AS estatus,
            o.fecha_inicio,
            o.fecha_termino,
            o.etapa,
            (i.inversion_programada_federal + i.inversion_programada_estatal + i.inversion_programada_municipal + i.inversion_programada_credito + i.inversion_programada_beneficiarios + i.inversion_programada_otros) AS inv_programada,
            (i.inversion_autorizada_federal + i.inversion_autorizada_estatal + i.inversion_autorizada_municipal + i.inversion_autorizada_credito + i.inversion_autorizada_beneficiarios + i.inversion_autorizada_otros) AS inv_autorizada,
            (i.inversion_ejercida_federal + i.inversion_ejercida_estatal + i.inversion_ejercida_municipal + i.inversion_ejercida_credito + i.inversion_ejercida_beneficiarios + i.inversion_ejercida_otros) AS inv_ejercida,
            mi.nombre AS modalidad_inv,
            o.tipo_ejecucion,
            o.avance_fisico_anual,
            o.avance_financiero_anual
        FROM obra o
        JOIN inversion i ON o.id_obra = i.id_obra
        LEFT JOIN dependencia d ON o.id_dependencia = d.id_dependencia
        LEFT JOIN tipo_obra t ON o.id_tipoObra = t.id_tipoObra
        LEFT JOIN localidad l ON o.id_localidad = l.id_localidad
        LEFT JOIN municipio m ON l.id_municipio = m.id_municipio
        LEFT JOIN modalidad_inversion mi ON o.id_modalidadInv = mi.id_modalidadInv
        WHERE 1 ";

// Agregamos condiciones de filtrado según cada campo.


// Restricción por dependencia, solo para capturistas.
if ($nivel_usuario != 1) {
    // Si el usuario es capturista, forzamos que solo se muestren obras de su dependencia.
    $sql .= " AND o.id_dependencia = " . (int) $id_dependencia_usuario . " ";
} else {
    // Si el usuario es admin, aplicamos el filtro si se eligió dependencia en el select.
    if ($dependenciaInput !== "") {
        $ds = mysqli_real_escape_string($conn, mb_strtolower($dependenciaInput));
        $sql .= " AND LOWER(d.nombre) LIKE '%{$ds}%' ";
    }
}

//clave
if ($clave !== "") {
    $cs = mysqli_real_escape_string($conn, mb_strtolower($clave));
    $sql .= " AND (LOWER(o.nombre) LIKE '%{$cs}%' OR o.id_obra = '{$cs}') ";
}


// 3) Filtrado por Ejercicio Fiscal y Tipo Captura, usando comparación exacta para las fechas.
if ($ejercicioFiscalInput !== "") {
    $ej = (int) $ejercicioFiscalInput;
    $prev = $ej - 1;
    // Para Informe de Gobierno, se requiere que:
    // - La fecha de inicio sea exactamente el 16 de octubre del año previo
    // - La fecha de término sea exactamente el 15 de octubre del año buscado.
    $inicioGov = "{$prev}-10-16";
    $finGov = "{$ej}-10-15";

    if ($tipoCaptura === "total") {
        // Captura Total: Se filtran solo por el año fiscal.
        $sql .= " AND o.ano_ejercicio_fiscal = {$ej} ";
    } elseif ($tipoCaptura === "ejerciciofiscal") {
        // Para Ejercicio Fiscal:
        // Se muestran aquellas obras que:
        // - Tengan tipo_destino en 'EJERCICIO_FISCAL' o 'AMBOS'
        // - Y que NO tengan las fechas exactamente iguales al patrón de Informe de Gobierno.
        $sql .= " AND o.ano_ejercicio_fiscal = {$ej}
              AND (
                  o.tipo_destino IN ('EJERCICIO_FISCAL', 'AMBOS')
                  AND NOT (o.fecha_inicio = '$inicioGov' AND o.fecha_termino = '$finGov')
              )";
    } elseif ($tipoCaptura === "informegobierno") {
        // Para Informe de Gobierno:
        // Se muestran aquellas obras que:
        // - Tengan el patrón exacto de fechas (16 de octubre y 15 de octubre)
        //   OR tengan tipo_destino en 'INFORME_GOBIERNO' o 'AMBOS'
        $sql .= " AND o.ano_ejercicio_fiscal = {$ej}
              AND (
                  (o.fecha_inicio = '$inicioGov' AND o.fecha_termino = '$finGov')
                  OR o.tipo_destino IN ('INFORME_GOBIERNO', 'AMBOS')
              )";
    }
}

$filtroActivo = ($clave !== '' || $dependenciaInput !== '' || $ejercicioFiscalInput !== '');
if ($filtroActivo) {
    $sql .= " ORDER BY o.id_obra DESC";
} else {
    $sql .= " ORDER BY o.id_obra DESC LIMIT 30";
}

$result = mysqli_query($conn, $sql);
?>

<!-- Contenedor para el formulario de búsqueda -->
<div class="container mt-4">
    <div class="card text-white" style="background-color: #8B4513;">
        <div class="card-body">
            <h1 class="card-title text-center mb-0">Buscar Obra</h1>
        </div>
    </div>

    <!-- Formulario de búsqueda -->
    <!-- Usamos método GET para facilitar el filtrado con parámetros en la URL -->
    <form class="mt-4" method="GET">
        <div class="card mb-4">
            <div class="card-header">Datos de la Búsqueda</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Clave de la Obra -->
                    <div class="col-md-6 mb-3">
                        <label for="claveObra" class="form-label">Clave de la Obra</label>
                        <input type="text" class="form-control" id="claveObra" name="claveObra"
                            value="<?php echo htmlspecialchars($clave); ?>" placeholder="Ingrese la clave de la obra">
                    </div>
                    <!-- Campo: Dependencia -->
                    <div class="col-md-6 mb-3">
                        <label for="dependencia" class="form-label">Dependencia</label>
                        <select class="form-select" id="dependencia" name="dependencia">
                            <option value="">Seleccione...</option>
                            <?php
                            if ($resDeps) {
                                while ($dep = mysqli_fetch_assoc($resDeps)) {
                                    // Se compara en minúsculas para mantener consistencia con el filtrado
                                    $selected = (strtolower($dep['nombre']) === strtolower($dependenciaInput)) ? "selected" : "";
                                    echo "<option value=\"" . htmlspecialchars($dep['nombre']) . "\" $selected>" . htmlspecialchars($dep['nombre']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Ejercicio Fiscal -->
                    <div class="col-md-6 mb-3">
                        <label for="ejercicioFiscal" class="form-label">Ejercicio Fiscal</label>
                        <input type="text" class="form-control" id="ejercicioFiscal" name="ejercicioFiscal"
                            value="<?php echo htmlspecialchars($ejercicioFiscalInput); ?>"
                            placeholder="Ingrese el ejercicio fiscal">
                    </div>
                    <!-- Campo: Tipo de Captura -->
                    <div class="col-md-6 mb-3">
                        <label for="tipoCaptura" class="form-label">Tipo de Captura</label>
                        <select class="form-select" id="tipoCaptura" name="tipoCaptura">
                            <?php
                            $opcionesTipo = [
                                "total" => "Captura Total",
                                "ejerciciofiscal" => "Captura para el Ejercicio Fiscal",
                                "informegobierno" => "Captura para el Informe de Gobierno",
                            ];
                            foreach ($opcionesTipo as $valor => $texto) {
                                $selected = ($valor === $tipoCaptura) ? "selected" : "";
                                echo "<option value=\"{$valor}\" {$selected}>{$texto}</option>";
                            }
                            ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">BUSCAR</button>
    </form>
</div>

<!-- Contenedor para la tabla de resultados -->
<div class="container-fluid mt-4">
    <div class="table-responsive">
        <table class="table table-bordered table-striped w-100" id="tablaObras">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Ejercicio Fiscal</th>
                    <th>Dependencia</th>
                    <th>Obra</th>
                    <th>Tipo de Obra</th>
                    <th>Localidad</th>
                    <th>Municipio</th>
                    <th>Estatus</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>No. Etapa</th>
                    <th>Inv. Programada</th>
                    <th>Inv. Autorizada</th>
                    <th>Inv. Ejercida</th>
                    <th>Modalidad Inv.</th>
                    <th>Tipo Ejecución</th>
                    <th>Avance Físico</th>
                    <th>Avance Financiero</th>
                    <th style="min-width:170px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['clave']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ano_ejercicio_fiscal']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dependencia'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['obra']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tipo_obra'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['localidad'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['municipio'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['estatus']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_inicio']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_termino']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['etapa']) . "</td>";
                        echo "<td>$" . number_format($row['inv_programada'], 2) . "</td>";
                        echo "<td>$" . number_format($row['inv_autorizada'], 2) . "</td>";
                        echo "<td>$" . number_format($row['inv_ejercida'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($row['modalidad_inv'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['tipo_ejecucion']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['avance_fisico_anual']) . "%</td>";
                        echo "<td>" . htmlspecialchars($row['avance_financiero_anual']) . "%</td>";
                        echo '<td>
                               <div class="d-flex flex-wrap gap-2">
                               <a href="imprimirObraCompacto.php?id=' . urlencode($row['clave']) . '" class="btn btn-sm btn-primary" target="_blank">Imprimir Compacto</a>
                               <a href="imprimirObra.php?id=' . urlencode($row['clave']) . '" class="btn btn-sm btn-secondary" target="_blank">Imprimir</a>
                               <a href="editarObra.php?id=' . urlencode($row['clave']) . '" class="btn btn-sm btn-warning" target="_blank">Modificar</a>
                               <a href="eliminarObra.php?id=' . urlencode($row['clave']) . '" class="btn btn-sm btn-danger" target="_blank">Eliminar</a>
                                 </div>
                                     </td>';
                    }
                } else {
                    echo "<tr><td colspan='19'>No se encontraron obras.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>