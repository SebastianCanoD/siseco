<?php
include 'includes/conexion.php'; // Asegúrate de incluir la conexión a la BD

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /*********************** 1. INSERTAR EN TABLA OBRA ************************/

    // Recopilamos los datos del formulario para la obra.
    $nombre = $_POST['obraAccion'] ?? null;               // "Obra o Acción"
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

    // Datos de Apertura Programática
    $prioridad = $_POST['prioridad'] ?? null;
    $id_funcion = $_POST['finalidad'] ?? null;              // FK: Finalidad / Función / Subfunción
    $id_modalidad = $_POST['programaPresupuestal'] ?? null;   // FK: Programa Presupuestal (modalidad)
    $id_partida = $_POST['partida'] ?? null;
    $id_tipoGasto = $_POST['tipoGasto'] ?? null;
    $id_fuenteFinanciamiento = $_POST['fuenteFinanciamiento'] ?? null;

    // Datos de la sección "Modalidad"
    $id_modalidadInv = $_POST['modalidadInversion'] ?? null;  // FK: Modalidad de la Inversión (de modalidad_inversion)
    $tipo_ejecucion = $_POST['tipoEjecucion'] ?? null;
    $indicadores_pobreza = $_POST['indicadoresPobreza'] ?? null;
    $id_sector = $_POST['sector'] ?? null;
    $id_proyecto = $_POST['proyecto'] ?? null;
    $id_programaInv = $_POST['programasInversion'] ?? null;
    $causas = $_POST['causas'] ?? null;
    $tiempo_mayor_intensidad = $_POST['tiempoMayorIntensidad'] ?? null;
    $razones_construccion_obra = $_POST['razonesConstruccionObra'] ?? null;

    // Datos de la sección "Meta Anual"
    $meta_servicio_cantidad = $_POST['servicioCantidad'] ?? 0;
    $meta_cantidad_total = $_POST['cantidadTotal'] ?? 0;
    $meta_beneficiarios_cantidad = $_POST['beneficiariosCantidad'] ?? 0;
    $avance_fisico_anual = $_POST['avanceFisicoAnual'] ?? 0;
    $avance_financiero_anual = $_POST['avanceFinancieroAnual'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? null;

    // Datos de "Detalles de Ejecución" y Ubicación
    $id_dependencia = $_POST['dependencia'] ?? null;
    $id_localidad = $_POST['localidad'] ?? null;

    // Datos de "Datos de la Obra" (selects de tipo, línea e indicador)
    $id_tipoObra = $_POST['tipoObra'] ?? null;
    $id_linea_accion = $_POST['lineaAccion'] ?? null;
    $id_indicador = $_POST['indicador'] ?? null;

    // Datos de Meta Anual adicionales (unidad de servicio y beneficiarios)
    $id_servicio = $_POST['servicioUnidad'] ?? null;
    $id_beneficiarios = $_POST['beneficiariosUnidad'] ?? null;

    // Preparamos la consulta INSERT para la tabla obra (35 columnas)
    $sql = "INSERT INTO obra (
    nombre, fecha_inicio, fecha_termino, ano_ejercicio_fiscal, status_obra,
    latitud, longitud, etapa, tipo_destino,
    prioridad, tipo_ejecucion, indicadores_pobreza, causas,
    tiempo_mayor_intensidad, razones_construccion_obra,
    meta_servicio_cantidad, meta_cantidad_total, meta_beneficiarios_cantidad,
    avance_fisico_anual, avance_financiero_anual, observaciones,
    cct_oalta, obras_oalta, aulas_oalta, laboratorios_oalta,
    talleres_oalta, anexos_oalta, descripcion_oalta,
    id_servicio, id_beneficiarios, id_modalidadInv, id_sector,
    id_proyecto, id_programaInv, id_localidad, id_dependencia,
    id_tipoObra, id_funcion, id_modalidad, id_partida,
    id_tipoGasto, id_fuenteFinanciamiento, id_linea_accion, id_indicador
) VALUES (" . rtrim(str_repeat('?,', 44), ',') . ")";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        // La cadena de tipos para los primeros 19 campos (de obra) es:
        //  1. nombre: s  
        //  2. fecha_inicio: s  
        //  3. fecha_termino: s  
        //  4. ano_ejercicio_fiscal: i  
        //  5. status_obra: s  
        //  6. latitud: d  
        //  7. longitud: d  
        //  8. prioridad: s  
        //  9. tipo_ejecucion: s  
        // 10. indicadores_pobreza: s  
        // 11. causas: s  
        // 12. tiempo_mayor_intensidad: s  
        // 13. razones_construccion_obra: s  
        // 14. meta_servicio_cantidad: i  
        // 15. meta_cantidad_total: i  
        // 16. meta_beneficiarios_cantidad: i  
        // 17. avance_fisico_anual: d  
        // 18. avance_financiero_anual: d  
        // 19. observaciones: s  
        //
        // Luego, 16 campos de FK (todos enteros): de id_servicio a id_indicador.
        // Total = 19 + 16 = 35.
        $types =
            'sssi'            // 1-4
            . 's'               // 5
            . 'dd'              // 6-7
            . str_repeat('s', 8)// 8-15
            . str_repeat('i', 3)// 16-18
            . 'dd'              // 19-20
            . 'ss'              // 21-22
            . str_repeat('i', 5) // 23-27
            . 's'               // 28
            . str_repeat('i', 16);// 29-44

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            $nombre,
            $fecha_inicio,
            $fecha_termino,
            $ano_ejercicio_fiscal,
            $status_obra,
            $latitud,
            $longitud,
            $etapa,
            $tipo_destino,
            $prioridad,
            $tipo_ejecucion,
            $indicadores_pobreza,
            $causas,
            $tiempo_mayor_intensidad,
            $razones_construccion_obra,
            $meta_servicio_cantidad,
            $meta_cantidad_total,
            $meta_beneficiarios_cantidad,
            $avance_fisico_anual,
            $avance_financiero_anual,
            $observaciones,
            $cct_oalta,
            $obras_oalta,
            $aulas_oalta,
            $laboratorios_oalta,
            $talleres_oalta,
            $anexos_oalta,
            $descripcion_oalta,
            $id_servicio,
            $id_beneficiarios,
            $id_modalidadInv,
            $id_sector,
            $id_proyecto,
            $id_programaInv,
            $id_localidad,
            $id_dependencia,
            $id_tipoObra,
            $id_funcion,
            $id_modalidad,
            $id_partida,
            $id_tipoGasto,
            $id_fuenteFinanciamiento,
            $id_linea_accion,
            $id_indicador
        );

        if (mysqli_stmt_execute($stmt)) {
            // Se inserta la obra correctamente, obtenemos su id
            $id_obra = mysqli_insert_id($conn);
            echo "La obra se ha registrado correctamente. ID: " . $id_obra . "<br>";
        } else {
            echo "Error al registrar la obra: " . mysqli_stmt_error($stmt);
            exit;
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparando la consulta de obra: " . mysqli_error($conn);
        exit;
    }

    /*********************** 2. INSERTAR EN TABLA INVERSION ************************/
    // Recopilamos los datos de inversión del formulario. En caso de que algún campo
    // no esté presente, se asigna 0.00.
    // Inversión Programada (6 campos)
    $inv_prog_fed = $_POST['inversion_programada_federal'] ?? 0.00;
    $inv_prog_est = $_POST['inversion_programada_estatal'] ?? 0.00;
    $inv_prog_mun = $_POST['inversion_programada_municipal'] ?? 0.00;
    $inv_prog_cre = $_POST['inversion_programada_credito'] ?? 0.00;
    $inv_prog_ben = $_POST['inversion_programada_beneficiarios'] ?? 0.00;
    $inv_prog_otros = $_POST['inversion_programada_otros'] ?? 0.00;

    // Inversión Autorizada (6 campos)
    $inv_auth_fed = $_POST['inversion_autorizada_federal'] ?? 0.00;
    $inv_auth_est = $_POST['inversion_autorizada_estatal'] ?? 0.00;
    $inv_auth_mun = $_POST['inversion_autorizada_municipal'] ?? 0.00;
    $inv_auth_cre = $_POST['inversion_autorizada_credito'] ?? 0.00;
    $inv_auth_ben = $_POST['inversion_autorizada_beneficiarios'] ?? 0.00;
    $inv_auth_otros = $_POST['inversion_autorizada_otros'] ?? 0.00;

    // Inversión Modificada (6 campos)
    $inv_mod_fed = $_POST['inversion_modificada_federal'] ?? 0.00;
    $inv_mod_est = $_POST['inversion_modificada_estatal'] ?? 0.00;
    $inv_mod_mun = $_POST['inversion_modificada_municipal'] ?? 0.00;
    $inv_mod_cre = $_POST['inversion_modificada_credito'] ?? 0.00;
    $inv_mod_ben = $_POST['inversion_modificada_beneficiarios'] ?? 0.00;
    $inv_mod_otros = $_POST['inversion_modificada_otros'] ?? 0.00;

    // Inversión Liberada (6 campos)
    $inv_lib_fed = $_POST['inversion_liberada_federal'] ?? 0.00;
    $inv_lib_est = $_POST['inversion_liberada_estatal'] ?? 0.00;
    $inv_lib_mun = $_POST['inversion_liberada_municipal'] ?? 0.00;
    $inv_lib_cre = $_POST['inversion_liberada_credito'] ?? 0.00;
    $inv_lib_ben = $_POST['inversion_liberada_beneficiarios'] ?? 0.00;
    $inv_lib_otros = $_POST['inversion_liberada_otros'] ?? 0.00;

    // Inversión Ejercida (6 campos)
    $inv_eje_fed = $_POST['inversion_ejercida_federal'] ?? 0.00;
    $inv_eje_est = $_POST['inversion_ejercida_estatal'] ?? 0.00;
    $inv_eje_mun = $_POST['inversion_ejercida_municipal'] ?? 0.00;
    $inv_eje_cre = $_POST['inversion_ejercida_credito'] ?? 0.00;
    $inv_eje_ben = $_POST['inversion_ejercida_beneficiarios'] ?? 0.00;
    $inv_eje_otros = $_POST['inversion_ejercida_otros'] ?? 0.00;

    // Preparamos la consulta INSERT para la tabla inversion (31 columnas: id_obra + 30 campos decimales)
    $sql2 = "INSERT INTO inversion (
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
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    if ($stmt2 = mysqli_prepare($conn, $sql2)) {
        // La cadena de tipos: "i" para id_obra y 30 "d" para los demás valores (total 31)
        $types2 = "i" . str_repeat("d", 30);
        mysqli_stmt_bind_param(
            $stmt2,
            $types2,
            $id_obra,
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
            $inv_eje_otros
        );

        if (mysqli_stmt_execute($stmt2)) {
            // Limpia el buffer para enviar el script correctamente
            ob_clean();
            // Muestra alerta con el nombre de la obra y redirige
            echo "<script>
            alert('La obra “" . addslashes($nombre) . "” se ha guardado correctamente.');
            window.location.href = 'buscarObra.php';
          </script>";
            exit;
        } else {
            echo "Error al registrar la inversión: " . mysqli_stmt_error($stmt2);
        }
        mysqli_stmt_close($stmt2);
    } else {
        echo "Error preparando la consulta para la inversión: " . mysqli_error($conn);
    }

    mysqli_close($conn);

} else {
    echo "Acceso no autorizado.";
}
?>