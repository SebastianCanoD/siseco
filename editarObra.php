<?php
include 'includes/header.php';
include 'includes/conexion.php';
include 'capturarObraQueries.php';


// 1. Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID de obra no válido.'); window.location.href='buscarObra.php';</script>";
    exit;
}
$id_obra = (int) $_GET['id'];

// 2. Obtener datos de obra
$stmt = $conn->prepare("SELECT * FROM obra WHERE id_obra = ?");
$stmt->bind_param('i', $id_obra);
$stmt->execute();
$obra = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$obra) {
    echo "<script>alert('Obra no encontrada.'); window.location.href='buscarObra.php';</script>";
    exit;
}

$sql = "SELECT * FROM inversion WHERE id_obra = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_obra);
$stmt->execute();
$inversion = $stmt->get_result()->fetch_assoc();
$stmt->close();


// 3. Calcular jerarquía desde id_linea_accion
$id_linea_accion = (int) $obra['id_linea_accion'];
$id_estrategia = null;
$id_objetivo = null;
$id_eje = null;
if ($id_linea_accion) {
    $s = $conn->prepare("SELECT id_estrategia FROM linea_accion WHERE id_linea_accion=?");
    $s->bind_param('i', $id_linea_accion);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $id_estrategia = $r['id_estrategia'] ?? null;
    $s->close();
}
if ($id_estrategia) {
    $s = $conn->prepare("SELECT id_objetivo FROM estrategia WHERE id_estrategia=?");
    $s->bind_param('i', $id_estrategia);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $id_objetivo = $r['id_objetivo'] ?? null;
    $s->close();
}
if ($id_objetivo) {
    $s = $conn->prepare("SELECT id_eje FROM objetivo WHERE id_objetivo=?");
    $s->bind_param('i', $id_objetivo);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $id_eje = $r['id_eje'] ?? null;
    $s->close();
}

// 4. Función dependientes
function obtenerDependientes($tabla, $idCol, $nomCol, $fkCol, $fkVal)
{
    global $conn;
    $out = [];
    $qry = "SELECT $idCol AS id, $nomCol AS nombre FROM $tabla WHERE $fkCol=?";
    $st = $conn->prepare($qry);
    $st->bind_param('i', $fkVal);
    $st->execute();
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    $st->close();
    return $out;
}

// Función para obtener el ID de municipio a partir de la localidad
function obtenerMunicipioDesdeLocalidad($id_localidad)
{
    global $conn;
    $qry = "SELECT id_municipio FROM localidad WHERE id_localidad = ?";
    $st = $conn->prepare($qry);
    $st->bind_param('i', $id_localidad);
    $st->execute();
    $result = $st->get_result()->fetch_assoc();
    $st->close();
    return $result['id_municipio'] ?? null;
}


// 5. Cargar opciones
$ejes = obtenerOpciones('eje', 'id_eje', 'nombre');
$objetivos = $id_eje ? obtenerDependientes('objetivo', 'id_objetivo', 'nombre', 'id_eje', $id_eje) : [];
$estrategias = $id_objetivo ? obtenerDependientes('estrategia', 'id_estrategia', 'nombre', 'id_objetivo', $id_objetivo) : [];
$lineas = $id_estrategia ? obtenerDependientes('linea_accion', 'id_linea_accion', 'nombre', 'id_estrategia', $id_estrategia) : [];
$indicadores = $id_eje ? obtenerDependientes('indicador', 'id_indicador', 'nombre', 'id_eje', $id_eje) : [];

// Suponiendo que en la obra existe el campo 'id_localidad'
$id_localidad = !empty($obra['id_localidad']) ? (int) $obra['id_localidad'] : 0;
$id_municipio = ($id_localidad) ? obtenerMunicipioDesdeLocalidad($id_localidad) : null;


$tiposObra = obtenerOpciones('tipo_obra', 'id_tipoObra', 'nombre');
$dependencias = obtenerOpciones('dependencia', 'id_dependencia', 'nombre');
$municipios = obtenerOpciones('municipio', 'id_municipio', 'nombre');
$finalidades = obtenerOpciones('finalidad', 'id_funcion', 'finalidad_nombre');
$modalidades = obtenerOpciones('modalidad', 'id_modalidad', 'modalidad_nombre');
$partidas = obtenerOpciones('partida', 'id_partida', 'partida_nombre');
$tipoGastos = obtenerOpciones('tipo_gasto', 'id_tipoGasto', 'tipoGasto_nombre');
$fuentes = obtenerOpciones('fuente_financiamiento', 'id_fuenteFinanciamiento', 'nombre');
$sectores = obtenerOpciones('sector', 'id_sector', 'nombre');
$proyectos = obtenerOpciones('proyecto', 'id_proyecto', 'proyecto_nombre');
$programasInv = obtenerOpciones('programa_inversion', 'id_programaInv', 'nombre');
$modalidadesInv = obtenerOpciones('modalidad_inversion', 'id_modalidadInv', 'nombre');
$unidadServ = obtenerOpciones('unidad_servicio', 'id_servicio', 'nombre');
$unidadBen = obtenerOpciones('unidad_beneficiarios', 'id_beneficiarios', 'nombre');
$localidades = ($id_municipio) ? obtenerDependientes('localidad', 'id_localidad', 'nombre', 'id_municipio', $id_municipio) : [];
$fuenteFinanciamientos = obtenerOpciones('fuente_financiamiento', 'id_fuenteFinanciamiento', 'nombre');
$unidadServicio = obtenerOpciones('unidad_servicio', 'id_servicio', 'nombre');
$unidadBeneficiarios = obtenerOpciones('unidad_beneficiarios', 'id_beneficiarios', 'nombre');
?>

<div class="container mt-4">
    <h1 class="mb-4">Editar Obra</h1>

    <form action="editarObraProceso.php" method="POST">
        <!-- Enviamos el id de la obra -->
        <input type="hidden" name="id_obra" value="<?= $obra['id_obra'] ?>">
        <!-- Sección: Datos de la Obra -->
        <div class="card mb-4">
            <div class="card-header">Datos de la Obra</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="eje" class="form-label">Eje</label>
                        <select id="eje" name="eje" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($ejes as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $id_eje ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="objetivo" class="form-label">Objetivo</label>
                        <select id="objetivo" name="objetivo" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($objetivos as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $id_objetivo ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estrategias" class="form-label">Estrategia</label>
                        <select id="estrategias" name="estrategias" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($estrategias as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $id_estrategia ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lineaAccion" class="form-label">Línea de Acción</label>
                        <select id="lineaAccion" name="lineaAccion" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($lineas as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $id_linea_accion ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label for="indicador" class="form-label">Indicador</label>
                        <select id="indicador" name="indicador" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($indicadores as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $obra['id_indicador'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipoObra" class="form-label">Tipo de Obra</label>
                        <select id="tipoObra" name="tipoObra" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($tiposObra as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $t['id'] == $obra['id_tipoObra'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <!-- Sección: Detalles de Ejecución -->
        <div class="card mb-4">
            <div class="card-header">Detalles de Ejecución</div>
            <div class="card-body">
                <!-- Campo: Obra o Acción -->
                <div class="mb-3">
                    <label for="obraAccion" class="form-label">Obra o Acción</label>
                    <input type="text" class="form-control" id="obraAccion" name="obraAccion"
                        placeholder="Ingrese la obra o acción" value="<?= htmlspecialchars($obra['nombre']) ?>">
                </div>
                <div class="mb-3">
                    <!-- Campo: Dependencia -->
                    <label for="dependencia" class="form-label">Dependencia</label>
                    <select class="form-select" id="dependencia" name="dependencia">
                        <option value="">---------</option>
                        <?php foreach ($dependencias as $dep): ?>
                            <option value="<?= $dep['id']; ?>" <?= ($dep['id'] == $obra['id_dependencia']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dep['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <!-- Campo: Latitud -->
                    <div class="col-md-4 mb-3">
                        <label for="latitud" class="form-label">Latitud</label>
                        <input type="text" class="form-control" id="latitud" name="latitud"
                            placeholder="Ingrese la latitud" value="<?= htmlspecialchars($obra['latitud']) ?>">
                    </div>
                    <!-- Campo: Longitud -->
                    <div class="col-md-4 mb-3">
                        <label for="longitud" class="form-label">Longitud</label>
                        <input type="text" class="form-control" id="longitud" name="longitud"
                            placeholder="Ingrese la longitud" value="<?= htmlspecialchars($obra['longitud']) ?>">
                    </div>
                    <!-- Campo: Estatus de la Obra -->
                    <div class="col-md-4 mb-3">
                        <label for="estatusObra" class="form-label">Estatus de la Obra</label>
                        <select class="form-select" id="estatusObra" name="estatusObra">
                            <option value="">Seleccione...</option>
                            <option value="En_progreso" <?= ($obra['status_obra'] == 'En_progreso') ? 'selected' : '' ?>>En
                                Progreso</option>
                            <option value="Completa" <?= ($obra['status_obra'] == 'Completa') ? 'selected' : '' ?>>Completa
                            </option>
                            <option value="Planificada" <?= ($obra['status_obra'] == 'Planificada') ? 'selected' : '' ?>>
                                Planificada</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Año Ejercicio Fiscal -->
                    <div class="col-md-4 mb-3">
                        <label for="ejercicioFiscal" class="form-label">Año Ejercicio Fiscal</label>
                        <input type="number" class="form-control" id="ejercicioFiscal" name="ejercicioFiscal"
                            placeholder="Ingrese el año" value="<?= htmlspecialchars($obra['ano_ejercicio_fiscal']) ?>">
                    </div>
                    <!-- Campo: Fecha Inicio -->
                    <div class="col-md-4 mb-3">
                        <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fechaInicio" name="fechaInicio"
                            value="<?= htmlspecialchars($obra['fecha_inicio']) ?>">
                    </div>
                    <!-- Campo: Fecha Termino -->
                    <div class="col-md-4 mb-3">
                        <label for="fechaTermino" class="form-label">Fecha Termino</label>
                        <input type="date" class="form-control" id="fechaTermino" name="fechaTermino"
                            value="<?= htmlspecialchars($obra['fecha_termino']) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Información Geográfica -->
        <div class="card mb-4">
            <div class="card-header">Información Geográfica</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Municipio -->
                    <div class="col-md-6 mb-3">
                        <label for="municipio" class="form-label">Municipio</label>
                        <select class="form-select" id="municipio" name="municipio">
                            <option value="">Seleccione...</option>
                            <?php foreach ($municipios as $mun): ?>
                                <option value="<?= $mun['id']; ?>" <?= ($mun['id'] == $id_municipio) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mun['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Localidad -->
                    <div class="col-md-6 mb-3">
                        <label for="localidad" class="form-label">Localidad</label>
                        <select class="form-select" id="localidad" name="localidad">
                            <option value="">Seleccione...</option>
                            <?php foreach ($localidades as $loc): ?>
                                <option value="<?= $loc['id']; ?>" <?= ($loc['id'] == $obra['id_localidad']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <!-- Sección: Etapa y Tipo de Destino -->
        <div class="card mb-4">
            <div class="card-header">
                Etapa y Destino
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Etapa -->
                    <div class="col-md-6 mb-3">
                        <label for="etapa" class="form-label">Etapa</label>
                        <input type="text" class="form-control" id="etapa" name="etapa"
                             value="<?= htmlspecialchars($obra['etapa'] ?? '-') ?>" placeholder="Ingrese la etapa de la obra">
                    </div>
<!-- Campo: Tipo de Destino -->
<div class="col-md-6 mb-3">
    <label for="tipo_destino" class="form-label">Destino</label>
    <select class="form-select" id="tipo_destino" name="tipo_destino">
        <option value="EJERCICIO_FISCAL" <?= ($obra['tipo_destino'] ?? '') === 'EJERCICIO_FISCAL' ? 'selected' : '' ?>>
            Ejercicio Fiscal
        </option>
        <option value="INFORME_GOBIERNO" <?= ($obra['tipo_destino'] ?? '') === 'INFORME_GOBIERNO' ? 'selected' : '' ?>>
            Informe de Gobierno
        </option>
        <option value="AMBOS" <?= ($obra['tipo_destino'] ?? '') === 'AMBOS' ? 'selected' : '' ?>>
            Ambos
        </option>
    </select>
</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Dependencias que Realizan Edificaciones (OALTA)</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: C.C.T. -->
                    <div class="col-md-4 mb-3">
                        <label for="cct" class="form-label">C.C.T.</label>
                        <input type="text" class="form-control" id="cct" name="cct"
                            value="<?= htmlspecialchars($obra['cct_oalta'] ?? '-') ?>" placeholder="Ingrese C.C.T.">
                    </div>
                    <!-- Campo: Obra(s) -->
                    <div class="col-md-4 mb-3">
                        <label for="obras" class="form-label">Obra(s)</label>
                        <input type="number" class="form-control" id="obras" name="obras"
                            value="<?= $obra['obras_oalta'] ?? 0 ?>" placeholder="0">
                    </div>
                    <!-- Campo: Aula(s) -->
                    <div class="col-md-4 mb-3">
                        <label for="aulas" class="form-label">Aula(s)</label>
                        <input type="number" class="form-control" id="aulas" name="aulas"
                            value="<?= $obra['aulas_oalta'] ?? 0 ?>" placeholder="0">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Laboratorio(s) -->
                    <div class="col-md-4 mb-3">
                        <label for="laboratorios" class="form-label">Laboratorio(s)</label>
                        <input type="number" class="form-control" id="laboratorios" name="laboratorios"
                            value="<?= $obra['laboratorios_oalta'] ?? 0 ?>" placeholder="0">
                    </div>
                    <!-- Campo: Taller(es) -->
                    <div class="col-md-4 mb-3">
                        <label for="talleres" class="form-label">Taller(es)</label>
                        <input type="number" class="form-control" id="talleres" name="talleres"
                            value="<?= $obra['talleres_oalta'] ?? 0 ?>" placeholder="0">
                    </div>
                    <!-- Campo: Anexo(s) -->
                    <div class="col-md-4 mb-3">
                        <label for="anexos" class="form-label">Anexo(s)</label>
                        <input type="number" class="form-control" id="anexos" name="anexos"
                            value="<?= $obra['anexos_oalta'] ?? 0 ?>" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="descripcion_oalta" class="form-label">Descripción de OALTA:</label>
                    <textarea class="form-control" id="descripcion_oalta" name="descripcion_oalta"
                        rows="3"><?= htmlspecialchars($obra['descripcion_oalta'] ?? '-') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Apertura Programática</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Finalidad / Función / Subfunción -->
                    <div class="col-md-6 mb-3">
                        <label for="finalidad" class="form-label">Finalidad / Función / Subfunción</label>
                        <select class="form-select" id="finalidad" name="finalidad">
                            <option value="">Seleccione...</option>
                            <?php foreach ($finalidades as $finalidad): ?>
                                <option value="<?= $finalidad['id']; ?>" <?= ($finalidad['id'] == $obra['id_funcion']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($finalidad['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Programa Presupuestal (modalidad) -->
                    <div class="col-md-6 mb-3">
                        <label for="programaPresupuestal" class="form-label">Programa Presupuestal (Modalidad)</label>
                        <select class="form-select" id="programaPresupuestal" name="programaPresupuestal">
                            <option value="">Seleccione...</option>
                            <?php foreach ($modalidades as $modalidad): ?>
                                <option value="<?= $modalidad['id']; ?>" <?= ($modalidad['id'] == $obra['id_modalidad']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($modalidad['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Partida -->
                    <div class="col-md-4 mb-3">
                        <label for="partida" class="form-label">Partida</label>
                        <select class="form-select" id="partida" name="partida">
                            <option value="">Seleccione...</option>
                            <?php foreach ($partidas as $partida): ?>
                                <option value="<?= $partida['id']; ?>" <?= ($partida['id'] == $obra['id_partida']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($partida['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Tipo Gasto (TG) -->
                    <div class="col-md-4 mb-3">
                        <label for="tipoGasto" class="form-label">Tipo Gasto (TG)</label>
                        <select class="form-select" id="tipoGasto" name="tipoGasto">
                            <option value="">Seleccione...</option>
                            <?php foreach ($tipoGastos as $tg): ?>
                                <option value="<?= $tg['id']; ?>" <?= ($tg['id'] == $obra['id_tipoGasto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tg['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Prioridad -->
                    <div class="col-md-4 mb-3">
                        <label for="prioridad" class="form-label">Prioridad</label>
                        <input type="text" class="form-control" id="prioridad" name="prioridad"
                            value="<?= htmlspecialchars($obra['prioridad'] ?? '1') ?>">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Fuente de Financiamiento (FF) -->
                    <div class="col-md-6 mb-3">
                        <label for="fuenteFinanciamiento" class="form-label">Fuente de Financiamiento (FF)</label>
                        <select class="form-select" id="fuenteFinanciamiento" name="fuenteFinanciamiento">
                            <option value="">Seleccione...</option>
                            <?php foreach ($fuenteFinanciamientos as $ff): ?>
                                <option value="<?= $ff['id']; ?>" <?= ($ff['id'] == $obra['id_fuenteFinanciamiento']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ff['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Programada -->
        <div class="card mb-4">
            <div class="card-header">Inversión Programada</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_federal"
                            name="inversion_programada_federal"
                            value="<?= isset($inversion['inversion_programada_federal']) ? $inversion['inversion_programada_federal'] : 0 ?>">
                    </div>
                    <!-- Campo: Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_estatal"
                            name="inversion_programada_estatal"
                            value="<?= isset($inversion['inversion_programada_estatal']) ? $inversion['inversion_programada_estatal'] : 0 ?>">
                    </div>
                    <!-- Campo: Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_municipal"
                            name="inversion_programada_municipal"
                            value="<?= isset($inversion['inversion_programada_municipal']) ? $inversion['inversion_programada_municipal'] : 0 ?>">
                    </div>
                    <!-- Campo: Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_beneficiarios"
                            name="inversion_programada_beneficiarios"
                            value="<?= isset($inversion['inversion_programada_beneficiarios']) ? $inversion['inversion_programada_beneficiarios'] : 0 ?>">
                    </div>
                    <!-- Campo: Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_otros"
                            name="inversion_programada_otros"
                            value="<?= isset($inversion['inversion_programada_otros']) ? $inversion['inversion_programada_otros'] : 0 ?>">
                    </div>
                    <!-- Campo: Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_credito"
                            name="inversion_programada_credito"
                            value="<?= isset($inversion['inversion_programada_credito']) ? $inversion['inversion_programada_credito'] : 0 ?>">
                    </div>
                </div>
            </div>
        </div>


        <!-- Sección: Inversión Autorizada -->
        <div class="card mb-4">
            <div class="card-header">Inversión Autorizada</div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_federal"
                            name="inversion_autorizada_federal"
                            value="<?= isset($inversion['inversion_autorizada_federal']) ? $inversion['inversion_autorizada_federal'] : 0 ?>">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_estatal"
                            name="inversion_autorizada_estatal"
                            value="<?= isset($inversion['inversion_autorizada_estatal']) ? $inversion['inversion_autorizada_estatal'] : 0 ?>">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_municipal"
                            name="inversion_autorizada_municipal"
                            value="<?= isset($inversion['inversion_autorizada_municipal']) ? $inversion['inversion_autorizada_municipal'] : 0 ?>">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_beneficiarios"
                            name="inversion_autorizada_beneficiarios"
                            value="<?= isset($inversion['inversion_autorizada_beneficiarios']) ? $inversion['inversion_autorizada_beneficiarios'] : 0 ?>">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_otros"
                            name="inversion_autorizada_otros"
                            value="<?= isset($inversion['inversion_autorizada_otros']) ? $inversion['inversion_autorizada_otros'] : 0 ?>">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_credito"
                            name="inversion_autorizada_credito"
                            value="<?= isset($inversion['inversion_autorizada_credito']) ? $inversion['inversion_autorizada_credito'] : 0 ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Modificada -->
        <div class="card mb-4">
            <div class="card-header">Inversión Modificada</div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_federal"
                            name="inversion_modificada_federal"
                            value="<?= isset($inversion['inversion_modificada_federal']) ? $inversion['inversion_modificada_federal'] : 0 ?>">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_estatal"
                            name="inversion_modificada_estatal"
                            value="<?= isset($inversion['inversion_modificada_estatal']) ? $inversion['inversion_modificada_estatal'] : 0 ?>">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_municipal"
                            name="inversion_modificada_municipal"
                            value="<?= isset($inversion['inversion_modificada_municipal']) ? $inversion['inversion_modificada_municipal'] : 0 ?>">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_beneficiarios"
                            name="inversion_modificada_beneficiarios"
                            value="<?= isset($inversion['inversion_modificada_beneficiarios']) ? $inversion['inversion_modificada_beneficiarios'] : 0 ?>">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_otros"
                            name="inversion_modificada_otros"
                            value="<?= isset($inversion['inversion_modificada_otros']) ? $inversion['inversion_modificada_otros'] : 0 ?>">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_credito"
                            name="inversion_modificada_credito"
                            value="<?= isset($inversion['inversion_modificada_credito']) ? $inversion['inversion_modificada_credito'] : 0 ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Liberada -->
        <div class="card mb-4">
            <div class="card-header">Inversión Liberada</div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_federal"
                            name="inversion_liberada_federal"
                            value="<?= isset($inversion['inversion_liberada_federal']) ? $inversion['inversion_liberada_federal'] : 0 ?>">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_estatal"
                            name="inversion_liberada_estatal"
                            value="<?= isset($inversion['inversion_liberada_estatal']) ? $inversion['inversion_liberada_estatal'] : 0 ?>">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_municipal"
                            name="inversion_liberada_municipal"
                            value="<?= isset($inversion['inversion_liberada_municipal']) ? $inversion['inversion_liberada_municipal'] : 0 ?>">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_beneficiarios"
                            name="inversion_liberada_beneficiarios"
                            value="<?= isset($inversion['inversion_liberada_beneficiarios']) ? $inversion['inversion_liberada_beneficiarios'] : 0 ?>">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_otros"
                            name="inversion_liberada_otros"
                            value="<?= isset($inversion['inversion_liberada_otros']) ? $inversion['inversion_liberada_otros'] : 0 ?>">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_credito"
                            name="inversion_liberada_credito"
                            value="<?= isset($inversion['inversion_liberada_credito']) ? $inversion['inversion_liberada_credito'] : 0 ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Ejercida -->
        <div class="card mb-4">
            <div class="card-header">Inversión Ejercida</div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_federal"
                            name="inversion_ejercida_federal"
                            value="<?= isset($inversion['inversion_ejercida_federal']) ? $inversion['inversion_ejercida_federal'] : 0 ?>">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_estatal"
                            name="inversion_ejercida_estatal"
                            value="<?= isset($inversion['inversion_ejercida_estatal']) ? $inversion['inversion_ejercida_estatal'] : 0 ?>">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_municipal"
                            name="inversion_ejercida_municipal"
                            value="<?= isset($inversion['inversion_ejercida_municipal']) ? $inversion['inversion_ejercida_municipal'] : 0 ?>">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_beneficiarios"
                            name="inversion_ejercida_beneficiarios"
                            value="<?= isset($inversion['inversion_ejercida_beneficiarios']) ? $inversion['inversion_ejercida_beneficiarios'] : 0 ?>">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_otros"
                            name="inversion_ejercida_otros"
                            value="<?= isset($inversion['inversion_ejercida_otros']) ? $inversion['inversion_ejercida_otros'] : 0 ?>">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_credito"
                            name="inversion_ejercida_credito"
                            value="<?= isset($inversion['inversion_ejercida_credito']) ? $inversion['inversion_ejercida_credito'] : 0 ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Modalidad -->
        <div class="card mb-4">
            <div class="card-header">Modalidad</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Modalidad de la Inversión -->
                    <div class="col-md-6 mb-3">
                        <label for="modalidadInversion" class="form-label">Modalidad de la Inversión</label>
                        <select class="form-select" id="modalidadInversion" name="modalidadInversion">
                            <option value="">Seleccione...</option>
                            <?php foreach ($modalidadesInv as $modalidadInv): ?>
                                <option value="<?= $modalidadInv['id']; ?>"
                                    <?= ($modalidadInv['id'] == $obra['id_modalidadInv']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($modalidadInv['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo: Tipo de Ejecución (opciones fijas) -->
                    <div class="col-md-6 mb-3">
                        <label for="tipoEjecucion" class="form-label">Tipo de Ejecución</label>
                        <select class="form-select" id="tipoEjecucion" name="tipoEjecucion">
                            <option value="" <?= empty($obra['tipo_ejecucion']) ? 'selected' : '' ?>>Seleccione...
                            </option>
                            <option value="tipo1" <?= ($obra['tipo_ejecucion'] ?? '') == 'tipo1' ? 'selected' : '' ?>>Tipo
                                1</option>
                            <option value="tipo2" <?= ($obra['tipo_ejecucion'] ?? '') == 'tipo2' ? 'selected' : '' ?>>Tipo
                                2</option>
                        </select>
                    </div>

                    <!-- Campo: Indicadores de Pobreza (CONEVAL) -->
                    <div class="col-md-6 mb-3">
                        <label for="indicadoresPobreza" class="form-label">Indicadores de Pobreza (CONEVAL)</label>
                        <select class="form-select" id="indicadoresPobreza" name="indicadoresPobreza">
                            <option value="" <?= empty($obra['indicadores_pobreza']) ? 'selected' : '' ?>>Seleccione...
                            </option>
                            <option value="coneval1" <?= ($obra['indicadores_pobreza'] ?? '') == 'coneval1' ? 'selected' : '' ?>>CONEVAL 1</option>
                            <option value="coneval2" <?= ($obra['indicadores_pobreza'] ?? '') == 'coneval2' ? 'selected' : '' ?>>CONEVAL 2</option>
                        </select>
                    </div>

                    <!-- Campo: Sector -->
                    <div class="col-md-6 mb-3">
                        <label for="sector" class="form-label">Sector</label>
                        <select class="form-select" id="sector" name="sector">
                            <option value="">Seleccione...</option>
                            <?php foreach ($sectores as $sector): ?>
                                <option value="<?= $sector['id']; ?>" <?= ($sector['id'] == $obra['id_sector']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sector['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Proyecto -->
                    <div class="col-md-6 mb-3">
                        <label for="proyecto" class="form-label">Proyecto</label>
                        <select class="form-select" id="proyecto" name="proyecto">
                            <option value="">Seleccione...</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?= $proyecto['id']; ?>" <?= ($proyecto['id'] == $obra['id_proyecto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($proyecto['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Programas de Inversión -->
                    <div class="col-md-6 mb-3">
                        <label for="programasInversion" class="form-label">Programas de Inversión</label>
                        <select class="form-select" id="programasInversion" name="programasInversion">
                            <option value="">Seleccione...</option>
                            <?php foreach ($programasInv as $programa): ?>
                                <option value="<?= $programa['id']; ?>" <?= ($programa['id'] == $obra['id_programaInv']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($programa['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: CAUSAS -->
                    <div class="col-md-12 mb-3">
                        <label for="causas" class="form-label">CAUSAS</label>
                        <textarea class="form-control" id="causas" name="causas" rows="3"
                            placeholder="Describa las causas"><?= htmlspecialchars($obra['causas'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Tiempo de Mayor Intensidad del Indicador -->
                    <div class="col-md-6 mb-3">
                        <label for="tiempoMayorIntensidad" class="form-label">Tiempo de Mayor Intensidad del
                            Indicador</label>
                        <input type="text" class="form-control" id="tiempoMayorIntensidad" name="tiempoMayorIntensidad"
                            placeholder="Describa el período"
                            value="<?= htmlspecialchars($obra['tiempo_mayor_intensidad'] ?? '') ?>">
                    </div>
                    <!-- Campo: Razones por la que se Construye la Obra -->
                    <div class="col-md-6 mb-3">
                        <label for="razonesConstruccionObra" class="form-label">Razones por la que se Construye la
                            Obra</label>
                        <textarea class="form-control" id="razonesConstruccionObra" name="razonesConstruccionObra"
                            rows="3"
                            placeholder="Describa las razones"><?= htmlspecialchars($obra['razones_construccion_obra'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Meta Anual -->
        <div class="card mb-4">
            <div class="card-header">Meta Anual</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Servicio Cantidad -->
                    <div class="col-md-4 mb-3">
                        <label for="servicioCantidad" class="form-label">Servicio Cantidad</label>
                        <input type="number" class="form-control" id="servicioCantidad" name="servicioCantidad"
                            value="<?= isset($obra['meta_servicio_cantidad']) ? $obra['meta_servicio_cantidad'] : 0 ?>"
                            placeholder="0">
                    </div>
                    <!-- Campo: Beneficiarios Cantidad -->
                    <div class="col-md-4 mb-3">
                        <label for="beneficiariosCantidad" class="form-label">Beneficiarios Cantidad</label>
                        <input type="number" class="form-control" id="beneficiariosCantidad"
                            name="beneficiariosCantidad"
                            value="<?= isset($obra['meta_beneficiarios_cantidad']) ? $obra['meta_beneficiarios_cantidad'] : 0 ?>"
                            placeholder="0">
                    </div>
                    <!-- Campo: Cantidad Total -->
                    <div class="col-md-4 mb-3">
                        <label for="cantidadTotal" class="form-label">Cantidad Total</label>
                        <input type="number" class="form-control" id="cantidadTotal" name="cantidadTotal"
                            value="<?= isset($obra['meta_cantidad_total']) ? $obra['meta_cantidad_total'] : 0 ?>"
                            placeholder="0">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Servicio Unidad -->
                    <div class="col-md-6 mb-3">
                        <label for="servicioUnidad" class="form-label">Servicio Unidad</label>
                        <select class="form-select" id="servicioUnidad" name="servicioUnidad">
                            <option value="">Seleccione...</option>
                            <?php foreach ($unidadServicio as $servicio): ?>
                                <option value="<?= $servicio['id']; ?>" <?= ($servicio['id'] == $obra['id_servicio']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($servicio['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Beneficiarios Unidad -->
                    <div class="col-md-6 mb-3">
                        <label for="beneficiariosUnidad" class="form-label">Beneficiarios Unidad</label>
                        <select class="form-select" id="beneficiariosUnidad" name="beneficiariosUnidad">
                            <option value="">Seleccione...</option>
                            <?php foreach ($unidadBeneficiarios as $beneficiario): ?>
                                <option value="<?= $beneficiario['id']; ?>"
                                    <?= ($beneficiario['id'] == $obra['id_beneficiarios']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($beneficiario['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Avance Físico Anual -->
                    <div class="col-md-6 mb-3">
                        <label for="avanceFisicoAnual" class="form-label">Avance Físico Anual</label>
                        <input type="number" class="form-control" id="avanceFisicoAnual" name="avanceFisicoAnual"
                            value="<?= isset($obra['avance_fisico_anual']) ? $obra['avance_fisico_anual'] : 0 ?>"
                            placeholder="0%">
                    </div>
                    <!-- Campo: Avance Financiero Anual -->
                    <div class="col-md-6 mb-3">
                        <label for="avanceFinancieroAnual" class="form-label">Avance Financiero Anual</label>
                        <input type="number" class="form-control" id="avanceFinancieroAnual"
                            name="avanceFinancieroAnual"
                            value="<?= isset($obra['avance_financiero_anual']) ? $obra['avance_financiero_anual'] : 0 ?>"
                            placeholder="0%">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Observaciones -->
                    <div class="col-md-12 mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                            placeholder="Ingrese observaciones"><?= htmlspecialchars($obra['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>



        <button type="submit" class="btn btn-primary">Registrar Obra</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>

<!-- Asegúrate de cargar jQuery -->
<!-- Asegúrate de cargar jQuery antes -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Cuando se cambie el select "Eje"

        var ejePre = "<?= $obra['id_eje'] ?? '' ?>";
        var objetivoPre = "<?= $obra['id_objetivo'] ?? '' ?>";
        var estrategiaPre = "<?= $obra['id_estrategia'] ?? '' ?>";
        var lineaPre = "<?= $obra['id_linea_accion'] ?? '' ?>";
        var indicadorPre = "<?= $obra['id_indicador'] ?? '' ?>";

        // Si hay un Eje seleccionado, cargar Objetivos y Objetivos precargados
        if (ejePre !== "") {
            $.ajax({
                url: "capturaObraGet.php",
                type: "GET",
                data: { nivel: "objetivos", id: ejePre },
                dataType: "json",
                success: function (data) {
                    var html = '<option value="">Seleccione...</option>';
                    $.each(data, function (index, item) {
                        var selected = (item.id == objetivoPre) ? "selected" : "";
                        html += '<option value="' + item.id + '" ' + selected + '>' + item.nombre + '</option>';
                    });
                    $("#objetivo").html(html);
                },
                error: function () {
                    console.error("Error al cargar los objetivos.");
                }
            });

            // Puedes hacer lo mismo para Indicadores (si dependen directamente del Eje)
            $.ajax({
                url: "capturaObraGet.php",
                type: "GET",
                data: { nivel: "indicadores", id: ejePre },
                dataType: "json",
                success: function (data) {
                    var html = '<option value="">Seleccione...</option>';
                    $.each(data, function (index, item) {
                        var selected = (item.id == indicadorPre) ? "selected" : "";
                        html += '<option value="' + item.id + '" ' + selected + '>' + item.nombre + '</option>';
                    });
                    $("#indicador").html(html);
                },
                error: function () {
                    console.error("Error al cargar los indicadores.");
                }
            });
        }

        // Al cambiar manualmente el select de "Objetivo", cargar estrategias
        $("#objetivo").change(function () {
            var objetivoID = $(this).val();
            $.ajax({
                url: "capturaObraGet.php",
                type: "GET",
                data: { nivel: "estrategias", id: objetivoID },
                dataType: "json",
                success: function (data) {
                    var html = '<option value="">Seleccione...</option>';
                    $.each(data, function (index, item) {
                        var selected = (item.id == estrategiaPre) ? "selected" : "";
                        html += '<option value="' + item.id + '" ' + selected + '>' + item.nombre + '</option>';
                    });
                    $("#estrategias").html(html);
                },
                error: function () {
                    console.error("Error al cargar las estrategias.");
                }
            });
        });

        // Al cambiar manualmente "Estrategias", cargar Línea de Acción
        $("#estrategias").change(function () {
            var estrategiaID = $(this).val();
            $.ajax({
                url: "capturaObraGet.php",
                type: "GET",
                data: { nivel: "lineaAccion", id: estrategiaID },
                dataType: "json",
                success: function (data) {
                    var html = '<option value="">Seleccione...</option>';
                    $.each(data, function (index, item) {
                        var selected = (item.id == lineaPre) ? "selected" : "";
                        html += '<option value="' + item.id + '" ' + selected + '>' + item.nombre + '</option>';
                    });
                    $("#lineaAccion").html(html);
                },
                error: function () {
                    console.error("Error al cargar la línea de acción.");
                }
            });
        });

        $("#eje").change(function () {
            var ejeID = $(this).val();

            // Reiniciamos los selects dependientes: objetivo, estrategias, línea de acción e indicador.
            $("#objetivo").html('<option selected>Seleccione...</option>');
            $("#estrategias").html('<option selected>Seleccione...</option>');
            $("#lineaAccion").html('<option selected>Seleccione...</option>');
            $("#indicador").html('<option selected>Seleccione...</option>');

            // Cargar los objetivos relacionados al eje seleccionado.
            $.ajax({
                url: "capturarObraGet.php",
                type: "GET",
                data: { nivel: "objetivos", id: ejeID },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, item) {
                        $("#objetivo").append('<option value="' + item.id + '">' + item.nombre + '</option>');
                    });
                },
                error: function () {
                    console.error("Error al cargar los objetivos.");
                }
            });

            // Cargar los indicadores relacionados al eje seleccionado.
            $.ajax({
                url: "capturarObraGet.php",
                type: "GET",
                data: { nivel: "indicadores", id: ejeID },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, item) {
                        $("#indicador").append('<option value="' + item.id + '">' + item.nombre + '</option>');
                    });
                },
                error: function () {
                    console.error("Error al cargar los indicadores.");
                }
            });
        });

        // Cuando cambie el select "Objetivo", cargar las estrategias.
        $("#objetivo").change(function () {
            var objetivoID = $(this).val();
            $("#estrategias").html('<option selected>Seleccione...</option>');
            $("#lineaAccion").html('<option selected>Seleccione...</option>');

            $.ajax({
                url: "capturarObraGet.php",
                type: "GET",
                data: { nivel: "estrategias", id: objetivoID },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, item) {
                        $("#estrategias").append('<option value="' + item.id + '">' + item.nombre + '</option>');
                    });
                },
                error: function () {
                    console.error("Error al cargar las estrategias.");
                }
            });
        });

        // Cuando cambie el select "Estrategias", cargar las líneas de acción.
        $("#estrategias").change(function () {
            var estrategiaID = $(this).val();
            $("#lineaAccion").html('<option selected>Seleccione...</option>');

            $.ajax({
                url: "capturarObraGet.php",
                type: "GET",
                data: { nivel: "lineaAccion", id: estrategiaID },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, item) {
                        $("#lineaAccion").append('<option value="' + item.id + '">' + item.nombre + '</option>');
                    });
                },
                error: function () {
                    console.error("Error al cargar la línea de acción.");
                }
            });
        });

        $("#municipio").change(function () {
            var municipioID = $(this).val();
            // Reinicia el select de Localidad a su opción inicial
            $("#localidad").html('<option selected>Seleccione...</option>');

            $.ajax({
                url: "capturarObraGet.php",
                type: "GET",
                data: { nivel: "localidades", id: municipioID },
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, item) {
                        $("#localidad").append('<option value="' + item.id + '">' + item.nombre + '</option>');
                    });
                },
                error: function () {
                    console.error("Error al cargar las localidades.");
                }
            });
        });
    });
</script>