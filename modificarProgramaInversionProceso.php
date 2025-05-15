<?php
// modificarProgramaInversionProceso.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarProgramaInversion.php');
    exit;
}

// Obtener los datos enviados por el formulario
$id_programaInv = isset($_POST['id_programaInv']) ? (int) $_POST['id_programaInv'] : 0;
$nombre         = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre del programa de inversión no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Preparar la consulta de actualización
$sql = "UPDATE programa_inversion SET nombre = ? WHERE id_programaInv = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nombre, $id_programaInv);

// Ejecutar consulta y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Programa de inversión actualizado correctamente.');
            window.location.href = 'buscarProgramaInversion.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar el programa de inversión: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
exit;
