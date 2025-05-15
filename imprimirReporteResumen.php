<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

include 'includes/conexion.php';

// Recibimos los filtros vía GET
// Recibimos los filtros vía GET (puedes usar también POST)
$dependencia = isset($_GET['dependencia']) ? trim($_GET['dependencia']) : '';
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
$finGov = "{$yearSafe}-10-15";

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

// Datos fijos para imágenes (asegúrate de usar URLs absolutas o base64)
//$headerImage = $_SERVER['DOCUMENT_ROOT'] . "/assets/logoGobierno.jpg";

$path = $_SERVER['DOCUMENT_ROOT'] . '/siseco/assets/logoGobierno.jpg';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$headerImage = 'data:image/' . $type . ';base64,' . base64_encode($data);

// Construir el HTML del PDF
$html = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Resumen - Ejercicio Fiscal</title>
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
      $pdf->page_text(400, 585, "Página {PAGE_NUM} / {PAGE_COUNT}", $font, 7, array(0,0,0));
    }
  </script>

</head>

<body>
  <div class="outer-container">

      <!-- Encabezado -->
      <div class="header">
        <img src="' . $headerImage . '" alt="Encabezado">
    </div>

    <div class="data-header">
      <h1>RESUMEN PARA EJERCICIO FISCAL POR DEPENDENCIA</h1>
      <h5>Año ' . htmlspecialchars($ejercicio_fiscal) . ' - ' . htmlspecialchars($dependencia) . '</h5>
    </div>
    
    <!-- Tarjetas Resumen -->
    <table class="cards">
      <tr>
        <td><strong>Obras</strong><br>' . number_format($summary['obras']) . '</td>
        <td><strong>Prog. Total</strong><br>$' . number_format($summary['prog_total'], 2) . '</td>
        <td><strong>Mod. Total</strong><br>$' . number_format($summary['mod_total'], 2) . '</td>
        <td><strong>Eje. Total</strong><br>$' . number_format($summary['eje_total'], 2) . '</td>
        <td><strong>Avance Físico</strong><br>' . number_format($summary['fis'], 2) . '% </td>
        <td><strong>Avance Financiero</strong><br>' . number_format($summary['finan'], 2) . '%</td>
      </tr>
    </table>
    
    <!-- Primera Tabla: Resumen de la Dependencia -->
    <div class="table-container">
      <table>
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
        <tbody>';

if ($summary) {
    $html .= "<tr>";
    $html .= "<td>" . htmlspecialchars($summary['dependencia']) . "</td>";
    $html .= "<td>" . number_format($summary['obras']) . "</td>";
    $html .= "<td>$" . number_format($summary['prog_total'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['prog_federal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['prog_estatal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['prog_mpal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['prog_cred'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['prog_bene'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['mod_total'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['mod_federal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['mod_estatal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['mod_mpal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['mod_cred'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['mod_bene'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['eje_total'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['eje_federal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['eje_estatal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['eje_mpal'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['eje_cred'], 2) . "</td>";
    $html .= "<td>$" . number_format($summary['eje_bene'], 2) . "</td>";
    $html .= "<td>" . number_format($summary['fis'], 2) . "</td>";
    $html .= "<td>" . number_format($summary['finan'], 2) . "</td>";
    $html .= "</tr>";
} else {
    $html .= "<tr><td colspan='22'>No se encontraron datos.</td></tr>";
}

$html .= '
        </tbody>
      </table>
    </div>
    
    <!-- Segunda Tabla: Detalle de Programas -->
    <div class="table-container">
      <table>
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
        <tbody>';

if ($resDetail && mysqli_num_rows($resDetail) > 0) {
    while ($row = mysqli_fetch_assoc($resDetail)) {
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($row['programa']) . "</td>";
        $html .= "<td>" . number_format($row['obras']) . "</td>";
        $html .= "<td>$" . number_format($row['prog_total'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['prog_federal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['prog_estatal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['prog_mpal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['prog_cred'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['prog_bene'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['mod_total'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['mod_federal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['mod_estatal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['mod_mpal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['mod_cred'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['mod_bene'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['eje_total'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['eje_federal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['eje_estatal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['eje_mpal'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['eje_cred'], 2) . "</td>";
        $html .= "<td>$" . number_format($row['eje_bene'], 2) . "</td>";
        $html .= "<td>" . number_format($row['fis'], 2) . "</td>";
        $html .= "<td>" . number_format($row['finan'], 2) . "</td>";
        $html .= "</tr>";
    }
} else {
    $html .= "<tr><td colspan='22'>No se encontraron datos para el detalle.</td></tr>";
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
$dompdf->stream("Reporte_Resumen.pdf", ['Attachment' => false]);
?>