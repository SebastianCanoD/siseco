<?php
include 'includes/header.php';
include 'includes/conexion.php';

// Recibimos los filtros vía GET (puedes usar también POST)
$dependencia      = isset($_GET['dependencia']) ? trim($_GET['dependencia']) : '';
$ejercicio_fiscal = isset($_GET['ejercicio_fiscal']) ? trim($_GET['ejercicio_fiscal']) : '';

if (empty($dependencia) || empty($ejercicio_fiscal)) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Faltan parámetros en la búsqueda.</div></div>";
    exit;
}

// Construimos la cláusula WHERE
$where = "WHERE 1=1 ";

// Filtramos por dependencia (comparación exacta)
$depSafe = mysqli_real_escape_string($conn, $dependencia);
$where .= " AND d.nombre = '$depSafe' ";

// Filtramos por año fiscal y aplicamos el criterio para Ejercicio Fiscal:
$yearSafe = mysqli_real_escape_string($conn, $ejercicio_fiscal);
$prev = $yearSafe - 1;
// Para Ejercicio Fiscal, queremos incluir obras del año ingresado que tengan
// en tipo_destino 'EJERCICIO_FISCAL' o 'AMBOS'
// Y que NO presenten el patrón exacto de Informe de Gobierno (fechas: inicio = 16 de octubre del año previo, término = 15 de octubre del año ingresado)
$inicioGov = "{$prev}-10-16";
$finGov    = "{$yearSafe}-10-15";

$where .= " AND o.ano_ejercicio_fiscal = '$yearSafe' 
            AND (
                o.tipo_destino IN ('EJERCICIO_FISCAL', 'AMBOS')
                AND NOT (o.fecha_inicio = '$inicioGov' AND o.fecha_termino = '$finGov')
            )";

// --- Consulta Resumen General ---
// Se cuentan las obras, se suman las inversiones (programadas, modificadas y ejercidas)
// y se obtienen promedios de avances (físico y financiero)
$summarySql = "SELECT 
    d.nombre AS dependencia,
    COUNT(o.id_obra) AS obras,
    SUM(i.inversion_programada_federal + i.inversion_programada_estatal + 
        i.inversion_programada_municipal + i.inversion_programada_credito + 
        i.inversion_programada_beneficiarios + i.inversion_programada_otros) AS prog_total,
    SUM(i.inversion_programada_federal) AS prog_federal,
    SUM(i.inversion_programada_estatal) AS prog_estatal,
    SUM(i.inversion_programada_municipal) AS prog_mpal,
    SUM(i.inversion_programada_credito) AS prog_cred,
    SUM(i.inversion_programada_beneficiarios) AS prog_bene,
    SUM(i.inversion_modificada_federal + i.inversion_modificada_estatal +
        i.inversion_modificada_municipal + i.inversion_modificada_credito +
        i.inversion_modificada_beneficiarios + i.inversion_modificada_otros) AS mod_total,
    SUM(i.inversion_modificada_federal) AS mod_federal,
    SUM(i.inversion_modificada_estatal) AS mod_estatal,
    SUM(i.inversion_modificada_municipal) AS mod_mpal,
    SUM(i.inversion_modificada_credito) AS mod_cred,
    SUM(i.inversion_modificada_beneficiarios) AS mod_bene,
    SUM(i.inversion_ejercida_federal + i.inversion_ejercida_estatal +
        i.inversion_ejercida_municipal + i.inversion_ejercida_credito +
        i.inversion_ejercida_beneficiarios + i.inversion_ejercida_otros) AS eje_total,
    SUM(i.inversion_ejercida_federal) AS eje_federal,
    SUM(i.inversion_ejercida_estatal) AS eje_estatal,
    SUM(i.inversion_ejercida_municipal) AS eje_mpal,
    SUM(i.inversion_ejercida_credito) AS eje_cred,
    SUM(i.inversion_ejercida_beneficiarios) AS eje_bene,
    AVG(o.avance_fisico_anual) AS fis,
    AVG(o.avance_financiero_anual) AS finan
FROM obra o
JOIN inversion i ON o.id_obra = i.id_obra
LEFT JOIN dependencia d ON o.id_dependencia = d.id_dependencia
$where
GROUP BY d.nombre";

$resSummary = mysqli_query($conn, $summarySql);
if (!$resSummary) {
    die("Error en la consulta resumen: " . mysqli_error($conn));
}
$summary = mysqli_fetch_assoc($resSummary);

// Para gráficas, se calculan porcentajes de inversión modificada y ejercida sobre el total programado.
$progTotal = floatval($summary['prog_total']);
$modPercentage = ($progTotal > 0) ? ($summary['mod_total'] / $progTotal * 100) : 0;
$ejePercentage = ($progTotal > 0) ? ($summary['eje_total'] / $progTotal * 100) : 0;

// --- Consulta Detalle de Programas ---
// Se agrupa por programa (mediante unión con programa_inversion) para obtener el desglose.
$detailSql = "SELECT 
    p.nombre AS programa,
    COUNT(o.id_obra) AS obras,
    SUM(i.inversion_programada_federal + i.inversion_programada_estatal + 
        i.inversion_programada_municipal + i.inversion_programada_credito + 
        i.inversion_programada_beneficiarios + i.inversion_programada_otros) AS prog_total,
    SUM(i.inversion_programada_federal) AS prog_federal,
    SUM(i.inversion_programada_estatal) AS prog_estatal,
    SUM(i.inversion_programada_municipal) AS prog_mpal,
    SUM(i.inversion_programada_credito) AS prog_cred,
    SUM(i.inversion_programada_beneficiarios) AS prog_bene,
    SUM(i.inversion_modificada_federal + i.inversion_modificada_estatal +
        i.inversion_modificada_municipal + i.inversion_modificada_credito +
        i.inversion_modificada_beneficiarios + i.inversion_modificada_otros) AS mod_total,
    SUM(i.inversion_modificada_federal) AS mod_federal,
    SUM(i.inversion_modificada_estatal) AS mod_estatal,
    SUM(i.inversion_modificada_municipal) AS mod_mpal,
    SUM(i.inversion_modificada_credito) AS mod_cred,
    SUM(i.inversion_modificada_beneficiarios) AS mod_bene,
    SUM(i.inversion_ejercida_federal + i.inversion_ejercida_estatal +
        i.inversion_ejercida_municipal + i.inversion_ejercida_credito +
        i.inversion_ejercida_beneficiarios + i.inversion_ejercida_otros) AS eje_total,
    SUM(i.inversion_ejercida_federal) AS eje_federal,
    SUM(i.inversion_ejercida_estatal) AS eje_estatal,
    SUM(i.inversion_ejercida_municipal) AS eje_mpal,
    SUM(i.inversion_ejercida_credito) AS eje_cred,
    SUM(i.inversion_ejercida_beneficiarios) AS eje_bene,
    AVG(o.avance_fisico_anual) AS fis,
    AVG(o.avance_financiero_anual) AS finan
FROM obra o
JOIN inversion i ON o.id_obra = i.id_obra
LEFT JOIN dependencia d ON o.id_dependencia = d.id_dependencia
LEFT JOIN programa_inversion p ON o.id_programaInv = p.id_programaInv
$where
GROUP BY p.nombre
ORDER BY p.nombre";

$resDetail = mysqli_query($conn, $detailSql);
if (!$resDetail) {
    die("Error en la consulta detalle: " . mysqli_error($conn));
}
?>

<!-- Aquí se presentarán los resultados (resumen y desglose por programa), por ejemplo en tablas o gráficos -->

<!-- Estilos -->
<style>
  .chart-container {
    position: relative;
    height: 150px;
    width: 150px;
    margin: 0 auto;
  }

  /* Puedes ajustar o quitar la clase w-100 según tu diseño */
</style>

<!-- Encabezado del Reporte -->
<div class="container mt-4">
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h1 class="card-title text-center mb-0">RESUMEN PARA EJERCICIO FISCAL POR DEPENDENCIA</h1>
      <h5 class="card-subtitle text-center">Año <?php echo htmlspecialchars($ejercicio_fiscal); ?> -
        <?php echo htmlspecialchars($dependencia); ?>
      </h5>
    </div>
  </div>

  <!-- Tarjetas Resumen -->
  <div class="row mt-4">
    <!-- TOTAL de Obras -->
    <div class="col-md-3">
      <div class="card text-white bg-primary mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Obras</h6>
          <p class="card-text"><?php echo number_format($summary['obras']); ?></p>
        </div>
      </div>
    </div>
    <!-- Prog. Total -->
    <div class="col-md-3">
      <div class="card text-white bg-success mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Prog. Total</h6>
          <p class="card-text">$<?php echo number_format($summary['prog_total'], 2); ?></p>
        </div>
      </div>
    </div>
    <!-- Mod. Total -->
    <div class="col-md-3">
      <div class="card text-white bg-warning mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Mod. Total</h6>
          <p class="card-text">$<?php echo number_format($summary['mod_total'], 2); ?></p>
        </div>
      </div>
    </div>
    <!-- Eje. Total -->
    <div class="col-md-3">
      <div class="card text-white bg-info mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Eje. Total</h6>
          <p class="card-text">$<?php echo number_format($summary['eje_total'], 2); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sección: Gráficos -->
<div class="container mt-4">
  <div class="row">
    <!-- Gráfico de % Modificada -->
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Avance Físico</h6>
          <div class="chart-container">
            <canvas id="chartModificada"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- Gráfico de % Ejercida -->
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Avance Financiero.</h6>
          <div class="chart-container">
            <canvas id="chartEjercida"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Primera Tabla: Resumen de la Dependencia -->
<div class="container-fluid mt-4">
  <div class="card">
    <div class="card-header">
      Resumen de la Dependencia
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped w-100" id="tablaResumenDependencia">
          <thead>
            <tr>
              <th>Dependencia</th>
              <th>Obras</th>
              <th>Prog. Total</th>
              <th>Prog. Federal</th>
              <th>Prog. Estatal</th>
              <th>Prog. Mpal</th>
              <th>Prog. Cred</th>
              <th>Prog. Bene</th>
              <th>Mod. Total</th>
              <th>Mod. Federal</th>
              <th>Mod. Estatal</th>
              <th>Mod. Mpal</th>
              <th>Mod. Cred</th>
              <th>Mod. Bene</th>
              <th>Eje. Total</th>
              <th>Eje. Federal</th>
              <th>Eje. Estatal</th>
              <th>Eje. Mpal</th>
              <th>Eje. Cred</th>
              <th>Eje. Bene</th>
              <th>Fis %</th>
              <th>Finan %</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($summary) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($summary['dependencia']) . "</td>";
              echo "<td>" . number_format($summary['obras']) . "</td>";
              echo "<td>$" . number_format($summary['prog_total'], 2) . "</td>";
              echo "<td>$" . number_format($summary['prog_federal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['prog_estatal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['prog_mpal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['prog_cred'], 2) . "</td>";
              echo "<td>$" . number_format($summary['prog_bene'], 2) . "</td>";
              echo "<td>$" . number_format($summary['mod_total'], 2) . "</td>";
              echo "<td>$" . number_format($summary['mod_federal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['mod_estatal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['mod_mpal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['mod_cred'], 2) . "</td>";
              echo "<td>$" . number_format($summary['mod_bene'], 2) . "</td>";
              echo "<td>$" . number_format($summary['eje_total'], 2) . "</td>";
              echo "<td>$" . number_format($summary['eje_federal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['eje_estatal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['eje_mpal'], 2) . "</td>";
              echo "<td>$" . number_format($summary['eje_cred'], 2) . "</td>";
              echo "<td>$" . number_format($summary['eje_bene'], 2) . "</td>";
              echo "<td>" . number_format($summary['fis'], 2) . "</td>";
              echo "<td>" . number_format($summary['finan'], 2) . "</td>";
              echo "</tr>";
            } else {
              echo "<tr><td colspan='22'>No se encontraron datos.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Segunda Tabla: Detalle de Programas de la Dependencia -->
<div class="container-fluid mt-4">
  <div class="card">
    <div class="card-header">
      Detalle de Programas de la Dependencia
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped w-100" id="tablaDetalleProgramas">
          <thead>
            <tr>
              <th>Programa</th>
              <th>Obras</th>
              <th>Prog. Total</th>
              <th>Prog. Federal</th>
              <th>Prog. Estatal</th>
              <th>Prog. Mpal</th>
              <th>Prog. Cred</th>
              <th>Prog. Bene</th>
              <th>Mod. Total</th>
              <th>Mod. Federal</th>
              <th>Mod. Estatal</th>
              <th>Mod. Mpal</th>
              <th>Mod. Cred</th>
              <th>Mod. Bene</th>
              <th>Eje. Total</th>
              <th>Eje. Federal</th>
              <th>Eje. Estatal</th>
              <th>Eje. Mpal</th>
              <th>Eje. Cred</th>
              <th>Eje. Bene</th>
              <th>Fis %</th>
              <th>Finan %</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($resDetail && mysqli_num_rows($resDetail) > 0) {
              while ($row = mysqli_fetch_assoc($resDetail)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['programa']) . "</td>";
                echo "<td>" . number_format($row['obras']) . "</td>";
                echo "<td>$" . number_format($row['prog_total'], 2) . "</td>";
                echo "<td>$" . number_format($row['prog_federal'], 2) . "</td>";
                echo "<td>$" . number_format($row['prog_estatal'], 2) . "</td>";
                echo "<td>$" . number_format($row['prog_mpal'], 2) . "</td>";
                echo "<td>$" . number_format($row['prog_cred'], 2) . "</td>";
                echo "<td>$" . number_format($row['prog_bene'], 2) . "</td>";
                echo "<td>$" . number_format($row['mod_total'], 2) . "</td>";
                echo "<td>$" . number_format($row['mod_federal'], 2) . "</td>";
                echo "<td>$" . number_format($row['mod_estatal'], 2) . "</td>";
                echo "<td>$" . number_format($row['mod_mpal'], 2) . "</td>";
                echo "<td>$" . number_format($row['mod_cred'], 2) . "</td>";
                echo "<td>$" . number_format($row['mod_bene'], 2) . "</td>";
                echo "<td>$" . number_format($row['eje_total'], 2) . "</td>";
                echo "<td>$" . number_format($row['eje_federal'], 2) . "</td>";
                echo "<td>$" . number_format($row['eje_estatal'], 2) . "</td>";
                echo "<td>$" . number_format($row['eje_mpal'], 2) . "</td>";
                echo "<td>$" . number_format($row['eje_cred'], 2) . "</td>";
                echo "<td>$" . number_format($row['eje_bene'], 2) . "</td>";
                echo "<td>" . number_format($row['fis'], 2) . "</td>";
                echo "<td>" . number_format($row['finan'], 2) . "</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='22'>No se encontraron datos para el detalle.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
      <!-- Botón para Imprimir PDF -->
      <div class="text-center my-4">
        <a href="imprimirReporteResumen.php?dependencia=<?php echo urlencode($dependencia); ?>&ejercicio_fiscal=<?php echo urlencode($ejercicio_fiscal); ?>"
          class="btn btn-dark" target="_blank">
          Imprimir PDF
        </a>
      </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Incluir Chart.js -->
<script src="/siseco/assets/js/dist/chart.umd.js"></script>
<script>
  // Para los gráficos de avances, usamos los avances ponderados obtenidos:
  const avanceFisico = <?php echo round($summary['fis'], 2); ?>;
  const avanceFinanciero = <?php echo round($summary['finan'], 2); ?>;

  // Se muestran como el porcentaje de avance y el resto hasta 100.
  const chartFisicoData = [avanceFisico, Math.max(0, 100 - avanceFisico)];
  const chartFinancieroData = [avanceFinanciero, Math.max(0, 100 - avanceFinanciero)];

  // Gráfico de Avance Físico
  const ctxFisico = document.getElementById('chartModificada').getContext('2d');
  new Chart(ctxFisico, {
    type: 'doughnut',
    data: {
      labels: ['Avance Físico', 'Resto'],
      datasets: [{
        data: chartFisicoData,
        backgroundColor: ['#ffc107', '#e9ecef']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%'
    }
  });

  // Gráfico de Avance Financiero
  const ctxEje = document.getElementById('chartEjercida').getContext('2d');
  new Chart(ctxEje, {
    type: 'doughnut',
    data: {
      labels: ['Avance Financiero', 'Resto'],
      datasets: [{
        data: chartFinancieroData,
        backgroundColor: ['#17a2b8', '#e9ecef']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%'
    }
  });
</script>