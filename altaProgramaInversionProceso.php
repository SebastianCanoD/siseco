<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaProgramaInversion.php');
    exit;
}

// Obtener el nombre desde el formulario
$nombre = isset($_POST['programaInversion']) ? trim($_POST['programaInversion']) : '';

// Validar que no esté vacío
if (empty($nombre)) {
    echo "<script>
            alert('El nombre del programa de inversión no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Insertar en la tabla programa_inv
$sql = "INSERT INTO programa_inversion (nombre) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $nombre);

if ($stmt->execute()) {
    echo "<script>
            alert('Programa de inversión registrado correctamente.');
            window.location.href = 'buscarProgramaInversion.php';
          </script>";
} else {
    echo "<script>
            alert('Error al registrar el programa de inversión: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

$stmt->close();
$conn->close();
