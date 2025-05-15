<?php
// procesarAltaMetasServicio.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar que se hizo el envío por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaMetasServicio.php');
    exit;
}

// Obtener el dato del formulario
$nombre = isset($_POST['metaServicio']) ? trim($_POST['metaServicio']) : '';

// Validar que no esté vacío
if (empty($nombre)) {
    echo "<script>
            alert('El nombre de la meta de servicio no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Insertar en la base de datos
$sql = "INSERT INTO unidad_servicio (nombre) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $nombre);

// Ejecutar y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Meta de servicio registrada correctamente.');
            window.location.href = 'buscarMetasServicio.php';
          </script>";
} else {
    echo "<script>
            alert('Error al registrar la meta de servicio: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
