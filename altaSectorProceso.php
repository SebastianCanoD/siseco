<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verifica si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaSector.php');
    exit;
}

// Obtener y validar el nombre del sector
$nombre = isset($_POST['sector']) ? trim($_POST['sector']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre del sector no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Insertar en la tabla 'sector'
$sql = "INSERT INTO sector (nombre) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $nombre);

if ($stmt->execute()) {
    echo "<script>
            alert('Sector registrado correctamente.');
            window.location.href = 'buscarSector.php';
          </script>";
} else {
    echo "<script>
            alert('Error al registrar el sector: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

$stmt->close();
$conn->close();
