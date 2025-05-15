<?php
session_start();
require 'includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: buscarObra.php');
  exit;
}

// 1) Recopilar datos del formulario
$id_obra = (int) $_POST['id_obra'];
$nombre = $_POST['obraAccion'] ?? null;
$fecha_inicio = $_POST['fechaInicio'] ?? null;
$fecha_termino = $_POST['fechaTermino'] ?? null;
$ano_ejercicio_fiscal = $_POST['ejercicioFiscal'] ?? null;
$status_obra = $_POST['estatusObra'] ?? null;
$latitud = $_POST['latitud'] ?? null;
$longitud = $_POST['longitud'] ?? null;
$etapa = $_POST['etapa'] ?? null;
$tipo_destino = $_POST['tipo_destino'] ?? 'EJERCICIO_FISCAL';

// OALTA
$cct_oalta = $_POST['cct'] ?? null;
$obras_oalta = $_POST['obras'] ?? 0;
$aulas_oalta = $_POST['aulas'] ?? 0;
$laboratorios_oalta = $_POST['laboratorios'] ?? 0;
$talleres_oalta = $_POST['talleres'] ?? 0;
$anexos_oalta = $_POST['anexos'] ?? 0;
$descripcion_oalta = $_POST['descripcion_oalta'] ?? null;

// Apertura Programática
$prioridad = $_POST['prioridad'] ?? null;
$id_funcion = $_POST['finalidad'] ?? null;
$id_modalidad = $_POST['programaPresupuestal'] ?? null;
$id_partida = $_POST['partida'] ?? null;
$id_tipoGasto = $_POST['tipoGasto'] ?? null;
$id_fuenteFinanciamiento = $_POST['fuenteFinanciamiento'] ?? null;

// Modalidad / Características
$id_modalidadInv = $_POST['modalidadInversion'] ?? null;
$tipo_ejecucion = $_POST['tipoEjecucion'] ?? null;
$indicadores_pobreza = $_POST['indicadoresPobreza'] ?? null;
$id_sector = $_POST['sector'] ?? null;
$id_proyecto = $_POST['proyecto'] ?? null;
$id_programaInv = $_POST['programasInversion'] ?? null;
$causas = $_POST['causas'] ?? null;
$tiempo_mayor_intensidad = $_POST['tiempoMayorIntensidad'] ?? null;
$razones_construccion_obra = $_POST['razonesConstruccionObra'] ?? null;

// Meta Anual
$meta_servicio_cantidad = $_POST['servicioCantidad'] ?? 0;
$meta_cantidad_total = $_POST['cantidadTotal'] ?? 0;
$meta_beneficiarios_cantidad = $_POST['beneficiariosCantidad'] ?? 0;
$avance_fisico_anual = $_POST['avanceFisicoAnual'] ?? 0.0;
$avance_financiero_anual = $_POST['avanceFinancieroAnual'] ?? 0.0;
$observaciones = $_POST['observaciones'] ?? null;

// Ubicación y detalles
$id_dependencia = $_POST['dependencia'] ?? null;
$id_localidad = $_POST['localidad'] ?? null;
$id_tipoObra = $_POST['tipoObra'] ?? null;
$id_linea_accion = $_POST['lineaAccion'] ?? null;
$id_indicador = $_POST['indicador'] ?? null;

// Unidades Meta Anual
$id_servicio = $_POST['servicioUnidad'] ?? null;
$id_beneficiarios = $_POST['beneficiariosUnidad'] ?? null;

// 2) Preparamos el UPDATE con 36 columnas
$sql = "UPDATE obra SET
    nombre                     = ?, 
    fecha_inicio               = ?, 
    fecha_termino              = ?, 
    ano_ejercicio_fiscal       = ?, 
    status_obra                = ?, 
    latitud                    = ?, 
    longitud                   = ?, 
    etapa                      = ?, 
    tipo_destino               = ?, 
    prioridad                  = ?, 
    tipo_ejecucion             = ?, 
    indicadores_pobreza        = ?, 
    causas                     = ?, 
    tiempo_mayor_intensidad    = ?, 
    razones_construccion_obra  = ?, 
    meta_servicio_cantidad     = ?, 
    meta_cantidad_total        = ?, 
    meta_beneficiarios_cantidad= ?, 
    avance_fisico_anual        = ?, 
    avance_financiero_anual    = ?, 
    observaciones              = ?, 
    cct_oalta                  = ?, 
    obras_oalta                = ?, 
    aulas_oalta                = ?, 
    laboratorios_oalta         = ?, 
    talleres_oalta             = ?, 
    anexos_oalta               = ?, 
    descripcion_oalta          = ?, 
    id_servicio                = ?, 
    id_beneficiarios           = ?, 
    id_modalidadInv            = ?, 
    id_sector                  = ?, 
    id_proyecto                = ?, 
    id_programaInv             = ?, 
    id_localidad               = ?, 
    id_dependencia             = ?, 
    id_tipoObra                = ?, 
    id_funcion                 = ?, 
    id_modalidad               = ?, 
    id_partida                 = ?, 
    id_tipoGasto               = ?, 
    id_fuenteFinanciamiento    = ?, 
    id_linea_accion            = ?, 
    id_indicador               = ?
  WHERE id_obra = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo "<script>
            alert('Error preparando la consulta: " . addslashes($conn->error) . "');
            history.back();
          </script>";
  exit;
}

// 3) Cadena de tipos: 36 tipos + 1 (para el id_obra final) = 37
//   (s=string, i=integer, d=double/float)
// 3) Cadena de tipos precisa de 36 caracteres:
$types =
    'sssss'    //  1-5: nombre, fecha_inicio, fecha_termino, ano_ejercicio_fiscal, status_obra
  . 'dd'       //  6-7: latitud, longitud
  . 'ss'       //  8-9: etapa, tipo_destino
  . 'sss'      // 10-12: prioridad, tipo_ejecucion, indicadores_pobreza
  . 'sss'      // 13-15: causas, tiempo_mayor_intensidad, razones_construccion_obra
  . 'iii'      // 16-18: meta_servicio_cantidad, meta_cantidad_total, meta_beneficiarios_cantidad
  . 'dd'       // 19-20: avance_fisico_anual, avance_financiero_anual
  . 's'        //     21: observaciones
  . 's'        //     22: cct_oalta   ← debe ser 's'
  . 'iiiii'    // 23-27: obras_oalta, aulas_oalta, laboratorios_oalta, talleres_oalta, anexos_oalta
  . 's'        //     28: descripcion_oalta ← también 's'
  . str_repeat('i', 16) // 29-44: id_servicio … id_indicador (16 enteros)
  . 'i';  

// 4) Bind de parámetros
$stmt->bind_param(
  $types,
  $nombre,                    // s
  $fecha_inicio,              // s
  $fecha_termino,             // s
  $ano_ejercicio_fiscal,      // s
  $status_obra,               // s
  $latitud,                   // d
  $longitud,                  // d
  $etapa,                     // s
  $tipo_destino,              // s
  $prioridad,                 // s
  $tipo_ejecucion,            // s
  $indicadores_pobreza,       // s
  $causas,                    // s
  $tiempo_mayor_intensidad,   // s
  $razones_construccion_obra, // s
  $meta_servicio_cantidad,    // i
  $meta_cantidad_total,       // i
  $meta_beneficiarios_cantidad,// i
  $avance_fisico_anual,       // d
  $avance_financiero_anual,   // d
  $observaciones,             // s  ← Aquí vuelve a ser string
  $cct_oalta,                 // s
  $obras_oalta,               // i
  $aulas_oalta,               // i
  $laboratorios_oalta,        // i
  $talleres_oalta,            // i
  $anexos_oalta,              // i
  $descripcion_oalta,         // s
  $id_servicio,               // i
  $id_beneficiarios,          // i
  $id_modalidadInv,           // i
  $id_sector,                 // i
  $id_proyecto,               // i
  $id_programaInv,            // i
  $id_localidad,              // i
  $id_dependencia,            // i
  $id_tipoObra,               // i
  $id_funcion,                // i
  $id_modalidad,              // i
  $id_partida,                // i
  $id_tipoGasto,              // i
  $id_fuenteFinanciamiento,   // i
  $id_linea_accion,           // i
  $id_indicador,              // i
  $id_obra                    // i (WHERE)
);




if ($stmt->execute()) {
  // Actualizamos la tabla obra
  echo "<script>
            alert('Obra actualizada correctamente.');
          </script>";
} else {
  echo "<script>
            alert('Error al actualizar obra: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
  exit;
}
$stmt->close();
// … tras cerrar $stmt de obra …

// 4) Recopilar datos de inversión (30 campos)
$inv_prog_fed = $_POST['inversion_programada_federal'] ?? 0.00;
$inv_prog_est = $_POST['inversion_programada_estatal'] ?? 0.00;
$inv_prog_mun = $_POST['inversion_programada_municipal'] ?? 0.00;
$inv_prog_cre = $_POST['inversion_programada_credito'] ?? 0.00;
$inv_prog_ben = $_POST['inversion_programada_beneficiarios'] ?? 0.00;
$inv_prog_otros = $_POST['inversion_programada_otros'] ?? 0.00;

$inv_auth_fed = $_POST['inversion_autorizada_federal'] ?? 0.00;
$inv_auth_est = $_POST['inversion_autorizada_estatal'] ?? 0.00;
$inv_auth_mun = $_POST['inversion_autorizada_municipal'] ?? 0.00;
$inv_auth_cre = $_POST['inversion_autorizada_credito'] ?? 0.00;
$inv_auth_ben = $_POST['inversion_autorizada_beneficiarios'] ?? 0.00;
$inv_auth_otros = $_POST['inversion_autorizada_otros'] ?? 0.00;

$inv_mod_fed = $_POST['inversion_modificada_federal'] ?? 0.00;
$inv_mod_est = $_POST['inversion_modificada_estatal'] ?? 0.00;
$inv_mod_mun = $_POST['inversion_modificada_municipal'] ?? 0.00;
$inv_mod_cre = $_POST['inversion_modificada_credito'] ?? 0.00;
$inv_mod_ben = $_POST['inversion_modificada_beneficiarios'] ?? 0.00;
$inv_mod_otros = $_POST['inversion_modificada_otros'] ?? 0.00;

$inv_lib_fed = $_POST['inversion_liberada_federal'] ?? 0.00;
$inv_lib_est = $_POST['inversion_liberada_estatal'] ?? 0.00;
$inv_lib_mun = $_POST['inversion_liberada_municipal'] ?? 0.00;
$inv_lib_cre = $_POST['inversion_liberada_credito'] ?? 0.00;
$inv_lib_ben = $_POST['inversion_liberada_beneficiarios'] ?? 0.00;
$inv_lib_otros = $_POST['inversion_liberada_otros'] ?? 0.00;

$inv_eje_fed = $_POST['inversion_ejercida_federal'] ?? 0.00;
$inv_eje_est = $_POST['inversion_ejercida_estatal'] ?? 0.00;
$inv_eje_mun = $_POST['inversion_ejercida_municipal'] ?? 0.00;
$inv_eje_cre = $_POST['inversion_ejercida_credito'] ?? 0.00;
$inv_eje_ben = $_POST['inversion_ejercida_beneficiarios'] ?? 0.00;
$inv_eje_otros = $_POST['inversion_ejercida_otros'] ?? 0.00;

// 5) Comprobar existencia
$chk = $conn->prepare("SELECT id_inversion FROM inversion WHERE id_obra = ?");
$chk->bind_param('i', $id_obra);
$chk->execute();
$chk->store_result();

if ($chk->num_rows > 0) {
  // Ya existe: hacemos UPDATE
  $sql2 = "
      UPDATE inversion SET
        inversion_programada_federal       = ?,
        inversion_programada_estatal       = ?,
        inversion_programada_municipal     = ?,
        inversion_programada_credito       = ?,
        inversion_programada_beneficiarios = ?,
        inversion_programada_otros         = ?,

        inversion_autorizada_federal       = ?,
        inversion_autorizada_estatal       = ?,
        inversion_autorizada_municipal     = ?,
        inversion_autorizada_credito       = ?,
        inversion_autorizada_beneficiarios = ?,
        inversion_autorizada_otros         = ?,

        inversion_modificada_federal       = ?,
        inversion_modificada_estatal       = ?,
        inversion_modificada_municipal     = ?,
        inversion_modificada_credito       = ?,
        inversion_modificada_beneficiarios = ?,
        inversion_modificada_otros         = ?,

        inversion_liberada_federal         = ?,
        inversion_liberada_estatal         = ?,
        inversion_liberada_municipal       = ?,
        inversion_liberada_credito         = ?,
        inversion_liberada_beneficiarios   = ?,
        inversion_liberada_otros           = ?,

        inversion_ejercida_federal         = ?,
        inversion_ejercida_estatal         = ?,
        inversion_ejercida_municipal       = ?,
        inversion_ejercida_credito         = ?,
        inversion_ejercida_beneficiarios   = ?,
        inversion_ejercida_otros           = ?
      WHERE id_obra = ?";
} else {
  // No existe: hacemos INSERT
  $sql2 = "
      INSERT INTO inversion (
        id_obra,
        inversion_programada_federal,
        inversion_programada_estatal,
        inversion_programada_municipal,
        inversion_programada_credito,
        inversion_programada_beneficiarios,
        inversion_programada_otros,
        inversion_autorizada_federal,
        inversion_autorizada_estatal,
        inversion_autorizada_municipal,
        inversion_autorizada_credito,
        inversion_autorizada_beneficiarios,
        inversion_autorizada_otros,
        inversion_modificada_federal,
        inversion_modificada_estatal,
        inversion_modificada_municipal,
        inversion_modificada_credito,
        inversion_modificada_beneficiarios,
        inversion_modificada_otros,
        inversion_liberada_federal,
        inversion_liberada_estatal,
        inversion_liberada_municipal,
        inversion_liberada_credito,
        inversion_liberada_beneficiarios,
        inversion_liberada_otros,
        inversion_ejercida_federal,
        inversion_ejercida_estatal,
        inversion_ejercida_municipal,
        inversion_ejercida_credito,
        inversion_ejercida_beneficiarios,
        inversion_ejercida_otros
      ) VALUES (
        ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
      )";
}
$chk->close();

// 6) Preparar y bind de parámetros
$stmt2 = $conn->prepare($sql2);
if (!$stmt2) {
  echo "<script>alert('Error preparando inversión: " . $conn->error . "');history.back();</script>";
  exit;
}

// 30 decimales y luego 1 entero (id_obra) o 31 parámetros en INSERT
$types2 = str_repeat('d', 30) . 'i';
$params = [
  $inv_prog_fed,
  $inv_prog_est,
  $inv_prog_mun,
  $inv_prog_cre,
  $inv_prog_ben,
  $inv_prog_otros,
  $inv_auth_fed,
  $inv_auth_est,
  $inv_auth_mun,
  $inv_auth_cre,
  $inv_auth_ben,
  $inv_auth_otros,
  $inv_mod_fed,
  $inv_mod_est,
  $inv_mod_mun,
  $inv_mod_cre,
  $inv_mod_ben,
  $inv_mod_otros,
  $inv_lib_fed,
  $inv_lib_est,
  $inv_lib_mun,
  $inv_lib_cre,
  $inv_lib_ben,
  $inv_lib_otros,
  $inv_eje_fed,
  $inv_eje_est,
  $inv_eje_mun,
  $inv_eje_cre,
  $inv_eje_ben,
  $inv_eje_otros,
  $id_obra
];
// Si es INSERT, movemos $id_obra al inicio
if (stripos($sql2, 'INSERT') === 0) {
  $types2 = 'i' . str_repeat('d', 30);
  array_unshift($params, $id_obra);
}
$stmt2->bind_param($types2, ...$params);

if ($stmt2->execute()) {
  echo "<script>
            alert('Obra e inversión guardadas correctamente.');
            window.location.href='buscarObra.php';
          </script>";
} else {
  echo "<script>alert('Error inversión: " . addslashes($stmt2->error) . "');history.back();</script>";
}
$stmt2->close();
$conn->close();
