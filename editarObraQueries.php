<?php
// Archivo: queries.php

include 'includes/conexion.php'; // Conexión a la base de datos

/**
 * Obtiene las opciones de una tabla de la base de datos.
 *
 * @param string $tabla Nombre de la tabla.
 * @param string $columnaId Nombre de la columna de identificación.
 * @param string $columnaNombre Nombre de la columna que contiene la descripción a mostrar.
 *
 * @return array Arreglo de opciones, cada opción es un array asociativo con 'id' y 'nombre'.
 */
function obtenerOpciones($tabla, $columnaId, $columnaNombre) {
    global $conn;
    $sql = "SELECT $columnaId, $columnaNombre FROM $tabla";
    $resultado = mysqli_query($conn, $sql);
    
    $opciones = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // Para la tabla 'eje', ya no separamos la cadena aunque contenga comas
        if ($tabla === 'eje') {
            $opciones[] = [
                'id' => $fila[$columnaId],
                'nombre' => $fila[$columnaNombre]
            ];
        } else {
            // Para las demás tablas, se almacena la fila sin separaciones.
            $opciones[] = [
                'id' => $fila[$columnaId],
                'nombre' => $fila[$columnaNombre]
            ];
        }
    }
    return $opciones;
}


?>
