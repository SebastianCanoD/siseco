<?php
// modificarProyectoProceso.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarProyecto.php');
    exit;
}

// Obtener los datos enviados por el formulario
$id_proyecto = isset($_POST['id_proyecto']) ? (int) $_POST['id_proyecto'] : 0;
$nombre      = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre del proyecto no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Preparar la consulta de actualización
$sql = "UPDATE proyecto SET proyecto_nombre = ? WHERE id_proyecto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nombre, $id_proyecto);

// Ejecutar consulta y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Proyecto actualizado correctamente.');
            window.location.href = 'buscarProyecto.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar el proyecto: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
exit;
