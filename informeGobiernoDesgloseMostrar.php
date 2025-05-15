  <?php
  include 'includes/header.php';
  include 'includes/conexion.php';

  /*
    Se reciben los filtros enviados desde informes22Desglose.php mediante POST (o GET).
    Los filtros son:
      - dependencia: se compara con el nombre de la dependencia.
      - ejercicio_fiscal: se compara con la columna ano_ejercicio_fiscal de la tabla obra.
    Si no se indica ejercicio fiscal se usa por defecto “2022 en adelante”.
  */
  $dependencia = isset($_GET['dependencia']) ? trim($_GET['dependencia']) : '';
  $ejercicio_fiscal = isset($_GET['ejercicio_fiscal']) ? trim($_GET['ejercicio_fiscal']) : '';

  $where = "WHERE 1=1 ";

  if (!empty($dependencia)) {
    $depSafe = mysqli_real_escape_string($conn, $dependencia);
    // Comparamos el nombre de la dependencia exactamente (puedes modificar para coincidencias parciales)
    $where .= " AND d.nombre = '$depSafe' ";
  }

  if (!empty($ejercicio_fiscal)) {
    $yearSafe = mysqli_real_escape_string($conn, $ejercicio_fiscal);

    $prev = $yearSafe - 1;
    // Para Informe de Gobierno, se requiere que:
    // - La fecha de inicio sea exactamente el 16 de octubre del año previo
    // - La fecha de término sea exactamente el 15 de octubre del año buscado.
    $inicioGov = "{$prev}-10-16";
    $finGov = "{$yearSafe}-10-15";

    $where .= " AND o.ano_ejercicio_fiscal = '$yearSafe' AND (
                    (o.fecha_inicio = '$inicioGov' AND o.fecha_termino = '$finGov')
                    OR o.tipo_destino IN ('INFORME_GOBIERNO', 'AMBOS')
                )";
  }
  /* Consulta resumen para las tarjetas:
    - Total de Obras: se cuenta el número de obras de la dependencia.
    - Inversión Programada: se suma la inversión programada en sus 6 subcampos.
    - Inversión Ejercida: se suma la inversión ejercida en sus 6 subcampos.
    - Avance Físico y Avance Financiero: se calcula el promedio (para usar en los gráficos).
  */
  $summarySql = "SELECT 
          COUNT(o.id_obra) AS total_obras,
          SUM(i.inversion_programada_federal + i.inversion_programada_estatal + 
              i.inversion_programada_municipal + i.inversion_programada_credito + 
              i.inversion_programada_beneficiarios + i.inversion_programada_otros) AS inv_programada,
          SUM(i.inversion_ejercida_federal + i.inversion_ejercida_estatal + 
              i.inversion_ejercida_municipal + i.inversion_ejercida_credito + 
              i.inversion_ejercida_beneficiarios + i.inversion_ejercida_otros) AS inv_ejercida,
          AVG(o.avance_fisico_anual) AS avance_fisico,
          AVG(o.avance_financiero_anual) AS avance_financiero
      FROM obra o 
      JOIN inversion i ON o.id_obra = i.id_obra
      LEFT JOIN dependencia d ON o.id_dependencia = d.id_dependencia
      $where";
  $resSummary = mysqli_query($conn, $summarySql);
  $summary = mysqli_fetch_assoc($resSummary);

  /* Consulta detalle para el desglose de obras. Se une obra con inversión y datos de otras tablas.
    Se limitan los resultados a los 30 registros más recientes.
    Cabe destacar que para unir con municipio se toma el id_municipio de localidad.
  */
  $detailSql = "SELECT
      o.nombre AS obra,
      t.nombre AS tipo_obra,
      CONCAT(m.nombre, ', ', l.nombre) AS mpio_loc,
      o.status_obra AS estatus,
      o.etapa,
      o.fecha_inicio,
      o.fecha_termino,
      -- Inversión Programada
      i.inversion_programada_federal,
      i.inversion_programada_estatal,
      i.inversion_programada_municipal,
      i.inversion_programada_credito,
      i.inversion_programada_beneficiarios,
      i.inversion_programada_otros,
      -- Inversión Ejercida
      i.inversion_ejercida_federal,
      i.inversion_ejercida_estatal,
      i.inversion_ejercida_municipal,
      i.inversion_ejercida_credito,
      i.inversion_ejercida_beneficiarios,
      i.inversion_ejercida_otros,
      mi.nombre AS modalidad_inv,
      o.tipo_ejecucion,
      o.avance_fisico_anual,
      o.avance_financiero_anual,
      o.observaciones,
      o.meta_servicio_cantidad,          -- Serv Can
      o.meta_beneficiarios_cantidad,      -- Bene Can
      us.nombre AS servicio_unidad,       -- Serv Uni (nombre de la unidad de servicio)
      ub.nombre AS beneficiarios_unidad   -- Bene Uni (nombre de la unidad de beneficiarios)
  FROM obra o
  JOIN inversion i ON o.id_obra = i.id_obra
  LEFT JOIN dependencia d ON o.id_dependencia = d.id_dependencia
  LEFT JOIN tipo_obra t ON o.id_tipoObra = t.id_tipoObra
  LEFT JOIN localidad l ON o.id_localidad = l.id_localidad
  LEFT JOIN municipio m ON l.id_municipio = m.id_municipio
  LEFT JOIN modalidad_inversion mi ON o.id_modalidadInv = mi.id_modalidadInv
  LEFT JOIN unidad_servicio us ON o.id_servicio = us.id_servicio
  LEFT JOIN unidad_beneficiarios ub ON o.id_beneficiarios = ub.id_beneficiarios
      $where
      ORDER BY o.id_obra DESC";
  $resDetail = mysqli_query($conn, $detailSql);
  ?>

<!-- Estilos para los canvas de los gráficos -->
<style>
  .chart-container {
    position: relative;
    height: 150px;
    width: 150px;
    margin: 0 auto;
  }
</style>

<div class="container mt-4">
  <!-- Encabezado del Reporte -->
  <div class="card text-white" style="background-color: #8B4513;">
    <div class="card-body">
      <h1 class="card-title text-center mb-0">DESGLOSE PARA INFORME DE GOBIERNO POR DEPENDENCIA</h1>
      <h5 class="card-subtitle text-center">Año <?php echo htmlspecialchars($ejercicio_fiscal); ?> -
        <?php echo htmlspecialchars($dependencia); ?>
      </h5>
    </div>
  </div>

  <!-- Tarjetas Resumen -->
  <div class="row mt-4">
    <!-- Total de Obras -->
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Total de Obras</h6>
          <p class="card-text"><?php echo number_format($summary['total_obras']); ?></p>
        </div>
      </div>
    </div>
    <!-- Inversión Programada -->
    <div class="col-md-4">
      <div class="card text-white bg-success mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Inversión Programada</h6>
          <p class="card-text">$<?php echo number_format($summary['inv_programada'], 2); ?></p>
        </div>
      </div>
    </div>
    <!-- Inversión Ejercida -->
    <div class="col-md-4">
      <div class="card text-white bg-danger mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Inversión Ejercida</h6>
          <p class="card-text">$<?php echo number_format($summary['inv_ejercida'], 2); ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Segunda fila: Gráficos para los porcentajes -->
  <div class="row">
    <!-- Gráfico de Avance Físico -->
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Avance Físico</h6>
          <div class="chart-container">
            <canvas id="chartFisico"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- Gráfico de Avance Financiero -->
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body text-center">
          <h6 class="card-title">Avance Financiero</h6>
          <div class="chart-container">
            <canvas id="chartFinanciero"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sección: Tabla de Desglose (a ancho completo) -->
<!-- Contenedor para la tabla de resultados -->
<div class="container-fluid mt-4">
  <div class="card">
    <div class="card-header">
      Desglose de Obras
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <!-- Se establece un min-width para la tabla para mostrar todos los campos -->
        <table class="table table-bordered table-striped" id="tablaObras" style="min-width: 1800px;">
          <thead>
            <!-- Encabezado de dos filas -->
            <tr>
              <th rowspan="2">Obra / Acción</th>
              <th rowspan="2">Tipo Obra</th>
              <th rowspan="2">Mpio y Loc</th>
              <th rowspan="2">Estatus</th>
              <th colspan="2">Fecha</th>
              <th colspan="6">Inversión Programada</th>
              <th colspan="6">Inversión Ejercida</th>
              <th rowspan="2">modalidad Inv</th>
              <th colspan="4">Metas</th>
              <th colspan="2">Avances</th>
              <th rowspan="2">Observaciones</th>
            </tr>
            <tr>
              <th>Ini</th>
              <th>Fin</th>
              <th>Fed</th>
              <th>Est</th>
              <th>Mun</th>
              <th>Cre</th>
              <th>Ben</th>
              <th>Otr</th>
              <th>Fed</th>
              <th>Est</th>
              <th>Mun</th>
              <th>Cre</th>
              <th>Ben</th>
              <th>Otr</th>
              <th>Serv Can</th>
              <th>Serv Uni</th>
              <th>Bene Can</th>
              <th>Bene Uni</th>
              <th>Fis</th>
              <th>Finan</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($resDetail && mysqli_num_rows($resDetail) > 0) {
              while ($row = mysqli_fetch_assoc($resDetail)) {
                echo "<tr>";
                // Columnas fijas
                echo "<td>" . htmlspecialchars($row['obra']) . "</td>";
                echo "<td>" . htmlspecialchars($row['tipo_obra'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['mpio_loc'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['estatus']) . "</td>";
                echo "<td>" . htmlspecialchars($row['fecha_inicio']) . "</td>";
                echo "<td>" . htmlspecialchars($row['fecha_termino']) . "</td>";

                // Inversión Programada (6 columnas)
                echo "<td>$" . number_format($row['inversion_programada_federal'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_programada_estatal'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_programada_municipal'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_programada_credito'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_programada_beneficiarios'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_programada_otros'], 2) . "</td>";

                // Inversión Ejercida (6 columnas)
                echo "<td>$" . number_format($row['inversion_ejercida_federal'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_ejercida_estatal'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_ejercida_municipal'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_ejercida_credito'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_ejercida_beneficiarios'], 2) . "</td>";
                echo "<td>$" . number_format($row['inversion_ejercida_otros'], 2) . "</td>";

                // modalidad Inv (se saca de id_modalidadInv, ya mostrada como modalidad_inv)
                echo "<td>" . htmlspecialchars($row['modalidad_inv']) . "</td>";

                // Metas: sacar de la tabla obra (ya insertadas)
                echo "<td>" . htmlspecialchars($row['meta_servicio_cantidad']) . "</td>";           // Serv Can
                echo "<td>" . htmlspecialchars($row['servicio_unidad']) . "</td>";                    // Serv Uni
                echo "<td>" . htmlspecialchars($row['meta_beneficiarios_cantidad']) . "</td>";          // Bene Can
                echo "<td>" . htmlspecialchars($row['beneficiarios_unidad']) . "</td>";                 // Bene Uni
            
                // Avances (se muestran con el símbolo de %)
                echo "<td>" . htmlspecialchars($row['avance_fisico_anual']) . "%</td>";
                echo "<td>" . htmlspecialchars($row['avance_financiero_anual']) . "%</td>";

                // Observaciones
                echo "<td>" . htmlspecialchars($row['observaciones']) . "</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='26'>No se encontraron obras para los filtros aplicados.</td></tr>";
            }
            ?>
          </tbody>

        </table>
      </div>
      <!-- Botón para Imprimir PDF -->
      <div class="text-center my-4">
        <a href="imprimirInformeDesglose.php?dependencia=<?php echo urlencode($dependencia); ?>&ejercicio_fiscal=<?php echo urlencode($ejercicio_fiscal); ?>"
          class="btn btn-dark" target="_blank">
          Imprimir PDF
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Incluir Chart.js desde CDN -->
<script src="/siseco/assets/js/dist/chart.umd.js"></script>

<!-- Script para generar los gráficos -->
<script>
  // Se usan los valores del resumen para los avances.
  // Se obtienen los promedios o totales que se deseen (en este ejemplo, los promedios de avance físico y financiero).
  const avanceFisico = <?php echo (!empty($summary['avance_fisico']) ? $summary['avance_fisico'] : 0); ?>;
  const avanceFinanciero = <?php echo (!empty($summary['avance_financiero']) ? $summary['avance_financiero'] : 0); ?>;

  // Para el gráfico se muestra el avance y el resto para llegar a 100%.
  const chartFisicoData = [avanceFisico, Math.max(0, 100 - avanceFisico)];
  const chartFinancieroData = [avanceFinanciero, Math.max(0, 100 - avanceFinanciero)];

  // Gráfico de Avance Físico
  const ctxFisico = document.getElementById('chartFisico').getContext('2d');
  new Chart(ctxFisico, {
    type: 'doughnut',
    data: {
      labels: ['Avance', 'Resto'],
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
  const ctxFinanciero = document.getElementById('chartFinanciero').getContext('2d');
  new Chart(ctxFinanciero, {
    type: 'doughnut',
    data: {
      labels: ['Avance', 'Resto'],
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

<?php include 'includes/footer.php'; ?>