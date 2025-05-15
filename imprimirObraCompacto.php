<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
include 'includes/conexion.php';

// Validar que se reciba el identificador de la obra
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No se proporcionó el identificador de la obra.");
}
$idObra = mysqli_real_escape_string($conn, $_GET['id']);

// Consulta para obtener los datos de la obra y las relaciones
$sqlObra = "SELECT o.*, 
                 d.nombre AS dependencia, 
                 t.nombre AS tipo_obra, 
                 l.nombre AS localidad,
                 m.nombre AS municipio,
                 e.nombre AS eje, 
                 obj.nombre AS objetivo, 
                 es.nombre AS estrategia,
                 la.nombre AS linea_accion, 
                 ind.nombre AS indicador,
                 mi.nombre AS modalidadInv,
                 sec.nombre AS sector,
                 pi.nombre AS programaInv,
                 p.proyecto_nombre AS proyecto,
                 pa.partida_nombre AS partida,
                 ub.nombre AS beneficiarios,
                 us.nombre AS servicio
          FROM obra o
          LEFT JOIN dependencia d ON o.id_dependencia = d.id_dependencia
          LEFT JOIN tipo_obra t ON o.id_tipoObra = t.id_tipoObra
          LEFT JOIN localidad l ON o.id_localidad = l.id_localidad
          LEFT JOIN municipio m ON l.id_municipio = m.id_municipio
          LEFT JOIN indicador ind ON o.id_indicador = ind.id_indicador
          LEFT JOIN linea_accion la ON o.id_linea_accion = la.id_linea_accion
          LEFT JOIN estrategia es ON la.id_estrategia = es.id_estrategia
          LEFT JOIN objetivo obj ON es.id_objetivo = obj.id_objetivo
          LEFT JOIN eje e ON obj.id_eje = e.id_eje
          LEFT JOIN modalidad_inversion mi ON o.id_modalidadInv = mi.id_modalidadInv
          LEFT JOIN sector sec ON o.id_sector = sec.id_sector
          LEFT JOIN programa_inversion pi ON o.id_programaInv = pi.id_programaInv
          LEFT JOIN proyecto p ON o.id_proyecto = p.id_proyecto
          LEFT JOIN partida pa ON o.id_partida = pa.id_partida
          LEFT JOIN unidad_beneficiarios ub ON o.id_beneficiarios = ub.id_beneficiarios
          LEFT JOIN unidad_servicio us ON o.id_servicio = us.id_servicio
          WHERE o.id_obra = '$idObra'";

$resultObra = mysqli_query($conn, $sqlObra);
if (!$resultObra || mysqli_num_rows($resultObra) == 0) {
    die("No se encontró la obra con el identificador proporcionado.");
}
$obra = mysqli_fetch_assoc($resultObra);

// Consulta para obtener los datos de inversión asociados a la obra
$sqlInversion = "SELECT * FROM inversion WHERE id_obra = '$idObra'";
$resultInversion = mysqli_query($conn, $sqlInversion);
$inversion = mysqli_fetch_assoc($resultInversion); // Puede ser null si no hay datos

// Si no hay datos de inversión, definimos campos en cero para evitar errores:
$investmentFields = [
    'inversion_programada_federal',
    'inversion_programada_estatal',
    'inversion_programada_municipal',
    'inversion_programada_credito',
    'inversion_programada_beneficiarios',
    'inversion_programada_otros',
    'inversion_autorizada_federal',
    'inversion_autorizada_estatal',
    'inversion_autorizada_municipal',
    'inversion_autorizada_credito',
    'inversion_autorizada_beneficiarios',
    'inversion_autorizada_otros',
    'inversion_modificada_federal',
    'inversion_modificada_estatal',
    'inversion_modificada_municipal',
    'inversion_modificada_credito',
    'inversion_modificada_beneficiarios',
    'inversion_modificada_otros',
    'inversion_liberada_federal',
    'inversion_liberada_estatal',
    'inversion_liberada_municipal',
    'inversion_liberada_credito',
    'inversion_liberada_beneficiarios',
    'inversion_liberada_otros',
    'inversion_ejercida_federal',
    'inversion_ejercida_estatal',
    'inversion_ejercida_municipal',
    'inversion_ejercida_credito',
    'inversion_ejercida_beneficiarios',
    'inversion_ejercida_otros'
];
if (!$inversion) {
    foreach ($investmentFields as $field) {
        $inversion[$field] = 0.00;
    }
}

// Convertir el logo del Gobierno a base64  
$logoPath = $_SERVER['DOCUMENT_ROOT'] . '/siseco/assets/logoGobierno.jpg';
if (file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $dataLogo = file_get_contents($logoPath);
    $headerImage = 'data:image/' . $type . ';base64,' . base64_encode($dataLogo);
} else {
    $headerImage = "";
}

// Función helper para construir filas de inversión
$displayInvestmentRow = function ($label, $prefix, $d, &$html) {
    $subfields = ['federal', 'estatal', 'municipal', 'credito', 'beneficiarios', 'otros'];
    $total = 0;
    $cells = [];
    foreach ($subfields as $sf) {
        $field = $prefix . $sf;
        $value = isset($d[$field]) ? $d[$field] : 0;
        $total += $value;
        $cells[] = '$' . number_format($value, 2);
    }
    $html .= "<tr><th>$label</th>";
    foreach ($cells as $c) {
        $html .= "<td>$c</td>";
    }
    $html .= "<td>$" . number_format($total, 2) . "</td></tr>";
};

// Construir el HTML con secciones
$html = '<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Detalle compacto Obra ' . htmlspecialchars($obra['id_obra']) . '</title>
<style>
  @page { margin: 20px 10px 30px 10px; }
  body { 
    font-family: Arial, sans-serif; 
    font-size: 7pt; 
    margin: 0; 
    padding: 0; 
    background: #fff; 
  }
  .header { text-align: center; margin-bottom: 2px; }
  .header img { max-height: 60px; }
  .section { 
    margin-bottom: 4px; 
    padding: 2px; 
    border: 1px solid #aaa; 
    border-radius: 3px; 
    page-break-inside: avoid; 
  }
  .section h2 { 
    background: #333; 
    color: #fff; 
    padding: 2px; 
    font-size: 9pt; 
    margin: 0 0 2px; 
    text-align: center; 
    border-radius: 3px;
  }
  .table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 2px; 
  }
  .table th, .table td { 
    border: 1px solid #666; 
    padding: 1px; 
  }
  .table th { background: #eee; width: 30%; }
  .footer { 
    font-size: 7pt; 
    text-align: center; 
    border-top: 1px solid #666; 
    padding-top: 2px; 
    position: fixed; 
    bottom: 0; 
    left: 0; 
    right: 0; 
  }
</style>

  <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
        $pdf->page_text(500, 570, "Página {PAGE_NUM} / {PAGE_COUNT}", $font, 7, array(0,0,0));
    }
  </script>
</head>
<body>
  <!-- Encabezado -->
  <div class="header">';
if (!empty($headerImage)) {
    $html .= '<img src="' . $headerImage . '" alt="Logo del Gobierno">';
}
$html .= '</div>
  <!-- Sección 1: Eje, Objetivo, Estrategia, Línea e Indicador -->
  <div class="section">
    <h2> Eje, Objetivo, Estrategia, Línea e Indicador</h2>
    <table class="table">
      <tr><th>Eje</th><td>' . htmlspecialchars($obra['eje']) . '</td></tr>
      <tr><th>Objetivo</th><td>' . htmlspecialchars($obra['objetivo']) . '</td></tr>
      <tr><th>Estrategia</th><td>' . htmlspecialchars($obra['estrategia']) . '</td></tr>
      <tr><th>Línea de Acción</th><td>' . htmlspecialchars($obra['linea_accion']) . '</td></tr>
      <tr><th>Indicador</th><td>' . htmlspecialchars($obra['indicador']) . '</td></tr>
    </table>
  </div>
  
  <!-- Sección 2: Datos Principales -->
  <div class="section">
    <h2>Datos Principales</h2>
    <table class="table">
      <tr>
        <th>Dependencia</th><td>' . htmlspecialchars($obra['dependencia']) . '</td>
        <th>Tipo Obra</th><td>' . htmlspecialchars($obra['tipo_obra']) . '</td>
      </tr>
      <tr>
        <th>Localidad</th><td>' . htmlspecialchars($obra['localidad']) . '</td>
        <th>Municipio</th><td>' . htmlspecialchars($obra['municipio']) . '</td>
      </tr>
      <tr>
        <th>Estatus</th><td>' . htmlspecialchars($obra['status_obra']) . '</td>
        <th>Año Fiscal</th><td>' . htmlspecialchars($obra['ano_ejercicio_fiscal']) . '</td>
      </tr>
      <tr>
        <th>Inicio</th><td>' . htmlspecialchars($obra['fecha_inicio']) . '</td>
        <th>Término</th><td>' . htmlspecialchars($obra['fecha_termino']) . '</td>
      </tr>
      <tr>
        <th>Etapa</th><td>' . htmlspecialchars($obra['etapa']) . '</td>
        <th>Coordenadas</th><td>Lat: ' . htmlspecialchars($obra['latitud']) . ', Lon: ' . htmlspecialchars($obra['longitud']) . '</td>
      </tr>
    </table>
  </div>

  <!-- Sección 3: OALTA -->
  <div class="section">
    <h2>OALTA</h2>
    <table class="table">
      <tr>
        <th>CCT</th><td>' . htmlspecialchars($obra['cct_oalta']) . '</td>
        <th>Obras</th><td>' . htmlspecialchars($obra['obras_oalta']) . '</td>
        <th>Aulas</th><td>' . htmlspecialchars($obra['aulas_oalta']) . '</td>
      </tr>
      <tr>
        <th>Laboratorios</th><td>' . htmlspecialchars($obra['laboratorios_oalta']) . '</td>
        <th>Talleres</th><td>' . htmlspecialchars($obra['talleres_oalta']) . '</td>
        <th>Anexos</th><td>' . htmlspecialchars($obra['anexos_oalta']) . '</td>
      </tr>
      <tr><th colspan="6">Observaciones OALTA</th></tr>
      <tr><td colspan="6">' . nl2br(htmlspecialchars($obra['descripcion_oalta'])) . '</td></tr>
    </table>
  </div>

  <!-- Sección 4: Inversiones -->
  <div class="section">
    <h2>Inversiones</h2>
    <table class="table">
      <tr>
        <th>Tipo</th>
        <th>Fed</th>
        <th>Est</th>
        <th>Mun</th>
        <th>Cre</th>
        <th>Ben</th>
        <th>Otros</th>
        <th>Total</th>
      </tr>';
// Usamos el helper para inversión, pasando $obra + $inversion en un solo array
// Para simplificar, fusionamos los datos de obra e inversion en $d. Si hay conflictos, la prioridad dará al de inversión.
$d = array_merge($obra, $inversion);
$displayInvestmentRow("Modificada", "inversion_modificada_", $d, $html);
$displayInvestmentRow("Liberada", "inversion_liberada_", $d, $html);
$displayInvestmentRow("Ejercida", "inversion_ejercida_", $d, $html);
$html .= '</table>
  </div>

  <!-- Sección 5: Modalidad, Ejecución, Pobreza -->
  <div class="section">
    <h2>Modalidad, Ejecución, Pobreza</h2>
    <table class="table">
      <tr>
        <th>Modalidad Inv</th>
        <td>' . htmlspecialchars($obra['modalidadInv']) . '</td>
        <th>Tipo Ejecución</th>
        <td>' . htmlspecialchars($obra['tipo_ejecucion']) . '</td>
      </tr>
      <tr>
        <th>Indicadores Pobreza</th>
        <td colspan="3">' . htmlspecialchars($obra['indicadores_pobreza']) . '</td>
      </tr>
    </table>
  </div>

  <!-- Sección 6: Características -->
  <div class="section">
    <h2>Características</h2>
    <table class="table">
      <tr><th>Causas</th><td>' . htmlspecialchars($obra['causas']) . '</td></tr>
      <tr><th>Tiempo Mayor Intensidad</th><td>' . htmlspecialchars($obra['tiempo_mayor_intensidad']) . '</td></tr>
      <tr><th>Razones Construcción</th><td>' . htmlspecialchars($obra['razones_construccion_obra']) . '</td></tr>
    </table>
  </div>

  <!-- Sección 7: Programa & Proyecto -->
  <div class="section">
    <h2>Programa & Proyecto</h2>
    <table class="table">
      <tr>
        <th>Sector</th><td>' . htmlspecialchars($obra['sector']) . '</td>
        <th>Proyecto</th><td>' . htmlspecialchars($obra['proyecto']) . '</td>
        <th>Programa Inv</th><td>' . htmlspecialchars($obra['programaInv']) . '</td>
      </tr>
    </table>
  </div>

  <!-- Sección 8: Meta Anual -->
  <div class="section">
    <h2>Meta Anual</h2>
    <table class="table">
      <tr>
        <th>Serv Can</th><td>' . htmlspecialchars($obra['meta_servicio_cantidad']) . '</td>
        <th>Serv Uni</th><td>' . htmlspecialchars($obra['servicio']) . '</td>
      </tr>
      <tr>
        <th>Ben Can</th><td>' . htmlspecialchars($obra['meta_beneficiarios_cantidad']) . '</td>
        <th>Ben Uni</th><td>' . htmlspecialchars($obra['beneficiarios']) . '</td>
      </tr>
      <tr>
        <th>Cant Total</th><td colspan="3">' . htmlspecialchars($obra['meta_cantidad_total']) . '</td>
            </tr>
      <tr>
        <th>Avance Físico</th><td>' . htmlspecialchars($obra['avance_fisico_anual']) . '%</td>
        <th>Avance Financiero</th><td >' . htmlspecialchars($obra['avance_financiero_anual']) . '%</td>
  </tr>
    </table>
  </div>


  <!-- Sección Final: Resumen Final -->
  <div class="section">
    <h2>Resumen Final</h2>
    <table class="table">
      <tr>
        <th>Unidad Responsable</th><td>' . htmlspecialchars($obra['dependencia']) . '</td>
      </tr>
      <tr>
        <th>Nombre del Proyecto</th><td>' . htmlspecialchars($obra['proyecto']) . '</td>
      </tr>
      <tr>
        <th>Descripción del Proyecto</th><td>' . htmlspecialchars($obra['nombre']) . '</td>
      </tr> 
      <tr>
        <th>Partida</th><td>' . htmlspecialchars($obra['partida']) . '</td>
      </tr>
    </table>
  </div>

  <!-- Footer -->
  <div class="footer">
    <p>Derechos Reservados Secretaría de Planeación y Desarrollo Regional (SEPLADER) del Gobierno del Estado de Guerrero - Sistema Central para el Control y Registro de Obras y Acciones del Gobierno del Estado (SICECO)</p>
    <p>Palacio de Gobierno, Primer Piso, Edificio Costa Chica, Blvd. René Juárez Cisneros No. 62, Col. Ciudad de los Servicios, C.P. 39074.</p>
    <p>Chilpancingo de los Bravo, Guerrero. Tels. (01747) 471991 Ext. 6992 | www.guerrero.gob.mx</p>
  </div>
</body>
</html>';

// Instanciar Dompdf y generar el PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream("Detalle_Obra.pdf", ['Attachment' => false]);

// Definir la función helper para construir las filas de inversión
function displayInvestmentRow($label, $prefix, $d, &$html)
{
    $subfields = ['federal', 'estatal', 'municipal', 'credito', 'beneficiarios', 'otros'];
    $total = 0;
    $cells = [];
    foreach ($subfields as $sf) {
        $field = $prefix . $sf;
        $value = isset($d[$field]) ? $d[$field] : 0;
        $total += $value;
        $cells[] = '$' . number_format($value, 2);
    }
    $html .= "<tr><th>$label</th>";
    foreach ($cells as $c) {
        $html .= "<td>$c</td>";
    }
    $html .= "<td>$" . number_format($total, 2) . "</td></tr>";
}
?>