<?php
// modificarSectorProceso.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarSector.php');
    exit;
}

// Obtener los datos enviados por el formulario
$id_sector = isset($_POST['id_sector']) ? (int) $_POST['id_sector'] : 0;
$nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre del sector no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Preparar la consulta de actualización
$sql = "UPDATE sector SET nombre = ? WHERE id_sector = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nombre, $id_sector);

// Ejecutar consulta y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Sector actualizado correctamente.');
            window.location.href = 'buscarSector.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar el sector: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
exit;
