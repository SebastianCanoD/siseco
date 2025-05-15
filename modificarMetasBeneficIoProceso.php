<?php
// modificarMetasBeneficioProceso.php

session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarMetasBeneficio.php');
    exit;
}

// Obtener los datos enviados por el formulario
$id_beneficiario = isset($_POST['id_beneficiarios']) ? (int) $_POST['id_beneficiarios'] : 0;
$nombre          = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre de la meta de beneficio no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Preparar la consulta de actualización
$sql = "UPDATE unidad_beneficiarios SET nombre = ? WHERE id_beneficiarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nombre, $id_beneficiario);

// Ejecutar consulta y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Meta de beneficio actualizada correctamente.');
            window.location.href = 'buscarMetasBeneficio.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar la meta de beneficio: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
exit;
