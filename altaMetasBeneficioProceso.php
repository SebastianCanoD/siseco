<?php
// procesarAltaMetasBeneficio.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaMetasBeneficio.php');
    exit;
}

// Obtener el dato del formulario
$nombre = isset($_POST['metaBeneficio']) ? trim($_POST['metaBeneficio']) : '';

// Validar que no esté vacío
if (empty($nombre)) {
    echo "<script>
            alert('El nombre de la meta de beneficio no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Preparar la consulta (tabla correcta: beneficiarios)
$sql = "INSERT INTO unidad_beneficiarios (nombre) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $nombre);

// Ejecutar y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Meta de beneficio registrada correctamente.');
            window.location.href = 'buscarMetasBeneficio.php';
          </script>";
} else {
    echo "<script>
            alert('Error al registrar la meta de beneficio: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
