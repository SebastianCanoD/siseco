<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

include 'includes/conexion.php';

// Recibimos los filtros vía GET
$dependencia = isset($_GET['dependencia']) ? trim($_GET['dependencia']) : '';
$ejercicio_fiscal = isset($_GET['ejercicio_fiscal']) ? trim($_GET['ejercicio_fiscal']) : '';

if (empty($dependencia) || empty($ejercicio_fiscal)) {
  die("Faltan parámetros en la búsqueda.");
}

// Construimos la cláusula WHERE
$where = "WHERE 1=1 ";

// Filtramos por dependencia (comparación exacta)
$depSafe = mysqli_real_escape_string($conn, $dependencia);
$where .= " AND d.nombre = '$depSafe' ";

// Filtramos por año fiscal y aplicamos el criterio para Informe de Gobierno
$yearSafe = mysqli_real_escape_string($conn, $ejercicio_fiscal);
$prev = $yearSafe - 1;
// Se definen las fechas esperadas para Informe de Gobierno:
// Por ejemplo, para ejercicio_fiscal 2025 se espera:
//   fecha_inicio = '2024-10-16'
//   fecha_termino  = '2025-10-15'
$inicioGov = "{$prev}-10-16";
$finGov = "{$yearSafe}-10-15";

$where .= " AND o.ano_ejercicio_fiscal = '$yearSafe' 
            AND (
                (o.fecha_inicio = '$inicioGov' AND o.fecha_termino = '$finGov')
                OR o.tipo_destino IN ('INFORME_GOBIERNO', 'AMBOS')
            )";

// Consulta Resumen
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

// Consulta Detalle
$detailSql = "SELECT
    o.nombre AS obra,
    t.nombre AS tipo_obra,
    CONCAT(m.nombre, ', ', l.nombre) AS mpio_loc,
    o.status_obra AS estatus,
    o.etapa,
    o.fecha_inicio,
    o.fecha_termino,
    i.inversion_programada_federal,
    i.inversion_programada_estatal,
    i.inversion_programada_municipal,
    i.inversion_programada_credito,
    i.inversion_programada_beneficiarios,
    i.inversion_programada_otros,
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
    o.meta_servicio_cantidad,
    o.meta_beneficiarios_cantidad,
    us.nombre AS servicio_unidad,
    ub.nombre AS beneficiarios_unidad
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

// Datos fijos para imágenes (asegúrate de usar URLs absolutas o base64)
//$headerImage = $_SERVER['DOCUMENT_ROOT'] . "/assets/logoGobierno.jpg";

$path = $_SERVER['DOCUMENT_ROOT'] . '/siseco/assets/logoGobierno.jpg';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$headerImage = 'data:image/' . $type . ';base64,' . base64_encode($data);

$html = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Desglose - Reporte Fiscal</title>
  <style>
    /* Reducir tamaño general de la fuente y márgenes */
    body { 
      font-family: Arial, sans-serif; 
      font-size: 7pt; 
      margin: 0; 
      padding: 0; 
      background: #fff;
    }
    /* Envolver todo en un contenedor que se escale */
    .outer-container {
      transform: scale(0.8);
      transform-origin: top left;
      /* Ajusta el ancho para compensar el escalado, si es necesario */
      width: 125%;
    }
    .header, .footer { 
      text-align: center; 
    }
    .header img { 
      max-width: 100%; 
    }
    .data-header { 
      text-align: center; 
      margin: 5px 0; 
    }
    .data-header h1 {
      font-size: 14pt; 
      margin: 0;
      display: inline-block;
      background: rgba(126, 13, 13, 0.92).6); /* tono vino claro semitransparente */
      color: #fff;
      padding: 5px 10px;
      border-radius: 4px;
    }
    .data-header h5 { 
      margin: 3px 0 8px; 
      font-weight: normal;
      font-size: 10pt;
    }
    /* Convertir las tarjetas resumen en tabla simple para ahorrar espacio */
    .cards { 
      width: 100%; 
      border-collapse: collapse; 
      margin-bottom: 10px; 
    }
    .cards td {
      background: rgba(114, 13, 13, 0.94);
      color: #fff;
      text-align: center;
      padding: 5px;
      border: 1px solid rgb(0, 0, 0);
    }
    .table-container { 
      width: 100%; 
      overflow-x: auto; 
      margin-bottom: 10px; 
    }
    table { 
      border-collapse: collapse; 
      width: 100%; 
      min-width: 900px; /* Reducido de 1200px a 900px */
    }
    th, td { 
      border: 1px solid #444; 
      padding: 3px; /* Reducido el padding */
      text-align: center; 
    }
       /* Footer fijo */
    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7pt;
      color: #444;
    }
    .footer hr {
      margin: 0 10px;
      border: none;
      border-top: 0.5px solid #444;
    }
    .footer p {
      margin: 2px;
    }

  </style>
    <script type="text/php">
    if ( isset($pdf) ) {
      $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
      // Ajusta las coordenadas (x, y) según el tamaño del papel
      $pdf->page_text(400, 585, "Página {PAGE_NUM} / {PAGE_COUNT}", $font, 7, array(0,0,0));
    }
  </script>

</head>

<body>
  <!-- Encabezado -->
  <div class="outer-container">
      <div class="header">
        <img src="' . $headerImage . '" alt="Encabezado">
    </div>

  <!-- Datos principales -->
  <div class="data-header">
    <h1>DESGLOSE PARA INFORME DE GOBIERNO POR DEPENDENCIA</h1>
    <h5>Año ' . htmlspecialchars($ejercicio_fiscal) . ' - ' . htmlspecialchars($dependencia) . '</h5>
  </div>

  <!-- TARJETAS RESUMEN COMO TABLA -->
  <table class="cards">
    <tr>
      <td>
        <strong>Total de Obras</strong><br>
        ' . number_format($summary['total_obras']) . '
      </td>
      <td>
        <strong>Inversión Programada</strong><br>
        $' . number_format($summary['inv_programada'], 2) . '
      </td>
      <td>
        <strong>Inversión Ejercida</strong><br>
        $' . number_format($summary['inv_ejercida'], 2) . '
      </td>
          <td>
      <strong>Avance Físico</strong><br>
      ' . number_format($summary['avance_fisico'], 2) . '%
    </td>
    <td>
      <strong>Avance Financiero</strong><br>
      ' . number_format($summary['avance_financiero'], 2) . '%
    </td>
    </tr>
  </table>
  
  <!-- Tabla de Desglose -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th rowspan="2">Obra / Acción</th>
          <th rowspan="2">Tipo Obra</th>
          <th rowspan="2">Mpio y Loc</th>
          <th rowspan="2">Estatus</th>
          <th colspan="2">Fecha</th>
          <th colspan="6">Inv. Programada</th>
          <th colspan="6">Inv. Ejercida</th>
          <th rowspan="2">Modalidad Inv</th>
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
      <tbody>';

if ($resDetail && mysqli_num_rows($resDetail) > 0) {
  while ($row = mysqli_fetch_assoc($resDetail)) {
    $html .= "<tr>";
    $html .= "<td>" . htmlspecialchars($row['obra']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['tipo_obra'] ?? '') . "</td>";
    $html .= "<td>" . htmlspecialchars($row['mpio_loc'] ?? '') . "</td>";
    $html .= "<td>" . htmlspecialchars($row['estatus']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['fecha_inicio']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['fecha_termino']) . "</td>";

    // Inversión Programada
    $html .= "<td>$" . number_format($row['inversion_programada_federal'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_programada_estatal'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_programada_municipal'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_programada_credito'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_programada_beneficiarios'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_programada_otros'], 2) . "</td>";

    // Inversión Ejercida
    $html .= "<td>$" . number_format($row['inversion_ejercida_federal'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_ejercida_estatal'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_ejercida_municipal'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_ejercida_credito'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_ejercida_beneficiarios'], 2) . "</td>";
    $html .= "<td>$" . number_format($row['inversion_ejercida_otros'], 2) . "</td>";

    // Modalidad
    $html .= "<td>" . htmlspecialchars($row['modalidad_inv']) . "</td>";

    // Metas
    $html .= "<td>" . htmlspecialchars($row['meta_servicio_cantidad']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['servicio_unidad']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['meta_beneficiarios_cantidad']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['beneficiarios_unidad']) . "</td>";

    // Avances
    $html .= "<td>" . htmlspecialchars($row['avance_fisico_anual']) . "%</td>";
    $html .= "<td>" . htmlspecialchars($row['avance_financiero_anual']) . "%</td>";

    // Observaciones
    $html .= "<td>" . htmlspecialchars($row['observaciones']) . "</td>";
    $html .= "</tr>";
  }
} else {
  $html .= "<tr><td colspan='26'>No se encontraron obras para los filtros aplicados.</td></tr>";
}

$html .= '
      </tbody>
    </table>
  </div>
  </div> <!-- fin outer-container -->

    <!-- Footer con texto -->
    <div class="footer">
      <hr>
      <p>Derechos Reservados Secretaría de Planeación y Desarrollo Regional (SEPLADER) del Gobierno del Estado de Guerrero - Sistema Central para el Control y Registro de Obras y Acciones del Gobierno del Estado (SICECO)</p>
      <p>Palacio de Gobierno, Primer Piso, Edificio Costa Chica, Boulevard René Juárez Cisneros No. 62 Col. Ciudad de los Servicios, C.P. 39074.</p>
      <p>Chilpancingo de los Bravo, Guerrero. Tels. (01747) 471991 Ext. 6992&nbsp;&nbsp;&nbsp;www.guerrero.gob.mx</p>
    </div>
</body>
</html>';

// Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
// Orientation: Letter landscape
$dompdf->setPaper('letter', 'landscape');
$dompdf->render();
$dompdf->stream("Informe_Desglose.pdf", ['Attachment' => false]);
?>