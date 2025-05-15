<?php
include 'includes/conexion.php'; // Asegúrate de tener tu archivo de conexión

if (isset($_GET['nivel']) && isset($_GET['id'])) {
    $nivel = $_GET['nivel'];
    $parentId = intval($_GET['id']);

    // Definimos la consulta según el nivel solicitado.
    switch ($nivel) {
        case "objetivos":
            // Devuelve los objetivos del eje seleccionado.
            $sql = "SELECT id_objetivo AS id, nombre FROM objetivo WHERE id_eje = $parentId";
            break;
        case "estrategias":
            // Devuelve las estrategias del objetivo seleccionado.
            $sql = "SELECT id_estrategia AS id, nombre FROM estrategia WHERE id_objetivo = $parentId";
            break;
        case "lineaAccion":
            // Devuelve las líneas de acción de la estrategia seleccionada.
            $sql = "SELECT id_linea_accion AS id, nombre FROM linea_accion WHERE id_estrategia = $parentId";
            break;
        case "indicadores":
            // Devuelve los indicadores del eje seleccionado.
            $sql = "SELECT id_indicador AS id, nombre FROM indicador WHERE id_eje = $parentId";
            break;
        case "localidades":
            // Consulta para obtener localidades filtradas por el municipio
            $sql = "SELECT id_localidad AS id, nombre FROM localidad WHERE id_municipio = $parentId";
            break;
        default:
            echo json_encode([]);
            exit;


    }

    $result = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode($data);
}


?>