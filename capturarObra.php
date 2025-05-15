<?php
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
$nivel_usuario = $_SESSION['nivel'] ?? null;
$id_dependencia_usuario = $_SESSION['id_dependencia'] ?? null;

// Comprobar si alguna variable es nula
if ($id_usuario === null || $nivel_usuario === null || $id_dependencia_usuario === null) {
    header("Location: login.php");
    exit;
}

include 'includes/header.php';
include 'capturarObraQueries.php';

// Cargamos los select
$ejes = obtenerOpciones('eje', 'id_eje', 'nombre');
$tiposObra = obtenerOpciones('tipo_obra', 'id_tipoObra', 'nombre');

$municipios = obtenerOpciones('municipio', 'id_municipio', 'nombre');
$finalidades = obtenerOpciones('finalidad', 'id_funcion', 'finalidad_nombre');
$modalidades = obtenerOpciones('modalidad', 'id_modalidad', 'modalidad_nombre');
$partidas = obtenerOpciones('partida', 'id_partida', 'partida_nombre');
$tipoGastos = obtenerOpciones('tipo_gasto', 'id_tipoGasto', 'tipoGasto_nombre');
$fuenteFinanciamientos = obtenerOpciones('fuente_financiamiento', 'id_fuenteFinanciamiento', 'nombre');
$sectores = obtenerOpciones('sector', 'id_sector', 'nombre');
$proyectos = obtenerOpciones('proyecto', 'id_proyecto', 'proyecto_nombre');  // Nota: la columna es "proyecto_nombre"
$programasInversion = obtenerOpciones('programa_inversion', 'id_programaInv', 'nombre');
$modalidadesInversion = obtenerOpciones('modalidad_inversion', 'id_modalidadInv', 'nombre');
$unidadServicio = obtenerOpciones('unidad_servicio', 'id_servicio', 'nombre');
$unidadBeneficiarios = obtenerOpciones('unidad_beneficiarios', 'id_beneficiarios', 'nombre');

if ($nivel_usuario == 1) {
    $dependencias = obtenerOpciones('dependencia', 'id_dependencia', 'nombre');
} else {
    $dependencias = obtenerOpcionesFiltradas('dependencia', 'id_dependencia', 'nombre', 'id_dependencia', $id_dependencia_usuario);
}
?>

<div class="container mt-4">
    <h1 class="mb-4">Capturar Obra</h1>
    <form action="capturarObraProceso.php" method="POST">
        <!-- Sección: Datos de la Obra -->
        <div class="card mb-4">
            <div class="card-header">Datos de la Obra</div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Eje -->
                    <div class="col-md-6 mb-3">
                        <label for="eje" class="form-label">Eje</label>
                        <select class="form-select" id="eje" name="eje">
                            <option selected>Seleccione...</option>
                            <?php foreach ($ejes as $eje): ?>
                                <option value="<?= $eje['id']; ?>"><?= $eje['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Objetivo -->
                    <div class="col-md-6 mb-3">
                        <label for="objetivo" class="form-label">Objetivo</label>
                        <select class="form-select" id="objetivo" name="objetivo">
                            <option selected>Seleccione...</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Estrategias -->
                    <div class="col-md-6 mb-3">
                        <label for="estrategias" class="form-label">Estrategias</label>
                        <select class="form-select" id="estrategias" name="estrategias">
                            <option selected>Seleccione...</option>
                        </select>
                    </div>
                    <!-- Campo: Línea de Acción -->
                    <div class="col-md-6 mb-3">
                        <label for="lineaAccion" class="form-label">Línea de Acción</label>
                        <select class="form-select" id="lineaAccion" name="lineaAccion">
                            <option selected>Seleccione...</option>
                        </select>
                    </div>
                    <!-- Campo: Indicador -->
                    <div class="col-md-6 mb-3">
                        <label for="indicador" class="form-label">Indicador</label>
                        <select class="form-select" id="indicador" name="indicador">
                            <option selected>Seleccione...</option>
                            <!-- Las opciones se cargarán dinámicamente según el eje seleccionado -->
                        </select>
                    </div>

                    <!-- Campo: Tipo de Obra -->
                    <div class="col-md-6 mb-3">
                        <label for="tipoObra" class="form-label">Tipo de Obra</label>
                        <select class="form-select" id="tipoObra" name="tipoObra">
                            <option selected>Seleccione...</option>
                            <?php foreach ($tiposObra as $tipo): ?>
                                <option value="<?= $tipo['id']; ?>"><?= $tipo['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Detalles de Ejecución -->
        <div class="card mb-4">
            <div class="card-header">
                Detalles de Ejecución
            </div>
            <div class="card-body">
                <!-- Campo: Obra o Acción -->
                <div class="row">
                    <div class="mb-3">
                        <label for="obraAccion" class="form-label">Obra o Acción</label>
                        <input type="text" class="form-control" id="obraAccion" name="obraAccion"
                            placeholder="Ingrese la obra o acción">
                    </div>
                </div>
                <div class="row">

                    <!-- Campo: Dependencia -->
                    <div class="mb-3">
                        <label for="dependencia" class="form-label">Dependencia</label>
                        <select class="form-select" id="dependencia" name="dependencia">
                            <option value="">Seleccione...</option>
                            <?php foreach ($dependencias as $dep): ?>
                                <option value="<?= $dep['id']; ?>" <?= ($nivel_usuario == 0) ? 'selected' : '' ?>>
                                    <?= $dep['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
                <div class="row">
                    <!-- Campo: Latitud -->
                    <div class="col-md-4 mb-3">
                        <label for="latitud" class="form-label">Latitud</label>
                        <input type="text" class="form-control" id="latitud" name="latitud"
                            placeholder="Ingrese la latitud">
                    </div>
                    <!-- Campo: Longitud -->
                    <div class="col-md-4 mb-3">
                        <label for="longitud" class="form-label">Longitud</label>
                        <input type="text" class="form-control" id="longitud" name="longitud"
                            placeholder="Ingrese la longitud">
                    </div>
                    <!-- Campo: Estatus de la Obra -->
                    <div class="col-md-4 mb-3">
                        <label for="estatusObra" class="form-label">Estatus de la Obra</label>
                        <select class="form-select" id="estatusObra" name="estatusObra">
                            <option selected>Seleccione...</option>
                            <option value="En_progreso">En Progreso</option>
                            <option value="Completa">Completa</option>
                            <option value="Planificada">Planificada</option>
                        </select>
                    </div>

                </div>
                <div class="row">
                    <!-- Campo: Año Ejercicio Fiscal -->
                    <div class="col-md-4 mb-3">
                        <label for="ejercicioFiscal" class="form-label">Año Ejercicio Fiscal</label>
                        <input type="number" class="form-control" id="ejercicioFiscal" name="ejercicioFiscal"
                            placeholder="Ingrese el año">
                    </div>
                    <!-- Campo: Fecha Inicio -->
                    <div class="col-md-4 mb-3">
                        <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                    </div>
                    <!-- Campo: Fecha Termino -->
                    <div class="col-md-4 mb-3">
                        <label for="fechaTermino" class="form-label">Fecha Termino</label>
                        <input type="date" class="form-control" id="fechaTermino" name="fechaTermino">
                    </div>
                </div>
                <!-- Nueva fila para campos adicionales -->
            </div>
        </div>

        <!-- Sección: Información Geográfica -->
        <div class="card mb-4">
            <div class="card-header">
                Información Geográfica
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Municipio -->
                    <div class="col-md-6 mb-3">
                        <label for="municipio" class="form-label">Municipio</label>
                        <select class="form-select" id="municipio" name="municipio">
                            <option selected>Seleccione...</option>
                            <?php foreach ($municipios as $mun): ?>
                                <option value="<?= $mun['id']; ?>"><?= $mun['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo: Localidad -->
                    <div class="col-md-6 mb-3">
                        <label for="localidad" class="form-label">Localidad</label>
                        <select class="form-select" id="localidad" name="localidad">
                            <option selected>Seleccione...</option>
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
                            placeholder="Ingrese la etapa de la obra">
                    </div>
                    <!-- Campo: Tipo de Destino -->
                    <div class="col-md-6 mb-3">
                        <label for="tipo_destino" class="form-label">Destino</label>
                        <select class="form-select" id="tipo_destino" name="tipo_destino">
                            <option value="EJERCICIO_FISCAL" selected>Ejercicio Fiscal</option>
                            <option value="INFORME_GOBIERNO">Informe de Gobierno</option>
                            <option value="AMBOS">Ambos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Dependencias que realizan edificaciones (OALTA) -->
        <div class="card mb-4">
            <div class="card-header">
                DEPENDENCIAS QUE REALIZAN EDIFICACIONES (OALTA)
            </div>
            <div class="card-body">
                <!-- Primera fila: C.C.T., Obra(s) y Aula(s) -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="cct" class="form-label">C.C.T.</label>
                        <input type="text" class="form-control" id="cct" name="cct" value="-"
                            placeholder="Ingrese C.C.T.">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="obras" class="form-label">Obra(s)</label>
                        <input type="number" class="form-control" id="obras" name="obras" value="0" placeholder="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="aulas" class="form-label">Aula(s)</label>
                        <input type="number" class="form-control" id="aulas" name="aulas" value="0" placeholder="0">
                    </div>
                </div>
                <!-- Segunda fila: Laboratorio(s), Taller(es) y Anexo(s) -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="laboratorios" class="form-label">Laboratorio(s)</label>
                        <input type="number" class="form-control" id="laboratorios" name="laboratorios" value="0"
                            placeholder="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="talleres" class="form-label">Taller(es)</label>
                        <input type="number" class="form-control" id="talleres" name="talleres" value="0"
                            placeholder="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="anexos" class="form-label">Anexo(s)</label>
                        <input type="number" class="form-control" id="anexos" name="anexos" value="0" placeholder="0">
                    </div>
                </div>
                <!-- Campo: DESCRIPCIÓN DE OALTA -->
                <div class="mb-3">
                    <label for="descripcion_oalta" class="form-label">DESCRIPCIÓN DE OALTA</label>
                    <textarea class="form-control" id="descripcion_oalta" name="descripcion_oalta" rows="3">-</textarea>
                </div>
            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header">
                APERTURA PROGRAMATICA
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Finalidad / Función / Subfunción -->
                    <div class="col-md-6 mb-3">
                        <label for="finalidad" class="form-label">Finalidad / Función / Subfunción</label>
                        <select class="form-select" id="finalidad" name="finalidad">
                            <option selected>Seleccione...</option>
                            <?php foreach ($finalidades as $finalidad): ?>
                                <option value="<?= $finalidad['id']; ?>"><?= $finalidad['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Programa Presupuestal (modalidad) -->
                    <div class="col-md-6 mb-3">
                        <label for="programaPresupuestal" class="form-label">Programa Presupuestal (modalidad)</label>
                        <select class="form-select" id="programaPresupuestal" name="programaPresupuestal">
                            <option selected>Seleccione...</option>
                            <?php foreach ($modalidades as $modalidad): ?>
                                <option value="<?= $modalidad['id']; ?>"><?= $modalidad['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Partida -->
                    <div class="col-md-4 mb-3">
                        <label for="partida" class="form-label">Partida</label>
                        <select class="form-select" id="partida" name="partida">
                            <option selected>Seleccione...</option>
                            <?php foreach ($partidas as $partida): ?>
                                <option value="<?= $partida['id']; ?>"><?= $partida['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Tipo Gasto (TG) -->
                    <div class="col-md-4 mb-3">
                        <label for="tipoGasto" class="form-label">Tipo Gasto (TG)</label>
                        <select class="form-select" id="tipoGasto" name="tipoGasto">
                            <option selected>Seleccione...</option>
                            <?php foreach ($tipoGastos as $tg): ?>
                                <option value="<?= $tg['id']; ?>"><?= $tg['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Prioridad -->
                    <div class="col-md-4 mb-3">
                        <label for="prioridad" class="form-label">Prioridad</label>
                        <input type="text" class="form-control" id="prioridad" name="prioridad" value="1">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Fuente de Financiamiento (FF) -->
                    <div class="col-md-6 mb-3">
                        <label for="fuenteFinanciamiento" class="form-label">Fuente de Financiamiento (FF)</label>
                        <select class="form-select" id="fuenteFinanciamiento" name="fuenteFinanciamiento">
                            <option selected>Seleccione...</option>
                            <?php foreach ($fuenteFinanciamientos as $ff): ?>
                                <option value="<?= $ff['id']; ?>"><?= $ff['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <!-- Sección: Inversión Programada -->
        <div class="card mb-4">
            <div class="card-header">
                Inversión Programada
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_federal"
                            name="inversion_programada_federal" value="0">
                    </div>
                    <!-- Campo: Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_estatal"
                            name="inversion_programada_estatal" value="0">
                    </div>
                    <!-- Campo: Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_municipal"
                            name="inversion_programada_municipal" value="0">
                    </div>
                    <!-- Campo: Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_beneficiarios"
                            name="inversion_programada_beneficiarios" value="0">
                    </div>
                    <!-- Campo: Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_otros"
                            name="inversion_programada_otros" value="0">
                    </div>
                    <!-- Campo: Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_programada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_programada_credito"
                            name="inversion_programada_credito" value="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Autorizada -->
        <div class="card mb-4">
            <div class="card-header">
                Inversión Autorizada
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_federal"
                            name="inversion_autorizada_federal" value="0">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_estatal"
                            name="inversion_autorizada_estatal" value="0">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_municipal"
                            name="inversion_autorizada_municipal" value="0">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_beneficiarios"
                            name="inversion_autorizada_beneficiarios" value="0">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_otros"
                            name="inversion_autorizada_otros" value="0">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_autorizada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_autorizada_credito"
                            name="inversion_autorizada_credito" value="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Modificada -->
        <div class="card mb-4">
            <div class="card-header">
                Inversión Modificada
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_federal"
                            name="inversion_modificada_federal" value="0">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_estatal"
                            name="inversion_modificada_estatal" value="0">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_municipal"
                            name="inversion_modificada_municipal" value="0">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_beneficiarios"
                            name="inversion_modificada_beneficiarios" value="0">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_otros"
                            name="inversion_modificada_otros" value="0">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_modificada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_modificada_credito"
                            name="inversion_modificada_credito" value="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Liberada -->
        <div class="card mb-4">
            <div class="card-header">
                Inversión Liberada
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_federal"
                            name="inversion_liberada_federal" value="0">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_estatal"
                            name="inversion_liberada_estatal" value="0">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_municipal"
                            name="inversion_liberada_municipal" value="0">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_beneficiarios"
                            name="inversion_liberada_beneficiarios" value="0">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_otros"
                            name="inversion_liberada_otros" value="0">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_liberada_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_liberada_credito"
                            name="inversion_liberada_credito" value="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Inversión Ejercida -->
        <div class="card mb-4">
            <div class="card-header">
                Inversión Ejercida
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Federal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_federal" class="form-label">Federal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_federal"
                            name="inversion_ejercida_federal" value="0">
                    </div>
                    <!-- Estatal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_estatal" class="form-label">Estatal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_estatal"
                            name="inversion_ejercida_estatal" value="0">
                    </div>
                    <!-- Municipal -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_municipal" class="form-label">Municipal</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_municipal"
                            name="inversion_ejercida_municipal" value="0">
                    </div>
                    <!-- Beneficiarios -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_beneficiarios" class="form-label">Beneficiarios</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_beneficiarios"
                            name="inversion_ejercida_beneficiarios" value="0">
                    </div>
                    <!-- Otros -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_otros" class="form-label">Otros</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_otros"
                            name="inversion_ejercida_otros" value="0">
                    </div>
                    <!-- Crédito -->
                    <div class="col-md-2 mb-3">
                        <label for="inversion_ejercida_credito" class="form-label">Crédito</label>
                        <input type="number" step="0.01" class="form-control" id="inversion_ejercida_credito"
                            name="inversion_ejercida_credito" value="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Modalidad -->
        <div class="card mb-4">
            <div class="card-header">
                Modalidad
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Modalidad de la Inversión -->
                    <div class="col-md-6 mb-3">
                        <label for="modalidadInversion" class="form-label">Modalidad de la Inversión</label>
                        <select class="form-select" id="modalidadInversion" name="modalidadInversion">
                            <option selected>Seleccione...</option>
                            <?php foreach ($modalidadesInversion as $modalidadInv): ?>
                                <option value="<?= $modalidadInv['id']; ?>"><?= $modalidadInv['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Tipo de Ejecución (opciones fijas o estáticas) -->
                    <div class="col-md-6 mb-3">
                        <label for="tipoEjecucion" class="form-label">Tipo de Ejecución</label>
                        <select class="form-select" id="tipoEjecucion" name="tipoEjecucion">
                            <option selected>Seleccione...</option>
                            <option value="tipo1">Tipo 1</option>
                            <option value="tipo2">Tipo 2</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Indicadores de Pobreza (CONEVAL) (opciones estáticas) -->
                    <div class="col-md-6 mb-3">
                        <label for="indicadoresPobreza" class="form-label">Indicadores de Pobreza (CONEVAL)</label>
                        <select class="form-select" id="indicadoresPobreza" name="indicadoresPobreza">
                            <option selected>Seleccione...</option>
                            <option value="coneval1">CONEVAL 1</option>
                            <option value="coneval2">CONEVAL 2</option>
                        </select>
                    </div>
                    <!-- Campo: Sector (dinámico) -->
                    <div class="col-md-6 mb-3">
                        <label for="sector" class="form-label">Sector</label>
                        <select class="form-select" id="sector" name="sector">
                            <option selected>Seleccione...</option>
                            <?php foreach ($sectores as $sector): ?>
                                <option value="<?= $sector['id']; ?>"><?= $sector['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Proyecto (dinámico) -->
                    <div class="col-md-6 mb-3">
                        <label for="proyecto" class="form-label">Proyecto</label>
                        <select class="form-select" id="proyecto" name="proyecto">
                            <option selected>Seleccione...</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?= $proyecto['id']; ?>"><?= $proyecto['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Programas de Inversión (dinámico) -->
                    <div class="col-md-6 mb-3">
                        <label for="programasInversion" class="form-label">Programas de Inversión</label>
                        <select class="form-select" id="programasInversion" name="programasInversion">
                            <option selected>Seleccione...</option>
                            <?php foreach ($programasInversion as $programa): ?>
                                <option value="<?= $programa['id']; ?>"><?= $programa['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: CAUSAS -->
                    <div class="col-md-12 mb-3">
                        <label for="causas" class="form-label">CAUSAS</label>
                        <textarea class="form-control" id="causas" name="causas" rows="3"
                            placeholder="Describa las causas"></textarea>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Tiempo de Mayor Intensidad del Indicador -->
                    <div class="col-md-6 mb-3">
                        <label for="tiempoMayorIntensidad" class="form-label">Tiempo de Mayor Intensidad del
                            Indicador</label>
                        <input type="text" class="form-control" id="tiempoMayorIntensidad" name="tiempoMayorIntensidad"
                            placeholder="Describa el período">
                    </div>
                    <!-- Campo: Razones por la que se Construye la Obra -->
                    <div class="col-md-6 mb-3">
                        <label for="razonesConstruccionObra" class="form-label">Razones por la que se Construye la
                            Obra</label>
                        <textarea class="form-control" id="razonesConstruccionObra" name="razonesConstruccionObra"
                            rows="3" placeholder="Describa las razones"></textarea>
                    </div>
                </div>
            </div>
        </div>


        <!-- Sección: Meta Anual -->
        <div class="card mb-4">
            <div class="card-header">
                Meta Anual
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Campo: Servicio Cantidad -->
                    <div class="col-md-4 mb-3">
                        <label for="servicioCantidad" class="form-label">Servicio Cantidad</label>
                        <input type="number" class="form-control" id="servicioCantidad" name="servicioCantidad"
                            value="0" placeholder="0">
                    </div>
                    <!-- Campo: Beneficiarios Cantidad -->
                    <div class="col-md-4 mb-3">
                        <label for="beneficiariosCantidad" class="form-label">Beneficiarios Cantidad</label>
                        <input type="number" class="form-control" id="beneficiariosCantidad"
                            name="beneficiariosCantidad" value="0" placeholder="0">
                    </div>
                    <!-- Campo: Cantidad Total -->
                    <div class="col-md-4 mb-3">
                        <label for="cantidadTotal" class="form-label">Cantidad Total</label>
                        <input type="number" class="form-control" id="cantidadTotal" name="cantidadTotal" value="0"
                            placeholder="0">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Servicio Unidad -->
                    <div class="col-md-6 mb-3">
                        <label for="servicioUnidad" class="form-label">Servicio Unidad</label>
                        <select class="form-select" id="servicioUnidad" name="servicioUnidad">
                            <option selected>Seleccione...</option>
                            <?php foreach ($unidadServicio as $servicio): ?>
                                <option value="<?= $servicio['id']; ?>"><?= $servicio['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Campo: Beneficiarios Unidad -->
                    <div class="col-md-6 mb-3">
                        <label for="beneficiariosUnidad" class="form-label">Beneficiarios Unidad</label>
                        <select class="form-select" id="beneficiariosUnidad" name="beneficiariosUnidad">
                            <option selected>Seleccione...</option>
                            <?php foreach ($unidadBeneficiarios as $beneficiario): ?>
                                <option value="<?= $beneficiario['id']; ?>"><?= $beneficiario['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Avance Físico Anual -->
                    <div class="col-md-6 mb-3">
                        <label for="avanceFisicoAnual" class="form-label">Avance Físico Anual</label>
                        <input type="number" class="form-control" id="avanceFisicoAnual" name="avanceFisicoAnual"
                            value="0" placeholder="0%">
                    </div>
                    <!-- Campo: Avance Financiero Anual -->
                    <div class="col-md-6 mb-3">
                        <label for="avanceFinancieroAnual" class="form-label">Avance Financiero Anual</label>
                        <input type="number" class="form-control" id="avanceFinancieroAnual"
                            name="avanceFinancieroAnual" value="0" placeholder="0%">
                    </div>
                </div>
                <div class="row">
                    <!-- Campo: Observaciones -->
                    <div class="col-md-12 mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                            placeholder="Ingrese observaciones"></textarea>
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