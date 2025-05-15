<?php
// modificarMetasServicioProceso.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarMetasServicio.php');
    exit;
}

// Obtener los datos enviados por el formulario
$id_servicio = isset($_POST['id_servicio']) ? (int) $_POST['id_servicio'] : 0;
$nombre      = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre de la meta de servicio no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Preparar la consulta de actualización
$sql = "UPDATE unidad_servicio SET nombre = ? WHERE id_servicio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nombre, $id_servicio);

// Ejecutar consulta y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Meta de servicio actualizada correctamente.');
            window.location.href = 'buscarMetasServicio.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar la meta de servicio: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
exit;
